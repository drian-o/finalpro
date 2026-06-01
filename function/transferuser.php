<?php
require_once('session.php');

$usernamenya = $u['user'];
$usersID = $u['cuid'];
$currentPass = $u['pass'];
$tujuan = $_POST['tujuan'];
$nominal = preg_replace('/[^0-9\-]/','',$_POST['nominal']);
$password = mysqli_real_escape_string($conn,$_POST['password']);

$catatan = 'Transfer Balance Rp. '.$_POST['nominal'].' Ke '.$tujuan;
$catatan2 = 'Transfer Balance Rp. '.$_POST['nominal'].' Dari '.$usernamenya;

$cekBalance = mysqli_query($conn,"SELECT * FROM `tb_balance` WHERE userID = '$usersID'") or die(mysqli_error());
$cb = mysqli_fetch_array($cekBalance);
$saldoAktif = $cb['active'];

$unik = date('Hs');
$kode_unik = substr(str_shuffle(1234567890),0,3);
$kd_transaksi = date('Ymds').$kode_unik;

$created_date = date('Y-m-d H:i:s');

if(password_verify($password,$currentPass)){
    if($nominal < 50000){
        header('Location:../transfer/?notif=2');
        exit();
    }
    else {
        if($saldoAktif < $nominal){
            header('Location:../transfer/?notif=4');
            exit();
        }
        else {
            $cekTransaksi = mysqli_query($conn,"SELECT * FROM `tb_user` WHERE user = '$tujuan'") or die(mysqli_error());
            $ct = mysqli_num_rows($cekTransaksi);
            if($ct == 0){
                header('Location:../transfer/?notif=5');
                exit();
            }
            else {
                $ctt = mysqli_fetch_array($cekTransaksi);
                $tujuanID = $ctt['cuid'];
                $insert_transaksi = mysqli_query($conn,"INSERT INTO `tb_transaksi` (`kd_transaksi`, `date`, `transaksi`, `total`, `saldo`, `note`, `gameid`, `providerID`, `jenis`, `metode`, `pay_from`, `userID`, `status`) VALUES ('$kd_transaksi','$created_date','Transfer Balance','$nominal',0, '$catatan', '', '0','2','0','0','$usersID',0)") or die(mysqli_error());
                $updateBalance = mysqli_query($conn,"UPDATE `tb_balance` SET `active` = active - '$nominal', `payout` = payout + '$nominal' WHERE userID = '$usersID'") or die(mysqli_error());
                
                $insert_transaksi1 = mysqli_query($conn,"INSERT INTO `tb_transaksi` (`kd_transaksi`, `date`, `transaksi`, `total`, `saldo`, `note`, `gameid`, `providerID`, `jenis`, `metode`, `pay_from`, `userID`, `status`) VALUES ('$kd_transaksi','$created_date','Transfer Balance','$nominal',0, '$catatan2', '', '0','7','$usersID','0','$tujuanID',0)") or die(mysqli_error());
                $updateBalance1 = mysqli_query($conn,"UPDATE `tb_balance` SET `active` = active + '$nominal' WHERE userID = '$tujuanID'") or die(mysqli_error());
                
                header('Location:../transfer/?notif=1');
                exit();
            }
        }
    }
}
else {
    header('location:../transfer/?notif=3');
    exit();
}
?>