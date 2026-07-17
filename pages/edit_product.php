<?php
require_once "../includes/config.php";
require_once "../includes/auth.php";

adminOnly();

if (!isset($_GET['id'])) {
    header("Location: products.php");
    exit();
}

$id = intval($_GET['id']);

$result = mysqli_query($conn, "SELECT * FROM products WHERE id='$id'");

if (mysqli_num_rows($result) == 0) {
    header("Location: products.php");
    exit();
}

$product = mysqli_fetch_assoc($result);

if (isset($_POST['update'])) {

    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $supplier = mysqli_real_escape_string($conn, $_POST['supplier']);
    $purchase_price = mysqli_real_escape_string($conn, $_POST['purchase_price']);
    $selling_price = mysqli_real_escape_string($conn, $_POST['selling_price']);
    $stock = mysqli_real_escape_string($conn, $_POST['stock']);
    $unit = mysqli_real_escape_string($conn, $_POST['unit']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    /* Validation */

    if (empty($product_name)) {

        $error = "Product name is required.";
    } elseif (empty($category)) {

        $error = "Please select a category.";
    } elseif ($purchase_price < 0) {

        $error = "Purchase price cannot be negative.";
    } elseif ($selling_price < 0) {

        $error = "Selling price cannot be negative.";
    } elseif ($stock < 0) {

        $error = "Stock cannot be negative.";
    } elseif ($selling_price < $purchase_price) {

        $error = "Selling price should not be less than purchase price.";
    } else {

        $check = mysqli_query(
            $conn,
            "SELECT id FROM products
    WHERE product_code='" . $product['product_code'] . "'
    AND id!='$id'"
        );

        if (mysqli_num_rows($check) > 0) {

            $error = "Product code already exists.";
        }
    }
    if (!isset($error)) {

        $image = $product['image'];

        if ($_FILES['image']['name'] != "") {

            $image = time() . "_" . $_FILES['image']['name'];

            move_uploaded_file(
                $_FILES['image']['tmp_name'],
                "../uploads/products/" . $image
            );
        }

        $sql = "UPDATE products SET

    product_name='$product_name',
    category='$category',
    supplier='$supplier',
    purchase_price='$purchase_price',
    selling_price='$selling_price',
    stock='$stock',
    unit='$unit',
    description='$description',
    image='$image',
    status='$status'

    WHERE id='$id'";

        if (mysqli_query($conn, $sql)) {

            $_SESSION['success'] = "Product updated successfully.";

            header("Location: products.php");
            exit();
        } else {

            $error = "Unable to update product.";
        }
    }
}
include "../includes/header.php";
include "../includes/sidebar.php";

?>

<div class="main-content">

    <div class="container-fluid">

        <div class="card shadow">

            <div class="card-header bg-warning">

                <h4 class="mb-0">

                    <i class="bi bi-pencil-square"></i>

                    Edit Product

                </h4>

            </div>

            <div class="card-body">

                <form method="POST" enctype="multipart/form-data">

                    <div class="row">

                        <div class="col-md-6 mb-3">

                            <label>Product Code</label>

                            <input
                                type="text"
                                class="form-control"
                                value="<?= $product['product_code']; ?>"
                                readonly>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label>Product Name</label>

                            <input
                                type="text"
                                name="product_name"
                                class="form-control"
                                value="<?= htmlspecialchars($product['product_name']); ?>"
                                required>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label>Category</label>

                            <select
                                name="category"
                                class="form-select"
                                required>

                                <option value="">-- Select Category --</option>

                                <option value="Electronics" <?= ($product['category'] == "Electronics") ? "selected" : ""; ?>>Electronics</option>

                                <option value="Groceries" <?= ($product['category'] == "Groceries") ? "selected" : ""; ?>>Groceries</option>

                                <option value="Stationery" <?= ($product['category'] == "Stationery") ? "selected" : ""; ?>>Stationery</option>

                                <option value="Furniture" <?= ($product['category'] == "Furniture") ? "selected" : ""; ?>>Furniture</option>

                                <option value="Clothing" <?= ($product['category'] == "Clothing") ? "selected" : ""; ?>>Clothing</option>

                                <option value="Books" <?= ($product['category'] == "Books") ? "selected" : ""; ?>>Books</option>

                                <option value="Sports" <?= ($product['category'] == "Sports") ? "selected" : ""; ?>>Sports</option>

                                <option value="Medical" <?= ($product['category'] == "Medical") ? "selected" : ""; ?>>Medical</option>

                                <option value="Cosmetics" <?= ($product['category'] == "Cosmetics") ? "selected" : ""; ?>>Cosmetics</option>

                                <option value="Home Appliances" <?= ($product['category'] == "Home Appliances") ? "selected" : ""; ?>>Home Appliances</option>

                                <option value="Mobile Accessories" <?= ($product['category'] == "Mobile Accessories") ? "selected" : ""; ?>>Mobile Accessories</option>

                                <option value="Computer Accessories" <?= ($product['category'] == "Computer Accessories") ? "selected" : ""; ?>>Computer Accessories</option>

                                <option value="Kitchen" <?= ($product['category'] == "Kitchen") ? "selected" : ""; ?>>Kitchen</option>

                                <option value="Toys" <?= ($product['category'] == "Toys") ? "selected" : ""; ?>>Toys</option>

                                <option value="Others" <?= ($product['category'] == "Others") ? "selected" : ""; ?>>Others</option>

                            </select>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label>Supplier</label>

                            <input
                                type="text"
                                name="supplier"
                                class="form-control"
                                value="<?= htmlspecialchars($product['supplier']); ?>">

                        </div>

                        <div class="col-md-6 mb-3">

                            <label>Purchase Price</label>

                            <input
                                type="number"
                                step="0.01"
                                name="purchase_price"
                                class="form-control"
                                value="<?= $product['purchase_price']; ?>">

                        </div>

                        <div class="col-md-6 mb-3">

                            <label>Selling Price</label>

                            <input
                                type="number"
                                step="0.01"
                                name="selling_price"
                                class="form-control"
                                value="<?= $product['selling_price']; ?>">

                        </div>

                        <div class="col-md-6 mb-3">

                            <label>Stock</label>

                            <input
                                type="number"
                                name="stock"
                                class="form-control"
                                value="<?= $product['stock']; ?>">

                        </div>

                        <div class="col-md-6 mb-3">

                            <label>Unit</label>

                            <input
                                type="text"
                                name="unit"
                                class="form-control"
                                value="<?= htmlspecialchars($product['unit']); ?>">

                        </div>

                        <div class="col-12 mb-3">

                            <label>Description</label>

                            <textarea
                                name="description"
                                class="form-control"
                                rows="4"><?= htmlspecialchars($product['description']); ?></textarea>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label>Current Image</label><br>

                            <?php

                            if ($product['image'] != "") {

                            ?>

                                <img src="../uploads/products/<?= $product['image']; ?>"
                                    width="120"
                                    class="img-thumbnail">

                            <?php

                            } else {

                                echo "No Image";
                            }

                            ?>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label>Change Image</label>

                            <input
                                type="file"
                                name="image"
                                class="form-control">

                        </div>

                        <div class="col-md-6 mb-3">

                            <label>Status</label>

                            <select
                                name="status"
                                class="form-select">

                                <option value="Active" <?= ($product['status'] == "Active") ? "selected" : ""; ?>>Active</option>

                                <option value="Inactive" <?= ($product['status'] == "Inactive") ? "selected" : ""; ?>>Inactive</option>

                            </select>

                        </div>

                    </div>

                    <button
                        class="btn btn-warning"
                        name="update">

                        <i class="bi bi-check-circle"></i>

                        Update Product

                    </button>

                    <a
                        href="products.php"
                        class="btn btn-secondary">

                        Cancel

                    </a>

                </form>

            </div>

        </div>

    </div>

</div>

<?php include "../includes/footer.php"; ?>