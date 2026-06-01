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

$cekGame = mysqli_query($conn,"SELECT * FROM `tb_gamelist` WHERE `gameid` = '$gameID' AND `provider` = 'PragmaticPlay'") or die(mysqli_error());
$cek = mysqli_fetch_array($cekGame);
if($cek['datatype'] == 'RNG'){
    $urlBack = $urlweb.'/slots/';
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
$catatan = 'Transfer to PragmaticPlay';
$requestIDD = $requestID.date('s');

$inserPlayer = mysqli_query($conn,"UPDATE `tb_ppplayer` SET `status` = 1 WHERE userID = '$usersID' AND `provider` = 'PragmaticPlay'") or die(mysqli_error());
$sql_provider = mysqli_query($conn,"SELECT * FROM `tb_tripayapi` WHERE cuid = 1") or die(mysqli_error());
$sp = mysqli_fetch_array($sql_provider);
$urlRequest = $sp['urlRequest'];
$gameUrl = $sp['urlResponse'];
$secureLogin = $sp['api_key']; //apiKey
$secretKey = $sp['secret_key']; //brandID

    $params = 'externalPlayerId='.$externalPlayerId.'&secureLogin='.$secureLogin.$secretKey;
    $hashNeed = md5($params);
    $curl = curl_init();
                                                    
    curl_setopt_array($curl, array(
      CURLOPT_URL => $urlRequest.'/balance/current',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => "secureLogin=".$secureLogin."&externalPlayerId=".$externalPlayerId."&hash=".$hashNeed,
      CURLOPT_HTTPHEADER => array(
        "Content-Type: application/x-www-form-urlencoded",
        "Cache-Control: no-cache"
      ),
    ));
                                                                
    $response = curl_exec($curl);
    //echo $response;
    curl_close($curl);
    $hasil = json_decode($response, true);
    $newSaldo = $hasil['balance'];
    if($newSaldo == $nominale){
        $params5 = 'amount=-'.$newSaldo.'&externalPlayerId='.$externalPlayerId.'&externalTransactionId='.$kd_transaksi.'&secureLogin='.$secureLogin.$secretKey;
        $hashNeed5 = md5($params5);
        $curl5 = curl_init();                                
        curl_setopt_array($curl5, array(
            CURLOPT_URL => $urlRequest.'/balance/transfer',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "secureLogin=".$secureLogin."&externalPlayerId=".$externalPlayerId."&externalTransactionId=".$kd_transaksi."&amount=-".$newSaldo."&hash=".$hashNeed5,
            CURLOPT_HTTPHEADER => array(
            "Content-Type: application/x-www-form-urlencoded",
            "Cache-Control: no-cache"
            ),
        ));                                         
        $response5 = curl_exec($curl5);
        //echo $response1;
        curl_close($curl5);
        $hasil5 = json_decode($response5, true);
    }

if($sp['status'] == 1){
    header('location:'.$urlweb.'/mtgame/');
}
else {
    

    $params2 = 'cashierUrl='.$urlweb.'&externalPlayerId='.$externalPlayerId.'&gameId='.$gameID.'&language=en&lobbyUrl='.$urlweb.'&platform=WEB&secureLogin='.$secureLogin.$secretKey;
    $hashNeed2 = md5($params2);
    $curl2 = curl_init();
    curl_setopt_array($curl2, array(
        CURLOPT_URL => $urlRequest.'/game/start',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => "secureLogin=".$secureLogin."&externalPlayerId=".$externalPlayerId."&gameId=".$gameID."&language=en&platform=WEB&cashierUrl=".$urlweb."&lobbyUrl=".$urlweb."&hash=".$hashNeed2,
        CURLOPT_HTTPHEADER => array(
            "Content-Type: application/x-www-form-urlencoded",
            "Cache-Control: no-cache"
        ),
    ));
                                                       
    $response2 = curl_exec($curl2);
    //echo $response2;
    curl_close($curl2);
    $hasil2 = json_decode($response2, true);
    if($hasil2['description'] == 'OK'){
        $playUrl = $hasil2['gameURL'];
        $params1 = 'amount='.$nominale.'&externalPlayerId='.$externalPlayerId.'&externalTransactionId='.$kd_transaksi.'&secureLogin='.$secureLogin.$secretKey;
        $hashNeed1 = md5($params1);
        $curl1 = curl_init();
                        
        curl_setopt_array($curl1, array(
            CURLOPT_URL => $urlRequest.'/balance/transfer',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "secureLogin=".$secureLogin."&externalPlayerId=".$externalPlayerId."&externalTransactionId=".$kd_transaksi."&amount=".$nominale."&hash=".$hashNeed1,
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/x-www-form-urlencoded",
                "Cache-Control: no-cache"
            ),
        ));
                             
        $response1 = curl_exec($curl1);
        //echo $response1;
        curl_close($curl1);
        $hasil1 = json_decode($response1, true);
        if($hasil1['description'] == 'OK'){
            $transactionId = $hasil1['transactionId'];
            $insert_transaksi = mysqli_query($conn,"INSERT INTO `tb_transaksi` (`kd_transaksi`, `date`, `transaksi`, `total`, `saldo`, `note`, `gameid`, `providerID`, `jenis`, `metode`, `pay_from`, `userID`, `status`) VALUES ('$kd_transaksi', '$created_date', 'Transfer', '$nominale', 0, '$catatan', '$gameID', 1, 5, 0, 0,'$usersID', 1)") or die(mysqli_error());
            $updateBalance = mysqli_query($conn,"UPDATE `tb_balance` SET `active` = active - '$nominale', `transfer` = transfer + '$nominale' WHERE userID = '$usersID'") or die(mysqli_error());
            $updatePlayerBalance = mysqli_query($conn,"UPDATE `tb_ppplayer` SET `balance` = '$nominale' WHERE userID = '$usersID' AND `provider` = 'PragmaticPlay'") or die(mysqli_error());
            $updatePlayer = mysqli_query($conn,"UPDATE `tb_ppplayer` SET `status` = 0 WHERE userID = '$usersID' AND `provider` != 'PragmaticPlay'") or die(mysqli_error());
        }
        else {
            $params4 = 'externalPlayerId='.$externalPlayerId.'&secureLogin='.$secureLogin.$secretKey;
            $hashNeed4 = md5($params4);
            $curl4 = curl_init();
                                                
            curl_setopt_array($curl4, array(
                CURLOPT_URL => $urlRequest.'/balance/current',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => "secureLogin=".$secureLogin."&externalPlayerId=".$externalPlayerId."&hash=".$hashNeed4,
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/x-www-form-urlencoded",
                    "Cache-Control: no-cache"
                ),
            ));                                                            
            $response4 = curl_exec($curl4);
            //echo $response;
            curl_close($curl4);
            $hasil4 = json_decode($response4, true);
            $newSaldo = $hasil4['balance'];
            if($newSaldo > $nominale){
                $params3 = 'amount=-'.$nominale.'&externalPlayerId='.$externalPlayerId.'&externalTransactionId='.$kd_transaksi.'&secureLogin='.$secureLogin.$secretKey;
                $hashNeed3 = md5($params3);
                $curl3 = curl_init();
                                
                curl_setopt_array($curl3, array(
                    CURLOPT_URL => $urlRequest.'/balance/transfer',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => "secureLogin=".$secureLogin."&externalPlayerId=".$externalPlayerId."&externalTransactionId=".$kd_transaksi."&amount=-".$nominale."&hash=".$hashNeed3,
                    CURLOPT_HTTPHEADER => array(
                        "Content-Type: application/x-www-form-urlencoded",
                        "Cache-Control: no-cache"
                    ),
                ));
                                        
                $response3 = curl_exec($curl3);
                //echo $response1;
                curl_close($curl3);
            }
            $updatePlayer = mysqli_query($conn,"UPDATE `tb_ppplayer` SET `status` = 0 WHERE userID = '$usersID' AND `provider` = 'PragmaticPlay'") or die(mysqli_error());
        }
        header('Location:'.$playUrl);
        exit();
    }
    else {
        header('Location:'.$urlweb.'?notif=2');
        exit();
    }

    //Transfer Dana
}
?>