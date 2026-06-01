<?php
// ubah_turnover.php - Halaman Standalone
ob_start();

include_once '../koneksi.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['kode_admin'])) {
    echo '<script>alert("Terjadi kesalahan, harap masuk kembali!"); window.location.href = "'.$alamat_admin.'masuk";</script>';
    exit();
}

$nama_pengguna = null;
$data_anggota = null;

if (isset($_GET['nama'])) {
    $nama_pengguna = urldecode($_GET['nama']);
    $stmt = $koneksi->prepare("SELECT * FROM anggota WHERE nama_pengguna_anggota = ?");
    $stmt->bind_param("s", $nama_pengguna);
    $stmt->execute();
    $result = $stmt->get_result();
    $data_anggota = $result->fetch_assoc();
    $stmt->close();
    
    if (!$data_anggota) {
        echo '<script>alert("Data anggota tidak ditemukan!"); window.location.href = "'.$alamat_admin.'turnover";</script>';
        exit();
    }
} else {
    echo '<script>alert("Nama pengguna tidak ditemukan!"); window.location.href = "'.$alamat_admin.'turnover";</script>';
    exit();
}

if (isset($_POST['update_data'])) {
    $updated_turnover = $_POST['turnover_amount'];
    $updated_last_update = date('Y-m-d H:i:s'); 

    $stmt_update = $koneksi->prepare("UPDATE anggota SET turnover_amount = ?, last_turnover_update = ? WHERE nama_pengguna_anggota = ?");
    $stmt_update->bind_param("dss", $updated_turnover, $updated_last_update, $nama_pengguna);
    
    if ($stmt_update->execute()) {
        echo '<script>alert("Berhasil memperbarui data turnover."); window.location.href = "'.$alamat_admin.'turnover";</script>';
    } else {
        echo '<script>alert("Gagal memperbarui data turnover: ' . $stmt_update->error . '"); window.location.href = "'.$alamat_admin.'turnover";</script>';
    }
    $stmt_update->close();
    exit();
}

$formatted_turnover = number_format($data_anggota['turnover_amount'], 2, '.', '');
?>

<!DOCTYPE html>
<html lang="en" class="dark-style layout-navbar-fixed layout-menu-fixed layout-footer-fixed" dir="ltr" data-theme="theme-default" data-assets-path="assets/" data-template="vertical-menu-template-starter">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
    <title>Ubah Data Turnover | Panel Admin</title>
    <base href="<?php echo $alamat_admin; ?>">
    <link rel="icon" type="image/x-icon" href="assets/img/favicon.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/vendor/fonts/materialdesignicons.css">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font/css/materialdesignicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/vendor/fonts/fontawesome.css">
    <link rel="stylesheet" href="assets/vendor/css/rtl/core.css" class="template-customizer-core-css">
    <link rel="stylesheet" href="assets/vendor/css/rtl/theme-default.css" class="template-customizer-theme-css">
    <link rel="stylesheet" href="assets/css/demo.css">
    <link rel="stylesheet" href="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css">
    <link rel="stylesheet" href="assets/vendor/libs/node-waves/node-waves.css">
    <link rel="stylesheet" href="assets/vendor/libs/typeahead-js/typeahead.css" />
    <link rel="stylesheet" href="assets/vendor/libs/flatpickr/flatpickr.css" />
    <link rel="stylesheet" href="assets/vendor/libs/select2/select2.css" />
    <link rel="stylesheet" href="assets/vendor/libs/summernote/summernote-bs4.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.3/css/dataTables.bootstrap5.css" />
    <script src="assets/vendor/js/helpers.js"></script>
    <script src="assets/vendor/js/template-customizer.js"></script>
    <script src="assets/js/config.js"></script>
  </head>
  <body>
    <div class="layout-wrapper layout-content-navbar">
      <div class="layout-container">
        <div class="content-wrapper">
          <div class="container-xxl flex-grow-1 container-p-y">
            <div class="row gy-4 mb-4">
              <div class="col-md-6">
                <div class="fw-bold fs-4 text-center text-md-start">Ubah Data Turnover</div>
              </div>
              <div class="col-md-6">
                <div class="text-center text-md-end">
                  <a href="<?php echo htmlspecialchars($alamat_admin.'turnover'); ?>" class="btn btn-sm btn-primary waves-effect waves-light">
                    <span class="tf-icons mdi mdi-chevron-double-left me-1"></span>
                    Kembali
                  </a>
                </div>
              </div>
            </div>

            <div class="card mb-4">
              <h5 class="card-header">Formulir Turnover</h5>
              <form method="post" class="card-body">
                <div class="row g-3">
                  <div class="col-md-6">
                      <div class="form-floating form-floating-outline">
                          <input type="text" class="form-control" name="turnover_amount" value="<?php echo htmlspecialchars($formatted_turnover); ?>" required>
                          <label>Turnover Amount</label>
                      </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-floating form-floating-outline">
                      <input type="datetime-local" class="form-control" name="last_turnover_update" value="<?php echo date('Y-m-d\TH:i:s'); ?>" required>
                      <label>Terakhir Diperbarui</label>
                    </div>
                  </div>
                </div>
                <div class="pt-4 text-end">
                  <button type="submit" name="update_data" class="btn btn-primary waves-effect waves-light">
                    <span class="tf-icons mdi mdi-content-save me-1"></span>
                    Simpan Perubahan
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
        </div>
    </div>
    <script src="assets/vendor/libs/jquery/jquery.js"></script>
    <script src="assets/vendor/libs/popper/popper.js"></script>
    <script src="assets/vendor/js/bootstrap.js"></script>
    <script src="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="assets/vendor/libs/node-waves/node-waves.js"></script>
    <script src="assets/vendor/libs/hammer/hammer.js"></script>
    <script src="assets/vendor/js/menu.js"></script>
    <script src="assets/vendor/libs/cleavejs/cleave.js"></script>
    <script src="assets/vendor/libs/cleavejs/cleave-phone.js"></script>
    <script src="assets/vendor/libs/moment/moment.js"></script>
    <script src="assets/vendor/libs/flatpickr/flatpickr.js"></script>
    <script src="assets/vendor/libs/select2/select2.js"></script>
    <script src="assets/vendor/libs/summernote/summernote-bs4.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="https://cdn.datatables.net/2.0.3/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.0.3/js/dataTables.bootstrap5.js"></script>
    <script src="assets/js/form-layouts.js"></script>
  </body>
</html>