<?php
// api_proxy.php
session_start();

// Cek apakah user sudah login. Jika tidak, hentikan eksekusi.
if (!isset($_SESSION['id_anggota']) || !isset($_SESSION['nama_pengguna_anggota'])) {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'message' => 'Anda harus login untuk mengakses fitur ini.'
    ]);
    exit;
}

// Sertakan file kelas API Nexus dan kredensial
require_once 'classes/connectAPI.php';
require_once 'classes/class.nexusggr.php';

// Inisialisasi objek API dengan kredensial dari connectAPI.php
$api = new API($user_agent, $signature);
$user_code = $_SESSION['nama_pengguna_anggota'];

// Tentukan tindakan berdasarkan parameter 'action'
$action = $_GET['action'] ?? null;
$response = ['status' => 'error', 'message' => 'Tindakan tidak valid.'];

try {
    switch ($action) {
        case 'game_list':
            $search_term = $_GET['search'] ?? '';
            // Panggil API untuk mendapatkan daftar provider
            $provider_response = $api->provider_list();

            if ($provider_response['status'] !== 1 || empty($provider_response['providers'])) {
                throw new Exception('Gagal mendapatkan daftar provider dari API.');
            }
            
            $all_games = [];
            foreach ($provider_response['providers'] as $provider) {
                // Untuk setiap provider, ambil daftar game-nya
                $game_response = $api->game_list($provider['provider_code']);

                if ($game_response['status'] === 1 && !empty($game_response['games'])) {
                    foreach ($game_response['games'] as $game) {
                        // Tambahkan provider_code dan image_url ke setiap objek game
                        $game['provider_code'] = $provider['provider_code'];
                        $game['image_url'] = $provider['image_url'];
                        $all_games[] = $game;
                    }
                }
            }

            // Lakukan filter pencarian di sini
            if (!empty($search_term)) {
                $filtered_games = array_filter($all_games, function($game) use ($search_term) {
                    return stripos($game['game_name'], $search_term) !== false;
                });
                $all_games = array_values($filtered_games);
            }

            $response = ['status' => 'success', 'data' => $all_games];
            break;
            
        case 'game_launch':
            $provider_code = $_GET['provider_code'] ?? null;
            $game_code = $_GET['game_code'] ?? null;

            if (!$provider_code || !$game_code) {
                throw new Exception('Parameter game tidak lengkap.');
            }

            // Panggil API untuk meluncurkan game
            $game_launch_response = $api->game_launch($user_code, $provider_code, $game_code);
            
            if ($game_launch_response['status'] === 1 && isset($game_launch_response['game_url'])) {
                $response = ['status' => 'success', 'game_url' => $game_launch_response['game_url']];
            } else {
                throw new Exception('Gagal meluncurkan game. Kode kesalahan: ' . ($game_launch_response['error'] ?? 'Tidak diketahui.'));
            }
            break;
            
        default:
            $response = ['status' => 'error', 'message' => 'Tindakan tidak valid.'];
            break;
    }
} catch (Exception $e) {
    $response = ['status' => 'error', 'message' => $e->getMessage()];
}

// Kirim respons dalam format JSON
header('Content-Type: application/json');
echo json_encode($response);