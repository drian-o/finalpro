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

// --- Koneksi Database ---
include_once __DIR__ . '/koneksi.php'; // Pastikan koneksi.php ada dan menyediakan objek $koneksi
// --- Akhir Koneksi Database ---

// Log permintaan yang diterima
log_qris_status("Request received. GET parameters: " . json_encode($_GET));

// Inisialisasi respons awal
$response = [
    'payment_status' => 'INVALID_REQUEST', 
    'trx_id' => null, 
    'error' => 'TRX ID tidak disediakan.'
];

if (!isset($_GET['trx_id']) || empty(trim($_GET['trx_id']))) {
    $error_message = "TRX ID not provided or empty.";
    log_qris_status("Error: " . $error_message);
    echo json_encode($response);
    exit;
}

$trxIdRaw = $_GET['trx_id'];
log_qris_status("TRX ID raw: '" . $trxIdRaw . "'");

$trxId = trim($trxIdRaw);
// Sanitasi TRX ID untuk mencegah Directory Traversal atau masalah SQL Injection.
// Hanya izinkan karakter alfanumerik, underscore, dan hyphen.
$trxIdSanitized = preg_replace("/[^a-zA-Z0-9_-]/", "", $trxId);
log_qris_status("TRX ID sanitized: '" . $trxIdSanitized . "' (original: '" . $trxId . "')");

// Set TRX ID sanitized ke respons
$response['trx_id'] = $trxIdSanitized;

// Jika setelah sanitasi TRX ID menjadi kosong, anggap tidak valid
if (empty($trxIdSanitized)) {
    $response['payment_status'] = 'INVALID_TRX_ID';
    $response['error'] = 'TRX ID tidak valid setelah sanitasi.';
    log_qris_status("Error: TRX ID '" . $trxIdRaw . "' became empty after sanitization.");
    echo json_encode($response);
    exit;
}

$paymentStatus = 'PENDING';
$db_error_message = null;

// Cek koneksi database
if (!isset($koneksi) || !($koneksi instanceof mysqli) || $koneksi->connect_error) {
    $response['payment_status'] = 'DB_ERROR';
    $response['error'] = 'Koneksi database tidak tersedia atau gagal.';
    log_qris_status("CRITICAL: Koneksi DB tidak tersedia atau gagal. Error: " . ($koneksi->connect_error ?? 'N/A'));
    echo json_encode($response);
    exit;
}

log_qris_status("Checking database for TRX ID: '" . $trxIdSanitized . "'");

$stmt = $koneksi->prepare("SELECT status_deposit FROM deposit WHERE transaction_id = ? LIMIT 1");
if ($stmt) {
    $stmt->bind_param("s", $trxIdSanitized);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        $status_from_db = strtoupper(trim($data['status_deposit']));
        log_qris_status("Status found in DB for TRX ID: '" . $trxIdSanitized . "'. Status: '" . $status_from_db . "'");

        // Mapping status database ke status respons
        if ($status_from_db === 'DISETUJUI') {
            $paymentStatus = 'SUCCESS';
        } elseif ($status_from_db === 'DIPROSES') {
            $paymentStatus = 'PENDING'; // Tetap pending jika masih diproses
        } elseif ($status_from_db === 'DIBATALKAN') { // Asumsi FAILED/EXPIRED di-map ke 'dibatalkan' di DB
            $paymentStatus = 'FAILED'; // Atau bisa juga 'EXPIRED' jika Anda ingin bedakan
        } else {
            // Status tidak dikenal dari database, biarkan PENDING atau error jika perlu
            log_qris_status("Unknown status from DB for TRX ID: '" . $trxIdSanitized . "'. DB Status: '" . $status_from_db . "'. Status remains PENDING.");
            $response['error'] = 'Status dari database tidak dikenali.';
            $paymentStatus = 'PENDING';
        }
    } else {
        log_qris_status("TRX ID: '" . $trxIdSanitized . "' NOT found in database. Status remains PENDING.");
        $response['error'] = 'Transaksi tidak ditemukan di database.';
        $paymentStatus = 'PENDING';
    }
    $stmt->close();
} else {
    $db_error_message = $koneksi->error;
    $response['payment_status'] = 'DB_QUERY_ERROR';
    $response['error'] = 'Gagal menyiapkan query database: ' . $db_error_message;
    log_qris_status("Error preparing DB query for TRX ID: '" . $trxIdSanitized . "'. Error: " . $db_error_message);
}

// Tutup koneksi database
if (isset($koneksi) && $koneksi instanceof mysqli && $koneksi->thread_id) { $koneksi->close(); }

$response['payment_status'] = $paymentStatus; 
// Jika ada error dari DB_QUERY_ERROR, error message sudah di set.
// Jika tidak, dan paymentStatus adalah PENDING, kita bisa set error default jika belum ada.
if ($response['payment_status'] === 'PENDING' && !isset($response['error'])) {
    $response['error'] = 'Menunggu pembayaran diproses.';
}

log_qris_status("Sending response: " . json_encode($response));

echo json_encode($response);
exit;
?>