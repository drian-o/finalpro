<?php
session_start();
header('Content-Type: application/json');

// Cek apakah user sudah login
if (!isset($_SESSION['id_anggota'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Anda harus login untuk mencari game.']);
    exit;
}

// Sertakan file koneksi database
require_once '../koneksi.php';

$search_term = '%' . ($_GET['search'] ?? '') . '%';
$games = [];

if (!$koneksi) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Koneksi database gagal.']);
    exit;
}

try {
    // Query yang sudah diperbaiki untuk UNION ALL
    $query = "
        (SELECT
            provider_code,
            game_code,
            game_name,
            game_image_local,
            game_image_url_api,
            game_type,
            game_source,
            urutan
        FROM nexus_gamelist
        WHERE game_name LIKE ? AND game_status = 'open')
        
        UNION ALL
        
        (SELECT
            provider_code,
            game_code,
            game_name,
            game_image_local,
            game_image_url_api,
            game_type,
            game_source,
            urutan
        FROM srg_gamelist
        WHERE game_name LIKE ? AND game_type != 'slot' AND game_status = 'active')
        
        ORDER BY urutan DESC, game_name ASC
        LIMIT 20
    ";

    if ($stmt = mysqli_prepare($koneksi, $query)) {
        mysqli_stmt_bind_param($stmt, "ss", $search_term, $search_term);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $games[] = $row;
        }
        
        mysqli_stmt_close($stmt);
        
        echo json_encode(['status' => 'success', 'data' => $games]);
    } else {
        throw new Exception("Gagal menyiapkan query database: " . mysqli_error($koneksi));
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$koneksi->close();
?>