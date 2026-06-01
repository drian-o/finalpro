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
  if (isset($_GET['id_promosi'])) {
    $id_promosi = $_GET['id_promosi'];
    $promosi = mysqli_query($koneksi, "SELECT * FROM promosi WHERE id_promosi = '$id_promosi'");
    $data_promosi = mysqli_fetch_array($promosi);
    $gambar_promosi = $data_promosi['gambar_promosi'];
    $judul_promosi = $data_promosi['judul_promosi'];
    $kategori_promosi = $data_promosi['kategori_promosi'];
    $deskripsi_promosi = $data_promosi['deskripsi_promosi'];
  } else {
    echo '
      <script>
        alert("Pilih promosi yang ingin diubah!");
        window.location.replace("'.$alamat_admin.'promosi");
      </script>
    ';
  }
  if (isset($_POST['ubah_data'])) {
    $judul_promosi_2 = $_POST['judul_promosi'];
    $kategori_promosi_2 = $_POST['kategori_promosi'];
    $deskripsi_promosi_2 = $_POST['deskripsi_promosi'];
    $random = rand(1000000000, 9999999999);
    $tmp_file = $_FILES['gambar_promosi']['tmp_name'];
    $nama_file = $_FILES['gambar_promosi']['name'];
    if (!empty($nama_file)) {
      $format =  array('png', 'PNG', 'jpg', 'JPG', 'jpeg', 'JPEG', 'gif', 'GIF', 'svg', 'SVG');
      $extensi = pathinfo($nama_file, PATHINFO_EXTENSION);
      if (!in_array($extensi, $format)) {
        echo '
          <script>
            alert("Format gambar salah, format gambar yang diperbolehkan adalah PNG, JPG, JPEG, GIF, dan SVG!");
            window.location.replace("'.$alamat_admin.'ubah_promosi/'.$id_promosi.'");
          </script>
        ';
      } else {
        $file = strtolower(str_replace(" ", "_", $nama_file));
        $file_input = $random.'_'.$file;
        $lokasi_simpan = "../assets/img/".$file_input;
        if (move_uploaded_file($tmp_file, $lokasi_simpan)) {
          $ubah_promosi = mysqli_query($koneksi, "UPDATE promosi SET gambar_promosi = '$file_input', judul_promosi = '$judul_promosi_2', kategori_promosi = '$kategori_promosi_2', deskripsi_promosi = '$deskripsi_promosi_2' WHERE id_promosi = '$id_promosi'");
          if ($ubah_promosi) {
            echo '
              <script>
                alert("Berhasil ubah data.");
                window.location.replace("'.$alamat_admin.'promosi");
              </script>
            ';
          } else {
            echo "Proses Gagal<br>Error : ".$ubah_promosi."<br>".mysqli_error($koneksi);
          }
        } else {
          echo '
            <script>
              alert("Gagal upload gambar, usahakan nama file gambar pendek, atau cek koneksi internet anda!");
              window.location.replace("'.$alamat_admin.'ubah_promosi/'.$id_promosi.'");
            </script>
          ';
        }
      }
    } else {
      $ubah_promosi = mysqli_query($koneksi, "UPDATE promosi SET judul_promosi = '$judul_promosi_2', kategori_promosi = '$kategori_promosi_2', deskripsi_promosi = '$deskripsi_promosi_2' WHERE id_promosi = '$id_promosi'");
      if ($ubah_promosi) {
        echo '
          <script>
            alert("Berhasil ubah data.");
            window.location.replace("'.$alamat_admin.'promosi");
          </script>
        ';
      } else {
        echo "Proses Gagal<br>Error : ".$ubah_promosi."<br>".mysqli_error($koneksi);
      }
    }
  } else if (isset($_POST['hapus_data'])) {
    $hapus_data = mysqli_query($koneksi, "DELETE FROM promosi WHERE id_promosi = '$id_promosi'");
    if ($hapus_data) {
      echo '
        <script>
          alert("Berhasil hapus data.");
          window.location.replace("'.$alamat_admin.'promosi");
        </script>
      ';
    } else {
      echo "Proses Gagal<br>Error : ".$hapus_promosi."<br>".mysqli_error($koneksi);
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
    <h5 class="card-header">Ubah Data Promosi</h5>
    <form method="post" enctype="multipart/form-data" class="card-body">
      <h6>1. Gambar</h6>
      <div class="row g-3">
        <div class="col-12">
          <div class="mb-3">
            <div class="bg-secondary rounded text-center p-3 mb-3">
              <img src="<?php echo '../assets/img/'.$gambar_promosi; ?>" alt="<?php echo $jenis_promosi; ?>" class="img-fluid">
            </div>
            <input type="file" name="gambar_promosi" class="form-control" id="formFile">
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
            <input type="text" name="judul_promosi" class="form-control" value="<?php echo $judul_promosi; ?>" required>
            <label>Judul</label>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-floating form-floating-outline">
            <input type="text" name="kategori_promosi" class="form-control" value="<?php echo $kategori_promosi; ?>" required>
            <label>Kategori</label>
          </div>
        </div>
        <div class="col-12">
          <textarea name="deskripsi_promosi" class="summernote"><?php echo $deskripsi_promosi; ?></textarea>
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