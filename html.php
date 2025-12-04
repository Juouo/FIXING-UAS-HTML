<?php
session_start();
include "koneksi.php";
include "";
if (!isset($_SESSION['user_id'])) {
    header('Location: loginn.php');
    exit;
}

// AMBIL SALDO BANK DARI DATABASE
$query = "SELECT bank FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

$currentBank = intval($data['bank']); // SALDO ASLI DARI DATABASE
$message = "";
$message_type = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bank_method = $_POST['bank_method'] ?? '';
    $amount = intval($_POST['amount']);

    if ($amount <= 0) {
        $message = "Masukkan jumlah top up yang valid.";
        $message_type = "error";
    } elseif (empty($bank_method)) {
        $message = "Pilih metode pembayaran.";
        $message_type = "error";
    } else {

        // UPDATE saldo di database
        $newBalance = $currentBank + $amount;

        $update = "UPDATE users SET bank = ? WHERE id = ?";
        $stmt2 = mysqli_prepare($conn, $update);
        mysqli_stmt_bind_param($stmt2, "ii", $newBalance, $_SESSION['user_id']);
        mysqli_stmt_execute($stmt2);

        // UPDATE SESSION JUGA
        $_SESSION['bank'] = $newBalance;

        $currentBank = $newBalance;

        $message = "Top Up berhasil! Saldo sekarang: Rp " . number_format($newBalance, 0, ',', '.');
        $message_type = "success";
    }
}
?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="login.css">
    <title>Top Up</title>
</head>
<body>

<h2>Top Up Saldo</h2>

<p>User: <b><?= $_SESSION['username'] ?></b></p>
<p>Saldo Bank Sekarang: <b>Rp <?= number_format($currentBank, 0, ',', '.') ?></b></p>

<?php if ($message): ?>
    <div style="color:<?= $message_type == 'success' ? 'lime' : 'red' ?>;">
        <?= $message ?>
    </div>
<?php endif; ?>
<!-- di html.php (atau game.php) tempat sebelum load ody git.js -->
<input type="hidden" id="php-balance" value="<?php echo isset($_SESSION['bank']) ? (int)$_SESSION['bank'] : 0; ?>">
<input type="hidden" id="php-userid" value="<?php echo isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0; ?>">

<form method="POST">
    <label>Pilih Bank :</label><br>
    <input type="radio" name="bank_method" value="bca">BCA<br>
    <input type="radio" name="bank_method" value="bni">BNI<br>
    <input type="radio" name="bank_method" value="mandiri">Mandiri<br><br>

    <label>Jumlah Top Up:</label>
    <input type="number" name="amount" required><br><br>

    <button type="submit">Top Up</button>
</form>

<br>
<a href="html.php">Kembali</a>

</body>
</html>
