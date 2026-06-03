<?php
    if (session_status() == PHP_SESSION_NONE) {
      session_start();
  }
  date_default_timezone_set("Asia/Jakarta");
  $host = "137.184.155.151";
  $username = "mysql";
  $password = 'tkgs585XFckRoqum8keyAQKu9EjezXsrsZx8wNNHQQNI9nBY5K1RrC26jIuK6gci';
  $database = "default";
  $koneksi = mysqli_connect($host, $username, $password, $database);
  if ($koneksi) {
    include_once 'fungsi_umum.php';
    $alamat_website = 'http://sfpho7xg4jjpep1xpnaf8y8o.137.184.155.151.sslip.io/';
    $alamat_admin = 'http://sfpho7xg4jjpep1xpnaf8y8o.137.184.155.151.sslip.io/zuzulo/';
    $alamat_staff = 'http://sfpho7xg4jjpep1xpnaf8y8o.137.184.155.151.sslip.io/STAFF_Promotor/';
    
   // Judul Web
   $judul_web = mysqli_query($koneksi, "SELECT * FROM pengaturan WHERE nama_pengaturan = 'judul_web'");
   $data_judul_web = mysqli_fetch_array($judul_web);
   $id_judul_web = $data_judul_web['id_pengaturan'];
   $isi_1_judul_web = $data_judul_web['isi_1_pengaturan'];
   $isi_2_judul_web = $data_judul_web['isi_2_pengaturan'];
   $isi_3_judul_web = $data_judul_web['isi_3_pengaturan'];
   $default_provider_slug = 'pragmatic';
   $games_per_page = 12;
   // Deskripsi Web
   $deskripsi_web = mysqli_query($koneksi, "SELECT * FROM pengaturan WHERE nama_pengaturan = 'deskripsi_web'");
   $data_deskripsi_web = mysqli_fetch_array($deskripsi_web);
   $id_deskripsi_web = $data_deskripsi_web['id_pengaturan'];
   $isi_1_deskripsi_web = $data_deskripsi_web['isi_1_pengaturan'];
   $isi_2_deskripsi_web = $data_deskripsi_web['isi_2_pengaturan'];
   $isi_3_deskripsi_web = $data_deskripsi_web['isi_3_pengaturan'];
   // Kata Kunci Web
   $kata_kunci_web = mysqli_query($koneksi, "SELECT * FROM pengaturan WHERE nama_pengaturan = 'kata_kunci_web'");
   $data_kata_kunci_web = mysqli_fetch_array($kata_kunci_web);
   $id_kata_kunci_web = $data_kata_kunci_web['id_pengaturan'];
   $isi_1_kata_kunci_web = $data_kata_kunci_web['isi_1_pengaturan'];
   $isi_2_kata_kunci_web = $data_kata_kunci_web['isi_2_pengaturan'];
   $isi_3_kata_kunci_web = $data_kata_kunci_web['isi_3_pengaturan'];
   // Link APK Web
   $link_apk_web = mysqli_query($koneksi, "SELECT * FROM pengaturan WHERE nama_pengaturan = 'link_apk_web'");
   $data_link_apk_web = mysqli_fetch_array($link_apk_web);
   $id_link_apk_web = $data_link_apk_web['id_pengaturan'];
   $isi_1_link_apk_web = $data_link_apk_web['isi_1_pengaturan'];
   $isi_2_link_apk_web = $data_link_apk_web['isi_2_pengaturan'];
   $isi_3_link_apk_web = $data_link_apk_web['isi_3_pengaturan'];
   // Logo Web
   $logo_web = mysqli_query($koneksi, "SELECT * FROM pengaturan WHERE nama_pengaturan = 'logo_web'");
   $data_logo_web = mysqli_fetch_array($logo_web);
   $id_logo_web = $data_logo_web['id_pengaturan'];
   $isi_1_logo_web = $data_logo_web['isi_1_pengaturan'];
   $isi_2_logo_web = $data_logo_web['isi_2_pengaturan'];
   $isi_3_logo_web = $data_logo_web['isi_3_pengaturan'];
   // Favicon Web
   $favicon_web = mysqli_query($koneksi, "SELECT * FROM pengaturan WHERE nama_pengaturan = 'favicon_web'");
   $data_favicon_web = mysqli_fetch_array($favicon_web);
   $id_favicon_web = $data_favicon_web['id_pengaturan'];
   $isi_1_favicon_web = $data_favicon_web['isi_1_pengaturan'];
   $isi_2_favicon_web = $data_favicon_web['isi_2_pengaturan'];
   $isi_3_favicon_web = $data_favicon_web['isi_3_pengaturan'];
   // Teks Berjalan Web
   $teks_berjalan_web = mysqli_query($koneksi, "SELECT * FROM pengaturan WHERE nama_pengaturan = 'teks_berjalan_web'");
   $data_teks_berjalan_web = mysqli_fetch_array($teks_berjalan_web);
   $id_teks_berjalan_web = $data_teks_berjalan_web['id_pengaturan'];
   $isi_1_teks_berjalan_web = $data_teks_berjalan_web['isi_1_pengaturan'];
   $isi_2_teks_berjalan_web = $data_teks_berjalan_web['isi_2_pengaturan'];
   $isi_3_teks_berjalan_web = $data_teks_berjalan_web['isi_3_pengaturan'];
   // Facebook Web
   $facebook_web = mysqli_query($koneksi, "SELECT * FROM pengaturan WHERE nama_pengaturan = 'facebook_web'");
   $data_facebook_web = mysqli_fetch_array($facebook_web);
   $id_facebook_web = $data_facebook_web['id_pengaturan'];
   $isi_1_facebook_web = $data_facebook_web['isi_1_pengaturan'];
   $isi_2_facebook_web = $data_facebook_web['isi_2_pengaturan'];
   $isi_3_facebook_web = $data_facebook_web['isi_3_pengaturan'];
   // Telegram Web
   $telegram_web = mysqli_query($koneksi, "SELECT * FROM pengaturan WHERE nama_pengaturan = 'telegram_web'");
   $data_telegram_web = mysqli_fetch_array($telegram_web);
   $id_telegram_web = $data_telegram_web['id_pengaturan'];
   $isi_1_telegram_web = $data_telegram_web['isi_1_pengaturan'];
   $isi_2_telegram_web = $data_telegram_web['isi_2_pengaturan'];
   $isi_3_telegram_web = $data_telegram_web['isi_3_pengaturan'];
   // Popup Pengumuman Web
   $popup_pengumuman_web = mysqli_query($koneksi, "SELECT * FROM pengaturan WHERE nama_pengaturan = 'popup_pengumuman_web'");
   $data_popup_pengumuman_web = mysqli_fetch_array($popup_pengumuman_web);
   $id_popup_pengumuman_web = $data_popup_pengumuman_web['id_pengaturan'];
   $isi_1_popup_pengumuman_web = $data_popup_pengumuman_web['isi_1_pengaturan'];
   $isi_2_popup_pengumuman_web = $data_popup_pengumuman_web['isi_2_pengaturan'];
   $isi_3_popup_pengumuman_web = $data_popup_pengumuman_web['isi_3_pengaturan'];
   // Link LiveChat Web
   $link_livechat_web = mysqli_query($koneksi, "SELECT * FROM pengaturan WHERE nama_pengaturan = 'link_livechat_web'");
   $data_link_livechat_web = mysqli_fetch_array($link_livechat_web);
   $id_link_livechat_web = $data_link_livechat_web['id_pengaturan'];
   $isi_1_link_livechat_web = $data_link_livechat_web['isi_1_pengaturan'];
   $isi_2_link_livechat_web = $data_link_livechat_web['isi_2_pengaturan'];
   $isi_3_link_livechat_web = $data_link_livechat_web['isi_3_pengaturan'];
   // Popup Teks Belum Login Web
   $popup_teks_belum_login_web = mysqli_query($koneksi, "SELECT * FROM pengaturan WHERE nama_pengaturan = 'popup_teks_belum_login_web'");
   $data_popup_teks_belum_login_web = mysqli_fetch_array($popup_teks_belum_login_web);
   $id_popup_teks_belum_login_web = $data_popup_teks_belum_login_web['id_pengaturan'];
   $isi_1_popup_teks_belum_login_web = $data_popup_teks_belum_login_web['isi_1_pengaturan'];
   $isi_2_popup_teks_belum_login_web = $data_popup_teks_belum_login_web['isi_2_pengaturan'];
   $isi_3_popup_teks_belum_login_web = $data_popup_teks_belum_login_web['isi_3_pengaturan'];
   // Popup Teks Tidak Ada Saldo Web
   $popup_teks_tidak_ada_saldo_web = mysqli_query($koneksi, "SELECT * FROM pengaturan WHERE nama_pengaturan = 'popup_teks_tidak_ada_saldo_web'");
   $data_popup_teks_tidak_ada_saldo_web = mysqli_fetch_array($popup_teks_tidak_ada_saldo_web);
   $id_popup_teks_tidak_ada_saldo_web = $data_popup_teks_tidak_ada_saldo_web['id_pengaturan'];
   $isi_1_popup_teks_tidak_ada_saldo_web = $data_popup_teks_tidak_ada_saldo_web['isi_1_pengaturan'];
   $isi_2_popup_teks_tidak_ada_saldo_web = $data_popup_teks_tidak_ada_saldo_web['isi_2_pengaturan'];
   $isi_3_popup_teks_tidak_ada_saldo_web = $data_popup_teks_tidak_ada_saldo_web['isi_3_pengaturan'];
   // Popup Teks Ada Saldo Web
   $popup_teks_ada_saldo_web = mysqli_query($koneksi, "SELECT * FROM pengaturan WHERE nama_pengaturan = 'popup_teks_ada_saldo_web'");
   $data_popup_teks_ada_saldo_web = mysqli_fetch_array($popup_teks_ada_saldo_web);
   $id_popup_teks_ada_saldo_web = $data_popup_teks_ada_saldo_web['id_pengaturan'];
   $isi_1_popup_teks_ada_saldo_web = $data_popup_teks_ada_saldo_web['isi_1_pengaturan'];
   $isi_2_popup_teks_ada_saldo_web = $data_popup_teks_ada_saldo_web['isi_2_pengaturan'];
   $isi_3_popup_teks_ada_saldo_web = $data_popup_teks_ada_saldo_web['isi_3_pengaturan'];
   // Popup Teks Setelah Deposit Web
   $popup_teks_setelah_deposit_web = mysqli_query($koneksi, "SELECT * FROM pengaturan WHERE nama_pengaturan = 'popup_teks_setelah_deposit_web'");
   $data_popup_teks_setelah_deposit_web = mysqli_fetch_array($popup_teks_setelah_deposit_web);
   $id_popup_teks_setelah_deposit_web = $data_popup_teks_setelah_deposit_web['id_pengaturan'];
   $isi_1_popup_teks_setelah_deposit_web = $data_popup_teks_setelah_deposit_web['isi_1_pengaturan'];
   $isi_2_popup_teks_setelah_deposit_web = $data_popup_teks_setelah_deposit_web['isi_2_pengaturan'];
   $isi_3_popup_teks_setelah_deposit_web = $data_popup_teks_setelah_deposit_web['isi_3_pengaturan'];
   // Popup Teks Setelah Withdraw Web
   $popup_teks_setelah_withdraw_web = mysqli_query($koneksi, "SELECT * FROM pengaturan WHERE nama_pengaturan = 'popup_teks_setelah_withdraw_web'");
   $data_popup_teks_setelah_withdraw_web = mysqli_fetch_array($popup_teks_setelah_withdraw_web);
   $id_popup_teks_setelah_withdraw_web = $data_popup_teks_setelah_withdraw_web['id_pengaturan'];
   $isi_1_popup_teks_setelah_withdraw_web = $data_popup_teks_setelah_withdraw_web['isi_1_pengaturan'];
   $isi_2_popup_teks_setelah_withdraw_web = $data_popup_teks_setelah_withdraw_web['isi_2_pengaturan'];
   $isi_3_popup_teks_setelah_withdraw_web = $data_popup_teks_setelah_withdraw_web['isi_3_pengaturan'];
   // RTP Web
   $rtp_web = mysqli_query($koneksi, "SELECT * FROM pengaturan WHERE nama_pengaturan = 'rtp_web'");
   $data_rtp_web = mysqli_fetch_array($rtp_web);
   $id_rtp_web = $data_rtp_web['id_pengaturan'];
   $isi_1_rtp_web = $data_rtp_web['isi_1_pengaturan'];
   $isi_2_rtp_web = $data_rtp_web['isi_2_pengaturan'];
   $isi_3_rtp_web = $data_rtp_web['isi_3_pengaturan'];
   // BG 1 Web
   $bg_1_web = mysqli_query($koneksi, "SELECT * FROM pengaturan WHERE nama_pengaturan = 'bg_1_web'");
   $data_bg_1_web = mysqli_fetch_array($bg_1_web);
   $id_bg_1_web = $data_bg_1_web['id_pengaturan'];
   $isi_1_bg_1_web = $data_bg_1_web['isi_1_pengaturan'];
   $isi_2_bg_1_web = $data_bg_1_web['isi_2_pengaturan'];
   $isi_3_bg_1_web = $data_bg_1_web['isi_3_pengaturan'];
   // BG 2 Web
   $bg_2_web = mysqli_query($koneksi, "SELECT * FROM pengaturan WHERE nama_pengaturan = 'bg_2_web'");
   $data_bg_2_web = mysqli_fetch_array($bg_2_web);
   $id_bg_2_web = $data_bg_2_web['id_pengaturan'];
   $isi_1_bg_2_web = $data_bg_2_web['isi_1_pengaturan'];
   $isi_2_bg_2_web = $data_bg_2_web['isi_2_pengaturan'];
   $isi_3_bg_2_web = $data_bg_2_web['isi_3_pengaturan'];
   // BG 3 Web
   $bg_3_web = mysqli_query($koneksi, "SELECT * FROM pengaturan WHERE nama_pengaturan = 'bg_3_web'");
   $data_bg_3_web = mysqli_fetch_array($bg_3_web);
   $id_bg_3_web = $data_bg_3_web['id_pengaturan'];
   $isi_1_bg_3_web = $data_bg_3_web['isi_1_pengaturan'];
   $isi_2_bg_3_web = $data_bg_3_web['isi_2_pengaturan'];
   $isi_3_bg_3_web = $data_bg_3_web['isi_3_pengaturan'];
   // Script LiveChat Web
   $script_livechat_web = mysqli_query($koneksi, "SELECT * FROM pengaturan WHERE nama_pengaturan = 'script_livechat_web'");
   $data_script_livechat_web = mysqli_fetch_array($script_livechat_web);
   $id_script_livechat_web = $data_script_livechat_web['id_pengaturan'];
   $isi_1_script_livechat_web = $data_script_livechat_web['isi_1_pengaturan'];
   $isi_2_script_livechat_web = $data_script_livechat_web['isi_2_pengaturan'];
   $isi_3_script_livechat_web = $data_script_livechat_web['isi_3_pengaturan'];
   // WhatsApp Web
   $whatsapp_web = mysqli_query($koneksi, "SELECT * FROM pengaturan WHERE nama_pengaturan = 'whatsapp_web'");
   $data_whatsapp_web = mysqli_fetch_array($whatsapp_web);
   $id_whatsapp_web = $data_whatsapp_web['id_pengaturan'];
   $isi_1_whatsapp_web = $data_whatsapp_web['isi_1_pengaturan'];
   $isi_2_whatsapp_web = $data_whatsapp_web['isi_2_pengaturan'];
   $isi_3_whatsapp_web = $data_whatsapp_web['isi_3_pengaturan'];
   // BG Gradient 1 Web
   $bg_gradient_1_web = mysqli_query($koneksi, "SELECT * FROM pengaturan WHERE nama_pengaturan = 'bg_gradient_1_web'");
   $data_bg_gradient_1_web = mysqli_fetch_array($bg_gradient_1_web);
   $id_bg_gradient_1_web = $data_bg_gradient_1_web['id_pengaturan'];
   $isi_1_bg_gradient_1_web = $data_bg_gradient_1_web['isi_1_pengaturan'];
   $isi_2_bg_gradient_1_web = $data_bg_gradient_1_web['isi_2_pengaturan'];
   $isi_3_bg_gradient_1_web = $data_bg_gradient_1_web['isi_3_pengaturan'];
   // BG Gradient 2 Web
   $bg_gradient_2_web = mysqli_query($koneksi, "SELECT * FROM pengaturan WHERE nama_pengaturan = 'bg_gradient_2_web'");
   $data_bg_gradient_2_web = mysqli_fetch_array($bg_gradient_2_web);
   $id_bg_gradient_2_web = $data_bg_gradient_2_web['id_pengaturan'];
   $isi_1_bg_gradient_2_web = $data_bg_gradient_2_web['isi_1_pengaturan'];
   $isi_2_bg_gradient_2_web = $data_bg_gradient_2_web['isi_2_pengaturan'];
   $isi_3_bg_gradient_2_web = $data_bg_gradient_2_web['isi_3_pengaturan'];
   // BG Gradient 3 Web
   $bg_gradient_3_web = mysqli_query($koneksi, "SELECT * FROM pengaturan WHERE nama_pengaturan = 'bg_gradient_3_web'");
   $data_bg_gradient_3_web = mysqli_fetch_array($bg_gradient_3_web);
   $id_bg_gradient_3_web = $data_bg_gradient_3_web['id_pengaturan'];
   $isi_1_bg_gradient_3_web = $data_bg_gradient_3_web['isi_1_pengaturan'];
   $isi_2_bg_gradient_3_web = $data_bg_gradient_3_web['isi_2_pengaturan'];
   $isi_3_bg_gradient_3_web = $data_bg_gradient_3_web['isi_3_pengaturan'];
   // BG Gradient 4 Web
   $bg_gradient_4_web = mysqli_query($koneksi, "SELECT * FROM pengaturan WHERE nama_pengaturan = 'bg_gradient_4_web'");
   $data_bg_gradient_4_web = mysqli_fetch_array($bg_gradient_4_web);
   $id_bg_gradient_4_web = $data_bg_gradient_4_web['id_pengaturan'];
   $isi_1_bg_gradient_4_web = $data_bg_gradient_4_web['isi_1_pengaturan'];
   $isi_2_bg_gradient_4_web = $data_bg_gradient_4_web['isi_2_pengaturan'];
   $isi_3_bg_gradient_4_web = $data_bg_gradient_4_web['isi_3_pengaturan'];
   // BG Gradient 5 Web
   $bg_gradient_5_web = mysqli_query($koneksi, "SELECT * FROM pengaturan WHERE nama_pengaturan = 'bg_gradient_5_web'");
   $data_bg_gradient_5_web = mysqli_fetch_array($bg_gradient_5_web);
   $id_bg_gradient_5_web = $data_bg_gradient_5_web['id_pengaturan'];
   $isi_1_bg_gradient_5_web = $data_bg_gradient_5_web['isi_1_pengaturan'];
   $isi_2_bg_gradient_5_web = $data_bg_gradient_5_web['isi_2_pengaturan'];
   $isi_3_bg_gradient_5_web = $data_bg_gradient_5_web['isi_3_pengaturan'];
   // QRIS Web
   $qris_web = mysqli_query($koneksi, "SELECT * FROM pengaturan WHERE nama_pengaturan = 'qris_web'");
   $data_qris_web = mysqli_fetch_array($qris_web);
   $id_qris_web = $data_qris_web['id_pengaturan'];
   $isi_1_qris_web = $data_qris_web['isi_1_pengaturan'];
   $isi_2_qris_web = $data_qris_web['isi_2_pengaturan'];
   $isi_3_qris_web = $data_qris_web['isi_3_pengaturan'];
      // BG HEAD Web
   $bg_head_dekstop = mysqli_query($koneksi, "SELECT * FROM pengaturan WHERE nama_pengaturan = 'bg_head_dekstop'");
   $data_bg_head_dekstop = mysqli_fetch_array($bg_head_dekstop);
   $id_bg_head_dekstop = $data_bg_head_dekstop['id_pengaturan'];
   $isi_1_bg_head_dekstop = $data_bg_head_dekstop['isi_1_pengaturan'];
   $isi_2_bg_head_dekstop = $data_bg_head_dekstop['isi_2_pengaturan'];
   $isi_3_bg_head_dekstop = $data_bg_head_dekstop['isi_3_pengaturan'];
  // BG HEAD Web
  $bg_head_dekstop_query = mysqli_query($koneksi, "SELECT * FROM pengaturan WHERE nama_pengaturan = 'bg_head_dekstop'");
  if (!$bg_head_dekstop_query) {
      die("Query gagal: " . mysqli_error($koneksi));
  }
  $data_bg_head_dekstop = mysqli_fetch_array($bg_head_dekstop_query);
  $id_bg_head_dekstop = $data_bg_head_dekstop['id_pengaturan'];
  $isi_1_bg_head_dekstop = $data_bg_head_dekstop['isi_1_pengaturan'] ?? '';
  $isi_2_bg_head_dekstop = $data_bg_head_dekstop['isi_2_pengaturan'] ?? '';
  $isi_3_bg_head_dekstop = $data_bg_head_dekstop['isi_3_pengaturan'] ?? '';
 } else {
   echo "Kesalahan : Tidak dapat terhubung ke database." . PHP_EOL;
   echo "Kode Kesalahan : " . mysqli_connect_errno() . PHP_EOL;
   echo "Pesan Kesalahan : " . mysqli_connect_error() . PHP_EOL;
   exit;
 }

$saldo_anggota_session = 0;
$bonus_balance_session = 0;
$to_anggota_session = 0; // Tambahkan variabel ini

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    $id_anggota = $_SESSION['id_anggota'];
    $query_data_anggota = mysqli_prepare($koneksi, "SELECT saldo_anggota, bonus_balance, turnover_amount FROM anggota WHERE id_anggota = ?");
    if ($query_data_anggota) {
        mysqli_stmt_bind_param($query_data_anggota, 'i', $id_anggota);
        mysqli_stmt_execute($query_data_anggota);
        mysqli_stmt_bind_result($query_data_anggota, $db_saldo_anggota, $db_bonus_balance, $db_turnover_amount);
        if (mysqli_stmt_fetch($query_data_anggota)) {
            $saldo_anggota_session = $db_saldo_anggota;
            $bonus_balance_session = $db_bonus_balance;
            $to_anggota_session = $db_turnover_amount; // Simpan nilai turnover_amount
        }
        mysqli_stmt_close($query_data_anggota);
    }
}
?>
