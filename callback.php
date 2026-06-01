<?php
include('koneksi.php');
include('classes/class.exa.php');

$POST = file_get_contents("php://input");
$data = json_decode($POST, true);

if (!isset($data['terminal_id'], $data['trx_id'], $data['amount'], $data['custom_ref'], $data['created_at'], $data['status'])) {
    $result = array('success' => false, 'message' => 'Missing required fields');
    echo json_encode($result);
    exit;
}

$userqris = $data['terminal_id'];
$trxid = $data['trx_id'];
$amount = $data['amount'];
$custom_ref = $data['custom_ref'];
$created_at = $data['created_at'];
$status = $data['status'];

$userqris = mysqli_real_escape_string($conn, $userqris);
$trxid = mysqli_real_escape_string($conn, $trxid);
$amount = mysqli_real_escape_string($conn, $amount);
$status = mysqli_real_escape_string($conn, $status);
$amounts = (float)$amount;

$getuserID = mysqli_query($conn, "SELECT * FROM tb_user WHERE username = '$userqris'") or die(mysqli_error($conn));
$gu = mysqli_fetch_array($getuserID);
if (!$gu) {
    $result = array('success' => false, 'message' => 'User not found');
    echo json_encode($result);
    exit;
}
$userIDnya = $gu['cuid'];
$created_date = date('Y-m-d H:i:s');
$note = 'Topup QRIS Otomatis';
$kd_transaksi = 'QRIS'.$trxid;

if ($status == 'success') {
    $insert_transaksi = mysqli_query($conn, "INSERT INTO tb_transaksi (kd_transaksi, date, transaksi, total, saldo, note, gameid, providerID, jenis, metode, pay_from, userID, status) VALUES ('$kd_transaksi', '$created_date', 'Top Up QRIS', '$amount', '0', '$note', '0', '0', '1', '6', '0', '$userIDnya', 1)") or die(mysqli_error($conn));
    $deposit = $WL->deposit($userqris, $amounts);
    
    $updateBalance = mysqli_query($conn, "UPDATE tb_balance SET active = active + '$amount' WHERE userID = '$userIDnya'") or die(mysqli_error($conn));
    
if ($status == 'success') { $result = array('message' => 'Transaksi Sukses'); } else { $result = array('success' => false, 'message' => 'Invalid payment status'); }
}

header('Content-type: application/json');
echo json_encode($result);
?>