<?php
require_once "../includes/config.php";
require_once "../includes/auth.php";

adminOnly();

include "../includes/header.php";
include "../includes/sidebar.php";

/* -------------------------
   REPORT STATISTICS
--------------------------*/

// Today's Sales
$todaySales = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT IFNULL(SUM(grand_total),0) AS total
         FROM sales
         WHERE invoice_date = CURDATE()"
    )
);

// Monthly Revenue
$monthlyRevenue = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT IFNULL(SUM(grand_total),0) AS total
         FROM sales
         WHERE MONTH(invoice_date)=MONTH(CURDATE())
         AND YEAR(invoice_date)=YEAR(CURDATE())"
    )
);

// Total Invoices
$totalInvoices = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM sales"
    )
);

// Products Sold
$productsSold = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT IFNULL(SUM(quantity),0) AS total
         FROM sale_items"
    )
);
/* -------------------------
   TOP SELLING PRODUCTS
--------------------------*/

$topProducts = mysqli_query(
    $conn,
    "SELECT
        products.product_name,
        SUM(sale_items.quantity) AS qty_sold,
        SUM(sale_items.total) AS revenue
    FROM sale_items
    INNER JOIN products
        ON sale_items.product_id = products.id
    GROUP BY sale_items.product_id
    ORDER BY qty_sold DESC
    LIMIT 5"
);
/* -------------------------
   TOP CUSTOMERS
--------------------------*/

$topCustomers = mysqli_query(
    $conn,
    "SELECT
        customers.customer_name,
        COUNT(sales.id) AS total_bills,
        SUM(sales.grand_total) AS total_spent
    FROM sales
    INNER JOIN customers
        ON sales.customer_id = customers.id
    GROUP BY customers.id
    ORDER BY total_spent DESC
    LIMIT 5"
);

/* -------------------------
   LOW STOCK PRODUCTS
--------------------------*/

$lowStock = mysqli_query(
    $conn,
    "SELECT
        product_name,
        product_code,
        stock
    FROM products
    WHERE stock <= 5
    ORDER BY stock ASC
    LIMIT 10"
);

/* -------------------------
   RECENT SALES
--------------------------*/

$recentSales = mysqli_query(
    $conn,
    "SELECT
        sales.id,
        sales.invoice_no,
        sales.invoice_date,
        sales.grand_total,
        customers.customer_name
    FROM sales
    INNER JOIN customers
        ON sales.customer_id = customers.id
    ORDER BY sales.id DESC
    LIMIT 10"
);

?>

<div class="main-content">

    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-4">

            <h2 class="fw-bold">

                <i class="bi bi-graph-up-arrow"></i>

                Reports Dashboard

            </h2>

        </div>

        <!-- Cards will come here -->

        <div class="row">

            <div class="col-lg-3 col-md-6 mb-4">

                <div class="card shadow border-0 bg-primary text-white">

                    <div class="card-body">

                        <h6>Today's Sales</h6>

                        <h3>

                            ₹<?= number_format($todaySales['total'], 2); ?>

                        </h3>

                    </div>

                </div>

            </div>

            <div class="col-lg-3 col-md-6 mb-4">

                <div class="card shadow border-0 bg-success text-white">

                    <div class="card-body">

                        <h6>Monthly Revenue</h6>

                        <h3>

                            ₹<?= number_format($monthlyRevenue['total'], 2); ?>

                        </h3>

                    </div>

                </div>

            </div>

            <div class="col-lg-3 col-md-6 mb-4">

                <div class="card shadow border-0 bg-warning text-dark">

                    <div class="card-body">

                        <h6>Total Invoices</h6>

                        <h3>

                            <?= $totalInvoices['total']; ?>

                        </h3>

                    </div>

                </div>

            </div>

            <div class="col-lg-3 col-md-6 mb-4">

                <div class="card shadow border-0 bg-info text-white">

                    <div class="card-body">

                        <h6>Products Sold</h6>

                        <h3>

                            <?= $productsSold['total']; ?>

                        </h3>

                    </div>

                </div>

            </div>

        </div>
        <!-- Tables -->
        <div class="row mt-4">

            <!-- Top Selling Products -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow border-0 h-100">

                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">🏆 Top Selling Products</h5>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">

                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th class="text-center">Qty Sold</th>
                                        <th class="text-end">Revenue</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php if (mysqli_num_rows($topProducts) > 0) { ?>

                                        <?php while ($row = mysqli_fetch_assoc($topProducts)) { ?>

                                            <tr>
                                                <td><?= htmlspecialchars($row['product_name']); ?></td>

                                                <td class="text-center">
                                                    <?= $row['qty_sold']; ?>
                                                </td>

                                                <td class="text-end">
                                                    ₹<?= number_format($row['revenue'], 2); ?>
                                                </td>
                                            </tr>

                                        <?php } ?>

                                    <?php } else { ?>

                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">
                                                No Sales Available
                                            </td>
                                        </tr>

                                    <?php } ?>
                                </tbody>
                            </table>

                        </div>
                    </div>

                </div>
            </div>

            <!-- Top Customers -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow border-0 h-100">

                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">👑 Top Customers</h5>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">

                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Customer</th>
                                        <th class="text-center">Bills</th>
                                        <th class="text-end">Spent</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php if (mysqli_num_rows($topCustomers) > 0) { ?>

                                        <?php while ($row = mysqli_fetch_assoc($topCustomers)) { ?>

                                            <tr>
                                                <td><?= htmlspecialchars($row['customer_name']); ?></td>

                                                <td class="text-center">
                                                    <?= $row['total_bills']; ?>
                                                </td>

                                                <td class="text-end">
                                                    ₹<?= number_format($row['total_spent'], 2); ?>
                                                </td>
                                            </tr>

                                        <?php } ?>

                                    <?php } else { ?>

                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">
                                                No Customers Found
                                            </td>
                                        </tr>

                                    <?php } ?>
                                </tbody>
                            </table>

                        </div>
                    </div>

                </div>
            </div>

            <div class="row mt-4">

                <div class="col-12">

                    <div class="card shadow border-0">

                        <div class="card-header bg-danger text-white">

                            <h5 class="mb-0">

                                ⚠️ Low Stock Products

                            </h5>

                        </div>

                        <div class="card-body">

                            <div class="table-responsive">

                                <table class="table table-hover">

                                    <thead>

                                        <tr>

                                            <th>Product Code</th>

                                            <th>Product Name</th>

                                            <th class="text-center">Stock</th>

                                            <th class="text-center">Status</th>

                                        </tr>

                                    </thead>

                                    <tbody>

                                        <?php

                                        if (mysqli_num_rows($lowStock) > 0) {

                                            while ($row = mysqli_fetch_assoc($lowStock)) {

                                                $status = ($row['stock'] <= 2)
                                                    ? '<span class="badge bg-danger">Critical</span>'
                                                    : '<span class="badge bg-warning text-dark">Low</span>';

                                        ?>

                                                <tr>

                                                    <td><?= htmlspecialchars($row['product_code']); ?></td>

                                                    <td><?= htmlspecialchars($row['product_name']); ?></td>

                                                    <td class="text-center">

                                                        <?= $row['stock']; ?>

                                                    </td>

                                                    <td class="text-center">

                                                        <?= $status; ?>

                                                    </td>

                                                </tr>

                                            <?php

                                            }
                                        } else {

                                            ?>

                                            <tr>

                                                <td colspan="4" class="text-center text-muted">

                                                    No Low Stock Products

                                                </td>

                                            </tr>

                                        <?php } ?>

                                    </tbody>

                                </table>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

            <div class="row mt-4">

                <div class="col-12">

                    <div class="card shadow border-0">

                        <div class="card-header bg-dark text-white">

                            <h5 class="mb-0">

                                🧾 Recent Sales

                            </h5>

                        </div>

                        <div class="card-body">

                            <div class="table-responsive">

                                <table class="table table-hover">

                                    <thead>

                                        <tr>

                                            <th>Invoice</th>

                                            <th>Customer</th>

                                            <th>Date</th>

                                            <th class="text-end">Amount</th>

                                            <th class="text-center">Action</th>

                                        </tr>

                                    </thead>

                                    <tbody>

                                        <?php

                                        if (mysqli_num_rows($recentSales) > 0) {

                                            while ($row = mysqli_fetch_assoc($recentSales)) {

                                        ?>

                                                <tr>

                                                    <td>

                                                        <?= htmlspecialchars($row['invoice_no']); ?>

                                                    </td>

                                                    <td>

                                                        <?= htmlspecialchars($row['customer_name']); ?>

                                                    </td>

                                                    <td>

                                                        <?= date("d-m-Y", strtotime($row['invoice_date'])); ?>

                                                    </td>

                                                    <td class="text-end">

                                                        ₹<?= number_format($row['grand_total'], 2); ?>

                                                    </td>

                                                    <td class="text-center">

                                                        <a href="invoice.php?id=<?= $row['id']; ?>"
                                                            class="btn btn-sm btn-primary">

                                                            <i class="bi bi-eye"></i>

                                                        </a>

                                                    </td>

                                                </tr>

                                            <?php

                                            }
                                        } else {

                                            ?>

                                            <tr>

                                                <td colspan="5" class="text-center text-muted">

                                                    No Sales Available

                                                </td>

                                            </tr>

                                        <?php } ?>

                                    </tbody>

                                </table>

                            </div>

                        </div>

                    </div>

                </div>

            </div>
        </div>

        <?php include "../includes/footer.php"; ?>