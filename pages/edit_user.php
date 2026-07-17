<?php
require_once "../includes/config.php";
require_once "../includes/auth.php";

adminOnly();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: users.php");
    exit();
}

$id = (int)$_GET['id'];

$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id=?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    header("Location: users.php");
    exit();
}

$user = mysqli_fetch_assoc($result);

if (isset($_POST['update'])) {

    $full_name = trim($_POST['full_name']);
    $username  = trim($_POST['username']);
    $email     = trim($_POST['email']);
    $role      = $_POST['role'];
    $status    = $_POST['status'];

    $check = mysqli_prepare($conn, "SELECT id FROM users WHERE (username=? OR email=?) AND id<>?");
    mysqli_stmt_bind_param($check, "ssi", $username, $email, $id);
    mysqli_stmt_execute($check);
    mysqli_stmt_store_result($check);

    if (mysqli_stmt_num_rows($check) > 0) {
        $error = "Username or Email already exists.";
    } else {

        if (!empty($_POST['password'])) {
            if ($_POST['password'] != $_POST['confirm_password']) {
                $error = "Passwords do not match.";
            } else {
                $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $sql = "UPDATE users SET full_name=?,username=?,email=?,password=?,role=?,status=? WHERE id=?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "ssssssi", $full_name, $username, $email, $hash, $role, $status, $id);
            }
        } else {
            $sql = "UPDATE users SET full_name=?,username=?,email=?,role=?,status=? WHERE id=?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sssssi", $full_name, $username, $email, $role, $status, $id);
        }

        if (!isset($error) && mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "User updated successfully.";
            header("Location: users.php");
            exit();
        } elseif (!isset($error)) {
            $error = "Unable to update user.";
        }
    }
}

include "../includes/header.php";
include "../includes/sidebar.php";
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="bi bi-pencil-square"></i> Edit User</h4>
            </div>

            <div class="card-body">

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= $error; ?></div>
                <?php endif; ?>

                <form method="POST">

                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label>Full Name</label>
                            <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Username</label>
                            <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Role</label>
                            <select name="role" class="form-select">
                                <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                <option value="staff" <?= $user['role'] == 'staff' ? 'selected' : ''; ?>>Staff</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>New Password <small>(Leave blank to keep current)</small></label>
                            <input type="password" name="password" class="form-control">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Confirm Password</label>
                            <input type="password" name="confirm_password" class="form-control">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Status</label>
                            <select name="status" class="form-select">
                                <option value="Active" <?= $user['status'] == 'Active' ? 'selected' : ''; ?>>Active</option>
                                <option value="Inactive" <?= $user['status'] == 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>

                    </div>

                    <button type="submit" name="update" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Update User
                    </button>

                    <a href="users.php" class="btn btn-secondary">Cancel</a>

                </form>

            </div>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>