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

$provider_code_from_req = isset($_GET['provider_code']) ? mysqli_real_escape_string($koneksi, $_GET['provider_code']) : '';
$game_type = isset($_GET['game_type']) ? mysqli_real_escape_string($koneksi, $_GET['game_type']) : 'slot';
$search_term = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 12;
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

$response_data = [
    'success' => false,
    'message' => 'Invalid request.',
    'gamesHtml' => '',
    'totalGamesOverall' => 0,
    'totalGamesLoaded' => 0,
    'hasMore' => false,
    'providerName' => ''
];

if (empty($provider_code_from_req)) {
    if ($provider_code_from_req === 'ALL') {
        $response_data['providerName'] = 'Semua Slot';
    } else {
        $response_data['message'] = 'Provider code is missing.';
        echo json_encode($response_data);
        exit();
    }
} else {
    $provider_name_query = mysqli_query($koneksi, "SELECT provider_name FROM nexus_provider WHERE provider_code = '{$provider_code_from_req}' LIMIT 1");
    if ($provider_name_query && mysqli_num_rows($provider_name_query) > 0) {
        $provider_row = mysqli_fetch_assoc($provider_name_query);
        $response_data['providerName'] = $provider_row['provider_name'];
    } else {
        $response_data['message'] = 'Provider not found in database.';
        echo json_encode($response_data);
        exit();
    }
}

try {
    $count_query = "SELECT COUNT(*) AS total_games FROM nexus_gamelist WHERE game_type = '{$game_type}' AND game_status = 'open'";
    if ($provider_code_from_req !== 'ALL') {
        $count_query .= " AND provider_code = '{$provider_code_from_req}'";
    }
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

    $query_games = "SELECT game_code, game_name, game_image_local, game_image_url_api, game_status, provider_code, urutan 
                    FROM nexus_gamelist 
                    WHERE game_type = '{$game_type}'";
    if ($provider_code_from_req !== 'ALL') {
        $query_games .= " AND provider_code = '{$provider_code_from_req}'";
    }
    
    if (!empty($search_term)) {
        $query_games .= " AND (game_name LIKE '%{$search_term}%' OR game_code LIKE '%{$search_term}%')";
    }
    
    $query_games .= " AND game_status = 'open'";
    $query_games .= " ORDER BY (CASE WHEN urutan > 0 THEN 0 ELSE 1 END) ASC, urutan ASC, game_name ASC LIMIT {$limit} OFFSET {$offset}";

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
            $game_image_local = htmlspecialchars($game['game_image_local']);
            $game_image_url_api = htmlspecialchars($game['game_image_url_api']);

            $gambar_src = '';
            if (!empty($game_image_local) && file_exists('../' . $game_image_local)) {
                $gambar_src = '../' . $game_image_local;
            } elseif (!empty($game_image_url_api)) {
                $gambar_src = $game_image_url_api;
            } else {
                $gambar_src = '../upload/no-image.png';
            }

            $server_attribute_value = 'nexus'; 

            $games_html .= '
                <a href="#" class="game-grid-item play-game-trigger" 
                   data-game-code="'.$game_code.'" 
                   data-provider="'.$game['provider_code'].'" 
                   data-game-type="'.$game_type.'"
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
        $response_data['message'] = 'Tidak ada game yang tersedia untuk provider ini atau tidak ditemukan dengan kata kunci tersebut.';
        if ($offset == 0) {
            $response_data['gamesHtml'] = '<p class="col-span-full text-center py-5 text-gray-400">Tidak ada game yang tersedia untuk provider ini atau tidak ditemukan dengan kata kunci tersebut.</p>';
        }
    }

} catch (Exception $e) {
    $response_data['message'] = 'Error: ' . $e->getMessage();
    error_log("AJAX slot_gamelist error: " . $e->getMessage());
}

echo json_encode($response_data);
?>