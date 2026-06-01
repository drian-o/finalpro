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
$jumlah = COUNT($_POST['nominal']);
$total = $_POST['nominal'];
$subtotal = array_sum($total);
if($saldoAktif < $subtotal){
	header('location:../taruhan.php?pid='.$pid.'&gameid=23&notif=1');
	exit();
}
else {
	for($i=0;$i<$jumlah;$i++){
		$tebak = $_POST['angka'][$i];
		$explode = explode(' - ',$tebak);
		$angka = $explode[0];
		$posisi = $explode[1];
		$nominal = $_POST['nominal'][$i];
		$getGame = mysqli_query($conn,"SELECT * FROM `tb_game` WHERE parent = 23 AND title = '$tebak'") or die(mysqli_error());
		$gg = mysqli_fetch_array($getGame);
		if($gg['price'] < 0){
			$bayar = $nominal - ($nominal*($gg['price']/100));
			$menang = $gg['price'];
			$diskons = $bayar * ($gg['diskon']/100);
			$diskon = round($diskons);
			$totalBayar = $bayar - $diskon;
			$jumlahMenang = $nominal + $totalBayar;
		}
		else {
			$bayar = $nominal;
			$menang = $gg['price'];
			$diskons = $bayar * ($gg['diskon']/100);
			$diskon = round($diskons);
			$totalBayar = $bayar - $diskon;
			$jumlahMenang = $nominal + $totalBayar + ($nominal*($gg['price']/100));
		}
		if($nominal != ''){
			
			$insert_taruhan = mysqli_query($conn,"INSERT INTO `tb_taruhan` (`userID`, `pid`, `gameid`, `code`, `periode`, `created_date`, `tebak`, `posisi`, `nominal`, `menang`, `diskon`, `bayar`, `jumlah`, `keterangan`, `status`) VALUES ('$useridnya', '$pid', 23, '$kodenya', '$periode', '$created_date', '$angka', '$posisi', '$nominal', '$menang', '$diskon', '$totalBayar', '$jumlahMenang', '', 0)") or die(mysqli_error());
			$insert_transaksi = mysqli_query($conn,"INSERT INTO `tb_transaksi` (`kd_transaksi`, `date`, `transaksi`, `total`, `saldo`, `note`, `gameid`, `providerID`, `jenis`, `metode`, `pay_from`, `userID`, `status`) VALUES ('$kd_transaksi','$created_date','Taruhan','$totalBayar',0, 'Taruhan $posisi $angka', '', '7','7','0','0','$useridnya',1)") or die(mysqli_error($conn));
            $updateBalance = mysqli_query($conn,"UPDATE `tb_balance` SET `active` = active - '$totalBayar' WHERE userID = '$useridnya'") or die(mysqli_error($conn));
			
		}
	}
	header('location:../betting/');
	exit();
}
?>