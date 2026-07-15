<?php
require_once "../includes/config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: sales.php");
    exit();
}

$sale_id = intval($_GET['id']);

mysqli_begin_transaction($conn);

try {

    // ==========================
    // GET ALL SALE ITEMS
    // ==========================

    $sql = "SELECT product_id, quantity
            FROM sale_items
            WHERE sale_id = ?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "i", $sale_id);

    mysqli_stmt_execute($stmt);

    $items = mysqli_stmt_get_result($stmt);

    // ==========================
    // RESTORE STOCK
    // ==========================

    while ($item = mysqli_fetch_assoc($items)) {

        $sql = "UPDATE products
                SET stock = stock + ?
                WHERE id = ?";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "ii",
            $item['quantity'],
            $item['product_id']
        );

        mysqli_stmt_execute($stmt);
    }

    // ==========================
    // DELETE SALE ITEMS
    // ==========================

    $sql = "DELETE FROM sale_items
            WHERE sale_id = ?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "i", $sale_id);

    mysqli_stmt_execute($stmt);

    // ==========================
    // DELETE SALE
    // ==========================

    $sql = "DELETE FROM sales
            WHERE id = ?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "i", $sale_id);

    mysqli_stmt_execute($stmt);

    mysqli_commit($conn);

    $_SESSION['success'] = "Sale deleted successfully.";

} catch (Exception $e) {

    mysqli_rollback($conn);

    $_SESSION['error'] = "Failed to delete sale.";

}

header("Location: sales.php");
exit();