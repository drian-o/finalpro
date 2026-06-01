<?php
  session_start();
  include_once '../koneksi.php';
  if (isset($_SESSION['kode_staff'])) {
    echo '
      <script>
        window.location.replace("'.$alamat_staff.'dasbor");
      </script>
    ';
  }
  if (isset($_SESSION['id_staff'])) {
    $id_staff_aktif = $_SESSION['id_staff'];
  } else {
    echo '
      <script>
        alert("Terjadi kesalahan, harap masuk lagi!");
        window.location.replace("'.$alamat_staff.'keluar.php");
      </script>
    ';
  }
  if (isset($_POST['verifikasi'])) {
    $pin1 = $_POST['pin1'];
    $pin2 = $_POST['pin2'];
    $pin3 = $_POST['pin3'];
    $pin4 = $_POST['pin4'];
    $pin5 = $_POST['pin5'];
    $pin6 = $_POST['pin6'];
    $pin = $pin1.$pin2.$pin3.$pin4.$pin5.$pin6;
    $cek_staff = mysqli_query($koneksi, "SELECT * FROM staff WHERE id_staff = '$id_staff_aktif'");
    if (mysqli_num_rows($cek_staff) == 1) {
      $data_cek_staff = mysqli_fetch_array($cek_staff);
      $pin_cek_staff = $data_cek_staff['pin_staff'];
      if (password_verify($pin, $pin_cek_staff)) {
        $kode_staff = generatorRangkaianAcak(20);
        $perbarui_data_staff = mysqli_query($koneksi, "UPDATE staff SET kode_staff = '$kode_staff' WHERE id_staff = '$id_staff_aktif'");
        if ($perbarui_data_staff) {
          $_SESSION['kode_staff'] = $kode_staff;
          echo '
            <script>
              window.location.replace("'.$alamat_staff.'dasbor");
            </script>
          ';
        } else {
          echo 'Proses Gagal<br>Error : '.$perbarui_data_staff.'<br>'.mysqli_error($koneksi);
        }
      } else {
        echo '
          <script>
            alert("Pin salah, silahkan coba lagi!");
            window.location.replace("'.$alamat_staff.'verifikasi");
          </script>
        ';
      }
    } else {
      echo '
        <script>
          alert("Data staff tidak ditemukan, silahkan coba lagi!");
          window.location.replace("'.$alamat_staff.'keluar.php");
        </script>
      ';
    }
  }
?>
<!DOCTYPE html>
<html lang="en" class="light-style customizer-hide" dir="ltr" data-theme="theme-default" data-assets-path="assets/" data-template="vertical-menu-template">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>Verifikasi | Panel staff</title>
    <base href="<?php echo $alamat_staff; ?>">
    <meta name="description" content="" />
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/img/favicon/favicon.ico" />
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <!-- Icons -->
    <link rel="stylesheet" href="assets/vendor/fonts/materialdesignicons.css" />
    <link rel="stylesheet" href="assets/vendor/fonts/fontawesome.css" />
    <!-- Core CSS -->
    <link rel="stylesheet" href="assets/vendor/css/rtl/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="assets/vendor/css/rtl/theme-default.css" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="assets/css/demo.css" />
    <!-- Vendors CSS -->
    <link rel="stylesheet" href="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <link rel="stylesheet" href="assets/vendor/libs/node-waves/node-waves.css" />
    <link rel="stylesheet" href="assets/vendor/libs/typeahead-js/typeahead.css" />
    <!-- Vendor -->
    <link rel="stylesheet" href="assets/vendor/libs/formvalidation/dist/css/formValidation.min.css" />
    <!-- Page CSS -->
    <!-- Page -->
    <link rel="stylesheet" href="assets/vendor/css/pages/page-auth.css" />
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
    <div class="positive-relative">
      <div class="authentication-wrapper authentication-basic">
        <div class="authentication-inner py-4">
          <!--  Two Steps Verification -->
          <div class="card p-2">
            <!-- Logo -->
            <div class="app-brand justify-content-center mt-5">
              <a href="javascript:void(0);" class="app-brand-link gap-2">
                <span class="app-brand-logo demo">
                  <img src="assets/img/logo.png" alt="Logo">
                </span>
                <span class="app-brand-text demo text-heading fw-bold">Panel staff</span>
              </a>
            </div>
            <!-- /Logo -->
            <div class="card-body">
              <h4 class="mb-2 fw-semibold"><?php echo ucapan(); ?></h4>
              <p class="text-start mb-4">Silahkan konfirmasi PIN anda.</p>
              <form method="post">
                <div class="mb-3">
                  <div
                    class="auth-input-wrapper d-flex align-items-center justify-content-sm-between numeral-mask-wrapper">
                    <input type="password" class="form-control auth-input w-px-50 text-center numeral-mask text-center h-px-50 mx-1 my-2" name="pin1" maxlength="1" autofocus />
                    <input type="password" class="form-control auth-input w-px-50 text-center numeral-mask text-center h-px-50 mx-1 my-2" name="pin2" maxlength="1" />
                    <input type="password" class="form-control auth-input w-px-50 text-center numeral-mask text-center h-px-50 mx-1 my-2" name="pin3" maxlength="1" />
                    <input type="password" class="form-control auth-input w-px-50 text-center numeral-mask text-center h-px-50 mx-1 my-2" name="pin4" maxlength="1" />
                    <input type="password" class="form-control auth-input w-px-50 text-center numeral-mask text-center h-px-50 mx-1 my-2" name="pin5" maxlength="1" />
                    <input type="password" class="form-control auth-input w-px-50 text-center numeral-mask text-center h-px-50 mx-1 my-2" name="pin6" maxlength="1" />
                  </div>
                </div>
                <button type="submit" name="verifikasi" class="btn btn-primary d-grid w-100 mb-3">Verifikasi</button>
              </form>
            </div>
          </div>
          <!-- / Two Steps Verification -->
          <img alt="mask" src="assets/img/illustrations/auth-basic-register-mask-light.png" class="authentication-image d-none d-lg-block" data-app-light-img="illustrations/auth-basic-register-mask-light.png" data-app-dark-img="illustrations/auth-basic-register-mask-dark.png" />
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
    <script src="assets/vendor/libs/cleavejs/cleave.js"></script>
    <script src="assets/vendor/libs/formvalidation/dist/js/FormValidation.min.js"></script>
    <script src="assets/vendor/libs/formvalidation/dist/js/plugins/Bootstrap5.min.js"></script>
    <script src="assets/vendor/libs/formvalidation/dist/js/plugins/AutoFocus.min.js"></script>

    <!-- Main JS -->
    <script src="assets/js/main.js"></script>

    <!-- Page JS -->
    <script src="assets/js/pages-auth.js"></script>
    <script src="assets/js/pages-auth-two-steps.js"></script>
  </body>
</html>