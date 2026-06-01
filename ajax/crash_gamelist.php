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

$response_data = [
    'success' => false,
    'message' => 'Error.',
    'gamesHtml' => '',
    'totalGamesOverall' => 0,
    'totalGamesLoaded' => 0,
    'hasMore' => false,
    'providerName' => 'Semua Game'
];

try {
    // Kueri untuk menghitung total game.
    $count_query = "SELECT COUNT(*) AS total_games
                    FROM crash_gamelist crg
                    LEFT JOIN srg_gamelist srg ON crg.game_code = srg.game_code AND crg.provider_code = srg.provider_code
                    WHERE 1=1"; 
    
    if (!empty($search_term)) {
        $count_query .= " AND (crg.game_name LIKE '%{$search_term}%' OR crg.game_code LIKE '%{$search_term}%')";
    }
    $count_result = mysqli_query($koneksi, $count_query);
    $total_games_overall = 0;
    if ($count_result) {
        $count_row = mysqli_fetch_assoc($count_result);
        $total_games_overall = $count_row['total_games'];
    }
    $response_data['totalGamesOverall'] = $total_games_overall;

    // Kueri untuk mengambil data game dengan pengurutan baru.
    $query_games = "SELECT crg.game_code, crg.provider_code, crg.game_source, crg.game_name, crg.game_type, crg.custom_image_path, crg.display_order,
                            srg.game_image_local AS srg_img_local, srg.game_image_url_api AS srg_img_api
                    FROM crash_gamelist crg
                    LEFT JOIN srg_gamelist srg ON crg.game_code = srg.game_code AND crg.provider_code = srg.provider_code
                    WHERE 1=1"; 
    
    if (!empty($search_term)) {
        $query_games .= " AND (crg.game_name LIKE '%{$search_term}%' OR crg.game_code LIKE '%{$search_term}%')";
    }
    
    // Klausa ORDER BY yang diperbarui:
    // 1. Menggunakan CASE untuk mengelompokkan nilai non-nol terlebih dahulu (0) dan nol di akhir (1).
    // 2. Kemudian, mengurutkan nilai non-nol secara ascending (1, 2, 3, dst.).
    // 3. Terakhir, mengurutkan berdasarkan nama game untuk pengurutan sekunder.
    $query_games .= " ORDER BY CASE WHEN crg.display_order = 0 THEN 1 ELSE 0 END, crg.display_order ASC, crg.game_name ASC LIMIT {$limit} OFFSET {$offset}";

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
            $provider_code = htmlspecialchars($game['provider_code']);
            $game_type_from_db = htmlspecialchars($game['game_type']);
            $display_order = htmlspecialchars($game['display_order']);

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

            $server_attribute_value = 'gamexa'; 

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
        $response_data['message'] = 'Tidak ada game yang tersedia atau tidak ditemukan dengan kata kunci tersebut.';
        if ($offset == 0) {
            $response_data['gamesHtml'] = '<p class="col-span-full text-center py-5 text-gray-400">Tidak ada game yang tersedia atau tidak ditemukan dengan kata kunci tersebut.</p>';
        }
    }

} catch (Exception $e) {
    $response_data['message'] = 'Error: ' . $e->getMessage();
    error_log("AJAX crash_gamelist error: " . $e->getMessage());
}

echo json_encode($response_data);
?>