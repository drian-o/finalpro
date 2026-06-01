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
  include_once '../classes/diamond-telo.php'; // Sertakan file kelas whitelabel

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

  // --- Ambil Daftar Provider dari Database (untuk dropdown dan proses) ---
  $db_providers = [];
  $db_provider_details = []; // Simpan detail provider termasuk type
  $db_provider_error = '';

  // Dapatkan semua tipe unik dari telo_provider yang aktif
  $allowed_telo_game_types = [];
  $temp_types_query = mysqli_query($koneksi, "SELECT DISTINCT provider_type FROM telo_provider WHERE provider_status = 'active' ORDER BY provider_type ASC");
  if ($temp_types_query) {
      while ($row = mysqli_fetch_assoc($temp_types_query)) {
          $allowed_telo_game_types[] = $row['provider_type'];
      }
      $temp_types_query->free();
  }

  // Jika tidak ada tipe yang ditemukan, set default untuk menghindari error
  if (empty($allowed_telo_game_types)) {
      $allowed_telo_game_types = ['SLOT', 'CASINO', 'SPORT', 'FISHING', 'LOTTERY', 'OTHER']; // Fallback types
  }


  if (isset($koneksi) && $koneksi instanceof mysqli) {
      // Ambil provider_code, provider_name, dan provider_type dari tabel telo_provider
      $query_providers = "SELECT provider_code, provider_name, provider_type FROM telo_provider WHERE provider_status = 'active' ORDER BY provider_name ASC, provider_type ASC";
      $result_providers = mysqli_query($koneksi, $query_providers);
      if ($result_providers) {
          while ($row = mysqli_fetch_assoc($result_providers)) {
              $db_providers[] = $row; // Untuk mengisi dropdown
              $db_provider_details[$row['provider_code'] . '_' . $row['provider_type']] = $row; // Key unik: code_type
          }
          $result_providers->free();
      } else {
          $db_provider_error = "Gagal mengambil daftar provider dari database: " . htmlspecialchars($koneksi->error);
      }
  } else {
      $db_provider_error = "Koneksi database tidak valid.";
  }

  // --- Inisialisasi Variabel untuk Tampilan Hasil ---
  $selected_provider_code = '';
  $selected_game_type = ''; // Tipe game yang dipilih dari dropdown
  if (isset($_POST['update_games']) && isset($_POST['provider_code'])) {
      $selected_provider_code = $_POST['provider_code'];
      $selected_game_type = $_POST['game_type_filter'] ?? ''; // Ambil dari dropdown
  } elseif (isset($_GET['provider'])) { // Untuk load dari URL
      $selected_provider_code = mysqli_real_escape_string($koneksi, $_GET['provider']);
      $selected_game_type = mysqli_real_escape_string($koneksi, $_GET['type'] ?? $allowed_telo_game_types[0]); // Default ke tipe pertama jika tidak di URL
      if (!in_array($selected_game_type, $allowed_telo_game_types)) {
          $selected_game_type = $allowed_telo_game_types[0]; // Reset jika tipe di URL tidak valid
      }
  } else {
      $selected_game_type = $allowed_telo_game_types[0]; // Default tipe di awal load halaman
  }

  $current_provider_name_initial = '';
  // Mendapatkan nama provider untuk breadcrumb/header jika provider sudah dipilih
  if (!empty($selected_provider_code)) {
      foreach ($db_providers as $p) {
          if ($p['provider_code'] === $selected_provider_code) {
              $current_provider_name_initial = $p['provider_name'];
              break;
          }
      }
  }


  $game_list_data = null; // Data game yang akan ditampilkan di tabel (khusus single update)
  $api_error_message = ''; // Pesan error API untuk single update
  $raw_api_response_display = 'Tidak ada respon mentah.'; // Respon mentah API
  $database_message = ''; // Pesan untuk status update/insert database game
  $show_results = false; // Flag untuk menampilkan hasil hanya setelah tombol diklik
  $is_one_tap_update = false; // Flag untuk menandai apakah ini proses "One Tap Update"

  // Inisialisasi objek Whitelabel (diasumsikan $WL sudah ada secara global dari diamond-telo.php)
  global $WL; //
  if (!isset($WL) || !($WL instanceof whitelabel)) {
      // Ini adalah error kritis jika class tidak di-load/instantiate dengan benar
      $api_error_message = 'Objek Whitelabel (Telo API) tidak terinisialisasi dengan benar.';
      error_log("CRITICAL: \$WL (Whitelabel) not available in telo_game.php");
  }


  /**
   * Fungsi untuk memproses update/insert game untuk provider tertentu.
   * Ini diisolasi agar bisa dipanggil oleh logika single update atau one-tap update.
   *
   * @param string $provider_code Kode provider yang sedang diproses.
   * @param string $provider_type Tipe provider (misal: 'slot', 'casino'). Ini akan menjadi game_type di telo_gamelist.
   * @param mysqli $koneksi Objek koneksi database.
   * @param whitelabel $WL Objek Whitelabel.
   * @param array $db_providers Array detail provider dari database.
   * @return array [success (bool), api_response (array), db_message (string)]
   */
  function processProviderGamesTelo($provider_code, $provider_type, $koneksi, $WL, $db_providers) {
      $process_success = false;
      $process_api_response = null;
      $process_db_message = '';

      echo "<p>--- Memulai proses untuk provider: <strong>" . htmlspecialchars($provider_code) . "</strong> (Tipe Game: <strong>" . htmlspecialchars($provider_type) . "</strong>) ---</p>";
      ob_flush();
      flush();

      try {
          // Panggil metode gameList() dari objek Whitelabel
          // Telo API::gameList membutuhkan provider_code
          $response = $WL->gameList($provider_code);
          $process_api_response = $response; // Simpan untuk raw display

          echo "<p>Respon API diterima. Memproses " . (count($response['games'] ?? [])) . " game.</p>";
          ob_flush();
          flush();

          if (isset($response['status']) && $response['status'] === 1) {
              $game_list_api_data = $response['games'] ?? [];

              if (!empty($game_list_api_data)) {
                  $inserted_count = 0;
                  $updated_count = 0;
                  $error_db_count = 0;
                  $download_success_count = 0;
                  $download_fail_count = 0;

                  if ($koneksi instanceof mysqli) {
                      $provider_name_for_folder = 'unknown_provider';
                      // Dapatkan nama provider dari $db_providers yang sudah diambil dari DB lokal
                      foreach ($db_providers as $p) {
                          if ($p['provider_code'] === $provider_code && $p['provider_type'] === $provider_type) {
                              $provider_name_for_folder = preg_replace('/[^a-zA-Z0-9_\-]/', '', $p['provider_name']);
                              break;
                          }
                      }

                      $upload_dir_physical = '../upload/source/' . $provider_name_for_folder . '/'; // Folder baru untuk Telo.is game
                      $upload_dir_db_relative = 'upload/source/' . $provider_name_for_folder . '/'; // Path relatif untuk DB

                      if (!is_dir($upload_dir_physical)) {
                          if(mkdir($upload_dir_physical, 0777, true)) {
                              echo "<p>Direktori '$upload_dir_physical' berhasil dibuat.</p>";
                          } else {
                              echo "<p style='color:red;'>Gagal membuat direktori '$upload_dir_physical'. Pastikan izin folder 'upload/game' benar.</p>";
                          }
                          ob_flush(); flush();
                      }

                      // Kolom: provider_code, game_code, game_name, game_type, game_image_local, game_image_url_api, game_status
                      // game_type di sini diambil dari provider_type karena API gameList tidak memberikan game_type per game
                      $stmt = $koneksi->prepare("INSERT INTO telo_gamelist (provider_code, game_code, game_name, game_type, game_image_local, game_image_url_api, game_status) VALUES (?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE game_name = VALUES(game_name), game_type = VALUES(game_type), game_image_local = VALUES(game_image_local), game_image_url_api = VALUES(game_image_url_api), game_status = VALUES(game_status), last_updated = CURRENT_TIMESTAMP");

                      if ($stmt === false) {
                          $process_db_message = '<p style="color:red;"><strong>Kesalahan SQL:</strong> Gagal menyiapkan statement: ' . htmlspecialchars($koneksi->error) . '</p>';
                          error_log("SQL Prepare Error (telo_gamelist): " . $koneksi->error);
                      } else {
                          $game_counter = 0;
                          foreach ($game_list_api_data as $game) {
                              $game_counter++;
                              echo "<p>Memproses game #{$game_counter}: <strong>" . htmlspecialchars($game['game_name'] ?? 'Unknown Game') . "</strong> (Code: " . htmlspecialchars($game['game_code'] ?? 'N/A') . ")</p>";
                              ob_flush();
                              flush();

                              $telo_game_code = $game['game_code'] ?? null;
                              $telo_game_name = $game['game_name'] ?? null;
                              $telo_game_status_api = $game['status'] ?? 0; // Telo.is status: 1 (available) atau 0
                              $telo_game_status = ($telo_game_status_api === 1 || $telo_game_status_api === '1') ? 'active' : 'inactive'; // Konversi ke string

                              $telo_game_image_url_api = $game['banner'] ?? null; // Telo.is pakai 'banner' untuk gambar

                              $game_image_to_save_in_db = null;

                              if (!empty($telo_game_image_url_api)) {
                                  $image_info = pathinfo($telo_game_image_url_api);
                                  $image_extension = $image_info['extension'] ?? 'png';
                                  $local_filename = $telo_game_code . '.' . $image_extension;
                                  $full_local_path_on_server = $upload_dir_physical . $local_filename;
                                  $db_image_path_for_column = $upload_dir_db_relative . $local_filename;

                                  if (file_exists($full_local_path_on_server)) {
                                      $game_image_to_save_in_db = $db_image_path_for_column;
                                      $download_success_count++;
                                      echo "<p style='color: grey;'>Gambar sudah ada di server lokal: " . htmlspecialchars($db_image_path_for_column) . "</p>";
                                  } else {
                                      // Menggunakan cURL untuk unduh gambar
                                      $ch_img = curl_init($telo_game_image_url_api);
                                      curl_setopt($ch_img, CURLOPT_RETURNTRANSFER, true);
                                      curl_setopt($ch_img, CURLOPT_FOLLOWLOCATION, true);
                                      curl_setopt($ch_img, CURLOPT_TIMEOUT, 10);
                                      $image_content = curl_exec($ch_img);
                                      $http_code = curl_getinfo($ch_img, CURLINFO_HTTP_CODE);
                                      curl_close($ch_img);

                                      if ($image_content !== false && $http_code >= 200 && $http_code < 300) {
                                          if (file_put_contents($full_local_path_on_server, $image_content)) {
                                              $game_image_to_save_in_db = $db_image_path_for_column;
                                              $download_success_count++;
                                              echo "<p style='color: green;'>Gambar berhasil diunduh ke: " . htmlspecialchars($db_image_path_for_column) . "</p>";
                                          } else {
                                              $download_fail_count++;
                                              $game_image_to_save_in_db = $telo_game_image_url_api;
                                              echo "<p style='color: orange;'>Gagal menyimpan gambar lokal. Menggunakan URL API: " . htmlspecialchars($telo_game_image_url_api) . "</p>";
                                              error_log("Failed to save image locally for " . $telo_game_code . ": " . $full_local_path_on_server);
                                          }
                                      } else {
                                          $download_fail_count++;
                                          $game_image_to_save_in_db = $telo_game_image_url_api;
                                          echo "<p style='color: orange;'>Gagal mengunduh gambar dari API. Menggunakan URL API: " . htmlspecialchars($telo_game_image_url_api) . " (HTTP Code: ".$http_code.")</p>";
                                          error_log("Failed to download image from URL for " . $telo_game_code . ": " . $telo_game_image_url_api . " (HTTP Code: ".$http_code.")");
                                      }
                                  }
                              } else {
                                  $game_image_to_save_in_db = null;
                                  echo "<p style='color: grey;'>Tidak ada URL gambar dari API untuk game ini.</p>";
                              }
                              ob_flush();
                              flush();

                              if ($telo_game_code !== null && $telo_game_name !== null) {
                                  // Bind parameter: provider_code, game_code, game_name, game_type (dari provider_type), game_image_local, game_image_url_api, game_status
                                  $stmt->bind_param("sssssss", $provider_code, $telo_game_code, $telo_game_name, $provider_type, $game_image_to_save_in_db, $telo_game_image_url_api, $telo_game_status);
                                  if ($stmt->execute()) {
                                      if ($stmt->affected_rows === 1) {
                                          $inserted_count++;
                                          echo "<p style='color: blue;'>Game berhasil di-INSERT ke database.</p>";
                                      } elseif ($stmt->affected_rows === 2) {
                                          $updated_count++;
                                          echo "<p style='color: purple;'>Game berhasil di-UPDATE di database.</p>";
                                      }
                                  } else {
                                      $error_db_count++;
                                      echo "<p style='color: red;'>Gagal INSERT/UPDATE game ke database: " . htmlspecialchars($stmt->error) . "</p>";
                                      error_log("Database error for game " . $telo_game_code . " (" . $provider_code . "/" . $provider_type . "): " . $stmt->error);
                                  }
                              } else {
                                  $error_db_count++;
                                  echo "<p style='color: red;'>Data penting game tidak lengkap. Gagal INSERT/UPDATE. Game Code: " . htmlspecialchars($telo_game_code ?? 'N/A') . "</p>";
                                  error_log("Missing essential data for game. Code: " . ($telo_game_code ?? 'N/A') . " - Full game data: " . json_encode($game));
                              }
                              ob_flush();
                              flush();
                          }
                          $stmt->close();
                          $process_db_message = '<div class="alert alert-success" role="alert">Proses database untuk <strong>' . htmlspecialchars($provider_code) . ' (' . htmlspecialchars($provider_type) . ')</strong> selesai.<br>Insert: <strong>'.$inserted_count.'</strong>, Update: <strong>'.$updated_count.'</strong>, Gagal DB: <strong>'.$error_db_count.'</strong>.<br>Gambar Download Berhasil: <strong>'.$download_success_count.'</strong>, Gagal Download Gambar: <strong>'.$download_fail_count.'</strong>.</div>';
                          $process_success = true;
                      }
                  } else {
                      $process_db_message = '<div class="alert alert-warning" role="alert"><strong>Peringatan:</strong> Objek koneksi database ($koneksi) tidak ditemukan atau tidak valid. Pastikan `koneksi.php` sudah benar. Data tidak disimpan ke database.</div>';
                  }
              } else {
                  $process_db_message = '<div class="alert alert-info" role="alert">Tidak ada data game dari API untuk provider ini.</div>';
                  $process_success = true; // API call was successful, just no games
              }

          } else {
              // API mengembalikan status 0 atau pesan error
              $api_msg_from_telo = $response['msg'] ?? 'Tidak ada pesan dari API.';
              $process_db_message = '<div class="alert alert-danger" role="alert">Gagal mengambil data dari API untuk <strong>' . htmlspecialchars($provider_code) . ' (' . htmlspecialchars($provider_type) . ')</strong>: ' . htmlspecialchars($api_msg_from_telo) . '</div>';
          }
      } catch (Exception $e) {
          $process_db_message = '<div class="alert alert-danger" role="alert">Terjadi kesalahan pada proses API untuk <strong>' . htmlspecialchars($provider_code) . ' (' . htmlspecialchars($provider_type) . ')</strong>: ' . htmlspecialchars($e->getMessage()) . '</div>';
          error_log("Exception in processProviderGamesTelo for " . $provider_code . " (" . $provider_type . "): " . $e->getMessage());
      }
      echo "<p>--- Proses untuk provider: <strong>" . htmlspecialchars($provider_code) . " (" . htmlspecialchars($provider_type) . ")</strong> selesai. ---</p><br>";
      ob_flush();
      flush();

      return ['success' => $process_success, 'api_response' => $process_api_response, 'db_message' => $process_db_message];
  }

  // --- LOGIKA UTAMA PEMANGGILAN API GAME LIST DAN UPDATE DATABASE ---
  if (isset($_POST['update_games']) || isset($_POST['update_all_games'])) {
      $show_results = true;
      
      // Ambil objek Whitelabel (Telo API)
      global $WL; //
      if (!isset($WL) || !($WL instanceof whitelabel)) {
          $api_error_message = 'Objek Whitelabel (Telo API) tidak terinisialisasi dengan benar. Gagal memproses game.';
          error_log("CRITICAL: \$WL (Whitelabel) not available for game processing.");
          $game_list_data = null; // Pastikan data game kosong jika ada error umum
      } else {
          // Logika untuk Single Provider Update
          if (isset($_POST['update_games']) && !empty($selected_provider_code)) {
              echo '<hr class="my-4" />';
              echo '<h4>Status Proses Download & Database:</h4>';
              echo '<div class="progress-log" id="progressLog" style="max-height: 300px; overflow-y: scroll; border: 1px solid #ccc; padding: 10px; background-color: #f9f9f9;">';
              
              // Tentukan provider_type dari DB detail provider untuk single update
              $detail_key = $selected_provider_code . '_' . $selected_game_type; // Gunakan tipe yang dipilih dari dropdown
              $provider_type_for_process = $db_provider_details[$detail_key]['provider_type'] ?? $selected_game_type; // Fallback jika key unik tidak ditemukan

              $process_result = processProviderGamesTelo($selected_provider_code, $provider_type_for_process, $koneksi, $WL, $db_providers);
              
              if (!$process_result['success']) {
                  $api_error_message = "Gagal memproses game untuk provider yang dipilih: " . ($process_result['db_message'] ?? 'Error tidak diketahui.');
              }
              $raw_api_response_display = "<pre>" . htmlspecialchars(json_encode($process_result['api_response'], JSON_PRETTY_PRINT)) . "</pre>";
              
              // Ambil kembali data game dari DB untuk ditampilkan di tabel API result
              $game_list_data_query = mysqli_query($koneksi, "SELECT game_code, game_name, game_image_url_api, game_status, game_type, provider_code FROM telo_gamelist WHERE provider_code = '{$selected_provider_code}' AND game_type = '{$provider_type_for_process}' ORDER BY game_name ASC");
              $game_list_data = [];
              if ($game_list_data_query) {
                  while ($row = mysqli_fetch_assoc($game_list_data_query)) {
                      $game_list_data[] = $row;
                  }
                  $game_list_data_query->free();
              }
              
              echo '</div>'; // Tutup progress-log div
          }
          // Logika untuk One Tap Update (Semua Provider)
          elseif (isset($_POST['update_all_games'])) {
              $is_one_tap_update = true;
              echo '<script>alert("Proses akan memakan waktu beberapa menit (tergantung jumlah provider & game). Jangan tinggalkan halaman ini sampai proses selesai!");</script>';
              ob_flush();
              flush();

              echo '<hr class="my-4" />';
              echo '<h4>Status Proses Download & Database (One Tap Update):</h4>';
              echo '<div class="progress-log" id="progressLog" style="max-height: 500px; overflow-y: scroll; border: 1px solid #ccc; padding: 10px; background-color: #f9f9f9;">';

              if (!empty($db_providers)) {
                  // Kita perlu mengelompokkan provider berdasarkan provider_code dan type
                  // Karena satu provider_code bisa punya beberapa provider_type yang berbeda
                  // Misalnya: {'PRAGMATIC': ['slot', 'casino'], 'ASIA_GAMING': ['casino']}
                  $processed_combinations = []; // Untuk melacak kombinasi code-type yang sudah diproses

                  foreach ($db_providers as $provider_db_entry) {
                      $current_provider_code = $provider_db_entry['provider_code'];
                      $current_provider_type = $provider_db_entry['provider_type']; // Ambil tipe dari DB
                      
                      $combination_key = $current_provider_code . '_' . $current_provider_type;
                      if (in_array($combination_key, $processed_combinations)) {
                          continue; // Lewati jika kombinasi ini sudah diproses
                      }

                      // Panggil fungsi pemrosesan untuk setiap kombinasi provider_code dan provider_type
                      $process_result = processProviderGamesTelo($current_provider_code, $current_provider_type, $koneksi, $WL, $db_providers);
                      
                      // Hanya simpan raw response dari API terakhir yang diproses untuk ditampilkan
                      $raw_api_response_display = "<pre>" . htmlspecialchars(json_encode($process_result['api_response'], JSON_PRETTY_PRINT)) . "</pre>";
                      
                      $processed_combinations[] = $combination_key; // Tandai sudah diproses
                  }
                  echo '<div class="alert alert-success" role="alert"><strong>One Tap Update Selesai!</strong> Semua provider telah diproses. Silakan cek log di atas untuk detail setiap provider.</div>';
              } else {
                  echo '<div class="alert alert-warning" role="alert">Tidak ada provider yang ditemukan di database untuk diproses. Pastikan Anda sudah menjalankan Update & Insert Providers di halaman "Daftar Provider Telo.is".</div>';
              }
              echo '</div>'; // Tutup progress-log div
          }
      }
  }

  // --- Ambil Semua Data Game dari Database untuk Tampilan Tabel Bawah ---
  $all_db_games = [];
  $all_db_games_error = '';
  if (isset($koneksi) && $koneksi instanceof mysqli) {
      $query_all_games = $koneksi->query("SELECT id, provider_code, game_code, game_name, game_type, game_image_local, game_image_url_api, game_status FROM telo_gamelist ORDER BY provider_code, game_name ASC");
      if ($query_all_games) {
          while ($row = $query_all_games->fetch_assoc()) {
              $all_db_games[] = $row;
          }
          $query_all_games->free();
      } else {
          $all_db_games_error = "Gagal mengambil semua daftar game dari database: " . htmlspecialchars($koneksi->error);
      }
  } else {
      $all_db_games_error = "Koneksi database tidak valid untuk mengambil semua game.";
  }
?>
<div class="container-xxl flex-grow-1 container-p-y">
  <h4 class="py-3 mb-4">
    <span class="text-muted fw-light">Menu Utama /</span> Daftar Game Telo.is Berdasarkan Provider
  </h4>

  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-body">
          <p>Pilih provider dari daftar di bawah untuk memperbarui dan menyimpan daftar gamenya ke database Telo.is, beserta gambarnya.</p>

          <?php if (!empty($db_provider_error)): ?>
            <div class="alert alert-danger" role="alert">
              <strong>Error Database Provider:</strong> <?php echo htmlspecialchars($db_provider_error); ?>
              <p>Pastikan Anda sudah menjalankan Update & Insert Providers di halaman "Daftar Provider Telo.is" untuk mengisi dropdown ini.</p>
            </div>
          <?php else: ?>
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="providerSelect" class="form-label">Pilih Provider:</label>
                    <select class="form-select" id="providerSelect" name="provider_code">
                        <option value="">-- Pilih Provider --</option>
                        <?php
                        // Mengelompokkan provider berdasarkan tipe untuk tampilan dropdown yang lebih rapi
                        $grouped_providers = [];
                        foreach ($db_providers as $p) {
                            $grouped_providers[$p['provider_type']][] = $p;
                        }
                        // Loop melalui allowed_telo_game_types untuk membuat optgroup
                        foreach ($allowed_telo_game_types as $type_group) {
                            if (isset($grouped_providers[$type_group])) {
                                echo '<optgroup label="' . htmlspecialchars(str_replace('_', ' ', strtoupper($type_group))) . '">';
                                foreach ($grouped_providers[$type_group] as $provider) {
                                    $option_value = htmlspecialchars($provider['provider_code']); // Value untuk POST
                                    $option_text = htmlspecialchars($provider['provider_name']);
                                    // Pilihan default jika sudah ada di URL
                                    $selected = ($selected_provider_code === $provider['provider_code'] && $selected_game_type === $provider['provider_type']) ? 'selected' : '';
                                    echo '<option value="' . $option_value . '" data-type="' . htmlspecialchars($provider['provider_type']) . '" ' . $selected . '>' . $option_text . '</option>';
                                }
                                echo '</optgroup>';
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="gameTypeFilterSelect" class="form-label">Filter Tipe Game (Saat Update Single):</label>
                    <select class="form-select" id="gameTypeFilterSelect" name="game_type_filter">
                        <?php foreach ($allowed_telo_game_types as $type_option): ?>
                            <option value="<?php echo htmlspecialchars($type_option); ?>"
                                <?php echo ($selected_game_type === $type_option) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars(str_replace('_', ' ', strtoupper($type_option))); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" name="update_games" class="btn btn-primary mb-3">Update and Insert Games To Database</button>
                <button type="submit" name="update_all_games" class="btn btn-info mb-3 ms-2" onclick="return confirm('Apakah Anda yakin ingin melakukan One Tap Update untuk SEMUA provider? Proses ini akan memakan waktu beberapa menit dan jangan tinggalkan halaman ini.');">One Tap Update (Update All Providers)</button>
            </form>
          <?php endif; ?>

          <?php if ($show_results): // Tampilkan hasil hanya jika tombol sudah diklik ?>
              <?php if (!empty($api_error_message)): ?>
                <div class="alert alert-danger mt-3" role="alert">
                  <strong>Error API:</strong> <?php echo htmlspecialchars($api_error_message); ?>
                </div>
              <?php elseif ($game_list_data !== null && $game_list_data !== false): ?>
                <hr class="my-4" />
                <h4>Daftar Game yang Diambil dari API (untuk referensi - Single Update):</h4>
                <div class="table-responsive text-nowrap">
                  <table class="table table-bordered">
                    <thead>
                      <tr>
                        <th>Kode Game</th>
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
                          // Gunakan key array asosiatif ($game['key']) karena json_decode($res, true) di diamond-telo.php
                          $display_game_type = $game['game_type'] ?? 'N/A'; // Dari kolom DB telo_gamelist (disimpan dari provider_type)
                          $temp_provider_name_for_folder = 'unknown_provider';
                          // Dapatkan nama provider dari $db_provider_details yang sudah diambil dari DB lokal
                          if (isset($db_provider_details[$game['provider_code'] . '_' . $game['game_type']])) {
                             $temp_provider_name_for_folder = preg_replace('/[^a-zA-Z0-9_\-]/', '', $db_provider_details[$game['provider_code'] . '_' . $game['game_type']]['provider_name']);
                          }
                          $temp_game_code = $game['game_code'] ?? 'N/A';
                          
                          $temp_image_url_for_display = $game['game_image_url_api'] ?? null; // Dari kolom DB telo_gamelist
                          $temp_image_ext = pathinfo($temp_image_url_for_display ?? '', PATHINFO_EXTENSION);
                          $temp_image_ext = !empty($temp_image_ext) ? $temp_image_ext : 'png';
                          
                          // Path fisik lokal untuk cek keberadaan file (dari root web)
                          $temp_local_path_check = '../upload/source/' . $temp_provider_name_for_folder . '/' . $temp_game_code . '.' . $temp_image_ext;

                          $game_image_local_display = '';
                          // Path relatif dari web root yang kita harapkan di DB
                          $expected_db_local_path = 'upload/source/' . $temp_provider_name_for_folder . '/' . $temp_game_code . '.' . $temp_image_ext;

                          if(file_exists($temp_local_path_check)) {
                              $game_image_local_display = '<img src="../' . htmlspecialchars($expected_db_local_path) . '" alt="Lokal" style="max-width: 50px; height: auto;">';
                          } else if (!empty($temp_image_url_for_display)) {
                              $game_image_local_display = 'Menggunakan <a href="'.htmlspecialchars($temp_image_url_for_display).'" target="_blank">URL API</a> / Gagal Unduh';
                          } else {
                              $game_image_local_display = 'Tidak Ada Gambar';
                          }
                        ?>
                          <tr>
                            <td><strong><?php echo htmlspecialchars($game['game_code'] ?? 'N/A'); ?></strong></td>
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
                                $status_text = $game['game_status'] ?? 'unknown';
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
                <?php if ($is_one_tap_update): ?>
                  (Menampilkan respon API dari provider terakhir yang diproses dalam One Tap Update.)
                <?php endif; ?>
              </p>
              <?php echo $raw_api_response_display; ?>

          <?php endif; // End of $show_results ?>

          <hr class="my-4" />

          <h4>Daftar Semua Game dari Database:</h4>
          <?php if (!empty($all_db_games_error)): ?>
            <div class="alert alert-danger mt-3" role="alert">
              <strong>Error:</strong> <?php echo htmlspecialchars($all_db_games_error); ?>
            </div>
          <?php elseif (!empty($all_db_games)): ?>
            <div class="table-responsive text-nowrap">
              <table class="table table-bordered">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Kode Provider</th>
                    <th>Nama Provider</th>
                    <th>Kode Game</th>
                    <th>Nama Game</th>
                    <th>Tipe Game</th>
                    <th>Gambar Lokal</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                  <?php foreach ($all_db_games as $game):
                      // Dapatkan nama provider dari $db_provider_details (perhatikan kunci unik code_type)
                      $provider_name_from_db = $db_provider_details[$game['provider_code'] . '_' . $game['game_type']]['provider_name'] ?? 'N/A';
                  ?>
                    <tr>
                      <td><?php echo htmlspecialchars($game['id']); ?></td>
                      <td><strong><?php echo htmlspecialchars($game['provider_code']); ?></strong></td>
                      <td><?php echo htmlspecialchars($provider_name_from_db); ?></td>
                      <td><?php echo htmlspecialchars($game['game_code']); ?></td>
                      <td><?php echo htmlspecialchars($game['game_name']); ?></td>
                      <td><?php echo htmlspecialchars($game['game_type']); ?></td>
                      <td>
                        <?php if (!empty($game['game_image_local'])): ?>
                          <img src="../<?php echo htmlspecialchars($game['game_image_local']); ?>" alt="Gambar Lokal" style="max-width: 80px; height: auto;">
                        <?php else: ?>
                          Tidak Ada
                        <?php endif; ?>
                      </td>
                      <td>
                        <?php
                          $status_text = $game['game_status'] ?? 'unknown';
                          $status_color = ($status_text === 'active') ? 'badge bg-label-success' : 'badge bg-label-danger';
                        ?>
                        <span class="<?php echo $status_color; ?> me-1"><?php echo htmlspecialchars($status_text); ?></span>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <p>Tidak ada game di database. Klik tombol 'Update and Insert Games To Database' di atas untuk mengisinya.</p>
          <?php endif; ?>

        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
    const gameListContainer = document.getElementById('game-list-container');
    const gamesMessage = document.getElementById('games-message');
    const gameSearchInputMain = document.getElementById('game-search-input-main');
    const providerSelect = document.getElementById('providerSelect');
    const gameTypeFilterSelect = document.getElementById('gameTypeFilterSelect'); // Dropdown filter tipe game

    let currentProviderCode = '<?php echo htmlspecialchars($selected_provider_code); ?>';
    let currentProviderType = '<?php echo htmlspecialchars($selected_game_type); ?>'; // Dari URL atau default
    let currentSearchTerm = '';
    let isLoading = false;
    let searchDebounceTimer;

    // Fungsi untuk memuat game
    function fetchGames(providerCode, gameType, searchTermQuery = '') {
        if (!providerCode) {
            // Jika tidak ada provider code, jangan fetch, kosongkan saja
            gameListContainer.innerHTML = '<p class="col-span-full text-center py-10">Pilih provider di atas untuk melihat daftar game.</p>';
            gamesMessage.textContent = 'Pilih provider untuk melihat game.';
            return;
        }
        if (isLoading) return;
        isLoading = true;

        gameListContainer.innerHTML = '<p class="col-span-full text-center py-10 flex items-center justify-center"><span class="animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2"></span> Memuat game...</p>';
        gamesMessage.textContent = 'Memuat game...';

        const searchParam = searchTermQuery ? `&search=${encodeURIComponent(searchTermQuery)}` : '';
        // URL AJAX memanggil file baru untuk Telo.is games
        const url = `ajax/telo_game_gamelist.php?provider_code=${encodeURIComponent(providerCode)}&game_type=${encodeURIComponent(gameType)}${searchParam}&_=${new Date().getTime()}`;

        fetch(url)
            .then(response => {
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status} dari ${url}`);
                return response.json();
            })
            .then(data => {
                gameListContainer.innerHTML = '';
                if (data.success) {
                    gameListContainer.insertAdjacentHTML('beforeend', data.gamesHtml);
                    gamesMessage.textContent = `Menampilkan ${data.totalGamesOverall} game untuk ${data.providerName} (Tipe: ${gameType.replace(/_/g, ' ')}).`;
                    if (searchTermQuery) {
                        gamesMessage.textContent = `Menampilkan ${data.totalGamesOverall} game untuk "${searchTermQuery}" dari ${data.providerName} (Tipe: ${gameType.replace(/_/g, ' ')}).`;
                    }
                    // Update breadcrumb jika ini adalah tampilan game
                    if (document.getElementById('breadcrumb-provider-name')) {
                        document.querySelector('.current-provider-breadcrumb-text').textContent = data.providerName;
                        document.getElementById('breadcrumb-provider-name').classList.remove('hidden');
                    }
                } else {
                    gameListContainer.innerHTML = `<p class="col-span-full text-center py-5 text-red-500">Gagal memuat game: ${data.message || 'Error tidak diketahui.'}</p>`;
                    gamesMessage.textContent = data.message || 'Error memuat game.';
                }
            })
            .catch(error => {
                gameListContainer.innerHTML = `<p class="col-span-full text-center py-5 text-red-500">Terjadi kesalahan jaringan: ${error.message}.</p>`;
                gamesMessage.textContent = `Error jaringan: ${error.message}.`;
                console.error('Error fetching games:', error);
            })
            .finally(() => {
                isLoading = false;
            });
    }

    // Event listener untuk perubahan pada dropdown provider
    if (providerSelect) {
        providerSelect.addEventListener('change', function() {
            // Dapatkan provider code dari value yang dipilih
            const selectedOption = this.options[this.selectedIndex];
            currentProviderCode = selectedOption.value;
            // Dapatkan tipe game dari atribut data-type pada option yang dipilih
            // Jika option tidak memiliki data-type (misal option "--Pilih Provider--"), fallback ke gameTypeFilterSelect.value
            currentProviderType = selectedOption.dataset.type || gameTypeFilterSelect.value; 

            // Redirect dengan parameter URL baru agar halaman memuat ulang dengan provider dan tipe yang benar
            // Ini akan memastikan breadcrumb, header, dan dropdown filter tipe terisi dengan benar saat halaman load.
            if (currentProviderCode) {
                window.location.href = `telo_game.php?provider=${encodeURIComponent(currentProviderCode)}&type=${encodeURIComponent(currentProviderType)}`;
            } else {
                window.location.href = `telo_game.php`; // Kembali ke halaman daftar provider
            }
        });
    }

    // Event listener untuk dropdown pemilihan tipe game (hanya aktif di tampilan game)
    if (gameTypeFilterSelect) {
        gameTypeFilterSelect.addEventListener('change', function() {
            currentProviderType = this.value; // Update tipe game yang sedang dipilih
            if (currentProviderCode) { // Pastikan provider sudah dipilih
                fetchGames(currentProviderCode, currentProviderType, currentSearchTerm);
            }
        });
    }

    // Event listener untuk pencarian game (hanya muncul saat provider sudah dipilih)
    if(gameSearchInputMain) {
        gameSearchInputMain.addEventListener('input', function() {
            clearTimeout(searchDebounceTimer);
            const searchTerm = this.value.trim();
            searchDebounceTimer = setTimeout(() => {
                if (searchTerm !== currentSearchTerm) {
                    currentSearchTerm = searchTerm;
                    fetchGames(currentProviderCode, currentProviderType, currentSearchTerm);
                }
            }, 700);
        });
    }

    // Event listener untuk klik game
    const isLoggedIn = <?php echo isset($_SESSION['id_anggota']) ? 'true' : 'false'; ?>;
    const notLoggedInMessage = 'Silakan login untuk bermain.'; // Default message jika isi_1_popup_teks_belum_login_web tidak ada

    if (typeof registerPopup === 'undefined') {
        window.registerPopup = function(options) {
            alert(options.content || 'Pesan popup.');
        };
    }
    
    if(gameListContainer) {
        gameListContainer.addEventListener('click', function(event) {
            const targetLink = event.target.closest('.play-game-trigger');

            if (targetLink) {
                event.preventDefault();

                if (!isLoggedIn) {
                    if (typeof registerPopup === 'function') {
                        registerPopup({ content: notLoggedInMessage });
                    } else {
                        alert(notLoggedInMessage);
                    }
                    return;
                }
                
                // Asumsi elemen loading ada di halaman utama atau header
                const pageLoadingIndicator = document.getElementById('pageFullLoadingIndicator');
                if (pageLoadingIndicator) pageLoadingIndicator.style.display = 'flex';

                const gameCode = targetLink.dataset.gameCode;
                const providerCode = targetLink.dataset.provider;
                const gameType = targetLink.dataset.gameType; // Ambil tipe game dari dataset link

                // URL untuk meluncurkan game Telo.is
                const basePlayUrl = "playgame/play-telo-game.php"; // Ini file baru yang harus Anda buat
                const finalUrl = `${basePlayUrl}?game_code=${gameCode}&provider_code=${providerCode}&game_type=${gameType}`;
                
                window.location.href = finalUrl;
            }
        });
    }

    // --- Pemanggilan Awal Saat Halaman Dimuat ---
    // Hanya panggil fetchGames jika ada provider yang dipilih di URL
    if (currentProviderCode) {
        fetchGames(currentProviderCode, currentProviderType, currentSearchTerm);
        // Atur placeholder pencarian jika ada provider
        if (gameSearchInputMain && '<?php echo htmlspecialchars($current_provider_name_initial ?? ''); ?>') {
            gameSearchInputMain.placeholder = `Cari di <?php echo htmlspecialchars($current_provider_name_initial ?? ''); ?>...`;
        }
    } else {
        // Jika tidak ada provider di URL, tampilan default adalah daftar provider
        // Initial breadcrumb update if no provider is selected
        if (document.getElementById('breadcrumb-provider-name')) {
            document.getElementById('breadcrumb-provider-name').classList.add('hidden');
        }
        // Pastikan dropdown filter tipe game tidak terlihat di tampilan provider list
        if (gameTypeFilterSelect) {
             gameTypeFilterSelect.closest('.mb-3').style.display = 'none'; // Sembunyikan div induknya
        }
        if (gameSearchInputMain) {
            gameSearchInputMain.closest('.relative.flex.items-center').style.display = 'none'; // Sembunyikan div induknya
        }
    }
});
</script>

<?php
// Pastikan semua output buffer dibersihkan di akhir script
ob_end_flush();
?>