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
?>
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="row gy-4 mb-4">
    <div class="col-md-6">
      <div class="fw-bold fs-4 text-center text-md-start">Bonus</div>
    </div>
    <div class="col-md-6">
      <div class="text-center text-md-end">
        <a href="<?php echo $alamat_admin.'tambah_bonus'; ?>" class="btn btn-sm btn-primary waves-effect waves-light">
          <span class="tf-icons mdi mdi-plus me-1"></span>
          Tambah Data
        </a>
      </div>
    </div>
  </div>
  <div class="card table-responsive p-3">
    <table class="table" id="example">
      <thead>
        <tr>
          <th scope="col">#</th>
          <th scope="col">Judul</th>
          <th scope="col">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php
          $nomor_bonus = 1;
          $bonus = mysqli_query($koneksi, "SELECT * FROM bonus");
          while ($data_bonus = mysqli_fetch_array($bonus)) {
            if (mysqli_num_rows($bonus) >= 1) {
              $id_bonus = $data_bonus['id_bonus'];
              $judul_bonus = $data_bonus['judul_bonus'];
        ?>
        <tr>
          <th scope="row"><?php echo $nomor_bonus++; ?></th>
          <td><?php echo $judul_bonus; ?></td>
          <td>
            <a href="<?php echo $alamat_admin.'ubah_bonus/'.$id_bonus; ?>" class="btn btn-sm btn-primary waves-effect waves-light">
              <span class="tf-icons mdi mdi-pencil me-1"></span>
              Ubah
            </a>
          </td>
        </tr>
        <?php
            } else {
        ?>
        <tr>
          <td class="text-center" colspan="3">Tidak Ada Data</td>
        </tr>
        <?php
            }
          }
        ?>
      </tbody>
    </table>
  </div>
</div>