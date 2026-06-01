<?php
// editgamelist.php

// Pastikan session sudah dimulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once '../koneksi.php'; // Pastikan path ini benar

if (!isset($alamat_admin)) {
    $alamat_admin = '/admin/';
}

if (!isset($_SESSION['kode_admin'])) {
    echo '<script>
            alert("Sesi Anda telah berakhir atau tidak valid. Harap masuk kembali!");
            window.location.replace("'.$alamat_admin.'keluar.php");
          </script>';
    exit();
}

$db_connected = false;
$db_connection_var = null;
if (isset($koneksi) && $koneksi instanceof mysqli) {
    $db_connected = true;
    $db_connection_var = $koneksi;
} elseif (isset($koneksi_manual) && $koneksi_manual instanceof mysqli) {
    $db_connected = true;
    $db_connection_var = $koneksi_manual;
}

$game_data = null;
$message = '';
$error_message = '';
$game_id = null;
$current_game_name_for_title = '';
$base_upload_path = 'uploads/'; // Relatif terhadap root atau lokasi skrip, sesuaikan
$base_url_path = ''; // Kosongkan jika path sudah relatif dari root web, atau isi dengan URL dasar jika perlu

// 1. Ambil game_id dari parameter GET (disediakan oleh URL rewriting)
if (isset($_GET['game_id']) && is_numeric($_GET['game_id'])) {
    $game_id = intval($_GET['game_id']);
} else {
    $error_message = "ID Game tidak valid atau tidak ditemukan dari URL.";
}

// Penting: Ambil data game (termasuk provider) SEBELUM memproses POST,
// karena nama folder provider dibutuhkan saat upload.
if ($game_id && $db_connected) {
    $sql_select_initial = "SELECT id, game_code, game_name, banner, status, provider, sort, lang, datatype, frbavailable, provideragent 
                           FROM gamelist_slot WHERE id = ?";
    $stmt_select_initial = $db_connection_var->prepare($sql_select_initial);
    if ($stmt_select_initial) {
        $stmt_select_initial->bind_param("i", $game_id);
        $stmt_select_initial->execute();
        $result_initial = $stmt_select_initial->get_result();
        if ($result_initial->num_rows === 1) {
            $game_data = $result_initial->fetch_assoc();
            $current_game_name_for_title = $game_data['game_name'];
        } else {
            if (empty($error_message)) { // Hanya set error jika belum ada dari validasi ID awal
                 $error_message = "Data game dengan ID " . htmlspecialchars($game_id) . " tidak ditemukan.";
            }
        }
        $stmt_select_initial->close();
    } else {
        if (empty($error_message)) {
            $error_message = "Gagal menyiapkan statement select data game awal: " . $db_connection_var->error;
        }
    }
}


// 2. Proses form jika metode POST
if ($_SERVER["REQUEST_METHOD"] == "POST" && $db_connected && $game_id !== null && $game_data) { // Pastikan game_data ada
    if (isset($_POST['game_id']) && is_numeric($_POST['game_id']) && intval($_POST['game_id']) === $game_id) {
        $game_name = $_POST['game_name'] ?? '';
        $status = $_POST['status'] ?? '0';
        $sort = $_POST['sort'] ?? '0';
        $frbavailable = $_POST['frbavailable'] ?? '0';
        $provideragent = $_POST['provideragent'] ?? '';
        $existing_banner_path = $_POST['existing_banner_path'] ?? ($game_data['banner'] ?? ''); // Ambil dari POST atau data game awal
        $new_banner_path_for_db = $existing_banner_path; // Default ke banner yang ada

        // Validasi dasar
        if (empty($game_name)) {
            $error_message = "Nama Game tidak boleh kosong.";
        } elseif (!is_numeric($sort)) {
             $error_message = "Urutan (Sort) harus berupa angka.";
        } // ... (validasi lain) ...
        else {
            // --- Handle File Upload Banner ---
            if (isset($_FILES['banner_upload']) && $_FILES['banner_upload']['error'] == UPLOAD_ERR_OK) {
                $provider_folder_name = preg_replace('/[^a-zA-Z0-9_-]/', '_', strtolower($game_data['provider'])); // Sanitasi nama folder
                if (empty($provider_folder_name)) {
                    $error_message = "Nama provider tidak valid untuk membuat folder upload.";
                } else {
                    $target_dir_provider = rtrim($base_upload_path, '/') . '/' . $provider_folder_name . '/';

                    if (!is_dir($target_dir_provider)) {
                        if (!mkdir($target_dir_provider, 0755, true)) { // 0755 adalah izin umum, sesuaikan jika perlu
                            $error_message = "Gagal membuat direktori upload: " . htmlspecialchars($target_dir_provider);
                        }
                    }

                    if (empty($error_message) && is_writable($target_dir_provider)) {
                        $file_info = pathinfo($_FILES['banner_upload']['name']);
                        $file_extension = strtolower($file_info['extension']);
                        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                        $max_file_size = 5 * 1024 * 1024; // 5MB

                        if (!in_array($file_extension, $allowed_extensions)) {
                            $error_message = "Ekstensi file tidak diizinkan. Hanya JPG, JPEG, PNG, GIF, WEBP.";
                        } elseif ($_FILES['banner_upload']['size'] > $max_file_size) {
                            $error_message = "Ukuran file terlalu besar. Maksimal 5MB.";
                        } else {
                            // Buat nama file unik untuk menghindari timpaan dan masalah karakter
                            $safe_filename = preg_replace('/[^a-zA-Z0-9_.-]/', '_', $file_info['filename']);
                            $new_filename = $safe_filename . '_' . time() . '.' . $file_extension;
                            $target_file_path = $target_dir_provider . $new_filename;

                            if (move_uploaded_file($_FILES['banner_upload']['tmp_name'], $target_file_path)) {
                                $message .= " Banner baru berhasil diupload.";
                                $new_banner_path_for_db = $target_file_path; // Simpan path relatif ini ke DB

                                // Hapus banner lama jika ada dan merupakan file lokal
                                if (!empty($existing_banner_path) && strpos($existing_banner_path, '://') === false && file_exists($existing_banner_path) && $existing_banner_path != $new_banner_path_for_db) {
                                    if (unlink($existing_banner_path)) {
                                        $message .= " Banner lama berhasil dihapus.";
                                    } else {
                                        $error_message .= " Gagal menghapus banner lama.";
                                    }
                                }
                            } else {
                                $error_message = "Gagal memindahkan file yang diupload.";
                            }
                        }
                    } elseif (empty($error_message)) {
                         $error_message = "Direktori upload tidak dapat ditulis: " . htmlspecialchars($target_dir_provider);
                    }
                }
            } elseif (isset($_FILES['banner_upload']) && $_FILES['banner_upload']['error'] != UPLOAD_ERR_NO_FILE) {
                // Ada error lain saat upload selain tidak ada file yang dipilih
                $upload_errors = [
                    UPLOAD_ERR_INI_SIZE   => "File melebihi batas upload_max_filesize di php.ini.",
                    UPLOAD_ERR_FORM_SIZE  => "File melebihi batas MAX_FILE_SIZE di form HTML.",
                    UPLOAD_ERR_PARTIAL    => "File hanya terupload sebagian.",
                    UPLOAD_ERR_CANT_WRITE => "Gagal menulis file ke disk.",
                    UPLOAD_ERR_EXTENSION  => "Ekstensi PHP menghentikan upload file.",
                ];
                $error_code = $_FILES['banner_upload']['error'];
                $error_message = $upload_errors[$error_code] ?? "Terjadi error tidak diketahui saat upload banner.";
            }
            // --- Akhir Handle File Upload Banner ---

            if (empty($error_message)) { // Lanjutkan update DB jika tidak ada error dari validasi atau upload
                $sql_update = "UPDATE gamelist_slot SET 
                                game_name = ?, 
                                banner = ?, 
                                status = ?, 
                                sort = ?, 
                                frbavailable = ?,
                                provideragent = ?
                               WHERE id = ?";
                $stmt_update = $db_connection_var->prepare($sql_update);
                if ($stmt_update) {
                    $stmt_update->bind_param("ssssisi", $game_name, $new_banner_path_for_db, $status, $sort, $frbavailable, $provideragent, $game_id);
                    if ($stmt_update->execute()) {
                        if ($stmt_update->affected_rows > 0) {
                            $message = "Data game berhasil diperbarui!" . $message; // Gabungkan dengan pesan upload
                             // Ambil ulang data game untuk menampilkan banner yang baru diupdate
                            $stmt_select_initial->execute();
                            $result_initial = $stmt_select_initial->get_result();
                            if ($result_initial->num_rows === 1) $game_data = $result_initial->fetch_assoc();

                        } else {
                            $message = "Tidak ada perubahan data." . $message;
                        }
                    } else {
                        $error_message = "Gagal memperbarui data game ke database: " . $stmt_update->error;
                    }
                    $stmt_update->close();
                } else {
                    $error_message = "Gagal menyiapkan statement update database: " . $db_connection_var->error;
                }
            }
        }
    } else {
        $error_message = "ID Game untuk pembaruan tidak valid atau tidak cocok.";
    }
}


// Ambil ulang data game jika belum ada (misalnya jika akses langsung tanpa POST dan game_id valid tapi belum di-fetch)
// Atau jika ada $message dari POST sukses, data sudah di-refresh.
if (!$game_data && $game_id && $db_connected && empty($error_message) && $_SERVER["REQUEST_METHOD"] !== "POST") {
    $sql_select_refresh = "SELECT id, game_code, game_name, banner, status, provider, sort, lang, datatype, frbavailable, provideragent 
                           FROM gamelist_slot WHERE id = ?";
    $stmt_select_refresh = $db_connection_var->prepare($sql_select_refresh);
    if ($stmt_select_refresh) {
        $stmt_select_refresh->bind_param("i", $game_id);
        $stmt_select_refresh->execute();
        $result_refresh = $stmt_select_refresh->get_result();
        if ($result_refresh->num_rows === 1) {
            $game_data = $result_refresh->fetch_assoc();
            $current_game_name_for_title = $game_data['game_name'];
        }
        // Error "tidak ditemukan" sudah ditangani oleh pengambilan data awal jika $game_id valid
        $stmt_select_refresh->close();
    }
}


?>

<div class="container-xxl flex-grow-1 container-p-y">
  <h4 class="py-3 mb-4">
    <span class="text-muted fw-light">Manajemen Game /</span> Edit Game
    <?php if (!empty($current_game_name_for_title)): ?>
         / <?php echo htmlspecialchars($current_game_name_for_title); ?>
    <?php elseif ($game_id && !$game_data && empty($error_message)): ?>
         / ID: <?php echo htmlspecialchars($game_id); ?> (Data Tidak Ditemukan)
    <?php elseif ($game_id): ?>
         / ID: <?php echo htmlspecialchars($game_id); ?>
    <?php endif; ?>
  </h4>

    <?php if ($message): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo nl2br(htmlspecialchars($message)); // nl2br untuk pesan multiline dari upload ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    <?php if ($error_message): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo nl2br(htmlspecialchars($error_message)); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <?php if ($game_data && $db_connected): ?>
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Formulir Edit Game: <?php echo htmlspecialchars($game_data['game_name'] ?? ''); ?></h5>
            <small class="text-muted">ID Game: <?php echo htmlspecialchars($game_data['id'] ?? ''); ?></small>
        </div>
        <div class="card-body">
            <form method="POST" action="<?php echo htmlspecialchars($base_url_path . 'editgamelist/edit_game/' . $game_id); ?>" enctype="multipart/form-data">
                <input type="hidden" name="game_id" value="<?php echo htmlspecialchars($game_data['id'] ?? $game_id); ?>">
                <input type="hidden" name="existing_banner_path" value="<?php echo htmlspecialchars($game_data['banner'] ?? ''); ?>">

                <div class="mb-3 row">
                    <label for="game_code" class="col-sm-3 col-form-label">Game Code:</label>
                    <div class="col-sm-9">
                        <input type="text" readonly class="form-control-plaintext" id="game_code" value="<?php echo htmlspecialchars($game_data['game_code'] ?? ''); ?>">
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="provider" class="col-sm-3 col-form-label">Provider:</label>
                    <div class="col-sm-9">
                        <input type="text" readonly class="form-control-plaintext" id="provider" value="<?php echo htmlspecialchars($game_data['provider'] ?? ''); ?>">
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="game_name" class="col-sm-3 col-form-label">Nama Game:</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="game_name" name="game_name" value="<?php echo htmlspecialchars(isset($_POST['game_name']) ? $_POST['game_name'] : ($game_data['game_name'] ?? '')); ?>" required>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="banner_upload" class="col-sm-3 col-form-label">Upload Banner Baru:</label>
                    <div class="col-sm-9">
                        <input type="file" class="form-control" id="banner_upload" name="banner_upload" accept="image/jpeg,image/png,image/gif,image/webp">
                        <small class="form-text text-muted">Kosongkan jika tidak ingin mengubah banner. Maks 5MB (JPG, PNG, GIF, WEBP).</small>
                        <?php
                        $current_banner_display = $game_data['banner'] ?? '';
                        if (!empty($current_banner_display)):
                            // Cek apakah ini path lokal atau URL eksternal
                            if (strpos($current_banner_display, '://') === false) { // Anggap path lokal jika tidak ada '://'
                                $banner_src = htmlspecialchars($base_url_path . ltrim($current_banner_display, '/'));
                            } else { // Ini adalah URL eksternal
                                $banner_src = htmlspecialchars($current_banner_display);
                            }
                        ?>
                            <div class="mt-2">
                                <p class="mb-1">Banner Saat Ini:</p>
                                <img src="<?php echo $banner_src; ?>" alt="Banner Saat Ini" class="img-thumbnail" style="max-width: 150px; height: auto; border: 1px solid #ddd; padding: 2px;">
                            </div>
                        <?php endif; ?>
                    </div>
                </div>


                <div class="mb-3 row">
                    <label for="status" class="col-sm-3 col-form-label">Status:</label>
                    <div class="col-sm-9">
                        <select class="form-select" id="status" name="status">
                            <option value="1" <?php echo ((isset($_POST['status']) ? $_POST['status'] : ($game_data['status'] ?? '')) == '1') ? 'selected' : ''; ?>>Aktif (1)</option>
                            <option value="0" <?php echo ((isset($_POST['status']) ? $_POST['status'] : ($game_data['status'] ?? '')) == '0') ? 'selected' : ''; ?>>Tidak Aktif (0)</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="sort" class="col-sm-3 col-form-label">Urutan (Sort):</label>
                    <div class="col-sm-9">
                        <input type="number" class="form-control" id="sort" name="sort" value="<?php echo htmlspecialchars(isset($_POST['sort']) ? $_POST['sort'] : ($game_data['sort'] ?? '0')); ?>" required>
                    </div>
                </div>
                
                <div class="mb-3 row">
                    <label for="frbavailable" class="col-sm-3 col-form-label">FRB Available:</label>
                    <div class="col-sm-9">
                        <select class="form-select" id="frbavailable" name="frbavailable">
                            <option value="1" <?php echo ((isset($_POST['frbavailable']) ? $_POST['frbavailable'] : ($game_data['frbavailable'] ?? '')) == '1') ? 'selected' : ''; ?>>Yes (1)</option>
                            <option value="0" <?php echo ((isset($_POST['frbavailable']) ? $_POST['frbavailable'] : ($game_data['frbavailable'] ?? '')) == '0') ? 'selected' : ''; ?>>No (0)</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="provideragent" class="col-sm-3 col-form-label">Provider Agent:</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="provideragent" name="provideragent" value="<?php echo htmlspecialchars(isset($_POST['provideragent']) ? $_POST['provideragent'] : ($game_data['provideragent'] ?? '')); ?>">
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="lang" class="col-sm-3 col-form-label">Bahasa (Lang):</label>
                    <div class="col-sm-9"><input type="text" readonly class="form-control-plaintext" id="lang" value="<?php echo htmlspecialchars($game_data['lang'] ?? ''); ?>"></div>
                </div>
                <div class="mb-3 row">
                    <label for="datatype" class="col-sm-3 col-form-label">Tipe Data:</label>
                    <div class="col-sm-9"><input type="text" readonly class="form-control-plaintext" id="datatype" value="<?php echo htmlspecialchars($game_data['datatype'] ?? ''); ?>"></div>
                </div>

                <hr>
                <div class="row">
                    <div class="col-sm-9 offset-sm-3">
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        <a href="<?php echo htmlspecialchars($base_url_path . 'editgame.php'); // Kembali ke halaman daftar (sebelumnya tampil_game_provider.php) ?>" class="btn btn-secondary">Kembali ke Daftar Game</a>
                    </div>
                </div>
            </form>
        </div>
        <?php elseif (!$game_data && !$error_message && $db_connected && $game_id !== null): ?>
            <div class="card-body">
                <p class="text-warning">Data game dengan ID yang diminta tidak ditemukan.</p>
                 <a href="<?php echo htmlspecialchars($base_url_path . 'editgame.php'); ?>" class="btn btn-secondary mt-2">Kembali ke Daftar Game</a>
            </div>
        <?php elseif (!$db_connected && empty($error_message)): ?>
            <div class="card-body">
                <p class="text-danger">Koneksi ke database gagal. Tidak dapat menampilkan formulir edit.</p>
            </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php
// Tutup koneksi jika tidak ditangani oleh file footer template
// if ($db_connected && isset($db_connection_var) && $db_connection_var instanceof mysqli) {
//    $db_connection_var->close();
// }
?>