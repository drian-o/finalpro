<?php
// ajax_get_games.php
// Pastikan session sudah dimulai (idealnya di koneksi.php)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once '../koneksi.php'; // Sesuaikan path jika perlu

header('Content-Type: application/json'); // Set header output ke JSON

// Keamanan: Pastikan admin yang login dan ini adalah request AJAX
if (!isset($_SESSION['kode_admin'])) {
    http_response_code(403); // Forbidden
    echo json_encode(['error' => 'Akses ditolak. Sesi admin tidak ditemukan.']);
    exit();
}

// Anda bisa menambahkan pemeriksaan apakah ini benar-benar request AJAX jika diinginkan
// if(!(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')) {
// http_response_code(400);
// echo json_encode(['error' => 'Invalid request method.']);
// exit();
// }


if (!isset($_GET['provider']) || empty($_GET['provider'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Parameter provider tidak ditemukan atau kosong.']);
    exit();
}

$selected_provider = $_GET['provider'];
$games = [];

// Gunakan prepared statement untuk keamanan
$stmt_games = $koneksi->prepare("SELECT id, game_name, game_code FROM gamelist_slot WHERE provider = ? AND is_featured = 0 ORDER BY game_name ASC");

if ($stmt_games) {
    $stmt_games->bind_param("s", $selected_provider);
    if ($stmt_games->execute()) {
        $result_games = $stmt_games->get_result();
        while ($row_game = $result_games->fetch_assoc()) {
            $games[] = $row_game;
        }
    } else {
        // Jangan kirim error SQL detail ke client, cukup log di server
        error_log("AJAX Get Games Execute Error: " . $stmt_games->error);
        http_response_code(500); // Internal Server Error
        echo json_encode(['error' => 'Gagal mengambil data game.']);
        exit();
    }
    $stmt_games->close();
} else {
    error_log("AJAX Get Games Prepare Error: " . $koneksi->error);
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Gagal mempersiapkan query game.']);
    exit();
}

echo json_encode($games);
exit();
?>