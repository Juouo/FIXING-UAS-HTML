<?php
session_start();
include "koneksi.php";

if (!isset($_SESSION['user_id'])) {
    header('Location: loginn.php');
    exit;
}

// AMBIL SALDO BANK DARI DATABASE
$query = "SELECT bank, username, local_storage_key FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

$currentBank = intval($data['bank']); // SALDO ASLI DARI DATABASE
$username = $data['username'];
$localStorageKey = $data['local_storage_key'] ?: 'blackjack_' . $_SESSION['user_id'] . '_' . time();

// Generate unique key untuk localStorage jika belum ada
if (!$data['local_storage_key']) {
    $updateKey = "UPDATE users SET local_storage_key = ? WHERE id = ?";
    $stmt2 = mysqli_prepare($conn, $updateKey);
    mysqli_stmt_bind_param($stmt2, "si", $localStorageKey, $_SESSION['user_id']);
    mysqli_stmt_execute($stmt2);
}

// Update session
$_SESSION['bank'] = $currentBank;
$_SESSION['local_storage_key'] = $localStorageKey;

// Update last login
$updateLogin = "UPDATE users SET last_login = NOW() WHERE id = ?";
$stmt3 = mysqli_prepare($conn, $updateLogin);
mysqli_stmt_bind_param($stmt3, "i", $_SESSION['user_id']);
mysqli_stmt_execute($stmt3);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UAS Blackjack - Game Bank</title>
    <link rel="stylesheet" href="login.css">
    <style>
        /* ============ SAVE SYSTEM STYLES ============ */
        .save-system {
            background: linear-gradient(135deg, #1a2a3a, #0d1520);
            color: white;
            padding: 15px;
            border-radius: 10px;
            margin: 20px 0;
            border: 2px solid #2d4256;
        }
        
        .save-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .save-status {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.9em;
        }
        
        .save-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #4CAF50;
            animation: pulse 2s infinite;
        }
        
        .save-dot.offline {
            background: #e74c3c;
            animation: none;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        
        .save-controls {
            display: flex;
            gap: 10px;
            margin-top: 10px;
            flex-wrap: wrap;
        }
        
        .save-btn {
            padding: 8px 15px;
            background: #2d4256;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9em;
            transition: all 0.3s;
        }
        
        .save-btn:hover {
            background: #3d5266;
            transform: translateY(-2px);
        }
        
        .save-btn.primary {
            background: #4CAF50;
        }
        
        .save-btn.warning {
            background: #f39c12;
        }
        
        .save-btn.danger {
            background: #e74c3c;
        }
        
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            background: #4CAF50;
            color: white;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            z-index: 1000;
            transform: translateX(120%);
            transition: transform 0.3s ease;
        }
        
        .notification.show {
            transform: translateX(0);
        }
        
        .notification.error {
            background: #e74c3c;
        }
        
        .notification.warning {
            background: #f39c12;
        }
        
        .notification.info {
            background: #3498db;
        }
        
        .sync-info {
            font-size: 0.8em;
            color: #aaa;
            margin-top: 5px;
        }
        
        /* ============ GAME STYLES ============ */
        .game-container {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
        }
        
        .game-section {
            flex: 2;
            min-width: 300px;
            background: rgba(255, 255, 255, 0.05);
            padding: 20px;
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .bank-section {
            flex: 1;
            min-width: 250px;
            background: rgba(255, 255, 255, 0.05);
            padding: 20px;
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .section-title {
            color: #4CAF50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #2d4256;
            font-size: 1.3em;
        }
        
        .balance-display {
            font-size: 2em;
            font-weight: bold;
            color: #4CAF50;
            text-align: center;
            margin: 20px 0;
            padding: 20px;
            background: rgba(0,0,0,0.3);
            border-radius: 10px;
        }
        
        .balance-display.low {
            color: #e74c3c;
        }
        
        .balance-display.high {
            color: #f39c12;
        }
    </style>
</head>
<body>
    <div class="save-system">
        <div class="save-header">
            <h3><i class="fas fa-save"></i> SISTEM PENYIMPANAN OTOMATIS</h3>
            <div class="save-status">
                <div class="save-dot" id="saveStatus"></div>
                <span id="saveText">Menyimpan...</span>
            </div>
        </div>
        <p style="color: #aaa; font-size: 0.9em; margin-bottom: 10px;">
            <i class="fas fa-info-circle"></i> Saldo otomatis tersimpan seperti Robux. Data aman meski browser ditutup!
        </p>
        <div class="save-controls">
            <button class="save-btn primary" onclick="manualSave()">
                <i class="fas fa-save"></i> Simpan Sekarang
            </button>
            <button class="save-btn" onclick="syncWithServer()">
                <i class="fas fa-sync"></i> Sync ke Server
            </button>
            <button class="save-btn warning" onclick="loadFromLocal()">
                <i class="fas fa-download"></i> Load dari Browser
            </button>
            <button class="save-btn danger" onclick="resetLocalData()">
                <i class="fas fa-redo"></i> Reset Local Data
            </button>
        </div>
        <div class="sync-info">
            Terakhir disimpan: <span id="lastSaveTime">-</span> | 
            Key: <span id="storageKey"><?= $localStorageKey ?></span>
        </div>
    </div>

    <div class="game-container">
        <div class="bank-section">
            <h2 class="section-title"><i class="fas fa-user"></i> INFO PLAYER</h2>
            <p>User: <b><?= htmlspecialchars($username) ?></b></p>
            <p>ID: <b>#<?= $_SESSION['user_id'] ?></b></p>
            
            <div class="balance-display" id="balanceDisplay">
                SALDO: Rp <?= number_format($currentBank, 0, ',', '.') ?>
            </div>
            
            <h3 class="section-title"><i class="fas fa-money-bill-wave"></i> TOP UP SALDO</h3>
            <form method="POST" action="topup.php" style="margin-top: 20px;">
                <label>Pilih Bank :</label><br>
                <input type="radio" name="bank_method" value="bca" required> BCA<br>
                <input type="radio" name="bank_method" value="bni" required> BNI<br>
                <input type="radio" name="bank_method" value="mandiri" required> Mandiri<br><br>

                <label>Jumlah Top Up:</label>
                <input type="number" name="amount" min="1000" max="10000000" required style="width: 100%; padding: 8px;"><br><br>

                <button type="submit" style="width: 100%; padding: 10px; background: #4CAF50; color: white; border: none; border-radius: 5px; cursor: pointer;">
                    <i class="fas fa-coins"></i> TOP UP SEKARANG
                </button>
            </form>
            
            <div style="margin-top: 20px; text-align: center;">
                <a href="logout.php" style="color: #e74c3c; text-decoration: none;">
                    <i class="fas fa-sign-out-alt"></i> LOGOUT
                </a>
            </div>
        </div>
        
        <div class="game-section">
            <h2 class="section-title"><i class="fas fa-gamepad"></i> BLACKJACK GAME</h2>
            
            <!-- Hidden inputs untuk PHP data -->
            <input type="hidden" id="php-balance" value="<?= $currentBank ?>">
            <input type="hidden" id="php-userid" value="<?= $_SESSION['user_id'] ?>">
            <input type="hidden" id="php-storage-key" value="<?= $localStorageKey ?>">
            <input type="hidden" id="php-username" value="<?= htmlspecialchars($username) ?>">
            
            <!-- Game UI akan diisi oleh JavaScript -->
            <div id="gameArea">
                <div style="text-align: center; padding: 50px;">
                    <h3>Loading game...</h3>
                    <p>Harap tunggu sebentar</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Notification Element -->
    <div class="notification" id="notification">
        Data tersimpan!
    </div>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <script>
        // ============ SAVE SYSTEM VARIABLES ============
        const LOCAL_STORAGE_KEY = '<?= $localStorageKey ?>';
        const USER_ID = <?= $_SESSION['user_id'] ?>;
        let currentBalance = <?= $currentBank ?>;
        let lastSaveTime = null;
        let autoSaveInterval = null;
        let isOnline = navigator.onLine;
        
        // ============ SAVE SYSTEM FUNCTIONS ============
        
        // Load data dari localStorage
        function loadFromLocalStorage() {
            try {
                const savedData = localStorage.getItem(LOCAL_STORAGE_KEY);
                if (savedData) {
                    const gameData = JSON.parse(savedData);
                    
                    // Check data version and timestamp
                    const oneDay = 24 * 60 * 60 * 1000;
                    const now = new Date().getTime();
                    const saveTime = new Date(gameData.lastSave).getTime();
                    
                    // Jika data lebih dari 7 hari, minta konfirmasi
                    if ((now - saveTime) > (7 * oneDay)) {
                        if (!confirm('Data di browser sudah lama (lebih dari 7 hari). Tetap gunakan?')) {
                            return null;
                        }
                    }
                    
                    console.log('✅ Data loaded from localStorage:', gameData);
                    return gameData;
                }
            } catch (error) {
                console.error('Error loading from localStorage:', error);
            }
            return null;
        }
        
        // Save data ke localStorage
        function saveToLocalStorage(balance, additionalData = {}) {
            const gameData = {
                balance: balance,
                userId: USER_ID,
                lastSave: new Date().toISOString(),
                version: '2.0',
                ...additionalData
            };
            
            localStorage.setItem(LOCAL_STORAGE_KEY, JSON.stringify(gameData));
            lastSaveTime = gameData.lastSave;
            updateSaveDisplay();
            
            console.log('💾 Data saved to localStorage:', gameData);
            return gameData;
        }
        
        // Sync dengan server
        async function syncWithServer() {
            try {
                const response = await fetch('sync_balance.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        userId: USER_ID,
                        balance: currentBalance,
                        action: 'sync'
                    })
                });
                
                const data = await response.json();
                if (data.success) {
                    showNotification('✅ Sync berhasil!', 'success');
                    updateBalanceDisplay(data.balance);
                } else {
                    showNotification('Sync gagal: ' + data.message, 'error');
                }
            } catch (error) {
                console.error('Sync error:', error);
                showNotification('Gagal sync. Periksa koneksi.', 'error');
            }
        }
        
        // Update balance dan simpan otomatis
        function updateBalance(newBalance, source = 'game') {
            const oldBalance = currentBalance;
            currentBalance = newBalance;
            
            // Update display
            updateBalanceDisplay(newBalance);
            
            // Auto-save ke localStorage
            saveToLocalStorage(newBalance, { 
                source: source,
                oldBalance: oldBalance,
                timestamp: Date.now()
            });
            
            // Auto-sync ke server setiap 5 perubahan atau jika selisih besar
            if (Math.abs(newBalance - oldBalance) >= 10000) {
                syncWithServer();
            }
            
            return newBalance;
        }
        
        // Manual save
        function manualSave() {
            const savedData = saveToLocalStorage(currentBalance, { manual: true });
            syncWithServer();
            showNotification('✅ Data berhasil disimpan!', 'success');
        }
        
        // Load dari localStorage
        function loadFromLocal() {
            const savedData = loadFromLocalStorage();
            if (savedData) {
                if (confirm(`Load saldo dari browser: Rp ${savedData.balance.toLocaleString()}?`)) {
                    updateBalance(savedData.balance, 'local');
                    showNotification('✅ Data dimuat dari browser!', 'success');
                }
            } else {
                showNotification('Tidak ada data tersimpan di browser.', 'warning');
            }
        }
        
        // Reset local data
        function resetLocalData() {
            if (confirm('Hapus semua data di browser ini? Game tetap aman di server.')) {
                localStorage.removeItem(LOCAL_STORAGE_KEY);
                showNotification('Data browser direset!', 'info');
                updateSaveDisplay();
            }
        }
        
        // Update display
        function updateBalanceDisplay(balance = currentBalance) {
            const balanceElement = document.getElementById('balanceDisplay');
            if (balanceElement) {
                balanceElement.innerHTML = `SALDO: Rp ${balance.toLocaleString('id-ID')}`;
                
                // Warna berdasarkan saldo
                if (balance < 10000) {
                    balanceElement.className = 'balance-display low';
                } else if (balance > 100000) {
                    balanceElement.className = 'balance-display high';
                } else {
                    balanceElement.className = 'balance-display';
                }
            }
            
            // Update hidden input untuk game
            document.getElementById('php-balance').value = balance;
        }
        
        function updateSaveDisplay() {
            const saveStatus = document.getElementById('saveStatus');
            const saveText = document.getElementById('saveText');
            const lastSaveElement = document.getElementById('lastSaveTime');
            
            if (lastSaveTime) {
                const date = new Date(lastSaveTime);
                const timeString = date.toLocaleTimeString('id-ID', { 
                    hour: '2-digit', 
                    minute: '2-digit',
                    second: '2-digit'
                });
                lastSaveElement.textContent = timeString;
                
                saveStatus.className = 'save-dot';
                saveText.textContent = 'Tersimpan';
            } else {
                lastSaveElement.textContent = '-';
                saveStatus.className = 'save-dot offline';
                saveText.textContent = 'Belum disimpan';
            }
        }
        
        // Notification system
        function showNotification(text, type = 'success') {
            const notification = document.getElementById('notification');
            notification.textContent = text;
            notification.className = 'notification';
            notification.classList.add(type);
            notification.classList.add('show');
            
            setTimeout(() => {
                notification.classList.remove('show');
            }, 3000);
        }
        
        // Check online status
        function updateOnlineStatus() {
            isOnline = navigator.onLine;
            const statusElement = document.getElementById('saveStatus');
            const textElement = document.getElementById('saveText');
            
            if (isOnline) {
                statusElement.className = 'save-dot';
                textElement.textContent = 'Online - Tersimpan';
            } else {
                statusElement.className = 'save-dot offline';
                textElement.textContent = 'Offline - Simpan lokal';
            }
        }
        
        // ============ AUTO-SAVE SYSTEM ============
        
        function startAutoSave() {
            // Auto-save setiap 30 detik
            autoSaveInterval = setInterval(() => {
                saveToLocalStorage(currentBalance, { auto: true });
                
                // Sync ke server setiap 2 menit jika online
                if (isOnline && Math.random() < 0.25) { // 25% chance setiap 30 detik
                    syncWithServer();
                }
            }, 30000);
            
            console.log('Auto-save system started');
        }
        
        // ============ EVENT LISTENERS ============
        
        window.addEventListener('online', updateOnlineStatus);
        window.addEventListener('offline', updateOnlineStatus);
        
        window.addEventListener('beforeunload', () => {
            // Final save sebelum tutup
            saveToLocalStorage(currentBalance, { beforeunload: true });
            
            // Try to sync one last time
            if (isOnline && navigator.sendBeacon) {
                navigator.sendBeacon('sync_balance.php', 
                    JSON.stringify({ userId: USER_ID, balance: currentBalance, action: 'final' })
                );
            }
        });
        
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                // Tab tidak aktif, save data
                saveToLocalStorage(currentBalance, { tabHidden: true });
            } else {
                // Tab aktif kembali, cek sync
                updateOnlineStatus();
            }
        });
        
        // ============ INITIALIZATION ============
        
        document.addEventListener('DOMContentLoaded', () => {
            // Load saved data
            const savedData = loadFromLocalStorage();
            if (savedData) {
                // Gunakan saldo dari localStorage jika lebih baru
                const serverTime = new Date('<?= date('Y-m-d H:i:s') ?>').getTime();
                const localTime = new Date(savedData.lastSave).getTime();
                
                if (localTime > serverTime - 60000) { // Jika data lokal lebih baru dari 1 menit
                    currentBalance = savedData.balance;
                    showNotification(`Selamat datang kembali! Saldo: Rp ${currentBalance.toLocaleString()}`, 'success');
                } else {
                    // Sync dengan server
                    syncWithServer();
                }
            }
            
            // Update displays
            updateBalanceDisplay();
            updateSaveDisplay();
            updateOnlineStatus();
            
            // Start auto-save
            startAutoSave();
            
            // Load game script
            loadGameScript();
            
            console.log('Save system initialized for user:', USER_ID);
        });
        
        // Load game JavaScript
        function loadGameScript() {
            const script = document.createElement('script');
            script.src = 'game.js?v=' + Date.now();
            script.onload = () => {
                console.log('Game script loaded');
                initializeGame();
            };
            document.head.appendChild(script);
        }
        
        // Function untuk diakses oleh game
        function initializeGame() {
            if (typeof window.initializeBlackjack === 'function') {
                window.initializeBlackjack({
                    initialBalance: currentBalance,
                    userId: USER_ID,
                    updateBalanceCallback: updateBalance,
                    getBalance: () => currentBalance
                });
            }
        }
        
        // Expose functions untuk game
        window.saveSystem = {
            updateBalance,
            getBalance: () => currentBalance,
            manualSave,
            syncWithServer,
            loadFromLocal,
            showNotification
        };
    </script>
</body>
</html>