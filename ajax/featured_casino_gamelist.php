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
$page_game_type = 'casino';

$response_data = [
    'success' => false,
    'message' => 'Error.',
    'gamesHtml' => '',
    'totalGamesOverall' => 0,
    'totalGamesLoaded' => 0,
    'hasMore' => false,
    'providerName' => 'Rekomendasi GameXa'
];

try {
    // Pastikan tabel casino_gamelist sudah ada di database Anda
    $count_query = "SELECT COUNT(*) AS total_games
                    FROM casino_gamelist cg
                    LEFT JOIN srg_gamelist srg ON cg.game_code = srg.game_code AND cg.provider_code = srg.provider_code AND cg.game_source = 'srg'
                    WHERE cg.is_featured = TRUE AND cg.game_type = '{$page_game_type}' AND cg.game_source = 'srg'";
    
    if (!empty($search_term)) {
        $count_query .= " AND (cg.game_name LIKE '%{$search_term}%' OR cg.game_code LIKE '%{$search_term}%')";
    }
    $count_result = mysqli_query($koneksi, $count_query);
    $total_games_overall = 0;
    if ($count_result) {
        $count_row = mysqli_fetch_assoc($count_result);
        $total_games_overall = $count_row['total_games'];
    }
    $response_data['totalGamesOverall'] = $total_games_overall;

    $query_featured_games = "SELECT cg.game_code, cg.provider_code, cg.game_source, cg.game_name, cg.game_type, cg.custom_image_path,
                                    srg.game_image_local AS srg_img_local, srg.game_image_url_api AS srg_img_api
                            FROM casino_gamelist cg
                            LEFT JOIN srg_gamelist srg ON cg.game_code = srg.game_code AND cg.provider_code = srg.provider_code AND cg.game_source = 'srg'
                            WHERE cg.is_featured = TRUE AND cg.game_type = '{$page_game_type}' AND cg.game_source = 'srg'";
    
    if (!empty($search_term)) {
        $query_featured_games .= " AND (cg.game_name LIKE '%{$search_term}%' OR cg.game_code LIKE '%{$search_term}%')";
    }
    
    $query_featured_games .= " ORDER BY cg.display_order ASC, cg.game_name ASC LIMIT {$limit} OFFSET {$offset}";

    $result_featured_games = mysqli_query($koneksi, $query_featured_games);

    if (!$result_featured_games) {
        throw new Exception("Failed to query featured games from database: " . mysqli_error($koneksi));
    }

    $games_html = '';
    $total_games_loaded_this_request = mysqli_num_rows($result_featured_games);

    if ($total_games_loaded_this_request > 0) {
        while ($game = mysqli_fetch_assoc($result_featured_games)) {
            $gambar_src = '';
            if (!empty($game['custom_image_path']) && file_exists('../' . $game['custom_image_path'])) {
                $gambar_src = '../' . htmlspecialchars($game['custom_image_path']);
            } elseif ($game['game_source'] === 'srg' && !empty($game['srg_img_local']) && file_exists('../' . $game['srg_img_local'])) {
                $gambar_src = '../' . htmlspecialchars($game['srg_img_local']);
            } elseif ($game['game_source'] === 'srg' && !empty($game['srg_img_api'])) {
                $gambar_src = htmlspecialchars($game['srg_img_api']);
            } else {
                $gambar_src = '../path/to/default/game-no-image.jpg';
            }

            $data_server_value = 'gamexa';

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
        $response_data['totalGamesLoaded'] = $total_games_loaded_this_request;
        $response_data['hasMore'] = ($offset + $total_games_loaded_this_request) < $total_games_overall;
    } else {
        $response_data['success'] = true;
        $response_data['message'] = 'Tidak ada game pilihan yang tersedia.';
        if ($offset == 0) {
            $response_data['gamesHtml'] = '<p class="col-span-full text-center py-5 text-gray-400">Tidak ada game pilihan yang tersedia.</p>';
        }
    }

} catch (Exception $e) {
    $response_data['message'] = 'Error: ' . $e->getMessage();
    error_log("AJAX featured_casino_gamelist error: " . $e->getMessage());
}

echo json_encode($response_data);
?>