<?php
// koneksi.php - TANPA session_start() di sini
// Session akan dimulai di file yang memanggil koneksi.php

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "ocagaminghub_bank_fix";

$koneksi = mysqli_connect($host, $user, $pass, $dbname);
if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
mysqli_set_charset($koneksi, "utf8mb4");

// ==================== FUNGSI REMEMBER ME ====================

/**
 * Set Remember Me Cookie
 */
function setRememberMe($user_id, $username) {
    global $koneksi;
    
    // Generate token acak
    $token = bin2hex(random_bytes(32));
    $expiry = time() + (30 * 24 * 60 * 60); // 30 hari
    
    // Set cookie
    setcookie("remember_token", $token, $expiry, "/");
    setcookie("remember_user", $username, $expiry, "/");
    
    // Simpan token ke database
    $token_esc = mysqli_real_escape_string($koneksi, $token);
    mysqli_query($koneksi, "UPDATE users SET remember_token = '$token_esc' WHERE id = $user_id");
}

/**
 * Cek Remember Me Cookie
 */
function checkRememberMe() {
    if (isset($_COOKIE['remember_token']) && isset($_COOKIE['remember_user'])) {
        global $koneksi;
        
        $token = mysqli_real_escape_string($koneksi, $_COOKIE['remember_token']);
        $username = mysqli_real_escape_string($koneksi, $_COOKIE['remember_user']);
        
        $result = mysqli_query($koneksi, 
            "SELECT * FROM users 
             WHERE username = '$username' 
             AND remember_token = '$token' 
             LIMIT 1");
        
        if ($result && mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            return true;
        }
    }
    return false;
}

/**
 * Hapus Remember Me Cookie (logout)
 */
function clearRememberMe() {
    if (isset($_COOKIE['remember_token'])) {
        setcookie("remember_token", "", time() - 3600, "/");
    }
    if (isset($_COOKIE['remember_user'])) {
        setcookie("remember_user", "", time() - 3600, "/");
    }
}
?>