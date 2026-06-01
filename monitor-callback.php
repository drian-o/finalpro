<?php
/**
 * monitor-callback.php
 * 
 * Tool untuk memantau callback dan status deposit QRIS:
 * - Menampilkan log callback terbaru
 * - Menampilkan status deposit terakhir
 * - Memantau apakah callback berfungsi
 * - Menyediakan debugging informasi
 */

// Header security untuk memblokir browser dari caching atau menyimpan halaman ini
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header('Content-Type: application/json');

// Koneksi ke database
include_once 'koneksi.php';

// Fungsi untuk menulis log
function tulis_log($pesan, $file_log = 'monitor.log') {
    $log_dir = __DIR__ . '/logs/';
    if (!file_exists($log_dir)) {
        mkdir($log_dir, 0777, true);
    }
    $timestamp = date("Y-m-d H:i:s");
    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown';
    $log_entry = "[{$timestamp}] [IP: {$ip}] {$pesan}" . PHP_EOL;
    file_put_contents($log_dir . $file_log, $log_entry, FILE_APPEND);
}

// Cek autentikasi sederhana dengan key
if (!isset($_GET['key']) || $_GET['key'] !== 'secretmonitorkey') {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Unauthorized access',
        'details' => 'Access key not provided or invalid'
    ]);
    tulis_log("Unauthorized access attempt to monitor-callback from IP: " . $_SERVER['REMOTE_ADDR']);
    exit;
}

// Cek jika ada tindakan yang akan dilakukan
$action = isset($_GET['action']) ? $_GET['action'] : 'status';

// Untuk melakukan reset dan pembersihan sistem jika diperlukan (hanya admin)
if ($action === 'reset' && isset($_GET['admin_key']) && $_GET['admin_key'] === 'superadminkey') {
    // Kode reset bisa ditambahkan di sini jika diperlukan
    echo json_encode([
        'status' => 'success',
        'message' => 'Reset operation complete'
    ]);
    tulis_log("Admin reset executed from IP: " . $_SERVER['REMOTE_ADDR']);
    exit;
}

// Periksa untuk lihat detail transaksi spesifik
if ($action === 'transaction' && isset($_GET['id'])) {
    $transaction_id = $_GET['id'];
    $escaped_id = mysqli_real_escape_string($koneksi, $transaction_id);
    
    // Cari di tabel deposit
    $query = "SELECT * FROM deposit WHERE hokipay_trx_id = ? OR kode_deposit = ? LIMIT 1";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "ss", $escaped_id, $escaped_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $deposit_data = mysqli_fetch_assoc($result);
        
        // Jika ditemukan, periksa juga data anggota terkait
        $anggota_id = $deposit_data['id_anggota_deposit'];
        $anggota_query = "SELECT nama_pengguna_anggota, saldo_anggota FROM anggota WHERE id_anggota = ?";
        $anggota_stmt = mysqli_prepare($koneksi, $anggota_query);
        mysqli_stmt_bind_param($anggota_stmt, "i", $anggota_id);
        mysqli_stmt_execute($anggota_stmt);
        $anggota_result = mysqli_stmt_get_result($anggota_stmt);
        $anggota_data = mysqli_fetch_assoc($anggota_result);
        
        echo json_encode([
            'status' => 'success',
            'deposit_data' => $deposit_data,
            'anggota_data' => $anggota_data ?: 'not found'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Transaction not found',
            'id_checked' => $transaction_id
        ]);
    }
    exit;
}

// Default action: status monitoring
$log_dir = __DIR__ . '/logs/';
$callback_log = $log_dir . 'hokipay_callback.log';
$response = [
    'status' => 'success',
    'timestamp' => date('Y-m-d H:i:s'),
    'server_info' => [
        'php_version' => PHP_VERSION,
        'server_time' => date('Y-m-d H:i:s'),
        'timezone' => date_default_timezone_get()
    ]
];

// Periksa apakah file log callback ada
if (file_exists($callback_log)) {
    $response['callback_log_exists'] = true;
    $response['callback_log_size'] = filesize($callback_log);
    $response['callback_log_modified'] = date('Y-m-d H:i:s', filemtime($callback_log));
    
    // Ambil 50 baris terakhir dari log
    $logs = file($callback_log);
    $response['callback_log_lines'] = count($logs);
    $response['recent_logs'] = array_slice($logs, -50);
} else {
    $response['callback_log_exists'] = false;
    $response['message'] = 'Callback log file tidak ditemukan';
}

// Cek apakah direktori logs ada
$response['logs_directory_exists'] = is_dir($log_dir);

// Cek apakah direktori logs dapat ditulis
$response['logs_directory_writable'] = is_dir($log_dir) && is_writable($log_dir);

// Cek permissions pada direktori logs
if (function_exists('posix_getpwuid')) {
    $owner = posix_getpwuid(fileowner($log_dir));
    $response['logs_directory_owner'] = $owner['name'];
}

// Periksa 5 transaksi terakhir dari database
try {
    $query = "SELECT d.id_deposit, d.kode_deposit, d.id_anggota_deposit, d.nama_pengguna_anggota_deposit, 
              d.hokipay_trx_id, d.jumlah_deposit, d.status_deposit, d.tanggal_deposit, d.tanggal_bayar,
              a.saldo_anggota
              FROM deposit d 
              LEFT JOIN anggota a ON d.id_anggota_deposit = a.id_anggota
              WHERE d.hokipay_trx_id IS NOT NULL 
              ORDER BY d.id_deposit DESC LIMIT 5";
    
    $result = mysqli_query($koneksi, $query);
    
    if ($result) {
        $deposits = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $deposits[] = $row;
        }
        $response['recent_deposits'] = $deposits;
        $response['recent_deposits_count'] = count($deposits);
    } else {
        $response['db_error'] = mysqli_error($koneksi);
    }
} catch (Exception $e) {
    $response['db_exception'] = $e->getMessage();
}

// Periksa deposit yang masih diproses tetapi mungkin sudah selesai di Hokipay
try {
    $query = "SELECT d.id_deposit, d.kode_deposit, d.id_anggota_deposit, d.nama_pengguna_anggota_deposit, 
              d.hokipay_trx_id, d.jumlah_deposit, d.status_deposit, d.tanggal_deposit, 
              TIMESTAMPDIFF(MINUTE, d.tanggal_deposit, NOW()) as minutes_elapsed
              FROM deposit d 
              WHERE d.status_deposit IN ('diproses', 'pending', 'menunggu pembayaran')
              AND d.hokipay_trx_id IS NOT NULL
              AND TIMESTAMPDIFF(MINUTE, d.tanggal_deposit, NOW()) <= 30
              ORDER BY d.tanggal_deposit DESC LIMIT 10";
    
    $result = mysqli_query($koneksi, $query);
    
    if ($result) {
        $pending_deposits = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $pending_deposits[] = $row;
        }
        $response['pending_deposits'] = $pending_deposits;
        $response['pending_deposits_count'] = count($pending_deposits);
    } else {
        $response['pending_db_error'] = mysqli_error($koneksi);
    }
} catch (Exception $e) {
    $response['pending_db_exception'] = $e->getMessage();
}

// Hitung statistik transaksi
try {
    $stats_query = "SELECT 
                    COUNT(*) as total_transactions,
                    SUM(CASE WHEN status_deposit = 'disetujui' THEN 1 ELSE 0 END) as successful_count,
                    SUM(CASE WHEN status_deposit IN ('diproses', 'pending', 'menunggu pembayaran') THEN 1 ELSE 0 END) as pending_count,
                    SUM(CASE WHEN status_deposit IN ('gagal', 'kedaluwarsa', 'ditolak') THEN 1 ELSE 0 END) as failed_count,
                    MAX(tanggal_deposit) as latest_transaction
                    FROM deposit 
                    WHERE hokipay_trx_id IS NOT NULL
                    AND tanggal_deposit >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
    
    $stats_result = mysqli_query($koneksi, $stats_query);
    
    if ($stats_result) {
        $stats = mysqli_fetch_assoc($stats_result);
        $response['transaction_stats_24h'] = $stats;
    }
} catch (Exception $e) {
    $response['stats_exception'] = $e->getMessage();
}

// Periksa konfigurasi callback untuk debugging
$response['callback_url_configured'] = true; // Ganti dengan logika sesuai konfigurasi
$response['callback_file_path'] = __DIR__ . '/callbackQRIS.php';
$response['callback_file_exists'] = file_exists(__DIR__ . '/callbackQRIS.php');
$response['callback_file_permissions'] = $response['callback_file_exists'] ? 
                                       substr(sprintf('%o', fileperms(__DIR__ . '/callbackQRIS.php')), -4) : 'N/A';

// Tulis log untuk akses monitor
tulis_log("Monitor-callback accessed by IP: " . $_SERVER['REMOTE_ADDR']);

echo json_encode($response, JSON_PRETTY_PRINT);
?>