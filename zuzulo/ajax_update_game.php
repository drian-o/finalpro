<?php
// ajax_update_game.php
header('Content-Type: application/json');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include_once '../koneksi.php'; // Sesuaikan path

$response = ['status' => 'error', 'message' => 'Terjadi kesalahan yang tidak diketahui.'];
$base_upload_path = '../uploads/'; // Harus sama dengan yang di editgamelist.php sebelumnya

if (!isset($_SESSION['kode_admin'])) {
    $response['message'] = 'Akses ditolak. Sesi tidak valid.';
    echo json_encode($response);
    exit();
}

$db_connected = false;
$db_connection_var = null;
if (isset($koneksi) && $koneksi instanceof mysqli) {
    $db_connected = true;
    $db_connection_var = $koneksi;
} // ... (fallback koneksi lain jika ada) ...

if (!$db_connected) {
    $response['message'] = 'Kesalahan koneksi database.';
    echo json_encode($response);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['game_id']) && is_numeric($_POST['game_id'])) {
        $game_id = intval($_POST['game_id']);

        // Ambil data provider untuk nama folder dari DB berdasarkan game_id
        $provider_folder_name = '';
        $stmt_get_provider = $db_connection_var->prepare("SELECT provider FROM gamelist_slot WHERE id = ?");
        if ($stmt_get_provider) {
            $stmt_get_provider->bind_param("i", $game_id);
            $stmt_get_provider->execute();
            $result_provider = $stmt_get_provider->get_result();
            if ($row_provider = $result_provider->fetch_assoc()) {
                $provider_folder_name = preg_replace('/[^a-zA-Z0-9_-]/', '_', strtolower($row_provider['provider']));
            }
            $stmt_get_provider->close();
        }

        if (empty($provider_folder_name)) {
            $response['message'] = 'Gagal mendapatkan informasi provider untuk game ini.';
            $response['errors'] = 'Provider tidak ditemukan untuk ID game ' . $game_id;
            echo json_encode($response);
            exit();
        }

        $game_name = $_POST['game_name'] ?? '';
        $status = $_POST['status'] ?? '0';
        $sort = $_POST['sort'] ?? '0';
        $frbavailable = $_POST['frbavailable'] ?? '0';
        $provideragent = $_POST['provideragent'] ?? '';
        $existing_banner_path = $_POST['existing_banner_path'] ?? '';
        $new_banner_path_for_db = $existing_banner_path;
        $upload_message = '';
        $upload_error = '';


        // --- Handle File Upload Banner ---
        if (isset($_FILES['banner_upload']) && $_FILES['banner_upload']['error'] == UPLOAD_ERR_OK) {
            $target_dir_provider = rtrim($base_upload_path, '/') . '/' . $provider_folder_name . '/';
            if (!is_dir($target_dir_provider)) {
                if (!mkdir($target_dir_provider, 0755, true)) {
                    $upload_error = "Gagal membuat direktori upload: " . htmlspecialchars($target_dir_provider);
                }
            }

            if (empty($upload_error) && is_writable($target_dir_provider)) {
                $file_info = pathinfo($_FILES['banner_upload']['name']);
                $file_extension = strtolower($file_info['extension']);
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $max_file_size = 5 * 1024 * 1024; // 5MB

                if (!in_array($file_extension, $allowed_extensions)) {
                    $upload_error = "Ekstensi file tidak diizinkan.";
                } elseif ($_FILES['banner_upload']['size'] > $max_file_size) {
                    $upload_error = "Ukuran file terlalu besar (Maks 5MB).";
                } else {
                    $safe_filename = preg_replace('/[^a-zA-Z0-9_.-]/', '_', $file_info['filename']);
                    $new_filename = $safe_filename . '_' . time() . '.' . $file_extension;
                    $target_file_path = $target_dir_provider . $new_filename;

                    if (move_uploaded_file($_FILES['banner_upload']['tmp_name'], $target_file_path)) {
                        $upload_message = "Banner baru berhasil diupload.";
                        $new_banner_path_for_db = $target_file_path;
                        if (!empty($existing_banner_path) && strpos($existing_banner_path, '://') === false && file_exists($existing_banner_path) && $existing_banner_path != $new_banner_path_for_db) {
                            unlink($existing_banner_path); // Abaikan error jika gagal hapus file lama
                        }
                    } else {
                        $upload_error = "Gagal memindahkan file yang diupload.";
                    }
                }
            } elseif (empty($upload_error)) {
                $upload_error = "Direktori upload tidak dapat ditulis.";
            }
        } elseif (isset($_FILES['banner_upload']) && $_FILES['banner_upload']['error'] != UPLOAD_ERR_NO_FILE) {
            $upload_error = "Terjadi error saat upload banner (Code: ".$_FILES['banner_upload']['error'].").";
        }
        // --- Akhir Handle File Upload Banner ---

        if (!empty($upload_error)) {
            $response['message'] = 'Kesalahan upload banner.';
            $response['errors'] = $upload_error;
        } else {
            // Validasi server-side lainnya
            if (empty($game_name)) {
                $response['message'] = 'Validasi gagal.';
                $response['errors'] = ['game_name' => 'Nama Game tidak boleh kosong.'];
            } else {
                $sql_update = "UPDATE gamelist_slot SET 
                                game_name = ?, banner = ?, status = ?, sort = ?, 
                                frbavailable = ?, provideragent = ?
                               WHERE id = ?";
                $stmt_update = $db_connection_var->prepare($sql_update);
                if ($stmt_update) {
                    $stmt_update->bind_param("ssssisi", $game_name, $new_banner_path_for_db, $status, $sort, $frbavailable, $provideragent, $game_id);
                    if ($stmt_update->execute()) {
                        if ($stmt_update->affected_rows > 0 || empty($upload_error) && !empty($upload_message) || ($upload_error == '' && $_FILES['banner_upload']['error'] == UPLOAD_ERR_NO_FILE && $stmt_update->affected_rows >= 0) ) { // Sukses jika ada baris terupdate ATAU hanya banner diupdate ATAU tidak ada file banner baru & tidak ada error
                            $response['status'] = 'success';
                            $response['message'] = 'Data game berhasil diperbarui. ' . $upload_message;
                            // Kirim juga path banner baru jika berubah
                            if ($new_banner_path_for_db !== $existing_banner_path || !empty($upload_message)){
                                $response['new_banner_path'] = $new_banner_path_for_db;
                            }

                        } else {
                            $response['message'] = 'Tidak ada perubahan data atau update gagal. ' . $upload_message;
                             if ($stmt_update->error) $response['errors'] = $stmt_update->error;
                        }
                    } else {
                        $response['message'] = 'Gagal memperbarui data game ke DB.';
                        $response['errors'] = $stmt_update->error;
                    }
                    $stmt_update->close();
                } else {
                    $response['message'] = 'Gagal menyiapkan statement update DB.';
                    $response['errors'] = $db_connection_var->error;
                }
            }
        }
    } else {
        $response['message'] = 'ID Game tidak valid atau tidak dikirimkan.';
    }
} else {
    $response['message'] = 'Metode request tidak diizinkan.';
}

if ($db_connected && isset($db_connection_var)) {
    $db_connection_var->close();
}
echo json_encode($response);
exit();
?>