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
  if (isset($_GET['id_ikon_mengambang'])) {
    $id_ikon_mengambang = $_GET['id_ikon_mengambang'];
    $ikon_mengambang = mysqli_query($koneksi, "SELECT * FROM floating WHERE id_floating = '$id_ikon_mengambang'");
    $data_ikon_mengambang = mysqli_fetch_array($ikon_mengambang);
    $nama_ikon_mengambang = $data_ikon_mengambang['nama_floating'];
    $link_ikon_mengambang = $data_ikon_mengambang['link_floating'];
    $gambar_ikon_mengambang = $data_ikon_mengambang['gambar_floating'];
  } else {
    echo '
      <script>
        alert("Pilih ikon mengambang yang ingin diubah!");
        window.location.replace("'.$alamat_admin.'ikon_mengambang");
      </script>
    ';
  }
  if (isset($_POST['ubah_data'])) {
    $nama_ikon_mengambang_2 = $_POST['nama_floating'];
    $link_ikon_mengambang_2 = $_POST['link_floating'];
    $random = rand(1000000000, 9999999999);
    $tmp_file = $_FILES['gambar_floating']['tmp_name'];
    $nama_file = $_FILES['gambar_floating']['name'];
    if (!empty($nama_file)) {
      $format =  array('png', 'PNG', 'jpg', 'JPG', 'jpeg', 'JPEG', 'gif', 'GIF', 'svg', 'SVG');
      $extensi = pathinfo($nama_file, PATHINFO_EXTENSION);
      if (!in_array($extensi, $format)) {
        echo '
          <script>
            alert("Format gambar salah, format gambar yang diperbolehkan adalah PNG, JPG, JPEG, GIF, dan SVG!");
            window.location.replace("'.$alamat_admin.'ubah_ikon_mengambang/'.$id_ikon_mengambang.'");
          </script>
        ';
      } else {
        $file = strtolower(str_replace(" ", "_", $nama_file));
        $file_input = $random.'_'.$file;
        $lokasi_simpan = "../assets/img/".$file_input;
        if (move_uploaded_file($tmp_file, $lokasi_simpan)) {
          $ubah_ikon_mengambang = mysqli_query($koneksi, "UPDATE floating SET nama_floating = '$nama_ikon_mengambang_2', link_floating = '$link_ikon_mengambang_2', gambar_floating = '$file_input' WHERE id_floating = '$id_ikon_mengambang'");
          if ($ubah_ikon_mengambang) {
            echo '
              <script>
                alert("Berhasil ubah data.");
                window.location.replace("'.$alamat_admin.'ikon_mengambang");
              </script>
            ';
          } else {
            echo "Proses Gagal<br>Error : ".$ubah_ikon_mengambang."<br>".mysqli_error($koneksi);
          }
        } else {
          echo '
            <script>
              alert("Gagal upload gambar, usahakan nama file gambar pendek, atau cek koneksi internet anda!");
              window.location.replace("'.$alamat_admin.'ubah_ikon_mengambang/'.$id_ikon_mengambang.'");
            </script>
          ';
        }
      }
    } else {
      $ubah_ikon_mengambang = mysqli_query($koneksi, "UPDATE floating SET nama_floating = '$nama_ikon_mengambang_2', link_floating = '$link_ikon_mengambang_2' WHERE id_floating = '$id_ikon_mengambang'");
      if ($ubah_ikon_mengambang) {
        echo '
          <script>
            alert("Berhasil ubah data.");
            window.location.replace("'.$alamat_admin.'ikon_mengambang");
          </script>
        ';
      } else {
        echo "Proses Gagal<br>Error : ".$ubah_ikon_mengambang."<br>".mysqli_error($koneksi);
      }
    }
  } else if (isset($_POST['hapus_data'])) {
    $hapus_data = mysqli_query($koneksi, "DELETE FROM floating WHERE id_floating = '$id_ikon_mengambang'");
    if ($hapus_data) {
      echo '
        <script>
          alert("Berhasil hapus data.");
          window.location.replace("'.$alamat_admin.'ikon_mengambang");
        </script>
      ';
    } else {
      echo "Proses Gagal<br>Error : ".$hapus_ikon_mengambang."<br>".mysqli_error($koneksi);
    }
  }
?>
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="row gy-4 mb-4">
    <div class="col-md-6">
      <div class="fw-bold fs-4 text-center text-md-start">Ikon Mengambang</div>
    </div>
    <div class="col-md-6">
      <div class="text-center text-md-end">
        <a href="<?php echo $alamat_admin.'ikon_mengambang'; ?>" class="btn btn-sm btn-primary waves-effect waves-light">
          <span class="tf-icons mdi mdi-chevron-double-left me-1"></span>
          Kembali
        </a>
      </div>
    </div>
  </div>

  <div class="card mb-4">
    <h5 class="card-header">Ubah Data Ikon Mengambang</h5>
    <form method="post" enctype="multipart/form-data" class="card-body">
      <h6>1. Gambar</h6>
      <div class="row g-3">
        <div class="col-12">
          <div class="mb-3">
            <div class="bg-secondary rounded text-center p-3 mb-3">
              <img src="<?php echo '../assets/img/'.$gambar_ikon_mengambang; ?>" alt="<?php echo $jenis_ikon_mengambang; ?>" class="img-fluid">
            </div>
            <input type="file" name="gambar_floating" class="form-control" id="formFile">
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
            <input type="text" name="nama_floating" class="form-control" value="<?php echo $nama_ikon_mengambang; ?>" required>
            <label>Nama</label>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-floating form-floating-outline">
            <input type="text" name="link_floating" class="form-control" value="<?php echo $link_ikon_mengambang; ?>">
            <label>Link</label>
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