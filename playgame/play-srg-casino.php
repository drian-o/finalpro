<?php
// playgame/play-gamexa-slot.php

session_start();
require_once '../koneksi.php'; // Pastikan path ini benar (menyediakan $koneksi)
require_once '../classes/class.exa.php'; // Sertakan class GameXaAPI

// Error reporting untuk debugging (hapus di produksi)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- Fungsi Helper untuk Logging ---
function writeLog($message, $level = 'INFO') {
    $logFile = __DIR__ . '/../Logs/launch_exa.log'; // Path ke file log
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] [{$level}] " . $message . PHP_EOL; // PHP_EOL untuk baris baru

    // Pastikan folder Logs ada
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0775, true); // Buat folder jika belum ada, dengan izin yang sesuai
    }

    // Tulis ke file log
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
}
// --- Akhir Fungsi Helper untuk Logging ---

// Ambil parameter dari URL
$game_uid = isset($_GET['game_uid']) ? $_GET['game_uid'] : null;
$provider_code = isset($_GET['provider_code']) ? $_GET['provider_code'] : null;
$game_type = isset($_GET['game_type']) ? $_GET['game_type'] : null;

// Mulai logging proses
writeLog("Memulai proses peluncuran game.");

// Cek apakah user sudah login
if (!isset($_SESSION['id_anggota'])) {
    $msg = "Silakan login untuk bermain game.";
    writeLog("Redirect ke login. Keterangan: " . $msg, 'WARNING');
    header("Location: ../login?msg=" . urlencode($msg));
    exit();
}

$id_anggota_session = $_SESSION['id_anggota']; // ID dari sesi

// --- Ambil id_sigma dari database ---
$player_id_gamexa = null; // Variabel untuk menyimpan id_sigma yang akan dikirim ke GameXa

// Pastikan koneksi database tersedia
if (!$koneksi) {
    $msg = "Koneksi database gagal.";
    writeLog("Error: " . $msg, 'CRITICAL');
    echo htmlspecialchars($msg);
    exit();
}

// Query untuk mendapatkan id_sigma berdasarkan id_anggota
$query = "SELECT id_sigma FROM anggota WHERE id_anggota = ?";
if ($stmt = mysqli_prepare($koneksi, $query)) {
    mysqli_stmt_bind_param($stmt, "i", $id_anggota_session); // "i" untuk integer
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $id_sigma_from_db);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    if ($id_sigma_from_db !== null) {
        $player_id_gamexa = $id_sigma_from_db;
        writeLog("id_anggota ({$id_anggota_session}) berhasil dipetakan ke id_sigma GameXa: {$player_id_gamexa}");
    } else {
        $msg = "id_sigma tidak ditemukan atau NULL untuk id_anggota: {$id_anggota_session}. Pastikan pemain terdaftar di GameXa.";
        writeLog("Error: " . $msg, 'ERROR');
        echo htmlspecialchars($msg);
        exit();
    }
} else {
    $msg = "Gagal menyiapkan query database: " . mysqli_error($koneksi);
    writeLog("Error: " . $msg, 'CRITICAL');
    echo htmlspecialchars($msg);
    exit();
}
// --- Akhir pengambilan id_sigma ---

// Sekarang $player_id_gamexa berisi id_sigma yang akan digunakan

if (!$game_uid || !$provider_code || !$game_type) {
    $msg = "Parameter game tidak lengkap (game_uid: {$game_uid}, provider_code: {$provider_code}, game_type: {$game_type}).";
    writeLog("Error: " . $msg, 'ERROR');
    echo htmlspecialchars($msg);
    exit();
}

// Inisialisasi GameXaAPI
$gameXaAPI = new GameXaAPI();
writeLog("GameXaAPI diinisialisasi.");

try {
    // Tentukan mata uang untuk lingkungan pengujian (sesuai pesan error dari API)
    $currency_for_game = 'IDR'; // Bisa juga 'BRL' atau 'COP'

    // Siapkan body permintaan untuk log mentah
    $request_body_for_log = [
        "player_id" => $player_id_gamexa,
        "game_uid" => $game_uid,
        "currency" => $currency_for_game // <-- Tambahkan ini untuk log
    ];
    writeLog("Request mentah ke API GameXa (POST /api/games/launch): " . json_encode($request_body_for_log));

    // Panggil API launch game, sekarang menyertakan mata uang
    $launch_response = $gameXaAPI->launchGame($player_id_gamexa, $game_uid, $currency_for_game); // <-- Tambahkan $currency_for_game
    writeLog("Respons dari API GameXa: " . json_encode($launch_response));

    if ($launch_response['success'] && isset($launch_response['data']['game_launch_url'])) {
        $game_url = $launch_response['data']['game_launch_url'];
        $session_id = $launch_response['data']['session_id'] ?? 'N/A'; // Ambil session_id jika ada

        writeLog("Game berhasil diluncurkan.");
        writeLog("URL Game: " . $game_url);
        writeLog("Session ID GameXa: " . $session_id);
        writeLog("Keterangan: Pemain diarahkan ke URL game.");

        // Redirect pemain ke URL game yang diterima dari GameXa API
        header("Location: " . $game_url);
        exit();
    } else {
        // Tangani jika peluncuran game gagal
        $errorMessage = $launch_response['message'] ?? 'Error tidak diketahui saat meluncurkan game.';
        $errorCode = $launch_response['code'] ?? 'N/A';

        writeLog("Gagal meluncurkan game. Error Code: {$errorCode}, Message: " . $errorMessage, 'ERROR');
        writeLog("Keterangan: Kegagalan dari respons API GameXa.", 'ERROR');

        echo "Gagal meluncurkan game: " . htmlspecialchars($errorMessage);
        error_log("Failed to launch game (game_uid: {$game_uid}, player_id: {$player_id_gamexa}). Response: " . json_encode($launch_response));
        exit();
    }

} catch (Exception $e) {
    // Tangani error umum atau exception PHP
    $exceptionMessage = $e->getMessage();
    $exceptionTrace = $e->getTraceAsString();

    writeLog("Terjadi pengecualian saat meluncurkan game: " . $exceptionMessage, 'CRITICAL');
    writeLog("Stack Trace Pengecualian: " . $exceptionTrace, 'CRITICAL');
    writeLog("Keterangan: Kesalahan internal PHP.", 'CRITICAL');

    echo "Terjadi kesalahan saat meluncurkan game: " . htmlspecialchars($exceptionMessage);
    error_log("Exception in play-gamexa-slot.php: " . $exceptionMessage . " - Stack Trace: " . $exceptionTrace);
    exit();
}