<?php
  // Aktifkan pelaporan kesalahan PHP untuk debugging (HAPUS INI DI PRODUKSI)
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);

  // Memulai session, jika belum dimulai di koneksi.php
  if (session_status() == PHP_SESSION_NONE) {
      session_start();
  }

  // Sertakan file koneksi.php terlebih dahulu, karena mengandung $alamat_admin dan $koneksi
  include_once '../koneksi.php';

  // SERTKAN FILE KELAS GameXaAPI
  // Pastikan path ini benar sesuai lokasi class.exa.php Anda
  include_once '../classes/class.exa.php';

  // --- Pengalihan jika admin belum login ---
  // Variabel $alamat_admin didapatkan dari koneksi.php
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

  // --- Inisialisasi Variabel untuk Tampilan ---
  $database_message = ''; // Pesan untuk status update/insert database
  
  // --- Ambil semua kombinasi unik provider_code dan game_type dari srg_gamelist ---
  // Ini akan menjadi basis data utama untuk provider_code dan provider_type yang akan disimpan
  $gamelist_entries_for_insert = []; 
  $game_summary_error = ''; // Untuk pesan error terkait pengambilan data ringkasan

  if (isset($koneksi) && $koneksi instanceof mysqli) {
      // Ambil DISTINCT provider_code dan game_type untuk memastikan setiap kombinasi unik
      $query_gamelist_data = "SELECT DISTINCT provider_code, game_type FROM srg_gamelist ORDER BY provider_code, game_type";
      $result_gamelist_data = $koneksi->query($query_gamelist_data);
      if ($result_gamelist_data) {
          while ($row = $result_gamelist_data->fetch_assoc()) {
              $gamelist_entries_for_insert[] = [
                  'provider_code' => $row['provider_code'],
                  'provider_type' => $row['game_type'] // Ini adalah nilai untuk provider_type
              ];
          }
          $result_gamelist_data->free();
      } else {
          $game_summary_error = "Gagal mengambil jenis game unik dari database: " . htmlspecialchars($koneksi->error);
      }

      // Ambil data ringkasan game per provider/game_type/jumlah game untuk tampilan tabel ringkasan
      $game_summary_data = []; // Reset atau pastikan ini diisi hanya untuk tampilan
      $query_summary_for_display = "SELECT game_type, provider_code, COUNT(*) AS total_games FROM srg_gamelist GROUP BY game_type, provider_code ORDER BY game_type, provider_code";
      $result_summary_for_display = $koneksi->query($query_summary_for_display);
      if ($result_summary_for_display) {
          while ($row = $result_summary_for_display->fetch_assoc()) {
              $game_summary_data[] = $row;
          }
          $result_summary_for_display->free();
      } else {
          $game_summary_error .= (!empty($game_summary_error) ? '<br>' : '') . "Gagal mengambil data ringkasan game untuk tampilan: " . htmlspecialchars($koneksi->error);
      }

  } else {
      $game_summary_error = "Koneksi database tidak valid.";
  }

  // --- Logika Pemanggilan API GameXaAPI dan Update Database ---
  if (isset($_POST['update_providers_and_db'])) {
      try {
          $response = $gameXaAPI->getGameProviders();
          $api_providers_map = []; // Map API providers by code for quick lookup

          if ($response['success'] && isset($response['data']['providers'])) {
              foreach ($response['data']['providers'] as $api_provider) {
                  // Pastikan provider_code ada sebelum menyimpan ke map
                  if (isset($api_provider['provider_code'])) {
                      $api_providers_map[$api_provider['provider_code']] = $api_provider;
                  }
              }
          } else {
              $api_error_message = 'Gagal mengambil daftar provider dari API GameXa. Pesan: ' . ($response['message'] ?? 'Tidak diketahui.');
              $database_message = '<div class="alert alert-danger" role="alert">'.$api_error_message.'</div>';
          }

          if (isset($koneksi) && $koneksi instanceof mysqli) {
              $inserted_count = 0;
              $updated_count = 0;
              $error_db_count = 0;

              // Gunakan INSERT ... ON DUPLICATE KEY UPDATE karena kita memiliki UNIQUE KEY pada (provider_code, provider_type)
              $stmt = $koneksi->prepare("INSERT INTO srg_provider (provider_code, provider_name, provider_type, provider_status, provider_image) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE provider_name = VALUES(provider_name), provider_status = VALUES(provider_status), provider_image = VALUES(provider_image), last_updated = CURRENT_TIMESTAMP");

              if ($stmt === false) {
                  $database_message = '<div class="alert alert-danger" role="alert"><strong>Kesalahan SQL:</strong> Gagal menyiapkan statement: ' . htmlspecialchars($koneksi->error) . '</div>';
              } else {
                  // Iterasi melalui kombinasi provider_code dan provider_type dari srg_gamelist
                  foreach ($gamelist_entries_for_insert as $entry) {
                      $code = $entry['provider_code'];
                      $type = $entry['provider_type']; // Tipe diambil langsung dari gamelist

                      $name = 'Unknown Provider'; // Nama default jika tidak ditemukan di API
                      $status = 'inactive'; // Status default
                      $image_url = null; // Gambar default

                      // Coba cocokkan dengan data dari API menggunakan provider_code
                      if (isset($api_providers_map[$code])) {
                          $api_data = $api_providers_map[$code];
                          // Ambil nama, status, gambar dari API jika ada, jika tidak, gunakan default
                          $name = $api_data['provider_name'] ?? $name;
                          $status = $api_data['status'] ?? $status;
                          $image_url = $api_data['logo_url'] ?? $image_url;
                      }

                      // Sisipkan atau perbarui data
                      $stmt->bind_param("sssss", $code, $name, $type, $status, $image_url);
                      if ($stmt->execute()) {
                          if ($stmt->affected_rows === 1) $inserted_count++; // Baru dimasukkan
                          elseif ($stmt->affected_rows === 2) $updated_count++; // Diperbarui
                      } else {
                          $error_db_count++;
                          // Opsional: log $stmt->error for debugging
                          // error_log("Database error for provider " . $code . " type " . $type . ": " . $stmt->error);
                      }
                  }
                  $stmt->close();
                  $database_message = '<div class="alert alert-success" role="alert">Proses database selesai. Insert: <strong>'.$inserted_count.'</strong>, Update: <strong>'.$updated_count.'</strong>, Gagal: <strong>'.$error_db_count.'</strong>.</div>';
              }
          } else {
              $database_message = '<div class="alert alert-warning" role="alert"><strong>Peringatan:</strong> Objek koneksi database ($koneksi) tidak ditemukan.</div>';
          }
      } catch (Exception $e) {
          $database_message = '<div class="alert alert-danger" role="alert">Terjadi kesalahan pada proses API: ' . htmlspecialchars($e->getMessage()) . '</div>';
      }
  }

  // --- Ambil Semua Data Provider dari Database untuk Tampilan Tabel ---
  $all_db_providers = [];
  $all_db_providers_error = '';
  if (isset($koneksi) && $koneksi instanceof mysqli) {
      // Mengambil semua data untuk tampilan, termasuk multiple entries per provider_code
      $query_all_providers = $koneksi->query("SELECT id, provider_code, provider_name, provider_type, provider_status, provider_image FROM srg_provider ORDER BY provider_name ASC, provider_type ASC");
      if ($query_all_providers) {
          while ($row = $query_all_providers->fetch_assoc()) {
              $all_db_providers[] = $row;
          }
          $query_all_providers->free();
      } else {
          $all_db_providers_error = "Gagal mengambil daftar provider dari database: " . htmlspecialchars($koneksi->error);
      }
  } else {
      $all_db_providers_error = "Koneksi database tidak valid.";
  }

  // Bagian tampilan untuk "Ringkasan Game per Provider" tetap sama, karena sudah mengambil dari database.
?>
<div class="container-xxl flex-grow-1 container-p-y">
  <h4 class="py-3 mb-4">
    <span class="text-muted fw-light">Menu Utama /</span> Daftar Provider GameXa
  </h4>

  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-body">
          <p>Klik tombol di bawah untuk mengambil daftar provider game dari API GameXa dan menyimpannya ke database.</p>

          <form method="POST" action="">
              <button type="submit" name="update_providers_and_db" class="btn btn-primary mb-3">Update & Insert Providers From API to Database</button>
          </form>

          <?php echo $database_message; // Tampilkan pesan status database ?>

          <hr class="my-4" />

          <h4>Daftar Semua Provider dari Database:</h4>
          <?php if (!empty($all_db_providers_error)): ?>
            <div class="alert alert-danger mt-3" role="alert">
              <strong>Error:</strong> <?php echo htmlspecialchars($all_db_providers_error); ?>
            </div>
          <?php elseif (!empty($all_db_providers)): ?>
            <div class="table-responsive text-nowrap">
              <table class="table table-bordered">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Kode Provider</th>
                    <th>Nama Provider</th>
                    <th>Tipe</th>
                    <th>Status</th>
                    <th>Gambar Lokal</th>
                  </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                  <?php foreach ($all_db_providers as $provider): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($provider['id']); ?></td>
                      <td><strong><?php echo htmlspecialchars($provider['provider_code']); ?></strong></td>
                      <td><?php echo htmlspecialchars($provider['provider_name']); ?></td>
                      <td><?php echo htmlspecialchars($provider['provider_type']); ?></td>
                      <td>
                        <?php
                          $status_text = $provider['provider_status'] ?? 'unknown';
                          $status_color = ($status_text === 'active') ? 'badge bg-label-success' : 'badge bg-label-danger';
                        ?>
                        <span class="<?php echo $status_color; ?> me-1"><?php echo htmlspecialchars($status_text); ?></span>
                      </td>
                      <td>
                        <?php if (!empty($provider['provider_image'])): ?>
                          <img src="<?php echo htmlspecialchars($provider['provider_image']); ?>" alt="Gambar Provider" style="max-width: 80px; height: auto;">
                        <?php else: ?>
                          Tidak Ada
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <p>Tidak ada provider di database. Klik tombol 'Update & Insert Providers From API to Database' di atas untuk mengisinya.</p>
          <?php endif; ?>

          <hr class="my-4" />
          <h4>Ringkasan Game per Provider</h4>
          <?php if (!empty($game_summary_error)): ?>
            <div class="alert alert-danger mt-3" role="alert">
              <strong>Error:</strong> <?php echo htmlspecialchars($game_summary_error); ?>
            </div>
          <?php elseif (!empty($game_summary_data)): ?>
            <div class="table-responsive text-nowrap">
              <table class="table table-bordered">
                <thead>
                  <tr>
                    <th>Tipe Game</th>
                    <th>Kode Provider</th>
                    <th>Jumlah Game</th>
                  </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                  <?php foreach ($game_summary_data as $summary_row): ?>
                    <tr>
                      <td><strong><?php echo htmlspecialchars($summary_row['game_type']); ?></strong></td>
                      <td><?php echo htmlspecialchars($summary_row['provider_code']); ?></td>
                      <td><?php echo htmlspecialchars($summary_row['total_games']); ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <p>Tidak ada data ringkasan game yang ditemukan di tabel `srg_gamelist`.</p>
          <?php endif; ?>
          </div>
      </div>
    </div>
  </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>