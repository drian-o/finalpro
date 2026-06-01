<?php
require_once('session.php');

$useridnya = $u['user'];
$usersID = $u['cuid'];
$externalPlayerId = $u['extplayer'];
$gameID = $_GET['gamecode'];

$kode_unik = substr(str_shuffle(1234567890),0,3);
$kd_transaksi = date('Ymds').$kode_unik.$usersID;
$requestID = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz123456789'),0,8);
$created_date = date('Y-m-d H:i:s');

$cekGame = mysqli_query($conn,"SELECT * FROM `tb_gamelist` WHERE `gameid` = '$gameID' AND `provider` = 'Joker'") or die(mysqli_error());
$cek = mysqli_fetch_array($cekGame);
if($cek['datatype'] == 'RNG'){
    $urlBack = $urlweb.'/slot/';
}
else if($cek['datatype'] == 'EGames'){
    $urlBack = $urlweb.'/egames/';
}
else if($cek['datatype'] == 'Arcade'){
    $urlBack = $urlweb.'/egames/';
}
else if($cek['datatype'] == 'Fishing'){
    $urlBack = $urlweb.'/fishing/';
}
else if($cek['datatype'] == 'LC'){
    $urlBack = $urlweb.'/casino/';
}
else {
    $urlBack = $urlweb.'/';
}

//Transfer Dana

$getBalance = mysqli_query($conn,"SELECT * FROM `tb_balance` WHERE userID = '$usersID'") or die(mysqli_error());
$gb = mysqli_fetch_array($getBalance);
$nominale = $gb['active'];
$catatan = 'Transfer to Joker';
$requestIDD = $requestID.date('s');

$inserPlayer = mysqli_query($conn,"UPDATE `tb_ppplayer` SET `status` = 1 WHERE userID = '$usersID' AND `provider` = 'Joker'") or die(mysqli_error());
$sql_provider = mysqli_query($conn,"SELECT * FROM `tb_tripayapi` WHERE cuid = 2") or die(mysqli_error());
$sp = mysqli_fetch_array($sql_provider);
$urlRequest = $sp['urlRequest'];
$gameUrl = $sp['urlResponse'];
$secureLogin = $sp['api_key']; //apiKey
$secretKey = $sp['secret_key']; //brandID

if($sp['status'] == 1){
    header('location:'.$urlweb.'/mtgame/');
}
else {
    $timestamp = time();

    $fields = ['Method' => 'PLAY',
                 'Username'  => $externalPlayerId,
                 'Timestamp' => $timestamp,
                 'Amount'  => $nominale,
                 'RequestID'  => $requestID
                ];
    ksort($fields);
    $signature = urlencode(base64_encode(hash_hmac("sha1", urldecode(http_build_query($fields,'', '&')), $secretKey, TRUE)));
                                
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $urlRequest.'?appid='.$secureLogin.'&signature='.$signature,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => '{"Method":"PLAY","Username":"'.$externalPlayerId.'","Timestamp":"'.$timestamp.'","Amount":"'.$nominale.'","RequestID":"'.$requestID.'"}',
        CURLOPT_HTTPHEADER => array(
            "Content-Type: application/json",
            "Cache-Control: no-cache"
        ),
    ));
                                             
    $response = curl_exec($curl);
    //echo $response2;
    curl_close($curl);
    $hasil = json_decode($response, true);
    $tokennya = $hasil['Token'];

    $fields1 = ['Method' => 'GC',
               'Username' => $externalPlayerId,
               'Timestamp' => $timestamp
              ];
    ksort($fields1);
    $signature1 = urlencode(base64_encode(hash_hmac("sha1", urldecode(http_build_query($fields1,'', '&')), $secretKey, TRUE)));

    $curl1 = curl_init();
    curl_setopt_array($curl1, array(
        CURLOPT_URL => $urlRequest.'?appid='.$secureLogin.'&signature='.$signature1,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => '{"Method":"GC","Username":"'.$externalPlayerId.'","Timestamp":"'.$timestamp.'"}',
        CURLOPT_HTTPHEADER => array(
            "Content-Type: application/json",
            "Cache-Control: no-cache"
        ),
    ));
                                                           
    $response1 = curl_exec($curl1);
    //echo $response;
    curl_close($curl1);
    $hasil1 = json_decode($response1, true);
    $newSaldo = $hasil1['Credit'];

    $insert_transaksi = mysqli_query($conn,"INSERT INTO `tb_transaksi` (`kd_transaksi`, `date`, `transaksi`, `total`, `saldo`, `note`, `gameid`, `providerID`, `jenis`, `metode`, `pay_from`, `userID`, `status`) VALUES ('$kd_transaksi', '$created_date', 'Transfer', '$nominale', 0, '$catatan', '$gameID', 2, 5, 0, 0,'$usersID', 1)") or die(mysqli_error());
    $updateBalance = mysqli_query($conn,"UPDATE `tb_balance` SET `active` = active - '$nominale', `transfer` = transfer + '$nominale' WHERE userID = '$usersID'") or die(mysqli_error());
    $updatePlayerBalance = mysqli_query($conn,"UPDATE `tb_ppplayer` SET `balance` = '$nominale' WHERE userID = '$usersID' AND `provider` = 'Joker'") or die(mysqli_error());
    $updatePlayer = mysqli_query($conn,"UPDATE `tb_ppplayer` SET `status` = 0 WHERE userID = '$usersID' AND `provider` != 'Joker'") or die(mysqli_error());

    $playUrl = $gameUrl.'?token='.$tokennya.'&game='.$gameID.'&mobile=false';
    header('Location:'.$playUrl);
    exit();
    //Transfer Dana
    
}
?>