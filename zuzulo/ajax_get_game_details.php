<?php
// ajax_get_game_details.php
header('Content-Type: application/json');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include_once '../koneksi.php'; // Sesuaikan path

$response = ['status' => 'error', 'message' => 'Permintaan tidak valid.'];

if (!isset($_SESSION['kode_admin'])) {
    $response['message'] = 'Akses ditolak. Sesi tidak valid.';
    echo json_encode($response);
    exit();
}

$db_connected = false;
$db_connection_var = null;
if (isset($koneksi) && $koneksi instanceof mysqli) {
    $db_connected = true;
    $db_connection_var = $koneksi;
} // ... (fallback koneksi lain jika ada) ...

if (!$db_connected) {
    $response['message'] = 'Kesalahan koneksi database.';
    echo json_encode($response);
    exit();
}

if (isset($_GET['game_id']) && is_numeric($_GET['game_id'])) {
    $game_id = intval($_GET['game_id']);
    $sql = "SELECT id, game_code, game_name, banner, status, provider, sort, lang, frbavailable, provideragent 
            FROM gamelist_slot WHERE id = ?";
    $stmt = $db_connection_var->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $game_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($game = $result->fetch_assoc()) {
            $response['status'] = 'success';
            $response['game'] = $game;
            $response['message'] = 'Data game berhasil diambil.';
        } else {
            $response['message'] = 'Game tidak ditemukan.';
        }
        $stmt->close();
    } else {
        $response['message'] = 'Gagal menyiapkan query: ' . $db_connection_var->error;
    }
} else {
    $response['message'] = 'ID Game tidak valid.';
}

if ($db_connected && isset($db_connection_var)) {
    $db_connection_var->close();
}
echo json_encode($response);
exit();
?>