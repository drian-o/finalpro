<?php
ob_start();
session_start();
include('../config/koneksi.php');
$sql_0 = mysqli_query($conn,"SELECT * FROM `tb_seo` WHERE cuid = 1") or die(mysqli_error());
$s0 = mysqli_fetch_array($sql_0);
$urlweb = $s0['urlweb'];
$token = session_id();
$full_name = mysqli_real_escape_string($conn,$_POST['full_name']);
$usere = strtolower($_POST['user']);
$useree = mysqli_real_escape_string($conn,$usere);
$user = str_replace(' ','',$useree);
$uniknya = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz1234567890'),0,4);
$extplayer = 'ab'.$user.$uniknya;
$email = mysqli_real_escape_string($conn,$_POST['email']);
$no_hp = $_POST['no_hp'];
$akun = mysqli_real_escape_string($conn,$_POST['akun']);
$no_rek = $_POST['no_rek'];
$pass = password_hash($_POST['pass'],PASSWORD_DEFAULT);
$level = 'user';
$join_date = date('Y-m-d H:i:s');
$cekusere = mysqli_query($conn,"SELECT * FROM `tb_user` ORDER BY cuid DESC LIMIT 1") or die (mysqli_error());
$cus = mysqli_fetch_array($cekusere);
$cuid = $cus['cuid'] + 1;
$useridd = '1'.date('dmy').$cuid;

$cekUser = mysqli_query($conn,"SELECT * FROM `tb_user` WHERE user = '$user'") or die (mysqli_error());
$q = mysqli_num_rows($cekUser);
if($q > 0){
    header('location:../register/?notif=2');
}
else {
	$cekEmail = mysqli_query($conn,"SELECT * FROM `tb_user` WHERE email = '$email'") or die (mysqli_error());
	$qq = mysqli_num_rows($cekEmail);
	if($qq > 0){
	    header('location:../register/?notif=3');
	}
	else {
		$cekHp = mysqli_query($conn,"SELECT * FROM `tb_user` WHERE no_hp = '$no_hp'") or die (mysqli_error());
		$qqq = mysqli_num_rows($cekHp);
		if($qqq > 0){
		    header('location:../register/?notif=4');
		}
		else {
			$cekRekening = mysqli_query($conn,"SELECT * FROM `tb_bank` WHERE no_rek = '$no_rek'") or die (mysqli_error());
			$qqqq = mysqli_num_rows($cekRekening);
			if($qqqq > 0){
			    header('location:../register/?notif=5');
			}
			else {
				if($_POST['sponsor'] == ''){
					$uplineID = 1;
				}
				else {
					$upline = $_POST['sponsor'];
					$cekUpline = mysqli_query($conn,"SELECT * FROM `tb_user` WHERE user = '$upline'") or die (mysqli_error());
					$cu = mysqli_num_rows($cekUpline);
					if($cu == 0){
						$uplineID = 1;
					}
					else {
						$cuu = mysqli_fetch_array($cekUpline);
						$uplineID = $cuu['cuid'];
					}
				}

				$query = mysqli_query($conn,"INSERT INTO `tb_user` (`userid`, `extplayer`, `user`, `pass`, `token_id`, `image`, `full_name`, `email`, `no_hp`, `level`, `pinTrx`, `reff`, `uplineID`, `join_date`, `last_login`, `status`) VALUES ('0','$extplayer','$user', '$pass', '0', 'avatar5.png', '$full_name', '$email', '$no_hp', 'user', '', 0, '$uplineID', '$join_date', '$join_date', 1)") or die(mysqli_error());
			    $last_id = mysqli_insert_id($conn);
			    $query2 = mysqli_query($conn,"INSERT INTO `tb_balance` (`userID`, `active`, `pending`, `transfer`, `payout`, `created_date`) VALUES ('$last_id', 0, 0, 0, 0, '$join_date')") or die(mysqli_error());
			    $query3 = mysqli_query($conn,"INSERT INTO `tb_bank` (`image`, `akun`, `pemilik`, `no_rek`, `status`, `userID`) VALUES ('', '$akun', '$full_name', '$no_rek', 1, '$last_id')") or die(mysqli_error());
			    $update = mysqli_query($conn,"UPDATE `tb_user` SET reff = reff + 1 WHERE cuid = '$uplineID'") or die(mysqli_error());

				$sql_provider = mysqli_query($conn,"SELECT * FROM `tb_tripayapi`") or die(mysqli_error());
        		while($sp = mysqli_fetch_array($sql_provider)){
        			$urlRequest = $sp['urlRequest'];
			        $secureLogin = $sp['api_key']; //apikey
			        $secretKey = $sp['secret_key']; //brandID
			        $provider = $sp['provider'];
			        if($provider == 'PragmaticPlay'){
			        	$params = 'currency=IDR&externalPlayerId='.$extplayer.'&secureLogin='.$secureLogin.$secretKey;
				        $hashNeed = md5($params);
				        $curl = curl_init();
				                    
				        curl_setopt_array($curl, array(
				            CURLOPT_URL => $urlRequest.'/player/account/create',
				            CURLOPT_RETURNTRANSFER => true,
				            CURLOPT_ENCODING => "",
				            CURLOPT_MAXREDIRS => 10,
				            CURLOPT_TIMEOUT => 0,
				            CURLOPT_FOLLOWLOCATION => true,
				            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				            CURLOPT_CUSTOMREQUEST => "POST",
				            CURLOPT_POSTFIELDS => "secureLogin=".$secureLogin."&externalPlayerId=".$extplayer."&currency=IDR&hash=".$hashNeed,
				            CURLOPT_HTTPHEADER => array(
				                "Content-Type: application/x-www-form-urlencoded",
				                "Cache-Control: no-cache"
				            ),
				        ));
				                    
				        $response = curl_exec($curl);
				        echo $response;
				        curl_close($curl);
				        $hasil = json_decode($response, true);
				        $PlayerId = $hasil['playerId'];
				        $inserPlayer = mysqli_query($conn,"INSERT INTO `tb_ppplayer` (`userID`, `externalPlayerId`, `playerid`, `token`, `provider`, `balance`, `status`) VALUES ('$last_id','$extplayer','$PlayerId', '','$provider',0,0)") or die(mysqli_error());
			        }
        		}
			    
			    header('location:../login/?notif=4');
				exit();
			}
		}
	}
}
?>