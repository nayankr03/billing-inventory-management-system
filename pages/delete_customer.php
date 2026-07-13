<?php
require_once "../includes/config.php";

if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit();
}

if(!isset($_GET['id'])){
    header("Location: customers.php");
    exit();
}

$id = intval($_GET['id']);

$check = mysqli_query($conn,"SELECT * FROM customers WHERE id='$id'");

if(mysqli_num_rows($check)==0){
    $_SESSION['success']="Customer not found.";
    header("Location: customers.php");
    exit();
}

$customer = mysqli_fetch_assoc($check);

if(mysqli_query($conn,"DELETE FROM customers WHERE id='$id'")){
    $_SESSION['success']="Customer deleted successfully.";
}else{
    $_SESSION['success']="Unable to delete customer.";
}

header("Location: customers.php");
exit();
?>