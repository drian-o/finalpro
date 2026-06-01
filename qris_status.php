<?php
// qris_status.php
date_default_timezone_set('Asia/Jakarta'); 
header('Content-Type: application/json');

// --- Konfigurasi Logging ---
$logFileDir = __DIR__ . '/logs/';
$logFilePath = $logFileDir . 'qris_status.log';
// Pastikan direktori log ada
if (!is_dir($logFileDir)) {
    @mkdir($logFileDir, 0775, true);
}

// Fungsi untuk logging
function log_qris_status($message) {
    global $logFilePath;
    $timestamp = date("Y-m-d H:i:s");
    file_put_contents($logFilePath, "[{$timestamp}] " . $message . PHP_EOL, FILE_APPEND | LOCK_EX);
}
// --- Akhir Konfigurasi Logging ---

$statusDir = __DIR__ . '/status_pembayaran/'; 

// Log permintaan yang diterima
log_qris_status("Request received. GET parameters: " . json_encode($_GET));

$response = ['payment_status' => 'INVALID_REQUEST', 'error' => 'TRX ID tidak disediakan.'];

if (!isset($_GET['trx_id']) || empty(trim($_GET['trx_id']))) {
    $error_message = "TRX ID not provided or empty.";
    log_qris_status("Error: " . $error_message);
    echo json_encode($response);
    exit;
}

$trxIdRaw = $_GET['trx_id'];
log_qris_status("TRX ID raw: '" . $trxIdRaw . "'");

$trxId = trim($trxIdRaw);
// Sanitasi TRX ID untuk mencegah Directory Traversal atau masalah nama file ilegal.
// Hanya izinkan karakter alfanumerik, underscore, dan hyphen. Kemudian ambil basename.
$trxIdSanitized = basename(preg_replace("/[^a-zA-Z0-9_-]/", "", $trxId));
log_qris_status("TRX ID sanitized: '" . $trxIdSanitized . "' (original: '" . $trxId . "')");

// Jika setelah sanitasi TRX ID menjadi kosong, anggap tidak valid
if (empty($trxIdSanitized)) {
    $response = ['payment_status' => 'INVALID_TRX_ID', 'error' => 'TRX ID tidak valid setelah sanitasi.'];
    log_qris_status("Error: TRX ID '" . $trxIdRaw . "' became empty after sanitization.");
    echo json_encode($response);
    exit;
}

$statusFilePath = $statusDir . $trxIdSanitized . '.txt';
log_qris_status("Checking status file path: '" . $statusFilePath . "'");

$paymentStatus = 'PENDING';
$statusFromFile = '';

if (file_exists($statusFilePath)) {
    log_qris_status("Status file FOUND for TRX ID: '" . $trxIdSanitized . "' at path '" . $statusFilePath . "'. Attempting to read content.");
    $file_content = @file_get_contents($statusFilePath); // Gunakan @ untuk suppress warning jika gagal baca
    
    if ($file_content === false) {
        log_qris_status("Error reading status file for TRX ID: '" . $trxIdSanitized . "' at path '" . $statusFilePath . "'. Check permissions.");
        // Kita tetap kembalikan PENDING karena file ada tapi tidak bisa dibaca,
        // yang mungkin berarti proses callback belum selesai atau ada masalah izin.
    } else {
        $statusFromFile = trim(strtoupper($file_content));
        log_qris_status("Status file content raw: '" . $file_content . "'. Trimmed/Uppercased: '" . $statusFromFile . "'");

        if (!empty($statusFromFile)) {
            if ($statusFromFile === 'SUCCESS' || $statusFromFile === 'PAID') {
                $paymentStatus = 'SUCCESS';
            } elseif ($statusFromFile === 'FAILED' || $statusFromFile === 'EXPIRED') {
                $paymentStatus = $statusFromFile;
            } else {
                // Status tidak dikenal dari file, log saja dan biarkan PENDING
                log_qris_status("Unknown status content in file for TRX ID: '" . $trxIdSanitized . "'. Content: '" . $statusFromFile . "'. Status remains PENDING.");
            }
        } else {
            log_qris_status("Status file for TRX ID: '" . $trxIdSanitized . "' is empty. Status remains PENDING.");
        }
    }
} else {
    log_qris_status("Status file NOT found for TRX ID: '" . $trxIdSanitized . "' at path '" . $statusFilePath . "'. Status remains PENDING.");
}

$response = ['payment_status' => $paymentStatus, 'trx_id' => $trxIdSanitized];
log_qris_status("Sending response: " . json_encode($response));

echo json_encode($response);
exit;
?>