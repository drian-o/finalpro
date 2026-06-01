<?php
// process_claim_referral.php
session_start();
header('Content-Type: application/json');

include_once 'koneksi.php'; // Pastikan koneksi DB tersedia
include_once 'classes/class.exa.php'; // Pastikan GameXaAPI tersedia

$response = ['status' => 'error', 'message' => 'Terjadi kesalahan tidak diketahui.'];

// Pastikan pengguna sudah login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['id_anggota']) || !isset($_SESSION['nama_pengguna_anggota'])) {
    $response['message'] = 'Anda harus login untuk melakukan klaim.';
    echo json_encode($response);
    exit();
}

$id_anggota_login = $_SESSION['id_anggota'];
$username_login = $_SESSION['nama_pengguna_anggota'];

// Ambil ID Sigma untuk pemain yang login (yang akan menerima deposit)
$id_sigma_anggota = null;
$stmt_get_sigma = $koneksi->prepare("SELECT id_sigma FROM anggota WHERE id_anggota = ?");
if ($stmt_get_sigma) {
    $stmt_get_sigma->bind_param("i", $id_anggota_login);
    $stmt_get_sigma->execute();
    $result_sigma = $stmt_get_sigma->get_result();
    if ($data_sigma = $result_sigma->fetch_assoc()) {
        $id_sigma_anggota = $data_sigma['id_sigma'];
    }
    $stmt_get_sigma->close();
}

if (empty($id_sigma_anggota)) {
    $response['message'] = 'ID Pemain (Sigma ID) tidak ditemukan. Harap hubungi Customer Service.';
    echo json_encode($response);
    exit();
}

// Ambil total bonus referral untuk user_refferal ini (yang melakukan klaim)
$total_referral_bonus = 0;
$stmt_get_bonus = $koneksi->prepare("SELECT SUM(bonus) AS total_bonus FROM tb_refferal WHERE user_refferal = ?");
if ($stmt_get_bonus) {
    $stmt_get_bonus->bind_param("s", $username_login);
    $stmt_get_bonus->execute();
    $result_bonus = $stmt_get_bonus->get_result();
    if ($data_bonus = $result_bonus->fetch_assoc()) {
        $total_referral_bonus = floatval($data_bonus['total_bonus']);
    }
    $stmt_get_bonus->close();
}

if ($total_referral_bonus <= 0) {
    $response['message'] = 'Tidak ada bonus referral yang bisa diklaim.';
    echo json_encode($response);
    exit();
}

// Minimal klaim bonus (opsional, sesuaikan dengan kebijakan Anda)
$min_claim_amount = 10000; // Contoh: Minimal klaim Rp 10.000
if ($total_referral_bonus < $min_claim_amount) {
    $response['message'] = 'Bonus referral minimal klaim adalah Rp ' . number_format($min_claim_amount, 0, ',', '.') . '.';
    echo json_encode($response);
    exit();
}


$koneksi->begin_transaction();
try {
    // 1. Panggil API GameXa untuk deposit ke Main Balance
    $gameXaAPI = new GameXaAPI();
    $reference_id_claim = 'CLAIM_REF_' . uniqid($username_login); // ID Referensi unik untuk klaim
    
    $deposit_response = $gameXaAPI->depositToPlayer($id_sigma_anggota, $total_referral_bonus, $reference_id_claim);

    if (!($deposit_response['success'] ?? false)) {
        throw new Exception('Gagal melakukan deposit via API GameXa. Pesan: ' . ($deposit_response['message'] ?? 'Respon tidak valid.'));
    }

    // 2. Update saldo anggota di database lokal (opsional, karena GameXa akan jadi sumber utama saldo)
    // Sebaiknya panggil getPlayerBalance untuk memastikan saldo terbaru
    $balance_check_response = $gameXaAPI->getPlayerBalance($id_sigma_anggota);
    if (!($balance_check_response['success'] ?? false) || !isset($balance_check_response['data']['balance'])) {
        throw new Exception('Gagal memverifikasi saldo akhir dari GameXa setelah klaim. Klaim mungkin berhasil di GameXa, tetapi sinkronisasi gagal.');
    }
    $final_gamexa_balance = floatval($balance_check_response['data']['balance']);

    $stmt_update_saldo_lokal = $koneksi->prepare("UPDATE anggota SET saldo_anggota = ? WHERE id_anggota = ?");
    $stmt_update_saldo_lokal->bind_param("di", $final_gamexa_balance, $id_anggota_login);
    if (!$stmt_update_saldo_lokal->execute()) {
        throw new Exception('Gagal update saldo lokal setelah klaim: ' . $stmt_update_saldo_lokal->error);
    }
    $stmt_update_saldo_lokal->close();

    // Update session saldo pengguna
    $_SESSION['saldo_anggota'] = $final_gamexa_balance;

    // 3. Reset bonus di tabel tb_refferal
    // Format bonus yang diklaim di PHP, bukan di SQL
    $formatted_claimed_bonus = number_format($total_referral_bonus, 0, ',', '.');
    $keterangan_klaim = "Bonus diklaim pada " . date("Y-m-d H:i:s") . ". Klaim: Rp {$formatted_claimed_bonus}. TRX ID: {$reference_id_claim}";

    // Cara 1: Ubah bonus menjadi 0 untuk semua entri yang diklaim oleh user_refferal ini
    // dan perbarui keterangan.
    $stmt_reset_bonus = $koneksi->prepare("UPDATE tb_refferal SET bonus = 0, keterangan = ?, tanggal = NOW() WHERE user_refferal = ?");
    if (!$stmt_reset_bonus) {
         throw new Exception('Gagal mempersiapkan reset bonus referral: ' . $koneksi->error);
    }
    $stmt_reset_bonus->bind_param("ss", $keterangan_klaim, $username_login);
    if (!$stmt_reset_bonus->execute()) {
        throw new Exception('Gagal mereset bonus referral di DB: ' . $stmt_reset_bonus->error);
    }
    $stmt_reset_bonus->close();

    $koneksi->commit();
    $response['status'] = 'success';
    $response['message'] = 'Bonus referral sebesar Rp ' . number_format($total_referral_bonus, 0, ',', '.') . ' berhasil diklaim ke saldo utama Anda!';
    $response['new_balance'] = $final_gamexa_balance;

} catch (Exception $e) {
    if (isset($koneksi) && $koneksi->ping()) { $koneksi->rollback(); }
    $response['message'] = 'Proses klaim gagal: ' . $e->getMessage();
    error_log("Referral Claim Error for user {$username_login}: " . $e->getMessage());
}

echo json_encode($response);
exit();
?>