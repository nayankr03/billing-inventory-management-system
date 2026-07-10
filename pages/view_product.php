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
$result = mysqli_query($conn, "SELECT * FROM products WHERE id='$id'");
if (mysqli_num_rows($result) == 0) {
    header("Location: products.php");
    exit();
}
$product = mysqli_fetch_assoc($result);
include "../includes/header.php";
include "../includes/sidebar.php";
?>
<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold"><i class="bi bi-eye"></i> View Product</h2>
            <div>
                <a href="edit_product.php?id=<?= $product['id']; ?>" class="btn btn-warning"><i class="bi bi-pencil-square"></i> Edit</a>
                <a href="products.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
            </div>
        </div>
        <div class="card shadow">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <?php if (!empty($product['image'])) { ?>
                            <img src="../uploads/products/<?= htmlspecialchars($product['image']); ?>" class="img-fluid img-thumbnail" style="max-height:300px;object-fit:cover;">
                        <?php } else { ?>
                            <div class="border rounded p-5 text-muted">No Image</div>
                        <?php } ?>
                    </div>
                    <div class="col-md-8">
                        <table class="table table-bordered">
                            <tr>
                                <th width="35%">Product Code</th>
                                <td><?= htmlspecialchars($product['product_code']); ?></td>
                            </tr>
                            <tr>
                                <th>Product Name</th>
                                <td><?= htmlspecialchars($product['product_name']); ?></td>
                            </tr>
                            <tr>
                                <th>Category</th>
                                <td><?= htmlspecialchars($product['category']); ?></td>
                            </tr>
                            <tr>
                                <th>Supplier</th>
                                <td><?= $product['supplier'] ? htmlspecialchars($product['supplier']) : '-'; ?></td>
                            </tr>
                            <tr>
                                <th>Purchase Price</th>
                                <td>₹<?= number_format($product['purchase_price'], 2); ?></td>
                            </tr>
                            <tr>
                                <th>Selling Price</th>
                                <td>₹<?= number_format($product['selling_price'], 2); ?></td>
                            </tr>
                            <tr>
                                <th>Stock</th>
                                <td><?= $product['stock']; ?></td>
                            </tr>
                            <tr>
                                <th>Unit</th>
                                <td><?= $product['unit'] ? htmlspecialchars($product['unit']) : '-'; ?></td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td><?php if ($product['status'] == "Active") { ?><span class="badge bg-success">Active</span><?php } else { ?><span class="badge bg-secondary">Inactive</span><?php } ?></td>
                            </tr>
                            <tr>
                                <th>Description</th>
                                <td><?= $product['description'] ? nl2br(htmlspecialchars($product['description'])) : '-'; ?></td>
                            </tr>
                            <tr>
                                <th>Created At</th>
                                <td><?= $product['created_at']; ?></td>
                            </tr>
                            <tr>
                                <th>Updated At</th>
                                <td><?= $product['updated_at']; ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include "../includes/footer.php"; ?>