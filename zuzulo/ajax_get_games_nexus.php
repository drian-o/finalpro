<?php
header('Content-Type: application/json');

session_start();
include_once '../koneksi.php';

// Pastikan pengguna adalah admin dan sudah login
if (!isset($_SESSION['kode_admin'])) {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak.']);
    exit();
}

$provider_code = isset($_GET['provider_code']) ? mysqli_real_escape_string($koneksi, $_GET['provider_code']) : null;

$response = [
    'success' => false,
    'message' => 'Provider code tidak valid.',
    'providerName' => '',
    'games' => []
];

if ($provider_code) {
    // Ambil nama provider
    $query_provider = mysqli_query($koneksi, "SELECT provider_name FROM nexus_provider WHERE provider_code = '{$provider_code}' LIMIT 1");
    if ($query_provider && $data_provider = mysqli_fetch_assoc($query_provider)) {
        $response['providerName'] = $data_provider['provider_name'];

        // Ambil daftar game dari nexus_gamelist
        $query_games = mysqli_query($koneksi, "SELECT game_code, game_name, game_image_local, game_image_url_api, game_status FROM nexus_gamelist WHERE provider_code = '{$provider_code}' ORDER BY game_name ASC");

        if ($query_games && mysqli_num_rows($query_games) > 0) {
            $games_array = [];
            while ($game = mysqli_fetch_assoc($query_games)) {
                $gambar_src = '';
                if (!empty($game['game_image_local']) && file_exists('../' . $game['game_image_local'])) {
                    $gambar_src = '../' . $game['game_image_local'];
                } elseif (!empty($game['game_image_url_api'])) {
                    $gambar_src = $game['game_image_url_api'];
                } else {
                    $gambar_src = '../upload/no-image.png';
                }
                
                $game['game_image'] = $gambar_src;
                $games_array[] = $game;
            }
            $response['success'] = true;
            $response['message'] = 'Game berhasil dimuat.';
            $response['games'] = $games_array;
        } else {
            $response['success'] = true; // Anggap sukses, hanya saja tidak ada game
            $response['message'] = 'Tidak ada game yang tersedia untuk provider ini.';
        }
    } else {
        $response['message'] = 'Provider tidak ditemukan.';
    }
}

echo json_encode($response);
exit();
?>