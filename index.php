<?php
// Mulai sesi
session_start();

// Jika user belum login, arahkan ke halaman login
if (!isset($_SESSION['user_id'])) {
    header("Location: loginn.php");
    exit;
}

// Jika login sudah benar, langsung masuk ke halaman utama
header("Location: html.php");
exit;
?>