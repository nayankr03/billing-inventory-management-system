<?php
require_once "../includes/config.php";
require_once "../includes/auth.php";

staffOnly();

/* ==========================
   Get Logged-in User
========================== */

$user_id = $_SESSION['user_id'];

$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id=?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    header("Location: dashboard.php");
    exit();
}

$user = mysqli_fetch_assoc($result);

/* ==========================
   Update Profile
========================== */

if (isset($_POST['update_profile'])) {

    $full_name = trim($_POST['full_name']);
    $username  = trim($_POST['username']);
    $email     = trim($_POST['email']);

    $check = mysqli_prepare(
        $conn,
        "SELECT id FROM users
         WHERE (username=? OR email=?)
         AND id<>?"
    );

    mysqli_stmt_bind_param(
        $check,
        "ssi",
        $username,
        $email,
        $user_id
    );

    mysqli_stmt_execute($check);
    mysqli_stmt_store_result($check);

    if (mysqli_stmt_num_rows($check) > 0) {

        $_SESSION['error'] =
            "Username or Email already exists.";
    } else {

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE users
             SET full_name=?,
                 username=?,
                 email=?
             WHERE id=?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "sssi",
            $full_name,
            $username,
            $email,
            $user_id
        );

        if (mysqli_stmt_execute($stmt)) {

            $_SESSION['success'] =
                "Profile updated successfully.";

            $_SESSION['username']  = $username;
            $_SESSION['full_name'] = $full_name;

            header("Location: profile.php");
            exit();
        } else {

            $_SESSION['error'] =
                "Unable to update profile.";
        }
    }
}

/* ==========================
   Change Password
========================== */

if (isset($_POST['change_password'])) {

    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if (!password_verify($current, $user['password'])) {

        $_SESSION['error'] = "Current password is incorrect.";
    } elseif ($new != $confirm) {

        $_SESSION['error'] = "New passwords do not match.";
    } else {

        $hash = password_hash($new, PASSWORD_DEFAULT);

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE users SET password=? WHERE id=?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "si",
            $hash,
            $user_id
        );

        if (mysqli_stmt_execute($stmt)) {

            $_SESSION['success'] = "Password changed successfully.";
        } else {

            $_SESSION['error'] = "Unable to change password.";
        }
    }

    header("Location: profile.php");
    exit();
}

include "../includes/header.php";
include "../includes/sidebar.php";
?>

<div class="main-content">
    <div class="container-fluid">

        <?php if (isset($_SESSION['success'])): ?>

            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: '<?= $_SESSION['success']; ?>'
                });
            </script>

        <?php unset($_SESSION['success']);
        endif; ?>

        <?php if (isset($_SESSION['error'])): ?>

            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: '<?= $_SESSION['error']; ?>'
                });
            </script>

        <?php unset($_SESSION['error']);
        endif; ?>


        <div class="row">

            <div class="col-lg-8 mx-auto">

                <!-- Profile Card -->
                <div class="card shadow border-0">

                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="bi bi-person-circle"></i>
                            My Profile
                        </h4>
                    </div>

                    <div class="card-body">

                        <form method="POST">

                            <div class="row">

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Full Name</label>
                                    <input
                                        type="text"
                                        name="full_name"
                                        class="form-control"
                                        value="<?= htmlspecialchars($user['full_name']); ?>"
                                        required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Username</label>
                                    <input
                                        type="text"
                                        name="username"
                                        class="form-control"
                                        value="<?= htmlspecialchars($user['username']); ?>"
                                        required>
                                </div>

                                <div class="col-12 mb-3">
                                    <label class="form-label">Email</label>
                                    <input
                                        type="email"
                                        name="email"
                                        class="form-control"
                                        value="<?= htmlspecialchars($user['email']); ?>"
                                        required>
                                </div>

                            </div>

                            <button
                                type="submit"
                                name="update_profile"
                                class="btn btn-primary">
                                <i class="bi bi-check-circle"></i>
                                Update Profile
                            </button>

                        </form>

                    </div>

                </div>

                <!-- Change Password -->
                <div class="card shadow border-0 mt-4">

                    <div class="card-header bg-danger text-white">
                        <h4 class="mb-0">
                            <i class="bi bi-key-fill"></i>
                            Change Password
                        </h4>
                    </div>

                    <div class="card-body">

                        <form method="POST">

                            <div class="row">

                                <div class="col-12 mb-3">
                                    <label class="form-label">Current Password</label>
                                    <input
                                        type="password"
                                        name="current_password"
                                        class="form-control"
                                        required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">New Password</label>
                                    <input
                                        type="password"
                                        name="new_password"
                                        class="form-control"
                                        required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Confirm Password</label>
                                    <input
                                        type="password"
                                        name="confirm_password"
                                        class="form-control"
                                        required>
                                </div>

                            </div>

                            <button
                                type="submit"
                                name="change_password"
                                class="btn btn-danger">
                                <i class="bi bi-shield-lock-fill"></i>
                                Change Password
                            </button>

                        </form>

                    </div>

                </div>

            </div>

        </div>

        <?php include "../includes/footer.php"; ?>