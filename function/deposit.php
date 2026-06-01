<?php
    require_once('session.php');
    $metode = $_POST['metode'];
    $nominal = preg_replace('/[^A-Za-z0-9\-]/','',$_POST['nominal']);
    $pay_from = $_POST['pay_from'];
    $catatan = $_POST['catatan'];
    $gameid = $_POST['gameid'];
    $postID = $_POST['postID'];

    $unik = date('Hs');
	$kode_unik = substr(str_shuffle(1234567890),0,2);
	$kd_transaksi = date('Ymds').$kode_unik;

	$totalBayar = $nominal;

	$created_date = date('Y-m-d H:i:s');
	
	if(isset($_POST['submit'])){
        //if($nominal < 20000){
            //header('Location:../deposit/?notif=1');
            //exit();
        //}
        //else {
            $insert_transaksi = mysqli_query($conn,"INSERT INTO `tb_transaksi` (`kd_transaksi`, `date`, `transaksi`, `total`, `saldo`, `note`, `gameid`, `providerID`, `jenis`, `metode`, `pay_from`, `userID`, `status`) VALUES ('$kd_transaksi','$created_date','Top Up','$totalBayar',0, '$catatan', '$gameid', '0','1','$metode','$pay_from','$postID',0)") or die(mysqli_error($conn));
        
            if($gameid != ''){
                $sql_transaksi = mysqli_query($conn,"SELECT * FROM `tb_post` WHERE cuid = '$gameid'") or die(mysqli_error());
                $st = mysqli_fetch_array($sql_transaksi);
                $jml_to = $st['min_to'];
                $persen = $st['persen']/100;
                $bonusnya = $nominal * $persen;
                $bonuse = round($bonusnya);
                if($bonuse > 200000){
                    $bonusDepo = 200000;
                }
                else {
                    $bonusDepo = $bonuse;
                }
                $totalTO = ($nominal + $bonusDepo) * $jml_to;
                $insertTO = mysqli_query($conn,"INSERT INTO `tb_turnover` (`userID`, `trxID`, `depo`, `bonus`, `jmlh_to`, `total_to`, `sisa_to`, `status`) VALUES ('$postID','$kd_transaksi','$nominal','$bonusDepo','$jml_to','$totalTO','$totalTO',0)") or die(mysqli_error());
           }
           header('Location:../desktop/?notif=8');
           exit(); 
        //} 
	}
?>