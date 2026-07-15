<?php
require_once "../includes/config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

include "../includes/header.php";
include "../includes/sidebar.php";

/* -------------------------
   COMPANY SETTINGS
--------------------------*/

$companyResult = mysqli_query(
    $conn,
    "SELECT * FROM company_settings LIMIT 1"
);

$company = mysqli_fetch_assoc($companyResult);

if (!isset($_GET['id'])) {
    die("Invalid Invoice.");
}

$sale_id = intval($_GET['id']);
/* -------------------------
   GET SALE DETAILS
--------------------------*/

$sql = "SELECT
            sales.*,
            customers.customer_name,
            customers.mobile,
            customers.email,
            customers.city,
            customers.address
        FROM sales
        INNER JOIN customers
            ON sales.customer_id = customers.id
        WHERE sales.id = ?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param($stmt, "i", $sale_id);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {

    die("Invoice not found.");
}

$sale = mysqli_fetch_assoc($result);
/* -------------------------
   GET INVOICE ITEMS
--------------------------*/

$sql = "SELECT
            sale_items.*,
            products.product_name,
            products.product_code
        FROM sale_items
        INNER JOIN products
            ON sale_items.product_id = products.id
        WHERE sale_items.sale_id = ?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param($stmt, "i", $sale_id);

mysqli_stmt_execute($stmt);

$items = mysqli_stmt_get_result($stmt);
?>

<div class="main-content">
    <div class="container-fluid">

        <div class="card shadow">

            <div class="card-header bg-primary text-white">

                <h4 class="mb-0">
                    <i class="bi bi-receipt"></i>
                    Invoice
                </h4>

            </div>

            <div class="card-body">

                <h3 class="text-center">
                    Billing Inventory Management System
                </h3>

                <hr>

                <div class="row mb-4">

                    <div class="col-md-6">
                        <div class="d-flex justify-content-between mb-4">

                            <a href="sales.php" class="btn btn-secondary">

                                <i class="bi bi-arrow-left"></i>

                                Back to Sales

                            </a>

                            <button
                                onclick="window.print()"
                                class="btn btn-success">

                                <i class="bi bi-printer"></i>

                                Print Invoice

                            </button>

                        </div>
                        <div class="d-flex align-items-center mb-3">

                            <?php if (!empty($company['logo'])) { ?>

                                <img
                                    src="../uploads/logo/<?= htmlspecialchars($company['logo']); ?>"
                                    style="height:80px;width:auto;"
                                    class="me-3">

                            <?php } ?>

                            <div>

                                <h2 class="fw-bold text-primary mb-0">

                                    <?= htmlspecialchars($company['company_name']); ?>

                                </h2>

                                <p class="mb-0">

                                    <?= nl2br(htmlspecialchars($company['address'])); ?>

                                </p>

                                <small>

                                    Phone :
                                    <?= htmlspecialchars($company['phone']); ?>

                                </small>

                                <br>

                                <small>

                                    Email :
                                    <?= htmlspecialchars($company['email']); ?>

                                </small>

                                <br>

                                <?php if (!empty($company['gst_number'])) { ?>

                                    <small>

                                        GSTIN :
                                        <?= htmlspecialchars($company['gst_number']); ?>

                                    </small>

                                <?php } ?>

                            </div>

                        </div>

                    </div>

                    <div class="col-md-6 text-end">
                        <h3 class="fw-bold text-uppercase">

                            TAX INVOICE

                        </h3>

                        <p class="mb-1">
                            <strong>Invoice No:</strong>
                            <?= $sale['invoice_no']; ?>
                        </p>

                        <p class="mb-1">
                            <strong>Date:</strong>
                            <?= date("d-m-Y", strtotime($sale['invoice_date'])); ?>
                        </p>

                    </div>

                </div>

                <hr>

                <h5 class="fw-bold">

                    Bill To

                </h5>
                <div class="row">

                    <div class="col-md-6">

                        <p><strong>Name:</strong> <?= htmlspecialchars($sale['customer_name']); ?></p>

                        <p><strong>Mobile:</strong> <?= htmlspecialchars($sale['mobile']); ?></p>

                        <p><strong>Email:</strong> <?= htmlspecialchars($sale['email']); ?></p>

                    </div>

                    <div class="col-md-6">

                        <p><strong>City:</strong> <?= htmlspecialchars($sale['city']); ?></p>

                        <p><strong>Address:</strong> <?= htmlspecialchars($sale['address']); ?></p>

                        <p><strong>Payment:</strong> <?= htmlspecialchars($sale['payment_method']); ?></p>

                    </div>

                </div>

                <hr>

                <h5 class="mb-3">
                    Invoice Items
                </h5>

                <div class="table-responsive">

                    <table class="table table-bordered">

                        <thead class="table-primary">

                            <tr>

                                <th>#</th>

                                <th>Product Code</th>

                                <th>Product Name</th>

                                <th class="text-center">Qty</th>

                                <th class="text-end">Price</th>

                                <th class="text-end">Total</th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php

                            $sr = 1;

                            while ($item = mysqli_fetch_assoc($items)) {

                            ?>

                                <tr>

                                    <td><?= $sr++; ?></td>

                                    <td><?= htmlspecialchars($item['product_code']); ?></td>

                                    <td><?= htmlspecialchars($item['product_name']); ?></td>

                                    <td class="text-center"><?= $item['quantity']; ?></td>

                                    <td class="text-end">₹<?= number_format($item['price'], 2); ?></td>

                                    <td class="text-end">₹<?= number_format($item['total'], 2); ?></td>

                                </tr>

                            <?php } ?>

                        </tbody>

                        <tfoot>

                            <tr>

                                <th colspan="5" class="text-end">

                                    <h5 class="mb-0 text-success">

                                        Grand Total

                                    </h5>
                                </th>

                                <th class="text-end text-success">

                                    <h4 class="mb-0 text-success">

                                        ₹<?= number_format($sale['grand_total'], 2); ?>

                                    </h4>
                                </th>

                            </tr>

                        </tfoot>

                    </table>
                    <hr>

                    <div class="text-center mt-4">

                        <h5>

                            Thank You For Shopping!

                        </h5>

                        <p class="text-muted">

                            This is a computer-generated invoice.

                        </p>

                    </div>

                </div>

            </div>

        </div>

    </div>
</div>
<style>
    @media print {

        .sidebar,
        .navbar,
        .btn {

            display: none !important;

        }

        .main-content {

            margin: 0;
            padding: 0;

        }

        .card {

            border: none;
            box-shadow: none;

        }

        body {

            background: #fff;

        }

    }
</style>
<?php include "../includes/footer.php"; ?>