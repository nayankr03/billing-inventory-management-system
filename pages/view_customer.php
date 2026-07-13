<?php
require_once "../includes/config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: customer.php");
    exit();
}

$id = intval($_GET['id']);

$result = mysqli_query($conn, "SELECT * FROM customers WHERE id='$id'");

if (mysqli_num_rows($result) == 0) {
    header("Location: customer.php");
    exit();
}

$customer = mysqli_fetch_assoc($result);

include "../includes/header.php";
include "../includes/sidebar.php";
?>

<div class="main-content">
    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-4">

            <h2 class="fw-bold">
                <i class="bi bi-person-vcard"></i> View Customer
            </h2>

            <div>
                <a href="edit_customer.php?id=<?= $customer['id']; ?>" class="btn btn-warning">
                    <i class="bi bi-pencil-square"></i> Edit
                </a>

                <a href="customer.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>

        </div>

        <div class="card shadow">
            <div class="card-body">

                <table class="table table-bordered">

                    <tr>
                        <th width="30%">Customer Code</th>
                        <td><?= htmlspecialchars($customer['customer_code']); ?></td>
                    </tr>

                    <tr>
                        <th>Customer Name</th>
                        <td><?= htmlspecialchars($customer['customer_name']); ?></td>
                    </tr>

                    <tr>
                        <th>Mobile Number</th>
                        <td><?= htmlspecialchars($customer['mobile']); ?></td>
                    </tr>

                    <tr>
                        <th>Email</th>
                        <td><?= !empty($customer['email']) ? htmlspecialchars($customer['email']) : "-"; ?></td>
                    </tr>

                    <tr>
                        <th>Address</th>
                        <td><?= !empty($customer['address']) ? nl2br(htmlspecialchars($customer['address'])) : "-"; ?></td>
                    </tr>

                    <tr>
                        <th>City</th>
                        <td><?= !empty($customer['city']) ? htmlspecialchars($customer['city']) : "-"; ?></td>
                    </tr>

                    <tr>
                        <th>State</th>
                        <td><?= !empty($customer['state']) ? htmlspecialchars($customer['state']) : "-"; ?></td>
                    </tr>

                    <tr>
                        <th>Pincode</th>
                        <td><?= !empty($customer['pincode']) ? htmlspecialchars($customer['pincode']) : "-"; ?></td>
                    </tr>

                    <tr>
                        <th>GST Number</th>
                        <td><?= !empty($customer['gst_number']) ? htmlspecialchars($customer['gst_number']) : "-"; ?></td>
                    </tr>

                    <tr>
                        <th>Status</th>
                        <td>
                            <?php if ($customer['status'] == "Active") { ?>
                                <span class="badge bg-success">Active</span>
                            <?php } else { ?>
                                <span class="badge bg-secondary">Inactive</span>
                            <?php } ?>
                        </td>
                    </tr>

                    <tr>
                        <th>Created At</th>
                        <td><?= $customer['created_at']; ?></td>
                    </tr>

                    <tr>
                        <th>Updated At</th>
                        <td><?= $customer['updated_at']; ?></td>
                    </tr>

                </table>

            </div>
        </div>

    </div>
</div>

<?php include "../includes/footer.php"; ?>