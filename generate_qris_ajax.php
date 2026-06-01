<?php
// generate_qris_ajax.php
header('Content-Type: application/json');

// Jika menggunakan Composer untuk library QR Code
require_once __DIR__ . '/vendor/autoload.php'; 

use chillerlan\QRCode\{QRCode, QROptions};

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once 'koneksi.php'; // Diasumsikan menyediakan $koneksi dan $alamat_website

define('QRIS_MERCHANT_UID', 'f0470e36-3904-4488-82e8-259d9acbe879'); // GANTI DENGAN MERCHANT UID ANDA
define('QRIS_API_GENERATE_URL', 'https://rest.otomatis.vip/api/generate');
define('QR_IMAGE_SAVE_PATH', __DIR__ . '/folder_qr/'); 
// Pastikan $alamat_website diakhiri dengan slash jika belum
$alamat_website_base = isset($alamat_website) ? rtrim($alamat_website, '/') . '/' : 'https://77cair.xyz/';
define('QR_IMAGE_PUBLIC_URL_BASE', $alamat_website_base . 'folder_qr/');

$logFileDir = __DIR__ . '/logs/';
$logFilePath = $logFileDir . 'generate_qris_ajax.log';
if (!is_dir($logFileDir)) { @mkdir($logFileDir, 0775, true); }
if (!is_dir(QR_IMAGE_SAVE_PATH)) { @mkdir(QR_IMAGE_SAVE_PATH, 0775, true); }

function log_message_qris_gen($message) {
    global $logFilePath;
    $timestamp = date("Y-m-d H:i:s");
    file_put_contents($logFilePath, "[{$timestamp}] " . $message . PHP_EOL, FILE_APPEND | LOCK_EX);
}

$response_ajax = ['success' => false, 'message' => 'Terjadi kesalahan umum.'];

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['nama_pengguna_anggota'])) {
    $response_ajax['message'] = 'Sesi tidak valid atau pengguna belum login.';
    log_message_qris_gen("Error: Sesi tidak valid. Request dari IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'N/A'));
    echo json_encode($response_ajax);
    exit;
}
$username_pemain = $_SESSION['nama_pengguna_anggota'];
$id_anggota_session = $_SESSION['id_anggota'] ?? null;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response_ajax['message'] = 'Metode request tidak valid.';
    log_message_qris_gen("Error: Metode request bukan POST. User: {$username_pemain}");
    echo json_encode($response_ajax);
    exit;
}

if (!isset($_POST['jumlah_deposit']) || !is_numeric($_POST['jumlah_deposit'])) {
    $response_ajax['message'] = 'Jumlah deposit tidak valid.';
    log_message_qris_gen("Error: Jumlah deposit tidak valid. User: {$username_pemain}, Input: " . print_r($_POST, true));
    echo json_encode($response_ajax);
    exit;
}

$amount = intval($_POST['jumlah_deposit']);

if ($amount < 2000) {
    $response_ajax['message'] = 'Minimum deposit adalah IDR 2.000.';
    log_message_qris_gen("Error: Jumlah deposit kurang dari minimum. User: {$username_pemain}, Amount: {$amount}");
    echo json_encode($response_ajax);
    exit;
}

log_message_qris_gen("Attempting to generate QRIS data string for user: {$username_pemain}, amount: {$amount}");
$payload = json_encode(['username' => $username_pemain, 'amount' => $amount, 'uuid' => QRIS_MERCHANT_UID]);

$ch = curl_init(QRIS_API_GENERATE_URL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Content-Length: ' . strlen($payload)]);
curl_setopt($ch, CURLOPT_TIMEOUT, 45);
$api_response_raw = curl_exec($ch); $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch); curl_close($ch);

if ($curl_error) {
    $response_ajax['message'] = 'Gagal menghubungi server QRIS (cURL Error): ' . $curl_error;
    log_message_qris_gen("cURL Error for user {$username_pemain}: " . $curl_error);
} elseif ($api_response_raw === false) {
    $response_ajax['message'] = 'Tidak ada respons dari server QRIS.';
    log_message_qris_gen("No response from QRIS server for user {$username_pemain}. HTTP Code: {$http_code}");
} else {
    log_message_qris_gen("QRIS API Data Response for user {$username_pemain} (HTTP {$http_code}): " . $api_response_raw);
    $api_response_data = json_decode($api_response_raw, true);

    if ($http_code == 200 && isset($api_response_data['status']) && $api_response_data['status'] === true) {
        if (isset($api_response_data['data']) && isset($api_response_data['trx_id'])) {
            $qrisDataString = $api_response_data['data']; $trxId = $api_response_data['trx_id'];
            $qrCodeImageFileName = $trxId . '.png';
            $qrCodeImagePathOnServer = QR_IMAGE_SAVE_PATH . $qrCodeImageFileName;
            $qrCodePublicUrl = QR_IMAGE_PUBLIC_URL_BASE . $qrCodeImageFileName;
            try {
                $options = new QROptions([
                    'version' => 5, 'outputType' => QRCode::OUTPUT_IMAGE_PNG, 'eccLevel' => QRCode::ECC_L,
                    'scale' => 10, 'imageBase64' => false, 'imageTransparent' => false,
                    'backgroundColor' => '#FFFFFF', 'foregroundColor' => '#000000', 'quietzoneSize' => 2,
                ]);
                (new QRCode($options))->render($qrisDataString, $qrCodeImagePathOnServer);

                if (file_exists($qrCodeImagePathOnServer)) {
                    $response_ajax['success'] = true; $response_ajax['message'] = 'QRIS berhasil dibuat.';
                    $response_ajax['qris_image_url'] = $qrCodePublicUrl; $response_ajax['trx_id'] = $trxId;
                    $response_ajax['amount'] = $amount;
                    log_message_qris_gen("QRIS image successfully generated and saved for user {$username_pemain}. TRX ID: {$trxId}, URL: {$qrCodePublicUrl}");
                } else {
                    $response_ajax['message'] = 'Gagal menyimpan gambar QRIS di server.';
                    log_message_qris_gen("Failed to save QRIS image file for user {$username_pemain}. TRX ID: {$trxId}, Path: {$qrCodeImagePathOnServer}");
                }
            } catch (Exception $e) {
                $response_ajax['message'] = 'Gagal membuat gambar QRIS: ' . $e->getMessage();
                log_message_qris_gen("Exception generating QRIS image for {$username_pemain}, TRX ID: {$trxId}: " . $e->getMessage());
            }
        } else {
            $response_ajax['message'] = 'Respons API QRIS sukses namun data string atau TRX ID tidak lengkap.';
            log_message_qris_gen("QRIS API success but incomplete data for {$username_pemain}. Resp: " . $api_response_raw);
        }
    } elseif (isset($api_response_data['error'])) {
        $response_ajax['message'] = 'API QRIS Error: ' . htmlspecialchars($api_response_data['error']);
        log_message_qris_gen("QRIS API Error for {$username_pemain}: " . $api_response_data['error'] . ". Raw: " . $api_response_raw);
    } else {
        $response_ajax['message'] = 'Gagal memproses respons dari server QRIS (HTTP: {$http_code}).';
        log_message_qris_gen("Failed to process QRIS server resp for {$username_pemain}. HTTP: {$http_code}, Raw: " . $api_response_raw);
    }
}
if (isset($koneksi) && $koneksi instanceof mysqli) { $koneksi->close(); }
echo json_encode($response_ajax);
exit;
?>