<?php
// get_games.php
// Skrip ini mengambil data game dari database berdasarkan kata kunci pencarian.
header('Content-Type: application/json');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'koneksi.php'; // Pastikan path ini benar

$response = ['success' => false, 'games' => []];

// Pastikan koneksi database tersedia
if (!isset($koneksi) || !$koneksi instanceof mysqli) {
    $response['message'] = 'Database connection error.';
    echo json_encode($response);
    exit();
}

$search_term = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';

try {
    $query = "SELECT game_code, game_name, game_image_local, game_image_url_api, provider_code, game_type 
              FROM srg_gamelist 
              WHERE game_status = 'active'";
    
    if (!empty($search_term)) {
        $query .= " AND game_name LIKE '%{$search_term}%'";
    }

    $query .= " ORDER BY urutan DESC, game_name ASC";

    $result = mysqli_query($koneksi, $query);

    if (!$result) {
        throw new Exception("Failed to query games from database: " . mysqli_error($koneksi));
    }

    $games = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $games[] = $row;
    }

    $response['success'] = true;
    $response['games'] = $games;

} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
    error_log("AJAX get_games error: " . $e->getMessage());
}

echo json_encode($response);
?>