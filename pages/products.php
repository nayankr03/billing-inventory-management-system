r'''<?php
    require_once "../includes/config.php";

    if (!isset($_SESSION['user_id'])) {
        header("Location: ../login.php");
        exit();
    }

    include "../includes/header.php";
    include "../includes/sidebar.php";

    /* Search */
    if (isset($_GET['search']) && trim($_GET['search']) != "") {

        $search = mysqli_real_escape_string($conn, $_GET['search']);

        $sql = "SELECT * FROM products
            WHERE product_code LIKE '%$search%'
               OR product_name LIKE '%$search%'
               OR category LIKE '%$search%'
               OR supplier LIKE '%$search%'
            ORDER BY id DESC";
    } else {

        $sql = "SELECT * FROM products ORDER BY id DESC";
    }

    $result = mysqli_query($conn, $sql);
    ?>

<div class="main-content">
    <div class="container-fluid">
        <?php
        if (isset($_SESSION['success'])) {
        ?>

            <script>
                Swal.fire({

                    icon: 'success',

                    title: 'Success',

                    text: '<?= $_SESSION['success']; ?>',

                    confirmButtonColor: '#0d6efd'

                });
            </script>

        <?php

            unset($_SESSION['success']);
        }

        ?>

        <div class="d-flex justify-content-between align-items-center mb-4">

            <h2 class="fw-bold">
                <i class="bi bi-box-seam"></i> Products
            </h2>

            <a href="add_product.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add Product
            </a>

        </div>

        <div class="row mb-3">

            <div class="col-md-6">

                <form method="GET">

                    <div class="input-group">

                        <input
                            type="text"
                            name="search"
                            class="form-control"
                            placeholder="Search Product..."
                            value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">

                        <button class="btn btn-primary">
                            <i class="bi bi-search"></i> Search
                        </button>

                        <a href="products.php" class="btn btn-secondary">
                            Reset
                        </a>

                    </div>

                </form>

            </div>

        </div>

        <div class="card shadow">

            <div class="card-body">

                <div class="table-responsive">

                    <table class="table table-hover table-bordered align-middle">

                        <thead class="table-primary">

                            <tr>
                                <th>ID</th>
                                <th>Code</th>
                                <th>Image</th>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Supplier</th>
                                <th>Purchase</th>
                                <th>Selling</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th width="160">Action</th>
                            </tr>

                        </thead>

                        <tbody>

                            <?php if (mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) { ?>

                                    <tr>

                                        <td><?= $row['id']; ?></td>

                                        <td>
                                            <span class="badge bg-dark">
                                                <?= htmlspecialchars($row['product_code']); ?>
                                            </span>
                                        </td>

                                        <td>

                                            <?php if (!empty($row['image'])) { ?>

                                                <img src="../uploads/products/<?= htmlspecialchars($row['image']); ?>"
                                                    width="60"
                                                    height="60"
                                                    class="img-thumbnail"
                                                    style="object-fit:cover;">

                                            <?php } else { ?>

                                                <span class="text-muted">No Image</span>

                                            <?php } ?>

                                        </td>

                                        <td><strong><?= htmlspecialchars($row['product_name']); ?></strong></td>

                                        <td><?= htmlspecialchars($row['category']); ?></td>

                                        <td><?= !empty($row['supplier']) ? htmlspecialchars($row['supplier']) : "-"; ?></td>

                                        <td>₹<?= number_format($row['purchase_price'], 2); ?></td>

                                        <td>₹<?= number_format($row['selling_price'], 2); ?></td>

                                        <td>

                                            <?php

                                            if ($row['stock'] <= 5) {

                                                echo "<span class='badge bg-danger'>{$row['stock']}</span>";
                                            } elseif ($row['stock'] <= 15) {

                                                echo "<span class='badge bg-warning text-dark'>{$row['stock']}</span>";
                                            } else {

                                                echo "<span class='badge bg-success'>{$row['stock']}</span>";
                                            }

                                            ?>

                                        </td>

                                        <td>

                                            <?php

                                            if ($row['status'] == "Active") {

                                                echo "<span class='badge bg-success'>Active</span>";
                                            } else {

                                                echo "<span class='badge bg-secondary'>Inactive</span>";
                                            }

                                            ?>

                                        </td>

                                        <td>

                                            <a href="view_product.php?id=<?= $row['id']; ?>"
                                                class="btn btn-info btn-sm text-white"
                                                title="View">

                                                <i class="bi bi-eye"></i>

                                            </a>

                                            <a href="edit_product.php?id=<?= $row['id']; ?>"
                                                class="btn btn-warning btn-sm"
                                                title="Edit">

                                                <i class="bi bi-pencil-square"></i>

                                            </a>

                                            <a href="#"
                                                class="btn btn-danger btn-sm deleteBtn"
                                                data-url="delete_product.php?id=<?= $row['id']; ?>"
                                                title="Delete">

                                                <i class="bi bi-trash"></i>

                                            </a>

                                        </td>

                                    </tr>

                                <?php }
                            } else { ?>

                                <tr>
                                    <td colspan="11" class="text-center text-muted">
                                        No Products Found
                                    </td>
                                </tr>

                            <?php } ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>
<script>
    document.querySelectorAll('.deleteBtn').forEach(button => {

        button.addEventListener('click', function(e) {

            e.preventDefault();

            const url = this.dataset.url;

            Swal.fire({

                title: 'Delete Product?',

                text: 'This action cannot be undone.',

                icon: 'warning',

                showCancelButton: true,

                confirmButtonColor: '#dc3545',

                cancelButtonColor: '#6c757d',

                confirmButtonText: 'Delete'

            }).then((result) => {

                if (result.isConfirmed) {

                    window.location.href = url;

                }

            });

        });

    });
</script>
<?php include "../includes/footer.php"; ?>