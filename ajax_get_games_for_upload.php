<?php
header('Content-Type: application/json');

// Sertakan koneksi database
require_once __DIR__ . '/koneksi.php';

// --- KONFIGURASI KEAMANAN SEDERHANA ---
$allowed_secret_key = 'game_image_secret_key_123'; // HARUS SAMA DENGAN DI admin_game_image_upload.php
$input_secret_key = isset($_GET['key']) ? $_GET['key'] : '';

if ($input_secret_key !== $allowed_secret_key) {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak. Kunci keamanan tidak valid.']);
    exit();
}
// --- AKHIR KONFIGURASI KEAMANAN SEDERHANA ---

$provider_code = isset($_GET['provider_code']) ? mysqli_real_escape_string($koneksi, $_GET['provider_code']) : '';
$provider_server = isset($_GET['provider_server']) ? mysqli_real_escape_string($koneksi, $_GET['provider_server']) : '';

$response = ['success' => false, 'message' => 'Invalid request.', 'games' => []];

if (empty($provider_code) || empty($provider_server)) {
    echo json_encode($response);
    exit();
}

$table_name = '';
if ($provider_server === 'server2') { // SRG
    $table_name = 'srg_gamelist';
} elseif ($provider_server === 'server1') { // Telo
    $table_name = 'telo_gamelist';
} else {
    $response['message'] = 'Server provider tidak dikenal.';
    echo json_encode($response);
    exit();
}

try {
    // Query untuk mengambil game yang game_image_local-nya tidak berawalan 'upload/'
    // atau NULL, atau kosong.
    // Pastikan semua kolom yang diperlukan oleh frontend ada di SELECT
    $query = "SELECT game_code, game_name, game_type, game_status, provider_code, game_image_local 
              FROM {$table_name} 
              WHERE provider_code = '{$provider_code}' 
                AND (game_image_local IS NULL OR game_image_local = '' OR game_image_local NOT LIKE 'upload/%') 
              ORDER BY game_name ASC";

    $result = mysqli_query($koneksi, $query);

    if (!$result) {
        throw new Exception("Gagal mengambil daftar game dari database: " . mysqli_error($koneksi));
    }

    $games = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $games[] = $row;
    }

    $response['success'] = true;
    $response['message'] = 'Daftar game berhasil dimuat.';
    $response['games'] = $games;

} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
    error_log("AJAX get_games_for_upload error: " . $e->getMessage());
}

echo json_encode($response);
mysqli_close($koneksi);
?>