<?php
// login.php - PASTIKAN session_start() HANYA DI SINI
session_start();
require_once 'koneksi.php';

// Cek remember me (fungsi dari koneksi.php)
if (!isset($_SESSION['user_id']) && checkRememberMe()) {
    header('Location: dashboard.php');
    exit;
}

// Proses login form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);
    $remember = isset($_POST['remember']) ? true : false;
    
    // PERBAIKAN: Password seharusnya di-hash, tapi untuk demo pakai plain
    $result = mysqli_query($koneksi, "SELECT * FROM users WHERE username = '$username' AND password = '$password'");
    
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        
        // Jika remember me dicentang
        if ($remember) {
            setRememberMe($user['id'], $user['username']);
        }
        
        // Update last login
        mysqli_query($koneksi, "UPDATE users SET last_login = NOW() WHERE id = {$user['id']}");
        
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Username atau password salah';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - OcaGamingHub</title>
    <style>
        .remember-checkbox {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 15px 0;
        }
        body {
            font-family: Arial, sans-serif;
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background: #4CAF50;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
        }
        button:hover {
            background: #45a049;
        }
        .error {
            color: red;
            background: #ffe6e6;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <h2>🔐 Login - OcaGamingHub</h2>
    
    <?php if (isset($error)): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        
        <div class="remember-checkbox">
            <input type="checkbox" name="remember" id="remember">
            <label for="remember">📅 Ingat saya selama 30 hari</label>
        </div>
        
        <button type="submit">🚀 Login</button>
    </form>
    
    <p style="margin-top: 20px; text-align: center;">
        Belum punya akun? <a href="smart_setup.php">Buat di sini</a><br>
        <small>atau gunakan demo: <strong>Alex / 123</strong></small>
    </p>
</body>
</html>