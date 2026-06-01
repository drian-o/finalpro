<?php
session_start();
include_once '../koneksi.php'; // Pastikan $alamat_website tersedia dari sini

header('Content-Type: application/json');

// Pastikan admin sudah login
if (!isset($_SESSION['kode_admin'])) {
    echo json_encode(['success' => false, 'message' => 'Sesi tidak valid. Harap login kembali.']);
    exit();
}

$response = ['success' => false, 'message' => 'Terjadi kesalahan.'];

// Path ke direktori upload banner (relatif dari file ini untuk operasi file sistem)
$upload_dir_fs = "../uploads/livecasino_banners/"; // Untuk move_uploaded_file dan unlink
// Path folder dari root website (untuk membangun URL absolut)
$banner_folder_path_from_root = "uploads/livecasino_banners/";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['game_id']) && !empty($_POST['game_id']) && isset($_FILES['banner_file'])) {
        $game_id = mysqli_real_escape_string($koneksi, $_POST['game_id']);
        $file = $_FILES['banner_file'];

        // Validasi file
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $response['message'] = 'Error unggah file: ' . $file['error'];
            echo json_encode($response);
            exit;
        }

        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = mime_content_type($file['tmp_name']);
        if (!in_array($file_type, $allowed_types)) {
            $response['message'] = 'Tipe file tidak diizinkan. Hanya JPG, PNG, GIF, WEBP.';
            echo json_encode($response);
            exit;
        }

        $max_size = 5 * 1024 * 1024; // 5MB
        if ($file['size'] > $max_size) {
            $response['message'] = 'Ukuran file terlalu besar. Maksimal 5MB.';
            echo json_encode($response);
            exit;
        }

        if (!is_dir($upload_dir_fs)) {
            if (!mkdir($upload_dir_fs, 0775, true)) {
                 $response['message'] = 'Gagal membuat direktori upload.';
                 echo json_encode($response);
                 exit;
            }
        }
         if (!is_writable($upload_dir_fs)) {
            $response['message'] = 'Direktori upload tidak writable: ' . $upload_dir_fs;
            echo json_encode($response);
            exit;
        }

        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $new_filename_only = "banner_lc_" . $game_id . "_" . time() . "." . $file_extension;
        $upload_path_fs = $upload_dir_fs . $new_filename_only;

        // Hapus banner lama jika ada
        $sql_old_banner = "SELECT banner FROM gamelist_livecasino WHERE id = '$game_id'";
        $result_old_banner = mysqli_query($koneksi, $sql_old_banner);
        if ($result_old_banner && mysqli_num_rows($result_old_banner) > 0) {
            $old_banner_row = mysqli_fetch_assoc($result_old_banner);
            if (!empty($old_banner_row['banner'])) {
                // Ekstrak nama file dari URL yang mungkin tersimpan di DB
                $old_banner_filename = basename(parse_url($old_banner_row['banner'], PHP_URL_PATH));
                if (!empty($old_banner_filename) && file_exists($upload_dir_fs . $old_banner_filename)) {
                    unlink($upload_dir_fs . $old_banner_filename);
                }
            }
        }

        if (move_uploaded_file($file['tmp_name'], $upload_path_fs)) {
            // Bangun URL absolut lengkap untuk disimpan di database
            // Pastikan $alamat_website diakhiri dengan / dan $banner_folder_path_from_root tidak diawali /
            $base_website_url = rtrim($alamat_website, '/') . '/';
            $full_banner_url_for_db = $base_website_url . trim($banner_folder_path_from_root, '/') . '/' . $new_filename_only;

            $stmt = mysqli_prepare($koneksi, "UPDATE gamelist_livecasino SET banner = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "si", $full_banner_url_for_db, $game_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $response['success'] = true;
                $response['message'] = 'Banner berhasil diperbarui.';
                $response['newBannerFilename'] = $new_filename_only; // Kirim HANYA nama file ke JS
            } else {
                $response['message'] = 'Gagal memperbarui database: ' . mysqli_stmt_error($stmt);
                if (file_exists($upload_path_fs)) {
                    unlink($upload_path_fs);
                }
            }
            mysqli_stmt_close($stmt);
        } else {
            $response['message'] = 'Gagal memindahkan file yang diunggah.';
        }
    } else {
        $response['message'] = 'Data tidak lengkap atau file tidak terunggah.';
        if (!isset($_FILES['banner_file'])) {
             $response['message'] .= ' File banner tidak ditemukan.';
        }
         if (isset($_FILES['banner_file']) && $_FILES['banner_file']['error'] !== UPLOAD_ERR_OK) {
             $response['message'] .= ' Kode Error Unggah: ' . $_FILES['banner_file']['error'];
        }
    }
} else {
    $response['message'] = 'Metode request tidak valid.';
}

mysqli_close($koneksi);
echo json_encode($response);
?>
