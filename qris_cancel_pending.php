<?php
// qris_cancel_pending.php
// Skrip ini dijalankan oleh cron job untuk membatalkan semua transaksi deposit yang kedaluwarsa.

// Sertakan file koneksi database Anda
// Pastikan path-nya benar sesuai dengan struktur direktori Anda
include_once __DIR__ . '/koneksi.php';

// --- Konfigurasi Logging ---
$logFileDir = __DIR__ . '/logs/';
$logFilePath = $logFileDir . 'deposit_cancel_cron.log'; // Ubah nama log file untuk mencerminkan cakupan yang lebih luas
if (!is_dir($logFileDir)) { @mkdir($logFileDir, 0775, true); }

function log_cancel_cron($message) {
    global $logFilePath;
    $timestamp = date("Y-m-d H:i:s");
    file_put_contents($logFilePath, "[{$timestamp}] " . $message . PHP_EOL, FILE_APPEND);
}
// --- Akhir Konfigurasi Logging ---

log_cancel_cron("Starting all pending deposit transaction cancellation script.");

// Tetapkan durasi kedaluwarsa (dalam menit).
// Sesuaikan nilai ini sesuai kebijakan Anda. Misalnya, 15 menit untuk transfer bank.
$expirationMinutes = 5; 

// Hitung batas waktu (waktu sekarang dikurangi X menit)
$cutoffTime = date('Y-m-d H:i:s', strtotime("-{$expirationMinutes} minutes"));
log_cancel_cron("Cutoff time for cancellation is: {$cutoffTime}");

// Kueri baru: Hapus kondisi 'asal_deposit'
$query = "SELECT transaction_id, qris_file FROM deposit WHERE status_deposit = 'diproses' AND waktu_mulai <= ?";

$stmt = $koneksi->prepare($query);

if (!$stmt) {
    log_cancel_cron("Failed to prepare the SELECT statement. Error: " . $koneksi->error);
    if (isset($koneksi) && $koneksi instanceof mysqli) { $koneksi->close(); }
    exit;
}

$stmt->bind_param("s", $cutoffTime);
$stmt->execute();
$result = $stmt->get_result();

$transactions_to_cancel = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (empty($transactions_to_cancel)) {
    log_cancel_cron("No pending transactions found that need to be canceled.");
    if (isset($koneksi) && $koneksi instanceof mysqli) { $koneksi->close(); }
    exit;
}

log_cancel_cron("Found " . count($transactions_to_cancel) . " transactions to cancel.");

// Mulai transaksi database untuk memastikan operasi atomik
$koneksi->begin_transaction();
$canceledCount = 0;

try {
    foreach ($transactions_to_cancel as $transaction) {
        $trxId = $transaction['transaction_id'];
        $qrisFilePath = !empty($transaction['qris_file']) ? __DIR__ . '/' . $transaction['qris_file'] : null;

        // Update status di database menjadi 'dibatalkan'
        $stmt_update = $koneksi->prepare("UPDATE deposit SET status_deposit = 'dibatalkan' WHERE transaction_id = ? AND status_deposit = 'diproses'");
        if (!$stmt_update) {
            throw new Exception("Failed to prepare UPDATE statement. Error: " . $koneksi->error);
        }
        $stmt_update->bind_param("s", $trxId);
        $stmt_update->execute();

        if ($stmt_update->affected_rows > 0) {
            log_cancel_cron("Successfully canceled transaction with TRX ID: {$trxId}.");
            $canceledCount++;

            // Hapus file gambar QRIS jika ada
            if ($qrisFilePath && file_exists($qrisFilePath) && is_writable($qrisFilePath)) {
                if (@unlink($qrisFilePath)) {
                    log_cancel_cron("Successfully deleted QRIS file: {$qrisFilePath}");
                } else {
                    log_cancel_cron("Failed to delete QRIS file: {$qrisFilePath}");
                }
            }
            
            // Hapus file status polling jika ada
            $statusPollingFilePath = __DIR__ . '/status_pembayaran/' . basename(preg_replace("/[^a-zA-Z0-9_-]/", "", $trxId)) . '.txt';
            if (file_exists($statusPollingFilePath) && is_writable($statusPollingFilePath)) {
                if (@unlink($statusPollingFilePath)) {
                    log_cancel_cron("Successfully deleted polling status file: {$statusPollingFilePath}");
                } else {
                    log_cancel_cron("Failed to delete polling status file: {$statusPollingFilePath}");
                }
            }
        }
        $stmt_update->close();
    }

    $koneksi->commit();
    log_cancel_cron("Database transaction committed. Total canceled: {$canceledCount}");

} catch (Exception $e) {
    $koneksi->rollback();
    log_cancel_cron("Database transaction rolled back. Error: " . $e->getMessage());
}

if (isset($koneksi) && $koneksi instanceof mysqli) { $koneksi->close(); }
log_cancel_cron("Script finished.");
?>