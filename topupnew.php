<?php
// === topup.php (digabung dengan html.php) ===
session_start();
include "koneksi.php";

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: loginn.php");
    exit;
}

// Ambil saldo terbaru dari database
$query = "SELECT bank, username FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

$currentBank = intval($user["bank"]);
$username = $user["username"];
$_SESSION["bank"] = $currentBank;
$_SESSION["username"] = $username;

$message = '';
$message_type = '';

// Proses top up
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bank_method = isset($_POST['bank_method']) ? $_POST['bank_method'] : '';
    $amount = isset($_POST['amount']) ? (int)$_POST['amount'] : 0;

    if ($amount <= 0) {
        $message = 'Masukkan jumlah top up yang valid (lebih dari 0).';
        $message_type = 'error';
    } elseif (empty($bank_method)) {
        $message = 'Pilih metode pembayaran bank terlebih dahulu.';
        $message_type = 'error';
    } else {
        $newBalance = $currentBank + $amount;

        // Update saldo ke database
        $update = "UPDATE users SET bank = ? WHERE id = ?";
        $stmt2 = mysqli_prepare($conn, $update);
        mysqli_stmt_bind_param($stmt2, "ii", $newBalance, $_SESSION['user_id']);
        mysqli_stmt_execute($stmt2);

        $message = 'Top up berhasil! Saldo baru Anda: Rp ' . number_format($newBalance, 0, ',', '.');
        $message_type = 'success';

        $_SESSION['bank'] = $newBalance;
        $currentBank = $newBalance;
    }
}
?>

<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Top Up Saldo - GameHub</title>
  <link rel="stylesheet" href="login.css" />
</head>
<body style="background:#000; color:white; font-family:Arial;">
  <div style="max-width:600px; margin:auto; padding:20px;">

    <h1 style="color:#00FF00; text-align:center;">💳 Top Up Saldo</h1>

    <div style="background:rgba(0,100,0,0.3); padding:15px; border-radius:10px; border:1px solid #00FF00; margin-bottom:20px;">
      <p>Pemain: <b style="color:#00FF00;"><?= htmlspecialchars($username) ?></b></p>
      <p>Saldo Saat Ini: <b style="color:#00FF00;">Rp <?= number_format($currentBank, 0, ',', '.') ?></b></p>
    </div>

    <?php if ($message): ?>
      <div style="padding:12px; border-radius:8px; margin-bottom:15px; border:1px solid <?= $message_type=='success'?'#00FF00':'#FF6B6B' ?>; color:<?= $message_type=='success'?'#00FF00':'#FF9999' ?>;">
        <?= htmlspecialchars($message) ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="topup.php" style="background:rgba(0,100,0,0.2); padding:20px; border-radius:10px; border:1px solid #00FF00;">
      <label style="color:#00FF00;">🏦 Pilih Bank</label><br><br>

      <label><input type="radio" name="bank_method" value="bca"> BCA</label><br>
      <label><input type="radio" name="bank_method" value="mandiri"> Mandiri</label><br>
      <label><input type="radio" name="bank_method" value="bni"> BNI</label><br>
      <label><input type="radio" name="bank_method" value="cimb"> CIMB Niaga</label><br>
      <label><input type="radio" name="bank_method" value="ocbc"> OCBC NISP</label><br>
      <label><input type="radio" name="bank_method" value="ewallet"> E-Wallet</label><br><br>

      <label style="color:#00FF00;">💵 Jumlah Top Up</label>
      <input type="number" name="amount" min="1" placeholder="Contoh: 50000" required style="width:100%; padding:10px; border-radius:8px; margin-top:5px;">

      <br><br>

      <button type="submit" style="width:100%; padding:12px; background:#00FF00; border:none; border-radius:8px; font-weight:bold; cursor:pointer;">Lanjutkan Top Up</button>
      <br><br>
      <a href="html.php" style="display:block; text-align:center; padding:10px; color:#00FF00;">Kembali</a>
    </form>
  </div>
</body>
</html>