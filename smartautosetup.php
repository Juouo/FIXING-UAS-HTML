<?php
// smart_setup.php - Setup Database + Fitur Register & Remember Me
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Konfigurasi
$config = [
    'host' => 'localhost',
    'user' => 'root',
    'pass' => '',
    'dbname' => 'ocagaminghub_bank_fix',
    'site_name' => 'OcaGamingHub Bank',
    'default_bank' => 1000
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🚀 Smart Setup - <?php echo $config['site_name']; ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 800px;
            overflow: hidden;
        }
        .header {
            background: linear-gradient(to right, #4facfe 0%, #00f2fe 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 { font-size: 2.5em; margin-bottom: 10px; }
        .header p { opacity: 0.9; }
        .content {
            padding: 30px;
        }
        .tab-buttons {
            display: flex;
            margin-bottom: 30px;
            border-bottom: 2px solid #eee;
        }
        .tab-btn {
            padding: 15px 30px;
            background: none;
            border: none;
            font-size: 16px;
            font-weight: 600;
            color: #666;
            cursor: pointer;
            transition: all 0.3s;
            border-bottom: 3px solid transparent;
        }
        .tab-btn:hover { color: #4facfe; }
        .tab-btn.active {
            color: #4facfe;
            border-bottom: 3px solid #4facfe;
        }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        
        .setup-log {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            max-height: 300px;
            overflow-y: auto;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            line-height: 1.6;
        }
        .log-success { color: #28a745; }
        .log-error { color: #dc3545; }
        .log-info { color: #17a2b8; }
        
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: border 0.3s;
        }
        .form-control:focus {
            border-color: #4facfe;
            outline: none;
            box-shadow: 0 0 0 3px rgba(79, 172, 254, 0.2);
        }
        .btn {
            padding: 14px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        .btn-primary {
            background: linear-gradient(to right, #4facfe 0%, #00f2fe 100%);
            color: white;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(79, 172, 254, 0.3);
        }
        .btn-success {
            background: linear-gradient(to right, #42e695 0%, #3bb2b8 100%);
            color: white;
        }
        .user-badge {
            display: inline-block;
            background: #e9ecef;
            padding: 8px 15px;
            border-radius: 50px;
            margin: 5px;
            font-size: 14px;
        }
        .success-box {
            background: linear-gradient(to right, #d4edda, #c3e6cb);
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 <?php echo $config['site_name']; ?></h1>
            <p>Smart Database Setup + Custom User System</p>
        </div>
        
        <div class="content">
            <div class="tab-buttons">
                <button class="tab-btn active" onclick="openTab('setup')">🔧 Auto Setup</button>
                <button class="tab-btn" onclick="openTab('create')">👤 Buat User</button>
                <button class="tab-btn" onclick="openTab('users')">📋 List User</button>
                <button class="tab-btn" onclick="openTab('config')">⚙️ Config</button>
            </div>
            
            <!-- TAB 1: AUTO SETUP -->
            <div id="setup" class="tab-content active">
                <h2>🔧 Auto Setup Database</h2>
                <p>Klik tombol di bawah untuk setup database otomatis:</p>
                
                <div class="setup-log" id="setupLog">
                    <!-- Log akan muncul di sini -->
                </div>
                
                <button class="btn btn-primary" onclick="runSetup()">
                    <span>🚀 Jalankan Auto Setup</span>
                </button>
                
                <div id="setupResult" class="success-box" style="display: none;">
                    <!-- Hasil setup muncul di sini -->
                </div>
            </div>
            
            <!-- TAB 2: BUAT USER -->
            <div id="create" class="tab-content">
                <h2>👤 Buat User Baru</h2>
                <p>Buat user dengan username dan password bebas:</p>
                
                <form id="createUserForm">
                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input type="text" id="username" class="form-control" placeholder="Masukkan username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" class="form-control" placeholder="Masukkan password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="bank_amount">Saldo Awal Bank:</label>
                        <input type="number" id="bank_amount" class="form-control" value="1000" min="0">
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="remember_me"> 
                            Ingat saya (Remember Me Cookie)
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-success">
                        <span>➕ Buat User</span>
                    </button>
                </form>
                
                <div id="userResult" style="margin-top: 20px;"></div>
            </div>
            
            <!-- TAB 3: LIST USER -->
            <div id="users" class="tab-content">
                <h2>📋 Daftar User Tersedia</h2>
                <div id="userList">
                    <!-- List user akan dimuat di sini -->
                </div>
                <button class="btn btn-primary" onclick="loadUsers()">
                    <span>🔄 Refresh List</span>
                </button>
            </div>
            
            <!-- TAB 4: CONFIG -->
            <div id="config" class="tab-content">
                <h2>⚙️ Konfigurasi System</h2>
                <p>Setelah setup selesai, copy kode ini ke file <code>koneksi.php</code>:</p>
                
                <textarea class="form-control" rows="10" readonly onclick="this.select()">
<?php
echo htmlspecialchars('<?php
// KONEKSI DATABASE DENGAN REMEMBER ME SUPPORT
session_start();

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "ocagaminghub_bank_fix";

$koneksi = mysqli_connect($host, $user, $pass, $dbname);
if (!$koneksi) die("Koneksi gagal: " . mysqli_connect_error());
mysqli_set_charset($koneksi, "utf8mb4");

// FUNGSI REMEMBER ME
function setRememberMe($user_id, $username) {
    $token = bin2hex(random_bytes(32));
    $expiry = time() + (30 * 24 * 60 * 60); // 30 hari
    
    setcookie("remember_token", $token, $expiry, "/");
    setcookie("remember_user", $username, $expiry, "/");
    
    // Simpan token ke database (tambah kolom remember_token di tabel users)
    global $koneksi;
    mysqli_query($koneksi, "UPDATE users SET remember_token = \'$token\' WHERE id = $user_id");
}

function checkRememberMe() {
    if (isset($_COOKIE[\'remember_token\']) && isset($_COOKIE[\'remember_user\'])) {
        global $koneksi;
        $token = mysqli_real_escape_string($koneksi, $_COOKIE[\'remember_token\']);
        $username = mysqli_real_escape_string($koneksi, $_COOKIE[\'remember_user\']);
        
        $result = mysqli_query($koneksi, "SELECT * FROM users WHERE username = \'$username\' AND remember_token = \'$token\'");
        if (mysqli_num_rows($result) == 1) {
            $_SESSION[\'user_id\'] = mysqli_fetch_assoc($result)[\'id\'];
            $_SESSION[\'username\'] = $username;
            return true;
        }
    }
    return false;
}
?>');
?>
                </textarea>
                
                <div style="margin-top: 20px;">
                    <a href="login.php" class="btn btn-primary" style="text-decoration: none;">
                        <span>🔗 Ke Halaman Login</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Tab Navigation
    function openTab(tabName) {
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.remove('active');
        });
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        
        document.getElementById(tabName).classList.add('active');
        event.currentTarget.classList.add('active');
    }

    // 1. AUTO SETUP
    async function runSetup() {
        const log = document.getElementById('setupLog');
        const resultDiv = document.getElementById('setupResult');
        
        log.innerHTML = '<div class="log-info">🔄 Memulai setup database...</div>';
        
        try {
            const response = await fetch('setup_backend.php?action=setup');
            const data = await response.json();
            
            if (data.success) {
                log.innerHTML += `<div class="log-success">✅ ${data.message}</div>`;
                resultDiv.innerHTML = `
                    <h3>✅ Setup Berhasil!</h3>
                    <p>Database: <strong>${data.dbname}</strong></p>
                    <p>Tabel dibuat: <strong>${data.tables.join(', ')}</strong></p>
                    <p>User default: <strong>${data.users.join(', ')}</strong></p>
                    <p>Password default: <strong>123</strong></p>
                    <a href="login.php" style="color: #155724; font-weight: bold;">➡️ Klik untuk Login</a>
                `;
                resultDiv.style.display = 'block';
                loadUsers(); // Refresh user list
            } else {
                log.innerHTML += `<div class="log-error">❌ ${data.message}</div>`;
            }
        } catch (error) {
            log.innerHTML += `<div class="log-error">❌ Error: ${error.message}</div>`;
        }
    }

    // 2. CREATE USER
    document.getElementById('createUserForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;
        const bank = document.getElementById('bank_amount').value;
        const remember = document.getElementById('remember_me').checked;
        
        const resultDiv = document.getElementById('userResult');
        resultDiv.innerHTML = '<div class="log-info">🔄 Membuat user...</div>';
        
        try {
            const formData = new FormData();
            formData.append('username', username);
            formData.append('password', password);
            formData.append('bank', bank);
            formData.append('remember', remember);
            
            const response = await fetch('setup_backend.php?action=create_user', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                resultDiv.innerHTML = `
                    <div class="success-box">
                        <h4>✅ User Berhasil Dibuat!</h4>
                        <p>Username: <strong>${data.username}</strong></p>
                        <p>Password: <strong>${data.password}</strong></p>
                        <p>Saldo Bank: <strong>${data.bank} coins</strong></p>
                        <p>Remember Me: <strong>${data.remember ? 'AKTIF' : 'TIDAK AKTIF'}</strong></p>
                        <hr>
                        <a href="login.php" style="color: #155724; font-weight: bold;">➡️ Login dengan user ini</a>
                    </div>
                `;
                
                // Reset form
                document.getElementById('createUserForm').reset();
                loadUsers(); // Refresh list
            } else {
                resultDiv.innerHTML = `<div class="log-error">❌ ${data.message}</div>`;
            }
        } catch (error) {
            resultDiv.innerHTML = `<div class="log-error">❌ Error: ${error.message}</div>`;
        }
    });

    // 3. LOAD USERS
    async function loadUsers() {
        const userList = document.getElementById('userList');
        userList.innerHTML = '<div class="log-info">🔄 Memuat daftar user...</div>';
        
        try {
            const response = await fetch('setup_backend.php?action=get_users');
            const data = await response.json();
            
            if (data.success) {
                let html = '<div style="display: flex; flex-wrap: wrap; gap: 10px;">';
                data.users.forEach(user => {
                    html += `
                        <div class="user-badge">
                            <strong>${user.username}</strong><br>
                            <small>Bank: ${user.bank} coins</small>
                        </div>
                    `;
                });
                html += '</div>';
                userList.innerHTML = html;
            } else {
                userList.innerHTML = `<div class="log-error">${data.message}</div>`;
            }
        } catch (error) {
            userList.innerHTML = `<div class="log-error">Error: ${error.message}</div>`;
        }
    }

    // Load users on page load
    document.addEventListener('DOMContentLoaded', loadUsers);
    </script>
</body>
</html>