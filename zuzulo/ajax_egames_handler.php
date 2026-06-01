<?php
// ajax_egames_handler.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include_once '../koneksi.php';

header('Content-Type: application/json');
$response = ['status' => 'error', 'message' => 'Aksi tidak valid atau terjadi kesalahan.', 'games' => [], 'jenis_game_value' => ''];

if (!isset($_SESSION['kode_admin'])) {
    $response['message'] = 'Akses ditolak. Sesi tidak valid atau telah berakhir.';
    echo json_encode($response);
    exit();
}

$allowed_tables = [
    'gamelist_external', 'gamelist_action', 'gamelist_fishinghunter',
    'gamelist_future', 'gamelist_ot', 'gamelist_pokercard',
    'gamelist_sportsbook', 'gamelist_lotterykeno','gamelist_livecasino'
];

function sanitize_folder_name($name) {
    $name = strtolower($name);
    $name = preg_replace('/&/', 'and', $name);
    $name = preg_replace('/[^a-z0-9_ \-]/', '', $name);
    $name = preg_replace('/[\s\-]+/', '_', $name);
    $name = trim($name, '_');
    return empty($name) ? 'default' : $name;
}

/**
 * Fungsi baru untuk mengunduh gambar dari URL dan menyimpannya secara lokal.
 *
 * @param string $url URL gambar yang akan diunduh.
 * @param string $base_server_path Path absolut dasar di server (e.g., /var/www/html/uploads/egames).
 * @param string $sub_folder Nama sub-folder (e.g., 'action').
 * @param string $base_website_url URL dasar website untuk disimpan ke DB (e.g., https://domain.com).
 * @return string|false URL database ke file lokal jika berhasil, false jika gagal.
 */
function download_banner_from_url($url, $base_server_path, $sub_folder, $base_website_url) {
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return false;
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15); // Timeout 15 detik
    curl_setopt($ch, CURLOPT_FAILONERROR, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Diperlukan untuk beberapa server dengan sertifikat self-signed
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $imageData = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($imageData === false || $httpCode >= 400) {
        return false; // Gagal mengambil data gambar
    }

    $imageInfo = @getimagesizefromstring($imageData);
    if ($imageInfo === false) {
        return false; // Data yang diunduh bukan gambar yang valid
    }

    $mime_to_ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
    $extension = $mime_to_ext[$imageInfo['mime']] ?? null;
    if ($extension === null) {
        return false; // Format gambar tidak didukung
    }

    $target_dir = $base_server_path . '/' . $sub_folder;
    if (!is_dir($target_dir)) {
        if (!mkdir($target_dir, 0755, true)) {
            return false; // Gagal membuat direktori
        }
    }

    $new_file_name = uniqid('banner_dl_', true) . '.' . $extension;
    $target_file_path = $target_dir . '/' . $new_file_name;

    if (file_put_contents($target_file_path, $imageData)) {
        return rtrim($base_website_url, '/') . '/uploads/egames/' . $sub_folder . '/' . $new_file_name;
    }

    return false; // Gagal menyimpan file
}


if (isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action == 'fetch_games') {
        // ... (Kode untuk fetch_games tidak berubah dari sebelumnya) ...
        if (isset($_POST['table_name']) && in_array($_POST['table_name'], $allowed_tables)) {
            $table_name = $_POST['table_name'];
            if (!$koneksi) { $response['message'] = 'Koneksi database gagal.'; echo json_encode($response); exit(); }
            $jenis_game_prefix = 'gamelist_';
            $jenis_game_value = (strpos($table_name, $jenis_game_prefix) === 0) ? substr($table_name, strlen($jenis_game_prefix)) : $table_name;
            $response['jenis_game_value'] = $jenis_game_value;
            $sortBy = $_POST['sort_by'] ?? 'game_name';
            $sortOrder = strtoupper($_POST['sort_order'] ?? 'ASC');
            $allowedSortColumns = ['id', 'sort', 'game_code', 'game_name', 'status', 'provider', 'provideragent', 'game_vendor'];
            if (!in_array($sortBy, $allowedSortColumns)) { $sortBy = 'game_name'; }
            if ($sortOrder !== 'ASC' && $sortOrder !== 'DESC') { $sortOrder = 'ASC'; }
            $escaped_table_name = mysqli_real_escape_string($koneksi, $table_name);
            $query = "SELECT t1.id, t1.sort, t1.lang, t1.game_code, t1.game_name, t1.banner, t1.status, t1.provider, t1.frbavailable, t1.provideragent, t1.game_vendor 
                      FROM `{$escaped_table_name}` AS t1
                      LEFT JOIN `gamelist_egames` AS t2 ON t1.game_code = t2.game_code AND t2.jenis_game = ?
                      WHERE t2.id IS NULL
                      ORDER BY t1.`$sortBy` $sortOrder";
            $stmt = mysqli_prepare($koneksi, $query);
            mysqli_stmt_bind_param($stmt, "s", $jenis_game_value);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if ($result) {
                $games_data = [];
                while ($row = mysqli_fetch_assoc($result)) { $games_data[] = $row; }
                mysqli_free_result($result);
                mysqli_stmt_close($stmt);
                $response['status'] = 'success';
                $response['message'] = 'Data game berhasil diambil.';
                $response['games'] = $games_data;
            } else { $response['message'] = 'Gagal menjalankan query: ' . mysqli_error($koneksi); }
        } else { $response['message'] = 'Nama tabel tidak valid atau tidak diizinkan.'; }

    } elseif ($action == 'add_to_egames') {
        if (!$koneksi) { $response['message'] = 'Koneksi database gagal.'; http_response_code(500); echo json_encode($response); exit(); }

        $sort = $_POST['sort'] ?? '0';
        $lang = $_POST['lang'] ?? 'id';
        $game_code = $_POST['game_code'] ?? '';
        $game_name = $_POST['game_name'] ?? '';
        $status_val = $_POST['status'] ?? '0';
        $provider_val = $_POST['provider'] ?? ''; 
        $frbavailable = $_POST['frbavailable'] ?? '0';
        $provideragent = $_POST['provideragent'] ?? '';
        $game_vendor = isset($_POST['game_vendor']) && $_POST['game_vendor'] !== 'null' ? $_POST['game_vendor'] : null;
        $jenis_game = $_POST['jenis_game'] ?? 'default';
        $original_banner = $_POST['original_banner'] ?? '';

        if (empty($game_code) || empty($game_name) || empty($provider_val) || empty($jenis_game)) {
            $response['message'] = 'Data tidak lengkap. Pastikan game code, game name, provider, dan jenis game diisi.';
            http_response_code(400);
            echo json_encode($response);
            exit();
        }

        $banner_db_url = ''; 
        $sanitized_jenis_game_folder = sanitize_folder_name($jenis_game);
        $server_base_upload_dir = realpath(__DIR__ . '/../uploads/egames');
        
        // 1. Prioritas: Proses upload file manual jika ada
        if (isset($_FILES['banner_file_upload']) && $_FILES['banner_file_upload']['error'] == UPLOAD_ERR_OK) {
            $file_tmp_path = $_FILES['banner_file_upload']['tmp_name'];
            $file_name = $_FILES['banner_file_upload']['name'];
            $file_size = $_FILES['banner_file_upload']['size'];
            $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $max_file_size = 5 * 1024 * 1024; 

            if (in_array($file_extension, $allowed_extensions) && $file_size <= $max_file_size) {
                $target_subdir_on_server = $server_base_upload_dir . '/' . $sanitized_jenis_game_folder;
                if (!is_dir($target_subdir_on_server)) {
                    mkdir($target_subdir_on_server, 0755, true);
                }
                $new_file_name = uniqid('banner_up_', true) . '.' . $file_extension;
                $target_file_path_on_server = $target_subdir_on_server . '/' . $new_file_name;
                if (move_uploaded_file($file_tmp_path, $target_file_path_on_server)) {
                    $banner_db_url = rtrim($alamat_website, '/') . '/uploads/egames/' . $sanitized_jenis_game_folder . '/' . $new_file_name;
                }
            }
        } 
        
        // 2. Jika tidak ada upload manual, coba unduh dari URL asli
        if (empty($banner_db_url) && !empty($original_banner)) {
            $downloaded_url = download_banner_from_url($original_banner, $server_base_upload_dir, $sanitized_jenis_game_folder, $alamat_website);
            
            if ($downloaded_url) {
                // Jika unduh berhasil, gunakan URL lokal baru
                $banner_db_url = $downloaded_url;
            } else {
                // Jika unduh gagal, gunakan URL asli sebagai fallback
                $banner_db_url = $original_banner;
            }
        }
        
        // Cek duplikasi sebelum insert
        $check_stmt_sql = "SELECT id FROM gamelist_egames WHERE game_code = ? AND jenis_game = ?";
        $check_stmt = mysqli_prepare($koneksi, $check_stmt_sql);
        mysqli_stmt_bind_param($check_stmt, "ss", $game_code, $jenis_game);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);

        if (mysqli_stmt_num_rows($check_stmt) > 0) {
            $response['message'] = 'Game dengan Game Code & Jenis Game ini sudah ada di daftar Egames.';
            http_response_code(409);
            mysqli_stmt_close($check_stmt);
            echo json_encode($response);
            exit();
        }
        mysqli_stmt_close($check_stmt);

        // Insert data baru
        $insert_sql = "INSERT INTO gamelist_egames (sort, lang, game_code, game_name, banner, status, provider, frbavailable, provideragent, game_vendor, jenis_game) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($koneksi, $insert_sql);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sssssssssss",
                $sort, $lang, $game_code, $game_name, $banner_db_url, $status_val,
                $provider_val, $frbavailable, $provideragent, $game_vendor, $jenis_game
            );

            if (mysqli_stmt_execute($stmt)) {
                $response['status'] = 'success';
                $response['message'] = 'Game berhasil ditambahkan. Banner telah diproses.';
                $response['new_banner_url'] = $banner_db_url;
            } else {
                $response['message'] = 'Gagal menambahkan game: ' . mysqli_stmt_error($stmt);
                http_response_code(500);
            }
            mysqli_stmt_close($stmt);
        } else {
            $response['message'] = 'Gagal mempersiapkan SQL: ' . mysqli_error($koneksi);
            http_response_code(500);
        }
    }
}

echo json_encode($response);
exit();
?>