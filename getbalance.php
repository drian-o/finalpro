<?php
include('/desktop/session.php');
error_reporting(0);
$userID = $u['cuid'];
$getLastBalance = mysqli_query($conn,"SELECT * FROM `tb_balance` WHERE userID = '$userID'") or die(mysqli_error());
$glb = mysqli_fetch_array($getLastBalance);
echo 'IDR ' . number_format($glb['active'], 0, ',', '.'); // Menggunakan number_format untuk menampilkan jumlah dalam bentuk rupiah
?>
