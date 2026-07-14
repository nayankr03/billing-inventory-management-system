<?php
require_once "../includes/config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

include "../includes/header.php";
include "../includes/sidebar.php";

// Search
if (isset($_GET['search']) && $_GET['search'] != "") {

    $search = mysqli_real_escape_string($conn, $_GET['search']);

    $sql = "SELECT sales.*, customers.customer_name
            FROM sales
            INNER JOIN customers ON sales.customer_id = customers.id
            WHERE sales.invoice_no LIKE '%$search%'
            OR customers.customer_name LIKE '%$search%'
            ORDER BY sales.id DESC";
} else {

    $sql = "SELECT sales.*, customers.customer_name
            FROM sales
            INNER JOIN customers ON sales.customer_id = customers.id
            ORDER BY sales.id DESC";
}

$result = mysqli_query($conn, $sql);
?>

<div class="main-content">

    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-4">

            <h2 class="fw-bold">
                <i class="bi bi-receipt"></i> Sales
            </h2>

            <a href="new_sale.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> New Sale
            </a>

        </div>

        <div class="row mb-3">

            <div class="col-md-6">

                <form method="GET">

                    <div class="input-group">

                        <input
                            type="text"
                            name="search"
                            class="form-control"
                            placeholder="Search Invoice or Customer..."
                            value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">

                        <button class="btn btn-primary">

                            <i class="bi bi-search"></i>

                            Search

                        </button>

                        <a href="sales.php" class="btn btn-secondary">

                            Reset

                        </a>

                    </div>

                </form>

            </div>

        </div>

        <div class="card shadow">

            <div class="card-body">

                <div class="table-responsive">

                    <table class="table table-hover table-bordered align-middle">

                        <thead class="table-primary">

                            <tr>

                                <th>ID</th>

                                <th>Invoice No</th>

                                <th>Date</th>

                                <th>Customer</th>

                                <th>Payment</th>

                                <th>Grand Total</th>

                                <th width="150">Action</th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php

                            if (mysqli_num_rows($result) > 0) {

                                while ($row = mysqli_fetch_assoc($result)) {

                            ?>

                                    <tr>

                                        <td><?= $row['id']; ?></td>

                                        <td>

                                            <span class="badge bg-dark">

                                                <?= htmlspecialchars($row['invoice_no']); ?>

                                            </span>

                                        </td>

                                        <td>

                                            <?= date("d-m-Y", strtotime($row['invoice_date'])); ?>

                                        </td>

                                        <td>

                                            <?= htmlspecialchars($row['customer_name']); ?>

                                        </td>

                                        <td>

                                            <span class="badge bg-success">

                                                <?= htmlspecialchars($row['payment_method']); ?>

                                            </span>

                                        </td>

                                        <td>

                                            <strong>

                                                ₹<?= number_format($row['grand_total'], 2); ?>

                                            </strong>

                                        </td>

                                        <td>

                                            <a href="view_sale.php?id=<?= $row['id']; ?>"
                                                class="btn btn-info btn-sm">

                                                <i class="bi bi-eye"></i>

                                            </a>

                                            <a href="delete_sale.php?id=<?= $row['id']; ?>"
                                                class="btn btn-danger btn-sm"
                                                onclick="return confirm('Delete this sale?');">

                                                <i class="bi bi-trash"></i>

                                            </a>

                                        </td>

                                    </tr>

                                <?php

                                }
                            } else {

                                ?>

                                <tr>

                                    <td colspan="7" class="text-center text-muted">

                                        No Sales Found

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

<?php include "../includes/footer.php"; ?>