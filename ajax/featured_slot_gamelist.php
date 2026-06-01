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
$page_game_type = 'slot';

$response_data = [
    'success' => false,
    'message' => 'Error.',
    'gamesHtml' => '',
    'totalGamesOverall' => 0,
    'totalGamesLoaded' => 0,
    'hasMore' => false,
    'providerName' => 'Rekomendasi'
];

try {
    $count_query = "SELECT COUNT(*) AS total_games
                    FROM slot_gamelist
                    WHERE is_featured = 1 AND game_type = '{$page_game_type}'";
    
    if (!empty($search_term)) {
        $count_query .= " AND (game_name LIKE '%{$search_term}%' OR game_code LIKE '%{$search_term}%')";
    }
    $count_result = mysqli_query($koneksi, $count_query);
    $total_games_overall = 0;
    if ($count_result) {
        $count_row = mysqli_fetch_assoc($count_result);
        $total_games_overall = $count_row['total_games'];
    }
    $response_data['totalGamesOverall'] = $total_games_overall;

    $query_featured_games = "SELECT sg.game_code, sg.provider_code, sg.game_source, sg.game_name, sg.game_type, sg.custom_image_path, sg.display_order,
                                    ngl.game_image_local AS ngl_img_local, ngl.game_image_url_api AS ngl_img_api
                            FROM slot_gamelist sg
                            LEFT JOIN nexus_gamelist ngl ON sg.game_code = ngl.game_code AND sg.provider_code = ngl.provider_code
                            WHERE sg.is_featured = 1 AND sg.game_type = '{$page_game_type}'";
    
    if (!empty($search_term)) {
        $query_featured_games .= " AND (sg.game_name LIKE '%{$search_term}%' OR sg.game_code LIKE '%{$search_term}%')";
    }
    
    $query_featured_games .= " ORDER BY 
                                CASE 
                                    WHEN sg.display_order = 0 THEN 2 
                                    ELSE 1 
                                END ASC,
                                sg.display_order ASC, 
                                sg.game_name ASC 
                                LIMIT {$limit} OFFSET {$offset}";

    $result_featured_games = mysqli_query($koneksi, $query_featured_games);

    if (!$result_featured_games) {
        throw new Exception("Failed to query featured games from database: " . mysqli_error($koneksi));
    }

    $games_html = '';
    $total_games_loaded_this_request = mysqli_num_rows($result_featured_games);

    if ($total_games_loaded_this_request > 0) {
        while ($game = mysqli_fetch_assoc($result_featured_games)) {
            $gambar_src = '';
            if (!empty($game['custom_image_path'])) {
                $gambar_src = htmlspecialchars($game['custom_image_path']);
            } elseif (!empty($game['ngl_img_local']) && file_exists('../' . $game['ngl_img_local'])) {
                $gambar_src = '../' . htmlspecialchars($game['ngl_img_local']);
            } elseif (!empty($game['ngl_img_api'])) {
                $gambar_src = htmlspecialchars($game['ngl_img_api']);
            } else {
                $gambar_src = '../upload/no-image.png';
            }

            $data_server_value = 'nexus';

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
    error_log("AJAX featured_slot_gamelist error: " . $e->getMessage());
}

echo json_encode($response_data);
?>