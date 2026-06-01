<?php
session_start();
header('Content-Type: text/html');

// Pastikan pengguna sudah login
if (!isset($_SESSION['id_anggota'])) {
    http_response_code(403);
    echo '<p class="text-center mt-3 text-red-500">Akses ditolak. Mohon login terlebih dahulu.</p>';
    exit;
}

// Sertakan file koneksi database
include_once '../koneksi.php';

$id_anggota = $_SESSION['id_anggota'];
$nama_pengguna_anggota = $_SESSION['nama_pengguna_anggota'];

$data = [];

try {
    // Ambil data dari tabel `claim_bonus`
    $stmt_claim_bonus = $koneksi->prepare("
        SELECT 
            jumlah_bonus_diklaim AS jumlah, 
            tanggal_claim AS tanggal, 
            keterangan, 
            'Bonus Klaim Saldo' AS tipe_bonus
        FROM claim_bonus 
        WHERE id_anggota_claim = ?
    ");
    $stmt_claim_bonus->bind_param("i", $id_anggota);
    $stmt_claim_bonus->execute();
    $result_claim_bonus = $stmt_claim_bonus->get_result();

    while ($row = $result_claim_bonus->fetch_assoc()) {
        $data[] = $row;
    }
    $stmt_claim_bonus->close();

    // Ambil data dari tabel `user_claimed_vouchers`
    // Gabungkan dengan tabel `vouchers` untuk mendapatkan jumlah bonus
    $stmt_vouchers = $koneksi->prepare("
        SELECT 
            v.amount AS jumlah, 
            ucv.claimed_at AS tanggal,
            v.voucher_code AS keterangan,
            'Voucher' AS tipe_bonus
        FROM user_claimed_vouchers ucv
        JOIN vouchers v ON ucv.voucher_id = v.id
        WHERE ucv.user_id = ?
    ");
    $stmt_vouchers->bind_param("i", $id_anggota);
    $stmt_vouchers->execute();
    $result_vouchers = $stmt_vouchers->get_result();

    while ($row = $result_vouchers->fetch_assoc()) {
        $data[] = $row;
    }
    $stmt_vouchers->close();

    // Urutkan data berdasarkan tanggal terbaru
    usort($data, function($a, $b) {
        return strtotime($b['tanggal']) - strtotime($a['tanggal']);
    });
    
} catch (Exception $e) {
    http_response_code(500);
    echo '<p class="text-center mt-3 text-red-500">Terjadi kesalahan saat mengambil data: ' . htmlspecialchars($e->getMessage()) . '</p>';
    exit;
}

// Tampilkan hasil dalam tabel
if (empty($data)) {
    echo '<p class="text-center mt-3 text-gray-500">Tidak ada riwayat bonus yang ditemukan.</p>';
} else {
    ?>
    <div class="w-full lg:overflow-visible overflow-auto rounded-xl">
        <table class="min-w-full text-left text-sm whitespace-nowrap lg:border border-separator">
            <thead class="bg-primary text-white">
                <tr class="h-10">
                    <th scope="col" class="px-4 py-2 font-semibold">Tipe Bonus</th>
                    <th scope="col" class="px-4 py-2 font-semibold">Jumlah</th>
                    <th scope="col" class="px-4 py-2 font-semibold">Keterangan</th>
                    <th scope="col" class="px-4 py-2 font-semibold">Tanggal Klaim</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $row): ?>
                <tr class="border-b border-separator h-12">
                    <td class="px-4 py-2"><?php echo htmlspecialchars($row['tipe_bonus']); ?></td>
                    <td class="px-4 py-2"><?php echo number_format($row['jumlah'], 2, ',', '.'); ?></td>
                    <td class="px-4 py-2"><?php echo htmlspecialchars($row['keterangan']); ?></td>
                    <td class="px-4 py-2"><?php echo htmlspecialchars(date('Y-m-d H:i:s', strtotime($row['tanggal']))); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}