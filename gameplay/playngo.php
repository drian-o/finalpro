<?php
require_once('session.php');

$useridnya = $u['user'];
$usersID = $u['cuid'];
$externalPlayerId = $u['extplayer'];
$gameID = $_GET['gamecode'];

// Membuka permainan
$act = json_decode($SG->opengame($useridnya, $gameID, $urlweb, $urlweb.'deposit'), true);

if ($act['status'] == "success") {
    // Redirect ke URL permainan
    header('location: '.$act['gameUrl']);

    // Mendapatkan saldo setelah bermain
    $saldoSetelahBermain = $SG->getAfterGameBalance($useridnya);

    // Mengupdate saldo di database
    if ($saldoSetelahBermain !== null) {
        // Update saldo di database
        $SG->updateSaldoDiDatabase($useridnya, $saldoSetelahBermain);

        // Output saldo setelah bermain
        echo "Saldo setelah bermain: $saldoSetelahBermain";
    } else {
        echo "Gagal mendapatkan saldo setelah bermain untuk $useridnya";
    }
} else {
    echo $act['msg'];
}

?>