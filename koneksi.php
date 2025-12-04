<?php
$host = "localhost";
$user = "root";   // biasanya root
$pass = "";       // kosong jika XAMPP
$db   = "login";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
// Ensure 'balance' column exists in 'users' table. If not, add it with default 0.
$check = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'balance'");
if ($check && mysqli_num_rows($check) == 0) {
    // Attempt to add the column; ignore errors if table doesn't exist yet.
    @mysqli_query($conn, "ALTER TABLE users ADD COLUMN balance INT NOT NULL DEFAULT 0");
}
?>
