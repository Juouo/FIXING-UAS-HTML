<?php
// login.php dengan remember me support
require_once 'koneksi.php'; // File yang sudah diupdate

// Cek remember me
if (!isset($_SESSION['user_id']) && checkRememberMe()) {
    header('Location: dashboard.php');
    exit;
}

// Proses login form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);
    $remember = isset($_POST['remember']) ? true : false;
    
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
    </style>
</head>
<body>
    <h2>Login</h2>
    <?php if (isset($error)) echo "<p style='color:red'>$error</p>"; ?>
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        
        <div class="remember-checkbox">
            <input type="checkbox" name="remember" id="remember">
            <label for="remember">Ingat saya selama 30 hari</label>
        </div>
        
        <button type="submit">Login</button>
    </form>
    
    <p>Belum punya akun? <a href="smart_setup.php">Buat di sini</a></p>
</body>
</html>