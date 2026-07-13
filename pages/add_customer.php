<?php
require_once "../includes/config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

/* Auto Customer Code */
$q = mysqli_query($conn, "SELECT id FROM customers ORDER BY id DESC LIMIT 1");
if (mysqli_num_rows($q) > 0) {
    $r = mysqli_fetch_assoc($q);
    $customer_code = "CUS" . str_pad($r['id'] + 1, 3, "0", STR_PAD_LEFT);
} else {
    $customer_code = "CUS001";
}

if (isset($_POST['save'])) {

    $customer_code = mysqli_real_escape_string($conn, $_POST['customer_code']);
    $customer_name = mysqli_real_escape_string($conn, $_POST['customer_name']);
    $mobile = mysqli_real_escape_string($conn, $_POST['mobile']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $state = mysqli_real_escape_string($conn, $_POST['state']);
    $pincode = mysqli_real_escape_string($conn, $_POST['pincode']);
    $gst_number = mysqli_real_escape_string($conn, $_POST['gst_number']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    if (empty($customer_name)) {
        $error = "Customer name is required.";
    } elseif (empty($mobile)) {
        $error = "Mobile number is required.";
    } elseif (!preg_match('/^[0-9]{10}$/', $mobile)) {
        $error = "Enter a valid 10-digit mobile number.";
    } else {
        $check = mysqli_query($conn, "SELECT id FROM customers WHERE mobile='$mobile'");
        if (mysqli_num_rows($check) > 0) {
            $error = "Mobile number already exists.";
        } else {
            $sql = "INSERT INTO customers(customer_code,customer_name,mobile,email,address,city,state,pincode,gst_number,status)
            VALUES('$customer_code','$customer_name','$mobile','$email','$address','$city','$state','$pincode','$gst_number','$status')";
            if (mysqli_query($conn, $sql)) {
                $_SESSION['success'] = "Customer added successfully.";
                header("Location: customers.php");
                exit();
            } else {
                $error = "Unable to add customer.";
            }
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
                <h4 class="mb-0"><i class="bi bi-person-plus"></i> Add Customer</h4>
            </div>

            <div class="card-body">

                <?php if (isset($error)) { ?>
                    <div class="alert alert-danger"><?= $error; ?></div>
                <?php } ?>

                <form method="POST">

                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label>Customer Code</label>
                            <input type="text" name="customer_code" class="form-control" value="<?= $customer_code ?>" readonly>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Customer Name</label>
                            <input type="text" name="customer_name" class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Mobile Number</label>
                            <input type="text" name="mobile" class="form-control" maxlength="10" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control">
                        </div>

                        <div class="col-12 mb-3">
                            <label>Address</label>
                            <textarea name="address" class="form-control" rows="3"></textarea>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>City</label>
                            <input type="text" name="city" class="form-control">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>State</label>
                            <input type="text" name="state" class="form-control">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Pincode</label>
                            <input type="text" name="pincode" class="form-control">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>GST Number</label>
                            <input type="text" name="gst_number" class="form-control">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Status</label>
                            <select name="status" class="form-select">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>

                    </div>

                    <button type="submit" name="save" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Save Customer
                    </button>

                    <a href="customer.php" class="btn btn-secondary">Cancel</a>
                </form>

            </div>
        </div>

    </div>
</div>

<?php include "../includes/footer.php"; ?>