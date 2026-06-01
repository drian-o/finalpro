<?php
// api_check_user_pending_deposit.php
header('Content-Type: application/json');
session_start();

include_once __DIR__ . '/koneksi.php'; // Pastikan path ini benar

$response = ['has_pending' => false];

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['id_anggota'])) {
    // Pengguna tidak login, tidak mungkin ada pending deposit untuk mereka
    echo json_encode($response);
    exit;
}

$id_anggota = $_SESSION['id_anggota'];

// Periksa apakah ada deposit dengan status 'diproses' untuk id_anggota ini
$q_check_pending = "SELECT COUNT(*) FROM deposit WHERE id_anggota_deposit = ? AND status_deposit = 'diproses'";
$stmt_check_pending = $koneksi->prepare($q_check_pending);

if ($stmt_check_pending) {
    $stmt_check_pending->bind_param("i", $id_anggota);
    $stmt_check_pending->execute();
    $stmt_check_pending->bind_result($count_pending);
    $stmt_check_pending->fetch();
    $stmt_check_pending->close();

    if ($count_pending > 0) {
        $response['has_pending'] = true;
    }
} else {
    // Error pada prepared statement, log ini di server
    error_log("DB Error in api_check_user_pending_deposit.php: " . $koneksi->error);
    // Kita tetap kembalikan false, atau bisa tambahkan error message ke response jika perlu
}

if (isset($koneksi) && $koneksi instanceof mysqli && $koneksi->thread_id) { $koneksi->close(); }

echo json_encode($response);
exit;
?>