<?php
ob_start();
session_start();
date_default_timezone_set("Asia/Jakarta");
include('config/koneksi.php');
include('config/class_telo.php');

$sid = session_id();

$user = mysqli_query($conn, "SELECT * FROM `tb_user` WHERE username = '" . $_SESSION['user'] . "'") or die(mysqli_error());
$u = mysqli_fetch_array($user);

$users = $u['username'];
$id_user = $u['cuid'];
$userID = $u['cuid'];

$balance = $WL->getBalanceUser($users);
$user_balance = $balance['user_list']['0']['user_balance'];
$update_balance_query = "UPDATE tb_balance SET active = '$user_balance' WHERE userID = '$userID'";
$result = mysqli_query($conn, $update_balance_query);

if ($result) {
    // Format jumlah saldo dalam bentuk mata uang rupiah
    $formatted_balance =  number_format($user_balance, 0, ',', '.');
    echo $formatted_balance;
} else {
    echo "Terjadi kesalahan dalam memperbarui saldo ke dalam tabel tb_balance: " . mysqli_error($conn);
}
?>
