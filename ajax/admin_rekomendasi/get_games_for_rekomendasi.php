<?php
// ajax/admin_rekomendasi/get_games_for_rekomendasi.php

header('Content-Type: application/json');

include_once '../../koneksi.php'; // Path relatif dari ajax/admin_rekomendasi/

$response = ['success' => false, 'games' => [], 'message' => ''];

$game_type = $_GET['game_type'] ?? '';
$game_source = $_GET['game_source'] ?? '';
$provider_code = $_GET['provider_code'] ?? '';

if (empty($game_type) || empty($game_source) || empty($provider_code)) {
    $response['message'] = 'Tipe game, sumber, atau kode provider tidak valid.';
    echo json_encode($response);
    exit();
}

try {
    $games = [];
    $query = "";
    $param_type = "";

    // Tentukan tabel gamelist dan tipe yang relevan
    if ($game_source === 'srg') {
        $query = "SELECT game_code, game_name, game_type FROM srg_gamelist WHERE provider_code = ? AND game_type = ? AND game_status = 'active' ORDER BY game_name ASC";
        $param_type = $game_type;
    } elseif ($game_source === 'telo') {
        $query = "SELECT game_code, game_name, game_type FROM telo_gamelist WHERE provider_code = ? AND game_type = ? AND game_status = 'active' ORDER BY game_name ASC";
        $param_type = strtolower($game_type); // Telo.is game_type biasanya lowercase
    } else {
        throw new Exception("Sumber game tidak dikenal.");
    }

    $stmt = $koneksi->prepare($query);
    if (!$stmt) {
        throw new Exception("Gagal menyiapkan query game: " . $koneksi->error);
    }
    $stmt->bind_param("ss", $provider_code, $param_type);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $games[] = $row;
    }
    $stmt->close();

    $response['success'] = true;
    $response['games'] = $games;

} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
    error_log("AJAX get_games_for_rekomendasi.php Error: " . $e->getMessage());
} finally {
    if (isset($koneksi) && $koneksi instanceof mysqli) { $koneksi->close(); }
}

echo json_encode($response);
?>