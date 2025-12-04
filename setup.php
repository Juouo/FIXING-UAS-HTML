<?php
// setup_backend.php - Backend untuk semua operasi
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$response = ['success' => false, 'message' => ''];

// Koneksi database
$conn = new mysqli('localhost', 'root', '');

if ($conn->connect_error) {
    $response['message'] = 'Koneksi MySQL gagal: ' . $conn->connect_error;
    echo json_encode($response);
    exit;
}

switch ($action) {
    case 'setup':
        setupDatabase($conn);
        break;
        
    case 'create_user':
        createUser($conn);
        break;
        
    case 'get_users':
        getUsers($conn);
        break;
        
    default:
        $response['message'] = 'Action tidak valid';
        echo json_encode($response);
}

$conn->close();

// ==================== FUNGSI ====================

function setupDatabase($conn) {
    global $response;
    
    $dbname = 'ocagaminghub_bank_fix';
    
    // 1. Buat database
    if (!$conn->query("CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci")) {
        $response['message'] = 'Gagal buat database: ' . $conn->error;
        echo json_encode($response);
        return;
    }
    
    $conn->select_db($dbname);
    
    // 2. Buat tabel users dengan remember_token
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        bank INT NOT NULL DEFAULT 1000,
        remember_token VARCHAR(64) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_login TIMESTAMP NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if (!$conn->query($sql)) {
        $response['message'] = 'Gagal buat tabel users: ' . $conn->error;
        echo json_encode($response);
        return;
    }
    
    // 3. Insert user default
    $default_users = [
        ['admin', password_hash('admin123', PASSWORD_DEFAULT), 5000],
        ['Alex', '123', 500],
        ['Ody', '123', 1000],
        ['cliff', '123', 0]
    ];
    
    foreach ($default_users as $user) {
        $username = $conn->real_escape_string($user[0]);
        $password = $conn->real_escape_string($user[1]);
        $bank = (int)$user[2];
        
        // Check if exists
        $check = $conn->query("SELECT id FROM users WHERE username = '$username'");
        if ($check->num_rows == 0) {
            $conn->query("INSERT INTO users (username, password, bank) 
                         VALUES ('$username', '$password', $bank)");
        }
    }
    
    // 4. Buat tabel transactions
    $sql2 = "CREATE TABLE IF NOT EXISTS transactions (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        type ENUM('deposit','withdraw','transfer','bonus') NOT NULL,
        amount INT NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $conn->query($sql2);
    
    // 5. Update koneksi.php
    $koneksi_content = '<?php
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
    
    global $koneksi;
    $token_esc = mysqli_real_escape_string($koneksi, $token);
    mysqli_query($koneksi, "UPDATE users SET remember_token = \"$token_esc\" WHERE id = $user_id");
}

function checkRememberMe() {
    if (isset($_COOKIE[\'remember_token\']) && isset($_COOKIE[\'remember_user\'])) {
        global $koneksi;
        $token = mysqli_real_escape_string($koneksi, $_COOKIE[\'remember_token\']);
        $username = mysqli_real_escape_string($koneksi, $_COOKIE[\'remember_user\']);
        
        $result = mysqli_query($koneksi, "SELECT * FROM users WHERE username = \"$username\" AND remember_token = \"$token\"");
        if (mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);
            $_SESSION[\'user_id\'] = $user[\'id\'];
            $_SESSION[\'username\'] = $user[\'username\'];
            return true;
        }
    }
    return false;
}

// Cek remember me saat halaman dimuat
if (!isset($_SESSION[\'user_id\']) && checkRememberMe()) {
    // Otomatis login jika remember me valid
}
?>';
    
    file_put_contents('koneksi.php', $koneksi_content);
    
    $response['success'] = true;
    $response['message'] = 'Database setup berhasil!';
    $response['dbname'] = $dbname;
    $response['tables'] = ['users', 'transactions'];
    $response['users'] = ['admin', 'Alex', 'Ody', 'cliff'];
    
    echo json_encode($response);
}

function createUser($conn) {
    global $response;
    
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $bank = (int)($_POST['bank'] ?? 1000);
    $remember = isset($_POST['remember']) ? true : false;
    
    if (empty($username) || empty($password)) {
        $response['message'] = 'Username dan password harus diisi';
        echo json_encode($response);
        return;
    }
    
    $conn->select_db('ocagaminghub_bank_fix');
    
    // Check if user exists
    $check = $conn->query("SELECT id FROM users WHERE username = '" . $conn->real_escape_string($username) . "'");
    if ($check->num_rows > 0) {
        $response['message'] = 'Username sudah digunakan';
        echo json_encode($response);
        return;
    }
    
    // Insert user baru
    $username_esc = $conn->real_escape_string($username);
    $password_esc = $conn->real_escape_string($password); // Dalam real app, gunakan password_hash()
    
    $sql = "INSERT INTO users (username, password, bank) VALUES ('$username_esc', '$password_esc', $bank)";
    
    if ($conn->query($sql)) {
        $user_id = $conn->insert_id;
        
        // Jika remember me dipilih, generate token
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $conn->query("UPDATE users SET remember_token = '$token' WHERE id = $user_id");
        }
        
        $response['success'] = true;
        $response['message'] = 'User berhasil dibuat';
        $response['username'] = $username;
        $response['password'] = $password;
        $response['bank'] = $bank;
        $response['remember'] = $remember;
        $response['user_id'] = $user_id;
    } else {
        $response['message'] = 'Gagal membuat user: ' . $conn->error;
    }
    
    echo json_encode($response);
}

function getUsers($conn) {
    global $response;
    
    $conn->select_db('ocagaminghub_bank_fix');
    
    $result = $conn->query("SELECT username, bank FROM users ORDER BY created_at DESC");
    
    if ($result) {
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        
        $response['success'] = true;
        $response['users'] = $users;
    } else {
        $response['message'] = 'Belum ada user. Jalankan setup dulu.';
    }
    
    echo json_encode($response);
}