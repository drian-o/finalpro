<?php
// ajax/sync_balance.php

header('Content-Type: application/json'); // Respons dalam format JSON

// Mulai sesi
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- Includes & Konfigurasi (disesuaikan untuk AJAX) ---
include_once __DIR__ . '/../koneksi.php'; // Path relatif dari ajax/
include_once __DIR__ . '/../classes/class.srg.php'; // Path relatif dari ajax/

$logFileDir = __DIR__ . '/../logs/'; // Path relatif dari ajax/
$logFilePath = $logFileDir . 'user_balance_sync_ajax.log'; // Log terpisah untuk AJAX sync

// --- Fungsi Bantuan (disesuaikan untuk AJAX, jangan exit dengan echo HTML) ---
function write_ajax_log($message) {
    global $logFilePath;
    $entry = "[" . date("Y-m-d H:i:s") . "] " . $message . "\n";
    file_put_contents($logFilePath, $entry, FILE_APPEND | LOCK_EX);
}

function return_ajax_error($message, $log_message, $critical_log_details = null) {
    write_ajax_log("ERROR: " . $log_message);
    if ($critical_log_details) {
        error_log("CRITICAL (ajax/sync_balance.php): " . $log_message . " | Details: " . $critical_log_details);
    }
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

// --- Mulai Eksekusi AJAX ---
if (!is_dir($logFileDir)) { @mkdir($logFileDir, 0755, true); }
write_ajax_log("==== SRG BALANCE SYNC (AJAX) ATTEMPT ====");

// Validasi Sesi Pengguna untuk AJAX
if (!isset($_SESSION['nama_pengguna_anggota'], $_SESSION['id_anggota'])) {
    return_ajax_error("Pengguna belum login.", "Sesi tidak lengkap atau pengguna belum login.");
}

$nama_pengguna = $_SESSION['nama_pengguna_anggota'];
write_ajax_log("User logged in (AJAX Sync): {$nama_pengguna}");

// --- Inisialisasi & Validasi Objek API SRGConnect ---
global $SRG; // Mengakses instance global dari class.srg.php
if (!isset($SRG) || !($SRG instanceof SRGConnect)) { 
    return_ajax_error("Kesalahan konfigurasi API.", "Objek SRGConnect tidak ditemukan atau tidak valid.");
}

try {
    // STEP 1: Ambil saldo TERBARU dari Provider SRGConnect (master saldo)
    $srg_balance_response = $SRG->getBalanceUser($nama_pengguna); //
    write_ajax_log("STEP 1: GetBalance from SRG. Response: " . json_encode($srg_balance_response)); //

    $srg_current_balance = 0;
    if ($srg_balance_response !== false && isset($srg_balance_response->balance)) {
        $srg_current_balance = floatval($srg_balance_response->balance); //
        write_ajax_log("STEP 1: Saldo SRG ditemukan: {$srg_current_balance}");
    } else {
        write_ajax_log("STEP 1: Saldo SRG tidak ditemukan atau respons API tidak valid untuk {$nama_pengguna}. Menganggap saldo SRG adalah 0.");
    }

    // STEP 2: Ambil saldo lokal saat ini dari database
    $stmt_get_local_saldo = $koneksi->prepare("SELECT saldo_anggota FROM anggota WHERE nama_pengguna_anggota = ?"); //
    if (!$stmt_get_local_saldo) {
        throw new Exception("DB Error (prepare get local saldo): " . $koneksi->error);
    }
    $stmt_get_local_saldo->bind_param("s", $nama_pengguna); //
    $stmt_get_local_saldo->execute(); //
    $stmt_get_local_saldo->bind_result($local_db_balance); //
    $stmt_get_local_saldo->fetch(); //
    $stmt_get_local_saldo->close(); //
    write_ajax_log("STEP 2: Saldo lokal saat ini dari DB: {$local_db_balance}."); //

    // STEP 3: Bandingkan saldo SRG dengan saldo lokal dan perbarui jika ada ketidaksesuaian
    $updated_in_db = false;
    if (abs($srg_current_balance - $local_db_balance) > 0.001) { // Gunakan toleransi float
        write_ajax_log("STEP 3: Ketidaksesuaian saldo ditemukan! SRG: {$srg_current_balance}, Lokal: {$local_db_balance}. Melakukan update saldo lokal."); //
        
        $stmt_update_local_saldo = $koneksi->prepare("UPDATE anggota SET saldo_anggota = ? WHERE nama_pengguna_anggota = ?"); //
        if (!$stmt_update_local_saldo) {
            throw new Exception("DB Error (prepare update local saldo): " . $koneksi->error);
        }
        $stmt_update_local_saldo->bind_param("ds", $srg_current_balance, $nama_pengguna); // Saldo lokal diupdate langsung ke saldo SRG
        
        if (!$stmt_update_local_saldo->execute()) {
            throw new Exception("DB Error (execute update local saldo): " . $stmt_update_local_saldo->error);
        }
        $stmt_update_local_saldo->close(); //
        $updated_in_db = true;
        write_ajax_log("STEP 3: Saldo lokal berhasil diupdate menjadi {$srg_current_balance}."); //
    } else {
        write_ajax_log("STEP 3: Saldo SRG dan lokal sudah sesuai ({$srg_current_balance}). Tidak ada update database yang diperlukan."); //
    }
    
    // Finalisasi: Perbarui sesi dengan saldo SRG (karena itu adalah saldo master)
    $_SESSION['saldo_anggota'] = $srg_current_balance; 
    $saldo_formatted = number_format($srg_current_balance, 0, ',', '.'); 
    write_ajax_log("Final Saldo Disinkronkan: {$saldo_formatted}.");

    // Kirim respons sukses ke JavaScript
    echo json_encode([
        'success' => true,
        'message' => 'Sinkronisasi saldo berhasil.',
        'new_balance' => $saldo_formatted,
        'raw_balance' => $srg_current_balance,
        'updated_db' => $updated_in_db
    ]);

} catch (Exception $e) {
    return_ajax_error(
        "Terjadi kesalahan saat sinkronisasi saldo.",
        "PHP Exception (AJAX Sync): " . $e->getMessage() . " di baris " . $e->getLine()
    ); //
} finally {
    if (isset($koneksi) && $koneksi instanceof mysqli) {
        $koneksi->close(); //
    }
}
?>