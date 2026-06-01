<?php
require_once('session.php');

$currentPass = $u['password'];
$metode = $_POST['metode'];
$nominal = preg_replace('/[^0-9\-]/', '', $_POST['nominal']);
$amount_str = str_replace('.', '', $nominal);
$amounts = (float) $amount_str;


$usersID = $_POST['userID'];
$password = mysqli_real_escape_string($conn, $_POST['password']);


$getBank = mysqli_query($conn, "SELECT * FROM `tb_bank` WHERE userID = '$usersID'") or die(mysqli_error($conn));
$gb = mysqli_fetch_array($getBank);

$catatan = $gb['akun'] . ' ' . $gb['no_rek'] . ' a/n ' . $gb['pemilik'];

$cekBalance = mysqli_query($conn, "SELECT * FROM `tb_balance` WHERE userID = '$usersID'") or die(mysqli_error($conn));
$cb = mysqli_fetch_array($cekBalance);
$saldoAktif = $cb['active'];

$unik = date('Hs');
$kode_unik = substr(str_shuffle('1234567890'), 0, 3);
$kd_transaksi = date('Ymds') . $kode_unik;

$created_date = date('Y-m-d H:i:s');


try {
    if (password_verify($password, $currentPass)) {
        if ($nominal < 10000) {
            throw new Exception('Penarikan minimal adalah Rp 10.000,-');
        }
        if ($saldoAktif < $nominal) {
            throw new Exception('Saldo tidak mencukupi untuk melakukan penarikan');
        }
        $insert_transaksi = mysqli_query($conn, "INSERT INTO `tb_transaksi` (`kd_transaksi`, `date`, `transaksi`, `total`, `saldo`, `note`, `gameid`, `providerID`, `jenis`, `metode`, `pay_from`, `userID`, `status`) VALUES ('$kd_transaksi','$created_date','Penarikan Dana','$nominal',0, '$catatan', '', '0','2','$metode','0','$usersID',0)") or die(mysqli_error($conn));
        $updateBalance = mysqli_query($conn, "UPDATE `tb_balance` SET `active` = active - '$nominal', `pending` = pending + '$nominal' WHERE userID = '$usersID'") or die(mysqli_error($conn));
        $withdraw = $WL->withdraw($users, $amounts);
        header('Location:../desktop/?notif=9'); // Notifikasi berhasil
        exit();
    } else {
        throw new Exception('Password tidak valid');
    }
} catch (Exception $e) {
    $errorMessage = urlencode($e->getMessage());
    header("Location:../m/account/withdrawal.php?notif=6&message=$errorMessage");
    exit();
}
?>
