<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "ocagaminghub_bank_fix"; // DATABASE BENAR

$conn = mysqli_connect("localhost", "root", "", "ocagaminghub_bank_fix");


if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$check = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'balance'");
if ($check && mysqli_num_rows($check) == 0) {
    @mysqli_query($conn, "ALTER TABLE users ADD COLUMN balance INT NOT NULL DEFAULT 0");
}
?>
