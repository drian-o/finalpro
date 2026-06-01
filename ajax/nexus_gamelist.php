<?php
header('Content-Type: application/json');
require_once '../koneksi.php';

$provider_code = isset($_GET['provider_code']) ? mysqli_real_escape_string($koneksi, $_GET['provider_code']) : '';
$search_term = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 12;
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

$response = [
    'success' => false,
    'message' => 'Invalid request.',
    'gamesHtml' => '',
    'totalGamesOverall' => 0,
    'totalGamesLoaded' => 0,
    'hasMore' => false
];

if (empty($provider_code)) {
    echo json_encode(['success' => false, 'message' => 'Provider code is required.']);
    exit;
}

$search_condition = !empty($search_term) ? "AND game_name LIKE '%{$search_term}%'" : "";
$query_count = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM nexus_gamelist WHERE provider_code = '{$provider_code}' {$search_condition} AND game_status = 'open'");
$total_games = mysqli_fetch_assoc($query_count)['total'];

$query_games = mysqli_query($koneksi, "SELECT game_code, game_name, game_image_local, game_image_url_api FROM nexus_gamelist WHERE provider_code = '{$provider_code}' {$search_condition} AND game_status = 'open' ORDER BY game_name ASC LIMIT {$limit} OFFSET {$offset}");

$games_html = '';
if ($query_games && mysqli_num_rows($query_games) > 0) {
    while ($game = mysqli_fetch_assoc($query_games)) {
        $image_src = !empty($game['game_image_local']) ? htmlspecialchars($game['game_image_local']) : htmlspecialchars($game['game_image_url_api']);
        if (empty($image_src)) {
            $image_src = 'assets/img/default-game-no-image.jpg';
        }
        $games_html .= "
            <a href='#' class='game-grid-item play-game-trigger' 
               data-game-code='{$game['game_code']}' 
               data-provider='{$provider_code}' 
               data-game-type='slot' 
               data-server='nexus'>
                <figure class='game-grid-figure'>
                    <img alt='{$game['game_name']}' loading='lazy' src='{$image_src}'>
                </figure>
                <p class='game-grid-name'>{$game['game_name']}</p>
            </a>
        ";
    }
    $response['success'] = true;
    $response['message'] = 'Games loaded successfully.';
    $response['gamesHtml'] = $games_html;
    $response['totalGamesOverall'] = $total_games;
    $response['totalGamesLoaded'] = mysqli_num_rows($query_games);
    $response['hasMore'] = ($offset + $response['totalGamesLoaded']) < $total_games;
} else {
    $response['success'] = true;
    $response['message'] = 'Tidak ada game yang tersedia untuk provider ini.';
    $response['totalGamesOverall'] = $total_games;
}

echo json_encode($response);
mysqli_close($koneksi);
?>