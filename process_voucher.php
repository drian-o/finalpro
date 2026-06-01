<?php
// process_voucher.php

session_start();
header('Content-Type: application/json'); // Ensure the response is JSON

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Anda harus login untuk mengklaim voucher.']);
    exit();
}

// Include database connection and API class
include_once __DIR__ . '/koneksi.php'; // Adjust path as needed
// Ganti include_once __DIR__ . '/classes/chaos.php';
include_once __DIR__ . '/classes/class.exa.php'; // Mengganti dengan class.exa.php

// Inisialisasi API class GameXaAPI
$exaAPI = new GameXaAPI(); 

$response = ['status' => 'error', 'message' => 'Terjadi kesalahan tidak dikenal.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $voucherCode = trim($_POST['voucher_code'] ?? '');
    $userId = $_SESSION['id_anggota'] ?? null; // Assuming 'id_anggota' is stored in session
    $username = $_SESSION['nama_pengguna_anggota'] ?? null; // Assuming 'nama_pengguna_anggota' is in session

    if (empty($voucherCode)) {
        $response['message'] = 'Kode voucher tidak boleh kosong.';
        echo json_encode($response);
        exit();
    }

    if ($userId === null || $username === null) {
        $response['message'] = 'Informasi pengguna tidak lengkap. Harap login ulang.';
        echo json_encode($response);
        exit();
    }

    // Ambil id_sigma dari database lokal
    $id_sigma_anggota = null;
    $stmt_get_sigma = $koneksi->prepare("SELECT id_sigma FROM anggota WHERE id_anggota = ?");
    if ($stmt_get_sigma) {
        $stmt_get_sigma->bind_param("i", $userId);
        $stmt_get_sigma->execute();
        $result_sigma = $stmt_get_sigma->get_result();
        if ($data_sigma = $result_sigma->fetch_assoc()) {
            $id_sigma_anggota = $data_sigma['id_sigma'];
        }
        $stmt_get_sigma->close();
    }

    if (empty($id_sigma_anggota)) {
        $response['message'] = 'ID Pemain (Sigma ID) tidak ditemukan untuk akun Anda. Harap hubungi Customer Service.';
        echo json_encode($response);
        exit();
    }

    // Start a transaction for atomicity
    mysqli_begin_transaction($koneksi);

    try {
        // 1. Check if the voucher exists and is active
        $stmt = $koneksi->prepare("SELECT id, voucher_code, amount FROM vouchers WHERE voucher_code = ? AND is_active = TRUE");
        $stmt->bind_param("s", $voucherCode);
        $stmt->execute();
        $result = $stmt->get_result();
        $voucher = $result->fetch_assoc();
        $stmt->close();

        if (!$voucher) {
            $response['message'] = 'Kode voucher tidak ditemukan atau sudah tidak aktif.';
            mysqli_rollback($koneksi);
            echo json_encode($response);
            exit();
        }

        // 2. Check if the user has already claimed this voucher
        $stmt = $koneksi->prepare("SELECT id FROM user_claimed_vouchers WHERE user_id = ? AND voucher_id = ?");
        $stmt->bind_param("ii", $userId, $voucher['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $response['message'] = 'Anda sudah mengklaim voucher ini sebelumnya.';
            mysqli_rollback($koneksi);
            echo json_encode($response);
            exit();
        }
        $stmt->close();

        // 3. Process the deposit via API (GameXaAPI)
        $amountToDeposit = $voucher['amount'];
        $voucher_trx_id = 'VOUCHER_' . $voucherCode . '_' . uniqid(); // Unique reference ID for GameXa
        
        $api_deposit_response = $exaAPI->depositToPlayer($id_sigma_anggota, $amountToDeposit, $voucher_trx_id);

        // Periksa respons GameXaAPI
        if (isset($api_deposit_response['success']) && $api_deposit_response['success'] === true) {
            // API deposit successful, now get the latest balance from API for verification
            $api_getbalance_response = $exaAPI->getPlayerBalance($id_sigma_anggota);

            if (isset($api_getbalance_response['success']) && $api_getbalance_response['success'] === true && isset($api_getbalance_response['data']['balance'])) {
                $saldo_final_untuk_db = floatval($api_getbalance_response['data']['balance']);

                // Update local database balance
                $stmt_update_saldo = $koneksi->prepare("UPDATE anggota SET saldo_anggota = ? WHERE id_anggota = ?");
                $stmt_update_saldo->bind_param("ds", $saldo_final_untuk_db, $userId);
                if (!$stmt_update_saldo->execute()) {
                    throw new Exception("Gagal update saldo di database lokal setelah klaim voucher: " . $stmt_update_saldo->error);
                }
                $stmt_update_saldo->close();

                // 4. Record that the user claimed this voucher
                $stmt_claim = $koneksi->prepare("INSERT INTO user_claimed_vouchers (user_id, voucher_id, claimed_at) VALUES (?, ?, NOW())");
                $stmt_claim->bind_param("ii", $userId, $voucher['id']);
                if (!$stmt_claim->execute()) {
                    throw new Exception("Gagal mencatat klaim voucher: " . $stmt_claim->error);
                }
                $stmt_claim->close();

                mysqli_commit($koneksi); // Commit the transaction
                
                // Update session saldo pengguna
                $_SESSION['saldo_anggota'] = $saldo_final_untuk_db;

                $response['status'] = 'success';
                $response['message'] = 'Selamat! Voucher berhasil diklaim. Saldo Anda telah ditambahkan sebesar Rp ' . number_format($amountToDeposit, 2, ',', '.') . '.';
                $response['new_balance'] = $saldo_final_untuk_db; // Kirim saldo terbaru ke frontend
            } else {
                // GetBalance failed, rollback everything
                throw new Exception("Klaim voucher berhasil, tetapi gagal mendapatkan saldo terbaru dari API untuk verifikasi.");
            }
        } else {
            // API deposit failed, rollback everything
            $api_error_msg = "Gagal memproses deposit voucher via API.";
            if ($api_deposit_response && isset($api_deposit_response['message'])) {
                $api_error_msg .= " Pesan API: " . $api_deposit_response['message'];
            }
            throw new Exception($api_error_msg);
        }

    } catch (Exception $e) {
        mysqli_rollback($koneksi); // Rollback if any step fails
        $response['message'] = 'Klaim gagal: ' . $e->getMessage();
        error_log("Voucher Claim Error for user {$username} (Voucher: {$voucherCode}): " . $e->getMessage());
    }
} else {
    $response['message'] = 'Metode request tidak valid.';
}

echo json_encode($response);
exit();
?>