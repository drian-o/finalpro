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
    $judul_bonus = $_POST['judul_bonus'];
    $tambah_bonus = mysqli_query($koneksi, "INSERT INTO bonus (judul_bonus) VALUES ('$judul_bonus')");
    if ($tambah_bonus) {
      echo '
        <script>
          alert("Berhasil tambah data.");
          window.location.replace("'.$alamat_admin.'bonus");
        </script>
      ';
    } else {
      echo "Proses Gagal<br>Error : ".$tambah_bonus."<br>".mysqli_error($koneksi);
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
    <h5 class="card-header">Tambah Data Bonus</h5>
    <form method="post" class="card-body">
      <div class="row g-3">
        <div class="col-12">
          <div class="form-floating form-floating-outline">
            <input type="text" name="judul_bonus" class="form-control" placeholder="Judul" required>
            <label>Judul</label>
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