<?php
// admin/proses/process_calibrate_all.php
session_start();
header('Content-Type: application/json');

include_once '../../koneksi.php'; // Sesuaikan path jika perlu
include_once '../../classes/class.exa.php'; // Sesuaikan path jika perlu

$response = ['status' => 'error', 'message' => 'Terjadi kesalahan tidak diketahui.'];

// Pastikan admin login
if (!isset($_SESSION['kode_admin'])) {
    $response['message'] = 'Sesi admin tidak valid. Harap login kembali.';
    echo json_encode($response);
    exit();
}

$exaAPI = new GameXaAPI();
$koneksi->begin_transaction();

try {
    // 1. Ambil semua pemain dari GameXa API
    $getPlayersResponse = $exaAPI->getPlayers(1, 50000); // Mengambil jumlah pemain yang besar

    if (!($getPlayersResponse['success'] ?? false) || !isset($getPlayersResponse['data']['players'])) {
        throw new Exception('Gagal mendapatkan daftar pemain dari GameXa API: ' . ($getPlayersResponse['message'] ?? 'Respon tidak valid.'));
    }

    $playersFromAPI = $getPlayersResponse['data']['players'];
    $updatedCount = 0;
    $failedCount = 0;

    // 2. Loop setiap pemain dari API dan update saldo di database lokal
    foreach ($playersFromAPI as $playerAPI) {
        $username_api = $playerAPI['username'];
        $balance_api = floatval($playerAPI['balance']);
        $id_sigma_api = $playerAPI['id']; // ID sigma dari API adalah 'id' di respons getPlayers

        // Perbarui saldo di tabel anggota berdasarkan id_sigma
        // Gunakan prepared statement untuk keamanan
        $stmt_update_saldo = $koneksi->prepare("UPDATE anggota SET saldo_anggota = ? WHERE id_sigma = ?");
        if ($stmt_update_saldo) {
            $stmt_update_saldo->bind_param("di", $balance_api, $id_sigma_api);
            if ($stmt_update_saldo->execute()) {
                if ($stmt_update_saldo->affected_rows > 0) {
                    $updatedCount++;
                }
            } else {
                $failedCount++;
                error_log("Failed to update balance for username {$username_api} (ID Sigma: {$id_sigma_api}) - " . $stmt_update_saldo->error);
            }
            $stmt_update_saldo->close();
        } else {
            $failedCount++;
            error_log("Failed to prepare update statement for username {$username_api} - " . $koneksi->error);
        }
    }

    $koneksi->commit();
    $response['status'] = 'success';
    $response['message'] = "Kalibrasi semua saldo selesai. {$updatedCount} anggota diperbarui, {$failedCount} gagal.";
    $response['updated_count'] = $updatedCount;
    $response['failed_count'] = $failedCount;

} catch (Exception $e) {
    $koneksi->rollback();
    $response['message'] = 'Proses kalibrasi gagal: ' . $e->getMessage();
    error_log("Error in process_calibrate_all.php: " . $e->getMessage());
}

echo json_encode($response);
exit();
?>