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
    $judul_bukti_jp = $_POST['judul_bukti_jp'];
    $deskripsi_bukti_jp = $_POST['deskripsi_bukti_jp'];
    $tanggal_bukti_jp = $_POST['tanggal_bukti_jp'];
    $link_bukti_jp = $_POST['link_bukti_jp'];
    $random = rand(1000000000, 9999999999);
    $tmp_file = $_FILES['gambar_bukti_jp']['tmp_name'];
    $nama_file = $_FILES['gambar_bukti_jp']['name'];
    $format =  array('png', 'PNG', 'jpg', 'JPG', 'jpeg', 'JPEG', 'gif', 'GIF', 'svg', 'SVG');
    $extensi = pathinfo($nama_file, PATHINFO_EXTENSION);
    if (!in_array($extensi, $format)) {
      echo '
        <script>
          alert("Format gambar salah, format gambar yang diperbolehkan adalah PNG, JPG, JPEG, GIF, dan SVG!");
          window.location.replace("'.$alamat_admin.'tambah_bukti_jp");
        </script>
      ';
    } else {
      $file = strtolower(str_replace(" ", "_", $nama_file));
      $file_input = $random.'_'.$file;
      $lokasi_simpan = "../assets/img/bukti_jp/".$file_input;
      if (move_uploaded_file($tmp_file, $lokasi_simpan)) {
        $tambah_bukti_jp = mysqli_query($koneksi, "INSERT INTO bukti_jp (judul_bukti_jp, deskripsi_bukti_jp, gambar_bukti_jp, tanggal_bukti_jp, link_bukti_jp) VALUES ('$judul_bukti_jp', '$deskripsi_bukti_jp', '$file_input', '$tanggal_bukti_jp', '$link_bukti_jp')");
        if ($tambah_bukti_jp) {
          echo '
            <script>
              alert("Berhasil tambah data.");
              window.location.replace("'.$alamat_admin.'bukti_jp");
            </script>
          ';
        } else {
          echo "Proses Gagal<br>Error : ".$tambah_bukti_jp."<br>".mysqli_error($koneksi);
        }
      } else {
        echo '
          <script>
            alert("Gagal upload gambar, usahakan nama file gambar pendek, atau cek koneksi internet anda!");
            window.location.replace("'.$alamat_admin.'tambah_bukti_jp");
          </script>
        ';
      }
    }
  }
?>
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="row gy-4 mb-4">
    <div class="col-md-6">
      <div class="fw-bold fs-4 text-center text-md-start">Bukti JP</div>
    </div>
    <div class="col-md-6">
      <div class="text-center text-md-end">
        <a href="<?php echo $alamat_admin.'bukti_jp'; ?>" class="btn btn-sm btn-primary waves-effect waves-light">
          <span class="tf-icons mdi mdi-chevron-double-left me-1"></span>
          Kembali
        </a>
      </div>
    </div>
  </div>

  <div class="card mb-4">
    <h5 class="card-header">Tambah Data Bukti JP</h5>
    <form method="post" enctype="multipart/form-data" class="card-body">
      <h6>1. Gambar</h6>
      <div class="row g-3">
        <div class="col-12">
          <div class="mb-3">
            <input type="file" name="gambar_bukti_jp" class="form-control" required>
            <div class="form-text">
              Format gambar harus PNG, JPG, JPEG, GIF, atau SVG.
            </div>
          </div>
        </div>
      </div>
      <hr class="my-4 mx-n4">
      <h6>2. Detail</h6>
      <div class="row g-3">
        <div class="col-md-4">
          <div class="form-floating form-floating-outline">
            <input type="text" name="judul_bukti_jp" class="form-control" placeholder="Judul" required>
            <label>Judul</label>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-floating form-floating-outline">
            <input type="text" name="link_bukti_jp" class="form-control" placeholder="Link" required>
            <label>Link</label>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-floating form-floating-outline">
            <input type="date" name="tanggal_bukti_jp" class="form-control" placeholder="Tanggal" required>
            <label>Tanggal</label>
          </div>
        </div>
      </div>
      <hr class="my-4 mx-n4">
      <h6>3. Deskripsi</h6>
      <div class="row g-3">
        <div class="col-12">
          <textarea name="deskripsi_bukti_jp" class="summernote"></textarea>
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