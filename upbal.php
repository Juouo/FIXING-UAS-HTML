<?php
// update_balance.php
session_start();
include "koneksi.php";
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success'=>false,'message'=>'Not logged in']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!$data || !isset($data['balance'])) {
    echo json_encode(['success'=>false,'message'=>'No balance provided']);
    exit;
}

$user_id = intval($_SESSION['user_id']);
$new = intval($data['balance']);
if ($new < 0) $new = 0;

// Update DB (kolom bank)
$sql = "UPDATE users SET bank = ? WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $new, $user_id);
$ok = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

if (!$ok) {
    echo json_encode(['success'=>false,'message'=>'DB error']);
    exit;
}

// Update session
$_SESSION['bank'] = $new;

echo json_encode(['success'=>true,'new_balance'=>$new]);
exit;
?>
