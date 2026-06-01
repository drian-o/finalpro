<?php
  // Aktifkan pelaporan kesalahan PHP untuk debugging (HAPUS INI DI PRODUKSI)
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);

  // Memulai session, jika belum dimulai di koneksi.php
  if (session_status() == PHP_SESSION_NONE) {
      session_start();
  }

  include_once '../koneksi.php'; // Path ke file koneksi Anda
  include_once '../classes/diamond-telo.php'; // Sertakan file kelas whitelabel (telo)

  // Pastikan variabel $alamat_admin didefinisikan di koneksi.php
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

  // --- Inisialisasi Variabel untuk Tampilan ---
  $provider_list_data = null;
  $api_error_message = '';
  $raw_api_response_display = 'Tidak ada respon mentah.';
  $database_message = ''; // Pesan untuk status update/insert database
  $show_results = false; // Flag untuk menampilkan hasil hanya setelah tombol diklik

  // Inisialisasi objek Whitelabel (diasumsikan $WL sudah ada secara global dari diamond-telo.php)
  global $WL; //
  if (!isset($WL) || !($WL instanceof whitelabel)) {
      // Ini adalah error kritis jika class tidak di-load/instantiate dengan benar
      $api_error_message = 'Objek Whitelabel (Telo API) tidak terinisialisasi dengan benar.';
      error_log("CRITICAL: \$WL (Whitelabel) not available in telo_provider.php");
  }


  // --- Logika Pemanggilan API Telo.is dan Update Database ---
  if (isset($_POST['update_providers_and_db'])) { // Tombol yang akan memicu proses
      $show_results = true; // Aktifkan tampilan hasil

      if (!isset($WL) || !($WL instanceof whitelabel)) {
          $database_message = '<div class="alert alert-danger" role="alert"><strong>Kesalahan:</strong> API Whitelabel tidak siap.</div>';
          $raw_api_response_display = "<pre>API Whitelabel object is not available.</pre>";
      } else {
          try {
              // Panggil metode providerList() dari objek Whitelabel
              // Ini akan mengembalikan array asosiatif PHP (karena json_decode($res, true))
              $response = $WL->providerList();

              // Simpan respons mentah untuk ditampilkan
              $raw_api_response_display = "<pre>" . htmlspecialchars(json_encode($response, JSON_PRETTY_PRINT)) . "</pre>";

              // Cek jika respons API sukses (status: 1)
              if (isset($response['status']) && $response['status'] === 1) {
                  $provider_list_data = $response['providers'] ?? []; // Data provider ada di key 'providers'

                  // --- Proses Update/Insert ke Database ---
                  if (!empty($provider_list_data)) {
                      $inserted_count = 0;
                      $updated_count = 0;
                      $error_db_count = 0;

                      // Pastikan objek koneksi database ($koneksi) tersedia dan valid
                      if (isset($koneksi) && $koneksi instanceof mysqli) {
                          // Siapkan statement untuk INSERT ON DUPLICATE KEY UPDATE
                          // Sesuaikan dengan kolom tabel telo_provider dan nama key dari API Telo.is
                          $stmt = $koneksi->prepare("INSERT INTO telo_provider (provider_code, provider_name, provider_type, provider_status) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE provider_name = VALUES(provider_name), provider_status = VALUES(provider_status), last_updated = CURRENT_TIMESTAMP");

                          if ($stmt === false) {
                              $database_message = '<div class="alert alert-danger" role="alert"><strong>Kesalahan SQL:</strong> Gagal menyiapkan statement: ' . htmlspecialchars($koneksi->error) . '</div>';
                              error_log("SQL Prepare Error (telo_provider): " . $koneksi->error);
                          } else {
                              foreach ($provider_list_data as $provider) {
                                  $code = $provider['code'] ?? null;
                                  $name = $provider['name'] ?? null;
                                  $type = $provider['type'] ?? null; // Tipe game dari API
                                  $status = 'active'; // Default ke 'active' karena API tidak memberikan status provider secara eksplisit

                                  // Lanjutkan hanya jika data penting tidak null
                                  if ($code !== null && $name !== null && $type !== null) {
                                      $stmt->bind_param("ssss", $code, $name, $type, $status);
                                      if ($stmt->execute()) {
                                          if ($stmt->affected_rows === 1) {
                                              $inserted_count++;
                                          } elseif ($stmt->affected_rows === 2) { // 2 jika updated
                                              $updated_count++;
                                          }
                                      } else {
                                          $error_db_count++;
                                          error_log("Database error for telo_provider " . $code . " (" . $type . "): " . $stmt->error);
                                      }
                                  } else {
                                      $error_db_count++;
                                      error_log("Missing data for telo_provider. Code: " . ($code ?? 'N/A') . ", Type: " . ($type ?? 'N/A') . " - Full provider data: " . json_encode($provider));
                                  }
                              }
                              $stmt->close();
                              $database_message = '<div class="alert alert-success" role="alert">Proses database selesai. Insert: <strong>'.$inserted_count.'</strong>, Update: <strong>'.$updated_count.'</strong>, Gagal: <strong>'.$error_db_count.'</strong>.</div>';
                          }
                      } else {
                          $database_message = '<div class="alert alert-warning" role="alert"><strong>Peringatan:</strong> Objek koneksi database ($koneksi) tidak ditemukan atau tidak valid. Pastikan `koneksi.php` sudah benar. Data tidak disimpan ke database.</div>';
                      }
                  } else {
                      $database_message = '<div class="alert alert-info" role="alert">Tidak ada data provider dari API untuk disimpan.</div>';
                  }

              } else {
                  // API mengembalikan status 0 atau tidak ada status
                  $api_error_message = $response['msg'] ?? 'Gagal mengambil daftar provider dari Telo.is. Pesan API tidak tersedia atau status bukan sukses.';
                  $database_message = '<div class="alert alert-danger" role="alert">Gagal mengambil data dari API: ' . htmlspecialchars($api_error_message) . '</div>';
              }
          } catch (Exception $e) {
              $api_error_message = "Terjadi kesalahan saat memanggil API Telo.is: " . $e->getMessage();
              $raw_api_response_display = "<pre>Error mengambil respon mentah: " . htmlspecialchars($e->getMessage()) . "</pre>";
              $database_message = '<div class="alert alert-danger" role="alert">Terjadi kesalahan pada proses API: ' . htmlspecialchars($e->getMessage()) . '</div>';
          }
      }
  }

  // --- Ambil Semua Data Provider dari Database untuk Tampilan Tabel ---
  $all_db_providers = [];
  $all_db_providers_error = '';
  if (isset($koneksi) && $koneksi instanceof mysqli) {
      // Mengambil dari tabel telo_provider
      $query_all_providers = $koneksi->query("SELECT id, provider_code, provider_name, provider_type, provider_status, provider_image FROM telo_provider ORDER BY provider_name ASC, provider_type ASC");
      if ($query_all_providers) {
          while ($row = $query_all_providers->fetch_assoc()) {
              $all_db_providers[] = $row;
          }
          $query_all_providers->free();
      } else {
          $all_db_providers_error = "Gagal mengambil semua daftar provider dari database: " . htmlspecialchars($koneksi->error);
      }
  } else {
      $all_db_providers_error = "Koneksi database tidak valid untuk mengambil semua provider.";
  }
?>
<div class="container-xxl flex-grow-1 container-p-y">
  <h4 class="py-3 mb-4">
    <span class="text-muted fw-light">Menu Utama /</span> Daftar Provider Game Telo.is
  </h4>

  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-body">
          <p>Klik tombol di bawah untuk memperbarui dan menyimpan daftar provider game Telo.is ke database dari API.</p>

          <form method="POST" action="">
              <button type="submit" name="update_providers_and_db" class="btn btn-primary mb-3">Update and Insert From API To Database</button>
          </form>

          <?php echo $database_message; // Tampilkan pesan status database dari API update ?>

          <?php if ($show_results): // Tampilkan hasil API update hanya jika tombol sudah diklik ?>
              <hr class="my-4" />

              <?php if (!empty($api_error_message)): ?>
                <div class="alert alert-danger mt-3" role="alert">
                  <strong>Error API:</strong> <?php echo htmlspecialchars($api_error_message); ?>
                </div>
              <?php elseif ($provider_list_data !== null && $provider_list_data !== false): ?>
                <h4>Daftar Provider yang Diambil dari API (untuk referensi):</h4>
                <div class="table-responsive text-nowrap">
                  <table class="table table-bordered">
                    <thead>
                      <tr>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Tipe</th>
                      </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                      <?php if (!empty($provider_list_data)): ?>
                        <?php foreach ($provider_list_data as $provider): ?>
                          <tr>
                            <td><strong><?php echo htmlspecialchars($provider['code'] ?? 'N/A'); ?></strong></td>
                            <td><?php echo htmlspecialchars($provider['name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($provider['type'] ?? 'N/A'); ?></td>
                          </tr>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <tr>
                          <td colspan="3">Tidak ada data provider yang ditemukan dari API.</td>
                        </tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              <?php else: ?>
                  <div class="alert alert-warning mt-3" role="alert">
                      Tidak dapat mengambil data provider. Silakan coba lagi atau periksa log.
                  </div>
              <?php endif; ?>

              <hr class="my-4" />

              <h3>Respon Mentah API:</h3>
              <?php echo $raw_api_response_display; ?>

          <?php endif; // End of $show_results ?>

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
                    <th>Aksi</th> </tr>
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
                          <img src="../<?php echo htmlspecialchars($provider['provider_image']); ?>" alt="Gambar Provider" style="max-width: 80px; height: auto;">
                        <?php else: ?>
                          Tidak Ada
                        <?php endif; ?>
                      </td>
                      <td>
                        <button type="button" class="btn btn-sm btn-info edit-image-btn"
                                data-bs-toggle="modal" data-bs-target="#editImageModal"
                                data-code="<?php echo htmlspecialchars($provider['provider_code']); ?>"
                                data-name="<?php echo htmlspecialchars($provider['provider_name']); ?>"
                                data-type="<?php echo htmlspecialchars($provider['provider_type']); ?>"
                                data-image="<?php echo !empty($provider['provider_image']) ? '../' . htmlspecialchars($provider['provider_image']) : ''; ?>">
                            Edit Gambar
                        </button>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <p>Tidak ada provider di database. Klik tombol 'Update and Insert From API To Database' di atas untuk mengisinya.</p>
          <?php endif; ?>

        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="editImageModal" tabindex="-1" aria-labelledby="editImageModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editImageModalLabel">Edit Gambar Provider: <span id="modalProviderName"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="" enctype="multipart/form-data">
        <div class="modal-body">
          <input type="hidden" id="modalProviderCode" name="provider_code_modal">
          <input type="hidden" id="modalProviderType" name="provider_type_modal"> <div class="mb-3">
            <label for="providerImageFile" class="form-label">Pilih Gambar Baru:</label>
            <input class="form-control" type="file" id="providerImageFile" name="provider_image_file" accept="image/*" required>
          </div>
          <div class="text-center mt-3">
              <img id="currentProviderImage" src="" alt="Gambar Saat Ini" style="max-width: 100px; max-height: 100px; border: 1px solid #ddd; display: none;">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
          <button type="submit" name="upload_provider_image_btn" class="btn btn-primary">Unggah Gambar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
    // Logika untuk mengisi modal Edit Gambar Provider
    $('.edit-image-btn').on('click', function() {
        var providerCode = $(this).data('code');
        var providerName = $(this).data('name');
        var providerType = $(this).data('type'); // Dapatkan tipe provider
        var currentImageUrl = $(this).data('image'); 

        $('#modalProviderCode').val(providerCode);
        $('#modalProviderType').val(providerType); // Isi hidden input untuk tipe provider
        $('#modalProviderName').text(providerName + ' (' + providerType.toUpperCase() + ')'); // Tampilkan nama dan tipe
        
        if (currentImageUrl) {
            $('#currentProviderImage').attr('src', currentImageUrl).show();
        } else {
            $('#currentProviderImage').hide();
        }
    });

    // Logika untuk menangani upload gambar provider
    // (Ini akan memproses form modal secara terpisah)
    <?php
    // --- Logika Upload Gambar Provider ---
    $upload_image_message = '';
    if (isset($_POST['upload_provider_image_btn']) && isset($_POST['provider_code_modal']) && isset($_POST['provider_type_modal'])) {
        $provider_code_to_update = $_POST['provider_code_modal'];
        $provider_type_to_update = $_POST['provider_type_modal']; // Ambil tipe provider
        $uploaded_file = $_FILES['provider_image_file'] ?? null;

        if ($uploaded_file && $uploaded_file['error'] === UPLOAD_ERR_OK) {
            // Dapatkan nama provider dari database untuk membuat folder
            $provider_name_for_folder = 'unknown_provider';
            if (isset($koneksi) && $koneksi instanceof mysqli) {
                // Gunakan provider_code DAN provider_type untuk memastikan nama yang benar jika ada duplikasi kode
                $stmt_name = $koneksi->prepare("SELECT provider_name FROM telo_provider WHERE provider_code = ? AND provider_type = ?");
                $stmt_name->bind_param("ss", $provider_code_to_update, $provider_type_to_update);
                $stmt_name->execute();
                $result_name = $stmt_name->get_result();
                if ($row_name = $result_name->fetch_assoc()) {
                    // Sanitasi nama untuk nama folder
                    $provider_name_for_folder = preg_replace('/[^a-zA-Z0-9_\-]/', '', $row_name['provider_name']);
                }
                $stmt_name->close();
            }

            // Path untuk menyimpan gambar. Buat folder berdasarkan nama provider yang disanitasi.
            $upload_dir = '../upload/telo_provider_images/' . $provider_name_for_folder . '/'; // Folder baru untuk Telo.is
            $upload_url_path = 'upload/telo_provider_images/' . $provider_name_for_folder . '/'; // Path relatif untuk disimpan di DB

            if (!is_dir($upload_dir)) {
                if(!mkdir($upload_dir, 0777, true)) {
                    $upload_image_message = '<div class="alert alert-danger">Gagal membuat direktori upload: ' . htmlspecialchars($upload_dir) . '</div>';
                }
            }

            // Validasi ekstensi file
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp']; // Tambah webp
            $file_extension = strtolower(pathinfo($uploaded_file['name'], PATHINFO_EXTENSION));
            if (!in_array($file_extension, $allowed_extensions)) {
                $upload_image_message = '<div class="alert alert-danger">Jenis file tidak diizinkan. Hanya JPG, JPEG, PNG, GIF, WEBP.</div>';
            } else {
                // Nama file lokal: provider_code_provider_type.ext (untuk unik)
                $new_file_name = $provider_code_to_update . '_' . strtolower($provider_type_to_update) . '.' . $file_extension;
                $destination_path = $upload_dir . $new_file_name;
                $db_image_path = $upload_url_path . $new_file_name; // Path yang akan disimpan di database

                if (move_uploaded_file($uploaded_file['tmp_name'], $destination_path)) {
                    // Update database: kolom provider_image di tabel telo_provider
                    if (isset($koneksi) && $koneksi instanceof mysqli) {
                        $stmt = $koneksi->prepare("UPDATE telo_provider SET provider_image = ?, last_updated = CURRENT_TIMESTAMP WHERE provider_code = ? AND provider_type = ?");
                        if ($stmt === false) {
                            $upload_image_message = '<div class="alert alert-danger">Gagal menyiapkan statement database: ' . htmlspecialchars($koneksi->error) . '</div>';
                        } else {
                            $stmt->bind_param("sss", $db_image_path, $provider_code_to_update, $provider_type_to_update);
                            if ($stmt->execute()) {
                                $upload_image_message = '<div class="alert alert-success">Gambar provider berhasil diunggah dan diperbarui.</div>';
                                // Redirect untuk refresh halaman dan menampilkan gambar baru
                                header("Location: telo_provider.php?upload_status=success&msg=" . urlencode(strip_tags($upload_image_message)));
                                exit();
                            } else {
                                $upload_image_message = '<div class="alert alert-danger">Gagal memperbarui database: ' . htmlspecialchars($stmt->error) . '</div>';
                            }
                            $stmt->close();
                        }
                    } else {
                        $upload_image_message = '<div class="alert alert-danger">Koneksi database tidak valid.</div>';
                    }
                } else {
                    $upload_image_message = '<div class="alert alert-danger">Gagal memindahkan file yang diunggah.</div>';
                }
            }
        } else {
            $upload_image_message = '<div class="alert alert-danger">Tidak ada file yang diunggah atau terjadi kesalahan upload.</div>';
            if ($uploaded_file) $upload_image_message .= ' Error code: ' . $uploaded_file['error'];
        }
    }

    // Menampilkan pesan status upload dari redirect
    if (isset($_GET['upload_status']) && isset($_GET['msg'])) {
        $upload_image_message = '<div class="alert alert-' . htmlspecialchars($_GET['upload_status']) . '">' . htmlspecialchars($_GET['msg']) . '</div>';
    }
    echo $upload_image_message; // Tampilkan pesan status upload gambar di sini
    ?>

    // Reset modal saat ditutup (opsional, karena halaman akan reload setelah submit)
    $('#editImageModal').on('hidden.bs.modal', function () {
        $('#currentProviderImage').attr('src', '').hide();
        $('#providerImageFile').val(''); // Membersihkan input file
    });

    // Untuk menampilkan pesan status upload dari redirect
    const urlParams = new URLSearchParams(window.location.search);
    const uploadStatus = urlParams.get('upload_status');
    const uploadMsg = urlParams.get('msg');

    if (uploadStatus && uploadMsg) {
        // Hapus parameter dari URL agar tidak muncul saat refresh manual
        urlParams.delete('upload_status');
        urlParams.delete('msg');
        history.replaceState(null, null, window.location.pathname + '?' + urlParams.toString());
    }
});
</script>