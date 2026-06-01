<?php
require_once('session.php');

$useridnya = $u['username'];
$gameID = $_GET['gamecode'];
$gameProvider = $_GET['providercode'];
$game_type = 'slot';
$userID = $u['cuid'];

$amount = mysqli_query($conn, "SELECT * FROM `tb_balance` WHERE userID = '$userID'") or die(mysqli_error($conn));
$am = mysqli_fetch_array($amount);
$amounts = $am['active'];

$openGames = $WL->openGame($useridnya, $game_type, $gameProvider, $gameID, $amounts);
//var_dump($openGames);
if ($openGames['status'] == 1) {
    header('location: ' . $openGames['launch_url']);
} else {
    echo "Maaf, API Sedang dalam gangguan, tunggu beberapa saat.";
    exit();
}
