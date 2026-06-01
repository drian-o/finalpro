<?php
ob_start();

// Pastikan session_start() dipanggil pertama kali dan hanya sekali
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/../koneksi.php';
include_once __DIR__ . '/../classes/diamond-telo.php'; // Menyediakan $WL (instance dari Whitelabel)

// --- FUNGSI LOGGING ---
/**
 * Menulis pesan log ke file kustom logs/get_urls_telo_game.log
 * @param string $level 'INFO', 'WARN', 'ERROR', 'SUCCESS', 'DEBUG'
 * @param string $message Pesan yang akan dicatat.
 */
function write_game_log($level, $message) {
    $log_dir = __DIR__ . '/../logs/';
    $log_file = $log_dir . 'get_urls_telo_slot_game.log'; // Nama log file khusus

    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }

    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[{$timestamp}] [TELO SLOT LAUNCH] [" . strtoupper($level) . "] " . $message . "\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}

// --- AWAL PROSES ---

// LOG: Mencatat awal permintaan
write_game_log('INFO', 'Permintaan diterima dari IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'N/A'));
// LOG DEBUG: Tampilkan isi $_SESSION untuk debugging
write_game_log('DEBUG', 'Isi $_SESSION setelah session_start(): ' . var_export($_SESSION, true));


// --- Validasi instance class $WL ---
global $WL; 
if (!isset($WL) || !($WL instanceof whitelabel)) { // Memastikan $WL adalah instance whitelabel
    write_game_log('ERROR', 'FATAL: Instance class $WL (Whitelabel) tidak ditemukan atau tidak valid. Tidak dapat melanjutkan.');
    // Mengalihkan ke halaman utama jika ada masalah fatal
    header("Location: " . dirname(__DIR__, 2) . "/index.php"); 
    exit();
}

// Validasi sesi pengguna
if (!isset($_SESSION['nama_pengguna_anggota']) || empty($_SESSION['nama_pengguna_anggota'])) {
    write_game_log('WARN', 'Akses ditolak: Pengguna belum login atau nama_pengguna_anggota kosong. Mengalihkan ke halaman login.');
    // Mengalihkan ke auth-login.php
    header("Location: " . dirname(__DIR__, 2) . "/auth-login.php"); 
    exit();
}

// Validasi parameter GET
// Asumsi: 'provider_code' adalah kode provider Telo, 'game_code' adalah kode game, 'game_type' adalah tipe (misal: slot)
if (!isset($_GET['game_code']) || !isset($_GET['provider_code']) || !isset($_GET['game_type'])) {
    $redirect_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : dirname(__DIR__, 2) . "/index.php";
    write_game_log('WARN', 'Akses ditolak: Parameter game_code, provider_code, atau game_type tidak ada. Mengalihkan ke: ' . $redirect_url);
    header("Location: " . $redirect_url);
    exit();
}

// --- PARAMETER VALID, LANJUTKAN PROSES ---

$game_code = $_GET['game_code'];
$provider_code = $_GET['provider_code']; // Telo API provider_code bisa string (contoh: PRAGMATIC)
$player_username = $_SESSION['nama_pengguna_anggota'];
$game_type = $_GET['game_type']; // Tipe game (contoh: slot) dari URL

// Telo API launchGame membutuhkan 'lang' dan 'deposit_amount'
$lang = 'en'; // Atau 'id' jika ingin bahasa Indonesia
$deposit_amount_on_launch = 0; // Opsional, 0 jika tidak ingin deposit saat launch

write_game_log('INFO', "Mencoba membuka game TELO Slot untuk Player: '{$player_username}', Provider Code: '{$provider_code}', Game Code: '{$game_code}', Game Type: '{$game_type}', Lang: '{$lang}'");
write_game_log('INFO', "Memanggil metode WL->openGame()...");

// Memanggil API Telo.is untuk meluncurkan game
// WL->openGame($username, $game_code, $provider, $game_type, $amounts)
// Perhatikan bahwa $amounts di openGame diamond-telo.php tidak digunakan
// dan game_type di diamond-telo.php hardcoded 'slot'. Kita akan pastikan itu benar di sana.
$api_response = $WL->openGame($player_username, $game_code, $provider_code, $game_type, $deposit_amount_on_launch);

write_game_log('DEBUG', "Respons mentah dari API:\n" . json_encode($api_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));


// --- PROSES RESPONS DARI API Telo.is ---

// Cek jika respons valid dan sukses (status 1) dan launch_url tidak kosong
if ($api_response && isset($api_response['status']) && $api_response['status'] == 1 && !empty($api_response['launch_url'])) {
    $launch_url = $api_response['launch_url']; // Kunci URL game dari Telo.is
    
    write_game_log('SUCCESS', "Game TELO Slot berhasil dibuka untuk player '{$player_username}'. Mengalihkan ke URL game: " . $launch_url);
    
    // Alihkan pengguna ke URL game
    header("Location:" . $launch_url);
    exit;

} else {
    // --- JIKA GAGAL, CARI TAHU PENYEBABNYA ---
    $error_reason = 'URL game tidak didapatkan dari API atau respons API gagal.';
    $api_msg = $api_response['msg'] ?? 'Tidak ada pesan dari API.'; // Ambil pesan dari API

    if (!$api_response) {
        $error_reason = 'Respons dari API kosong (null atau false).';
    } elseif (!isset($api_response['status'])) {
        $error_reason = "Key 'status' tidak ditemukan dalam respons API.";
    } elseif ($api_response['status'] != 1) { // Telo.is status 1 adalah sukses
        $error_reason = "Nilai 'status' dari API bukan '1', melainkan '" . htmlspecialchars($api_response['status']) . "'. Pesan: " . htmlspecialchars($api_msg);
    } elseif (empty($api_response['launch_url'])) { // Kunci URL game dari Telo.is
        $error_reason = "Status API sukses, tetapi 'launch_url' kosong atau tidak ada. Pesan: " . htmlspecialchars($api_msg);
    }

    // LOG: Mencatat kegagalan dengan detail penyebabnya
    write_game_log('ERROR', "Gagal membuka game TELO Slot untuk player '{$player_username}'. Penyebab: {$error_reason}");

    // Alihkan pengguna kembali ke halaman utama atau halaman error khusus
    $redirect_to_error = dirname(__DIR__, 2) . "/index.php"; // Ke root web
    write_game_log('INFO', "Mengalihkan ke: " . $redirect_to_error);
    header("Location: " . $redirect_to_error); 
    exit;
}

ob_end_flush();
?>