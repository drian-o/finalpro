<?php
  if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
  include_once '../koneksi.php';

 $allowed_pages = [
    "dasbor", "pemberitahuan", "anggota", "kyc", "refferal",
    "ubah_saldo", "saldo", "tambah_anggota", "ubah_anggota",
    "deposit", "ubah_deposit", "withdraw", "ubah_withdraw",
    "rekening", "tambah_rekening", "ubah_rekening",
    "bukti_jp", "tambah_bukti_jp", "ubah_bukti_jp",
    "promosi", "tambah_promosi", "ubah_promosi",
    "staff", "tambah_staff", "ubah_staff",
    "bonus", "tambah_bonus", "ubah_bonus",
    "ikon_mengambang", "tambah_ikon_mengambang", "ubah_ikon_mengambang",
    "profil", "pengaturan","call_apply", 
    "change_user_rtp", 
    "change_agent_rtp", 
    "call_history","updategame","updateprovider","editgame","editgamelist","ajax_get_games","gamepopuler","gamerecomen","claim_bonus","proses_tambah_semua_bonus_mingguan","rekap","gamefeatured","banner_casino","egames","srg_provider","srg_game","voucher","telo_provider","telo_game","rekomendasi","pilihan_lottery","pilihan_slot","pilihan_casino","pilihan_table","pilihan_sports","pilihan_arcade","pilihan_card","pilihan_fishing","pilihan_cockfight","pilihan_crash","exa_stats","exa_transaction","voucher","ubah_pengaturan_referral","nexus_gamelist","nexus_provider","nexus_transaction","turnover","ubah_turnover",
    "tambah_domain" // 🔥 HAK AKSES BARU: Masukkan halaman tambah_domain di sini agar diizinkan sistem panel
  ];

  if (isset($_SESSION['kode_admin'])) {
    $kode_admin_aktif = $_SESSION['kode_admin'];
    $admin_aktif_query = mysqli_query($koneksi, "SELECT * FROM admin WHERE kode_admin = '$kode_admin_aktif'");

    if ($admin_aktif_query && mysqli_num_rows($admin_aktif_query) == 1) {
      $data_admin_aktif = mysqli_fetch_array($admin_aktif_query);
      if ($data_admin_aktif) {
        $id_admin = $data_admin_aktif['id_admin'];
        $nama_admin = $data_admin_aktif['nama_admin'];
        $nama_pengguna_admin = $data_admin_aktif['nama_pengguna_admin'];
        $kata_sandi_admin = $data_admin_aktif['kata_sandi_admin'];
        $pin_admin = $data_admin_aktif['pin_admin'];
      } else {
         echo '
            <script>
              alert("Terjadi kesalahan saat memproses data admin, harap masuk kembali!");
              window.location.replace("'.$alamat_admin.'keluar.php");
            </script>
          ';
          exit();
      }
    } else {
      echo '
        <script>
          alert("Terjadi kesalahan, harap masuk kembali!");
          window.location.replace("'.$alamat_admin.'keluar.php");
        </script>
      ';
      exit();
    }
  } else {
    echo '
      <script>
        window.location.replace("'.$alamat_admin.'masuk");
      </script>
    ';
    exit();
  }

  if (isset($_GET['halaman'])) {
    $halaman_input = strtolower($_GET['halaman']);
    if (in_array($halaman_input, $allowed_pages)) {
      $halaman_aktif = $halaman_input;
    } else {
      echo '
        <script>
          alert("Halaman tidak ditemukan!");
          window.location.replace("'.$alamat_admin.'dasbor");
        </script>
      ';
      exit();
    }
  } else {
    echo '
      <script>
        window.location.replace("'.$alamat_admin.'dasbor");
      </script>
    ';
    exit();
  }
?>
<!DOCTYPE html>
<html lang="en" class="dark-style layout-navbar-fixed layout-menu-fixed layout-footer-fixed" dir="ltr" data-theme="theme-default" data-assets-path="assets/" data-template="vertical-menu-template-starter">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
    <title><?php echo ucwords(str_replace('_', ' ', $halaman_aktif)); ?> | Panel Admin</title>
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
        <?php
          include_once "sidebar.php";
        ?>
        <div class="layout-page">
          <?php
            include_once "navbar.php";
          ?>
          <div class="content-wrapper">
            <?php
              include_once "$halaman_aktif.php";
              include_once "footer.php";
            ?>
          </div>
        </div>
      </div>
      <div class="layout-overlay layout-menu-toggle"></div>
      <div class="drag-target"></div>
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
    <script>
      $(document).ready(function() {
        $("#<?php echo $halaman_aktif; ?>").addClass("active");

        <?php
          $sub_menu_map = [
              "tambah_anggota" => "anggota",
              "ubah_anggota" => "anggota",
              "ubah_deposit" => "deposit",
              "ubah_withdraw" => "withdraw",
              "tambah_rekening" => "rekening",
              "ubah_rekening" => "rekening",
              "tambah_promosi" => "promosi",
              "ubah_promosi" => "promosi",
              "tambah_staff" => "staff",
              "ubah_staff" => "staff",
              "tambah_bonus" => "bonus",
              "ubah_bonus" => "bonus",
              "tambah_ikon_mengambang" => "ikon_mengambang",
              "ubah_ikon_mengambang" => "ikon_mengambang",
              "tambah_bukti_jp" => "bukti_jp",
              "ubah_bukti_jp" => "bukti_jp",
              "halaman_edit_game" => "manajemen_game",
              "editgamelist" => "editgamelist",
              "ubah_turnover" => "turnover" // Add this line to correctly highlight the parent menu
          ];
          if (isset($sub_menu_map[$halaman_aktif])) {
            echo '$("#' . $sub_menu_map[$halaman_aktif] . '").addClass("active");';
          }
        ?>

        function updateLiveTime() {
          var currentTime = new Date();
          var hours = currentTime.getHours();
          var minutes = currentTime.getMinutes();
          var seconds = currentTime.getSeconds();
          hours = (hours < 10 ? "0" : "") + hours;
          minutes = (minutes < 10 ? "0" : "") + minutes;
          seconds = (seconds < 10 ? "0" : "") + seconds;
          $("#jam_sekarang").html("Jam " + hours + ":" + minutes + ":" + seconds);
        }
        setInterval(updateLiveTime, 1000);

        $("#example").DataTable();
        $(".select2").select2();
        $(".summernote").summernote({
          tabsize: 2,
          height: 100
        });
      });
    </script>
  </body>
</html>
