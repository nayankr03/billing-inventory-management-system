<?php

session_start();

$host = "localhost";
$username = "root";
$password = "";
$database = "billing_inventory";

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("Database Connection Failed: " );
}

?>