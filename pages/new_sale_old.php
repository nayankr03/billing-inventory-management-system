<?php
require_once "../includes/config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

/* -------------------------
   QUICK ADD CUSTOMER
--------------------------*/
if (isset($_POST['quick_add_customer'])) {

    $customer_name = mysqli_real_escape_string($conn, trim($_POST['customer_name']));
    $mobile = mysqli_real_escape_string($conn, trim($_POST['mobile']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $city = mysqli_real_escape_string($conn, trim($_POST['city']));
    $address = mysqli_real_escape_string($conn, trim($_POST['address']));

    if ($customer_name != "" && preg_match('/^[0-9]{10}$/', $mobile)) {

        $check = mysqli_query($conn, "SELECT id FROM customers WHERE mobile='$mobile'");

        if (mysqli_num_rows($check) == 0) {

            $code = mysqli_query($conn, "SELECT customer_code FROM customers ORDER BY id DESC LIMIT 1");

            if (mysqli_num_rows($code) > 0) {
                $r = mysqli_fetch_assoc($code);
                $last = intval(substr($r['customer_code'], 3));
                $customerCode = "CUS" . str_pad($last + 1, 3, "0", STR_PAD_LEFT);
            } else {
                $customerCode = "CUS001";
            }

            mysqli_query($conn, "INSERT INTO customers(
    customer_code,
    customer_name,
    mobile,
    email,
    city,
    address,
    status
) VALUES(
    '$customerCode',
    '$customer_name',
    '$mobile',
    '$email',
    '$city',
    '$address',
    'Active'
)");

            $newCustomerId = mysqli_insert_id($conn);
            header("Location: new_sale.php?customer=" . $newCustomerId);
            exit();
        }
    }
}

include "../includes/header.php";
include "../includes/sidebar.php";

/* Invoice */
$invoice = mysqli_query($conn, "SELECT invoice_no FROM sales ORDER BY id DESC LIMIT 1");

if (mysqli_num_rows($invoice) > 0) {
    $row = mysqli_fetch_assoc($invoice);
    $last = intval(substr($row['invoice_no'], 3));
    $invoiceNo = "INV" . str_pad($last + 1, 6, "0", STR_PAD_LEFT);
} else {
    $invoiceNo = "INV000001";
}

$selectedCustomer = isset($_GET['customer']) ? intval($_GET['customer']) : 0;

$customers = mysqli_query($conn, "SELECT * FROM customers WHERE status='Active' ORDER BY customer_name");
$products = mysqli_query($conn, "SELECT * FROM products WHERE status='Active' ORDER BY product_name");
?>

<div class="main-content">
    <div class="container-fluid">

        <div class="card shadow">

            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-cart-plus"></i> Create New Sale</h5>
            </div>

            <div class="card-body">

                <form method="POST">

                    <div class="row">

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Invoice No</label>
                            <input type="text" name="invoice_no" class="form-control" value="<?= $invoiceNo ?>" readonly>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Date</label>
                            <input type="date" name="invoice_date" class="form-control" value="<?= date('Y-m-d'); ?>" readonly>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Customer</label>
                            <select name="customer_id" class="form-select" required>
                                <option value="">Select Customer</option>
                                <?php while ($c = mysqli_fetch_assoc($customers)) { ?>
                                    <option value="<?= $c['id']; ?>" <?= ($selectedCustomer == $c['id']) ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($c['customer_name']); ?> - <?= htmlspecialchars($c['mobile']); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="col-md-2 d-flex align-items-end mb-3">
                            <button type="button" class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                                <i class="bi bi-person-plus"></i> Add
                            </button>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Payment Method</label>
                            <select name="payment_method" class="form-select">
                                <option value="Cash">Cash</option>
                                <option value="UPI">UPI</option>
                                <option value="Card">Card</option>
                            </select>
                        </div>

                    </div>

                    <hr>

                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Product</label>

                            <select name="product_id" class="form-select">
                                <option value="">Select Product</option>

                                <?php while ($p = mysqli_fetch_assoc($products)) { ?>
                                    <option value="<?= $p['id']; ?>">
                                        <?= htmlspecialchars($p['product_name']); ?>
                                    </option>
                                <?php } ?>

                            </select>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Quantity</label>
                            <input type="number" name="quantity" class="form-control" value="1" min="1">
                        </div>

                        <div class="col-md-3 d-flex align-items-end mb-3">
                            <button type="button" class="btn btn-success w-100">
                                <i class="bi bi-plus-circle"></i> Add Product
                            </button>
                        </div>

                    </div>

                    <hr>

                    <h5 class="mb-3"><i class="bi bi-receipt"></i> Invoice Items</h5>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-primary">
                                <tr>
                                    <th width="45%">Product</th>
                                    <th width="15%">Qty</th>
                                    <th width="20%">Price</th>
                                    <th width="20%">Total</th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">
                                        No Product Added
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="row">
                        <div class="col-md-4 offset-md-8">
                            <table class="table table-bordered">
                                <tr>
                                    <th>Grand Total</th>
                                    <th class="text-end">₹0.00</th>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Save Sale
                        </button>
                    </div>

                </form>

            </div>
        </div>

    </div>
</div>

<!-- Quick Add Customer Modal -->
<div class="modal fade" id="addCustomerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <form method="POST">

                <div class="modal-header">
                    <h5 class="modal-title">Quick Add Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="mb-3">
                        <label>Customer Name</label>
                        <input type="text"
                            name="customer_name"
                            class="form-control"
                            required>
                    </div>

                    <div class="mb-3">
                        <label>Mobile Number</label>
                        <input type="text"
                            name="mobile"
                            maxlength="10"
                            class="form-control"
                            required>
                    </div>

                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email"
                            name="email"
                            class="form-control"
                            placeholder="example@gmail.com">
                    </div>

                    <div class="mb-3">
                        <label>City</label>
                        <input type="text"
                            name="city"
                            class="form-control"
                            placeholder="Enter City">
                    </div>

                    <div class="mb-3">
                        <label>Address</label>
                        <textarea name="address"
                            class="form-control"
                            rows="3"
                            placeholder="Enter Address"></textarea>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="quick_add_customer" class="btn btn-success">Save Customer</button>
                </div>

            </form>

        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>