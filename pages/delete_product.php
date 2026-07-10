<?php

require_once "../includes/config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: products.php");
    exit();
}

$id = intval($_GET['id']);

// Fetch Product
$result = mysqli_query($conn, "SELECT image FROM products WHERE id='$id'");

if (mysqli_num_rows($result) == 0) {
    header("Location: products.php");
    exit();
}

$product = mysqli_fetch_assoc($result);

// Delete Image
if (!empty($product['image'])) {

    $imagePath = "../uploads/products/" . $product['image'];
    if (file_exists($imagePath)) {
        unlink($imagePath);
    }
}

// Delete Product
if(mysqli_query($conn, "DELETE FROM products WHERE id='$id'")){

    $_SESSION['success'] = "Product deleted successfully.";

}else{

    $_SESSION['success'] = "Unable to delete product.";

}

header("Location: products.php");
exit();
