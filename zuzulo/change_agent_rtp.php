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
$response_data = null;

// Proses saat form disubmit
if (isset($_POST['submit_rtp'])) {
    // Ambil nilai RTP dari input pengguna
    $agent_rtp_input = $_POST['agent_rtp'];

    // Validasi input sederhana (pastikan angka dan dalam rentang yang wajar jika ada batasan)
    // Berdasarkan contoh failure response, ada batasan <= 95
    $agent_rtp = intval($agent_rtp_input); // Konversi ke integer

    if ($agent_rtp <= 0) {
         $message = '<div class="alert alert-warning">Nilai RTP harus lebih besar dari 0.</div>';
    } else {
        $message .= '<div class="alert alert-info">Memanggil API controlAgentRtp dengan RTP: ' . htmlspecialchars($agent_rtp) . '...</div>';

        // Panggil fungsi controlAgentRtp dari class whitelabel
        $response = $WL->controlAgentRtp($agent_rtp);

        // Simpan respons untuk ditampilkan
        $response_data = $response;

        // Tambahkan pesan berdasarkan status respons
        if (isset($response['status'])) {
            if ($response['status'] == 1) {
                $message .= '<div class="alert alert-success">API Call Success!</div>';
            } else {
                $message .= '<div class="alert alert-danger">API Call Failed!</div>';
            }
        } else {
             $message .= '<div class="alert alert-warning">Respons API tidak memiliki status yang jelas.</div>';
        }
    }
}

?>

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">Menu Utama /</span> Control Agent RTP
    </h4>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <?= $message ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="agent_rtp" class="form-label">Nilai RTP Agen:</label>
                            <input type="number" class="form-control" id="agent_rtp" name="agent_rtp" min="1" max="100" required>
                             <small class="form-text text-muted">Masukkan nilai RTP agen (misal: 92). Batas maksimum RTP agen biasanya 95.</small>
                        </div>
                        <button type="submit" name="submit_rtp" class="btn btn-primary">Set RTP Agen</button>
                    </form>

                    <?php if ($response_data !== null): ?>
                        <h5 class="mt-4">Hasil Respons API:</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Parameter</th>
                                        <th>Nilai</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Tampilkan semua data dari respons dalam tabel
                                    if (is_array($response_data) || is_object($response_data)) {
                                        foreach ($response_data as $key => $value) {
                                            echo '<tr>';
                                            echo '<td>' . htmlspecialchars($key) . '</td>';
                                            // Jika nilai adalah array atau objek, tampilkan sebagai JSON string
                                            if (is_array($value) || is_object($value)) {
                                                echo '<td><pre>' . htmlspecialchars(json_encode($value, JSON_PRETTY_PRINT)) . '</pre></td>';
                                            } else {
                                                echo '<td>' . htmlspecialchars($value) . '</td>';
                                            }
                                            echo '</tr>';
                                        }
                                    } else {
                                        // Jika respons bukan array/objek (misal string error mentah)
                                        echo '<tr><td colspan="2"><pre>' . htmlspecialchars(print_r($response_data, true)) . '</pre></td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>
