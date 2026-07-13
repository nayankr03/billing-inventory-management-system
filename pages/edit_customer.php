r'''<?php
require_once "../includes/config.php";

if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit();
}

if(!isset($_GET['id'])){
    header("Location: customer.php");
    exit();
}

$id=intval($_GET['id']);

$res=mysqli_query($conn,"SELECT * FROM customers WHERE id='$id'");
if(mysqli_num_rows($res)==0){
    header("Location: customer.php");
    exit();
}
$customer=mysqli_fetch_assoc($res);

if(isset($_POST['update'])){

    $customer_name=mysqli_real_escape_string($conn,$_POST['customer_name']);
    $mobile=mysqli_real_escape_string($conn,$_POST['mobile']);
    $email=mysqli_real_escape_string($conn,$_POST['email']);
    $address=mysqli_real_escape_string($conn,$_POST['address']);
    $city=mysqli_real_escape_string($conn,$_POST['city']);
    $state=mysqli_real_escape_string($conn,$_POST['state']);
    $pincode=mysqli_real_escape_string($conn,$_POST['pincode']);
    $gst_number=mysqli_real_escape_string($conn,$_POST['gst_number']);
    $status=mysqli_real_escape_string($conn,$_POST['status']);

    if(empty($customer_name)){
        $error="Customer name is required.";
    }elseif(empty($mobile)){
        $error="Mobile number is required.";
    }elseif(!preg_match('/^[0-9]{10}$/',$mobile)){
        $error="Enter a valid 10-digit mobile number.";
    }else{

        $check=mysqli_query($conn,"SELECT id FROM customers WHERE mobile='$mobile' AND id!='$id'");
        if(mysqli_num_rows($check)>0){
            $error="Mobile number already exists.";
        }else{

            $sql="UPDATE customers SET
            customer_name='$customer_name',
            mobile='$mobile',
            email='$email',
            address='$address',
            city='$city',
            state='$state',
            pincode='$pincode',
            gst_number='$gst_number',
            status='$status'
            WHERE id='$id'";

            if(mysqli_query($conn,$sql)){
                $_SESSION['success']="Customer updated successfully.";
                header("Location: customer.php");
                exit();
            }else{
                $error="Unable to update customer.";
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
<div class="card-header bg-warning">
<h4 class="mb-0"><i class="bi bi-pencil-square"></i> Edit Customer</h4>
</div>

<div class="card-body">

<?php if(isset($error)){ ?>
<div class="alert alert-danger"><?= $error; ?></div>
<?php } ?>

<form method="POST">

<div class="row">

<div class="col-md-6 mb-3">
<label>Customer Code</label>
<input type="text" class="form-control" value="<?= htmlspecialchars($customer['customer_code']); ?>" readonly>
</div>

<div class="col-md-6 mb-3">
<label>Customer Name</label>
<input type="text" name="customer_name" class="form-control" value="<?= htmlspecialchars($customer['customer_name']); ?>" required>
</div>

<div class="col-md-6 mb-3">
<label>Mobile Number</label>
<input type="text" name="mobile" maxlength="10" class="form-control" value="<?= htmlspecialchars($customer['mobile']); ?>" required>
</div>

<div class="col-md-6 mb-3">
<label>Email</label>
<input type="email" name="email" class="form-control" value="<?= htmlspecialchars($customer['email']); ?>">
</div>

<div class="col-12 mb-3">
<label>Address</label>
<textarea name="address" class="form-control" rows="3"><?= htmlspecialchars($customer['address']); ?></textarea>
</div>

<div class="col-md-4 mb-3">
<label>City</label>
<input type="text" name="city" class="form-control" value="<?= htmlspecialchars($customer['city']); ?>">
</div>

<div class="col-md-4 mb-3">
<label>State</label>
<input type="text" name="state" class="form-control" value="<?= htmlspecialchars($customer['state']); ?>">
</div>

<div class="col-md-4 mb-3">
<label>Pincode</label>
<input type="text" name="pincode" class="form-control" value="<?= htmlspecialchars($customer['pincode']); ?>">
</div>

<div class="col-md-6 mb-3">
<label>GST Number</label>
<input type="text" name="gst_number" class="form-control" value="<?= htmlspecialchars($customer['gst_number']); ?>">
</div>

<div class="col-md-6 mb-3">
<label>Status</label>
<select name="status" class="form-select">
<option value="Active" <?= $customer['status']=="Active"?"selected":""; ?>>Active</option>
<option value="Inactive" <?= $customer['status']=="Inactive"?"selected":""; ?>>Inactive</option>
</select>
</div>

</div>

<button type="submit" name="update" class="btn btn-warning">
<i class="bi bi-check-circle"></i> Update Customer
</button>

<a href="customer.php" class="btn btn-secondary">Cancel</a>
</form>

</div>
</div>

</div>
</div>

<?php include "../includes/footer.php"; ?>