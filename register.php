<?php
session_start();
include "koneksi.php";

$error = '';
$success = '';

if (isset($_POST['register'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {

        $check = mysqli_prepare($conn, "SELECT id FROM users WHERE username=?");
        mysqli_stmt_bind_param($check, "s", $username);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);

        if (mysqli_stmt_num_rows($check) > 0) {
            $error = "Username already exists.";
        } else {

            // DAFTAR + BANK 1000
            $insert = mysqli_prepare(
                $conn,
                "INSERT INTO users (username, password, bank) VALUES (?, ?, 1000)"
            );
            mysqli_stmt_bind_param($insert, "ss", $username, $password);

            mysqli_stmt_execute($insert);

            // Auto login
            $_SESSION['user_id'] = mysqli_insert_id($conn);
            $_SESSION['username'] = $username;
            $_SESSION['bank'] = 1000;

            header("Location: html.php");
            exit;
        }
    }
}
?>
