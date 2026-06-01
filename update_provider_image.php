<?php
// Pastikan output buffering aktif di awal
ob_start();

// Sertakan koneksi database
// Asumsi file ini berada di root proyek, jadi koneksi.php ada di direktori yang sama atau di atasnya
require_once __DIR__ . '/koneksi.php'; 

// --- KONFIGURASI KEAMANAN SEDERHANA ---
// UBAH INI DENGAN KUNCI RAHASIA YANG KUAT DAN SULIT DITEBAK!
// Contoh: $allowed_secret_key = 'your_super_secret_update_key_12345';
$allowed_secret_key = 'hanya_untuk_update_gambar_2025'; 
$input_secret_key = isset($_GET['key']) ? $_GET['key'] : '';

// Periksa secret key untuk otorisasi dasar
if ($input_secret_key !== $allowed_secret_key) {
    // Jika kunci tidak cocok, tampilkan pesan error dan hentikan eksekusi
    header('HTTP/1.1 401 Unauthorized');
    echo '<h1>401 Unauthorized</h1>';
    echo '<p>Akses ditolak. Kunci keamanan tidak valid.</p>';
    exit();
}
// --- AKHIR KONFIGURASI KEAMANAN SEDERHANA ---

$message = ''; // Untuk pesan sukses/error
$upload_dir = __DIR__ . '/upload/provider/'; // Direktori upload relatif dari lokasi file ini

// Pastikan direktori upload ada
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true); // Buat direktori jika tidak ada, dengan izin 0755
}

// Tangani proses upload gambar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_image') {
    $provider_id = isset($_POST['provider_id']) ? (int)$_POST['provider_id'] : 0;
    $provider_code = isset($_POST['provider_code']) ? mysqli_real_escape_string($koneksi, $_POST['provider_code']) : '';
    $provider_name_clean = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($_POST['provider_name'])); // Bersihkan nama provider
    
    if ($provider_id > 0 && !empty($provider_code) && !empty($provider_name_clean) && isset($_FILES['provider_image']) && $_FILES['provider_image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['provider_image']['tmp_name'];
        $file_name = $_FILES['provider_image']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($file_ext, $allowed_ext)) {
            // Buat nama file unik berdasarkan provider_code dan provider_name
            $new_file_name = 'provider_' . $provider_code . '_' . $provider_name_clean . '.' . $file_ext;
            $target_file = $upload_dir . $new_file_name;
            // Path yang disimpan di DB adalah relatif dari ROOT WEBSITE
            // Asumsi folder 'upload' berada di root website
            $relative_path_for_db = 'upload/provider/' . $new_file_name; 

            // Hapus gambar lama jika ada
            $query_old_image = mysqli_query($koneksi, "SELECT provider_image FROM srg_provider WHERE id = {$provider_id}");
            if ($query_old_image && mysqli_num_rows($query_old_image) > 0) {
                $old_image_row = mysqli_fetch_assoc($query_old_image);
                $old_image_path = $old_image_row['provider_image'];
                if (!empty($old_image_path) && file_exists(__DIR__ . '/../' . $old_image_path) && $old_image_path !== $relative_path_for_db) {
                    unlink(__DIR__ . '/../' . $old_image_path); // Hapus file lama
                }
            }

            if (move_uploaded_file($file_tmp, $target_file)) {
                // Update database
                $query_update = "UPDATE srg_provider SET provider_image = '{$relative_path_for_db}' WHERE id = {$provider_id}";
                if (mysqli_query($koneksi, $query_update)) {
                    $message = "<div class='alert alert-success'>Gambar provider " . htmlspecialchars($_POST['provider_name']) . " berhasil diupload dan diperbarui.</div>";
                } else {
                    $message = "<div class='alert alert-danger'>Gagal memperbarui database: " . mysqli_error($koneksi) . "</div>";
                    unlink($target_file); // Hapus file yang diupload jika update DB gagal
                }
            } else {
                $message = "<div class='alert alert-danger'>Gagal mengupload file gambar.</div>";
            }
        } else {
            $message = "<div class='alert alert-danger'>Ekstensi file tidak diizinkan. Hanya JPG, JPEG, PNG, GIF, WEBP yang diperbolehkan.</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>Data tidak lengkap atau tidak ada file gambar yang diupload.</div>";
    }
}

// Ambil daftar provider dari database
$query_providers = "SELECT id, provider_code, provider_name, provider_type, provider_image, provider_status FROM srg_provider ORDER BY provider_name ASC";
$result_providers = mysqli_query($koneksi, $query_providers);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Gambar Provider</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: sans-serif; background-color: #f4f4f4; padding-top: 20px; }
        .container { max-width: 960px; margin: auto; background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .provider-card { border: 1px solid #ddd; border-radius: 8px; margin-bottom: 20px; overflow: hidden; }
        .provider-card img { max-width: 100%; height: auto; display: block; margin-bottom: 10px; border-bottom: 1px solid #eee; }
        .provider-card .card-body { padding: 15px; }
        .provider-card h5 { margin-bottom: 10px; font-size: 1.2em; }
        .form-group { margin-bottom: 15px; }
        .current-image-preview { max-width: 150px; height: auto; margin-top: 10px; border: 1px solid #eee; }
    </style>
</head>
<body>

<div class="container">
    <h2 class="mb-4">Update Gambar Provider SRG</h2>
    <p class="alert alert-warning">
        <strong>Peringatan Keamanan:</strong> Halaman ini dilindungi dengan kunci URL sederhana. 
        Pastikan Anda mengganti `'hanya_untuk_update_gambar_2025'` dengan kunci rahasia yang kuat. 
        Jangan pernah membagikan kunci ini. Untuk keamanan lebih lanjut, batasi akses berdasarkan IP.
    </p>

    <?php if (!empty($message)): ?>
        <?php echo $message; ?>
    <?php endif; ?>

    <div class="row">
        <?php
        if ($result_providers && mysqli_num_rows($result_providers) > 0) {
            while ($provider = mysqli_fetch_assoc($result_providers)) {
        ?>
                <div class="col-md-4 col-sm-6">
                    <div class="provider-card">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($provider['provider_name']); ?></h5>
                            <p class="card-text small">
                                Kode: <?php echo htmlspecialchars($provider['provider_code']); ?><br>
                                Tipe: <?php echo htmlspecialchars($provider['provider_type']); ?><br>
                                Status: <?php echo htmlspecialchars($provider['provider_status']); ?>
                            </p>
                            <?php 
                            $image_src = '';
                            if (!empty($provider['provider_image'])) {
                                // Path gambar dari DB disimpan sebagai 'upload/provider/...'
                                // Kita perlu menambahkan '../' untuk path relatif dari file ini ke root website
                                $image_src = '../' . htmlspecialchars($provider['provider_image']); 
                                if (!file_exists($image_src)) { // Cek apakah file fisik ada
                                    $image_src = 'https://via.placeholder.com/150?text=Gambar+Tidak+Ditemukan'; // Placeholder jika file tidak ada
                                }
                            } else {
                                $image_src = 'https://via.placeholder.com/150?text=Tidak+Ada+Gambar';
                            }
                            ?>
                            <img src="<?php echo $image_src; ?>" alt="<?php echo htmlspecialchars($provider['provider_name']); ?>" class="current-image-preview">

                            <form action="" method="POST" enctype="multipart/form-data" class="mt-3">
                                <input type="hidden" name="action" value="upload_image">
                                <input type="hidden" name="provider_id" value="<?php echo $provider['id']; ?>">
                                <input type="hidden" name="provider_code" value="<?php echo htmlspecialchars($provider['provider_code']); ?>">
                                <input type="hidden" name="provider_name" value="<?php echo htmlspecialchars($provider['provider_name']); ?>">
                                
                                <div class="form-group">
                                    <label for="image_<?php echo $provider['id']; ?>" class="form-label">Pilih Gambar Baru (JPG, PNG, GIF, WEBP):</label>
                                    <input type="file" class="form-control" id="image_<?php echo $provider['id']; ?>" name="provider_image" accept="image/jpeg, image/png, image/gif, image/webp">
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm">Upload/Update Gambar</button>
                            </form>
                        </div>
                    </div>
                </div>
        <?php
            }
        } else {
            echo '<div class="col-12"><p class="text-center">Tidak ada provider ditemukan.</p></div>';
        }
        ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
// Tutup koneksi database
mysqli_close($koneksi);
ob_end_flush(); // Akhiri output buffering
?>