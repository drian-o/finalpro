<?php
// admin/proses/fetch_transactions.php
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

try {
    $exaAPI = new GameXaAPI();

    // Ambil parameter paginasi dan filter dari request GET/POST (jika ada)
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 100; // Default limit per panggilan
    $search = $_GET['search'] ?? null;
    $type = $_GET['type'] ?? null;
    $status = $_GET['status'] ?? null;
    $startDate = $_GET['startDate'] ?? date('Y-m-d', strtotime('-7 days'));
    $endDate = $_GET['endDate'] ?? date('Y-m-d');

    // Panggil API getAllTransactions
    $apiResponse = $exaAPI->getAllTransactions($page, $limit, $search, $type, $status, $startDate, $endDate);

    if ($apiResponse['success'] ?? false) {
        $response['status'] = 'success';
        $response['data'] = $apiResponse['data']['transactions'] ?? [];
        $response['message'] = 'Data transaksi berhasil dimuat.';
    } else {
        $response['message'] = 'Gagal mengambil data transaksi dari API: ' . ($apiResponse['message'] ?? 'Respon tidak valid.');
        error_log("Error fetch_transactions.php API: " . $response['message'] . " - " . json_encode($apiResponse));
    }
} catch (Exception $e) {
    $response['message'] = 'Terjadi kesalahan saat memanggil API: ' . $e->getMessage();
    error_log("Exception in fetch_transactions.php: " . $e->getMessage());
}

echo json_encode($response);
exit();
?>