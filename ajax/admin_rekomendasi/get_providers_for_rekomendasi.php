<?php
// ajax/admin_rekomendasi/get_providers_for_rekomendasi.php

header('Content-Type: application/json');

include_once '../../koneksi.php'; // Path relatif dari ajax/admin_rekomendasi/

$response = ['success' => false, 'providers' => [], 'message' => ''];

$game_type = $_GET['game_type'] ?? '';
$game_source = $_GET['game_source'] ?? '';

if (empty($game_type) || empty($game_source)) {
    $response['message'] = 'Tipe game atau sumber tidak valid.';
    echo json_encode($response);
    exit();
}

try {
    $providers = [];
    $query = "";
    $param_type = "";

    // Tentukan tabel provider dan tipe yang relevan
    if ($game_source === 'srg') {
        $query = "SELECT provider_code, provider_name, provider_type FROM srg_provider WHERE provider_status = 'active' AND provider_type = ? ORDER BY provider_name ASC";
        $param_type = $game_type; // SRG menggunakan tipe game langsung (SLOT, LOTTERY, LIVE_CASINO, SPORT_BOOK, COCK_FIGHTING, OTHER, VIRTUAL_SPORT)
    } elseif ($game_source === 'telo') {
        $query = "SELECT provider_code, provider_name, provider_type FROM telo_provider WHERE provider_status = 'active' AND provider_type = ? ORDER BY provider_name ASC";
        // Telo.is menggunakan tipe lowercase 'slot', 'casino'
        // Untuk egames, kita harus berhati-hati, asumsikan telo.is menggunakan tipe spesifik juga jika ada.
        $param_type = strtolower($game_type); 
        // Jika game_type dari request adalah salah satu dari e-games, dan telo memiliki tipe generik 'egames'
        // Anda perlu menyesuaikan ini. Untuk saat ini, kita cocokkan persis.
    } else {
        throw new Exception("Sumber provider tidak dikenal.");
    }

    $stmt = $koneksi->prepare($query);
    if (!$stmt) {
        throw new Exception("Gagal menyiapkan query provider: " . $koneksi->error);
    }
    $stmt->bind_param("s", $param_type);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $providers[] = $row;
    }
    $stmt->close();

    $response['success'] = true;
    $response['providers'] = $providers;

} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
    error_log("AJAX get_providers_for_rekomendasi.php Error: " . $e->getMessage());
} finally {
    if (isset($koneksi) && $koneksi instanceof mysqli) { $koneksi->close(); }
}

echo json_encode($response);
?>