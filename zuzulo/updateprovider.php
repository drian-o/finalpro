<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Sesuaikan path jika berbeda
include_once '../koneksi.php'; // Menggunakan koneksi.php dari parent directory
include_once '../classes/chaos.php'; // Menggunakan chaos.php dari ../classes/

$message = '';
$provider_data_to_display = [];
$inserted_count = 0;
$updated_count = 0;

// Pastikan variabel koneksi database dan instance WL tersedia
if (!isset($koneksi) || !($koneksi instanceof mysqli)) {
    $message = '<div class="alert alert-danger">Kesalahan Koneksi Database: Variabel $koneksi tidak valid atau tidak ditemukan. Periksa file <code>koneksi.php</code>.</div>';
} elseif (!isset($WL) || !($WL instanceof zulhayker)) {
    $message = '<div class="alert alert-danger">Kesalahan Sistem: Komponen Whitelabel (WL) tidak tersedia. Periksa file <code>classes/chaos.php</code>.</div>';
}

// Cek session admin setelah include dan pemeriksaan dasar
if (empty($message) && !isset($_SESSION['kode_admin'])) {
    // Alamat admin harus sudah didefinisikan di koneksi.php atau di sini
    if (!isset($alamat_admin)) {
        $alamat_admin = '../../admin/'; // Sesuaikan jika path admin berbeda
    }
    echo '<script>
            alert("Sesi tidak valid atau telah berakhir. Harap masuk kembali!");
            window.location.replace("'.$alamat_admin.'keluar.php");
          </script>';
    exit();
}


// Fungsi untuk memetakan provider_type API ke nilai jenis DB
function map_provider_type_to_jenis($api_provider_type) {
    $api_provider_type_upper = strtoupper($api_provider_type);
    switch ($api_provider_type_upper) {
        case 'SL': return 1; // Slot
        case 'SB': return 2; // Sports
        case 'LC': return 3; // Live Casino
        case 'FH': return 4; // Fishing (Jika API menggunakan 'FH' atau kode lain untuk fishing)
        case 'ES': return 5; // E-Games / E-Sports
        case 'LK': return 6; // Lottery Keno / Togel
        // Tambahkan case lain jika ada
        default: return 0; // Jenis tidak diketahui atau umum
    }
}

// Fungsi untuk membuat slug sederhana
function create_slug($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? 'n-a' : $text;
}


// Proses jika tombol "Update Semua Provider" diklik
if (empty($message) && isset($_POST['update_all_providers'])) {
    $message .= '<div class="alert alert-info">Memulai proses pembaruan semua provider dari API...</div>';
    
    $api_response = $WL->GetProviderList();

    if ($api_response && isset($api_response['status']) && $api_response['status'] === 'success' && isset($api_response['provider'])) {
        $provider_data_from_api = $api_response['provider'];

        if (empty($provider_data_from_api)) {
            $message .= '<div class="alert alert-info">Tidak ada provider ditemukan dari API.</div>';
        } else {
            $message .= '<div class="alert alert-success">Berhasil memuat ' . count($provider_data_from_api) . ' provider dari API. Memproses ke database...</div>';

            // Get max cuid untuk penomoran entri baru
            $max_cuid_result = $koneksi->query("SELECT MAX(cuid) as max_cuid FROM tb_providerbaru");
            $max_cuid_row = $max_cuid_result->fetch_assoc();
            $next_cuid = ($max_cuid_row && $max_cuid_row['max_cuid'] !== null) ? (int)$max_cuid_row['max_cuid'] + 1 : 1;

            $stmt_check = $koneksi->prepare("SELECT cuid FROM tb_providerbaru WHERE providerid = ?");
            $stmt_insert = $koneksi->prepare("INSERT INTO tb_providerbaru (cuid, providerid, providername, slug, type, status, providerimage, jenis, providerapi) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt_update = $koneksi->prepare("UPDATE tb_providerbaru SET providername = ?, slug = ?, type = ?, status = ?, providerimage = ?, jenis = ?, providerapi = ? WHERE providerid = ?");

            if ($stmt_check && $stmt_insert && $stmt_update) {
                 foreach ($provider_data_from_api as $provider_item) {
                    $providerid_api = $provider_item['provider_code'] ?? null;
                    $providername_api = $provider_item['provider_name'] ?? 'N/A';
                    $slug_val = create_slug($providername_api);
                    $type_api = $provider_item['provider_type'] ?? 'UNKNOWN';
                    $status_api = (string)($provider_item['provider_status'] ?? '0');
                    $providerimage_api = $provider_item['provider_image'] ?? '';
                    $jenis_val = map_provider_type_to_jenis($type_api);
                    $providerapi_val = $providerid_api; // providerapi diisi sama dengan providerid

                    if (empty($providerid_api)) {
                        $message .= '<div class="alert alert-warning">Ditemukan provider dengan provider_code kosong dari API, dilewati.</div>';
                        continue;
                    }

                    $stmt_check->bind_param("s", $providerid_api);
                    $stmt_check->execute();
                    $result_check = $stmt_check->get_result();
                    
                    if ($result_check->num_rows > 0) {
                        // Data sudah ada, lakukan UPDATE
                        $stmt_update->bind_param("sssssiss", $providername_api, $slug_val, $type_api, $status_api, $providerimage_api, $jenis_val, $providerapi_val, $providerid_api);
                        if ($stmt_update->execute()) {
                            if ($stmt_update->affected_rows > 0) {
                                $updated_count++;
                            }
                        } else {
                             $message .= '<div class="alert alert-danger">Gagal update provider ' . htmlspecialchars($providerid_api) . ': ' . $stmt_update->error . '</div>';
                        }
                    } else {
                        // Data belum ada, lakukan INSERT
                        $stmt_insert->bind_param("issssssis", $next_cuid, $providerid_api, $providername_api, $slug_val, $type_api, $status_api, $providerimage_api, $jenis_val, $providerapi_val);
                         if ($stmt_insert->execute()) {
                            $inserted_count++;
                            $next_cuid++; // Increment cuid untuk insert berikutnya
                        } else {
                             $message .= '<div class="alert alert-danger">Gagal insert provider ' . htmlspecialchars($providerid_api) . ': ' . $stmt_insert->error . '</div>';
                        }
                    }
                 }
                $stmt_check->close();
                $stmt_insert->close();
                $stmt_update->close();
                $message .= '<div class="alert alert-success">Proses database selesai. Data baru: ' . $inserted_count . ', Data update: ' . $updated_count . '.</div>';
            } else {
                 $message .= '<div class="alert alert-danger">Gagal menyiapkan statement database. Error: ' . $koneksi->error . '</div>';
            }
        }
    } elseif ($api_response && isset($api_response['status']) && $api_response['status'] !== 'success') {
        $message .= '<div class="alert alert-danger">Gagal mengambil data dari API. Pesan: ' . htmlspecialchars($api_response['msg'] ?? 'Tidak ada pesan dari API.') . '</div>';
    } else {
        $message .= '<div class="alert alert-danger">Gagal terhubung ke API provider atau respons tidak valid.</div>';
    }
}

// Selalu ambil data terbaru dari DB untuk ditampilkan (jika koneksi OK)
if (isset($koneksi) && ($koneksi instanceof mysqli)) {
    $result_select_db = $koneksi->query("SELECT cuid, providerid, providername, slug, type, status, providerimage, jenis, providerapi FROM tb_providerbaru ORDER BY providername ASC");
    if ($result_select_db) {
        while ($row = $result_select_db->fetch_assoc()) {
            $provider_data_to_display[] = $row;
        }
    } else {
        if(empty($message)) { // Hanya tampilkan error ini jika belum ada error koneksi sebelumnya
            $message .= '<div class="alert alert-danger">Gagal mengambil data provider dari database untuk ditampilkan: ' . $koneksi->error . '</div>';
        }
    }
}
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">Menu Utama /</span> Update Semua Provider
    </h4>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Update Otomatis Daftar Provider</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($message)) echo $message; ?>
                    
                    <?php if (isset($koneksi) && ($koneksi instanceof mysqli) && isset($WL) && ($WL instanceof zulhayker)): ?>
                    <form method="POST" action="">
                        <button type="submit" name="update_all_providers" class="btn btn-primary">
                            <i class="bx bx-refresh me-1"></i> Update Semua Provider dari API
                        </button>
                    </form>
                    <?php else: ?>
                    <p class="text-danger">Pengecekan awal sistem gagal, tombol update dinonaktifkan.</p>
                    <?php endif; ?>

                    <?php if (!empty($provider_data_to_display)): ?>
                        <h5 class="mt-4">Data Provider Tersimpan di Database:</h5>
                        <div class="table-responsive text-nowrap">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>CUID</th>
                                        <th>Provider ID</th>
                                        <th>Nama Provider</th>
                                        <th>Slug</th>
                                        <th>Tipe API</th>
                                        <th>Status API</th>
                                        <th>Gambar</th>
                                        <th>Jenis Lokal</th>
                                        <th>Provider API (Lokal)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($provider_data_to_display as $provider_row): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($provider_row['cuid'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($provider_row['providerid'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($provider_row['providername'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($provider_row['slug'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($provider_row['type'] ?? '') ?></td>
                                            <td>
                                                <?php 
                                                $status_text = 'Tidak Aktif';
                                                $status_class = 'danger';
                                                if (($provider_row['status'] ?? '0') == '1') {
                                                    $status_text = 'Aktif';
                                                    $status_class = 'success';
                                                }
                                                echo '<span class="badge bg-label-' . $status_class . '">' . $status_text . '</span>';
                                                ?>
                                            </td>
                                            <td>
                                                <?php if(!empty($provider_row['providerimage'])): ?>
                                                    <a href="<?= htmlspecialchars($provider_row['providerimage']) ?>" target="_blank">
                                                        <img src="<?= htmlspecialchars($provider_row['providerimage']) ?>" alt="<?= htmlspecialchars($provider_row['providername'] ?? '') ?>" style="width:100px; height:auto; max-height:40px; object-fit:contain;">
                                                    </a>
                                                <?php else: echo 'N/A'; endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($provider_row['jenis'] ?? '') ?> (<?= htmlspecialchars(map_provider_type_to_jenis_text($provider_row['jenis'] ?? 0)) ?>)</td>
                                            <td><?= htmlspecialchars($provider_row['providerapi'] ?? '') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php elseif (isset($koneksi) && ($koneksi instanceof mysqli)): ?>
                        <p class="mt-4">Tidak ada data provider ditemukan di database.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Fungsi tambahan untuk menampilkan teks jenis
function map_provider_type_to_jenis_text($jenis_id) {
    switch ((int)$jenis_id) {
        case 1: return 'Slot';
        case 2: return 'Sports';
        case 3: return 'Casino';
        case 4: return 'Fishing';
        case 5: return 'E-Games';
        case 6: return 'Togel/Lottery';
        default: return 'Tidak Diketahui';
    }
}

// Tutup koneksi database jika dibuka
// if (isset($koneksi) && ($koneksi instanceof mysqli)) {
//    $koneksi->close(); // Koneksi biasanya ditutup di akhir skrip utama atau di file footer jika ada.
// }
?>