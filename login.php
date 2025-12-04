<?php
session_start();
include "koneksi.php";

$error = '';

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    // Ambil data user
    $sql = "SELECT id, username, password, bank FROM users WHERE username = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {

        if ($password === $row['password']) {

            // SESSION BENAR
            $_SESSION['user_id']  = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['bank']     = intval($row['bank']);   // PENTING

            header("Location: html.php");
            exit;
        }
    }

    $error = "Invalid username or password.";
}
?>
