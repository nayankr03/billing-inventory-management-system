<?php

require_once "../includes/config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

include "../includes/header.php";
include "../includes/sidebar.php";

/* Products */
$product = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM products"));
$totalProducts = $product['total'] ?? 0;

/* Active Products */
$active = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM products WHERE status='Active'"));
$totalActive = $active['total'] ?? 0;

/* Inactive Products */
$inactive = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM products WHERE status='Inactive'"));
$totalInactive = $inactive['total'] ?? 0;

/* Inventory Value */
$value = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(purchase_price*stock) AS total FROM products"));
$inventoryValue = $value['total'] ?? 0;

/* Customers */
$totalCustomers = 0;
$todaySales = 0;
$totalSales = 0;
$totalRevenue = 0;

if (mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'customers'"))) {
    $c = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM customers"));
    $totalCustomers = $c['total'];
}

if (mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'sales'"))) {

    $s = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM sales"));
    $totalSales = $s['total'];

    $r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(grand_total) AS total FROM sales"));
    $totalRevenue = $r['total'] ?? 0;

    $t = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM sales WHERE DATE(created_at)=CURDATE()"));
    $todaySales = $t['total'];
}

$recent = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC LIMIT 5");

?>

<div class="main-content">
    <div class="container-fluid">

        <h2 class="mb-4">Welcome, <?= htmlspecialchars($_SESSION['username']); ?> 👋</h2>

        <div class="row g-4">

            <div class="col-lg-3 col-md-6">
                <div class="card shadow">
                    <div class="card-body text-center">
                        <h5>Total Products</h5>
                        <h2><?= $totalProducts ?></h2>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card shadow">
                    <div class="card-body text-center">
                        <h5>Customers</h5>
                        <h2><?= $totalCustomers ?></h2>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card shadow">
                    <div class="card-body text-center">
                        <h5>Sales</h5>
                        <h2><?= $totalSales ?></h2>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card shadow">
                    <div class="card-body text-center">
                        <h5>Revenue</h5>
                        <h2>₹<?= number_format($totalRevenue, 2) ?></h2>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card shadow">
                    <div class="card-body text-center">
                        <h5>Active Products</h5>
                        <h2 class="text-success"><?= $totalActive ?></h2>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card shadow">
                    <div class="card-body text-center">
                        <h5>Inactive Products</h5>
                        <h2 class="text-danger"><?= $totalInactive ?></h2>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card shadow">
                    <div class="card-body text-center">
                        <h5>Inventory Value</h5>
                        <h2 class="text-primary">₹<?= number_format($inventoryValue, 2) ?></h2>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card shadow">
                    <div class="card-body text-center">
                        <h5>Today's Sales</h5>
                        <h2 class="text-info"><?= $todaySales ?></h2>
                    </div>
                </div>
            </div>

        </div>

        <div class="card shadow mt-4">
            <div class="card-header bg-primary text-white">
                Recent Products
            </div>

            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-hover align-middle">

                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Image</th>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Stock</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php if (mysqli_num_rows($recent) > 0) {
                                while ($row = mysqli_fetch_assoc($recent)) { ?>

                                    <tr>

                                        <td><?= htmlspecialchars($row['product_code']); ?></td>

                                        <td>
                                            <?php if (!empty($row['image'])) { ?>
                                                <img src="../uploads/products/<?= htmlspecialchars($row['image']); ?>" width="50" height="50" class="img-thumbnail" style="object-fit:cover;">
                                            <?php } else { ?>
                                                <span class="text-muted">No Image</span>
                                            <?php } ?>
                                        </td>

                                        <td><?= htmlspecialchars($row['product_name']); ?></td>

                                        <td><?= htmlspecialchars($row['category']); ?></td>

                                        <td>

                                            <?php

                                            if ($row['stock'] <= 5) {

                                                echo "<span class='badge bg-danger'>{$row['stock']}</span>";
                                            } elseif ($row['stock'] <= 15) {

                                                echo "<span class='badge bg-warning text-dark'>{$row['stock']}</span>";
                                            } else {

                                                echo "<span class='badge bg-success'>{$row['stock']}</span>";
                                            }

                                            ?>

                                        </td>

                                        <td>

                                            <?php

                                            if ($row['status'] == "Active") {

                                                echo "<span class='badge bg-success'>Active</span>";
                                            } else {

                                                echo "<span class='badge bg-secondary'>Inactive</span>";
                                            }

                                            ?>

                                        </td>

                                    </tr>

                                <?php }
                            } else { ?>

                                <tr>
                                    <td colspan="6" class="text-center">No Products Found</td>
                                </tr>

                            <?php } ?>

                        </tbody>

                    </table>
                </div>

            </div>
        </div>

    </div>
</div>

<?php include "../includes/footer.php"; ?>