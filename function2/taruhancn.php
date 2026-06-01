<?php
require_once('session.php');
$useridnya = $u['cuid'];

$kode_unik = substr(str_shuffle(1234567890),0,3);
$kd_transaksi = date('Ymds').$kode_unik;
$requestID = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz123456789'),0,8);

$created_date = date('Y-m-d H:i:s');
$saldoAktif = $s3['active'];

$pid = $_POST['pid'];
$getPasaran = mysqli_query($conn,"SELECT * FROM `tb_pasaran` WHERE cuid = '$pid'") or die(mysqli_error());
$gp = mysqli_fetch_array($getPasaran);
$kodenya = $gp['code'];
$getPeriode = mysqli_query($conn,"SELECT * FROM `tb_periode` WHERE pid = '$pid' ORDER BY no DESC LIMIT 1") or die(mysqli_error());
$gppp = mysqli_num_rows($getPeriode);
if($gppp == 0){
	$periode = 1;
}
else {
	$gpp = mysqli_fetch_array($getPeriode);
	$periode = $gpp['no']+1;
}
$jumlah = COUNT($_POST['angka']);
$total = $_POST['nominal'];
$subtotal = array_sum($total);
if($saldoAktif < $subtotal){
	header('location:../taruhan.php?pid='.$pid.'&gameid=7&notif=1');
	exit();
}
else {
	for($i=0;$i<$jumlah;$i++){
		$angka = $_POST['angka'][$i];
		$nominal = $_POST['nominal'][$i];
		$posisi = 'COLOK NAGA';
		$getGame = mysqli_query($conn,"SELECT * FROM `tb_game` WHERE title = '$posisi'") or die(mysqli_error());
		$gg = mysqli_fetch_array($getGame);
		$menang = $gg['price'];
		$potongan = $gg['diskon'];
		$diskons = ($nominal * $potongan) / 100;
		$diskon = round($diskons);
		$totalBayar = $nominal - $diskon;
		$jumlahMenang = ($menang * $nominal) + $totalBayar;
		if($angka != ''){
			
			$insert_taruhan = mysqli_query($conn,"INSERT INTO `tb_taruhan` (`userID`, `pid`, `gameid`, `code`, `periode`, `created_date`, `tebak`, `posisi`, `nominal`, `menang`, `diskon`, `bayar`, `jumlah`, `keterangan`, `status`) VALUES ('$useridnya', '$pid', 7, '$kodenya', '$periode', '$created_date', '$angka', '3D', '$nominal', '$menang', '$diskon', '$totalBayar', '$jumlahMenang', '', 0)") or die(mysqli_error());
			$insert_transaksi = mysqli_query($conn,"INSERT INTO `tb_transaksi` (`kd_transaksi`, `date`, `transaksi`, `total`, `saldo`, `note`, `gameid`, `providerID`, `jenis`, `metode`, `pay_from`, `userID`, `status`) VALUES ('$kd_transaksi','$created_date','Taruhan','$totalBayar',0, 'Taruhan $posisi $angka', '', '7','7','0','0','$useridnya',1)") or die(mysqli_error($conn));
            $updateBalance = mysqli_query($conn,"UPDATE `tb_balance` SET `active` = active - '$totalBayar' WHERE userID = '$useridnya'") or die(mysqli_error($conn));
			
		}
	}
	header('location:../betting/');
	exit();
}
?>