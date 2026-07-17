<?php
require_once "../includes/config.php";
require_once "../includes/auth.php";

adminOnly();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid User.";
    header("Location: users.php");
    exit();
}

$id = (int) $_GET['id'];

/* ==========================
   Prevent Self Delete
========================== */

if ($id == $_SESSION['user_id']) {
    $_SESSION['error'] = "You cannot delete your own account.";
    header("Location: users.php");
    exit();
}

/* ==========================
   Check User Exists
========================== */

$stmt = mysqli_prepare($conn, "SELECT role FROM users WHERE id=?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    $_SESSION['error'] = "User not found.";
    header("Location: users.php");
    exit();
}

$user = mysqli_fetch_assoc($result);

/* ==========================
   Prevent Deleting Last Admin
========================== */

if ($user['role'] == "admin") {

    $count = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role='admin'");
    $admin = mysqli_fetch_assoc($count);

    if ($admin['total'] <= 1) {

        $_SESSION['error'] = "You cannot delete the last Admin.";

        header("Location: users.php");
        exit();
    }
}

/* ==========================
   Delete User
========================== */

$stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id=?");
mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {

    $_SESSION['success'] = "User deleted successfully.";

} else {

    $_SESSION['error'] = "Unable to delete user.";

}

header("Location: users.php");
exit();