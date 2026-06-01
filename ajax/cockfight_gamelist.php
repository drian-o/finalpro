<?php

header('Content-Type: application/json');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../koneksi.php';

if (!isset($koneksi) || !$koneksi instanceof mysqli) {
    echo json_encode(['success' => false, 'message' => 'Database connection error.']);
    exit();
}

$search_term = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 12;
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
$page_game_type = 'cockfight'; // Game type khusus untuk halaman ini

$response_data = [
    'success' => false,
    'message' => 'Error.',
    'gamesHtml' => '',
    'totalGamesOverall' => 0,
    'totalGamesLoaded' => 0,
    'hasMore' => false,
    'providerName' => 'Semua Game Sabung Ayam' // Nama tampilan untuk semua game sabung ayam
];

try {
    // Query untuk menghitung total game dari cockfight_gamelist
    $count_query = "SELECT COUNT(*) AS total_games
                    FROM cockfight_gamelist cfg
                    LEFT JOIN srg_gamelist srg ON cfg.game_code = srg.game_code AND cfg.provider_code = srg.provider_code AND cfg.game_source = 'srg'
                    WHERE cfg.game_type = '{$page_game_type}' AND cfg.game_source = 'srg'"; // Asumsi hanya GameXa/SRG
    
    if (!empty($search_term)) {
        $count_query .= " AND (cfg.game_name LIKE '%{$search_term}%' OR cfg.game_code LIKE '%{$search_term}%')";
    }
    $count_result = mysqli_query($koneksi, $count_query);
    $total_games_overall = 0;
    if ($count_result) {
        $count_row = mysqli_fetch_assoc($count_result);
        $total_games_overall = $count_row['total_games'];
    }
    $response_data['totalGamesOverall'] = $total_games_overall;

    // Mengambil game dengan limit dan offset dari cockfight_gamelist
    $query_games = "SELECT cfg.game_code, cfg.provider_code, cfg.game_source, cfg.game_name, cfg.game_type, cfg.custom_image_path,
                            srg.game_image_local AS srg_img_local, srg.game_image_url_api AS srg_img_api
                    FROM cockfight_gamelist cfg
                    LEFT JOIN srg_gamelist srg ON cfg.game_code = srg.game_code AND cfg.provider_code = srg.provider_code AND cfg.game_source = 'srg'
                    WHERE cfg.game_type = '{$page_game_type}' AND cfg.game_source = 'srg'"; // Asumsi hanya GameXa/SRG
    
    if (!empty($search_term)) {
        $query_games .= " AND (cfg.game_name LIKE '%{$search_term}%' OR cfg.game_code LIKE '%{$search_term}%')";
    }
    
    $query_games .= " ORDER BY cfg.game_name ASC LIMIT {$limit} OFFSET {$offset}"; // Urutkan berdasarkan nama game

    $result_games = mysqli_query($koneksi, $query_games);

    if (!$result_games) {
        throw new Exception("Failed to query games from database: " . mysqli_error($koneksi));
    }

    $games_html = '';
    $total_games_loaded_this_request = mysqli_num_rows($result_games);

    if ($total_games_loaded_this_request > 0) {
        while ($game = mysqli_fetch_assoc($result_games)) {
            $game_name = htmlspecialchars($game['game_name']);
            $game_code = htmlspecialchars($game['game_code']);
            $provider_code = htmlspecialchars($game['provider_code']); // Ambil provider_code dari cockfight_gamelist
            $game_type_from_db = htmlspecialchars($game['game_type']); // Ambil game_type dari cockfight_gamelist

            $gambar_src = '';
            if (!empty($game['custom_image_path']) && file_exists('../' . $game['custom_image_path'])) {
                $gambar_src = '../' . htmlspecialchars($game['custom_image_path']);
            } elseif ($game['game_source'] === 'srg' && !empty($game['srg_img_local']) && file_exists('../' . $game['srg_img_local'])) {
                $gambar_src = '../' . htmlspecialchars($game['srg_img_local']);
            } elseif (!empty($game['srg_img_api'])) {
                $gambar_src = htmlspecialchars($game['srg_img_api']);
            } else {
                $gambar_src = '../path/to/default/game-no-image.jpg';
            }

            $server_attribute_value = 'gamexa'; // Karena semua dari GameXa

            $games_html .= '
                <a href="#" class="game-grid-item play-game-trigger" 
                   data-game-code="'.$game_code.'" 
                   data-provider="'.$provider_code.'" 
                   data-game-type="'.$game_type_from_db.'"
                   data-server="'.$server_attribute_value.'"> <figure class="game-grid-figure">
                            <img alt="'.$game_name.'" loading="lazy" src="'.$gambar_src.'">
                        </figure>
                        <p class="game-grid-name">'.$game_name.'</p>
                    </a>
            ';
        }
        $response_data['success'] = true;
        $response_data['gamesHtml'] = $games_html;
        $response_data['totalGamesLoaded'] = $total_games_loaded_this_request;
        $response_data['hasMore'] = ($offset + $total_games_loaded_this_request) < $total_games_overall;
    } else {
        $response_data['success'] = true;
        $response_data['message'] = 'Tidak ada game sabung ayam yang tersedia atau tidak ditemukan dengan kata kunci tersebut.';
        if ($offset == 0) {
            $response_data['gamesHtml'] = '<p class="col-span-full text-center py-5 text-gray-400">Tidak ada game sabung ayam yang tersedia atau tidak ditemukan dengan kata kunci tersebut.</p>';
        }
    }

} catch (Exception $e) {
    $response_data['message'] = 'Error: ' . $e->getMessage();
    error_log("AJAX cockfight_gamelist error: " . $e->getMessage());
}

echo json_encode($response_data);
?>