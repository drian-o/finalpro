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
  if (isset($_GET['id_staff'])) {
    $id_staff = $_GET['id_staff'];
    $staff = mysqli_query($koneksi, "SELECT * FROM staff WHERE id_staff = '$id_staff'");
    $data_staff = mysqli_fetch_array($staff);
    $nama_staff = $data_staff['nama_staff'];
    $nama_pengguna_staff = $data_staff['nama_pengguna_staff'];
    $kata_sandi_staff = $data_staff['kata_sandi_staff'];
    $pin_staff = $data_staff['pin_staff'];
    $status_staff = $data_staff['status_staff'];
  } else {
    echo '
      <script>
        alert("Pilih staff yang ingin diubah!");
        window.location.replace("'.$alamat_admin.'staff");
      </script>
    ';
  }
  if (isset($_POST['ubah_data'])) {
    $nama_staff_2 = $_POST['nama_staff'];
    $nama_pengguna_staff_2 = strtolower($_POST['nama_pengguna_staff']);
    $kata_sandi_staff_2 = $_POST['kata_sandi_staff'];
    $pin_staff_2 = $_POST['pin_staff'];
    $status_staff_2 = $_POST['status_staff'];
    $opsi = ['cost' => 12];
    $kata_sandi_hash_staff = password_hash($kata_sandi_staff_2, PASSWORD_BCRYPT, $opsi);
    $pin_hash_staff = password_hash($pin_staff_2, PASSWORD_BCRYPT, $opsi);
    if (preg_match('/^[a-zA-Z0-9\s]+$/', $nama_pengguna_staff_2)) {
      $cek_nama_pengguna_staff = mysqli_query($koneksi, "SELECT * FROM staff WHERE NOT id_staff = '$id_staff' AND nama_pengguna_staff = '$nama_pengguna_staff_2'");
      if (mysqli_num_rows($cek_nama_pengguna_staff) >= 1) {
        echo '
          <script>
            alert("Nama Pengguna sudah terdaftar, gunakan yang lainnya.");
            window.location.replace("'.$alamat_admin.'ubah_staff/'.$id_staff.'");
          </script>
        ';
      } else {
        if (empty($kata_sandi_staff_2)) {
          if (empty($pin_staff_2)) {
            $ubah_staff = mysqli_query($koneksi, "UPDATE staff SET nama_staff = '$nama_staff_2', nama_pengguna_staff = '$nama_pengguna_staff_2', status_staff = '$status_staff_2' WHERE id_staff = '$id_staff'");
            if ($ubah_staff) {
              echo '
                <script>
                  alert("Berhasil ubah data.");
                  window.location.replace("'.$alamat_admin.'staff");
                </script>
              ';
            } else {
              echo "Proses Gagal<br>Error : ".$ubah_staff."<br>".mysqli_error($koneksi);
            }
          } else {
            $ubah_staff = mysqli_query($koneksi, "UPDATE staff SET nama_staff = '$nama_staff_2', nama_pengguna_staff = '$nama_pengguna_staff_2', pin_staff = '$pin_hash_staff', status_staff = '$status_staff_2' WHERE id_staff = '$id_staff'");
            if ($ubah_staff) {
              echo '
                <script>
                  alert("Berhasil ubah data.");
                  window.location.replace("'.$alamat_admin.'staff");
                </script>
              ';
            } else {
              echo "Proses Gagal<br>Error : ".$ubah_staff."<br>".mysqli_error($koneksi);
            }
          }
        } else {
          if (empty($pin_staff_2)) {
            $ubah_staff = mysqli_query($koneksi, "UPDATE staff SET nama_staff = '$nama_staff_2', nama_pengguna_staff = '$nama_pengguna_staff_2', kata_sandi_staff = '$kata_sandi_hash_staff', status_staff = '$status_staff_2' WHERE id_staff = '$id_staff'");
            if ($ubah_staff) {
              echo '
                <script>
                  alert("Berhasil ubah data.");
                  window.location.replace("'.$alamat_admin.'staff");
                </script>
              ';
            } else {
              echo "Proses Gagal<br>Error : ".$ubah_staff."<br>".mysqli_error($koneksi);
            }
          } else {
            $ubah_staff = mysqli_query($koneksi, "UPDATE staff SET nama_staff = '$nama_staff_2', nama_pengguna_staff = '$nama_pengguna_staff_2', kata_sandi_staff = '$kata_sandi_hash_staff', pin_staff = '$pin_hash_staff', status_staff = '$status_staff_2' WHERE id_staff = '$id_staff'");
            if ($ubah_staff) {
              echo '
                <script>
                  alert("Berhasil ubah data.");
                  window.location.replace("'.$alamat_admin.'staff");
                </script>
              ';
            } else {
              echo "Proses Gagal<br>Error : ".$ubah_staff."<br>".mysqli_error($koneksi);
            }
          }
        }
      }
    } else {
      echo '
        <script>
          alert("Nama Pengguna tidak boleh mengandung simbol maupun spasi!");
          window.location.replace("'.$alamat_admin.'ubah_staff/'.$id_staff.'");
        </script>
      ';
    }
  } else if (isset($_POST['hapus_data'])) {
    $hapus_data = mysqli_query($koneksi, "DELETE FROM staff WHERE id_staff = '$id_staff'");
    if ($hapus_data) {
      echo '
        <script>
          alert("Berhasil hapus data.");
          window.location.replace("'.$alamat_admin.'staff");
        </script>
      ';
    } else {
      echo "Proses Gagal<br>Error : ".$hapus_staff."<br>".mysqli_error($koneksi);
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
    <h5 class="card-header">Ubah Data Staff</h5>
    <form method="post" class="card-body">
    <h6>1. Akun</h6>
      <div class="row g-3">
        <div class="col-md-6">
          <div class="form-floating form-floating-outline">
            <input type="text" name="nama_staff" class="form-control" value="<?php echo $nama_staff; ?>" required>
            <label>Nama</label>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-floating form-floating-outline">
            <input type="text" name="nama_pengguna_staff" class="form-control" value="<?php echo $nama_pengguna_staff; ?>" required>
            <label>Nama Pengguna</label>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-password-toggle">
            <div class="input-group input-group-merge">
              <div class="form-floating form-floating-outline">
                <input type="password" name="kata_sandi_staff" class="form-control" placeholder="············">
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
          <div class="form-password-toggle">
            <div class="input-group input-group-merge">
              <div class="form-floating form-floating-outline">
                <input type="password" name="pin_staff" class="form-control" placeholder="············">
                <label>Pin</label>
              </div>
              <span class="input-group-text cursor-pointer" id="multicol-confirm-password2"><i class="mdi mdi-eye-off-outline"></i></span>
            </div>
            <div class="form-text">
              Kosongkan saja jika tidak ingin merubah pin.
            </div>
          </div>
        </div>
        <div class="col-12">
          <div class="form-floating form-floating-outline mb-4">
            <select name="status_staff" class="form-select select2" required>
              <?php
                if ($status_staff == "aktif") {
                  echo '
                    <option value="aktif" selected>Aktif</option>
                    <option value="terkunci">Terkunci</option>
                  ';
                } else {
                  echo '
                    <option value="terkunci" selected>Terkunci</option>
                    <option value="aktif">Aktif</option>
                  ';
                }
              ?>
            </select>
            <label>Status</label>
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
<!-- Modal Hapus Data -->
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