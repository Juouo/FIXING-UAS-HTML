<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: loginn.php');
    exit;
}

// Initialize balance if not set
if (!isset($_SESSION['balance'])) {
    $_SESSION['balance'] = 1000;
}

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bank_method = isset($_POST['bank_method']) ? $_POST['bank_method'] : '';
    $amount = isset($_POST['amount']) ? (int)$_POST['amount'] : 0;
    
    if ($amount <= 0) {
        $message = 'Masukkan jumlah top up yang valid (lebih dari 0).';
        $message_type = 'error';
    } elseif (empty($bank_method)) {
        $message = 'Pilih metode pembayaran terlebih dahulu.';
        $message_type = 'error';
    } else {
        // Add balance to session
        $_SESSION['balance'] += $amount;
        $message = 'Top up berhasil! Saldo Anda: Rp ' . number_format($_SESSION['balance'], 0, ',', '.');
        $message_type = 'success';
    }
}
?>


<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Top Up Saldo - OCA GameHub</title>
  <link rel="stylesheet" href="login.css" />
  <style>
    .topup-container {
      position: relative;
      z-index: 10;
      width: 100%;
      max-width: 600px;
      padding: 20px;
      margin: auto;
    }

    .topup-header {
      text-align: center;
      margin-bottom: 30px;
    }

    .topup-header h1 {
      color: #00FF00;
      font-size: 28px;
      text-shadow: 0 0 20px rgba(0, 255, 0, 0.6);
      margin-bottom: 10px;
    }

    .user-info {
      background: rgba(0, 100, 0, 0.3);
      border: 1px solid rgba(0, 255, 0, 0.3);
      border-radius: 8px;
      padding: 15px;
      margin-bottom: 20px;
    }

    .info-row {
      display: flex;
      justify-content: space-between;
      color: #88FF88;
      margin: 8px 0;
      font-size: 14px;
    }

    .balance-display {
      font-size: 18px;
      font-weight: bold;
      color: #00FF00;
    }

    .bank-methods {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
      gap: 12px;
      margin-bottom: 20px;
    }

    .bank-option {
      position: relative;
    }

    .bank-option input[type="radio"] {
      display: none;
    }

    .bank-label {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 15px;
      background: rgba(0, 100, 0, 0.2);
      border: 2px solid rgba(0, 255, 0, 0.3);
      border-radius: 8px;
      cursor: pointer;
      transition: all 0.3s ease;
      color: #88FF88;
      font-size: 12px;
      font-weight: 600;
    }

    .bank-option input[type="radio"]:checked + .bank-label {
      background: rgba(0, 255, 0, 0.3);
      border-color: #00FF00;
      box-shadow: 0 0 15px rgba(0, 255, 0, 0.5);
      color: #00FF00;
    }

    .bank-label:hover {
      background: rgba(0, 150, 0, 0.2);
      border-color: rgba(0, 255, 0, 0.6);
    }

    .bank-icon {
      font-size: 24px;
      margin-bottom: 5px;
    }

    .topup-form {
      background: rgba(0, 100, 0, 0.2);
      backdrop-filter: blur(10px);
      border: 2px solid rgba(0, 255, 0, 0.4);
      border-radius: 15px;
      padding: 30px;
      box-shadow: 0 8px 32px 0 rgba(0, 255, 0, 0.2);
    }

    .form-section {
      margin-bottom: 20px;
    }

    .form-section label {
      display: block;
      color: #00FF00;
      font-size: 13px;
      margin-bottom: 8px;
      font-weight: 600;
      letter-spacing: 0.5px;
    }

    .form-section input {
      width: 100%;
      padding: 12px;
      border: 1px solid rgba(0, 255, 0, 0.5);
      background: rgba(0, 100, 0, 0.1);
      color: #88FF88;
      border-radius: 8px;
      font-size: 14px;
      transition: all 0.3s ease;
    }

    .form-section input::placeholder {
      color: rgba(136, 255, 136, 0.5);
    }

    .form-section input:focus {
      outline: none;
      border-color: #00FF00;
      background: rgba(0, 150, 0, 0.2);
      box-shadow: 0 0 10px rgba(0, 255, 0, 0.5);
    }

    .quick-amounts {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 8px;
      margin-top: 10px;
    }

    .amount-btn {
      padding: 8px;
      background: rgba(0, 255, 0, 0.2);
      border: 1px solid rgba(0, 255, 0, 0.4);
      color: #00FF00;
      border-radius: 6px;
      cursor: pointer;
      font-size: 12px;
      transition: all 0.3s ease;
    }

    .amount-btn:hover {
      background: rgba(0, 255, 0, 0.3);
      border-color: #00FF00;
    }

    .message-success {
      background: rgba(0, 255, 0, 0.2);
      border: 1px solid #00FF00;
      color: #00FF00;
      padding: 12px;
      border-radius: 8px;
      margin-bottom: 15px;
      font-size: 13px;
      display: none;
    }

    .message-success.show {
      display: block;
    }

    .message-error {
      background: rgba(255, 107, 107, 0.2);
      border: 1px solid #FF6B6B;
      color: #FF9999;
      padding: 12px;
      border-radius: 8px;
      margin-bottom: 15px;
      font-size: 13px;
      display: none;
    }

    .message-error.show {
      display: block;
    }

    .button-group {
      display: flex;
      gap: 10px;
      margin-top: 20px;
    }

    .btn {
      padding: 12px;
      border: none;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      letter-spacing: 1px;
      flex: 1;
      text-decoration: none;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .btn-topup {
      background: linear-gradient(135deg, #00FF00, #00CC00);
      color: #000000;
    }

    .btn-topup:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(0, 255, 0, 0.6);
    }

    .btn-back {
      background: rgba(0, 255, 0, 0.2);
      color: #00FF00;
      border: 1px solid #00FF00;
    }

    .btn-back:hover {
      background: rgba(0, 255, 0, 0.3);
      box-shadow: 0 4px 15px rgba(0, 255, 0, 0.4);
    }
  </style>
</head>
<body>
  <div class="topup-container">
    <div class="topup-header">
      <h1>💳 Top Up Saldo</h1>
      <p style="color: #88FF88; font-size: 12px;">Pilih metode pembayaran bank Anda</p>
    </div>

    <div class="user-info">
      <div class="info-row">
        <span>Pemain:</span>
        <strong style="color: #00FF00;"><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
      </div>
      <div class="info-row">
        <span>Saldo Saat Ini:</span>
        <span class="balance-display" id="topup-balance">Rp <?php echo number_format((int)$_SESSION['balance'], 0, ',', '.'); ?></span>
      </div>
    </div>

    <div class="topup-form">
      <?php if ($message): ?>
        <div class="message-<?php echo $message_type; ?> show">
          <?php echo htmlspecialchars($message); ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="topup.php">
        <div class="form-section">
          <label>🏦 Pilih Bank</label>
          <div class="bank-methods">
            <div class="bank-option">
              <input type="radio" id="bank-bca" name="bank_method" value="bca" required />
              <label for="bank-bca" class="bank-label">
                <div class="bank-icon">🏛️</div>
                <div>BCA</div>
              </label>
            </div>
            <div class="bank-option">
              <input type="radio" id="bank-mandiri" name="bank_method" value="mandiri" />
              <label for="bank-mandiri" class="bank-label">
                <div class="bank-icon">🏦</div>
                <div>Mandiri</div>
              </label>
            </div>
            <div class="bank-option">
              <input type="radio" id="bank-bni" name="bank_method" value="bni" />
              <label for="bank-bni" class="bank-label">
                <div class="bank-icon">🏤</div>
                <div>BNI</div>
              </label>
            </div>
            <div class="bank-option">
              <input type="radio" id="bank-cimb" name="bank_method" value="cimb" />
              <label for="bank-cimb" class="bank-label">
                <div class="bank-icon">💰</div>
                <div>CIMB Niaga</div>
              </label>
            </div>
            <div class="bank-option">
              <input type="radio" id="bank-ocbc" name="bank_method" value="ocbc" />
              <label for="bank-ocbc" class="bank-label">
                <div class="bank-icon">🏢</div>
                <div>OCBC NISP</div>
              </label>
            </div>
            <div class="bank-option">
              <input type="radio" id="bank-doku" name="bank_method" value="doku" />
              <label for="bank-doku" class="bank-label">
                <div class="bank-icon">📱</div>
                <div>e-Wallet</div>
              </label>
            </div>
          </div>
        </div>

        <div class="form-section">
          <label for="amount">💵 Jumlah Top Up</label>
          <input id="amount" name="amount" type="number" min="1" placeholder="Contoh: 50000" required />
          <div class="quick-amounts">
            <button type="button" class="amount-btn" onclick="setAmount(10000)">Rp 10K</button>
            <button type="button" class="amount-btn" onclick="setAmount(50000)">Rp 50K</button>
            <button type="button" class="amount-btn" onclick="setAmount(100000)">Rp 100K</button>
            <button type="button" class="amount-btn" onclick="setAmount(250000)">Rp 250K</button>
            <button type="button" class="amount-btn" onclick="setAmount(500000)">Rp 500K</button>
            <button type="button" class="amount-btn" onclick="setAmount(1000000)">Rp 1JT</button>
          </div>
        </div>

        <div class="button-group">
          <button type="submit" class="btn btn-topup">Lanjutkan Top Up</button>
          <a href="html.php" class="btn btn-back">Kembali</a>
        </div>
      </form>
    </div>
  </div>

  <script>
    function setAmount(amount) {
      document.getElementById('amount').value = amount;
    }

    // Update balance display on page load
    const serverBalance = <?php echo (int)$_SESSION['balance']; ?>;
    document.getElementById('topup-balance').textContent = 'Rp ' + serverBalance.toLocaleString('id-ID', {
      minimumFractionDigits: 0,
      maximumFractionDigits: 0
    });
    localStorage.setItem('playerBalance', serverBalance);

  </script>
</body>
</html>
