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

$sql = "SELECT id,
               customer_name,
               mobile,
               email,
               city,
               address
        FROM customers
        WHERE status='Active'
        AND (
            customer_name LIKE '%$search%'
            OR mobile LIKE '%$search%'
        )
        ORDER BY customer_name ASC
        LIMIT 10";

$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {

    while ($row = mysqli_fetch_assoc($result)) {

        ?>

        <div class="list-group-item customer-item"
             data-id="<?= $row['id']; ?>"
             data-name="<?= htmlspecialchars($row['customer_name']); ?>"
             data-mobile="<?= htmlspecialchars($row['mobile']); ?>"
             data-email="<?= htmlspecialchars($row['email']); ?>"
             data-city="<?= htmlspecialchars($row['city']); ?>"
             data-address="<?= htmlspecialchars($row['address']); ?>">

            <div class="fw-bold">

                <?= htmlspecialchars($row['customer_name']); ?>

            </div>

            <small class="text-muted">

                <i class="bi bi-telephone"></i>

                <?= htmlspecialchars($row['mobile']); ?>

            </small>

        </div>

        <?php

    }

} else {

    ?>

    <div class="list-group-item text-danger">

        No Customer Found

    </div>

    <?php

}
?>