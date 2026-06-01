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
    $nama_staff = $_POST['nama_staff'];
    $nama_pengguna_staff = strtolower($_POST['nama_pengguna_staff']);
    $kata_sandi_staff = $_POST['kata_sandi_staff'];
    $pin_staff = $_POST['pin_staff'];
    $opsi = ['cost' => 12];
    $kata_sandi_hash_staff = password_hash($kata_sandi_staff, PASSWORD_BCRYPT, $opsi);
    $pin_hash_staff = password_hash($pin_staff, PASSWORD_BCRYPT, $opsi);
    if (preg_match('/^[a-zA-Z0-9\s]+$/', $nama_pengguna_staff)) {
      $cek_nama_pengguna_staff = mysqli_query($koneksi, "SELECT * FROM staff WHERE nama_pengguna_staff = '$nama_pengguna_staff'");
      if (mysqli_num_rows($cek_nama_pengguna_staff) >= 1) {
        echo '
          <script>
            alert("Nama Pengguna sudah terdaftar, gunakan yang lainnya.");
            window.location.replace("'.$alamat_admin.'staff");
          </script>
        ';
      } else {
        $tambah_staff = mysqli_query($koneksi, "INSERT INTO staff (nama_staff, nama_pengguna_staff, kata_sandi_staff, pin_staff) VALUES ('$nama_staff', '$nama_pengguna_staff', '$kata_sandi_hash_staff', '$pin_hash_staff')");
        if ($tambah_staff) {
          echo '
            <script>
              alert("Berhasil tambah data.");
              window.location.replace("'.$alamat_admin.'staff");
            </script>
          ';
        } else {
          echo "Proses Gagal<br>Error : ".$tambah_staff."<br>".mysqli_error($koneksi);
        }
      }
    } else {
      echo '
        <script>
          alert("Nama Pengguna tidak boleh mengandung simbol maupun spasi!");
          window.location.replace("'.$alamat_admin.'staff");
        </script>
      ';
    }
  }
?>
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="row gy-4 mb-4">
    <div class="col-md-6">
      <div class="fw-bold fs-4 text-center text-md-start">Staff</div>
    </div>
    <div class="col-md-6">
      <div class="text-center text-md-end">
        <a href="<?php echo $alamat_admin.'staff'; ?>" class="btn btn-sm btn-primary waves-effect waves-light">
          <span class="tf-icons mdi mdi-chevron-double-left me-1"></span>
          Kembali
        </a>
      </div>
    </div>
  </div>

  <div class="card mb-4">
    <h5 class="card-header">Tambah Data Staff</h5>
    <form method="post" class="card-body">
      <h6>1. Akun</h6>
      <div class="row g-3">
        <div class="col-md-6">
          <div class="form-floating form-floating-outline">
            <input type="text" name="nama_staff" class="form-control" placeholder="Nama" required>
            <label>Nama</label>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-floating form-floating-outline">
            <input type="text" name="nama_pengguna_staff" class="form-control" placeholder="Nama Pengguna" required>
            <label>Nama Pengguna</label>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-password-toggle">
            <div class="input-group input-group-merge">
              <div class="form-floating form-floating-outline">
                <input type="password" name="kata_sandi_staff" class="form-control" placeholder="············" required>
                <label>Kata Sandi</label>
              </div>
              <span class="input-group-text cursor-pointer" id="multicol-password2"><i class="mdi mdi-eye-off-outline"></i></span>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-password-toggle">
            <div class="input-group input-group-merge">
              <div class="form-floating form-floating-outline">
                <input type="password" name="pin_staff" class="form-control" placeholder="············" required>
                <label>Pin</label>
              </div>
              <span class="input-group-text cursor-pointer" id="multicol-confirm-password2"><i class="mdi mdi-eye-off-outline"></i></span>
            </div>
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