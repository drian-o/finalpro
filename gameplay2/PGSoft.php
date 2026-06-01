<?php
require_once('session.php');

$useridnya = $u['user'];
$usersID = $u['cuid'];
$externalPlayerId = $u['extplayer'];
$gameID = $_GET['gamecode'];

$getPlayer = mysqli_query($conn,"SELECT * FROM `tb_ppplayer` WHERE userID = '$usersID' AND provider = 'PGSoft'") or die(mysqli_error());
$gp = mysqli_fetch_array($getPlayer);
$operatorPlayerSession = $gp['token'];

$kode_unik = substr(str_shuffle(1234567890),0,3);
$kd_transaksi = date('Ymds').$kode_unik.$usersID;
$requestID = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz123456789'),0,8);
$created_date = date('Y-m-d H:i:s');

$cekGame = mysqli_query($conn,"SELECT * FROM `tb_gamelist` WHERE `gameid` = '$gameID' AND `provider` = 'PGSoft'") or die(mysqli_error());
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

function getGUID(){
    if (function_exists('com_create_guid')){
        return com_create_guid();
    }else{
        mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45);// "-"
        $uuid = 
            substr($charid, 0, 8).$hyphen
            .substr($charid, 8, 4).$hyphen
            .substr($charid,12, 4).$hyphen
            .substr($charid,16, 4).$hyphen
            .substr($charid,20,12);
                
        return $uuid;
    }
}

$guid = getGUID();

//Transfer Dana
$getBalance = mysqli_query($conn,"SELECT * FROM `tb_balance` WHERE userID = '$usersID'") or die(mysqli_error());
$gb = mysqli_fetch_array($getBalance);
$nominale = $gb['active'];
$nominales = $gb['active']/1000;
$nominaless = round($nominales,2);
$catatan = 'Transfer to PGSoft';
$requestIDD = $requestID.date('s');

$inserPlayer = mysqli_query($conn,"UPDATE `tb_ppplayer` SET `status` = 1 WHERE userID = '$usersID' AND `provider` = 'PGSoft'") or die(mysqli_error());
$sql_provider = mysqli_query($conn,"SELECT * FROM `tb_tripayapi` WHERE cuid = 4") or die(mysqli_error());
$sp = mysqli_fetch_array($sql_provider);
$urlRequest = $sp['urlRequest'];
$gameUrl = $sp['urlResponse'];
$secureLogin = $sp['api_key']; //apiKey
$secretKey = $sp['secret_key']; //brandID


if($sp['status'] == 1){
    header('location:'.$urlweb.'/mtgame/');
}
else {
    

    $curl = curl_init();
                               
    curl_setopt_array($curl, array(
        CURLOPT_URL => $urlRequest.'external/Cash/v3/TransferIn?trace_id='.$guid,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => "operator_token=".$secureLogin."&secret_key=".$secretKey."&player_name=".$externalPlayerId."&amount=".$nominaless."&transfer_reference=".$kd_transaksi."&currency=IDR",
        CURLOPT_HTTPHEADER => array(
            "Content-Type: application/x-www-form-urlencoded",
            "Cache-Control: no-cache"
        ),
    ));
                                
    $response = curl_exec($curl);
    //echo $response;
    curl_close($curl);
    $hasil = json_decode($response, true);

    $insert_transaksi = mysqli_query($conn,"INSERT INTO `tb_transaksi` (`kd_transaksi`, `date`, `transaksi`, `total`, `saldo`, `note`, `gameid`, `providerID`, `jenis`, `metode`, `pay_from`, `userID`, `status`) VALUES ('$kd_transaksi', '$created_date', 'Transfer', '$nominale', 0, '$catatan', '$gameID', 4, 5, 0, 0,'$usersID', 1)") or die(mysqli_error());
    $updateBalance = mysqli_query($conn,"UPDATE `tb_balance` SET `active` = active - '$nominale', `transfer` = transfer + '$nominale' WHERE userID = '$usersID'") or die(mysqli_error());
    $updatePlayerBalance = mysqli_query($conn,"UPDATE `tb_ppplayer` SET `balance` = '$nominaless' WHERE userID = '$usersID' AND `provider` = 'PGSoft'") or die(mysqli_error());
    $updatePlayer = mysqli_query($conn,"UPDATE `tb_ppplayer` SET `status` = 0 WHERE userID = '$usersID' AND `provider` != 'PGSoft'") or die(mysqli_error());

    $playUrl = 'https://m.pg-redirect.net/'.$gameID.'/index.html?ot='.$secureLogin.'&ops='.$operatorPlayerSession.'&btt=1';

    header('Location:'.$playUrl);
    exit();
    //Transfer Dana
}
if (isset($_GET['exit_game']) && $_GET['exit_game'] == 1) {
    // Mendapatkan data transaksi terkini dari database (contoh)
    $getLatestTransaction = mysqli_query($conn, "SELECT * FROM `tb_transaksi` WHERE `userID` = '$usersID' ORDER BY `date` DESC LIMIT 1") or die(mysqli_error());
    $latestTransaction = mysqli_fetch_array($getLatestTransaction);

    // Mendapatkan informasi transaksi terkini
    $kd_transaksi = $latestTransaction['kd_transaksi'];
    $transaksi_total = $latestTransaction['total'];

    // Mengembalikan saldo pengguna
    $refundAmount = $transaksi_total; // Jumlah yang akan dikembalikan (misalnya, total transaksi terkini)
    $updateBalance = mysqli_query($conn, "UPDATE `tb_balance` SET `active` = `active` + '$refundAmount' WHERE `userID` = '$usersID'") or die(mysqli_error());

    // Menandai transaksi sebagai selesai atau dikembalikan
    $updateTransactionStatus = mysqli_query($conn, "UPDATE `tb_transaksi` SET `status` = 2 WHERE `kd_transaksi` = '$kd_transaksi'") or die(mysqli_error());

    // Menerapkan tindakan tambahan sesuai kebutuhan (misalnya, log, notifikasi, dll.)

    // Redirect atau lakukan tindakan lain setelah pengembalian saldo
    header('Location:'.$urlBack);
    exit();
}

// ... (potongan kode setelahnya)

// Kode selanjutnya sesuai dengan logika aplikasi Anda
?>