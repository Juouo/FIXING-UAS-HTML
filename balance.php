<?php
session_start();
include "koneksi.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$userId = $_SESSION['user_id'];
$balance = isset($input['balance']) ? intval($input['balance']) : 0;
$action = $input['action'] ?? 'sync';

// Validasi balance
if ($balance < 0 || $balance > 1000000000) {
    echo json_encode(['success' => false, 'message' => 'Invalid balance']);
    exit;
}

// Ambil saldo dari database
$query = "SELECT bank FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);
$serverBalance = intval($data['bank']);

$finalBalance = $balance;
$message = '';

// Logic sync berdasarkan action
if ($action === 'sync') {
    // Gunakan yang lebih tinggi antara server dan client
    $finalBalance = max($serverBalance, $balance);
    $message = 'Synced successfully';
    
    // Update session
    $_SESSION['bank'] = $finalBalance;
} elseif ($action === 'final' || $action === 'update') {
    // Update server dengan data dari client
    $finalBalance = $balance;
    $message = 'Updated from client';
}

// Update database
$update = "UPDATE users SET bank = ?, last_sync = NOW() WHERE id = ?";
$stmt2 = mysqli_prepare($conn, $update);
mysqli_stmt_bind_param($stmt2, "ii", $finalBalance, $userId);
$success = mysqli_stmt_execute($stmt2);

if ($success) {
    echo json_encode([
        'success' => true,
        'balance' => $finalBalance,
        'server_balance' => $serverBalance,
        'client_balance' => $balance,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>