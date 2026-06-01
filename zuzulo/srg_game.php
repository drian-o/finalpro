<?php
  // Aktifkan output buffering dan implicit flush untuk tampilan real-time
  ob_implicit_flush(true);
  ob_start();

  // Aktifkan pelaporan kesalahan PHP untuk debugging (HAPUS INI DI PRODUKSI)
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);

  // Memulai session, jika belum dimulai di koneksi.php
  if (session_status() == PHP_SESSION_NONE) {
      session_start();
  }

  include_once '../koneksi.php'; // Path ke file koneksi Anda
  include_once '../classes/class.exa.php'; // Ganti ke class GameXaAPI

  // Pastikan variabel $alamat_admin didefinisikan di koneksi.php atau tempat lain
  if (!isset($alamat_admin)) {
      $alamat_admin = '/admin/'; // Default jika tidak didefinisikan
  }

  // Pengalihan jika admin belum login
  if (!isset($_SESSION['kode_admin'])) {
    echo '
      <script>
        alert("Terjadi kesalahan, harap masuk kembali!");
        window.location.replace("'.$alamat_admin.'keluar.php");
      </script>
    ';
    exit(); // Penting: tambahkan exit setelah redirect
  }

  // --- Inisialisasi GameXaAPI ---
  $gameXaAPI = new GameXaAPI();

  // --- LOGIKA AJAX UNTUK MENGAMBIL DAFTAR PROVIDER DARI API UNTUK DROPDOWN ---
  // Ini akan dipanggil oleh JavaScript (AJAX) untuk mengisi dropdown provider.
  if (isset($_GET['action']) && $_GET['action'] === 'get_api_providers_for_dropdown') {
      header('Content-Type: application/json');
      try {
          $response = $gameXaAPI->getGameProviders();
          if ($response['success'] && isset($response['data']['providers'])) {
              $providers_for_dropdown = [];
              foreach ($response['data']['providers'] as $provider) {
                  // Hanya tambahkan provider yang memiliki provider_code
                  if (isset($provider['provider_code'])) {
                      $providers_for_dropdown[] = [
                          'provider_code' => $provider['provider_code'],
                          'provider_name' => $provider['provider_name'] ?? 'Unknown Provider'
                      ];
                  }
              }
              echo json_encode(['success' => true, 'data' => $providers_for_dropdown]);
          } else {
              echo json_encode(['success' => false, 'message' => $response['message'] ?? 'Gagal mengambil provider dari API.']);
          }
      } catch (Exception $e) {
          echo json_encode(['success' => false, 'message' => 'API Error: ' . $e->getMessage()]);
      }
      exit(); // Hentikan eksekusi setelah respon JSON dikirim
  }

  // --- Ambil Daftar Provider dari Database (INI TETAP DIBUTUHKAN UNTUK LOGIKA UPDATE/INSERT DB GAME) ---
  // Karena 'processProviderGamesExa' perlu provider_name untuk folder gambar dan potentially provider_type,
  // kita masih perlu mengambil data ini dari srg_provider.
  // Jika Anda memiliki kasus di mana provider di API tapi tidak di DB, proses pembuatan folder akan menggunakan 'unknown_provider'.
  $db_providers = [];
  $db_provider_details = []; // Simpan detail provider termasuk type
  $db_provider_error = '';

  if (isset($koneksi) && $koneksi instanceof mysqli) {
      $query_providers = $koneksi->query("SELECT provider_code, provider_name, provider_type FROM srg_provider WHERE provider_status = 'active' ORDER BY provider_name ASC");
      if ($query_providers) {
          while ($row = mysqli_fetch_assoc($query_providers)) {
              $db_providers[] = $row;
              $db_provider_details[$row['provider_code']] = $row;
          }
          $query_providers->free();
      } else {
          $db_provider_error = "Gagal mengambil daftar provider dari database (untuk proses update game): " . htmlspecialchars($koneksi->error);
      }
  } else {
      $db_provider_error = "Koneksi database tidak valid.";
  }

  // --- Inisialisasi Variabel untuk Tampilan Hasil ---
  $selected_provider_code = '';
  if (isset($_POST['update_games']) && isset($_POST['provider_code'])) {
      $selected_provider_code = $_POST['provider_code'];
  }

  $game_list_data = null;
  $api_error_message = '';
  $raw_api_response_display = 'Tidak ada respon mentah.';
  $database_message = ''; // Pesan untuk status update/insert database game
  $show_results = false; // Flag untuk menampilkan hasil hanya setelah tombol diklik
  $is_one_tab_update = false; // Flag untuk menandai apakah ini proses "One Tab Update"

  // --- VARIABEL GLOBAL UNTUK LOGGING ---
  $log_total_games_processed = 0;
  $log_inserted_updated_games = []; // Untuk menyimpan daftar game yang di-insert/update
  $log_games_needing_image_update = 0; // Untuk game yang gambarnya tidak di-download (menggunakan API URL atau null)

  /**
   * Fungsi untuk memproses update/insert game untuk provider tertentu.
   * Ini diisolasi agar bisa dipanggil oleh logika single update atau one-tab update.
   * Parameter $all_games_from_api digunakan untuk One Tab Update, agar tidak memanggil getAllGames berulang kali.
   */
  function processProviderGamesExa($provider_code, $provider_type, $koneksi, $gameXaAPI, $db_providers, $all_games_from_api = null) {
      // Variabel global ini diakses di dalam fungsi
      global $game_list_data, $api_error_message, $raw_api_response_display, $database_message;
      global $log_total_games_processed, $log_inserted_updated_games, $log_games_needing_image_update; // Akses variabel log

      echo "<p>--- Memulai proses untuk provider: <strong>" . htmlspecialchars($provider_code) . "</strong> (Tipe Database: <strong>" . htmlspecialchars($provider_type) . "</strong>) ---</p>";
      ob_flush();
      flush();

      try {
          $response = [];
          if ($all_games_from_api !== null) {
              // Jika ini bagian dari One Tab Update, filter dari data getAllGames yang sudah ada
              $game_list_for_current_provider = array_filter($all_games_from_api, function($game) use ($provider_code) {
                  return ($game['provider_code'] ?? null) === $provider_code;
              });
              $game_list_data = array_values($game_list_for_current_provider); // Reset keys
              echo "<p>Data game diambil dari cache global (One Tab Update).</p>";
          } else {
              // Panggil metode getGamesByProvider() dari objek GameXaAPI untuk single update
              $response = $gameXaAPI->getGamesByProvider($provider_code);

              // Untuk Single Update, tampilkan raw response spesifik
              $raw_api_response_display = "<pre>" . htmlspecialchars(json_encode($response, JSON_PRETTY_PRINT)) . "</pre>";

              if ($response['success'] && isset($response['data']['games']) && is_array($response['data']['games'])) {
                  $game_list_data = $response['data']['games'];
                  echo "<p>Respon API diterima. Memproses " . count($game_list_data) . " game untuk provider ini.</p>";
              } else {
                  $error_message_from_exa = $response['message'] ?? 'Respon API tidak valid atau kosong.';
                  $api_error_message = 'Gagal mengambil daftar game dari API untuk ' . htmlspecialchars($provider_code) . ': ' . htmlspecialchars($error_message_from_exa);
                  echo '<div class="alert alert-danger" role="alert"><strong>Error API:</strong> ' . htmlspecialchars($api_error_message) . '</div>';
                  ob_flush();
                  flush();
                  return; // Hentikan proses untuk provider ini jika gagal API
              }
          }
          ob_flush();
          flush();

          if (!empty($game_list_data)) {
              $inserted_count = 0;
              $updated_count = 0;
              $error_db_count = 0;
              $download_success_count = 0;
              $download_fail_count = 0;

              // Tambahkan untuk keperluan logging lokal
              $current_provider_log_inserted_updated_games = [];
              $current_provider_log_games_needing_image_update = 0;
              $log_total_games_processed += count($game_list_data); // Tambahkan ke total game yang diproses

              if ($koneksi instanceof mysqli) {
                  $provider_name_for_folder = 'unknown_provider';
                  // Cari nama provider dari $db_providers yang sudah diambil dari database
                  foreach ($db_providers as $p) {
                      if ($p['provider_code'] === $provider_code) {
                          $provider_name_for_folder = preg_replace('/[^a-zA-Z0-9_\-]/', '', $p['provider_name']);
                          break;
                      }
                  }

                  $upload_dir_physical = '../upload/game/' . $provider_name_for_folder . '/'; // Path fisik untuk PHP
                  $upload_dir_db_relative = 'upload/game/' . $provider_name_for_folder . '/'; // Path relatif untuk disimpan di DB (tanpa ../)

                  if (!is_dir($upload_dir_physical)) {
                      if(mkdir($upload_dir_physical, 0777, true)) {
                          echo "<p>Direktori '$upload_dir_physical' berhasil dibuat.</p>";
                      } else {
                          echo "<p style='color:red;'>Gagal membuat direktori '$upload_dir_physical'. Pastikan izin folder 'upload/game' benar.</p>";
                      }
                      ob_flush(); flush();
                  }

                  // Gunakan kolom tabel srg_gamelist: provider_code, game_code, game_name, game_type, game_image_local, game_image_url_api, game_status
                  // game_code di sini akan kita gunakan untuk menyimpan game_uid dari GameXa
                  // Hapus 'game_image_local = VALUES(game_image_local)' dari klausa ON DUPLICATE KEY UPDATE
                  $stmt = $koneksi->prepare("INSERT INTO srg_gamelist (provider_code, game_code, game_name, game_type, game_image_local, game_image_url_api, game_status) VALUES (?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE game_name = VALUES(game_name), game_type = VALUES(game_type), game_image_url_api = VALUES(game_image_url_api), game_status = VALUES(game_status), last_updated = CURRENT_TIMESTAMP");

                  if ($stmt === false) {
                      echo '<p style="color:red;"><strong>Kesalahan SQL:</strong> Gagal menyiapkan statement: ' . htmlspecialchars($koneksi->error) . '</p>';
                      error_log("SQL Prepare Error (srg_gamelist): " . $koneksi->error);
                  } else {
                      $game_counter = 0;
                      foreach ($game_list_data as $game) {
                          $game_counter++;
                          echo "<p>Memproses game #{$game_counter}: <strong>" . htmlspecialchars($game['game_name'] ?? 'Unknown Game') . "</strong> (UID: " . htmlspecialchars($game['game_uid'] ?? 'N/A') . ")</p>";
                          ob_flush();
                          flush();

                          // Sesuaikan dengan kunci dari respons GameXa API (array asosiatif)
                          $exa_game_uid = $game['game_uid'] ?? null; // Ini akan jadi game_code di DB
                          $exa_game_name = $game['game_name'] ?? null;
                          $exa_game_type = $game['game_type'] ?? 'slot'; // API GameXa punya 'game_type'
                          $exa_game_status = $game['status'] ?? 'inactive'; // API GameXa punya 'status'
                          $exa_game_image_url_api = $game['image_url'] ?? null; // API GameXa punya 'image_url'

                          $game_image_to_save_in_db = null; // Ini akan menjadi path lokal atau URL API

                          if (!empty($exa_game_image_url_api)) {
                              $image_info = pathinfo($exa_game_image_url_api);
                              $image_extension = $image_info['extension'] ?? 'png'; // Default to png
                              $local_filename = $exa_game_uid . '.' . $image_extension; // Gunakan game_uid untuk nama file
                              $full_local_path_on_server = $upload_dir_physical . $local_filename; // Path fisik untuk file_exists() dan file_put_contents()
                              $db_image_path_for_column = $upload_dir_db_relative . $local_filename; // Path yang akan disimpan di database (tanpa '../')

                              if (file_exists($full_local_path_on_server)) {
                                  $game_image_to_save_in_db = $db_image_path_for_column;
                                  $download_success_count++;
                                  echo "<p style='color: grey;'>Gambar sudah ada di server lokal: " . htmlspecialchars($db_image_path_for_column) . "</p>";
                              } else {
                                  $image_content = @file_get_contents($exa_game_image_url_api);
                                  if ($image_content !== false) {
                                      if (file_put_contents($full_local_path_on_server, $image_content)) {
                                          $game_image_to_save_in_db = $db_image_path_for_column;
                                          $download_success_count++;
                                          echo "<p style='color: green;'>Gambar berhasil diunduh ke: " . htmlspecialchars($db_image_path_for_column) . "</p>";
                                      } else {
                                          $download_fail_count++;
                                          $game_image_to_save_in_db = null; // Gagal simpan, biarkan null untuk insert baru
                                          $current_provider_log_games_needing_image_update++; // Hitung sebagai perlu update gambar
                                          echo "<p style='color: orange;'>Gagal menyimpan gambar lokal. Menggunakan NULL di kolom lokal (untuk insert baru).</p>";
                                          error_log("Failed to save image locally for " . $exa_game_uid . ": " . $full_local_path_on_server);
                                      }
                                  } else {
                                      $download_fail_count++;
                                      $game_image_to_save_in_db = null; // Gagal download, biarkan null untuk insert baru
                                      $current_provider_log_games_needing_image_update++; // Hitung sebagai perlu update gambar
                                      echo "<p style='color: orange;'>Gagal mengunduh gambar dari API. Menggunakan NULL di kolom lokal (untuk insert baru).</p>";
                                      error_log("Failed to download image from URL for " . $exa_game_uid . ": " . $exa_game_image_url_api);
                                  }
                              }
                          } else {
                              // Jika tidak ada URL gambar dari API, set ke null.
                              // Ini berarti game baru akan memiliki null di game_image_local
                              $game_image_to_save_in_db = null;
                              $current_provider_log_games_needing_image_update++; // Hitung sebagai perlu update gambar
                              echo "<p style='color: grey;'>Tidak ada URL gambar dari API untuk game ini. Kolom gambar lokal akan NULL (jika game baru).</p>";
                          }
                          ob_flush();
                          flush();

                          if ($exa_game_uid !== null && $exa_game_name !== null) {
                              // Perhatikan urutan parameter sesuai dengan statement SQL
                              $stmt->bind_param("sssssss", $provider_code, $exa_game_uid, $exa_game_name, $exa_game_type, $game_image_to_save_in_db, $exa_game_image_url_api, $exa_game_status);
                              if ($stmt->execute()) {
                                  if ($stmt->affected_rows === 1) {
                                      $inserted_count++;
                                      $current_provider_log_inserted_updated_games[] = "INSERTED: [{$provider_code}] {$exa_game_name} (UID: {$exa_game_uid})";
                                      echo "<p style='color: blue;'>Game berhasil di-INSERT ke database.</p>";
                                  } elseif ($stmt->affected_rows === 2) {
                                      $updated_count++;
                                      $current_provider_log_inserted_updated_games[] = "UPDATED: [{$provider_code}] {$exa_game_name} (UID: {$exa_game_uid})";
                                      echo "<p style='color: purple;'>Game berhasil di-UPDATE di database (kolom lain, gambar lokal tidak).</p>";
                                  } else { // affected_rows === 0 (data sama, hanya last_updated yang mungkin berubah)
                                      echo "<p style='color: grey;'>Game sudah ada dan tidak ada perubahan data (gambar lokal tidak diubah).</p>";
                                  }
                              } else {
                                  $error_db_count++;
                                  echo "<p style='color: red;'>Gagal INSERT/UPDATE game ke database: " . htmlspecialchars($stmt->error) . "</p>";
                                  error_log("Database error for game " . $exa_game_uid . ": " . $stmt->error);
                              }
                          } else {
                              $error_db_count++;
                              echo "<p style='color: red;'>Data penting game tidak lengkap. Gagal INSERT/UPDATE. Game UID: " . htmlspecialchars($exa_game_uid ?? 'N/A') . "</p>";
                              error_log("Missing essential data for game. UID: " . ($exa_game_uid ?? 'N/A') . " - Full game data: " . json_encode($game));
                          }
                          ob_flush();
                          flush();
                      }
                      $stmt->close();
                      echo '<div class="alert alert-success" role="alert">Proses database untuk <strong>' . htmlspecialchars($provider_code) . '</strong> selesai.<br>Insert: <strong>'.$inserted_count.'</strong>, Update: <strong>'.$updated_count.'</strong>, Gagal: <strong>'.$error_db_count.'</strong>.<br>Gambar Download Berhasil: <strong>'.$download_success_count.'</strong>, Gagal Download Gambar: <strong>'.$download_fail_count.'</strong>.</div>';
                  }
              } else {
                  echo '<div class="alert alert-warning" role="alert"><strong>Peringatan:</strong> Objek koneksi database ($koneksi) tidak ditemukan atau tidak valid. Pastikan `koneksi.php` sudah benar. Data tidak disimpan ke database.</div>';
              }
              // Tambahkan hasil per provider ke log global
              $log_inserted_updated_games = array_merge($log_inserted_updated_games, $current_provider_log_inserted_updated_games);
              $log_games_needing_image_update += $current_provider_log_games_needing_image_update;

          } else {
              echo '<div class="alert alert-info" role="alert">Tidak ada data game dari API untuk disimpan untuk provider ini.</div>';
          }
      } catch (Exception $e) {
          $api_error_message = "Terjadi kesalahan saat memanggil API untuk " . htmlspecialchars($provider_code) . ": " . $e->getMessage();
          echo '<div class="alert alert-danger" role="alert">Terjadi kesalahan pada proses API untuk <strong>' . htmlspecialchars($provider_code) . '</strong>: ' . htmlspecialchars($e->getMessage()) . '</div>';
          error_log("Exception in processProviderGamesExa for " . $provider_code . ": " . $e->getMessage());
      }
      echo "<p>--- Proses untuk provider: <strong>" . htmlspecialchars($provider_code) . "</strong> selesai. ---</p><br>";
      ob_flush();
      flush();
  }

  // --- Logika Pemanggilan API Game List dan Update Database Game (Single Provider) ---
  if (isset($_POST['update_games']) && !empty($selected_provider_code)) {
      $show_results = true;
      $provider_type_from_db = $db_provider_details[$selected_provider_code]['provider_type'] ?? 'unknown_type';

      echo '<hr class="my-4" />';
      echo '<h4>Status Proses Download & Database:</h4>';
      echo '<div class="progress-log" id="progressLog" style="max-height: 300px; overflow-y: scroll; border: 1px solid #ccc; padding: 10px; background-color: #f9f9f9;">';
      processProviderGamesExa($selected_provider_code, $provider_type_from_db, $koneksi, $gameXaAPI, $db_providers, null); // Null for all_games_from_api
      echo '</div>'; // Tutup progress-log div
  }
  // --- Logika Pemanggilan API Game List dan Update Database Game (One Tab Update - All Providers) ---
  elseif (isset($_POST['update_all_games'])) {
      $show_results = true;
      $is_one_tab_update = true; // Set flag

      echo '<script>alert("Proses akan memakan waktu. Jangan tinggalkan halaman ini sampai proses selesai!");</script>';
      ob_flush();
      flush();

      echo '<hr class="my-4" />';
      echo '<h4>Status Proses Download & Database (One Tab Update):</h4>';
      echo '<div class="progress-log" id="progressLog" style="max-height: 500px; overflow-y: scroll; border: 1px solid #ccc; padding: 10px; background-color: #f9f9f9;">'; // Lebih tinggi untuk semua provider

      // Panggil getAllGames sekali untuk semua game
      $all_games_response = $gameXaAPI->getAllGames();
      $all_games_data = [];
      if ($all_games_response['success'] && isset($all_games_response['data']['games'])) {
          $all_games_data = $all_games_response['data']['games'];
          echo "<p>Berhasil mendapatkan " . count($all_games_data) . " total game dari API GameXa.</p>";
      } else {
          echo '<div class="alert alert-danger" role="alert"><strong>Error:</strong> Gagal mengambil semua game dari API GameXa untuk One Tab Update. Pesan: ' . htmlspecialchars($all_games_response['message'] ?? 'Tidak diketahui') . '</div>';
          ob_flush();
          flush();
          // Jika gagal mendapatkan semua game, hentikan proses one-tab update
          echo '</div>'; // Tutup progress-log div
          ob_end_flush();
          exit();
      }

      // Pastikan $db_providers terisi untuk logika pembuatan folder di processProviderGamesExa()
      // Jika Anda tidak mengandalkan DB untuk folder nama, bagian ini bisa dihilangkan.
      if (empty($db_providers) && isset($koneksi) && $koneksi instanceof mysqli) {
          $query_all_providers_for_folder_names = $koneksi->query("SELECT provider_code, provider_name, provider_type FROM srg_provider WHERE provider_status = 'active'");
          if ($query_all_providers_for_folder_names) {
              while($row = mysqli_fetch_assoc($query_all_providers_for_folder_names)) {
                  $db_providers[] = $row;
              }
              $query_all_providers_for_folder_names->free();
          }
      }


      if (!empty($db_providers)) { // Iterasi hanya provider yang ada di DB untuk One Tab Update
          foreach ($db_providers as $provider) {
              $current_provider_code = $provider['provider_code'];
              $current_provider_type = $provider['provider_type'];

              // Teruskan $all_games_data agar fungsi tidak perlu memanggil API lagi
              processProviderGamesExa($current_provider_code, $current_provider_type, $koneksi, $gameXaAPI, $db_providers, $all_games_data);
          }
          echo '<div class="alert alert-success" role="alert"><strong>One Tab Update Selesai!</strong> Semua provider yang aktif di database telah diproses. Silakan cek log di atas untuk detail setiap provider.</div>';
      } else {
          echo '<div class="alert alert-warning" role="alert">Tidak ada provider aktif yang ditemukan di database untuk diproses. Silakan tambahkan provider melalui halaman Provider terlebih dahulu.</div>';
      }
      echo '</div>'; // Tutup progress-log div
  }

  // --- LOGGING KE FILE SETELAH SEMUA PROSES SELESAI ---
  if ($show_results) { // Hanya log jika ada proses yang dijalankan
      $log_file_path = __DIR__ . '/../logs/image_update.log'; // Path absolut untuk log file
      $log_directory = dirname($log_file_path);

      // Buat direktori logs jika belum ada
      if (!is_dir($log_directory)) {
          mkdir($log_directory, 0777, true);
      }

      $log_content = "\n--- Log Update Game - " . date('Y-m-d H:i:s') . " ---\n";
      $log_content .= "Mode: " . ($is_one_tab_update ? "One Tab Update (Semua Provider)" : "Single Provider Update") . "\n";
      $log_content .= "Provider Terpilih: " . ($is_one_tab_update ? "SEMUA" : htmlspecialchars($selected_provider_code)) . "\n\n";
      $log_content .= "Jumlah total game yang diproses API: " . $log_total_games_processed . "\n";
      $log_content .= "Jumlah game yang gambar lokalnya tidak diunduh (menggunakan URL API / NULL di DB karena API tidak menyediakan gambar atau gagal unduh): " . $log_games_needing_image_update . "\n\n";
      $log_content .= "Daftar game yang baru saja di-INSERT/UPDATE di database:\n";

      if (!empty($log_inserted_updated_games)) {
          foreach ($log_inserted_updated_games as $index => $game_log_entry) {
              $log_content .= ($index + 1) . ". " . $game_log_entry . "\n";
          }
      } else {
          $log_content .= "Tidak ada game yang di-INSERT atau di-UPDATE pada proses ini.\n";
      }
      $log_content .= "--------------------------------------------------------\n";

      // Tulis log ke file, append mode
      file_put_contents($log_file_path, $log_content, FILE_APPEND | LOCK_EX);
  }

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Game GameXa Berdasarkan Provider</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* CSS untuk progress log */
        .progress-log {
            max-height: 300px;
            overflow-y: scroll;
            border: 1px solid #ccc;
            padding: 10px;
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>

<div class="container-xxl flex-grow-1 container-p-y">
  <h4 class="py-3 mb-4">
    <span class="text-muted fw-light">Menu Utama /</span> Daftar Game GameXa Berdasarkan Provider
  </h4>

  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-body">
          <p>Pilih provider dari daftar di bawah untuk memperbarui dan menyimpan daftar gamenya ke database, beserta gambarnya.</p>

          <?php if (!empty($db_provider_error) && !$show_results): // Tampilkan error DB hanya jika belum ada proses yang berjalan ?>
            <div class="alert alert-danger" role="alert">
              <strong>Error Database:</strong> <?php echo htmlspecialchars($db_provider_error); ?>
            </div>
          <?php endif; ?>

          <form method="POST" action="">
              <div class="mb-3">
                  <label for="providerSelect" class="form-label">Pilih Provider (dari API):</label>
                  <select class="form-select" id="providerSelect" name="provider_code">
                      <option value="">-- Memuat Provider dari API --</option>
                      </select>
              </div>
              <button type="submit" name="update_games" class="btn btn-primary mb-3">Update and Insert Games To Database (Selected Provider)</button>
              <button type="submit" name="update_all_games" class="btn btn-info mb-3 ms-2" onclick="return confirm('Apakah Anda yakin ingin melakukan One Tab Update untuk SEMUA provider yang aktif di database? Proses ini akan memakan waktu beberapa menit dan jangan tinggalkan halaman ini.');">One Tab Update (Update All Active Providers from DB)</button>
          </form>

          <?php if ($show_results): // Tampilkan hasil hanya jika tombol sudah diklik ?>
              <?php if (!$is_one_tab_update && !empty($api_error_message)): // Tampilkan pesan error API jika ada, hanya untuk single update ?>
                <div class="alert alert-danger mt-3" role="alert">
                  <strong>Error API:</strong> <?php echo htmlspecialchars($api_error_message); ?>
                </div>
              <?php elseif (!$is_one_tab_update && $game_list_data !== null && $game_list_data !== false): // Tampilkan daftar game jika berhasil dan tidak ada error API, hanya untuk single update ?>
                <hr class="my-4" />
                <h4>Daftar Game yang Diambil dari API GameXa (untuk referensi):</h4>
                <div class="table-responsive text-nowrap">
                  <table class="table table-bordered">
                    <thead>
                      <tr>
                        <th>Kode Game (UID)</th>
                        <th>Nama Game</th>
                        <th>Tipe Game</th>
                        <th>Gambar API</th>
                        <th>Gambar Lokal</th>
                        <th>Status</th>
                      </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                      <?php if (!empty($game_list_data)): ?>
                        <?php foreach ($game_list_data as $game):
                          // Gunakan properti array asosiatif ($game['property']) karena json_decode di class.exa.php mengembalikan array
                          $display_game_type = $game['game_type'] ?? 'N/A'; // Ambil langsung dari API GameXa
                          $temp_provider_name_for_folder = 'unknown_provider';
                          // Dapatkan nama provider untuk folder dari $db_providers (yang diambil dari DB lokal)
                          foreach ($db_providers as $p) {
                              if ($p['provider_code'] === ($game['provider_code'] ?? null)) { // Gunakan game['provider_code']
                                  $temp_provider_name_for_folder = preg_replace('/[^a-zA-Z0-9_\-]/', '', $p['provider_name']);
                                  break;
                              }
                          }
                          $temp_game_uid = $game['game_uid'] ?? 'N/A'; // Gunakan game['game_uid']
                          
                          $temp_image_url_for_display = $game['image_url'] ?? null; // Gunakan game['image_url'] dari API GameXa

                          $temp_image_ext = pathinfo($temp_image_url_for_display ?? '', PATHINFO_EXTENSION);
                          $temp_image_ext = !empty($temp_image_ext) ? $temp_image_ext : 'png'; // Default ke png
                          
                          // Path fisik lokal untuk cek keberadaan file
                          $temp_local_path_check = '../upload/game/' . $temp_provider_name_for_folder . '/' . $temp_game_uid . '.' . $temp_image_ext;

                          $game_image_local_display = '';
                          // Path relatif dari web root yang kita harapkan di DB
                          $expected_db_local_path = 'upload/game/' . $temp_provider_name_for_folder . '/' . $temp_game_uid . '.' . $temp_image_ext;

                          if(file_exists($temp_local_path_check)) { // Cek dengan path fisik
                              $game_image_local_display = '<img src="../' . htmlspecialchars($expected_db_local_path) . '" alt="Lokal" style="max-width: 50px; height: auto;">';
                          } else if (!empty($temp_image_url_for_display)) {
                              $game_image_local_display = 'Menggunakan <a href="'.htmlspecialchars($temp_image_url_for_display).'" target="_blank">URL API</a> / Gagal Unduh';
                          } else {
                              $game_image_local_display = 'Tidak Ada Gambar';
                          }
                        ?>
                          <tr>
                            <td><strong><?php echo htmlspecialchars($game['game_uid'] ?? 'N/A'); ?></strong></td>
                            <td><?php echo htmlspecialchars($game['game_name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($display_game_type); ?></td>
                            <td>
                                <?php if (!empty($temp_image_url_for_display)): ?>
                                    <img src="<?php echo htmlspecialchars($temp_image_url_for_display); ?>" alt="<?php echo htmlspecialchars($game['game_name']); ?>" style="max-width: 100px; height: auto;">
                                <?php else: ?>
                                    Tidak Ada Gambar
                                <?php endif; ?>
                            </td>
                            <td><?php echo $game_image_local_display; ?></td>
                            <td>
                              <?php
                                $status_text = $game['status'] ?? 'unknown'; // Kunci 'status' dari API GameXa
                                $status_color = ($status_text === 'active') ? 'badge bg-label-success' : 'badge bg-label-danger';
                              ?>
                              <span class="<?php echo $status_color; ?> me-1"><?php echo htmlspecialchars($status_text); ?></span>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <tr>
                          <td colspan="6">Tidak ada data game yang ditemukan dari API untuk provider ini.</td>
                        </tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              <?php endif; ?>

              <hr class="my-4" />

              <h3>Respon Mentah API (Game List):</h3>
              <p>
                <?php if ($is_one_tab_update): ?>
                  (Menampilkan respon API dari panggilan `getAllGames()` lengkap.)
                <?php endif; ?>
              </p>
              <?php echo $raw_api_response_display; ?>

          <?php endif; // End of $show_results ?>

        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
    // Fungsi untuk mengisi dropdown provider dari API
    function loadProvidersIntoDropdown() {
        $.ajax({
            url: 'srg_game.php?action=get_api_providers_for_dropdown', // Endpoint PHP yang baru
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    let selectElement = $('#providerSelect');
                    selectElement.empty(); // Kosongkan opsi yang sudah ada
                    selectElement.append('<option value="">-- Pilih Provider --</option>'); // Tambahkan opsi default

                    // Urutkan provider berdasarkan nama sebelum ditambahkan ke dropdown
                    response.data.sort(function(a, b) {
                        return a.provider_name.localeCompare(b.provider_name);
                    });

                    $.each(response.data, function(index, provider) {
                        selectElement.append('<option value="' + provider.provider_code + '">' + provider.provider_name + '</option>');
                    });

                    // Set selected_provider_code jika ada (dari $_POST saat submit sebelumnya)
                    <?php if (!empty($selected_provider_code)): ?>
                        selectElement.val('<?php echo htmlspecialchars($selected_provider_code); ?>');
                    <?php endif; ?>

                } else {
                    console.error('Failed to load providers from API:', response.message);
                    $('#providerSelect').empty().append('<option value="">-- Gagal Memuat Provider --</option>');
                    // Tampilkan pesan error di UI jika perlu
                    // Misalnya, dengan div terpisah di dekat dropdown
                    $('#apiResponseMessages').html('<div class="alert alert-danger">Gagal memuat daftar provider: ' + response.message + '</div>');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error loading providers:', status, error);
                $('#providerSelect').empty().append('<option value="">-- Error Koneksi --</option>');
                $('#apiResponseMessages').html('<div class="alert alert-danger">Terjadi kesalahan saat memuat daftar provider. Cek koneksi atau log server.</div>');
            }
        });
    }

    // Panggil fungsi untuk mengisi dropdown saat halaman selesai dimuat
    loadProvidersIntoDropdown();

    // Sisa kode JavaScript Anda (untuk submit form update_games dan update_all_games)
    // Tidak ada perubahan besar di sini karena logika submit form masih sama.
    // Pastikan ID elemen dan name input tetap sama agar logika PHP di sisi server tetap berfungsi.

});
</script>
</body>
</html>