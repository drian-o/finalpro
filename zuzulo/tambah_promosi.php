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
    $judul_promosi = $_POST['judul_promosi'];
    $kategori_promosi = $_POST['kategori_promosi'];
    $deskripsi_promosi = $_POST['deskripsi_promosi'];
    $random = rand(1000000000, 9999999999);
    $tmp_file = $_FILES['gambar_promosi']['tmp_name'];
    $nama_file = $_FILES['gambar_promosi']['name'];
    $format =  array('png', 'PNG', 'jpg', 'JPG', 'jpeg', 'JPEG', 'gif', 'GIF', 'svg', 'SVG');
    $extensi = pathinfo($nama_file, PATHINFO_EXTENSION);
    if (!in_array($extensi, $format)) {
      echo '
        <script>
          alert("Format gambar salah, format gambar yang diperbolehkan adalah PNG, JPG, JPEG, GIF, dan SVG!");
          window.location.replace("'.$alamat_admin.'tambah_promosi");
        </script>
      ';
    } else {
      $file = strtolower(str_replace(" ", "_", $nama_file));
      $file_input = $random.'_'.$file;
      $lokasi_simpan = "../assets/img/".$file_input;
      if (move_uploaded_file($tmp_file, $lokasi_simpan)) {
        $tambah_promosi = mysqli_query($koneksi, "INSERT INTO promosi (gambar_promosi, judul_promosi, kategori_promosi, deskripsi_promosi) VALUES ('$file_input', '$judul_promosi', '$kategori_promosi', '$deskripsi_promosi')");
        if ($tambah_promosi) {
          echo '
            <script>
              alert("Berhasil tambah data.");
              window.location.replace("'.$alamat_admin.'promosi");
            </script>
          ';
        } else {
          echo "Proses Gagal<br>Error : ".$tambah_promosi."<br>".mysqli_error($koneksi);
        }
      } else {
        echo '
          <script>
            alert("Gagal upload gambar, usahakan nama file gambar pendek, atau cek koneksi internet anda!");
            window.location.replace("'.$alamat_admin.'tambah_promosi");
          </script>
        ';
      }
    }
  }
?>
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="row gy-4 mb-4">
    <div class="col-md-6">
      <div class="fw-bold fs-4 text-center text-md-start">Promosi</div>
    </div>
    <div class="col-md-6">
      <div class="text-center text-md-end">
        <a href="<?php echo $alamat_admin.'promosi'; ?>" class="btn btn-sm btn-primary waves-effect waves-light">
          <span class="tf-icons mdi mdi-chevron-double-left me-1"></span>
          Kembali
        </a>
      </div>
    </div>
  </div>

  <div class="card mb-4">
    <h5 class="card-header">Tambah Data Promosi</h5>
    <form method="post" enctype="multipart/form-data" class="card-body">
      <h6>1. Gambar</h6>
      <div class="row g-3">
        <div class="col-12">
          <div class="mb-3">
            <input type="file" name="gambar_promosi" class="form-control" required>
            <div class="form-text">
              Format gambar harus PNG, JPG, JPEG, GIF, atau SVG.
            </div>
          </div>
        </div>
      </div>
      <hr class="my-4 mx-n4">
      <h6>2. Detail</h6>
      <div class="row g-3">
        <div class="col-md-6">
          <div class="form-floating form-floating-outline">
            <input type="text" name="judul_promosi" class="form-control" placeholder="Judul" required>
            <label>Judul</label>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-floating form-floating-outline">
            <input type="text" name="kategori_promosi" class="form-control" placeholder="Kategori" required>
            <label>Kategori</label>
          </div>
        </div>
        <div class="col-12">
          <textarea name="deskripsi_promosi" class="summernote"></textarea>
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