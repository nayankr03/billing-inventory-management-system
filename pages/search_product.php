<?php
require_once "../includes/config.php";

if (!isset($_SESSION['user_id'])) {
    exit();
}

$search = "";

if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, trim($_GET['search']));
}

if ($search == "") {
    exit();
}

$sql = "SELECT
            id,
            product_code,
            product_name,
            category,
            supplier,
            selling_price,
            stock
        FROM products
        WHERE status='Active'
        AND (
            product_name LIKE '%$search%'
            OR product_code LIKE '%$search%'
        )
        ORDER BY product_name ASC
        LIMIT 10";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("SQL Error : " . mysqli_error($conn));
}

if (mysqli_num_rows($result) > 0) {

    while ($row = mysqli_fetch_assoc($result)) {
?>

        <div class="list-group-item list-group-item-action product-item"
            style="cursor:pointer;"
            data-id="<?= $row['id']; ?>"
            data-code="<?= htmlspecialchars($row['product_code']); ?>"
            data-name="<?= htmlspecialchars($row['product_name']); ?>"
            data-price="<?= $row['selling_price']; ?>"
            data-stock="<?= $row['stock']; ?>"
            data-category="<?= htmlspecialchars($row['category']); ?>"
            data-supplier="<?= htmlspecialchars($row['supplier']); ?>">

            <div class="d-flex justify-content-between align-items-center">

                <div>

                    <strong>
                        <?= htmlspecialchars($row['product_name']); ?>
                    </strong>

                    <br>

                    <small class="text-muted">
                        Code :
                        <?= htmlspecialchars($row['product_code']); ?>
                    </small>

                </div>

                <div class="text-end">

                    <strong class="text-success">
                        ₹<?= number_format($row['selling_price'], 2); ?>
                    </strong>

                    <br>

                    <small class="text-primary">
                        Stock :
                        <?= $row['stock']; ?>
                    </small>

                </div>

            </div>

        </div>

    <?php
    }
} else {
    ?>

    <div class="list-group-item text-danger">

        <i class="bi bi-exclamation-circle"></i>

        No Product Found

    </div>

<?php
}
?>