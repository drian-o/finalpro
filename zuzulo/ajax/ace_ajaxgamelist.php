<?php
// Pastikan tidak ada output sebelum json_encode di akhir
header('Content-Type: application/json');

// Memulai session, jika belum dimulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Sertakan file koneksi.php dan class Acenet
include_once '../../koneksi.php'; // Sesuaikan path
include_once '../../classes/class.acenet.php';

// Pastikan admin sudah login
if (!isset($_SESSION['kode_admin'])) {
    echo json_encode(['status' => 'error', 'message' => 'Sesi tidak valid, harap login kembali.']);
    exit();
}

// Periksa apakah request adalah POST dan memiliki provider_code
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['provider_code']) || empty($_POST['provider_code'])) {
    echo json_encode(['status' => 'error', 'message' => 'Permintaan tidak valid.']);
    exit();
}

$selected_provider_code = $_POST['provider_code'];
$response_data = []; // Data yang akan dikembalikan sebagai JSON
$process_log = []; // Log detail proses

$process_log[] = "Memulai proses update/insert game untuk provider: " . htmlspecialchars($selected_provider_code);

try {
    // Memanggil fungsi getGameListByProvider dari instance $WL
    $api_response = $WL->getGameListByProvider($selected_provider_code);
    $process_log[] = "Respon API diterima. Memproses " . (count($api_response['games'] ?? [])) . " game.";

    if (isset($api_response['status']) && $api_response['status'] === 1) {
        $game_list_data = $api_response['games'] ?? [];

        // --- Proses Update/Insert Game ke Database ---
        if (!empty($game_list_data)) {
            $inserted_count = 0;
            $updated_count = 0;
            $error_db_count = 0;
            $download_success_count = 0;
            $download_fail_count = 0;

            if (isset($koneksi) && $koneksi instanceof mysqli) {
                // Mendapatkan nama provider dari daftar yang diambil dari DB untuk nama folder
                $provider_name_for_folder = 'unknown_provider';
                // Ambil daftar provider dari DB (lagi), karena kita membutuhkannya di sini
                // Bisa juga dikirimkan dari AJAX request jika ingin menghemat query DB
                $query_providers_db = $koneksi->query("SELECT provider_code, provider_name FROM ace_provider WHERE provider_status = 1");
                if ($query_providers_db) {
                    while ($row = $query_providers_db->fetch_assoc()) {
                        if ($row['provider_code'] === $selected_provider_code) {
                            $provider_name_for_folder = preg_replace('/[^a-zA-Z0-9_\-]/', '', $row['provider_name']);
                            break;
                        }
                    }
                    $query_providers_db->free();
                }

                $upload_dir = '../upload/game/' . $provider_name_for_folder . '/';
                if (!is_dir($upload_dir)) {
                    if(mkdir($upload_dir, 0777, true)) {
                        $process_log[] = "Direktori '$upload_dir' berhasil dibuat.";
                    } else {
                        $process_log[] = "<span style='color:red;'>Gagal membuat direktori '$upload_dir'. Pastikan izin folder 'upload/game' benar.</span>";
                    }
                }

                // Siapkan statement untuk INSERT ON DUPLICATE KEY UPDATE
                $stmt = $koneksi->prepare("INSERT INTO ace_gamelist (provider_code, game_code, game_name, game_image_local, game_image_url_api, game_status) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE provider_code = VALUES(provider_code), game_name = VALUES(game_name), game_image_local = VALUES(game_image_local), game_image_url_api = VALUES(game_image_url_api), game_status = VALUES(game_status), last_updated = CURRENT_TIMESTAMP");

                if ($stmt === false) {
                    $process_log[] = "<span style='color:red;'><strong>Kesalahan SQL:</strong> Gagal menyiapkan statement: " . htmlspecialchars($koneksi->error) . "</span>";
                    error_log("SQL Prepare Error (ace_gamelist - AJAX): " . $koneksi->error);
                } else {
                    $game_counter = 0;
                    foreach ($game_list_data as $game) {
                        $game_counter++;
                        $current_game_log = "Memproses game #" . $game_counter . ": <strong>" . htmlspecialchars($game['game_name'] ?? 'Unknown Game') . "</strong> (Code: " . htmlspecialchars($game['game_code'] ?? 'N/A') . ")";

                        $game_code = $game['game_code'] ?? null;
                        $game_name = $game['game_name'] ?? null;
                        $game_image_url_api = $game['game_image'] ?? null;
                        $game_status = (int)($game['game_status'] ?? 0); // Pastikan integer
                        $game_image_to_save_in_db = null; // Ini akan menjadi path lokal ATAU URL API

                        // --- Download Gambar ---
                        if (!empty($game_image_url_api)) {
                            $image_info = pathinfo($game_image_url_api);
                            $image_extension = $image_info['extension'] ?? 'jpg';
                            $local_filename = $game_code . '.' . $image_extension;
                            $full_local_path = $upload_dir . $local_filename;

                            if (file_exists($full_local_path)) {
                                $game_image_to_save_in_db = $full_local_path;
                                $download_success_count++;
                                $current_game_log .= "<br><span style='color: grey;'>Gambar sudah ada di server lokal: " . htmlspecialchars($full_local_path) . "</span>";
                            } else {
                                $image_content = @file_get_contents($game_image_url_api);
                                if ($image_content !== false) {
                                    if (file_put_contents($full_local_path, $image_content)) {
                                        $game_image_to_save_in_db = $full_local_path;
                                        $download_success_count++;
                                        $current_game_log .= "<br><span style='color: green;'>Gambar berhasil diunduh ke: " . htmlspecialchars($full_local_path) . "</span>";
                                    } else {
                                        $download_fail_count++;
                                        $game_image_to_save_in_db = $game_image_url_api; // Gunakan URL API jika gagal simpan lokal
                                        $current_game_log .= "<br><span style='color: orange;'>Gagal menyimpan gambar lokal. Menggunakan URL API: " . htmlspecialchars($game_image_url_api) . "</span>";
                                        error_log("Failed to save image locally for " . $game_code . ": " . $full_local_path);
                                    }
                                } else {
                                    $download_fail_count++;
                                    $game_image_to_save_in_db = $game_image_url_api; // Gunakan URL API jika gagal download
                                    $current_game_log .= "<br><span style='color: orange;'>Gagal mengunduh gambar dari API. Menggunakan URL API: " . htmlspecialchars($game_image_url_api) . "</span>";
                                    error_log("Failed to download image from URL for " . $game_code . ": " . $game_image_url_api);
                                }
                            }
                        } else {
                            $game_image_to_save_in_db = null;
                            $current_game_log .= "<br><span style='color: grey;'>Tidak ada URL gambar dari API untuk game ini.</span>";
                        }

                        // Lanjutkan hanya jika data penting tidak null
                        if ($game_code !== null && $game_name !== null) {
                            $stmt->bind_param("sssssi", $selected_provider_code, $game_code, $game_name, $game_image_to_save_in_db, $game_image_url_api, $game_status);
                            if ($stmt->execute()) {
                                if ($stmt->affected_rows === 1) {
                                    $inserted_count++;
                                    $current_game_log .= "<br><span style='color: blue;'>Game berhasil di-INSERT ke database.</span>";
                                } elseif ($stmt->affected_rows === 2) {
                                    $updated_count++;
                                    $current_game_log .= "<br><span style='color: purple;'>Game berhasil di-UPDATE di database.</span>";
                                }
                            } else {
                                $error_db_count++;
                                $current_game_log .= "<br><span style='color: red;'>Gagal INSERT/UPDATE game ke database: " . htmlspecialchars($stmt->error) . "</span>";
                                error_log("Database error for game " . $game_code . ": " . $stmt->error);
                            }
                        } else {
                            $error_db_count++;
                            $current_game_log .= "<br><span style='color: red;'>Data penting game tidak lengkap. Gagal INSERT/UPDATE. Game Code: " . htmlspecialchars($game_code ?? 'N/A') . "</span>";
                            error_log("Missing essential data for game. Code: " . ($game_code ?? 'N/A') . " - Full game data: " . json_encode($game));
                        }
                        $process_log[] = $current_game_log; // Tambahkan log untuk game ini ke array utama
                    }
                    $stmt->close();
                    $response_data['status_message'] = "Proses database selesai.<br>Insert: <strong>{$inserted_count}</strong>, Update: <strong>{$updated_count}</strong>, Gagal DB: <strong>{$error_db_count}</strong>.<br>Gambar Download Berhasil: <strong>{$download_success_count}</strong>, Gagal Download Gambar: <strong>{$download_fail_count}</strong>.";
                    $response_data['status_type'] = 'success'; // Tipe status untuk alert
                } else {
                    $response_data['status_message'] = '<span style="color:red;"><strong>Peringatan:</strong> Objek koneksi database ($koneksi) tidak ditemukan atau tidak valid. Pastikan `koneksi.php` sudah benar. Data tidak disimpan ke database.</span>';
                    $response_data['status_type'] = 'warning';
                }
            } else {
                $response_data['status_message'] = '<span style="color:blue;">Tidak ada data game dari API untuk disimpan.</span>';
                $response_data['status_type'] = 'info';
            }

        } else {
            $api_error_message = $api_response['msg'] ?? 'Gagal mengambil daftar game. Pesan API tidak tersedia.';
            $response_data['status_message'] = '<span style="color:red;">Gagal mengambil data dari API: ' . htmlspecialchars($api_error_message) . '</span>';
            $response_data['status_type'] = 'danger';
        }
    } catch (Exception $e) {
        $api_error_message = "Terjadi kesalahan saat memanggil API: " . $e->getMessage();
        $response_data['status_message'] = '<span style="color:red;">Terjadi kesalahan pada proses API: ' . htmlspecialchars($e->getMessage()) . '</span>';
        $response_data['status_type'] = 'danger';
    }
} catch (Exception $e) {
    // Menangkap exception yang mungkin terjadi di luar try utama
    $process_log[] = "<span style='color:red;'>Kesalahan tak terduga: " . htmlspecialchars($e->getMessage()) . "</span>";
    $response_data['status_message'] = '<span style="color:red;">Terjadi kesalahan tak terduga: ' . htmlspecialchars($e->getMessage()) . '</span>';
    $response_data['status_type'] = 'danger';
}

// Kirim semua log dan status kembali ke klien
$response_data['process_log'] = $process_log;
$response_data['raw_api_response'] = $api_response ?? null; // Kirim respons API mentah juga (sudah didekode)

echo json_encode($response_data);
exit();
?>