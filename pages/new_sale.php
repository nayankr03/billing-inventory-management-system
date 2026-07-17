<?php
require_once "../includes/config.php";
require_once "../includes/auth.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

staffOnly();
/* -------------------------
   AUTO INVOICE NUMBER
--------------------------*/
$invoice = mysqli_query($conn, "SELECT invoice_no FROM sales ORDER BY id DESC LIMIT 1");

if (mysqli_num_rows($invoice) > 0) {
    $row = mysqli_fetch_assoc($invoice);
    $last = intval(substr($row['invoice_no'], 3));
    $invoiceNo = "INV" . str_pad($last + 1, 6, "0", STR_PAD_LEFT);
} else {
    $invoiceNo = "INV000001";
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['save_customer'])) {

    $customer_id = intval($_POST['customer_id']);

    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);

    $cart = json_decode($_POST['cart_data'], true);

    if ($customer_id == 0) {
        die("Customer not selected.");
    }

    if (empty($cart)) {
        die("Cart is empty.");
    }

    mysqli_begin_transaction($conn);

    try {

        // Invoice Details
        $invoice_no = $invoiceNo;
        $invoice_date = date("Y-m-d");
        $user_id = $_SESSION['user_id'];

        // Calculate Grand Total
        $grand_total = 0;

        foreach ($cart as $item) {
            $grand_total += $item['total'];
        }

        // ==========================
        // INSERT INTO SALES
        // ==========================

        $sql = "INSERT INTO sales
        (
            invoice_no,
            customer_id,
            user_id,
            invoice_date,
            grand_total,
            payment_method
        )
        VALUES
        (
            ?, ?, ?, ?, ?, ?
        )";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "siisds",
            $invoice_no,
            $customer_id,
            $user_id,
            $invoice_date,
            $grand_total,
            $payment_method
        );

        mysqli_stmt_execute($stmt);

        $sale_id = mysqli_insert_id($conn);


        if (!$sale_id) {
            throw new Exception("Failed to create Sale.");
        }

        // ==========================
        // SAVE SALE ITEMS
        // ==========================

        foreach ($cart as $item) {

            $product_id = intval($item['product_id']);
            $qty = intval($item['qty']);
            $price = floatval($item['price']);
            $total = floatval($item['total']);

            $sql = "INSERT INTO sale_items
            (
                sale_id,
                product_id,
                quantity,
                price,
                total
            )
            VALUES
            (
                ?, ?, ?, ?, ?
            )";

            $stmt = mysqli_prepare($conn, $sql);

            mysqli_stmt_bind_param(
                $stmt,
                "iiidd",
                $sale_id,
                $product_id,
                $qty,
                $price,
                $total
            );

            mysqli_stmt_execute($stmt);

            // Reduce Stock

            $sql = "UPDATE products
                    SET stock = stock - ?
                    WHERE id = ?";

            $stmt = mysqli_prepare($conn, $sql);

            mysqli_stmt_bind_param(
                $stmt,
                "ii",
                $qty,
                $product_id
            );

            mysqli_stmt_execute($stmt);
        }

        // ==========================
        // COMMIT
        // ==========================

        mysqli_commit($conn);

        $_SESSION['success'] = "Sale Saved Successfully.";

        header("Location: invoice.php?id=" . $sale_id);
        exit();
    } catch (Exception $e) {

        mysqli_rollback($conn);

        die($e->getMessage());
    }
}

include "../includes/header.php";
include "../includes/sidebar.php";


/* ==========================
   QUICK ADD CUSTOMER
========================== */

if (isset($_POST['save_customer'])) {

    $customer_name = mysqli_real_escape_string($conn, trim($_POST['customer_name']));
    $mobile        = mysqli_real_escape_string($conn, trim($_POST['mobile']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $city = mysqli_real_escape_string($conn, trim($_POST['city']));
    $address = mysqli_real_escape_string($conn, trim($_POST['address']));

    if ($customer_name == "" || $mobile == "") {

        $error = "Customer Name and Mobile are required.";
    } elseif (!preg_match('/^[0-9]{10}$/', $mobile)) {

        $error = "Enter a valid 10-digit mobile number.";
    } else {

        // Check duplicate mobile
        $check = mysqli_query(
            $conn,
            "SELECT id FROM customers WHERE mobile='$mobile'"
        );

        if (mysqli_num_rows($check) > 0) {

            $error = "Customer already exists.";
        } else {

            // Generate Customer Code
            $codeQuery = mysqli_query(
                $conn,
                "SELECT customer_code
                 FROM customers
                 ORDER BY id DESC
                 LIMIT 1"
            );

            if (mysqli_num_rows($codeQuery) > 0) {

                $row = mysqli_fetch_assoc($codeQuery);

                $last = intval(substr($row['customer_code'], 3));

                $customerCode = "CUS" .
                    str_pad($last + 1, 3, "0", STR_PAD_LEFT);
            } else {

                $customerCode = "CUS001";
            }

            mysqli_query(
                $conn,
                "INSERT INTO customers
            (
                customer_code,
                customer_name,
                mobile,
                email,
                city,
                address,
                status
            )
            VALUES
            (
               '$customerCode',
                '$customer_name',
                '$mobile',
                '$email',
                '$city',
                '$address',
                'Active'
            )"
            );

            $_SESSION['success'] = "Customer Added Successfully";

            header("Location:new_sale.php");

            exit();
        }
    }
}

?>



<div class="main-content">
    <div class="container-fluid">

        <div class="card shadow border-0">

            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">
                    <i class="bi bi-cart-check"></i>
                    Create New Bill
                </h4>
            </div>

            <div class="card-body">
                <form method="POST" id="saleForm">
                    <div class="row mb-4">

                        <div class="col-md-6">
                            <label class="fw-semibold">Invoice No</label>
                            <input type="text" class="form-control" value="<?= $invoiceNo ?>" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="fw-semibold">Date</label>
                            <input type="date" class="form-control" value="<?= date('Y-m-d'); ?>" readonly>
                        </div>

                    </div>

                    <hr>

                    <h5 class="fw-bold mb-3">Customer</h5>

                    <div class="row align-items-end">

                        <div class="col-md-9">

                            <label class="form-label">Search Customer</label>

                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-search"></i>
                                </span>

                                <input
                                    type="text"
                                    id="customerSearch"
                                    class="form-control"
                                    placeholder="Search by Customer Name or Mobile"
                                    autocomplete="off">
                            </div>

                            <div id="customerResults"
                                class="list-group mt-1 shadow-sm"
                                style="display:none;max-height:250px;overflow-y:auto;">
                            </div>

                        </div>

                        <div class="col-md-3">
                            <button
                                type="button"
                                class="btn btn-success w-100"
                                data-bs-toggle="modal"
                                data-bs-target="#customerModal">

                                <i class="bi bi-person-plus"></i>

                                Add Customer

                            </button>
                        </div>

                    </div>

                    <div class="card mt-4 border shadow-sm">
                        <div class="card-body">

                            <h5 class="mb-3">Selected Customer</h5>

                            <p><strong>Name :</strong>
                                <span id="selectedCustomerName">None</span>
                            </p>

                            <p><strong>Mobile :</strong>
                                <span id="selectedCustomerMobile">-</span>
                            </p>

                            <p><strong>Email :</strong>
                                <span id="selectedCustomerEmail">-</span>
                            </p>

                            <p>
                                <strong>City :</strong>
                                <span id="selectedCustomerCity"></span>
                            </p>

                            <p class="mb-0"><strong>Address :</strong>
                                <span id="selectedCustomerAddress">-</span>
                            </p>

                            <input
                                type="hidden"
                                name="customer_id"
                                id="customerId">

                            <input
                                type="hidden"
                                name="cart_data"
                                id="cartData">
                        </div>
                    </div>


                    <!-- product-->
                    <hr>

                    <h5 class="fw-bold mb-3">
                        Product
                    </h5>

                    <div class="row align-items-end">

                        <div class="col-md-9">

                            <label class="form-label">Search Product</label>

                            <div class="input-group">

                                <span class="input-group-text">
                                    <i class="bi bi-search"></i>
                                </span>

                                <input
                                    type="text"
                                    id="productSearch"
                                    class="form-control"
                                    placeholder="Search by Product Name or Code"
                                    autocomplete="off">

                            </div>

                            <div id="productResults"
                                class="list-group mt-1 shadow-sm"
                                style="display:none;max-height:250px;overflow-y:auto;">
                            </div>

                        </div>

                    </div>
                    <div class="card mt-4 border shadow-sm">

                        <div class="card-body">

                            <h5 class="mb-3">

                                Selected Product

                            </h5>

                            <p>

                                <strong>Product :</strong>

                                <span id="selectedProductName">

                                    None

                                </span>

                            </p>

                            <p>

                                <strong>Code :</strong>

                                <span id="selectedProductCode">

                                    -

                                </span>

                            </p>

                            <p>

                                <strong>Price :</strong>

                                ₹<span id="selectedProductPrice">

                                    0.00

                                </span>

                            </p>

                            <p>

                                <strong>Stock :</strong>

                                <span id="selectedProductStock">

                                    0

                                </span>

                            </p>

                            <input
                                type="hidden"
                                name="product_id"
                                id="productId">

                        </div>

                    </div>
                    <div class="row mt-4">

                        <div class="col-md-4">

                            <label>

                                Quantity

                            </label>

                            <input
                                type="number"
                                id="quantity"
                                name="quantity"
                                class="form-control"
                                value="1"
                                min="1">

                        </div>

                        <div class="col-md-4">

                            <label>

                                Selling Price

                            </label>

                            <input
                                type="text"
                                id="price"
                                class="form-control"
                                readonly>

                        </div>

                        <div class="col-md-4 d-flex align-items-end">

                            <button
                                type="button"
                                class="btn btn-primary w-100"
                                id="addProduct">

                                <i class="bi bi-plus-circle"></i>

                                Add Product

                            </button>

                        </div>

                    </div>
                    <!-- INVOICE-->
                    <hr>

                    <h4 class="fw-bold mt-4">
                        <i class="bi bi-receipt"></i> Invoice Items
                    </h4>

                    <div class="table-responsive">

                        <table class="table table-bordered table-hover align-middle">

                            <thead class="table-primary">

                                <tr>

                                    <th width="5%">#</th>

                                    <th>Product</th>

                                    <th width="10%">Qty</th>

                                    <th width="15%">Price</th>

                                    <th width="15%">Total</th>

                                    <th width="8%">Action</th>

                                </tr>

                            </thead>

                            <tbody id="invoiceItems">

                                <tr id="emptyRow">

                                    <td colspan="6" class="text-center text-muted">

                                        No Product Added

                                    </td>

                                </tr>

                            </tbody>

                        </table>
                        <div class="row">

                            <div class="col-md-4 ms-auto">

                                <div class="card border-success shadow-sm">

                                    <div class="card-body text-center">

                                        <h5 class="mb-2">

                                            Grand Total

                                        </h5>

                                        <h2 class="text-success">

                                            ₹ <span id="grandTotal">0.00</span>

                                        </h2>

                                    </div>

                                </div>

                            </div>

                            <div class="row mt-4">

                                <div class="col-md-4">

                                    <label class="form-label">
                                        <strong>Payment Method</strong>
                                    </label>
                                    <select
                                        name="payment_method"
                                        id="paymentMethod"
                                        class="form-select">

                                        <option value="Cash">Cash</option>
                                        <option value="UPI">UPI</option>
                                        <option value="Card">Card</option>

                                    </select>

                                </div>

                                <div class="col-md-8 d-flex align-items-end justify-content-end">

                                    <button
                                        type="button"
                                        id="saveSale"
                                        class="btn btn-success btn-lg">

                                        <i class="bi bi-check-circle"></i>

                                        Save Sale

                                    </button>
                                </div>

                            </div>

                        </div>

                    </div>
            </div>

        </div>

    </div>

</div>
</form>
<script>
    let cart = [];
    let serial = 1;
    const customerSearch = document.getElementById("customerSearch");
    const customerResults = document.getElementById("customerResults");

    customerSearch.addEventListener("keyup", function() {

        let keyword = this.value;

        if (keyword.length < 1) {

            customerResults.style.display = "none";
            customerResults.innerHTML = "";
            return;

        }

        fetch("search_customer.php?search=" + encodeURIComponent(keyword))
            .then(response => response.text())
            .then(data => {

                customerResults.innerHTML = data;
                customerResults.style.display = "block";

            });

    });

    document.addEventListener("click", function(e) {

        if (e.target.closest(".customer-item")) {

            let item = e.target.closest(".customer-item");

            document.getElementById("customerId").value = item.dataset.id;
            document.getElementById("selectedCustomerName").innerText = item.dataset.name;
            document.getElementById("selectedCustomerMobile").innerText = item.dataset.mobile;
            document.getElementById("selectedCustomerEmail").innerText = item.dataset.email;
            document.getElementById("selectedCustomerCity").innerText = item.dataset.city;
            document.getElementById("selectedCustomerAddress").innerText = item.dataset.address;

            customerSearch.value = item.dataset.name + " - " + item.dataset.mobile;

            customerResults.style.display = "none";

        }

    });

    document.addEventListener("click", function(e) {

        if (!e.target.closest("#customerSearch") &&
            !e.target.closest("#customerResults")) {

            customerResults.style.display = "none";

        }

    });


    //product//
    const productSearch = document.getElementById("productSearch");
    const productResults = document.getElementById("productResults");

    productSearch.addEventListener("keyup", function() {

        let keyword = this.value;

        if (keyword.length < 1) {

            productResults.style.display = "none";
            productResults.innerHTML = "";
            return;

        }

        fetch("search_product.php?search=" + encodeURIComponent(keyword))

            .then(response => response.text())

            .then(data => {

                productResults.innerHTML = data;

                productResults.style.display = "block";

            });

    });

    document.addEventListener("click", function(e) {

        if (e.target.closest(".product-item")) {

            let item = e.target.closest(".product-item");

            document.getElementById("productId").value = item.dataset.id;

            document.getElementById("selectedProductName").innerText = item.dataset.name;

            document.getElementById("selectedProductCode").innerText = item.dataset.code;

            document.getElementById("selectedProductPrice").innerText = item.dataset.price;

            document.getElementById("selectedProductStock").innerText = item.dataset.stock;

            document.getElementById("price").value = item.dataset.price;

            productSearch.value =
                item.dataset.name + " (" + item.dataset.code + ")";

            productResults.style.display = "none";

        }

    });

    //PRODUCT LOGIC//
    // =============================
    // ADD PRODUCT TO CART
    // =============================

    document.getElementById("addProduct").addEventListener("click", function() {

        let productId = document.getElementById("productId").value;
        let productName = document.getElementById("selectedProductName").innerText;
        let qty = parseInt(document.getElementById("quantity").value);
        let price = parseFloat(document.getElementById("price").value);
        let stock = parseInt(document.getElementById("selectedProductStock").innerText);

        // No Product Selected
        if (productId == "") {

            Swal.fire({
                icon: 'warning',
                title: 'No Product Selected',
                text: 'Please search and select a product first.'
            });

            return;
        }

        // Invalid Quantity
        if (qty <= 0 || isNaN(qty)) {

            Swal.fire({
                icon: 'warning',
                title: 'Invalid Quantity',
                text: 'Quantity must be greater than zero.'
            });

            return;
        }

        // Quantity greater than stock
        if (qty > stock) {

            Swal.fire({
                icon: 'warning',
                title: 'Invalid Quantity',
                text: 'Entered quantity is greater than available stock.'
            });

            return;
        }

        // Check if already in cart
        let existing = cart.find(item => item.product_id == productId);

        if (existing) {

            // Prevent exceeding stock
            if ((existing.qty + qty) > existing.stock) {

                Swal.fire({
                    icon: 'warning',
                    title: 'Stock Limit Reached',
                    text: `Only ${existing.stock} item(s) are available in stock.`
                });

                return;
            }

            existing.qty += qty;
            existing.total = existing.qty * existing.price;

        } else {

            cart.push({

                product_id: productId,
                product_name: productName,
                qty: qty,
                price: price,
                stock: stock,
                total: qty * price

            });

        }

        renderCart();

        // =============================
        // RESET PRODUCT SECTION
        // =============================

        productSearch.value = "";

        document.getElementById("productId").value = "";

        document.getElementById("selectedProductName").innerText = "None";

        document.getElementById("selectedProductCode").innerText = "-";

        document.getElementById("selectedProductPrice").innerText = "0.00";

        document.getElementById("selectedProductStock").innerText = "0";

        document.getElementById("price").value = "";

        document.getElementById("quantity").value = 1;

        productResults.innerHTML = "";

        productResults.style.display = "none";

        productSearch.focus();

    });


    document.addEventListener("click", function(e) {

        const btn = e.target.closest(".remove-product");

        if (!btn) return;

        const index = parseInt(btn.dataset.index);

        cart.splice(index, 1);

        renderCart();

    });

    function renderCart() {

        let tbody = document.getElementById("invoiceItems");

        tbody.innerHTML = "";

        let grand = 0;

        cart.forEach((item, index) => {

            grand += item.total;

            tbody.innerHTML += `

<tr>

<td>${index+1}</td>

<td>

<strong>${item.product_name}</strong>

<br>

<small class="text-muted">

Available Stock : ${item.stock}

</small>

</td>
<td>

<div class="input-group input-group-sm">

<button
class="btn btn-outline-secondary decreaseQty"
data-index="${index}">

-

</button>

<input
type="text"
class="form-control text-center"
value="${item.qty}"
readonly>

<button
class="btn btn-outline-secondary increaseQty"
data-index="${index}">

+

</button>

</div>

</td>
<td>₹${item.price.toFixed(2)}</td>

<td>₹${item.total.toFixed(2)}</td>

<td>

<button
 class="btn btn-sm btn-danger remove-product"
        data-index="${index}">
        <i class="bi bi-trash"></i>
</button>

</td>

</tr>

`;

        });

        if (cart.length == 0) {

            tbody.innerHTML = `

<tr>

<td colspan="6" class="text-center text-muted">

No Product Added

</td>

</tr>

`;

        }

        document.getElementById("grandTotal").innerText = grand.toFixed(2);

    }


    document.addEventListener("click", function(e) {

        const btn = e.target.closest(".increaseQty");

        if (!btn) return;

        let index = parseInt(btn.dataset.index);

        if (cart[index].qty >= cart[index].stock) {

            Swal.fire({
                icon: 'warning',
                title: 'Stock Limit',
                text: 'Cannot exceed available stock.'
            });

            return;

        }

        cart[index].qty++;

        cart[index].total = cart[index].qty * cart[index].price;

        renderCart();

    });


    document.addEventListener("click", function(e) {

        const btn = e.target.closest(".decreaseQty");

        if (!btn) return;

        let index = parseInt(btn.dataset.index);

        if (cart[index].qty > 1) {

            cart[index].qty--;

            cart[index].total =
                cart[index].qty * cart[index].price;

        }

        renderCart();

    });

    //savesale logic//

    document.getElementById("saveSale").addEventListener("click", function() {

        if (document.getElementById("customerId").value == "") {

            Swal.fire({
                icon: "warning",
                title: "Customer Required",
                text: "Please select a customer."
            });

            return;

        }

        if (cart.length == 0) {

            Swal.fire({
                icon: "warning",
                title: "Invoice Empty",
                text: "Please add at least one product."
            });

            return;

        }

        document.getElementById("cartData").value =
            JSON.stringify(cart);

        document.getElementById("saleForm").submit();

    });
</script>

<!-- Customer Modal -->

<div class="modal fade"
    id="customerModal"
    tabindex="-1">

    <div class="modal-dialog">

        <div class="modal-content">

            <form method="POST" id="customerForm">
                <div class="modal-header">

                    <h5 class="modal-title">

                        Quick Add Customer

                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                    </button>

                </div>

                <div class="modal-body">

                    <div class="mb-3">

                        <label>

                            Customer Name

                        </label>

                        <input
                            type="text"
                            name="customer_name"
                            class="form-control"
                            required>

                    </div>

                    <div class="mb-3">

                        <label>

                            Mobile Number

                        </label>

                        <input
                            type="text"
                            name="mobile"
                            maxlength="10"
                            class="form-control"
                            required>

                    </div>

                    <div class="mb-3">

                        <label>

                            Email

                        </label>

                        <input
                            type="email"
                            name="email"
                            class="form-control">


                    </div>

                    <div class="mb-3">

                        <label>City</label>

                        <input
                            type="text"
                            name="city"
                            class="form-control"
                            placeholder="Enter City">

                    </div>
                    <div class="mb-3">

                        <label>

                            Address

                        </label>

                        <textarea
                            name="address"
                            rows="3"
                            class="form-control">
                        </textarea>

                    </div>

                </div>

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">

                        Cancel

                    </button>

                    <button
                        type="submit"
                        name="save_customer"
                        class="btn btn-success">

                        Save Customer

                    </button>

                </div>
            </form>

        </div>

    </div>

</div>


<?php if (isset($_SESSION['success'])) { ?>

    <script>
        Swal.fire({

            icon: 'success',

            title: 'Success',

            text: '<?= $_SESSION['success']; ?>',

            timer: 1800,

            showConfirmButton: false

        });
    </script>

<?php unset($_SESSION['success']);
} ?>

<?php include "../includes/footer.php"; ?>