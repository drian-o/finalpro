<?php
// Pastikan session sudah dimulai jika menggunakan $_SESSION
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include class whitelabel
// Pastikan path 'diamond-telo.php' sudah benar
include_once '../classes/diamond-telo.php';

// Cek session admin
// Asumsi $alamat_admin didefinisikan di file lain yang di-include sebelum ini,
// atau Anda bisa mendefinisikannya di sini jika perlu.
// include_once '../koneksi.php'; // Jika $alamat_admin ada di koneksi.php
if (!isset($_SESSION['kode_admin'])) {
    // Definisikan $alamat_admin jika belum ada
    $alamat_admin = '/admin/'; // Ganti dengan path yang sesuai jika perlu
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

// Variabel untuk menyimpan pesan hasil proses
$message = '';
$players = [];
$calls_from_api = []; // Untuk menyimpan data calls langsung dari API
$selected_user = null;
$selected_provider = null;
$selected_game = null;

// --- STEP 1: Tampilkan daftar pemain yang sedang bermain ---
// Hanya jalankan jika tidak ada POST data untuk memilih pemain atau panggilan
if (!isset($_POST['select_player']) && !isset($_POST['select_call'])) {
    $message .= '<div class="alert alert-info">Memuat daftar pemain yang sedang bermain...</div>';
    $response_players = $WL->callPlayers();

    if (isset($response_players['status']) && $response_players['status'] == 1 && !empty($response_players['data'])) {
        $players = $response_players['data'];
        $message .= '<div class="alert alert-success">Berhasil memuat '.count($players).' pemain. Silakan pilih pemain:</div>';
    } else {
        $message .= '<div class="alert alert-warning">Tidak ada pemain yang sedang bermain atau terjadi kesalahan saat memuat data pemain. Status: ' . ($response_players['status'] ?? 'N/A') . ', Pesan: ' . ($response_players['msg'] ?? 'Tidak ada pesan') . '</div>';
         // Tampilkan tombol kembali jika tidak ada pemain
         $message .= '<br><a href="' . $_SERVER['PHP_SELF'] . '" class="btn btn-primary">Refresh Halaman</a>';
    }
}

// --- STEP 2: Jika pemain dipilih, panggil callList dan tampilkan untuk dipilih ---
if (isset($_POST['select_player'])) {
    // Ambil data pemain dari POST
    $selected_user = $_POST['user_code'];
    $selected_provider = $_POST['provider_code'];
    $selected_game = $_POST['game_code'];

    $message .= '<div class="alert alert-info">Memuat daftar panggilan (calls) untuk pemain ' . htmlspecialchars($selected_user) . '...</div>';

    // Panggil API callList
    // call_type 1 sesuai dengan dokumentasi dan permintaan awal untuk request call_list
    $response_call_list = $WL->callList($selected_provider, $selected_game, $selected_user);

    if (isset($response_call_list['status']) && $response_call_list['status'] == 1 && !empty($response_call_list['calls'])) {
        $calls_from_api = $response_call_list['calls']; // Simpan data calls langsung dari API
        $message .= '<div class="alert alert-success">Berhasil memuat ' . count($calls_from_api) . ' panggilan. Silakan pilih panggilan:</div>';

    } elseif (isset($response_call_list['status']) && $response_call_list['status'] == 1 && empty($response_call_list['calls'])) {
         $message .= '<div class="alert alert-warning">Tidak ada panggilan (calls) yang tersedia dari API untuk pemain ini. Status: ' . ($response_call_list['status'] ?? 'N/A') . ', Pesan: ' . ($response_call_list['msg'] ?? 'Tidak ada pesan') . '</div>';
         // Tampilkan tombol kembali jika tidak ada panggilan dari API
         $message .= '<br><a href="' . $_SERVER['PHP_SELF'] . '" class="btn btn-primary">Kembali</a>';
    }
    else {
        $message .= '<div class="alert alert-danger">Gagal memuat daftar panggilan dari API. Status: ' . ($response_call_list['status'] ?? 'N/A') . ', Pesan: ' . ($response_call_list['msg'] ?? 'Tidak ada pesan') . '</div>';
        // Tampilkan tombol kembali jika ada error API callList
        $message .= '<br><a href="' . $_SERVER['PHP_SELF'] . '" class="btn btn-primary">Kembali</a>';
    }
}

// --- STEP 3: Jika panggilan dipilih, panggil callApply ---
if (isset($_POST['select_call'])) {
    // Ambil data dari POST
    $selected_user = $_POST['user_code'];
    $selected_provider = $_POST['provider_code'];
    $selected_game = $_POST['game_code'];
    // Ambil RTP dan Call Type langsung dari hidden input
    $call_rtp = intval($_POST['call_rtp']);
    $call_type = intval($_POST['call_type']); // Ambil call_type dari hidden input

    $message .= '<div class="alert alert-info">Menerapkan panggilan dengan RTP ' . htmlspecialchars($call_rtp) . ' dan Call Type ' . htmlspecialchars($call_type) . ' untuk pemain ' . htmlspecialchars($selected_user) . '...</div>';

    // Panggil API callApply
    $response_call_apply = $WL->callApply($selected_user, $selected_game, $selected_provider, $call_rtp, $call_type);

    if (isset($response_call_apply['status']) && $response_call_apply['status'] == 1) {
        $message .= '<div class="alert alert-success">Call Apply Berhasil! Detail: <pre>' . htmlspecialchars(print_r($response_call_apply, true)) . '</pre></div>';
    } else {
        $message .= '<div class="alert alert-danger">Call Apply Gagal! Status: ' . ($response_call_apply['status'] ?? 'N/A') . ', Pesan: ' . ($response_call_apply['msg'] ?? 'Tidak ada pesan') . '</div>';
    }

    // Setelah callApply, kembali ke tampilan awal atau tampilkan pesan selesai
    // Untuk contoh ini, kita akan kembali ke tampilan awal setelah proses selesai
    $message .= '<br><a href="' . $_SERVER['PHP_SELF'] . '" class="btn btn-primary">Kembali</a>';
}

?>

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">Menu Utama /</span> Call Apply
    </h4>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <?= $message ?>

                    <?php
                    // Tampilkan form pilihan pemain hanya jika $players tidak kosong DAN belum ada POST untuk select_player atau select_call
                    if (!empty($players) && !isset($_POST['select_player']) && !isset($_POST['select_call'])): ?>
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="player_select" class="form-label">Pilih Pemain:</label>
                                <select class="form-select" id="player_select" name="player_data" required>
                                    <option value="">-- Pilih Pemain --</option>
                                    <?php foreach ($players as $player): ?>
                                        <option value="<?= htmlspecialchars($player['user_code']) ?>|<?= htmlspecialchars($player['provider_code']) ?>|<?= htmlspecialchars($player['game_code']) ?>">
                                            <?= htmlspecialchars($player['user_code']) ?> (<?= htmlspecialchars($player['provider_code']) ?> - <?= htmlspecialchars($player['game_code']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="hidden" name="user_code" id="selected_user_code">
                                <input type="hidden" name="provider_code" id="selected_provider_code">
                                <input type="hidden" name="game_code" id="selected_game_code">
                            </div>
                            <button type="submit" name="select_player" class="btn btn-primary">Pilih Pemain</button>
                        </form>

                        <script>
                            // Script untuk mengisi hidden input saat dropdown berubah
                            document.getElementById('player_select').addEventListener('change', function() {
                                const selectedValue = this.value;
                                if (selectedValue) {
                                    const [userCode, providerCode, gameCode] = selectedValue.split('|');
                                    document.getElementById('selected_user_code').value = userCode;
                                    document.getElementById('selected_provider_code').value = providerCode;
                                    document.getElementById('selected_game_code').value = gameCode;
                                } else {
                                    document.getElementById('selected_user_code').value = '';
                                    document.getElementById('selected_provider_code').value = '';
                                    document.getElementById('selected_game_code').value = '';
                                }
                            });
                        </script>

                    <?php
                    // Tampilkan form pilihan panggilan hanya jika $calls_from_api tidak kosong DAN sudah ada POST select_player TAPI belum ada POST select_call
                    elseif (!empty($calls_from_api) && isset($_POST['select_player']) && !isset($_POST['select_call'])): ?>
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="call_select" class="form-label">Pilih Panggilan (Call):</label>
                                <select class="form-select" id="call_select" name="call_data" required>
                                    <option value="">-- Pilih Panggilan --</option>
                                    <?php foreach ($calls_from_api as $call): ?>
                                        <?php
                                            // Asumsi call_type dari API callList adalah string seperti "Free".
                                            // Untuk request call_apply, kita perlu call_type integer.
                                            // Berdasarkan permintaan awal, call_type untuk request call_list adalah 1.
                                            // Kita akan gunakan nilai 1 ini sebagai call_type untuk request call_apply.
                                            // Jika ada logika konversi dari string "Free" ke integer lain, sesuaikan di sini.
                                            $call_type_for_apply = 1; // Menggunakan 1 sesuai permintaan awal
                                        ?>
                                        <option value="<?= htmlspecialchars($call['rtp']) ?>|<?= htmlspecialchars($call_type_for_apply) ?>">
                                            RTP: <?= htmlspecialchars($call['rtp']) ?>, Type: <?= htmlspecialchars($call['call_type'] ?? 'N/A') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="hidden" name="user_code" value="<?= htmlspecialchars($selected_user) ?>">
                                <input type="hidden" name="provider_code" value="<?= htmlspecialchars($selected_provider) ?>">
                                <input type="hidden" name="game_code" value="<?= htmlspecialchars($selected_game) ?>">
                                <input type="hidden" name="call_rtp" id="selected_call_rtp">
                                <input type="hidden" name="call_type" id="selected_call_type">
                            </div>
                            <button type="submit" name="select_call" class="btn btn-primary">Terapkan Panggilan</button>
                        </form>

                         <script>
                            // Script untuk mengisi hidden input saat dropdown berubah
                            document.getElementById('call_select').addEventListener('change', function() {
                                const selectedValue = this.value;
                                if (selectedValue) {
                                    const [rtp, callType] = selectedValue.split('|');
                                    document.getElementById('selected_call_rtp').value = rtp;
                                    document.getElementById('selected_call_type').value = callType;
                                } else {
                                    document.getElementById('selected_call_rtp').value = '';
                                    document.getElementById('selected_call_type').value = '';
                                }
                            });
                        </script>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Tidak ada penutupan koneksi database karena tidak menggunakan $conn
?>
