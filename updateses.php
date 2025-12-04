<?php
// update_session.php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success'=>false,'message'=>'Not logged in']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!$data || !isset($data['balance'])) {
    echo json_encode(['success'=>false,'message'=>'No balance provided']);
    exit;
}

$_SESSION['bank'] = intval($data['balance']);
echo json_encode(['success'=>true,'bank'=>$_SESSION['bank']]);
exit;
?>
