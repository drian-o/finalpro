<?php
// admin/proses/fetch_transaction_stats.php
session_start();
header('Content-Type: application/json');

include_once '../../koneksi.php'; // Sesuaikan path jika perlu
include_once '../../classes/class.exa.php'; // Sesuaikan path jika perlu

$response = ['status' => 'error', 'message' => 'Terjadi kesalahan tidak diketahui.', 'data' => []];

// Pastikan admin login
if (!isset($_SESSION['kode_admin'])) {
    $response['message'] = 'Sesi admin tidak valid. Harap login kembali.';
    echo json_encode($response);
    exit();
}

// Ambil parameter tanggal dari request GET
$startDate = $_GET['start_date'] ?? null;
$endDate = $_GET['end_date'] ?? null;

if (empty($startDate) || empty($endDate)) {
    $response['message'] = 'Tanggal mulai dan tanggal berakhir wajib diisi.';
    echo json_encode($response);
    exit();
}

try {
    $exaAPI = new GameXaAPI();

    // Panggil API getTransactionStats
    $apiResponse = $exaAPI->getTransactionStats($startDate, $endDate);

    if ($apiResponse['success'] ?? false) {
        $response['status'] = 'success';
        // Pastikan 'data' ada dan merupakan array/objek yang berisi statistik
        $response['data'] = $apiResponse['data'] ?? [];
        $response['message'] = 'Data statistik berhasil dimuat.';
    } else {
        $response['message'] = 'Gagal mengambil data statistik dari API: ' . ($apiResponse['message'] ?? 'Respon tidak valid.');
        error_log("Error fetch_transaction_stats.php API: " . $response['message'] . " - " . json_encode($apiResponse));
    }
} catch (Exception $e) {
    $response['message'] = 'Terjadi kesalahan saat memanggil API: ' . $e->getMessage();
    error_log("Exception in fetch_transaction_stats.php: " . $e->getMessage());
}

echo json_encode($response);
exit();
?>