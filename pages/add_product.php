<?php

require_once "../includes/config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

/* Auto Product Code */
$codeQuery = mysqli_query($conn, "SELECT id FROM products ORDER BY id DESC LIMIT 1");

if (mysqli_num_rows($codeQuery) > 0) {

    $last = mysqli_fetch_assoc($codeQuery);

    $product_code = "PRD" . str_pad($last['id'] + 1, 3, "0", STR_PAD_LEFT);
} else {

    $product_code = "PRD001";
}

/* Save Product */

if (isset($_POST['save'])) {

    $product_code = mysqli_real_escape_string($conn, $_POST['product_code']);
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
            "SELECT id FROM products WHERE product_code='$product_code'"
        );

        if (mysqli_num_rows($check) > 0) {

            $error = "Product code already exists.";
        }
    }
    if (!isset($error)) {

        $image = "";

        if (isset($_FILES['image']) && $_FILES['image']['name'] != "") {

            $image = time() . "_" . $_FILES['image']['name'];

            move_uploaded_file(
                $_FILES['image']['tmp_name'],
                "../uploads/products/" . $image
            );
        }

        $sql = "INSERT INTO products
    (
    product_code,
    product_name,
    category,
    supplier,
    purchase_price,
    selling_price,
    stock,
    unit,
    description,
    image,
    status
    )

    VALUES(

    '$product_code',
    '$product_name',
    '$category',
    '$supplier',
    '$purchase_price',
    '$selling_price',
    '$stock',
    '$unit',
    '$description',
    '$image',
    '$status'

    )";

        if (mysqli_query($conn, $sql)) {

            $_SESSION['success'] = "Product added successfully.";

            header("Location: products.php");
            exit();
        } else {

            $error = "Unable to save product.";
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

                <h4 class="mb-0">

                    <i class="bi bi-plus-circle"></i>

                    Add Product

                </h4>

            </div>

            <div class="card-body">

                <?php if (isset($error)) { ?>

                    <div class="alert alert-danger">

                        <?= $error ?>

                    </div>

                <?php } ?>

                <form method="POST" enctype="multipart/form-data">

                    <div class="row">

                        <div class="col-md-6 mb-3">

                            <label>Product Code</label>

                            <input
                                type="text"
                                name="product_code"
                                class="form-control"
                                value="<?= $product_code ?>"
                                readonly>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label>Product Name</label>

                            <input
                                type="text"
                                name="product_name"
                                class="form-control"
                                required>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label>Category</label>

                            <select
                                name="category"
                                class="form-select"
                                required>

                                <option value="">-- Select Category --</option>

                                <option value="Electronics">Electronics</option>
                                <option value="Groceries">Groceries</option>
                                <option value="Stationery">Stationery</option>
                                <option value="Furniture">Furniture</option>
                                <option value="Clothing">Clothing</option>
                                <option value="Books">Books</option>
                                <option value="Sports">Sports</option>
                                <option value="Medical">Medical</option>
                                <option value="Cosmetics">Cosmetics</option>
                                <option value="Home Appliances">Home Appliances</option>
                                <option value="Mobile Accessories">Mobile Accessories</option>
                                <option value="Computer Accessories">Computer Accessories</option>
                                <option value="Kitchen">Kitchen</option>
                                <option value="Toys">Toys</option>
                                <option value="Others">Others</option>

                            </select>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label>Supplier</label>

                            <input
                                type="text"
                                name="supplier"
                                class="form-control">

                        </div>

                        <div class="col-md-6 mb-3">

                            <label>Purchase Price</label>

                            <input
                                type="number"
                                step="0.01"
                                name="purchase_price"
                                class="form-control"
                                required>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label>Selling Price</label>

                            <input
                                type="number"
                                step="0.01"
                                name="selling_price"
                                class="form-control"
                                required>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label>Stock</label>

                            <input
                                type="number"
                                name="stock"
                                class="form-control"
                                required>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label>Unit</label>

                            <input
                                type="text"
                                name="unit"
                                class="form-control"
                                placeholder="Piece">

                        </div>

                        <div class="col-12 mb-3">

                            <label>Description</label>

                            <textarea
                                name="description"
                                class="form-control"
                                rows="4"></textarea>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label>Product Image</label>

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

                                <option value="Active">Active</option>

                                <option value="Inactive">Inactive</option>

                            </select>

                        </div>

                    </div>

                    <button
                        class="btn btn-success"
                        name="save">

                        <i class="bi bi-check-circle"></i>

                        Save Product

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