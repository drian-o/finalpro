<?php
// ajax/featured_fishing_gamelist.php

header('Content-Type: application/json');

// Memulai session, jika belum dimulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Sertakan koneksi database
require_once '../koneksi.php';

// Pastikan koneksi database valid
if (!isset($koneksi) || !$koneksi instanceof mysqli) {
    echo json_encode(['success' => false, 'message' => 'Database connection error.']);
    exit();
}

// Ambil parameter pencarian (opsional)
$search_term = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
$page_game_type = 'fishing'; // Tipe game yang relevan untuk fishing_gamelist

$response_data = [
    'success' => false,
    'message' => 'Error.',
    'gamesHtml' => '',
    'totalGamesOverall' => 0,
    'providerName' => 'Pilihan Redaksi' // Nama khusus untuk "provider" ini
];

try {
    // Ambil Daftar Game Pilihan dari fishing_gamelist
    // JOIN dengan srg_gamelist untuk mendapatkan path gambar asli
    $query_featured_games = "SELECT lg.game_code, lg.provider_code, lg.game_source, lg.game_name, lg.game_type, lg.custom_image_path,
                                    srg.game_image_local AS srg_img_local, srg.game_image_url_api AS srg_img_api
                            FROM fishing_gamelist lg
                            LEFT JOIN srg_gamelist srg ON lg.game_code = srg.game_code AND lg.provider_code = srg.provider_code AND lg.game_source = 'srg'
                            WHERE lg.is_featured = TRUE AND lg.game_type = '{$page_game_type}'";
    
    // Tambahkan filter pencarian jika ada
    if (!empty($search_term)) {
        $query_featured_games .= " AND (lg.game_name LIKE '%{$search_term}%' OR lg.game_code LIKE '%{$search_term}%')";
    }
    
    $query_featured_games .= " ORDER BY lg.display_order ASC, lg.game_name ASC";

    $result_featured_games = mysqli_query($koneksi, $query_featured_games);

    if (!$result_featured_games) {
        throw new Exception("Failed to query featured games from database: " . mysqli_error($koneksi));
    }

    $games_html = '';
    $total_filtered_games = mysqli_num_rows($result_featured_games);

    if ($total_filtered_games > 0) {
        while ($game = mysqli_fetch_assoc($result_featured_games)) {
            // Tentukan gambar yang akan ditampilkan
            $gambar_src = '';
            if (!empty($game['custom_image_path']) && file_exists('../' . $game['custom_image_path'])) {
                $gambar_src = '../' . htmlspecialchars($game['custom_image_path']);
            } elseif ($game['game_source'] === 'srg' && !empty($game['srg_img_local']) && file_exists('../' . $game['srg_img_local'])) {
                $gambar_src = '../' . htmlspecialchars($game['srg_img_local']);
            } elseif ($game['game_source'] === 'srg' && !empty($game['srg_img_api'])) {
                $gambar_src = htmlspecialchars($game['srg_img_api']);
            } else {
                $gambar_src = '../path/to/default/game-no-image.jpg'; // Gambar default jika tidak ada
            }

            // Tentukan nilai 'data-server' berdasarkan game_source
            $data_server_value = '';
            if ($game['game_source'] === 'srg') {
                $data_server_value = 'server2'; // Jika sumbernya 'srg', gunakan 'server2'
            } else {
                $data_server_value = 'local'; // Atau nilai default jika sumbernya bukan 'srg'
            }

            // Semua game di fishing_gamelist diasumsikan 'active' jika ditampilkan
            $games_html .= '
                <a href="#" class="game-grid-item play-game-trigger" 
                   data-game-code="' . htmlspecialchars($game['game_code']) . '" 
                   data-provider="' . htmlspecialchars($game['provider_code']) . '" 
                   data-game-type="' . htmlspecialchars($game['game_type']) . '"
                   data-server="' . htmlspecialchars($data_server_value) . '"> <figure class="game-grid-figure">
                        <img alt="' . htmlspecialchars($game['game_name']) . '" loading="lazy" src="' . $gambar_src . '">
                    </figure>
                    <p class="game-grid-name">' . htmlspecialchars($game['game_name']) . '</p>
                </a>
            ';
        }
        $response_data['success'] = true;
        $response_data['gamesHtml'] = $games_html;
        $response_data['totalGamesOverall'] = $total_filtered_games;
    } else {
        $response_data['success'] = true;
        $response_data['message'] = 'Tidak ada game pilihan yang tersedia.';
        $response_data['gamesHtml'] = '<p class="col-span-full text-center py-5 text-gray-400">Tidak ada game pilihan yang tersedia.</p>';
    }

} catch (Exception $e) {
    $response_data['message'] = 'Error: ' . $e->getMessage();
    error_log("AJAX featured_fishing_gamelist error: " . $e->getMessage());
}

echo json_encode($response_data);
?>