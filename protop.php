<?php
// process_topup.php
session_start();
include "koneksi.php";
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success'=>false,'message'=>'Not logged in']);
    exit;
}

$user_id = intval($_SESSION['user_id']);

// get amount from POST (FormData)
$amount = isset($_POST['amount']) ? intval($_POST['amount']) : 0;
$bank_method = isset($_POST['bank_method']) ? trim($_POST['bank_method']) : '';

if ($amount <= 0) {
    echo json_encode(['success'=>false,'message'=>'Jumlah tidak valid']);
    exit;
}

// Ambil saldo current dari DB
$sql = "SELECT bank FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($res);
$current = $row ? intval($row['bank']) : 0;
mysqli_stmt_close($stmt);

// Hitung new bank
$new_bank = $current + $amount;

// Update DB
$u = "UPDATE users SET bank = ? WHERE id = ?";
$ust = mysqli_prepare($conn, $u);
mysqli_stmt_bind_param($ust, "ii", $new_bank, $user_id);
$ok = mysqli_stmt_execute($ust);
mysqli_stmt_close($ust);

if (!$ok) {
    echo json_encode(['success'=>false,'message'=>'Gagal update database']);
    exit;
}

// Update session
$_SESSION['bank'] = $new_bank;

// Kembalikan JSON
echo json_encode(['success'=>true,'new_balance'=>$new_bank,'message'=>'Top up berhasil']);
exit;
?>
