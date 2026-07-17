<?php
require_once "../includes/config.php";
require_once "../includes/auth.php";

staffOnly();

include "../includes/header.php";
include "../includes/sidebar.php";

// Search
$where = [];

if (!empty($_GET['search'])) {

    $search = mysqli_real_escape_string($conn, $_GET['search']);

    $where[] = "(sales.invoice_no LIKE '%$search%'
            OR customers.customer_name LIKE '%$search%'
            OR customers.mobile LIKE '%$search%')";
}

if (!empty($_GET['from_date'])) {

    $from = mysqli_real_escape_string($conn, $_GET['from_date']);

    $where[] = "sales.invoice_date >= '$from'";
}

if (!empty($_GET['to_date'])) {

    $to = mysqli_real_escape_string($conn, $_GET['to_date']);

    $where[] = "sales.invoice_date <= '$to'";
}

$sql = "SELECT
            sales.*,
            customers.customer_name,
            customers.mobile
        FROM sales
        INNER JOIN customers
        ON sales.customer_id = customers.id";

if (!empty($where)) {

    $sql .= " WHERE " . implode(" AND ", $where);
}
/* -------------------------
   PAGINATION
--------------------------*/

$limit = 10;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

if ($page < 1) {
    $page = 1;
}

$offset = ($page - 1) * $limit;

$sql .= " ORDER BY sales.id DESC
          LIMIT $offset, $limit";

$result = mysqli_query($conn, $sql);

$countSql = "SELECT COUNT(*) AS total
             FROM sales
             INNER JOIN customers
             ON sales.customer_id = customers.id";

if (!empty($where)) {

    $countSql .= " WHERE " . implode(" AND ", $where);
}

$countResult = mysqli_query($conn, $countSql);

$totalRows = mysqli_fetch_assoc($countResult)['total'];

$totalPages = ceil($totalRows / $limit);

/* -------------------------
   KEEP SEARCH & FILTERS
--------------------------*/

$queryString = "";

if (!empty($_GET['search'])) {
    $queryString .= "&search=" . urlencode($_GET['search']);
}

if (!empty($_GET['from_date'])) {
    $queryString .= "&from_date=" . urlencode($_GET['from_date']);
}

if (!empty($_GET['to_date'])) {
    $queryString .= "&to_date=" . urlencode($_GET['to_date']);
}

/* -------------------------
   SALES STATISTICS
--------------------------*/

// Total Sales
$totalSales = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM sales")
)['total'];

// Today's Sales
$todaySales = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM sales
         WHERE invoice_date = CURDATE()"
    )
)['total'];

// Total Revenue
$totalRevenue = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT SUM(grand_total) AS revenue
         FROM sales"
    )
)['revenue'];

if ($totalRevenue == NULL) {
    $totalRevenue = 0;
}

// Average Bill Value
$averageBill = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT AVG(grand_total) AS avg_bill
         FROM sales"
    )
)['avg_bill'];

if ($averageBill == NULL) {
    $averageBill = 0;
}
?>

<div class="main-content">

    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-4">

            <h2 class="fw-bold">
                <i class="bi bi-receipt"></i> Sales
            </h2>

            <a href="new_sale.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> New Bill
            </a>

        </div>
        <div class="row mb-4">

            <div class="col-lg-3 col-md-6 mb-3">

                <div class="card border-0 shadow-sm bg-primary text-white">

                    <div class="card-body">

                        <h6>Total Sales</h6>

                        <h2><?= $totalSales; ?></h2>

                    </div>

                </div>

            </div>

            <div class="col-lg-3 col-md-6 mb-3">

                <div class="card border-0 shadow-sm bg-success text-white">

                    <div class="card-body">

                        <h6>Today's Sales</h6>

                        <h2><?= $todaySales; ?></h2>

                    </div>

                </div>

            </div>

            <div class="col-lg-3 col-md-6 mb-3">

                <div class="card border-0 shadow-sm bg-warning text-dark">

                    <div class="card-body">

                        <h6>Total Revenue</h6>

                        <h4>₹<?= number_format($totalRevenue, 2); ?></h4>

                    </div>

                </div>

            </div>

            <div class="col-lg-3 col-md-6 mb-3">

                <div class="card border-0 shadow-sm bg-info text-white">

                    <div class="card-body">

                        <h6>Average Bill</h6>

                        <h4>₹<?= number_format($averageBill, 2); ?></h4>

                    </div>

                </div>

            </div>

        </div>
        <div class="row mb-3">

            <div class="col-md-6">

                <form method="GET">

                    <div class="row g-2">

                        <div class="col-md-4">

                            <input
                                type="text"
                                name="search"
                                class="form-control"
                                placeholder="Search Invoice, Customer or Mobile..."
                                value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">

                        </div>

                        <div class="col-md-2">

                            <input
                                type="date"
                                name="from_date"
                                class="form-control"
                                value="<?= $_GET['from_date'] ?? '' ?>">

                        </div>

                        <div class="col-md-2">

                            <input
                                type="date"
                                name="to_date"
                                class="form-control"
                                value="<?= $_GET['to_date'] ?? '' ?>">

                        </div>

                        <div class="col-md-2">

                            <button class="btn btn-primary w-100">

                                <i class="bi bi-search"></i>

                                Filter

                            </button>

                        </div>

                        <div class="col-md-2">

                            <a href="sales.php" class="btn btn-secondary w-100">

                                Reset

                            </a>

                        </div>

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
                                <th>Mobile</th>

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
                                            <?= htmlspecialchars($row['mobile']); ?>
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

                                            <a href="invoice.php?id=<?= $row['id']; ?>"
                                                class="btn btn-info btn-sm"
                                                title="View Invoice">

                                                <i class="bi bi-eye"></i>

                                            </a>

                                            <a href="invoice.php?id=<?= $row['id']; ?>"
                                                target="_blank"
                                                class="btn btn-success btn-sm"
                                                title="Print Invoice">

                                                <i class="bi bi-printer"></i>

                                            </a>

                                            </a>

                                            <?php if (isAdmin()): ?>

                                                <a href="delete_sale.php?id=<?= $row['id']; ?>"
                                                    class="btn btn-danger btn-sm">

                                                    <i class="bi bi-trash"></i>

                                                </a>

                                            <?php endif; ?>
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

                    <nav class="mt-3">

                        <ul class="pagination justify-content-end">

                            <!-- Previous -->

                            <li class="page-item <?= ($page <= 1) ? 'disabled' : ''; ?>">

                                <a class="page-link"
                                    href="?page=<?= $page - 1; ?><?= $queryString; ?>">

                                    Previous

                                </a>

                            </li>

                            <?php

                            $start = max(1, $page - 2);
                            $end = min($totalPages, $page + 2);

                            if ($start > 1) {

                            ?>

                                <li class="page-item">

                                    <a class="page-link"
                                        href="?page=1<?= $queryString; ?>">

                                        1

                                    </a>

                                </li>

                                <?php

                                if ($start > 2) {

                                ?>

                                    <li class="page-item disabled">

                                        <span class="page-link">...</span>

                                    </li>

                                <?php

                                }
                            }

                            for ($i = $start; $i <= $end; $i++) {

                                ?>

                                <li class="page-item <?= ($page == $i) ? 'active' : ''; ?>">

                                    <a class="page-link"
                                        href="?page=<?= $i; ?><?= $queryString; ?>">

                                        <?= $i; ?>

                                    </a>

                                </li>

                                <?php

                            }

                            if ($end < $totalPages) {

                                if ($end < $totalPages - 1) {

                                ?>

                                    <li class="page-item disabled">

                                        <span class="page-link">...</span>

                                    </li>

                                <?php

                                }

                                ?>

                                <li class="page-item">

                                    <a class="page-link"
                                        href="?page=<?= $totalPages; ?><?= $queryString; ?>">

                                        <?= $totalPages; ?>

                                    </a>

                                </li>

                            <?php

                            }

                            ?>

                            <!-- Next -->

                            <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : ''; ?>">

                                <a class="page-link"
                                    href="?page=<?= $page + 1; ?><?= $queryString; ?>">

                                    Next

                                </a>

                            </li>

                        </ul>

                    </nav>



                </div>

            </div>

        </div>

    </div>

</div>
<script>
    document.querySelectorAll(".deleteSale").forEach(button => {

        button.addEventListener("click", function(e) {

            e.preventDefault();

            let saleId = this.dataset.id;

            Swal.fire({

                title: "Delete Sale?",
                text: "This will restore product stock and permanently delete the invoice.",
                icon: "warning",

                showCancelButton: true,

                confirmButtonColor: "#d33",

                cancelButtonColor: "#3085d6",

                confirmButtonText: "Yes, Delete",

                cancelButtonText: "Cancel"

            }).then((result) => {

                if (result.isConfirmed) {

                    window.location =
                        "delete_sale.php?id=" + saleId;

                }

            });

        });

    });
</script>


<?php

if (isset($_SESSION['success'])) {
?>

    <script>
        Swal.fire({

            icon: "success",

            title: "Success",

            text: "<?= $_SESSION['success']; ?>"

        });
    </script>

<?php

    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
?>

    <script>
        Swal.fire({

            icon: "error",

            title: "Error",

            text: "<?= $_SESSION['error']; ?>"

        });
    </script>

<?php

    unset($_SESSION['error']);
}

?>

<?php include "../includes/footer.php"; ?>