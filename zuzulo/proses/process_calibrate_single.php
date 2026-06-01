<?php
// admin/proses/process_calibrate_single.php
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

// Pastikan menerima id_anggota
$id_anggota = $_POST['id_anggota'] ?? null;
if (empty($id_anggota)) {
    $response['message'] = 'ID Anggota tidak ditemukan.';
    echo json_encode($response);
    exit();
}

$koneksi->begin_transaction();
try {
    // 1. Ambil id_sigma dari database lokal berdasarkan id_anggota
    $id_sigma = null;
    $username_anggota = null;
    $stmt_get_anggota = $koneksi->prepare("SELECT id_sigma, nama_pengguna_anggota FROM anggota WHERE id_anggota = ?");
    if ($stmt_get_anggota) {
        $stmt_get_anggota->bind_param("i", $id_anggota);
        $stmt_get_anggota->execute();
        $result_anggota = $stmt_get_anggota->get_result();
        if ($data_anggota = $result_anggota->fetch_assoc()) {
            $id_sigma = $data_anggota['id_sigma'];
            $username_anggota = $data_anggota['nama_pengguna_anggota'];
        }
        $stmt_get_anggota->close();
    } else {
        throw new Exception('Gagal mempersiapkan query anggota: ' . $koneksi->error);
    }

    if (empty($id_sigma)) {
        throw new Exception('ID Sigma untuk anggota ini tidak ditemukan. Tidak dapat mengkalibrasi.');
    }

    // 2. Panggil API GameXa getPlayerBalance
    $exaAPI = new GameXaAPI();
    $getBalanceResponse = $exaAPI->getPlayerBalance($id_sigma);

    if (!($getBalanceResponse['success'] ?? false) || !isset($getBalanceResponse['data']['balance'])) {
        throw new Exception('Gagal mendapatkan saldo dari GameXa API untuk ' . ($username_anggota ?? 'Unknown User') . ': ' . ($getBalanceResponse['message'] ?? 'Respon tidak valid.'));
    }

    $new_balance = floatval($getBalanceResponse['data']['balance']);

    // 3. Update saldo di database lokal
    $stmt_update_saldo = $koneksi->prepare("UPDATE anggota SET saldo_anggota = ? WHERE id_anggota = ?");
    if ($stmt_update_saldo) {
        $stmt_update_saldo->bind_param("di", $new_balance, $id_anggota);
        if (!$stmt_update_saldo->execute()) {
            throw new Exception('Gagal update saldo di database lokal: ' . $stmt_update_saldo->error);
        }
        $stmt_update_saldo->close();
    } else {
        throw new Exception('Gagal mempersiapkan update saldo: ' . $koneksi->error);
    }

    $koneksi->commit();
    $response['status'] = 'success';
    $response['message'] = "Saldo anggota {$username_anggota} berhasil dikalibrasi ke Rp " . number_format($new_balance, 0, ',', '.') . ".";
    $response['new_balance'] = $new_balance; // Kirim saldo baru kembali untuk update tampilan
    $response['formatted_new_balance'] = 'Rp.' . number_format($new_balance, 0, ',', '.'); // Format untuk tampilan
    $response['id_anggota'] = $id_anggota; // Kirim ID kembali untuk update baris spesifik

} catch (Exception $e) {
    $koneksi->rollback();
    $response['message'] = 'Kalibrasi saldo gagal: ' . $e->getMessage();
    error_log("Error in process_calibrate_single.php: " . $e->getMessage());
}

echo json_encode($response);
exit();
?>