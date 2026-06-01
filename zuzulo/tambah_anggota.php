<?php
  include_once '../koneksi.php';
  if (!isset($_SESSION['kode_admin'])) {
    echo '
      <script>
        alert("Terjadi kesalahan, harap masuk kembali!");
        window.location.replace("'.$alamat_admin.'keluar.php");
      </script>
    ';
  }
  if (isset($_POST['tambah_data'])) {
    $nama_pengguna_anggota = strtolower($_POST['nama_pengguna_anggota']);
    $kata_sandi_anggota = $_POST['kata_sandi_anggota'];
    $email_anggota = $_POST['email_anggota'];
    $telepon_anggota = $_POST['telepon_anggota'];
    $bank_anggota = $_POST['bank_anggota'];
    $nama_rekening_anggota = $_POST['nama_rekening_anggota'];
    $nomor_rekening_anggota = $_POST['nomor_rekening_anggota'];
    $opsi = ['cost' => 12];
    $kata_sandi_hash_anggota = password_hash($kata_sandi_anggota, PASSWORD_BCRYPT, $opsi);
    if (preg_match('/^[a-zA-Z0-9\s]+$/', $nama_pengguna_anggota)) {
      $cek_nama_pengguna_anggota = mysqli_query($koneksi, "SELECT * FROM anggota WHERE nama_pengguna_anggota = '$nama_pengguna_anggota'");
      if (mysqli_num_rows($cek_nama_pengguna_anggota) >= 1) {
        echo '
          <script>
            alert("Nama Pengguna sudah terdaftar, gunakan yang lainnya.");
            window.location.replace("'.$alamat_admin.'anggota");
          </script>
        ';
      } else {
        $tambah_anggota = mysqli_query($koneksi, "INSERT INTO anggota (nama_pengguna_anggota, kata_sandi_anggota, email_anggota, telepon_anggota, bank_anggota, nama_rekening_anggota, nomor_rekening_anggota) VALUES ('$nama_pengguna_anggota', '$kata_sandi_hash_anggota', '$email_anggota', '$telepon_anggota', '$bank_anggota', '$nama_rekening_anggota', '$nomor_rekening_anggota')");
        if ($tambah_anggota) {
          echo '
            <script>
              alert("Berhasil tambah data.");
              window.location.replace("'.$alamat_admin.'anggota");
            </script>
          ';
        } else {
          echo "Proses Gagal<br>Error : ".$tambah_anggota."<br>".mysqli_error($koneksi);
        }
      }
    } else {
      echo '
        <script>
          alert("Nama Pengguna tidak boleh mengandung simbol maupun spasi!");
          window.location.replace("'.$alamat_admin.'anggota");
        </script>
      ';
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
        <a href="<?php echo $alamat_admin.'anggota'; ?>" class="btn btn-sm btn-primary waves-effect waves-light">
          <span class="tf-icons mdi mdi-chevron-double-left me-1"></span>
          Kembali
        </a>
      </div>
    </div>
  </div>

  <div class="card mb-4">
    <h5 class="card-header">Tambah Data Anggota</h5>
    <form method="post" class="card-body">
      <h6>1. Akun</h6>
      <div class="row g-3">
        <div class="col-md-6">
          <div class="form-floating form-floating-outline">
            <input type="text" name="nama_pengguna_anggota" class="form-control" placeholder="Nama Pengguna" required>
            <label>Nama Pengguna</label>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-password-toggle">
            <div class="input-group input-group-merge">
              <div class="form-floating form-floating-outline">
                <input type="password" name="kata_sandi_anggota" class="form-control" placeholder="············" required>
                <label>Kata Sandi</label>
              </div>
              <span class="input-group-text cursor-pointer" id="multicol-password2"><i class="mdi mdi-eye-off-outline"></i></span>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-floating form-floating-outline">
            <input type="text" name="email_anggota" class="form-control" placeholder="Email" required>
            <label>Email</label>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-floating form-floating-outline">
            <input type="text" name="telepon_anggota" class="form-control" placeholder="Telepon" required>
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
              <option value="" selected>-- Pilih Bank --</option>
              <option value="AXIS">AXIS</option>
              <option value="ARTHAGRAHA">ARTHA GRAHA</option>
              <option value="BCA">BCA</option>
              <option value="BJB">BJB</option>
              <option value="BNI">BNI</option>
              <option value="BRI">BRI</option>
              <option value="BTPN">BTPN</option>
              <option value="BUKOPIN">BUKOPIN</option>
              <option value="CIMB">CIMB</option>
              <option value="DANA">DANA</option>
              <option value="DANAMON">DANAMON</option>
              <option value="DKI">DKI</option>
              <option value="GOPAY">GOPAY</option>
              <option value="INDOSAT">INDOSAT</option>
              <option value="LINKAJA">LINKAJA</option>
              <option value="MANDIRI">MANDIRI</option>
              <option value="MEGA">MEGA</option>
              <option value="MYBANK">MYBANK</option>
              <option value="OVO">OVO</option>
              <option value="SAKUKU">SAKUKU</option>
              <option value="SHOPEEPAY">SHOPEEPAY</option>
              <option value="SIMPATI">SIMPATI</option>
              <option value="SINARMAS">SINARMAS</option>
              <option value="SYARIAHINDONESIA">SYARIAH INDONESIA</option>
              <option value="XL">XL</option>
            </select>
            <label>Bank</label>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-floating form-floating-outline">
            <input type="text" name="nama_rekening_anggota" class="form-control" placeholder="Nama Rekening" required>
            <label>Nama Rekening</label>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-floating form-floating-outline">
            <input type="text" name="nomor_rekening_anggota" class="form-control" placeholder="Nomor Rekening" required>
            <label>Nomor Rekening</label>
          </div>
        </div>
      </div>
      <div class="pt-4 text-end">
        <button type="submit" name="tambah_data" class="btn btn-primary waves-effect waves-light">
          <span class="tf-icons mdi mdi-content-save me-1"></span>
          Simpan
        </button>
      </div>
    </form>
  </div>
</div>