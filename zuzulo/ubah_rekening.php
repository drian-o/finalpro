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
  if (isset($_GET['id_rekening'])) {
    $id_rekening = $_GET['id_rekening'];
    $rekening = mysqli_query($koneksi, "SELECT * FROM bank WHERE id_bank = '$id_rekening'");
    $data_rekening = mysqli_fetch_array($rekening);
    $gambar_rekening = $data_rekening['gambar_bank'];
    $jenis_rekening = $data_rekening['jenis_bank'];
    $atas_nama_rekening = $data_rekening['atas_nama_bank'];
    $nomor_rekening_rekening = $data_rekening['nomor_rekening_bank'];
    $status_rekening = $data_rekening['status_bank'];
  } else {
    echo '
      <script>
        alert("Pilih rekening yang ingin diubah!");
        window.location.replace("'.$alamat_admin.'rekening");
      </script>
    ';
  }
  if (isset($_POST['ubah_data'])) {
    $jenis_rekening_2 = $_POST['jenis_bank'];
    $atas_nama_rekening_2 = $_POST['atas_nama_bank'];
    $nomor_rekening_rekening_2 = $_POST['nomor_rekening_bank'];
    $status_rekening_2 = $_POST['status_bank'];
    $random = rand(1000000000, 9999999999);
    $tmp_file = $_FILES['gambar_bank']['tmp_name'];
    $nama_file = $_FILES['gambar_bank']['name'];
    if (!empty($nama_file)) {
      $format =  array('png', 'PNG', 'jpg', 'JPG', 'jpeg', 'JPEG', 'gif', 'GIF', 'svg', 'SVG');
      $extensi = pathinfo($nama_file, PATHINFO_EXTENSION);
      if (!in_array($extensi, $format)) {
        echo '
          <script>
            alert("Format gambar salah, format gambar yang diperbolehkan adalah PNG, JPG, JPEG, GIF, dan SVG!");
            window.location.replace("'.$alamat_admin.'ubah_rekening/'.$id_rekening.'");
          </script>
        ';
      } else {
        $file = strtolower(str_replace(" ", "_", $nama_file));
        $file_input = $random.'_'.$file;
        $lokasi_simpan = "../assets/img/bank_admin/".$file_input;
        if (move_uploaded_file($tmp_file, $lokasi_simpan)) {
          $ubah_rekening = mysqli_query($koneksi, "UPDATE bank SET gambar_bank = '$file_input', jenis_bank = '$jenis_rekening_2', atas_nama_bank = '$atas_nama_rekening_2', nomor_rekening_bank = '$nomor_rekening_rekening_2', status_bank = '$status_rekening_2' WHERE id_bank = '$id_rekening'");
          if ($ubah_rekening) {
            echo '
              <script>
                alert("Berhasil ubah data.");
                window.location.replace("'.$alamat_admin.'rekening");
              </script>
            ';
          } else {
            echo "Proses Gagal<br>Error : ".$ubah_rekening."<br>".mysqli_error($koneksi);
          }
        } else {
          echo '
            <script>
              alert("Gagal upload gambar, usahakan nama file gambar pendek, atau cek koneksi internet anda!");
              window.location.replace("'.$alamat_admin.'ubah_rekening/'.$id_rekening.'");
            </script>
          ';
        }
      }
    } else {
      $ubah_rekening = mysqli_query($koneksi, "UPDATE bank SET jenis_bank = '$jenis_rekening_2', atas_nama_bank = '$atas_nama_rekening_2', nomor_rekening_bank = '$nomor_rekening_rekening_2', status_bank = '$status_rekening_2' WHERE id_bank = '$id_rekening'");
      if ($ubah_rekening) {
        echo '
          <script>
            alert("Berhasil ubah data.");
            window.location.replace("'.$alamat_admin.'rekening");
          </script>
        ';
      } else {
        echo "Proses Gagal<br>Error : ".$ubah_rekening."<br>".mysqli_error($koneksi);
      }
    }
  } else if (isset($_POST['hapus_data'])) {
    $hapus_data = mysqli_query($koneksi, "DELETE FROM bank WHERE id_bank = '$id_rekening'");
    if ($hapus_data) {
      echo '
        <script>
          alert("Berhasil hapus data.");
          window.location.replace("'.$alamat_admin.'rekening");
        </script>
      ';
    } else {
      echo "Proses Gagal<br>Error : ".$hapus_rekening."<br>".mysqli_error($koneksi);
    }
  }
?>
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="row gy-4 mb-4">
    <div class="col-md-6">
      <div class="fw-bold fs-4 text-center text-md-start">Rekening</div>
    </div>
    <div class="col-md-6">
      <div class="text-center text-md-end">
        <a href="<?php echo $alamat_admin.'rekening'; ?>" class="btn btn-sm btn-primary waves-effect waves-light">
          <span class="tf-icons mdi mdi-chevron-double-left me-1"></span>
          Kembali
        </a>
      </div>
    </div>
  </div>

  <div class="card mb-4">
    <h5 class="card-header">Ubah Data Rekening</h5>
    <form method="post" enctype="multipart/form-data" class="card-body">
      <h6>1. Gambar</h6>
      <div class="row g-3">
        <div class="col-12">
          <div class="mb-3">
            <div class="bg-secondary rounded text-center p-3 mb-3">
              <img src="<?php echo '../assets/img/bank_admin/'.$gambar_rekening; ?>" alt="<?php echo $jenis_rekening; ?>" class="img-fluid">
            </div>
            <input type="file" name="gambar_bank" class="form-control" id="formFile">
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
          <div class="form-floating form-floating-outline mb-4">
            <select name="jenis_bank" class="form-select select2" required>
              <option value="<?php echo $jenis_rekening; ?>" selected><?php echo $jenis_rekening; ?></option>
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
              <option value="QRIS">QRIS</option>
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
        <div class="col-md-6">
          <div class="form-floating form-floating-outline">
            <input type="text" name="atas_nama_bank" class="form-control" value="<?php echo $atas_nama_rekening; ?>" required>
            <label>Atas Nama</label>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-floating form-floating-outline">
            <input type="text" name="nomor_rekening_bank" class="form-control" value="<?php echo $nomor_rekening_rekening; ?>" required>
            <label>Nomor Rekening</label>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-floating form-floating-outline mb-4">
            <select name="status_bank" class="form-select select2" required>
              <?php
                if ($status_rekening == "aktif") {
                  echo '
                    <option value="aktif" selected>Aktif</option>
                    <option value="tidak aktif">Tidak Aktif</option>
                  ';
                } else {
                  echo '
                    <option value="tidak aktif" selected>Tidak Aktif</option>
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