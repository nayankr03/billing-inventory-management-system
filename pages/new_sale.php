<?php
    require_once "../includes/config.php";

    if (!isset($_SESSION['user_id'])) {
        header("Location: ../login.php");
        exit();
    }
    require_once "../includes/config.php";
    include "../includes/header.php";
    include "../includes/sidebar.php";

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
                    <i class="bi bi-cart-plus"></i> Create New Sale
                </h4>
            </div>

            <div class="card-body">

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

                        <input type="hidden" name="customer_id" id="customerId">

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
            </div>

        </div>

    </div>

</div>

<script>
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
</script>

<!-- Customer Modal -->

<div class="modal fade"
    id="customerModal"
    tabindex="-1">

    <div class="modal-dialog">

        <div class="modal-content">

            <form method="POST">

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