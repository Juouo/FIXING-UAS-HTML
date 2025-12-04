<?php
// save_bank.php
session_start();
include "koneksi.php";

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    die("Not logged in");
}

$user_id = $_SESSION['user_id'];
$amount = intval($_POST['amount']); // jumlah top up / perubahan saldo

// Ambil saldo awal dari database
$sql = "SELECT bank FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

$current_bank = intval($user['bank']);
$new_bank = $current_bank + $amount;

// Update saldo ke database
$update_sql = "UPDATE users SET bank = ? WHERE id = ?";
$update_stmt = mysqli_prepare($conn, $update_sql);
mysqli_stmt_bind_param($update_stmt, "ii", $new_bank, $user_id);
mysqli_stmt_execute($update_stmt);

// Update session biar realtime
$_SESSION['bank'] = $new_bank;

// Kirim respon
echo "SUCCESS:".$new_bank;
