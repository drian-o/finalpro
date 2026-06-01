<?php
session_start();
include_once '../koneksi.php';

if (isset($_SESSION['kode_admin'])) {
    echo '
      <script>
        window.location.replace("'.$alamat_admin.'dasbor");
      </script>
    ';
} else if (isset($_SESSION['id_admin'])) {
    echo '
      <script>
        window.location.replace("'.$alamat_admin.'verifikasi");
      </script>
    ';
}

if (isset($_POST['masuk'])) {
    $nama_pengguna_admin = $_POST['nama_pengguna_admin'];
    $kata_sandi_admin = $_POST['kata_sandi_admin'];

    // Validasi input
    if (preg_match('/^[a-zA-Z0-9\s]+$/', $nama_pengguna_admin)) {
        // Gunakan prepared statement untuk menghindari SQL injection
        $stmt = $koneksi->prepare("SELECT * FROM admin WHERE nama_pengguna_admin = ?");
        $stmt->bind_param("s", $nama_pengguna_admin);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $data_cek_admin = $result->fetch_assoc();
            $id_cek_admin = $data_cek_admin['id_admin'];
            $nama_cek_admin = $data_cek_admin['nama_admin'];
            $kata_sandi_cek_admin = $data_cek_admin['kata_sandi_admin'];

            // Verifikasi password
            if (password_verify($kata_sandi_admin, $kata_sandi_cek_admin)) {
                $_SESSION['id_admin'] = $id_cek_admin;
                echo '
                  <script>
                    window.location.replace("'.$alamat_admin.'verifikasi");
                  </script>
                ';
            } else {
                echo '
                  <script>
                    alert("Kata sandi salah, silahkan coba lagi!");
                    window.location.replace("'.$alamat_admin.'masuk");
                  </script>
                ';
            }
        } else {
            echo '
              <script>
                alert("Data admin tidak ditemukan, silahkan coba lagi!");
                window.location.replace("'.$alamat_admin.'keluar.php");
              </script>
            ';
        }
        $stmt->close();
    } else {
        echo '
          <script>
            alert("Dilarang menggunakan spasi atau simbol!");
            window.location.replace("'.$alamat_admin.'masuk");
          </script>
        ';
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="light-style customizer-hide" dir="ltr" data-theme="theme-default" data-assets-path="assets/" data-template="vertical-menu-template">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
    <title>Masuk | Panel Admin</title>
    <base href="<?php echo $alamat_admin; ?>">
    <link rel="icon" type="image/x-icon" href="assets/img/favicon.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Icons -->
    <link rel="stylesheet" href="assets/vendor/fonts/materialdesignicons.css">
    <link rel="stylesheet" href="assets/vendor/fonts/fontawesome.css">
    <!-- Core CSS -->
    <link rel="stylesheet" href="assets/vendor/css/rtl/core.css" class="template-customizer-core-css">
    <link rel="stylesheet" href="assets/vendor/css/rtl/theme-default.css" class="template-customizer-theme-css">
    <link rel="stylesheet" href="assets/css/demo.css">
    <!-- Vendors CSS -->
    <link rel="stylesheet" href="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css">
    <link rel="stylesheet" href="assets/vendor/libs/node-waves/node-waves.css">
    <link rel="stylesheet" href="assets/vendor/libs/typeahead-js/typeahead.css">
    <!-- Vendor -->
    <link rel="stylesheet" href="assets/vendor/libs/formvalidation/dist/css/formValidation.min.css">
    <!-- Page CSS -->
    <!-- Page -->
    <link rel="stylesheet" href="assets/vendor/css/pages/page-auth.css">
    <!-- Helpers -->
    <script src="assets/vendor/js/helpers.js"></script>
    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Template customizer: To hide customizer set displayCustomizer value false in config.js.  -->
    <script src="assets/vendor/js/template-customizer.js"></script>
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="assets/js/config.js"></script>
  </head>
  <body>
    <!-- Content -->
    <div class="position-relative">
      <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner py-4">
          <!-- Login -->
          <div class="card p-2">
            <!-- Logo -->
            <div class="app-brand justify-content-center mt-5">
              <a href="javascript:void(0);" class="app-brand-link gap-2">
                <span class="app-brand-logo demo">
                  <img src="assets/img/logo.png" alt="Logo">
                </span>
                <span class="app-brand-text demo text-heading fw-bold">Panel Admin</span>
              </a>
            </div>
            <!-- /Logo -->
            <div class="card-body mt-2">
              <h4 class="mb-2 fw-semibold"><?php echo ucapan(); ?></h4>
              <p class="mb-4">Silahkan konfirmasi Nama Pengguna dan Kata Sandi anda.</p>
              <form method="post" class="mb-3">
                <div class="form-floating form-floating-outline mb-3">
                  <input type="text" class="form-control" name="nama_pengguna_admin" placeholder="Nama Pengguna" autofocus required>
                  <label>Nama Pengguna</label>
                </div>
                <div class="mb-3">
                  <div class="form-password-toggle">
                    <div class="input-group input-group-merge">
                      <div class="form-floating form-floating-outline">
                        <input type="password" class="form-control" name="kata_sandi_admin" placeholder="Kata Sandi" required>
                        <label>Kata Sandi</label>
                      </div>
                      <span class="input-group-text cursor-pointer">
                        <i class="mdi mdi-eye-off-outline"></i>
                      </span>
                    </div>
                  </div>
                </div>
                <div class="mb-3">
                  <button type="submit" name="masuk" class="btn btn-primary d-grid w-100">Masuk</button>
                </div>
              </form>
            </div>
          </div>
          <!-- /Login -->
          <img alt="mask" src="assets/img/illustrations/auth-basic-login-mask-light.png" class="authentication-image d-none d-lg-block" data-app-light-img="illustrations/auth-basic-login-mask-light.png" data-app-dark-img="illustrations/auth-basic-login-mask-dark.png">
        </div>
      </div>
    </div>
    <!-- / Content -->
    <!-- Core JS -->
    <!-- build:js assets/vendor/js/core.js -->
    <script src="assets/vendor/libs/jquery/jquery.js"></script>
    <script src="assets/vendor/libs/popper/popper.js"></script>
    <script src="assets/vendor/js/bootstrap.js"></script>
    <script src="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="assets/vendor/libs/node-waves/node-waves.js"></script>
    <script src="assets/vendor/libs/hammer/hammer.js"></script>
    <script src="assets/vendor/libs/i18n/i18n.js"></script>
    <script src="assets/vendor/libs/typeahead-js/typeahead.js"></script>
    <script src="assets/vendor/js/menu.js"></script>
    <!-- endbuild -->
    <!-- Vendors JS -->
    <script src="assets/vendor/libs/formvalidation/dist/js/FormValidation.min.js"></script>
    <script src="assets/vendor/libs/formvalidation/dist/js/plugins/Bootstrap5.min.js"></script>
    <script src="assets/vendor/libs/formvalidation/dist/js/plugins/AutoFocus.min.js"></script>
    <!-- Main JS -->
    <script src="assets/js/main.js"></script>
    <!-- Page JS -->
    <script src="assets/js/pages-auth.js"></script>
  </body>
</html>
