<?php
// Pastikan session_start() sudah dipanggil di awal
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once '../koneksi.php';

if (!isset($_SESSION['kode_admin'])) {
  echo '
        <script>
            alert("Terjadi kesalahan, harap masuk kembali!");
            window.location.replace("' . $alamat_admin . 'keluar.php");
        </script>
    ';
  exit();
}

if (isset($_GET['id_anggota'])) {
  $id_anggota = $_GET['id_anggota'];
  $anggota = mysqli_query($koneksi, "SELECT * FROM anggota WHERE id_anggota = '$id_anggota'");
  $data_anggota = mysqli_fetch_array($anggota);

  $nama_pengguna_anggota = $data_anggota['nama_pengguna_anggota'];
  $kata_sandi_anggota = $data_anggata['kata_sandi_anggota']; // Typo: $data_anggata, harusnya $data_anggota
  $email_anggota = $data_anggota['email_anggota'];
  $telepon_anggota = $data_anggota['telepon_anggota'];
  $bank_anggota = $data_anggota['bank_anggota'];
  $nama_rekening_anggota = $data_anggota['nama_rekening_anggota'];
  $nomor_rekening_anggota = $data_anggota['nomor_rekening_anggota'];
  $saldo_anggota = $data_anggota['saldo_anggota']; // Baris ini tetap ada untuk membaca data
  $status_anggota = $data_anggota['status_anggota'];
  $status_game = $data_anggota['status_game'];
} else {
  echo '
        <script>
            alert("Pilih anggota yang ingin diubah!");
            window.location.replace("' . $alamat_admin . 'anggota");
        </script>
    ';
  exit();
}

if (isset($_POST['ubah_data'])) {
  $nama_pengguna_anggota_2 = strtolower($_POST['nama_pengguna_anggota']);
  $kata_sandi_anggota_2 = $_POST['kata_sandi_anggota'];
  $email_anggota_2 = $_POST['email_anggota'];
  $telepon_anggota_2 = $_POST['telepon_anggota'];
  $bank_anggota_2 = $_POST['bank_anggota'];
  $nama_rekening_anggota_2 = $_POST['nama_rekening_anggota'];
  $nomor_rekening_anggota_2 = $_POST['nomor_rekening_anggota'];
  // $saldo_anggota_2 = $_POST['saldo_anggota']; // Baris ini dihapus karena input field saldo dihilangkan
  $status_anggota_2 = $_POST['status_anggota'];
  $status_game_2 = $_POST['status_game'];
  $opsi = ['cost' => 12];
  // Hanya hash kata sandi jika tidak kosong
  if (!empty($kata_sandi_anggota_2)) {
      $kata_sandi_hash_anggota = password_hash($kata_sandi_anggota_2, PASSWORD_BCRYPT, $opsi);
  }


  if (preg_match('/^[a-zA-Z0-9\s]+$/', $nama_pengguna_anggota_2)) {
    $cek_nama_pengguna_anggota = mysqli_query($koneksi, "SELECT * FROM anggota WHERE NOT id_anggota = '$id_anggota' AND nama_pengguna_anggota = '$nama_pengguna_anggota_2'");
    if (mysqli_num_rows($cek_nama_pengguna_anggota) >= 1) {
      echo '
                <script>
                    alert("Nama Pengguna sudah terdaftar, gunakan yang lainnya.");
                    window.location.replace("' . $alamat_admin . 'ubah_anggota/' . $id_anggota . '");
                </script>
            ';
    } else {
      if (empty($kata_sandi_anggota_2)) {
        // Query UPDATE tanpa kolom saldo_anggota
        $ubah_anggota = mysqli_query($koneksi, "UPDATE anggota SET nama_pengguna_anggota = '$nama_pengguna_anggota_2', email_anggota = '$email_anggota_2', telepon_anggota = '$telepon_anggota_2', bank_anggota = '$bank_anggota_2', nama_rekening_anggota = '$nama_rekening_anggota_2', nomor_rekening_anggota = '$nomor_rekening_anggota_2', status_anggota = '$status_anggota_2', status_game = '$status_game_2' WHERE id_anggota = '$id_anggota'");
        if ($ubah_anggota) {
          echo '
                        <script>
                            alert("Berhasil ubah data.");
                            window.location.replace("' . $alamat_admin . 'anggota");
                        </script>
                    ';
        } else {
          echo "Proses Gagal<br>Error : " . mysqli_error($koneksi);
        }
      } else {
        // Query UPDATE tanpa kolom saldo_anggota (jika kata sandi diubah)
        $ubah_anggota = mysqli_query($koneksi, "UPDATE anggota SET nama_pengguna_anggota = '$nama_pengguna_anggota_2', kata_sandi_anggota = '$kata_sandi_hash_anggota', email_anggota = '$email_anggota_2', telepon_anggota = '$telepon_anggota_2', bank_anggota = '$bank_anggota_2', nama_rekening_anggota = '$nama_rekening_anggota_2', nomor_rekening_anggota = '$nomor_rekening_anggota_2', status_anggota = '$status_anggota_2', status_game = '$status_game_2' WHERE id_anggota = '$id_anggota'");
        if ($ubah_anggota) {
          echo '
                        <script>
                            alert("Berhasil ubah data.");
                            window.location.replace("' . $alamat_admin . 'anggota");
                        </script>
                    ';
        } else {
          echo "Proses Gagal<br>Error : " . mysqli_error($koneksi);
        }
      }
    }
  } else {
    echo '
            <script>
                alert("Nama Pengguna tidak boleh mengandung simbol maupun spasi!");
                window.location.replace("' . $alamat_admin . 'ubah_anggota/' . $id_anggota . '");
            </script>
        ';
  }
} else if (isset($_POST['hapus_data'])) {
  $hapus_data = mysqli_query($koneksi, "DELETE FROM anggota WHERE id_anggota = '$id_anggota'");
  if ($hapus_data) {
    echo '
            <script>
                alert("Berhasil hapus data.");
                window.location.replace("' . $alamat_admin . 'anggota");
            </script>
        ';
  } else {
    echo "Proses Gagal<br>Error : " . mysqli_error($koneksi);
  }
}
?>

<div class="container-xxl flex-grow-1 container-p-y">
  <div class="row gy-4 mb-4">
    <div class="col-md-6">
      <div class="fw-bold fs-4 text-center text-md-start">Anggota</div>
    </div>
    <div class="col-md-6">
      <div class="text-center text-md-end">
        <a href="<?php echo $alamat_admin . 'anggota'; ?>" class="btn btn-sm btn-primary waves-effect waves-light">
          <span class="tf-icons mdi mdi-chevron-double-left me-1"></span>
          Kembali
        </a>
      </div>
    </div>
  </div>

  <div class="card mb-4">
    <h5 class="card-header">Ubah Data Anggota</h5>
    <form method="post" class="card-body">
      <h6>1. Akun</h6>
      <div class="row g-3">
        <div class="col-md-6">
          <div class="form-floating form-floating-outline">
            <input type="text" name="nama_pengguna_anggota" class="form-control" value="<?php echo htmlspecialchars($nama_pengguna_anggota); ?>" required>
            <label>Nama Pengguna</label>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-password-toggle">
            <div class="input-group input-group-merge">
              <div class="form-floating form-floating-outline">
                <input type="password" name="kata_sandi_anggota" class="form-control" placeholder="············">
                <label>Kata Sandi</label>
              </div>
              <span class="input-group-text cursor-pointer" id="multicol-password2"><i class="mdi mdi-eye-off-outline"></i></span>
            </div>
            <div class="form-text">
              Kosongkan saja jika tidak ingin merubah kata sandi.
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-floating form-floating-outline">
            <input type="text" name="email_anggota" class="form-control" value="<?php echo htmlspecialchars($email_anggota); ?>" required>
            <label>Email</label>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-floating form-floating-outline">
            <input type="text" name="telepon_anggota" class="form-control" value="<?php echo htmlspecialchars($telepon_anggota); ?>" required>
            <label>Telepon</label>
          </div>
        </div>
      </div>
      <hr class="my-4 mx-n4">
      <h6>2. Bank</h6>
      <div class="row g-3">
        <div class="col-md-4">
          <div class="form-floating form-floating-outline mb-4">
            <select name="bank_anggota" class="form-select select2" required>
                <option value="">-- Pilih Bank / E-Wallet --</option>
                <?php $selected_bank = isset($bank_anggota) ? $bank_anggota : ''; // Variabel bantu ?>

                <optgroup label="Bank Umum">
                    <option value="Allo Bank / Bank Harda Internasional" <?php if ($selected_bank == 'Allo Bank / Bank Harda Internasional') echo 'selected'; ?>>Allo Bank / Bank Harda Internasional</option>
                    <option value="ANZ Indonesia" <?php if ($selected_bank == 'ANZ Indonesia') echo 'selected'; ?>>ANZ Indonesia</option>
                    <option value="Bank Aceh Syariah" <?php if ($selected_bank == 'Bank Aceh Syariah') echo 'selected'; ?>>Bank Aceh Syariah</option>
                    <option value="Bank Aladin Syariah" <?php if ($selected_bank == 'Bank Aladin Syariah') echo 'selected'; ?>>Bank Aladin Syariah</option>
                    <option value="Bank Amar Indonesia" <?php if ($selected_bank == 'Bank Amar Indonesia') echo 'selected'; ?>>Bank Amar Indonesia</option>
                    <option value="Bank Antardaerah" <?php if ($selected_bank == 'Bank Antardaerah') echo 'selected'; ?>>Bank Antardaerah</option>
                    <option value="Bank Artha Graha Internasional" <?php if ($selected_bank == 'Bank Artha Graha Internasional') echo 'selected'; ?>>Bank Artha Graha Internasional</option>
                    <option value="Bank Bengkulu" <?php if ($selected_bank == 'Bank Bengkulu') echo 'selected'; ?>>Bank Bengkulu</option>
                    <option value="Bank BJB" <?php if ($selected_bank == 'Bank BJB') echo 'selected'; ?>>Bank BJB</option>
                    <option value="Bank BJB Syariah" <?php if ($selected_bank == 'Bank BJB Syariah') echo 'selected'; ?>>Bank BJB Syariah</option>
                    <option value="Bank BPD DIY" <?php if ($selected_bank == 'Bank BPD DIY') echo 'selected'; ?>>Bank BPD DIY</option>
                    <option value="Bank BPD DIY Syariah" <?php if ($selected_bank == 'Bank BPD DIY SYARIAH') echo 'selected'; ?>>Bank BPD DIY Syariah</option>
                    <option value="Bank BTPN Syariah" <?php if ($selected_bank == 'Bank BTPN SYARIAH') echo 'selected'; ?>>Bank BTPN Syariah</option>
                    <option value="Bank Bukopin Syariah" <?php if ($selected_bank == 'Bank Bukopin Syariah') echo 'selected'; ?>>Bank Bukopin Syariah</option>
                    <option value="Bank Bumi Arta" <?php if ($selected_bank == 'Bank Bumi Arta') echo 'selected'; ?>>Bank Bumi Arta</option>
                    <option value="Bank Capital Indonesia" <?php if ($selected_bank == 'Bank Capital Indonesia') echo 'selected'; ?>>Bank Capital Indonesia</option>
                    <option value="BCA (Bank Central Asia)" <?php if ($selected_bank == 'BCA (Bank Central Asia)') echo 'selected'; ?>>BCA (Bank Central Asia)</option>
                    <option value="BCA Syariah" <?php if ($selected_bank == 'BCA Syariah') echo 'selected'; ?>>BCA Syariah</option>
                    <option value="Bank China Construction Bank Indonesia" <?php if ($selected_bank == 'Bank China Construction Bank Indonesia') echo 'selected'; ?>>Bank China Construction Bank Indonesia</option>
                    <option value="Bank CNB (Centratama Nasional Bank)" <?php if ($selected_bank == 'BANK CNB (Centratama Nasional Bank)') echo 'selected'; ?>>Bank CNB (Centratama Nasional Bank)</option>
                    <option value="CIMB Niaga" <?php if ($selected_bank == 'CIMB NIAGA') echo 'selected'; ?>>CIMB Niaga</option>
                    <option value="CIMB Niaga Syariah" <?php if ($selected_bank == 'CIMB NIAGA SYARIAH') echo 'selected'; ?>>CIMB Niaga Syariah</option>
                    <option value="Citibank" <?php if ($selected_bank == 'Citibank') echo 'selected'; ?>>Citibank</option>
                    <option value="Commonwealth Bank" <?php if ($selected_bank == 'COMMONWEALTH BANK') echo 'selected'; ?>>Commonwealth Bank</option>
                    <option value="CTBC (Chinatrust) Indonesia" <?php if ($selected_bank == 'CTBC (Chinatrust) Indonesia') echo 'selected'; ?>>CTBC (Chinatrust) Indonesia</option>
                    <option value="Bank Danamon" <?php if ($selected_bank == 'Bank Danamon') echo 'selected'; ?>>Bank Danamon</option>
                    <option value="Bank Danamon Syariah" <?php if ($selected_bank == 'Bank Danamon Syariah') echo 'selected'; ?>>Bank Danamon Syariah</option>
                    <option value="DBS Indonesia" <?php if ($selected_bank == 'DBS INDONESIA') echo 'selected'; ?>>DBS Indonesia</option>
                    <option value="Bank Dinar Indonesia" <?php if ($selected_bank == 'BANK DINAR INDONESIA') echo 'selected'; ?>>Bank Dinar Indonesia</option>
                    <option value="Bank DKI" <?php if ($selected_bank == 'BANK DKI') echo 'selected'; ?>>Bank DKI</option>
                    <option value="Bank DKI Syariah" <?php if ($selected_bank == 'BANK DKI SYARIAH') echo 'selected'; ?>>Bank DKI Syariah</option>
                    <option value="Bank Ganesha" <?php if ($selected_bank == 'Bank Ganesha') echo 'selected'; ?>>Bank Ganesha</option>
                    <option value="HSBC Indonesia" <?php if ($selected_bank == 'HSBC INDONESIA') echo 'selected'; ?>>HSBC Indonesia</option>
                    <option value="ICBC Indonesia" <?php if ($selected_bank == 'ICBC INDONESIA') echo 'selected'; ?>>ICBC Indonesia</option>
                    <option value="Bank Ina Perdana" <?php if ($selected_bank == 'Bank Ina Perdana') echo 'selected'; ?>>Bank Ina Perdana</option>
                    <option value="Bank Index Selindo" <?php if ($selected_bank == 'BANK INDEX SELINDO') echo 'selected'; ?>>Bank Index Selindo</option>
                    <option value="Bank of India Indonesia" <?php if ($selected_bank == 'BANK OF INDIA INDONESIA') echo 'selected'; ?>>Bank of India Indonesia</option>
                    <option value="Bank of Tokyo Mitsubishi UFJ" <?php if ($selected_bank == 'BANK OF TOKYO MITSUBISHI UFJ') echo 'selected'; ?>>Bank of Tokyo Mitsubishi UFJ</option>
                    <option value="Bank Jambi" <?php if ($selected_bank == 'Bank Jambi') echo 'selected'; ?>>Bank Jambi</option>
                    <option value="Bank Jambi Syariah" <?php if ($selected_bank == 'Bank Jambi Syariah') echo 'selected'; ?>>Bank Jambi Syariah</option>
                    <option value="Bank Jasa Jakarta" <?php if ($selected_bank == 'Bank Jasa Jakarta') echo 'selected'; ?>>Bank Jasa Jakarta</option>
                    <option value="Bank Jateng" <?php if ($selected_bank == 'Bank Jateng') echo 'selected'; ?>>Bank Jateng</option>
                    <option value="Bank Jateng Syariah" <?php if ($selected_bank == 'Bank Jateng Syariah') echo 'selected'; ?>>Bank Jateng Syariah</option>
                    <option value="Bank Jatim" <?php if ($selected_bank == 'Bank Jatim') echo 'selected'; ?>>Bank Jatim</option>
                    <option value="Bank Jatim Syariah" <?php if ($selected_bank == 'Bank Jatim Syariah') echo 'selected'; ?>>Bank Jatim Syariah</option>
                    <option value="Jago / Artos" <?php if ($selected_bank == 'Jago / Artos') echo 'selected'; ?>>Jago / Artos</option>
                    <option value="Bank Jago Syariah" <?php if ($selected_bank == 'Bank Jago Syariah') echo 'selected'; ?>>Bank Jago Syariah</option>
                    <option value="Bank Kalbar" <?php if ($selected_bank == 'Bank Kalbar') echo 'selected'; ?>>Bank Kalbar</option>
                    <option value="Bank Kalbar Syariah" <?php if ($selected_bank == 'Bank Kalbar Syariah') echo 'selected'; ?>>Bank Kalbar Syariah</option>
                    <option value="Bank Kalsel" <?php if ($selected_bank == 'Bank Kalsel') echo 'selected'; ?>>Bank Kalsel</option>
                    <option value="Bank Kalsel Syariah" <?php if ($selected_bank == 'Bank Kalsel Syariah') echo 'selected'; ?>>Bank Kalsel Syariah</option>
                    <option value="Bank Kalteng" <?php if ($selected_bank == 'Bank Kalteng') echo 'selected'; ?>>Bank Kalteng</option>
                    <option value="Bank Kaltimtara" <?php if ($selected_bank == 'Bank Kaltimtara') echo 'selected'; ?>>Bank Kaltimtara</option>
                    <option value="Bank Kaltim Syariah" <?php if ($selected_bank == 'Bank Kaltim Syariah') echo 'selected'; ?>>Bank Kaltim Syariah</option>
                    <option value="Krom Bank Indonesia" <?php if ($selected_bank == 'KROM BANK INDONESIA') echo 'selected'; ?>>Krom Bank Indonesia</option>
                    <option value="Bank Lampung" <?php if ($selected_bank == 'Bank Lampung') echo 'selected'; ?>>Bank Lampung</option>
                    <option value="LINE Bank / KEB Hana" <?php if ($selected_bank == 'LINE BANK / KEB HANA') echo 'selected'; ?>>LINE Bank / KEB Hana</option>
                    <option value="Bank Maluku" <?php if ($selected_bank == 'Bank Maluku') echo 'selected'; ?>>Bank Maluku</option>
                    <option value="Bank Mandiri" <?php if ($selected_bank == 'Bank Mandiri') echo 'selected'; ?>>Bank Mandiri</option>
                    <option value="Bank MANTAP (Mandiri Taspen)" <?php if ($selected_bank == 'Bank MANTAP (Mandiri Taspen)') echo 'selected'; ?>>Bank MANTAP (Mandiri Taspen)</option>
                    <option value="Bank Multi Arta Sentosa (Bank MAS)" <?php if ($selected_bank == 'Bank Multi Arta Sentosa (Bank MAS)') echo 'selected'; ?>>Bank Multi Arta Sentosa (Bank MAS)</option>
                    <option value="Bank Maspion Indonesia" <?php if ($selected_bank == 'Bank Maspion Indonesia') echo 'selected'; ?>>Bank Maspion Indonesia</option>
                    <option value="Maybank Indonesia" <?php if ($selected_bank == 'Maybank Indonesia') echo 'selected'; ?>>Maybank Indonesia</option>
                    <option value="Maybank Syariah" <?php if ($selected_bank == 'Maybank Syariah') echo 'selected'; ?>>Maybank Syariah</option>
                    <option value="Bank Mayapada" <?php if ($selected_bank == 'Bank Mayapada') echo 'selected'; ?>>Bank Mayapada</option>
                    <option value="Bank Mayora Indonesia" <?php if ($selected_bank == 'Bank Mayora Indonesia') echo 'selected'; ?>>Bank Mayora Indonesia</option>
                    <option value="Bank Mega" <?php if ($selected_bank == 'Bank Mega') echo 'selected'; ?>>Bank Mega</option>
                    <option value="Bank Mega Syariah" <?php if ($selected_bank == 'Bank Mega Syariah') echo 'selected'; ?>>Bank Mega Syariah</option>
                    <option value="Bank Mestika Dharma" <?php if ($selected_bank == 'Bank Mestika Dharma') echo 'selected'; ?>>Bank Mestika Dharma</option>
                    <option value="Bank Mizuho Indonesia" <?php if ($selected_bank == 'Bank Mizuho Indonesia') echo 'selected'; ?>>Bank Mizuho Indonesia</option>
                    <option value="Motion / MNC Bank" <?php if ($selected_bank == 'Motion / MNC Bank') echo 'selected'; ?>>Motion / MNC Bank</option>
                    <option value="Bank Muamalat" <?php if ($selected_bank == 'Bank Muamalat') echo 'selected'; ?>>Bank Muamalat</option>
                    <option value="Bank Mutiara" <?php if ($selected_bank == 'Bank Mutiara') echo 'selected'; ?>>Bank Mutiara</option>
                    <option value="Bank Nagari" <?php if ($selected_bank == 'Bank Nagari') echo 'selected'; ?>>Bank Nagari</option>
                    <option value="Bank Nagari Syariah" <?php if ($selected_bank == 'Bank Nagari Syariah') echo 'selected'; ?>>Bank Nagari Syariah</option>
                    <option value="Neo Commerce / Yudha Bhakti" <?php if ($selected_bank == 'Neo Commerce / Yudha Bhakti') echo 'selected'; ?>>Neo Commerce / Yudha Bhakti</option>
                    <option value="Nobu (Nationalnobu) Bank" <?php if ($selected_bank == 'Nobu (Nationalnobu) Bank') echo 'selected'; ?>>Nobu (Nationalnobu) Bank</option>
                    <option value="BNI (Bank Negara Indonesia)" <?php if ($selected_bank == 'BNI (Bank Negara Indonesia)') echo 'selected'; ?>>BNI (Bank Negara Indonesia)</option>
                    <option value="BNP Paribas Indonesia" <?php if ($selected_bank == 'BNP Paribas Indonesia') echo 'selected'; ?>>BNP Paribas Indonesia</option>
                    <option value="Bank NTB Syariah" <?php if ($selected_bank == 'Bank NTB Syariah') echo 'selected'; ?>>Bank NTB Syariah</option>
                    <option value="Bank NTT" <?php if ($selected_bank == 'Bank NTT') echo 'selected'; ?>>Bank NTT</option>
                    <option value="Bank Nusantara Parahyangan" <?php if ($selected_bank == 'Bank Nusantara Parahyangan') echo 'selected'; ?>>Bank Nusantara Parahyangan</option>
                    <option value="Bank OCBC NISP" <?php if ($selected_bank == 'Bank OCBC NISP') echo 'selected'; ?>>Bank OCBC NISP</option>
                    <option value="Bank OCBC NISP Syariah" <?php if ($selected_bank == 'Bank OCBC NISP Syariah') echo 'selected'; ?>>Bank OCBC NISP Syariah</option>
                    <option value="Panin Bank" <?php if ($selected_bank == 'Panin Bank') echo 'selected'; ?>>Panin Bank</option>
                    <option value="Panin Dubai Syariah" <?php if ($selected_bank == 'Panin Dubai Syariah') echo 'selected'; ?>>Panin Dubai Syariah</option>
                    <option value="Bank Papua" <?php if ($selected_bank == 'Bank Papua') echo 'selected'; ?>>Bank Papua</option>
                    <option value="Bank Permata" <?php if ($selected_bank == 'Bank Permata') echo 'selected'; ?>>Bank Permata</option>
                    <option value="Bank Permata Syariah" <?php if ($selected_bank == 'Bank Permata Syariah') echo 'selected'; ?>>Bank Permata Syariah</option>
                    <option value="Bank Prima Master" <?php if ($selected_bank == 'Bank Prima Master') echo 'selected'; ?>>Bank Prima Master</option>
                    <option value="QNB Indonesia" <?php if ($selected_bank == 'QNB Indonesia') echo 'selected'; ?>>QNB Indonesia</option>
                    <option value="Rabobank International Indonesia" <?php if ($selected_bank == 'Rabobank International Indonesia') echo 'selected'; ?>>Rabobank International Indonesia</option>
                    <option value="BRI (Bank Rakyat Indonesia)" <?php if ($selected_bank == 'BRI (Bank Rakyat Indonesia)') echo 'selected'; ?>>BRI (Bank Rakyat Indonesia)</option>
                    <option value="BRI Agroniaga" <?php if ($selected_bank == 'BRI Agroniaga') echo 'selected'; ?>>BRI Agroniaga</option>
                    <option value="Bank Resona Perdania" <?php if ($selected_bank == 'Bank Resona Perdania') echo 'selected'; ?>>Bank Resona Perdania</option>
                    <option value="Bank Riau Kepri" <?php if ($selected_bank == 'Bank Riau Kepri') echo 'selected'; ?>>Bank Riau Kepri</option>
                    <option value="Bank Sahabat Sampoerna" <?php if ($selected_bank == 'Bank Sahabat Sampoerna') echo 'selected'; ?>>Bank Sahabat Sampoerna</option>
                    <option value="Seabank / Bank BKE" <?php if ($selected_bank == 'Seabank / Bank BKE') echo 'selected'; ?>>Seabank / Bank BKE</option>
                    <option value="SBI Indonesia" <?php if ($selected_bank == 'SBI INDONESIA') echo 'selected'; ?>>SBI Indonesia</option>
                    <option value="Bank Shinhan Indonesia" <?php if ($selected_bank == 'Bank Shinhan Indonesia') echo 'selected'; ?>>Bank Shinhan Indonesia</option>
                    <option value="Bank Sinarmas" <?php if ($selected_bank == 'Bank Sinarmas') echo 'selected'; ?>>Bank Sinarmas</option>
                    <option value="Bank Sinarmas Syariah" <?php if ($selected_bank == 'Bank Sinarmas Syariah') echo 'selected'; ?>>Bank Sinarmas Syariah</option>
                    <option value="Standard Chartered Bank" <?php if ($selected_bank == 'STANDARD CHARTERED BANK') echo 'selected'; ?>>Standard Chartered Bank</option>
                    <option value="Bank Sulteng" <?php if ($selected_bank == 'Bank Sulteng') echo 'selected'; ?>>Bank Sulteng</option>
                    <option value="Bank Sultra" <?php if ($selected_bank == 'Bank SULTRA') echo 'selected'; ?>>Bank Sultra</option>
                    <option value="Bank Sulselbar" <?php if ($selected_bank == 'Bank Sulselbar') echo 'selected'; ?>>Bank Sulselbar</option>
                    <option value="Bank Sulselbar Syariah" <?php if ($selected_bank == 'BANK SULSELBAR SYARIAH') echo 'selected'; ?>>Bank Sulselbar Syariah</option>
                    <option value="Bank SulutGo" <?php if ($selected_bank == 'Bank SulutGo') echo 'selected'; ?>>Bank SulutGo</option>
                    <option value="Bank Sumsel Babel" <?php if ($selected_bank == 'Bank Sumsel Babel') echo 'selected'; ?>>Bank Sumsel Babel</option>
                    <option value="Bank Sumsel Babel Syariah" <?php if ($selected_bank == 'BANK SUMSEL BABEL SYARIAH') echo 'selected'; ?>>Bank Sumsel Babel Syariah</option>
                    <option value="Bank Sumut" <?php if ($selected_bank == 'Bank Sumut') echo 'selected'; ?>>Bank Sumut</option>
                    <option value="Bank Sumut Syariah" <?php if ($selected_bank == 'BANK SUMUT SYARIAH') echo 'selected'; ?>>Bank Sumut Syariah</option>
                    <option value="Superbank" <?php if ($selected_bank == 'SUPERBANK') echo 'selected'; ?>>Superbank</option>
                    <option value="BTPN" <?php if ($selected_bank == 'BTPN') echo 'selected'; ?>>BTPN</option>
                    <option value="TMRW by UOB" <?php if ($selected_bank == 'TMRW by UOB') echo 'selected'; ?>>TMRW by UOB</option>
                    <option value="Wokee by Bukopin" <?php if ($selected_bank == 'Wokee by Bukopin') echo 'selected'; ?>>Wokee by Bukopin</option>
                    <option value="Bank Woori Saudara" <?php if ($selected_bank == 'Bank Woori Saudara') echo 'selected'; ?>>Bank Woori Saudara</option>
                </optgroup>

                <optgroup label="E-Wallet">
                    <option value="DANA" <?php if ($selected_bank == 'DANA') echo 'selected'; ?>>DANA</option>
                    <option value="GOPAY" <?php if ($selected_bank == 'GOPAY') echo 'selected'; ?>>GoPay</option>
                    <option value="ISKK WALLET" <?php if ($selected_bank == 'ISKK WALLET') echo 'selected'; ?>>ISKK Wallet</option>
                    <option value="JENIUS PAY" <?php if ($selected_bank == 'JENIUS PAY') echo 'selected'; ?>>Jenius Pay</option>
                    <option value="LINKAJA" <?php if ($selected_bank == 'LINKAJA') echo 'selected'; ?>>LinkAja</option>
                    <option value="OVO" <?php if ($selected_bank == 'OVO') echo 'selected'; ?>>OVO</option>
                    <option value="SHOPEEPAY" <?php if ($selected_bank == 'SHOPEEPAY') echo 'selected'; ?>>ShopeePay</option>
                </optgroup>

                <optgroup label="Lain-lain (Operator Seluler / Lainnya)">
                    <option value="AXIS" <?php if ($selected_bank == 'AXIS') echo 'selected'; ?>>AXIS</option>
                    <option value="INDOSAT" <?php if ($selected_bank == 'INDOSAT') echo 'selected'; ?>>INDOSAT</option>
                    <option value="SAKUKU" <?php if ($selected_bank == 'SAKUKU') echo 'selected'; ?>>SAKUKU</option>
                    <option value="SIMPATI" <?php if ($selected_bank == 'SIMPATI') echo 'selected'; ?>>SIMPATI</option>
                    <option value="XL" <?php if ($selected_bank == 'XL') echo 'selected'; ?>>XL</option>
                </optgroup>
            </select>
            <label>Bank</label>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-floating form-floating-outline">
            <input type="text" name="nama_rekening_anggota" class="form-control" value="<?php echo htmlspecialchars($nama_rekening_anggota); ?>" required>
            <label>Nama Rekening</label>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-floating form-floating-outline">
            <input type="text" name="nomor_rekening_anggota" class="form-control" value="<?php echo htmlspecialchars($nomor_rekening_anggota); ?>" required>
            <label>Nomor Rekening</label>
          </div>
        </div>
      </div>
      <hr class="my-4 mx-n4">
      <h6>3. Lainnya</h6>
      <div class="row g-3">
        <div class="col-md-6"> <div class="form-floating form-floating-outline mb-4">
            <select name="status_anggota" class="form-select select2" required>
              <option value="aktif" <?php if ($status_anggota == "aktif") echo 'selected'; ?>>Aktif</option>
              <option value="terkunci" <?php if ($status_anggota == "terkunci") echo 'selected'; ?>>Terkunci</option>
            </select>
            <label>Status</label>
          </div>
        </div>

        <div class="col-md-6"> <div class="form-floating form-floating-outline mb-4">
            <select name="status_game" class="form-select select2" required>
              <option value="Aktif" <?php if ($status_game == "Aktif") echo 'selected'; ?>>Aktif</option>
              <option value="Tidak Aktif" <?php if ($status_game == "Tidak Aktif") echo 'selected'; ?>>Tidak Aktif</option>
            </select>
            <label>Status Game</label>
          </div>
        </div>
      </div>
      <div class="pt-4 text-end">
        <button type="button" class="btn btn-danger waves-effect waves-light me-sm-3 me-1" data-bs-toggle="modal" data-bs-target="#hapus_data">
          <span class="tf-icons mdi mdi-delete me-1"></span>
          Hapus
        </button>
        <button type="submit" name="ubah_data" class="btn btn-primary waves-effect waves-light">
          <span class="tf-icons mdi mdi-content-save me-1"></span>
          Simpan
        </button>
      </div>
    </form>
  </div>
</div>
<div class="modal fade" id="hapus_data" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5">Hapus Data</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="post">
        <div class="modal-body">
          Yakin ingin menghapus data ini?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" name="hapus_data" class="btn btn-danger">Hapus</button>
        </div>
      </form>
    </div>
  </div>
</div>