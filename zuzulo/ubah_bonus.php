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
  if (isset($_GET['id_bonus'])) {
    $id_bonus = $_GET['id_bonus'];
    $bonus = mysqli_query($koneksi, "SELECT * FROM bonus WHERE id_bonus = '$id_bonus'");
    $data_bonus = mysqli_fetch_array($bonus);
    $judul_bonus = $data_bonus['judul_bonus'];
  } else {
    echo '
      <script>
        alert("Pilih bonus yang ingin diubah!");
        window.location.replace("'.$alamat_admin.'bonus");
      </script>
    ';
  }
  if (isset($_POST['ubah_data'])) {
    $judul_bonus_2 = $_POST['judul_bonus'];
    $ubah_bonus = mysqli_query($koneksi, "UPDATE bonus SET judul_bonus = '$judul_bonus_2' WHERE id_bonus = '$id_bonus'");
    if ($ubah_bonus) {
      echo '
        <script>
          alert("Berhasil ubah data.");
          window.location.replace("'.$alamat_admin.'bonus");
        </script>
      ';
    } else {
      echo "Proses Gagal<br>Error : ".$ubah_bonus."<br>".mysqli_error($koneksi);
    }
  } else if (isset($_POST['hapus_data'])) {
    $hapus_data = mysqli_query($koneksi, "DELETE FROM bonus WHERE id_bonus = '$id_bonus'");
    if ($hapus_data) {
      echo '
        <script>
          alert("Berhasil hapus data.");
          window.location.replace("'.$alamat_admin.'bonus");
        </script>
      ';
    } else {
      echo "Proses Gagal<br>Error : ".$hapus_bonus."<br>".mysqli_error($koneksi);
    }
  }
?>
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="row gy-4 mb-4">
    <div class="col-md-6">
      <div class="fw-bold fs-4 text-center text-md-start">Bonus</div>
    </div>
    <div class="col-md-6">
      <div class="text-center text-md-end">
        <a href="<?php echo $alamat_admin.'bonus'; ?>" class="btn btn-sm btn-primary waves-effect waves-light">
          <span class="tf-icons mdi mdi-chevron-double-left me-1"></span>
          Kembali
        </a>
      </div>
    </div>
  </div>

  <div class="card mb-4">
    <h5 class="card-header">Ubah Data Bonus</h5>
    <form method="post" class="card-body">
      <div class="row g-3">
        <div class="col-12">
          <div class="form-floating form-floating-outline">
            <input type="text" name="judul_bonus" class="form-control" value="<?php echo $judul_bonus; ?>" required>
            <label>Judul</label>
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