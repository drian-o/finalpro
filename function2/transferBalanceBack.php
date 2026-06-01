<?php
require_once('session.php');

$useridnya = $u['user'];

$kode_unik = substr(str_shuffle(1234567890),0,3);
$kd_transaksi = date('Ymds').$kode_unik;
$requestID = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz123456789'),0,8);

$created_date = date('Y-m-d H:i:s');
    
$getUser = mysqli_query($conn,"SELECT * FROM `tb_user` WHERE user = '$useridnya'") or die(mysqli_error());
$gu = mysqli_fetch_array($getUser);
$externalPlayerId = $gu['extplayer'];
$usersID = $gu['cuid'];

$cekAktifGame = mysqli_query($conn,"SELECT * FROM `tb_ppplayer` WHERE userID = '$usersID' AND status = 1") or die(mysqli_error());
$cags = mysqli_num_rows($cekAktifGame);
if($cags != 0){
    $cag = mysqli_fetch_array($cekAktifGame);
    $provider = $cag['provider'];
    $catatan = $provider.' Transfer Back';

    if($provider == 'PragmaticPlay'){
        $sql_provider = mysqli_query($conn,"SELECT * FROM `tb_tripayapi` WHERE cuid = 1") or die(mysqli_error());
        $sp = mysqli_fetch_array($sql_provider);
        $urlRequest = $sp['urlRequest'];
        $secureLogin = $sp['api_key'];
        $secretKey = $sp['secret_key'];
        
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
        $nominal = $hasil['balance'];
        
            $cekID = mysqli_query($conn,"SELECT * FROM `tb_ppplayer` WHERE userID = '$usersID'") or die(mysqli_error());
            $cidd = mysqli_fetch_array($cekID);
            $playerId = $cidd['playerid'];
            $params1 = 'amount=-'.$nominal.'&externalPlayerId='.$externalPlayerId.'&externalTransactionId='.$kd_transaksi.'&secureLogin='.$secureLogin.$secretKey;
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
                CURLOPT_POSTFIELDS => "secureLogin=".$secureLogin."&externalPlayerId=".$externalPlayerId."&externalTransactionId=".$kd_transaksi."&amount=-".$nominal."&hash=".$hashNeed1,
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
                $newSaldo = $hasil1['balance'];
                $insert_transaksi = mysqli_query($conn,"INSERT INTO `tb_transaksi` (`kd_transaksi`, `date`, `transaksi`, `total`, `saldo`, `note`, `providerID`, `jenis`, `metode`, `pay_from`, `userID`, `status`) VALUES ('$kd_transaksi','$created_date','Transfer','$nominal',0, '$catatan', '1','6','0','$provider','$usersID',1)") or die(mysqli_error());
                $updateBalance = mysqli_query($conn,"UPDATE `tb_balance` SET `active` = active + $nominal WHERE userID = '$usersID'") or die(mysqli_error());
                $updatePlayer = mysqli_query($conn,"UPDATE `tb_ppplayer` SET `status` = 0 WHERE userID = '$usersID'") or die(mysqli_error());
    
            }
        
    }
    else if($provider == 'Joker'){
        $timestamp = time();
        $sql_provider = mysqli_query($conn,"SELECT * FROM `tb_tripayapi` WHERE cuid = 2") or die(mysqli_error());
        $sp = mysqli_fetch_array($sql_provider);
        $urlRequest = $sp['urlRequest'];
        $secureLogin = $sp['api_key'];
        $secretKey = $sp['secret_key'];
        
        $fields = ['Method' => 'WAC',
                  'Username' => $useridnya,
                  'Timestamp' => $timestamp,
                  'RequestID' => $requestID
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
            CURLOPT_POSTFIELDS => '{"Method":"WAC","Username":"'.$useridnya.'","Timestamp":"'.$timestamp.'","RequestID":"'.$requestID.'"}',
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "Cache-Control: no-cache"
            ),
        ));
                                
        $response = curl_exec($curl);
        //echo $response;
        curl_close($curl);
        $hasil = json_decode($response, true);
        $nominal = $hasil['Amount'];
        
            $insert_transaksi = mysqli_query($conn,"INSERT INTO `tb_transaksi` (`kd_transaksi`, `date`, `transaksi`, `total`, `saldo`, `note`, `providerID`, `jenis`, `metode`, `pay_from`, `userID`, `status`) VALUES ('$kd_transaksi','$created_date','Transfer','$nominal',0, '$catatan', '2','6','0','$provider','$usersID',1)") or die(mysqli_error());
            $updateBalance = mysqli_query($conn,"UPDATE `tb_balance` SET `active` = active + $nominal WHERE userID = '$usersID'") or die(mysqli_error());
            $updatePlayer = mysqli_query($conn,"UPDATE `tb_ppplayer` SET `status` = 0 WHERE userID = '$usersID'") or die(mysqli_error());
       
    }
    else if($provider == 'Habanero'){
        $sql_provider = mysqli_query($conn,"SELECT * FROM `tb_tripayapi` WHERE cuid = 3") or die(mysqli_error());
        $sp = mysqli_fetch_array($sql_provider);
        $urlRequest = $sp['urlRequest'];
        $apiKey = $sp['api_key'];
        $brandID = $sp['secret_key'];

        $curl = curl_init();
                    
        curl_setopt_array($curl, array(
            CURLOPT_URL => $urlRequest.'QueryPlayer',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => '{
                "BrandId": "'.$brandID.'", 
                "APIKey": "'.$apiKey.'",
                "Username": "'.$externalPlayerId.'",
                "password":"Testaja123"
            }',
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "Cache-Control: no-cache"
            ),
        ));
                                          
        $response = curl_exec($curl);
        echo $response;
        curl_close($curl);
        $hasil = json_decode($response, true);
        $nominal = $hasil['RealBalance'];
        
            $curl1 = curl_init();
                        
            curl_setopt_array($curl1, array(
                CURLOPT_URL => $urlRequest.'WithdrawPlayerMoney',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => '{
                    "BrandId": "'.$brandID.'", 
                    "APIKey": "'.$apiKey.'", 
                    "Username": "'.$externalPlayerId.'",
                    "password":"Testaja123", 
                    "currencycode":"IDR",
                    "Amount": "-'.$nominal.'",
                    "WithdrawAll": "true",
                    "RequestId":"'.$kode_unik.'"
                }',
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json",
                    "Cache-Control: no-cache"
                ),
            ));
                        
            $response1 = curl_exec($curl1);
            echo $response1;
            curl_close($curl1);
            $hasil1 = json_decode($response1, true);
    
            if($hasil1['Success'] == true){
                $insert_transaksi = mysqli_query($conn,"INSERT INTO `tb_transaksi` (`kd_transaksi`, `date`, `transaksi`, `total`, `saldo`, `note`, `providerID`, `jenis`, `metode`, `pay_from`, `userID`, `status`) VALUES ('$kd_transaksi','$created_date','Transfer','$nominal',0, '$catatan', '3','6','0','$provider','$usersID',1)") or die(mysqli_error());
                $updateBalance = mysqli_query($conn,"UPDATE `tb_balance` SET `active` = active + $nominal WHERE userID = '$usersID'") or die(mysqli_error());
                $updatePlayer = mysqli_query($conn,"UPDATE `tb_ppplayer` SET `status` = 0 WHERE userID = '$usersID'") or die(mysqli_error());
            }
        
    }
    
    else if($provider == 'CQ9'){
        $sql_provider = mysqli_query($conn,"SELECT * FROM `tb_tripayapi` WHERE cuid = 4") or die(mysqli_error());
        $sp = mysqli_fetch_array($sql_provider);
        $urlRequest = $sp['urlRequest'];
        $secureLogin = $sp['api_key'];
        
        $curl = curl_init();
                    
        curl_setopt_array($curl, array(
            CURLOPT_URL => $urlRequest.'/gameboy/player/balance/'.$useridnya,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
              "Authorization: ".$secureLogin,
              "Content-Type: application/x-www-form-urlencoded",
              "Cache-Control: no-cache"
            ),
        ));
                                        
        $response = curl_exec($curl);
        echo $response;
        curl_close($curl);
        $hasil = json_decode($response, true);
        $nominal = $hasil['data']['balance'];
        
            $curl1 = curl_init();
                        
            curl_setopt_array($curl1, array(
                CURLOPT_URL => $urlRequest.'/gameboy/player/withdraw',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => "account=".$useridnya."&mtcode=".$requestID."&amount=".$nominal,
                CURLOPT_HTTPHEADER => array(
                  "Authorization: ".$secureLogin,
                  "Content-Type: application/x-www-form-urlencoded",
                  "Cache-Control: no-cache"
                ),
            ));
                                            
            $response1 = curl_exec($curl1);
            echo $response1;
            curl_close($curl1);
            $hasil1 = json_decode($response1, true);
            if($hasil1['status']['message'] == 'Success'){
                $insert_transaksi = mysqli_query($conn,"INSERT INTO `tb_transaksi` (`kd_transaksi`, `date`, `transaksi`, `total`, `saldo`, `note`, `providerID`, `jenis`, `metode`, `pay_from`, `userID`, `status`) VALUES ('$kd_transaksi','$created_date','Transfer','$nominal',0, '$catatan', '4','6','0','$provider','$usersID',1)") or die(mysqli_error());
                $updateBalance = mysqli_query($conn,"UPDATE `tb_balance` SET `active` = active + $nominal WHERE userID = '$usersID'") or die(mysqli_error());
                $updatePlayer = mysqli_query($conn,"UPDATE `tb_ppplayer` SET `status` = 0 WHERE userID = '$usersID'") or die(mysqli_error());
            }
       
        
    }

}

if(isset($_GET['jenis'])){
    if($_GET['jenis'] == 1){
        header('location:../deposit/');
        exit();
    }
    else {
        header('location:../withdraw/');
        exit();
    }
}
else {
    header('location:../');
    exit();
}
?>