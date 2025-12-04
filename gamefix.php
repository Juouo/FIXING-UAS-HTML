<?php
session_start();
include "koneksi.php";

// =============================
// REQUIRE LOGIN
// =============================
if (!isset($_SESSION['user_id'])) {
    header("Location: loginn.php");
    exit;
}

// =============================
// AJAX HANDLER
// =============================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json; charset=utf-8');
    $user_id = intval($_SESSION['user_id']);
    $action = $_POST['action'];

    // ========= TOP UP ==========
    if ($action === 'topup') {
        $amount = intval($_POST['amount']);
        $bank_method = trim($_POST['bank_method']);

        if ($amount <= 0) {
            echo json_encode(['status'=>'error','message'=>'Jumlah top up tidak valid.']);
            exit;
        }

        // UPDATE SALDO (BANK)
        $sql = "UPDATE users SET bank = bank + ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $amount, $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // Ambil saldo baru
        $sql2 = "SELECT bank FROM users WHERE id = ?";
        $stmt2 = mysqli_prepare($conn, $sql2);
        mysqli_stmt_bind_param($stmt2, "i", $user_id);
        mysqli_stmt_execute($stmt2);
        $result = mysqli_stmt_get_result($stmt2);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt2);

        $_SESSION['bank'] = intval($row['bank']);

        echo json_encode([
            'status' => 'ok',
            'bank' => $_SESSION['bank'],
            'message' => 'Top up berhasil.'
        ]);
        exit;
    }

    // ========= SET BANK (saldo game win/lose) ===========
    if ($action === 'set_balance') {
        $newBank = intval($_POST['balance']);
        if ($newBank < 0) {
            echo json_encode(['status'=>'error','message'=>'Bank tidak valid']);
            exit;
        }

        $sql = "UPDATE users SET bank = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $newBank, $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        $_SESSION['bank'] = $newBank;

        echo json_encode(['status'=>'ok','bank'=>$newBank]);
        exit;
    }

    echo json_encode(['status'=>'error','message'=>'Action tidak dikenal']);
    exit;
}

// =============================
// LOAD USER DATA NORMAL
// =============================
$user_id = intval($_SESSION['user_id']);

$sql = "SELECT username, bank FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$user) {
    session_destroy();
    header("Location: loginn.php");
    exit;
}

$_SESSION['username'] = $user['username'];
$_SESSION['bank'] = intval($user['bank']);

$username = htmlspecialchars($_SESSION['username']);
$bank = intval($_SESSION['bank']);
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Blackjack [21]</title>
<link rel="stylesheet" href="cliff.css">
</head>
<body>

<div class="table">
    <header>
        <h1>Blackjack [21]</h1>

        <div class="controls">
            <div>
                Signed in as: <strong><?php echo $username; ?></strong>
                <button id="topup-btn">Top Up</button>
                <a href="logout.php" class="btn-danger">Logout</a>
            </div>

            <div style="margin-top:10px;">
                <div class="chip">Bank: Rp <span id="bank-display"><?php echo number_format($bank,0,',','.'); ?></span></div>
                <div class="chip">Taruhan: <span id="current-bet">0</span></div>
            </div>
        </div>
    </header>

    <main>
        <div id="game-area"></div>
    </main>

    <footer>OCA GameHub - Blackjack [21]</footer>
</div>

<!-- ========== MODAL TOP UP ========== -->
<div id="topup-modal" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close" id="close-topup">&times;</span>

        <h2>Top Up Saldo</h2>

        <div id="topup-msg" style="display:none;"></div>

        <p><strong>Pemain:</strong> <?php echo $username; ?></p>
        <p><strong>Saldo sekarang:</strong> Rp <span id="modal-bank"><?php echo number_format($bank,0,',','.'); ?></span></p>

        <form id="topup-form">
            <label>Metode:</label>
            <select id="bank-method">
                <option value="">-- pilih --</option>
                <option value="bca">BCA</option>
                <option value="bni">BNI</option>
                <option value="mandiri">Mandiri</option>
                <option value="cimb">CIMB</option>
                <option value="ewallet">E-Wallet</option>
            </select>

            <label>Jumlah (Rp):</label>
            <input type="number" id="topup-amount" min="1">

            <button type="submit">Top Up</button>
        </form>
    </div>
</div>

<script>
let bank = <?php echo $bank; ?>;

// Format rupiah
function formatIDR(n) {
    return Number(n).toLocaleString('id-ID');
}

// Update UI
function refreshBankUI() {
    document.getElementById("bank-display").innerText = formatIDR(bank);
    document.getElementById("modal-bank").innerText = formatIDR(bank);
}

// Modal
const modal = document.getElementById("topup-modal");
document.getElementById("topup-btn").onclick = () => modal.style.display = "flex";
document.getElementById("close-topup").onclick = () => modal.style.display = "none";

// ====== Top Up Submit ======
document.getElementById("topup-form").onsubmit = function(e){
    e.preventDefault();

    let amount = parseInt(document.getElementById("topup-amount").value);
    let method = document.getElementById("bank-method").value;

    if (!method) { alert("Pilih metode dulu"); return; }
    if (amount <= 0) { alert("Jumlah tidak valid"); return; }

    let data = new URLSearchParams();
    data.append("action","topup");
    data.append("amount",amount);
    data.append("bank_method",method);

    fetch("game.php", {
        method: "POST",
        headers: {"Content-Type":"application/x-www-form-urlencoded"},
        body: data.toString()
    })
    .then(r=>r.json())
    .then(res=>{
        if(res.status === "ok"){
            bank = res.bank;
            refreshBankUI();
            alert("Top up berhasil!");
            modal.style.display = "none";
        } else {
            alert(res.message);
        }
    });
};

// ====== Called from JS game (win/lose) ======
window.persistBalanceToServer = function(newBank){
    let data = new URLSearchParams();
    data.append("action","set_balance");
    data.append("balance",newBank);

    return fetch("game.php", {
        method:"POST",
        headers:{"Content-Type":"application/x-www-form-urlencoded"},
        body:data.toString()
    })
    .then(r=>r.json())
    .then(res=>{
        if(res.status === "ok"){
            bank = res.bank;
            refreshBankUI();
        }
    });
};

// untuk script game
window.getBank = () => bank;
window.setBank = (v) => { bank = v; refreshBankUI(); };
</script>

<script src="ody git.js"></script>

</body>
</html>