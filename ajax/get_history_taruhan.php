<?php
session_start();
header('Content-Type: application/json');

// Cek apakah user sudah login dan permintaan valid
if (!isset($_SESSION['id_anggota']) || !isset($_SESSION['nama_pengguna_anggota'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak. Mohon login terlebih dahulu.']);
    exit;
}

// Cek apakah parameter tanggal disediakan
if (!isset($_GET['start_date']) || !isset($_GET['end_date'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Data tanggal tidak lengkap.']);
    exit;
}

// Lokasi file
require_once '../classes/connectAPI.php';
require_once '../classes/class.nexusggr.php';

$nama_pengguna_anggota = $_SESSION['nama_pengguna_anggota'];
$start_date_input = $_GET['start_date'];
$end_date_input = $_GET['end_date'];

// Gunakan kombinasi tanggal sebagai kunci sesi
$session_key = 'history_game_' . $start_date_input . '_' . $end_date_input;

// Periksa apakah data sudah ada di sesi
if (isset($_SESSION[$session_key])) {
    $response = $_SESSION[$session_key];
    echo json_encode(['status' => 'success', 'data' => $response['slot'], 'totalItems' => $response['total_count'], 'message' => 'Data diambil dari sesi.']);
    exit;
}

// Jika data tidak ada di sesi, panggil API
$start_datetime = $start_date_input . ' 00:00:00';
$end_datetime = $end_date_input . ' 23:59:59';

try {
    // Inisialisasi objek API
    $api = new API($user_agent, $signature);

    // Panggil API history_game. Ambil semua data dengan perPage maksimal untuk sesi
    $response = $api->history_game(
        $nama_pengguna_anggota,
        'slot',
        $start_datetime,
        $end_datetime,
        0, // Mulai dari halaman 0
        1000 // Ambil 1000 data per halaman (maksimal)
    );

    // Periksa apakah respons API valid
    if (isset($response['status']) && $response['status'] === 1 && isset($response['slot'])) {
        // Simpan respons lengkap ke sesi
        $_SESSION[$session_key] = $response;

        // Format tanggal untuk tampilan sebelum dikirim
        $bets = $response['slot'];
        foreach ($bets as &$bet) {
             $bet['created_at'] = date('Y-m-d H:i:s', strtotime($bet['created_at']));
        }
        unset($bet);

        echo json_encode([
            'status' => 'success',
            'data' => $bets,
            'totalItems' => $response['total_count']
        ]);
    } else {
        $message = isset($response['error']) ? $response['error'] : 'Gagal mengambil data riwayat taruhan dari API. Respons API tidak sesuai.';
        echo json_encode(['status' => 'error', 'message' => $message]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan internal: ' . $e->getMessage()]);
}
?>