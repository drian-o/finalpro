<?php
// Pastikan skrip ini diakses melalui CLI atau dengan otorisasi yang aman
header('Content-Type: text/plain');

include_once 'koneksi.php';
include_once 'classes/class.nexusggr.php'; 

$user_agent = "garudaku12"; 
$signature = "dfeff997e14581f0a035ecc97c778b5b";
$NEXUS = new API($user_agent, $signature);

$koneksi->query("CREATE TABLE IF NOT EXISTS cron_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    process_name VARCHAR(255) NOT NULL,
    status VARCHAR(50) NOT NULL,
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

function log_message($koneksi, $process, $status, $message) {
    if ($status === "SUCCESS" || $status === "ERROR") {
        $stmt = $koneksi->prepare("INSERT INTO cron_log (process_name, status, message) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $process, $status, $message);
        $stmt->execute();
        $stmt->close();
    }
    
    echo "[" . date("Y-m-d H:i:s") . "] " . $status . " - " . $message . "\n";
}

log_message($koneksi, "update_turnover", "INFO", "Cronjob update turnover dimulai. Memanggil API history_bet...");

try {
    $response = $NEXUS->history_bet();
    
    if ($response['status'] != 1) {
        throw new Exception("API Gagal: " . json_encode($response));
    }
    
    $bet_history = $response['slot'] ?? [];
    
    if (empty($bet_history)) {
        log_message($koneksi, "update_turnover", "INFO", "Tidak ada riwayat bet baru dari API. Cronjob selesai.");
        exit();
    }
    
    $members = [];
    $stmt_get_anggota = $koneksi->prepare("SELECT id_anggota, nama_pengguna_anggota, last_turnover_update, turnover_amount, id_nexus FROM anggota WHERE id_nexus IS NOT NULL");
    $stmt_get_anggota->execute();
    $result_anggota = $stmt_get_anggota->get_result();
    while ($row = $result_anggota->fetch_assoc()) {
        $members[$row['id_nexus']] = $row;
    }
    $stmt_get_anggota->close();
    
    $deductions = [];
    $latest_update_times = [];
    
    foreach ($bet_history as $log) {
        $user_code = $log['user_code'];
        $log_created_at = $log['created_at'];

        if (isset($members[$user_code])) {
            $last_update_db = $members[$user_code]['last_turnover_update'];
            $log_timestamp = strtotime($log_created_at);
            $last_update_timestamp = strtotime($last_update_db);

            if ($log_timestamp > $last_update_timestamp) {
                $deductions[$user_code] = ($deductions[$user_code] ?? 0) + $log['bet_money'];
                
                if (!isset($latest_update_times[$user_code]) || $log_created_at > $latest_update_times[$user_code]) {
                    $latest_update_times[$user_code] = $log_created_at;
                }
            }
        }
    }
    
    foreach ($deductions as $user_code => $total_bet_to_deduct) {
        $koneksi->begin_transaction();
        try {
            $id_anggota = $members[$user_code]['id_anggota'];
            $nama_pengguna = $members[$user_code]['nama_pengguna_anggota'];
            $current_turnover = $members[$user_code]['turnover_amount'];

            // === Pengecekan Penting: Pastikan turnover tidak menjadi negatif ===
            $new_turnover = $current_turnover - $total_bet_to_deduct;
            if ($new_turnover < 0) {
                $new_turnover = 0;
            }
            // ====================================================================

            $new_last_update = $latest_update_times[$user_code];

            $stmt_update = $koneksi->prepare("UPDATE anggota SET turnover_amount = ?, last_turnover_update = ? WHERE id_anggota = ?");
            $stmt_update->bind_param("dsi", $new_turnover, $new_last_update, $id_anggota);
            
            if (!$stmt_update->execute()) {
                throw new Exception("Gagal update database untuk " . $nama_pengguna . ": " . $stmt_update->error);
            }
            $stmt_update->close();
            
            log_message($koneksi, "update_turnover", "SUCCESS", "Pembaruan " . $nama_pengguna . " berhasil.");
            
            $koneksi->commit();
        } catch (Exception $e) {
            $koneksi->rollback();
            log_message($koneksi, "update_turnover", "ERROR", "Pembaruan " . $user_code . " gagal. Pesan: " . $e->getMessage());
        }
    }
    
    log_message($koneksi, "update_turnover", "INFO", "Cronjob selesai.");
    
} catch (Exception $e) {
    log_message($koneksi, "update_turnover", "ERROR", "Kesalahan fatal pada cronjob: " . $e->getMessage());
}

$koneksi->close();
?>