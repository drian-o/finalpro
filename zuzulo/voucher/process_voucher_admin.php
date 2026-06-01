<?php
// admin/voucher/process_voucher_admin.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['kode_admin'])) {
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak. Silakan login sebagai admin.']);
    exit();
}

include_once __DIR__ . '/../../koneksi.php'; // Sesuaikan path

$response = ['status' => 'error', 'message' => 'Operasi gagal.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = $_POST['id'] ?? null;
    $voucher_code = trim($_POST['voucher_code'] ?? '');
    $amount = floatval($_POST['amount'] ?? 0);
    $is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1; // Default aktif

    // Logika proses berdasarkan 'action'
    switch ($action) {
        case 'add':
            if (empty($voucher_code) || $amount <= 0) {
                $response['message'] = 'Kode voucher dan jumlah tidak boleh kosong atau nol.';
                break;
            }
            
            // Cek duplikasi kode voucher
            $stmt = $koneksi->prepare("SELECT id FROM vouchers WHERE voucher_code = ?");
            $stmt->bind_param("s", $voucher_code);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $response['message'] = 'Kode voucher sudah ada, gunakan kode lain.';
                $stmt->close();
                break;
            }
            $stmt->close();

            $stmt = $koneksi->prepare("INSERT INTO vouchers (voucher_code, amount, is_active) VALUES (?, ?, ?)");
            $stmt->bind_param("sdi", $voucher_code, $amount, $is_active);
            if ($stmt->execute()) {
                $response['status'] = 'success';
                $response['message'] = 'Voucher berhasil ditambahkan!';
            } else {
                $response['message'] = 'Gagal menambahkan voucher: ' . $stmt->error;
            }
            $stmt->close();
            break;

        case 'edit':
            if (empty($id) || empty($voucher_code) || $amount <= 0) {
                $response['message'] = 'ID voucher, kode voucher, dan jumlah tidak boleh kosong atau nol.';
                break;
            }

            // Cek duplikasi kode voucher untuk kasus edit (kecuali kode itu sendiri)
            $stmt = $koneksi->prepare("SELECT id FROM vouchers WHERE voucher_code = ? AND id != ?");
            $stmt->bind_param("si", $voucher_code, $id);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $response['message'] = 'Kode voucher sudah digunakan oleh voucher lain.';
                $stmt->close();
                break;
            }
            $stmt->close();

            $stmt = $koneksi->prepare("UPDATE vouchers SET voucher_code = ?, amount = ?, is_active = ? WHERE id = ?");
            $stmt->bind_param("sidi", $voucher_code, $amount, $is_active, $id);
            if ($stmt->execute()) {
                $response['status'] = 'success';
                $response['message'] = 'Voucher berhasil diperbarui!';
            } else {
                $response['message'] = 'Gagal memperbarui voucher: ' . $stmt->error;
            }
            $stmt->close();
            break;

        case 'delete':
            if (empty($id)) {
                $response['message'] = 'ID voucher tidak valid untuk dihapus.';
                break;
            }
            $stmt = $koneksi->prepare("DELETE FROM vouchers WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $response['status'] = 'success';
                $response['message'] = 'Voucher berhasil dihapus!';
            } else {
                $response['message'] = 'Gagal menghapus voucher: ' . $stmt->error;
            }
            $stmt->close();
            break;

        default:
            $response['message'] = 'Aksi tidak valid.';
            break;
    }
} else {
    $response['message'] = 'Metode request tidak diizinkan.';
}

echo json_encode($response);
exit();
?>