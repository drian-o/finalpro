<?php
// rekomendasi.php
  include_once '../koneksi.php'; // Koneksi database
  // Asumsi $alamat_admin didefinisikan di koneksi.php
  if (!isset($_SESSION['kode_admin'])) {
    echo '
      <script>
        alert("Terjadi kesalahan, harap masuk kembali!");
        window.location.replace("'.$alamat_admin.'keluar.php");
      </script>
    ';
    exit(); //
  }

// --- FUNGSI HELPER & KONFIGURASI ---
$logFileDir = __DIR__ . '/logs/';
$logFilePath = $logFileDir . 'admin_rekomendasi.log'; //
if (!is_dir($logFileDir)) { @mkdir($logFileDir, 0775, true); } //

function log_rekomendasi_action($message) {
    global $logFilePath;
    $timestamp = date("Y-m-d H:i:s");
    file_put_contents($logFilePath, "[{$timestamp}] " . $message . PHP_EOL, FILE_APPEND | LOCK_EX); //
}

// Map tipe game ke nama tabel gamelist yang sesuai
$game_type_to_table_map = [
    'SLOT' => 'slot_gamelist',
    'LOTTERY' => 'lottery_gamelist',
    'LIVE_CASINO' => 'casino_gamelist',
    'COCK_FIGHTING' => 'egames_gamelist',
    'OTHER' => 'egames_gamelist',
    'VIRTUAL_SPORT' => 'egames_gamelist',
    'SPORT_BOOK' => 'sports_gamelist'
];

// Dapatkan daftar tipe game yang valid untuk dropdown di form
$valid_game_types_display = [
    'SLOT', 'LOTTERY', 'LIVE_CASINO', 'COCK_FIGHTING', 'OTHER', 'VIRTUAL_SPORT', 'SPORT_BOOK'
];

// --- PROSES ACTION (Tambah/Hapus Rekomendasi) ---
$flash_message = ['type' => '', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        try {
            $koneksi->begin_transaction(); //

            if ($_POST['action'] === 'add') {
                $game_type = $_POST['game_type'] ?? '';
                $provider_code = $_POST['provider_code'] ?? '';
                $game_code = $_POST['game_code'] ?? '';
                $game_source = $_POST['game_source'] ?? '';
                $display_order = (int)($_POST['display_order'] ?? 0);
                $custom_image_path = trim($_POST['custom_image_path'] ?? '');

                if (empty($game_type) || empty($provider_code) || empty($game_code) || empty($game_source) || !isset($game_type_to_table_map[$game_type])) {
                    throw new Exception("Data tidak lengkap untuk menambahkan rekomendasi.");
                }

                $target_table = $game_type_to_table_map[$game_type];
                $game_name_from_source = ''; // Akan dicari dari srg_gamelist atau telo_gamelist

                // Cari nama game dari tabel gamelist sumber yang benar
                if ($game_source === 'srg') {
                    $stmt_game_info = $koneksi->prepare("SELECT game_name FROM srg_gamelist WHERE game_code = ? AND provider_code = ? AND game_type = ? LIMIT 1");
                    // Bind game_type yang benar untuk SRG (uppercase)
                    $stmt_game_info->bind_param("sss", $game_code, $provider_code, $game_type);
                } elseif ($game_source === 'telo') {
                    $stmt_game_info = $koneksi->prepare("SELECT game_name FROM telo_gamelist WHERE game_code = ? AND provider_code = ? AND game_type = ? LIMIT 1");
                    // Bind game_type yang benar untuk Telo (lowercase)
                    $stmt_game_info->bind_param("sss", $game_code, $provider_code, strtolower($game_type));
                } else {
                    throw new Exception("Sumber game tidak valid.");
                }

                if (!$stmt_game_info || !$stmt_game_info->execute() || !($result_game_info = $stmt_game_info->get_result()) || !($game_info = $result_game_info->fetch_assoc())) {
                    throw new Exception("Game tidak ditemukan di database sumber. Pastikan data provider dan game sudah terupdate.");
                }
                $game_name_from_source = $game_info['game_name'];
                $stmt_game_info->close();

                // Cek duplikasi di tabel rekomendasi
                $stmt_check_duplicate = $koneksi->prepare("SELECT id FROM {$target_table} WHERE game_code = ? AND provider_code = ? AND game_source = ? AND game_type = ? LIMIT 1");
                $stmt_check_duplicate->bind_param("ssss", $game_code, $provider_code, $game_source, $game_type);
                $stmt_check_duplicate->execute();
                $stmt_check_duplicate->store_result();
                if ($stmt_check_duplicate->num_rows > 0) {
                    throw new Exception("Game ini sudah ada di daftar rekomendasi untuk tipe game yang dipilih.");
                }
                $stmt_check_duplicate->close();

                // Insert ke tabel rekomendasi
                $stmt_add_rekomendasi = $koneksi->prepare("INSERT INTO {$target_table} (game_code, provider_code, game_source, game_type, game_name, display_order, custom_image_path, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, TRUE)");
                
                // Pastikan game_type yang disimpan sesuai dengan tipe di tabel rekomendasi
                // (game_type dari POST sudah yang spesifik)
                
                $stmt_add_rekomendasi->bind_param("sssssis", $game_code, $provider_code, $game_source, $game_type, $game_name_from_source, $display_order, $custom_image_path);
                
                if (!$stmt_add_rekomendasi->execute()) {
                    throw new Exception("Gagal menambahkan game rekomendasi: " . $stmt_add_rekomendasi->error);
                }
                $stmt_add_rekomendasi->close();
                
                $flash_message = ['type' => 'success', 'message' => 'Game rekomendasi berhasil ditambahkan.'];
                log_rekomendasi_action("SUCCESS: Admin menambahkan rekomendasi {$game_code} dari {$provider_code} ({$game_source}) ke {$target_table}.");

            } elseif ($_POST['action'] === 'delete') {
                $id = (int)($_POST['id'] ?? 0);
                $game_type_list = $_POST['game_type_list'] ?? ''; // Tipe game dari list untuk menentukan tabel
                
                if ($id <= 0 || empty($game_type_list) || !isset($game_type_to_table_map[$game_type_list])) {
                    throw new Exception("Data tidak lengkap untuk menghapus rekomendasi.");
                }
                $target_table = $game_type_to_table_map[$game_type_list];

                $stmt_delete_rekomendasi = $koneksi->prepare("DELETE FROM {$target_table} WHERE id = ?");
                if (!$stmt_delete_rekomendasi) throw new Exception("DB Error (prepare delete rekomendasi): " . $koneksi->error);
                $stmt_delete_rekomendasi->bind_param("i", $id);
                if (!$stmt_delete_rekomendasi->execute()) {
                    throw new Exception("Gagal menghapus game rekomendasi: " . $stmt_delete_rekomendasi->error);
                }
                $stmt_delete_rekomendasi->close();

                $flash_message = ['type' => 'success', 'message' => 'Game rekomendasi berhasil dihapus.'];
                log_rekomendasi_action("SUCCESS: Admin menghapus rekomendasi ID: {$id} dari {$target_table}.");

            } else {
                throw new Exception("Aksi tidak valid.");
            }
            $koneksi->commit(); //
        } catch (Exception $e) {
            if (isset($koneksi) && $koneksi->ping()) { $koneksi->rollback(); } //
            $flash_message = ['type' => 'danger', 'message' => 'Error: ' . $e->getMessage()]; //
            log_rekomendasi_action("ERROR: Gagal proses aksi rekomendasi. Pesan: " . $e->getMessage()); //
        }
    }
}


// --- AMBIL DATA REKOMENDASI YANG SUDAH ADA ---
$all_recommendations = [];
foreach ($game_type_to_table_map as $type_key => $table_name) {
    // Penanganan khusus untuk tipe game E-Games yang menggunakan satu tabel (egames_gamelist)
    if (in_array($type_key, ['COCK_FIGHTING', 'OTHER', 'VIRTUAL_SPORT'])) {
        // Proses egames_gamelist hanya sekali
        if ($table_name === 'egames_gamelist' && !isset($all_recommendations['egames_processed_flag'])) {
            $query = "SELECT id, game_code, provider_code, game_source, game_type, game_name, display_order, custom_image_path FROM {$table_name} WHERE is_featured = TRUE AND game_type IN ('COCK_FIGHTING', 'OTHER', 'VIRTUAL_SPORT') ORDER BY game_type ASC, display_order ASC";
            $result = mysqli_query($koneksi, $query);
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $all_recommendations[$table_name][] = $row;
                }
            } else {
                log_rekomendasi_action("DB Error fetching {$table_name}: " . mysqli_error($koneksi));
            }
            $all_recommendations['egames_processed_flag'] = true; // Tandai sudah diproses
        }
        continue; // Lanjutkan ke tipe berikutnya setelah egames diproses
    }
    
    // Untuk tipe game lainnya (SLOT, LOTTERY, LIVE_CASINO, SPORT_BOOK)
    $query = "SELECT id, game_code, provider_code, game_source, game_type, game_name, display_order, custom_image_path FROM {$table_name} WHERE is_featured = TRUE AND game_type = ? ORDER BY display_order ASC, game_name ASC";
    $stmt = $koneksi->prepare($query);
    if ($stmt) {
        $stmt->bind_param("s", $type_key); // Bind tipe game
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = mysqli_fetch_assoc($result)) {
            $all_recommendations[$table_name][] = $row;
        }
        $stmt->close();
    } else {
        log_rekomendasi_action("DB Error preparing query for {$table_name}: " . mysqli_error($koneksi));
    }
}
unset($all_recommendations['egames_processed_flag']); // Hapus flag sementara setelah loop selesai

// --- AMBIL DATA SUMBER UNTUK DROPDOWN (Providers & Games) ---
$all_srg_providers = [];
$all_telo_providers = [];
$all_srg_games = [];
$all_telo_games = [];

// SRG Providers
$query_srg_prov = mysqli_query($koneksi, "SELECT provider_code, provider_name, provider_type FROM srg_provider ORDER BY provider_name ASC");
if ($query_srg_prov) {
    while($row = mysqli_fetch_assoc($query_srg_prov)) { $all_srg_providers[] = $row; }
}

// Telo Providers
$query_telo_prov = mysqli_query($koneksi, "SELECT provider_code, provider_name, provider_type FROM telo_provider ORDER BY provider_name ASC");
if ($query_telo_prov) {
    while($row = mysqli_fetch_assoc($query_telo_prov)) { $all_telo_providers[] = $row; }
}

// SRG Games (Hanya ambil game_code dan game_name)
$query_srg_games = mysqli_query($koneksi, "SELECT provider_code, game_code, game_name, game_type FROM srg_gamelist ORDER BY game_name ASC");
if ($query_srg_games) {
    while($row = mysqli_fetch_assoc($query_srg_games)) { 
        // Kunci array: provider_code_game_type_uppercase_untuk_konsistensi
        $key = $row['provider_code'] . '_' . strtoupper($row['game_type']);
        if (!isset($all_srg_games[$key])) { $all_srg_games[$key] = []; }
        $all_srg_games[$key][] = $row; 
    }
}

// Telo Games (Hanya ambil game_code dan game_name)
$query_telo_games = mysqli_query($koneksi, "SELECT provider_code, game_code, game_name, game_type FROM telo_gamelist ORDER BY game_name ASC");
if ($query_telo_games) {
    while($row = mysqli_fetch_assoc($query_telo_games)) { 
        // Kunci array: provider_code_game_type_uppercase_untuk_konsistensi
        $key = $row['provider_code'] . '_' . strtoupper($row['game_type']);
        if (!isset($all_telo_games[$key])) { $all_telo_games[$key] = []; }
        $all_telo_games[$key][] = $row; 
    }
}

// Data untuk JavaScript (gabungkan providers by source, games by key)
$js_providers_data = json_encode(['srg' => $all_srg_providers, 'telo' => $all_telo_providers]);
$js_games_data = json_encode(['srg' => $all_srg_games, 'telo' => $all_telo_games]);


?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manajemen Rekomendasi Game</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container .select2-selection--single { height: 38px !important; display: flex; align-items: center; }
        .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 38px; }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px; }
        .select2-dropdown { z-index: 1051; } /* Pastikan dropdown Select2 di atas modal jika ada */
        .card-header h5 { margin-bottom: 0; }
        .table-responsive { max-height: 400px; overflow-y: auto; }
    </style>
</head>
<body>
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="py-3 mb-4">
            <span class="text-muted fw-light">Menu Admin /</span> Manajemen Rekomendasi Game
        </h4>

        <?php if (!empty($flash_message['message'])): ?>
            <div class="alert alert-<?php echo $flash_message['type']; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($flash_message['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card mb-4">
            <h5 class="card-header">Tambah Rekomendasi Baru</h5>
            <div class="card-body">
                <form method="POST" action="rekomendasi.php">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="mb-3">
                        <label for="gameTypeSelect" class="form-label">Tipe Game:</label>
                        <select class="form-select" id="gameTypeSelect" name="game_type" required>
                            <option value="">-- Pilih Tipe Game --</option>
                            <?php foreach ($valid_game_types_display as $type): ?>
                                <option value="<?php echo htmlspecialchars($type); ?>"><?php echo htmlspecialchars(str_replace('_', ' ', strtoupper($type))); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="providerSourceSelect" class="form-label">Sumber Provider:</label>
                        <select class="form-select" id="providerSourceSelect" name="game_source" required>
                            <option value="">-- Pilih Sumber Provider --</option>
                            <option value="srg">SRG</option>
                            <option value="telo">Telo.is</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="providerSelect" class="form-label">Provider:</label>
                        <select class="form-select" id="providerSelect" name="provider_code" disabled required>
                            <option value="">-- Pilih Provider --</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="gameSelect" class="form-label">Game:</label>
                        <select class="form-select" id="gameSelect" name="game_code" disabled required>
                            <option value="">-- Pilih Game --</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="displayOrderInput" class="form-label">Urutan Tampilan (Display Order):</label>
                        <input type="number" class="form-control" id="displayOrderInput" name="display_order" value="0">
                    </div>

                    <div class="mb-3">
                        <label for="customImagePathInput" class="form-label">Custom Image Path (Opsional):</label>
                        <input type="text" class="form-control" id="customImagePathInput" name="custom_image_path" placeholder="e.g., upload/custom/mygame.png">
                        <small class="form-text text-muted">Path relatif dari root web, misal: `upload/custom/mygame.png`.</small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Tambah Rekomendasi</button>
                </form>
            </div>
        </div>

        <div class="card">
            <h5 class="card-header">Daftar Rekomendasi Saat Ini</h5>
            <div class="card-body">
                <?php if (empty($all_recommendations)): ?>
                    <p>Belum ada game yang direkomendasikan.</p>
                <?php else: ?>
                    <?php 
                    // Header tabel untuk setiap tipe game yang memiliki rekomendasi
                    $table_headers = [
                        'slot_gamelist' => 'Slot',
                        'lottery_gamelist' => 'Lotto/Keno',
                        'casino_gamelist' => 'Live Casino',
                        'egames_gamelist' => 'E-Games', // Ini akan mencakup semua tipe e-games
                        'sports_gamelist' => 'Sportsbook'
                    ];
                    ?>
                    <?php 
                    // Loop melalui setiap nama tabel dalam $game_type_to_table_map untuk menampilkan data
                    $processed_egames_table = false; // Flag untuk memastikan tabel egames hanya ditampilkan sekali
                    foreach ($game_type_to_table_map as $type_key_check => $table_name_check): 
                        // Jika ini adalah salah satu tipe E-Games, dan tabel egames_gamelist belum diproses
                        if (in_array($type_key_check, ['COCK_FIGHTING', 'OTHER', 'VIRTUAL_SPORT'])) {
                            if ($table_name_check === 'egames_gamelist' && !$processed_egames_table) {
                                // Proses egames_gamelist
                                if (isset($all_recommendations[$table_name_check]) && !empty($all_recommendations[$table_name_check])) {
                                    echo '<h6 class="mt-4 mb-3">E-Games (Cock Fighting, Other, Virtual Sport) Rekomendasi</h6>';
                                    echo '<div class="table-responsive mb-4">';
                                    echo '<table class="table table-bordered table-striped"><thead><tr><th>ID</th><th>Game Code</th><th>Nama Game</th><th>Provider Code</th><th>Sumber</th><th>Tipe Game</th><th>Urutan</th><th>Gambar Kustom</th><th>Aksi</th></tr></thead><tbody>';
                                    // Urutkan ulang data rekomendasi E-Games berdasarkan tipe dan urutan
                                    $current_recs = $all_recommendations[$table_name_check];
                                    usort($current_recs, function($a, $b) {
                                        if ($a['game_type'] !== $b['game_type']) {
                                            return strcmp($a['game_type'], $b['game_type']);
                                        }
                                        return $a['display_order'] <=> $b['display_order'];
                                    });
                                    foreach ($current_recs as $rec): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($rec['id']); ?></td>
                                            <td><?php echo htmlspecialchars($rec['game_code']); ?></td>
                                            <td><?php echo htmlspecialchars($rec['game_name']); ?></td>
                                            <td><?php echo htmlspecialchars($rec['provider_code']); ?></td>
                                            <td><?php echo htmlspecialchars(strtoupper($rec['game_source'])); ?></td>
                                            <td><?php echo htmlspecialchars(str_replace('_', ' ', strtoupper($rec['game_type']))); ?></td>
                                            <td><?php echo htmlspecialchars($rec['display_order']); ?></td>
                                            <td>
                                                <?php if (!empty($rec['custom_image_path'])): ?>
                                                    <img src="../<?php echo htmlspecialchars($rec['custom_image_path']); ?>" alt="Img" style="width: 50px; height: auto;">
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <form method="POST" action="rekomendasi.php" onsubmit="return confirm('Anda yakin ingin menghapus rekomendasi ini?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($rec['id']); ?>">
                                                    <input type="hidden" name="game_type_list" value="<?php echo htmlspecialchars($rec['game_type']); ?>"> <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach;
                                    echo '</tbody></table></div>';
                                }
                                $processed_egames_table = true; // Set flag agar tidak diproses lagi
                            }
                            continue; // Lanjutkan ke iterasi berikutnya
                        }
                        
                        // Untuk tipe game lainnya (SLOT, LOTTERY, LIVE_CASINO, SPORT_BOOK)
                        if (!isset($all_recommendations[$table_name_check]) || empty($all_recommendations[$table_name_check])) continue;
                    ?>
                        <h6 class="mt-4 mb-3"><?php echo htmlspecialchars($table_headers[$table_name_check] ?? ucfirst($table_name_check)) . ' Rekomendasi'; ?></h6>
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Game Code</th>
                                        <th>Nama Game</th>
                                        <th>Provider Code</th>
                                        <th>Sumber</th>
                                        <th>Tipe Game</th>
                                        <th>Urutan</th>
                                        <th>Gambar Kustom</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($all_recommendations[$table_name_check] as $rec): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($rec['id']); ?></td>
                                            <td><?php echo htmlspecialchars($rec['game_code']); ?></td>
                                            <td><?php echo htmlspecialchars($rec['game_name']); ?></td>
                                            <td><?php echo htmlspecialchars($rec['provider_code']); ?></td>
                                            <td><?php echo htmlspecialchars(strtoupper($rec['game_source'])); ?></td>
                                            <td><?php echo htmlspecialchars(str_replace('_', ' ', strtoupper($rec['game_type']))); ?></td>
                                            <td><?php echo htmlspecialchars($rec['display_order']); ?></td>
                                            <td>
                                                <?php if (!empty($rec['custom_image_path'])): ?>
                                                    <img src="../<?php echo htmlspecialchars($rec['custom_image_path']); ?>" alt="Img" style="width: 50px; height: auto;">
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <form method="POST" action="rekomendasi.php" onsubmit="return confirm('Anda yakin ingin menghapus rekomendasi ini?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($rec['id']); ?>">
                                                    <input type="hidden" name="game_type_list" value="<?php echo htmlspecialchars($rec['game_type']); ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            // Inisialisasi Select2 untuk semua dropdown
            $('.form-select').select2();

            // Data provider dan game dari PHP (JSON_encode)
            const allProvidersData = <?php echo $js_providers_data; ?>;
            const allGamesData = <?php echo $js_games_data; ?>;
            const gameTypeToTableMap = <?php echo json_encode($game_type_to_table_map); ?>; // Peta tipe game ke nama tabel

            const gameTypeSelect = $('#gameTypeSelect');
            const providerSourceSelect = $('#providerSourceSelect');
            const providerSelect = $('#providerSelect');
            const gameSelect = $('#gameSelect');

            // --- Fungsi untuk memuat dropdown Provider ---
            function loadProviders(selectedGameType, selectedSource) {
                providerSelect.empty().append('<option value="">-- Pilih Provider --</option>').prop('disabled', true);
                gameSelect.empty().append('<option value="">-- Pilih Game --</option>').prop('disabled', true);

                if (!selectedGameType || !selectedSource) {
                    return;
                }

                let providersToShow = [];
                if (selectedSource === 'srg') {
                    providersToShow = allProvidersData.srg;
                } else if (selectedSource === 'telo') {
                    providersToShow = allProvidersData.telo;
                }

                let filteredProviders = providersToShow.filter(p => {
                    // Cek jika tipe provider cocok dengan tipe game yang dipilih
                    // Perhatikan perbedaan casing: SRG 'SLOT', Telo 'slot'
                    // Untuk E-games, provider_type di DB mungkin generic, tapi game_type yang relevan adalah yang spesifik
                    if (['COCK_FIGHTING', 'OTHER', 'VIRTUAL_SPORT'].includes(selectedGameType)) {
                        // Jika game type adalah salah satu dari e-games, cari provider yang memiliki salah satu tipe e-games
                        // Atau jika provider_type spesifik di DB srg/telo, cocokkan langsung
                        return p.provider_type === selectedGameType || p.provider_type.toUpperCase() === selectedGameType.toUpperCase() || p.provider_type === selectedGameType.toLowerCase();
                    } else {
                        // Untuk tipe lain (SLOT, LOTTERY, LIVE_CASINO, SPORT_BOOK)
                        return p.provider_type.toUpperCase() === selectedGameType.toUpperCase();
                    }
                });

                $.each(filteredProviders, function(i, provider) {
                    providerSelect.append($('<option>', {
                        value: provider.provider_code,
                        text: provider.provider_name + ' (' + provider.provider_type.toUpperCase() + ')'
                    }));
                });
                providerSelect.prop('disabled', false);
            }

            // --- Fungsi untuk memuat dropdown Game ---
            function loadGames(selectedGameType, selectedSource, selectedProviderCode) {
                gameSelect.empty().append('<option value="">-- Pilih Game --</option>').prop('disabled', true);

                if (!selectedGameType || !selectedSource || !selectedProviderCode) {
                    return;
                }

                let gamesToShow = [];
                // Kunci untuk mencari game adalah kombinasi provider_code_game_type_uppercase_untuk_konsistensi
                const gameKey = selectedProviderCode + '_' + selectedGameType.toUpperCase(); 

                if (selectedSource === 'srg' && allGamesData.srg && allGamesData.srg[gameKey]) {
                    gamesToShow = allGamesData.srg[gameKey]; 
                } else if (selectedSource === 'telo' && allGamesData.telo && allGamesData.telo[gameKey.toLowerCase()]) { 
                    // Telo gameKey mungkin lowercase di sisi pengambilan data
                    gamesToShow = allGamesData.telo[gameKey.toLowerCase()];
                }

                $.each(gamesToShow, function(i, game) {
                    gameSelect.append($('<option>', {
                        value: game.game_code,
                        text: game.game_name
                    }));
                });
                gameSelect.prop('disabled', false);
            }

            // --- Event Listeners ---

            gameTypeSelect.on('change', function() {
                const selectedGameType = $(this).val();
                const selectedSource = providerSourceSelect.val();
                loadProviders(selectedGameType, selectedSource);
            });

            providerSourceSelect.on('change', function() {
                const selectedGameType = gameTypeSelect.val();
                const selectedSource = $(this).val();
                loadProviders(selectedGameType, selectedSource);
            });

            providerSelect.on('change', function() {
                const selectedGameType = gameTypeSelect.val();
                const selectedSource = providerSourceSelect.val();
                const selectedProviderCode = $(this).val();
                loadGames(selectedGameType, selectedSource, selectedProviderCode);
            });
            
            // --- Validasi Form Sebelum Submit ---
            $('form').on('submit', function(e) {
                const gameType = gameTypeSelect.val();
                const providerSource = providerSourceSelect.val();
                const providerCode = providerSelect.val();
                const gameCode = gameSelect.val();

                if (!gameType || !providerSource || !providerCode || !gameCode) {
                    alert('Mohon lengkapi semua pilihan Tipe Game, Sumber Provider, Provider, dan Game.');
                    e.preventDefault(); // Mencegah submit form
                    return false;
                }
            });

            // Initial calls if form fields have pre-selected values (e.g., from an error redirect)
            // Ini akan memastikan dropdown terisi jika halaman dimuat ulang karena error POST
            if (gameTypeSelect.val()) {
                const initialGameType = gameTypeSelect.val();
                const initialSource = providerSourceSelect.val();
                loadProviders(initialGameType, initialSource);
                // Menunggu providers dimuat sebelum mencoba memuat games
                providerSelect.one('change', function() {
                    const initialProviderCode = providerSelect.val();
                    if (initialProviderCode) {
                        loadGames(initialGameType, initialSource, initialProviderCode);
                    }
                });
            }
        });
    </script>
</body>
</html>