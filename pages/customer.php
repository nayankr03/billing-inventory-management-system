<?php
require_once "../includes/config.php";
require_once "../includes/auth.php";

staffOnly();

include "../includes/header.php";
include "../includes/sidebar.php";

if (isset($_GET['search']) && trim($_GET['search']) != '') {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $sql = "SELECT * FROM customers WHERE customer_code LIKE '%$search%' OR customer_name LIKE '%$search%' OR mobile LIKE '%$search%' OR city LIKE '%$search%' ORDER BY id DESC";
} else {
    $sql = "SELECT * FROM customers ORDER BY id DESC";
}
$result = mysqli_query($conn, $sql);
?>
<div class="main-content">
    <div class="container-fluid">
        <?php if (isset($_SESSION['success'])) { ?>
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: '<?= $_SESSION["success"] ?>'
                });
            </script>
        <?php unset($_SESSION['success']);
        } ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold"><i class="bi bi-people"></i> Customers</h2>
            <a href="add_customer.php" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Add Customer</a>
        </div>

        <form method="GET" class="mb-3">
            <div class="input-group" style="max-width:600px;">
                <input type="text" name="search" class="form-control" placeholder="Search Customer..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button class="btn btn-primary"><i class="bi bi-search"></i> Search</button>
                <a href="customer.php" class="btn btn-secondary">Reset</a>
            </div>
        </form>

        <div class="card shadow">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle">
                        <thead class="table-primary">
                            <tr>
                                <th>ID</th>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Mobile</th>
                                <th>Email</th>
                                <th>City</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) { ?>
                                    <tr>
                                        <td><?= $row['id'] ?></td>
                                        <td><span class="badge bg-dark"><?= htmlspecialchars($row['customer_code']) ?></span></td>
                                        <td><strong><?= htmlspecialchars($row['customer_name']) ?></strong></td>
                                        <td><?= htmlspecialchars($row['mobile']) ?></td>
                                        <td><?= $row['email'] ? htmlspecialchars($row['email']) : '-' ?></td>
                                        <td><?= $row['city'] ? htmlspecialchars($row['city']) : '-' ?></td>
                                        <td><?php if ($row['status'] == 'Active') { ?><span class="badge bg-success">Active</span><?php } else { ?><span class="badge bg-secondary">Inactive</span><?php } ?></td>
                                        <td>
                                            <a href="view_customer.php?id=<?= $row['id'] ?>" class="btn btn-info btn-sm text-white"><i class="bi bi-eye"></i></a>
                                            <a href="edit_customer.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm"><i class="bi bi-pencil-square"></i></a>
                                            <a href="#" class="btn btn-danger btn-sm deleteBtn" data-url="delete_customer.php?id=<?= $row['id'] ?>"><i class="bi bi-trash"></i></a>
                                        </td>
                                    </tr>
                                <?php }
                            } else { ?>
                                <tr>
                                    <td colspan="8" class="text-center">No Customers Found</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.deleteBtn').forEach(btn => {
        btn.onclick = function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Delete Customer?',
                icon: 'warning',
                showCancelButton: true
            }).then(r => {
                if (r.isConfirmed) {
                    location = this.dataset.url;
                }
            });
        };
    });
</script>

<?php include "../includes/footer.php"; ?>