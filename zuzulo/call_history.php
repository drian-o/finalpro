<?php
// Pastikan session sudah dimulai jika menggunakan $_SESSION
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include file koneksi database (untuk $alamat_admin jika diperlukan)
// dan class whitelabel
// Sesuaikan path jika berbeda
// include_once '../koneksi.php'; // Uncomment jika $alamat_admin ada di sini
include_once '../classes/diamond-telo.php'; // Pastikan path ini benar

// Cek session admin
// Asumsi $alamat_admin didefinisikan di file lain yang di-include sebelum ini,
// atau Anda bisa mendefinisikannya di sini jika perlu.
if (!isset($alamat_admin)) {
    $alamat_admin = '/admin/'; // Ganti dengan path yang sesuai jika perlu
}

if (!isset($_SESSION['kode_admin'])) {
    echo '
        <script>
            alert("Terjadi kesalahan, harap masuk kembali!");
            window.location.replace("'.$alamat_admin.'keluar.php");
        </script>
    ';
    exit();
}

// Inisialisasi class whitelabel
$WL = new whitelabel();

// Variabel untuk menyimpan pesan dan hasil respons
$message = '';
$call_history_data = []; // Untuk menyimpan data riwayat panggilan

// Panggil fungsi getCallHistory dari class whitelabel
// Parameter offset dan limit bisa disesuaikan jika Anda ingin paging
$response_history = $WL->getCallHistory();

// Proses respons
if (isset($response_history['status'])) {
    if ($response_history['status'] == 1 && !empty($response_history['data'])) {
        $message .= '<div class="alert alert-success">Berhasil memuat riwayat panggilan.</div>';
        $call_history_data = $response_history['data'];
    } elseif ($response_history['status'] == 1 && empty($response_history['data'])) {
         $message .= '<div class="alert alert-info">Tidak ada riwayat panggilan yang ditemukan.</div>';
    } else {
        $message .= '<div class="alert alert-danger">Gagal memuat riwayat panggilan. Status: ' . ($response_history['status'] ?? 'N/A') . ', Pesan: ' . ($response_history['msg'] ?? 'Tidak ada pesan') . '</div>';
    }
} else {
     $message .= '<div class="alert alert-warning">Respons API tidak memiliki status yang jelas atau terjadi kesalahan komunikasi.</div>';
}

?>

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">Menu Utama /</span> Call History
    </h4>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <?= $message ?>

                    <?php if (!empty($call_history_data)): ?>
                        <h5 class="mt-4">Riwayat Panggilan:</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Agent Code</th>
                                        <th>User Code</th>
                                        <th>Provider</th>
                                        <th>Game Code</th>
                                        <th>Bet</th>
                                        <th>User Before Balance</th>
                                        <th>User After Balance</th>
                                        <th>Agent Before Balance</th>
                                        <th>Agent After Balance</th>
                                        <th>Expect</th>
                                        <th>Missed</th>
                                        <th>Real</th>
                                        <th>RTP</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Message</th>
                                        <th>Created At</th>
                                        <th>Updated At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($call_history_data as $history): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($history['id'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($history['agent_code'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($history['user_code'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($history['provider_code'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($history['game_code'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($history['bet'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($history['user_before_balance'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($history['user_after_balance'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($history['agent_before_balance'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($history['agent_after_balance'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($history['expect'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($history['missed'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($history['real'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($history['rtp'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($history['type'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($history['status'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($history['msg'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($history['created_at'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($history['updated_at'] ?? '') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>
