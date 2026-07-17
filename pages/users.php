<?php
require_once "../includes/config.php";
require_once "../includes/auth.php";

adminOnly();

include "../includes/header.php";
include "../includes/sidebar.php";

if(isset($_SESSION['success'])){
?>
<script>
Swal.fire({
    icon:'success',
    title:'Success',
    text:'<?= $_SESSION['success']; ?>'
});
</script>
<?php
unset($_SESSION['success']);
}

if(isset($_SESSION['error'])){
?>
<script>
Swal.fire({
    icon:'error',
    title:'Error',
    text:'<?= $_SESSION['error']; ?>'
});
</script>
<?php
unset($_SESSION['error']);
}

/* ==========================
   Search + Pagination
========================== */

$limit = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

$search = trim($_GET['search'] ?? '');

$where = "";
$params = [];
$types = "";

if ($search !== "") {
    $where = "WHERE full_name LIKE ? OR username LIKE ? OR email LIKE ?";
    $like = "%{$search}%";
    $params = [$like, $like, $like];
    $types = "sss";
}

/* Total Records */
$countSql = "SELECT COUNT(*) AS total FROM users $where";
$stmt = mysqli_prepare($conn, $countSql);

if ($where) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$countResult = mysqli_stmt_get_result($stmt);
$totalRows = mysqli_fetch_assoc($countResult)['total'];
$totalPages = max(1, ceil($totalRows / $limit));

/* Users */
$sql = "SELECT * FROM users $where ORDER BY id DESC LIMIT ?, ?";
$stmt = mysqli_prepare($conn, $sql);

if ($where) {
    $bindTypes = $types . "ii";
    $bindParams = array_merge($params, [$offset, $limit]);
    mysqli_stmt_bind_param($stmt, $bindTypes, ...$bindParams);
} else {
    mysqli_stmt_bind_param($stmt, "ii", $offset, $limit);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<div class="main-content">
    <div class="container-fluid">

        <div class="card shadow border-0">

            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="bi bi-people-fill"></i> Users</h4>

                <a href="add_user.php" class="btn btn-light btn-sm">
                    <i class="bi bi-plus-circle"></i> Add User
                </a>
            </div>
            <div class="alert alert-light border mb-3">

                <strong>Total Users :</strong>

                <?= $totalRows; ?>

            </div>

            <div class="card-body">

                <form method="GET" class="row mb-3">
                    <div class="col-md-4">
                        <input
                            type="text"
                            name="search"
                            value="<?= htmlspecialchars($search) ?>"
                            class="form-control"
                            placeholder="Search by Name, Username or Email">
                    </div>

                    <div class="col-md-2">
                        <button class="btn btn-primary">
                            <i class="bi bi-search"></i> Search
                        </button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">

                        <thead class="table-primary text-center">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th width="140">Action</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php if (mysqli_num_rows($result) > 0): ?>

                                <?php $i = $offset + 1;
                                while ($row = mysqli_fetch_assoc($result)): ?>

                                    <tr>

                                        <td><?= $i++; ?></td>

                                        <td><?= htmlspecialchars($row['full_name']); ?></td>

                                        <td><?= htmlspecialchars($row['username']); ?></td>

                                        <td><?= htmlspecialchars($row['email']); ?></td>

                                        <td class="text-center">
                                            <?php
                                            $roleColors = [
                                                'admin' => 'danger',
                                                'staff' => 'primary'
                                            ];
                                            ?>

                                            <span class="badge bg-<?= $roleColors[$row['role']] ?? 'secondary'; ?>">
                                                <?= ucfirst($row['role']); ?>
                                            </span>
                                        </td>

                                        <td class="text-center">
                                            <?php if (($row['status'] ?? 'Active') == "Active"): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inactive</span>
                                            <?php endif; ?>
                                        </td>

                                        <td><?= date("d-m-Y", strtotime($row['created_at'])); ?></td>

                                        <td class="text-center">
                                            <a href="edit_user.php?id=<?= $row['id']; ?>" class="btn btn-warning btn-sm">
                                                <i class="bi bi-pencil"></i>
                                            </a>

                                            <a href="#"
                                                class="btn btn-danger btn-sm deleteUser"
                                                data-id="<?= $row['id']; ?>"
                                                data-name="<?= htmlspecialchars($row['full_name']); ?>">

                                                <i class="bi bi-trash"></i>

                                            </a>
                                        </td>

                                    </tr>

                                <?php endwhile; ?>

                            <?php else: ?>

                                <tr>
                                    <td colspan="8" class="text-center text-muted">
                                        <i class="bi bi-people"></i>

                                        No Users Found.
                                    </td>
                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>
                </div>

                <nav>
                    <ul class="pagination">
                        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                            <li class="page-item <?= $p == $page ? 'active' : ''; ?>">
                                <a class="page-link"
                                    href="?page=<?= $p ?>&search=<?= urlencode($search) ?>">
                                    <?= $p ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>

            </div>

        </div>

    </div>
</div>
<script>
document.querySelectorAll(".deleteUser").forEach(button => {

    button.addEventListener("click", function(e){

        e.preventDefault();

        let id = this.dataset.id;
        let name = this.dataset.name;

        Swal.fire({

            title: "Delete User?",

            html: "Are you sure you want to delete <b>" + name + "</b>?",

            icon: "warning",

            showCancelButton: true,

            confirmButtonColor: "#dc3545",

            cancelButtonColor: "#6c757d",

            confirmButtonText: "Yes, Delete",

            cancelButtonText: "Cancel"

        }).then((result)=>{

            if(result.isConfirmed){

                window.location.href = "delete_user.php?id=" + id;

            }

        });

    });

});
</script>
<?php include "../includes/footer.php"; ?>