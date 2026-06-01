<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Sesuaikan path jika perlu
include_once '../../koneksi.php'; 
include_once '../../classes/jp88.php';

// Keamanan: Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['kode_admin'])) {
    http_response_code(403);
    echo '<div class="alert alert-danger">Akses ditolak. Sesi tidak valid.</div>';
    exit();
}

// Mengatur agar script tidak timeout
@set_time_limit(0);

// Variabel untuk menampung seluruh output log HTML
$html_log = '';

// Fungsi untuk menambah pesan ke variabel log
function logMessage($message, $type = 'info') {
    global $html_log;
    $color = '#f8f9fa'; // Default: putih
    if ($type == 'success') {
        $color = '#28a745'; // Hijau
    } elseif ($type == 'error') {
        $color = '#dc3545'; // Merah
    } elseif ($type == 'process') {
        $color = '#ffc107'; // Kuning
    }
    $html_log .= '<p style="margin: 2px 0; color: ' . $color . '; word-break: break-all;">[' . date('H:i:s') . '] ' . htmlspecialchars($message) . '</p>';
}

// ===================================
// AWAL LOGIKA PROSES SINKRONISASI
// ===================================

$uploadDir = '../uploads/jp88/provider/';
$uploadUrl = $alamat_website . 'uploads/jp88/provider/';

logMessage('Memulai proses sinkronisasi...');

// 1. Membuat direktori jika belum ada
if (!is_dir($uploadDir)) {
    if (mkdir($uploadDir, 0755, true)) {
        logMessage("Direktori berhasil dibuat di: " . realpath($uploadDir), 'success');
    } else {
        logMessage("GAGAL membuat direktori di: " . realpath($uploadDir) . ". Harap periksa izin folder.", 'error');
        echo $html_log;
        exit();
    }
} else {
    logMessage("Direktori sudah ada: " . realpath($uploadDir));
}

// 2. Inisialisasi API
logMessage('Menginisialisasi kelas JP88...');
$JP = new jp88();
logMessage('Memanggil API: GetProviderList...', 'process');

// 3. Panggil API
$response = $JP->GetProviderList();

if (isset($response['status']) && $response['status'] == 1 && !empty($response['providers'])) {
    logMessage('Berhasil mendapatkan data dari API. Jumlah provider: ' . count($response['providers']), 'success');
    
    $providers = $response['providers'];
    $insertedCount = 0;
    $updatedCount = 0;
    $rekapData = ['inserted' => [], 'updated' => []];

    foreach ($providers as $provider) {
        $provider_code = $provider['provider_code'];
        $provider_name = $provider['provider_name'];
        $provider_type = $provider['provider_type'];
        $provider_status = $provider['provider_status'];
        $image_url_from_api = $provider['provider_image'];
        
        logMessage("--------------------------------------------------", 'process');
        logMessage("Memproses: " . $provider_name . " (" . $provider_code . ")");

        // 4. Proses unduh gambar
        $db_image_url = '';
        if(!empty($image_url_from_api)){
            $image_name = $provider_code . '.' . pathinfo(parse_url($image_url_from_api, PHP_URL_PATH), PATHINFO_EXTENSION);
            $local_image_path = $uploadDir . $image_name;
            $db_image_url = $uploadUrl . $image_name;
            $image_content = @file_get_contents($image_url_from_api);

            if ($image_content === false) {
                logMessage("GAGAL mengunduh gambar untuk " . $provider_name, 'error');
                $db_image_url = ''; // Kosongkan jika gagal diunduh
            } else {
                @file_put_contents($local_image_path, $image_content);
                logMessage("Gambar berhasil disimpan.", 'success');
            }
        }

        // 5. Proses Database (Insert/Update)
        $stmt_check = $koneksi->prepare("SELECT id FROM jp_providerlist WHERE provider_code = ?");
        $stmt_check->bind_param("s", $provider_code);
        $stmt_check->execute();
        $result = $stmt_check->get_result();

        if ($result->num_rows > 0) {
            // Data sudah ada -> UPDATE
            logMessage("Provider sudah ada. Memperbarui...", 'process');
            $stmt_update = $koneksi->prepare("UPDATE jp_providerlist SET provider_name = ?, provider_type = ?, provider_status = ?, provider_image = ? WHERE provider_code = ?");
            $stmt_update->bind_param("sssss", $provider_name, $provider_type, $provider_status, $db_image_url, $provider_code);
            $stmt_update->execute();
            $updatedCount++;
            $rekapData['updated'][] = $provider_name;
            $stmt_update->close();
        } else {
            // Data belum ada -> INSERT
            logMessage("Provider belum ada. Menambahkan...", 'process');
            $stmt_insert = $koneksi->prepare("INSERT INTO jp_providerlist (provider_name, provider_code, provider_type, provider_status, provider_image) VALUES (?, ?, ?, ?, ?)");
            $stmt_insert->bind_param("sssss", $provider_name, $provider_type, $provider_status, $db_image_url, $provider_code);
            $stmt_insert->execute();
            $insertedCount++;
            $rekapData['inserted'][] = $provider_name;
            $stmt_insert->close();
        }
        $stmt_check->close();
    }

    // 6. Buat Rekapitulasi
    logMessage("==================================================", 'info');
    logMessage("PROSES SINKRONISASI SELESAI", 'success');
    logMessage("==================================================", 'info');
    logMessage("Total Data Baru Dimasukkan: " . $insertedCount, 'info');
    logMessage("Total Data Diperbarui: " . $updatedCount, 'info');

} else {
    $errorMsg = isset($response['message']) ? $response['message'] : 'Respons tidak valid dari API.';
    logMessage("GAGAL mendapatkan data dari API: " . $errorMsg, 'error');
}

// 7. Tampilkan seluruh log yang sudah terkumpul
echo $html_log;

$koneksi->close();
?>