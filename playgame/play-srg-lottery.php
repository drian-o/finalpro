<?php
ob_start();

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/../koneksi.php';
include_once __DIR__ . '/../classes/class.srg.php';

// --- FUNGSI LOGGING ---
function write_game_log($level, $message) {
    $log_dir = __DIR__ . '/../logs/';
    $log_file = $log_dir . 'get_urls_srg_lottery_game.log';

    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }

    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[{$timestamp}] [SRG LOTTERY LAUNCH] [" . strtoupper($level) . "] " . $message . "\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}

// --- AWAL PROSES ---
write_game_log('INFO', 'Permintaan diterima dari IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'N/A'));
write_game_log('DEBUG', 'Isi $_SESSION setelah session_start(): ' . var_export($_SESSION, true));

global $SRG; 
if (!isset($SRG) || !($SRG instanceof SRGConnect)) {
    write_game_log('ERROR', 'FATAL: Instance class $SRG (SRGConnect) tidak ditemukan atau tidak valid. Tidak dapat melanjutkan.');
    header("Location: " . dirname(__DIR__, 2) . "/index.php");
    exit();
}

if (!isset($_SESSION['nama_pengguna_anggota']) || empty($_SESSION['nama_pengguna_anggota'])) {
    write_game_log('WARN', 'Akses ditolak: Pengguna belum login atau nama_pengguna_anggota kosong. Mengalihkan ke halaman login.');
    header("Location: " . dirname(__DIR__, 2) . "/auth-login.php");
    exit();
}

if (!isset($_GET['game_code']) || !isset($_GET['provider_code'])) {
    $redirect_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : dirname(__DIR__, 2) . "/index.php";
    write_game_log('WARN', 'Akses ditolak: Parameter game_code atau provider_code tidak ada. Mengalihkan ke: ' . $redirect_url);
    header("Location: " . $redirect_url);
    exit();
}

// --- PARAMETER VALID, LANJUTKAN PROSES ---
$game_code = $_GET['game_code'];
$provider_code = (int)$_GET['provider_code']; 
$player_username = $_SESSION['nama_pengguna_anggota'];
$game_type = $_GET['game_type'] ?? 'LOTTERY'; 

write_game_log('INFO', "Mencoba membuka game SRG Lottery untuk Player: '{$player_username}', Provider Code: '{$provider_code}', Game Code: '{$game_code}', Game Type: '{$game_type}'");
write_game_log('INFO', "Memanggil metode SRG->launchGame()...");

$launch_url = $SRG->launchGame($player_username, $game_code, $provider_code);

write_game_log('DEBUG', "Respons dari API SRG (URL atau false): " . ($launch_url ? $launch_url : 'false'));

// --- PROSES RESPONS DARI API SRGConnect ---
if (is_string($launch_url) && !empty($launch_url)) {
    write_game_log('SUCCESS', "Game SRG Lottery berhasil dibuka untuk player '{$player_username}'. Mengalihkan ke URL game: " . $launch_url);
    header("Location:" . $launch_url);
    exit;
} else {
    $error_reason = 'URL game tidak didapatkan dari API atau respons API gagal.';
    write_game_log('ERROR', "Gagal membuka game SRG Lottery untuk player '{$player_username}'. Penyebab: {$error_reason}");
    $redirect_to_error = dirname(__DIR__, 2) . "/index.php";
    write_game_log('INFO', "Mengalihkan ke: " . $redirect_to_error);
    header("Location: " . $redirect_to_error); 
    exit;
}

ob_end_flush();
?>