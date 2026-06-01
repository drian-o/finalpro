<?php
// update_saldo.php (Sinkronisasi Saldo Nexus ke Exa)

$script_start_time = microtime(true);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- Includes & Konfigurasi ---
include_once 'koneksi.php';
include_once 'classes/class.exa.php';
include_once 'classes/class.nexusggr.php';
include_once 'classes/connectAPI.php'; // Sertakan ini untuk kredensial Nexus

$logFileDir = __DIR__ . '/logs/';
$logFilePath = $logFileDir . 'balance_sync_nexus_to_exa.log';

// --- Fungsi Bantuan ---
function write_log($message) {
    global $logFilePath;
    $entry = "[" . date("Y-m-d H:i:s") . "] " . $message . "\n";
    file_put_contents($logFilePath, $entry, FILE_APPEND | LOCK_EX);
}

function exit_with_error($userMessage, $logMessage, $critical_log_details = null) {
    global $script_start_time;
    write_log("ERROR: " . $logMessage);
    if ($critical_log_details) {
        error_log("CRITICAL (update_saldo.php): " . $logMessage . " | Details: " . $critical_log_details);
    }
    $duration = number_format(microtime(true) - $script_start_time, 4);
    write_log("Total Script Execution Duration: {$duration}s");
    write_log("==== BALANCE SYNC END (ERROR) ====\n");
    echo $userMessage;
    exit;
}

// --- Mulai Eksekusi ---
if (!is_dir($logFileDir)) { @mkdir($logFileDir, 0755, true); }
write_log("==== BALANCE SYNC ATTEMPT ====");

if (!isset($_SESSION['nama_pengguna_anggota'], $_SESSION['id_anggota'])) {
    exit_with_error("Kesalahan: Pengguna belum login.", "Sesi tidak lengkap atau pengguna belum login.");
}

$nama_pengguna = $_SESSION['nama_pengguna_anggota'];
write_log("User Logged In: {$nama_pengguna}");

// --- Dapatkan ID pengguna dari database lokal ---
$id_sigma = null;
$id_nexus = null;
$local_db_balance = null;
$stmt_get_ids = $koneksi->prepare("SELECT id_sigma, id_nexus, saldo_anggota FROM anggota WHERE nama_pengguna_anggota = ?");
if (!$stmt_get_ids) {
    exit_with_error("Kesalahan DB.", "Gagal mempersiapkan statement untuk mendapatkan ID: " . $koneksi->error);
}
$stmt_get_ids->bind_param("s", $nama_pengguna);
$stmt_get_ids->execute();
$result_ids = $stmt_get_ids->get_result();
if ($data_ids = $result_ids->fetch_assoc()) {
    $id_sigma = $data_ids['id_sigma'];
    $id_nexus = $data_ids['id_nexus'];
    $local_db_balance = $data_ids['saldo_anggota'];
}
$stmt_get_ids->close();

if (!$id_sigma || !$id_nexus) {
    exit_with_error(
        "Kesalahan: ID GameXa atau Nexus tidak ditemukan untuk akun ini. Harap hubungi Customer Service.",
        "ID GameXa/Nexus tidak ditemukan di DB lokal untuk user: {$nama_pengguna}",
        "User: {$nama_pengguna}"
    );
}
write_log("ID GameXa: {$id_sigma}, ID Nexus: {$id_nexus} for user {$nama_pengguna}");

// --- Inisialisasi API ---
try {
    $GameXaAPI = new GameXaAPI();
    $NexusAPI = new API($user_agent, $signature);
} catch (Exception $e) {
    exit_with_error("Kesalahan konfigurasi API.", "Gagal inisialisasi kelas API: " . $e->getMessage());
}

try {
    $koneksi->begin_transaction(); // Mulai transaksi database

    // STEP 1: Ambil saldo TERBARU dari Provider Nexus
    $step1_start = microtime(true);
    $nexus_balance_response = $NexusAPI->money_info_user($id_nexus);
    write_log("STEP 1: GetBalance from Nexus. Duration: " . number_format(microtime(true) - $step1_start, 4) . "s. Response: " . json_encode($nexus_balance_response));

    $nexus_current_balance = 0;
    // Perbaikan: Navigasi respons API Nexus yang bersarang
    if (($nexus_balance_response['status'] ?? 0) == 1 && isset($nexus_balance_response['user']['balance'])) {
        $nexus_current_balance = floatval($nexus_balance_response['user']['balance']);
        write_log("STEP 1: Saldo Nexus ditemukan: {$nexus_current_balance}");
    } else {
        // Log pesan yang lebih jelas jika gagal menemukan saldo
        write_log("STEP 1: Gagal menemukan saldo Nexus. Respons API tidak valid. Respon: " . json_encode($nexus_balance_response));
    }
    
    // STEP 2: Tarik saldo dari Nexus jika ada
    $transferred_amount = 0;
    if ($nexus_current_balance > 0) {
        $step2_start = microtime(true);
        $reference_id_nexus = "withdraw_" . uniqid(time());
        $nexus_withdraw_response = $NexusAPI->user_withdraw($id_nexus, $nexus_current_balance);
        write_log("STEP 2: Withdraw from Nexus (Amount: {$nexus_current_balance}, RefID: {$reference_id_nexus}). Duration: " . number_format(microtime(true) - $step2_start, 4) . "s. Response: " . json_encode($nexus_withdraw_response));

        if (($nexus_withdraw_response['status'] ?? 0) == 1) {
            $transferred_amount = $nexus_current_balance;
            write_log("STEP 2: Withdraw Nexus berhasil. Jumlah yang ditarik: {$transferred_amount}");
        } else {
            // Jika withdraw Nexus gagal, hentikan proses untuk mencegah data tidak sinkron
            throw new Exception("Withdraw dari Nexus gagal: " . ($nexus_withdraw_response['msg'] ?? 'Unknown error.'));
        }
    } else {
        write_log("STEP 2: Saldo Nexus nol, tidak ada yang ditarik.");
    }
    
    // STEP 3: Deposit saldo ke Exa (termasuk saldo yang ditarik dari Nexus)
    $step3_start = microtime(true);
    $exa_balance_before_deposit = 0;
    $exa_balance_response_pre = $GameXaAPI->getPlayerBalance($id_sigma);
    if (($exa_balance_response_pre['success'] ?? false) && isset($exa_balance_response_pre['data']['balance'])) {
        $exa_balance_before_deposit = floatval($exa_balance_response_pre['data']['balance']);
    }
    
    $exa_total_deposit_amount = $transferred_amount; // Saldo yang ditarik dari Nexus
    
    // Hanya lakukan deposit jika ada saldo yang akan ditransfer
    if ($exa_total_deposit_amount > 0) {
        $reference_id_exa = "deposit_" . uniqid(time());
        $exa_deposit_response = $GameXaAPI->depositToPlayer($id_sigma, $exa_total_deposit_amount, $reference_id_exa);
        write_log("STEP 3: Deposit to GameXa (Amount: {$exa_total_deposit_amount}, RefID: {$reference_id_exa}). Duration: " . number_format(microtime(true) - $step3_start, 4) . "s. Response: " . json_encode($exa_deposit_response));

        if (!($exa_deposit_response['success'] ?? false)) {
            // Jika deposit Exa gagal, hentikan proses dan catat.
            // Dana masih aman di saldo agen, tetapi tidak di akun pengguna Exa.
            throw new Exception("Deposit ke GameXa gagal: " . ($exa_deposit_response['message'] ?? 'Unknown error.'));
        }
    }

    // STEP 4: Ambil saldo AKHIR dari Exa (saldo master)
    $step4_start = microtime(true);
    $gamexa_balance_response = $GameXaAPI->getPlayerBalance($id_sigma);
    write_log("STEP 4: Get Final Balance from GameXa. Duration: " . number_format(microtime(true) - $step4_start, 4) . "s. Response: " . json_encode($gamexa_balance_response));
    
    $gamexa_final_balance = 0;
    if (($gamexa_balance_response['success'] ?? false) && isset($gamexa_balance_response['data']['balance'])) {
        $gamexa_final_balance = floatval($gamexa_balance_response['data']['balance']);
        write_log("STEP 4: Saldo GameXa final: {$gamexa_final_balance}");
    } else {
        throw new Exception("Gagal mendapatkan saldo akhir dari GameXa.");
    }
    
    // STEP 5: Perbarui saldo lokal dengan saldo master dari Exa
    $step5_start = microtime(true);
    $stmt_update_local_saldo = $koneksi->prepare("UPDATE anggota SET saldo_anggota = ? WHERE id_sigma = ?");
    if (!$stmt_update_local_saldo) {
        throw new Exception("DB Error (prepare update local saldo): " . $koneksi->error);
    }
    $stmt_update_local_saldo->bind_param("ds", $gamexa_final_balance, $id_sigma); 
    
    if (!$stmt_update_local_saldo->execute()) {
        throw new Exception("DB Error (execute update local saldo): " . $stmt_update_local_saldo->error);
    }
    $stmt_update_local_saldo->close();
    write_log("STEP 5: Saldo lokal berhasil diupdate menjadi {$gamexa_final_balance}. Duration: " . number_format(microtime(true) - $step5_start, 4) . "s.");
    
    // Selesai, commit transaksi database
    $koneksi->commit();
    write_log("Transaksi database berhasil di-commit.");

    // Finalisasi: Perbarui sesi dengan saldo master dari Exa
    $_SESSION['saldo_anggota'] = $gamexa_final_balance; 
    $saldo_formatted = "IDR " . number_format($gamexa_final_balance, 0, ',', '.'); 
    write_log("Final Saldo Disinkronkan: {$saldo_formatted}.");

    $duration = number_format(microtime(true) - $script_start_time, 4);
    write_log("Total Script Execution Duration: {$duration}s");
    write_log("==== BALANCE SYNC END (SUCCESS) ====\n");
    echo $saldo_formatted;

} catch (Exception $e) {
    // Tangani semua kegagalan di sini
    $koneksi->rollback();
    exit_with_error(
        "Terjadi kesalahan sistem saat sinkronisasi saldo. Harap hubungi Customer Service.",
        "PHP Exception during sync: " . $e->getMessage() . " di baris " . $e->getLine()
    );
} finally {
    if (isset($koneksi) && $koneksi instanceof mysqli) {
        $koneksi->close();
    }
}
?>