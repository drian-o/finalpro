<?php
header('Content-Type: application/json');
session_start();

// Asumsi: root directory admin adalah satu tingkat di atas folder ajax
include_once '../../koneksi.php';
include_once '../../classes/class.nexusggr.php';
include_once '../../classes/connectAPI.php';

$response = [
    'success' => false,
    'message' => 'Akses ditolak.'
];

if (!isset($_SESSION['kode_admin'])) {
    echo json_encode($response);
    exit();
}

$start_time = isset($_POST['start_time']) ? $_POST['start_time'] : null;
$end_time = isset($_POST['end_time']) ? $_POST['end_time'] : null;

if (!$start_time || !$end_time) {
    $response['message'] = 'Waktu mulai dan selesai harus diisi.';
    echo json_encode($response);
    exit();
}

try {
    $nexus_api = new API($user_agent, $signature);

    // Panggil fungsi history_bet dengan parameter waktu
    // Perhatikan: fungsi history_bet() di class.nexusggr.php Anda saat ini tidak menerima parameter.
    // Saya asumsikan Anda akan memodifikasi class.nexusggr.php agar mendukungnya.
    // Jika tidak, Anda bisa memodifikasi di sini. Contoh modifikasi di sini:
    $postdata = [
        'method' => 'get_game_log',
        'agent_code' => $user_agent,
        'agent_token' => $signature,
        'game_type' => 'slot',
        'start' => $start_time,
        'end' => $end_time,
        'page' => 0,
        'perPage' => 1000
    ];
    $api_response = $nexus_api->send_request($postdata, 'https://api.nexusggr.com');

    if (isset($api_response['status']) && $api_response['status'] == 1) {
        $response['success'] = true;
        $response['message'] = 'Data berhasil dimuat.';
        $response['transactions'] = $api_response['slot']; // Sesuaikan dengan nama key di respons API
    } else {
        $response['message'] = $api_response['msg'] ?? 'Gagal mengambil data dari API.';
    }

} catch (Exception $e) {
    $response['message'] = 'Terjadi kesalahan sistem: ' . $e->getMessage();
}

echo json_encode($response);
exit();
?>