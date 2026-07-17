<?php
require_once "../includes/config.php";
require_once "../includes/auth.php";

adminOnly();

if (isset($_POST['save'])) {

    $full_name = trim($_POST['full_name']);
    $username  = trim($_POST['username']);
    $email     = trim($_POST['email']);
    $password  = $_POST['password'];
    $confirm   = $_POST['confirm_password'];
    $role      = $_POST['role'];
    $status    = $_POST['status'];

    if (empty($full_name) || empty($username) || empty($email) || empty($password)) {
        $error = "All required fields are mandatory.";
    } elseif ($password != $confirm) {
        $error = "Passwords do not match.";
    } else {

        $check = mysqli_prepare($conn, "SELECT id FROM users WHERE username=? OR email=?");
        mysqli_stmt_bind_param($check, "ss", $username, $email);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);

        if (mysqli_stmt_num_rows($check) > 0) {
            $error = "Username or Email already exists.";
        } else {

            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = mysqli_prepare($conn, "INSERT INTO users(full_name,username,email,password,role,status) VALUES(?,?,?,?,?,?)");
            mysqli_stmt_bind_param($stmt, "ssssss", $full_name, $username, $email, $hash, $role, $status);

            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success'] = "User added successfully.";
                header("Location: users.php");
                exit();
            } else {
                $error = "Unable to add user.";
            }
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
                <h4 class="mb-0"><i class="bi bi-person-plus-fill"></i> Add User</h4>
            </div>

            <div class="card-body">

                <?php if (isset($error)) { ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php } ?>

                <form method="POST">

                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label>Full Name</label>
                            <input type="text" name="full_name" class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Username</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Role</label>
                            <select name="role" class="form-select">
                                <option value="admin">Admin</option>
                                <option value="staff" selected>Staff</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Confirm Password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Status</label>
                            <select name="status" class="form-select">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>

                    </div>

                    <button type="submit" name="save" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Save User
                    </button>

                    <a href="users.php" class="btn btn-secondary">Cancel</a>

                </form>

            </div>
        </div>

    </div>
</div>

<?php include "../includes/footer.php"; ?>