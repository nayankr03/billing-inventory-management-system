<?php
require_once "../includes/config.php";
require_once "../includes/auth.php";

staffOnly();

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

    <div class="container py-4">

        <div class="d-flex justify-content-between align-items-center mb-3">

            <a href="sales.php" class="btn btn-secondary">

                <i class="bi bi-arrow-left"></i>

                Back to Sales

            </a>
            <a href="invoice_pdf.php?id=<?= $sale['id']; ?>"
                target="_blank"
                class="btn btn-primary">
                <i class="bi bi-printer"></i> Print Invoice
            </a>

        </div>

        <div class="row justify-content-center">

            <div class="col-lg-10">

                <div class="card shadow border-0">

                    <div class="card-header bg-primary text-white">

                        <h4 class="mb-0">
                            <i class="bi bi-receipt"></i>
                            Invoice
                        </h4>

                    </div>

                    <div class="card-body">

                        <div class="row mb-4">

                            <div class="col-md-6">

                                <div class="d-flex align-items-center">

                                    <?php if (!empty($company['logo'])) { ?>

                                        <img
                                            src="../uploads/logo/<?= htmlspecialchars($company['logo']); ?>"
                                            style="height:65px;width:90px;object-fit:contain;"
                                            class="me-4">

                                    <?php } ?>

                                    <div>

                                        <h1 class="fw-bold text-primary mb-1">

                                            <?= htmlspecialchars($company['company_name']); ?>

                                        </h1>

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

                            <div class="col-md-6 text-end d-flex flex-column justify-content-center">
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

                        <h5 class="fw-bold border-bottom pb-2 mb-3">
                            <strong>Bill To</strong>
                        </h5>

                        <table class="table table-borderless table-sm mb-2">

                            <tr>
                                <td width="15%"><strong>Name</strong></td>
                                <td width="35%">: <?= htmlspecialchars($sale['customer_name']); ?></td>

                                <td width="15%"><strong>Mobile</strong></td>
                                <td width="35%">: <?= htmlspecialchars($sale['mobile']); ?></td>
                            </tr>

                            <tr>
                                <td><strong>Address</strong></td>
                                <td>: <?= htmlspecialchars($sale['address']); ?></td>

                                <td><strong>Payment</strong></td>
                                <td>: <?= htmlspecialchars($sale['payment_method']); ?></td>
                            </tr>

                        </table>

                        <hr class="my-2">

                        <h5 class="mb-3">
                            Invoice Items
                        </h5>

                        <div class="table-responsive">

                            <table class="table table-bordered table-sm align-middle">
                                <table class="table table-bordered align-middle">
                                    <thead class="table-primary text-center">

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

                                            <th colspan="5" class="text-end bg-light">

                                                <span class="fw-bold text-success">

                                                    Grand Total

                                            </th>

                                            <th class="text-end bg-light">

                                                <h5 class="mb-0 text-success fw-bold">

                                                    ₹<?= number_format($sale['grand_total'], 2); ?>


                                            </th>

                                        </tr>

                                    </tfoot>

                                </table>
                                <hr class="my-2">

                                <div class="text-center mt-3 pt-2 border-top">

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
            <style>
                /* ======================================
   Invoice Layout
====================================== */

                .main-content {
                    display: flex;
                    justify-content: center;
                    padding: 30px;
                }

                .main-content>.container {
                    width: 100%;
                    max-width: 1050px;
                    margin: auto;
                }

                .card {
                    margin: auto;
                    border: none;
                    border-radius: 12px;
                    box-shadow: 0 6px 18px rgba(0, 0, 0, .12);
                }

                .card-header {
                    padding: 14px 20px;
                    font-size: 22px;
                    font-weight: 600;
                }

                .card-body {
                    padding: 20px;
                }

                /* ======================================
   Company Details
====================================== */

                .card-body h1,
                .card-body h2 {
                    font-weight: 700;
                    margin-bottom: 5px;
                }

                .card-body p {
                    margin-bottom: 4px;
                }

                /* ======================================
   Bill To
====================================== */

                .table-borderless td {
                    padding: 2px 6px;
                    font-size: 13px;
                }

                /* ======================================
   Product Table
====================================== */

                .table {
                    margin-bottom: 0;
                }

                .table th,
                .table td {
                    vertical-align: middle;
                }

                .table thead th {
                    text-align: center;
                    background: #dbeafe;
                    font-weight: 600;
                }

                .table td.text-end,
                .table th.text-end {
                    text-align: right;
                }

                .table td.text-center,
                .table th.text-center {
                    text-align: center;
                }

                /* ======================================
   Compact Table
====================================== */

                .table-sm td,
                .table-sm th {
                    padding: 5px 8px;
                    font-size: 13px;
                }

                .table tfoot th {
                    padding: 12px !important;
                    background: #f8f9fa;
                }

                tfoot h5 {
                    margin: 0;
                    font-size: 24px;
                }

                /* ======================================
   Thank You
====================================== */

                .text-center h5 {
                    font-weight: 600;
                }

                .text-center p {
                    color: #6c757d;
                }

                /* ======================================
   Print Layout
====================================== */

                @media print {

                    @page {
                        size: A4;
                        margin: 10mm;
                    }

                    body {
                        background: #fff;
                        font-size: 12px;
                    }

                    .sidebar,
                    .navbar,
                    .btn {
                        display: none !important;
                    }

                    .main-content {
                        display: block !important;
                        margin: 0 !important;
                        padding: 0 !important;
                    }

                    .container,
                    .container-fluid {
                        width: 100% !important;
                        max-width: 100% !important;
                        padding: 0 !important;
                        margin: 0 auto !important;
                    }

                    .card {
                        width: 100% !important;
                        border: none !important;
                        box-shadow: none !important;
                    }

                    .card-header {
                        display: none !important;
                    }

                    .card-body {
                        padding: 0 !important;
                    }

                    h1 {
                        font-size: 20px;
                    }

                    h2 {
                        font-size: 18px;
                    }

                    h3 {
                        font-size: 16px;
                    }

                    h4 {
                        font-size: 16px;
                    }

                    h5 {
                        font-size: 14px;
                    }

                    h6 {
                        font-size: 13px;
                    }

                    table {
                        page-break-inside: auto;
                    }

                    tr {
                        page-break-inside: avoid;
                        page-break-after: auto;
                    }

                    hr {
                        margin: 8px 0;
                    }
                }

                /* ======================================
   Desktop Width
====================================== */

                @media(min-width:1200px) {

                    .main-content>.container {
                        max-width: 950px;
                    }

                }
            </style>
            <?php include "../includes/footer.php"; ?>