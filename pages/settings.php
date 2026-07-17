<?php
require_once "../includes/config.php";
require_once "../includes/auth.php";

adminOnly();

/* -------------------------
GET COMPANY SETTINGS
--------------------------*/
$result = mysqli_query($conn, "SELECT * FROM company_settings LIMIT 1");
$settings = mysqli_fetch_assoc($result);

/* -------------------------
SAVE COMPANY SETTINGS
--------------------------*/
if (isset($_POST['save_settings'])) {

    $company_name   = mysqli_real_escape_string($conn, $_POST['company_name']);
    $owner_name     = mysqli_real_escape_string($conn, $_POST['owner_name']);
    $phone          = mysqli_real_escape_string($conn, $_POST['phone']);
    $email          = mysqli_real_escape_string($conn, $_POST['email']);
    $gst_number     = mysqli_real_escape_string($conn, $_POST['gst_number']);
    $invoice_prefix = mysqli_real_escape_string($conn, $_POST['invoice_prefix']);
    $address        = mysqli_real_escape_string($conn, $_POST['address']);
    $logo = $settings['logo'];

    if (!empty($_FILES['logo']['name'])) {

        $extension = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));

        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($extension, $allowed)) {

            $logo = time() . "_" . basename($_FILES['logo']['name']);

            move_uploaded_file(
                $_FILES['logo']['tmp_name'],
                "../uploads/logo/" . $logo
            );
        }
    }

    $sql = "UPDATE company_settings
            SET company_name=?,
            owner_name=?,
            phone=?,
            email=?,
            gst_number=?,
            address=?,
            invoice_prefix=?,
            logo=?
            WHERE id=?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "ssssssssi",
        $company_name,
        $owner_name,
        $phone,
        $email,
        $gst_number,
        $address,
        $invoice_prefix,
        $logo,
        $settings['id']
    );

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = "Company Settings Updated Successfully.";
        header("Location: settings.php");
        exit();
    } else {
        $_SESSION['error'] = "Failed to Update Settings.";
    }
}

$result = mysqli_query($conn, "SELECT * FROM company_settings LIMIT 1");
$settings = mysqli_fetch_assoc($result);

$pageTitle = "Company Settings";

include "../includes/header.php";
include "../includes/sidebar.php";
if (isset($_SESSION['success'])) {
?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: '<?= $_SESSION['success']; ?>'
        });
    </script>
<?php unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '<?= $_SESSION['error']; ?>'
        });
    </script>
<?php unset($_SESSION['error']);
}
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="card shadow border-0">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="bi bi-gear-fill"></i> Company Settings</h4>
            </div>

            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Company Name</label>
                            <input type="text" name="company_name" class="form-control" value="<?= htmlspecialchars($settings['company_name']); ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Owner Name</label>
                            <input type="text" name="owner_name" class="form-control" value="<?= htmlspecialchars($settings['owner_name']); ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($settings['phone']); ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($settings['email']); ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">GST Number</label>
                            <input type="text" name="gst_number" class="form-control" value="<?= htmlspecialchars($settings['gst_number']); ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Invoice Prefix</label>
                            <input type="text" name="invoice_prefix" class="form-control" value="<?= htmlspecialchars($settings['invoice_prefix']); ?>">
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label">Address</label>
                            <textarea name="address" rows="4" class="form-control"><?= htmlspecialchars($settings['address']); ?></textarea>
                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="form-label">

                                Company Logo

                            </label>

                            <input
                                type="file"
                                name="logo"
                                class="form-control"
                                accept="image/*">

                            <?php if (!empty($settings['logo'])) { ?>

                                <div class="mt-2">

                                    <img
                                        src="../uploads/logo/<?= htmlspecialchars($settings['logo']); ?>"
                                        style="max-height:100px;"
                                        class="img-thumbnail">

                                </div>

                            <?php } ?>

                        </div>

                        <div class="col-12">
                            <button type="submit" name="save_settings" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Save Settings
                            </button>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>