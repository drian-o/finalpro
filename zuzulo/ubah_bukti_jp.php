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
  if (isset($_GET['id_bukti_jp'])) {
    $id_bukti_jp = $_GET['id_bukti_jp'];
    $bukti_jp = mysqli_query($koneksi, "SELECT * FROM bukti_jp WHERE id_bukti_jp = '$id_bukti_jp'");
    $data_bukti_jp = mysqli_fetch_array($bukti_jp);
    $judul_bukti_jp = $data_bukti_jp['judul_bukti_jp'];
    $deskripsi_bukti_jp = $data_bukti_jp['deskripsi_bukti_jp'];
    $gambar_bukti_jp = $data_bukti_jp['gambar_bukti_jp'];
    $tanggal_bukti_jp = $data_bukti_jp['tanggal_bukti_jp'];
    $link_bukti_jp = $data_bukti_jp['link_bukti_jp'];
  } else {
    echo '
      <script>
        alert("Pilih bukti_jp yang ingin diubah!");
        window.location.replace("'.$alamat_admin.'bukti_jp");
      </script>
    ';
  }
  if (isset($_POST['ubah_data'])) {
    $judul_bukti_jp_2 = $_POST['judul_bukti_jp'];
    $deskripsi_bukti_jp_2 = $_POST['deskripsi_bukti_jp'];
    $tanggal_bukti_jp_2 = $_POST['tanggal_bukti_jp'];
    $link_bukti_jp_2 = $_POST['link_bukti_jp'];
    $random = rand(1000000000, 9999999999);
    $tmp_file = $_FILES['gambar_bukti_jp']['tmp_name'];
    $nama_file = $_FILES['gambar_bukti_jp']['name'];
    if (!empty($nama_file)) {
      $format =  array('png', 'PNG', 'jpg', 'JPG', 'jpeg', 'JPEG', 'gif', 'GIF', 'svg', 'SVG');
      $extensi = pathinfo($nama_file, PATHINFO_EXTENSION);
      if (!in_array($extensi, $format)) {
        echo '
          <script>
            alert("Format gambar salah, format gambar yang diperbolehkan adalah PNG, JPG, JPEG, GIF, dan SVG!");
            window.location.replace("'.$alamat_admin.'ubah_bukti_jp/'.$id_bukti_jp.'");
          </script>
        ';
      } else {
        $file = strtolower(str_replace(" ", "_", $nama_file));
        $file_input = $random.'_'.$file;
        $lokasi_simpan = "../assets/img/bukti_jp/".$file_input;
        if (move_uploaded_file($tmp_file, $lokasi_simpan)) {
          $ubah_bukti_jp = mysqli_query($koneksi, "UPDATE bukti_jp SET judul_bukti_jp = '$judul_bukti_jp_2', deskripsi_bukti_jp = '$deskripsi_bukti_jp_2', gambar_bukti_jp = '$file_input', tanggal_bukti_jp = '$tanggal_bukti_jp_2', link_bukti_jp = '$link_bukti_jp_2' WHERE id_bukti_jp = '$id_bukti_jp'");
          if ($ubah_bukti_jp) {
            echo '
              <script>
                alert("Berhasil ubah data.");
                window.location.replace("'.$alamat_admin.'bukti_jp");
              </script>
            ';
          } else {
            echo "Proses Gagal<br>Error : ".$ubah_bukti_jp."<br>".mysqli_error($koneksi);
          }
        } else {
          echo '
            <script>
              alert("Gagal upload gambar, usahakan nama file gambar pendek, atau cek koneksi internet anda!");
              window.location.replace("'.$alamat_admin.'ubah_bukti_jp/'.$id_bukti_jp.'");
            </script>
          ';
        }
      }
    } else {
      $ubah_bukti_jp = mysqli_query($koneksi, "UPDATE bukti_jp SET judul_bukti_jp = '$judul_bukti_jp_2', deskripsi_bukti_jp = '$deskripsi_bukti_jp_2', tanggal_bukti_jp = '$tanggal_bukti_jp_2', link_bukti_jp = '$link_bukti_jp_2' WHERE id_bukti_jp = '$id_bukti_jp'");
      if ($ubah_bukti_jp) {
        echo '
          <script>
            alert("Berhasil ubah data.");
            window.location.replace("'.$alamat_admin.'bukti_jp");
          </script>
        ';
      } else {
        echo "Proses Gagal<br>Error : ".$ubah_bukti_jp."<br>".mysqli_error($koneksi);
      }
    }
  } else if (isset($_POST['hapus_data'])) {
    $hapus_data = mysqli_query($koneksi, "DELETE FROM bukti_jp WHERE id_bukti_jp = '$id_bukti_jp'");
    if ($hapus_data) {
      echo '
        <script>
          alert("Berhasil hapus data.");
          window.location.replace("'.$alamat_admin.'bukti_jp");
        </script>
      ';
    } else {
      echo "Proses Gagal<br>Error : ".$hapus_bukti_jp."<br>".mysqli_error($koneksi);
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
    <h5 class="card-header">Ubah Data Bukti JP</h5>
    <form method="post" enctype="multipart/form-data" class="card-body">
      <h6>1. Gambar</h6>
      <div class="row g-3">
        <div class="col-12">
          <div class="mb-3">
            <div class="bg-secondary rounded text-center p-3 mb-3">
              <img src="<?php echo '../assets/img/bukti_jp/'.$gambar_bukti_jp; ?>" alt="<?php echo $jenis_bukti_jp; ?>" class="img-fluid">
            </div>
            <input type="file" name="gambar_bukti_jp" class="form-control" id="formFile">
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
            <input type="text" name="judul_bukti_jp" class="form-control" value="<?php echo $judul_bukti_jp; ?>" required>
            <label>Judul</label>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-floating form-floating-outline">
            <input type="text" name="link_bukti_jp" class="form-control" value="<?php echo $link_bukti_jp; ?>" required>
            <label>Link</label>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-floating form-floating-outline">
            <input type="date" name="tanggal_bukti_jp" class="form-control" value="<?php echo $tanggal_bukti_jp; ?>" required>
            <label>Tanggal</label>
          </div>
        </div>
      </div>
      <hr class="my-4 mx-n4">
      <h6>3. Deskripsi</h6>
      <div class="row g-3">
        <div class="col-12">
          <textarea name="deskripsi_bukti_jp" class="summernote"><?php echo $deskripsi_bukti_jp; ?></textarea>
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