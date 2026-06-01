<?php
// ajax_remove_featured.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include_once '../koneksi.php';

header('Content-Type: application/json');

if (!isset($_SESSION['kode_admin'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Akses ditolak. Sesi admin tidak ditemukan.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['game_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Request tidak valid.']);
    exit();
}

$game_id = intval($_POST['game_id']);
$provider_code = ''; // Untuk referensi di frontend

if ($game_id > 0) {
    // Ambil provider code sebelum update
    $stmt_get_provider = $koneksi->prepare("SELECT provider FROM gamelist_slot WHERE id = ?");
    if ($stmt_get_provider) {
        $stmt_get_provider->bind_param("i", $game_id);
        $stmt_get_provider->execute();
        $result_provider = $stmt_get_provider->get_result();
        if ($row_provider = $result_provider->fetch_assoc()) {
            $provider_code = $row_provider['provider'];
        }
        $stmt_get_provider->close();
    }

    $stmt_update = $koneksi->prepare("UPDATE gamelist_slot SET is_featured = 0 WHERE id = ?");
    if ($stmt_update) {
        $stmt_update->bind_param("i", $game_id);
        if ($stmt_update->execute()) {
            if ($stmt_update->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'Game berhasil dihapus dari unggulan!', 'provider_code' => $provider_code]);
            } else {
                // Bisa jadi game sudah tidak featured atau ID salah
                echo json_encode(['success' => false, 'message' => 'Game tidak ditemukan atau status tidak berubah.', 'provider_code' => $provider_code]);
            }
        } else {
            error_log("AJAX Remove Featured Execute Error: " . $stmt_update->error);
            echo json_encode(['success' => false, 'message' => 'Gagal mengubah status game.']);
        }
        $stmt_update->close();
    } else {
        error_log("AJAX Remove Featured Prepare Error: " . $koneksi->error);
        echo json_encode(['success' => false, 'message' => 'Gagal mempersiapkan statement.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'ID Game tidak valid.']);
}
exit();
?>