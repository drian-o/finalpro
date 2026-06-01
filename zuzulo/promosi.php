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
      <div class="fw-bold fs-4 text-center text-md-start">Promosi</div>
    </div>
    <div class="col-md-6">
      <div class="text-center text-md-end">
        <a href="<?php echo $alamat_admin.'tambah_promosi'; ?>" class="btn btn-sm btn-primary waves-effect waves-light">
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
          <th scope="col">Kategori</th>
          <th scope="col">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php
          $nomor_promosi = 1;
          $promosi = mysqli_query($koneksi, "SELECT * FROM promosi");
          while ($data_promosi = mysqli_fetch_array($promosi)) {
            if (mysqli_num_rows($promosi) >= 1) {
              $id_promosi = $data_promosi['id_promosi'];
              $gambar_promosi = $data_promosi['gambar_promosi'];
              $judul_promosi = $data_promosi['judul_promosi'];
              $kategori_promosi = $data_promosi['kategori_promosi'];
              $deskripsi_promosi = $data_promosi['deskripsi_promosi'];
        ?>
        <tr>
          <th scope="row"><?php echo $nomor_promosi++; ?></th>
          <td><?php echo $judul_promosi; ?></td>
          <td><?php echo $kategori_promosi; ?></td>
          <td>
            <a href="<?php echo $alamat_admin.'ubah_promosi/'.$id_promosi; ?>" class="btn btn-sm btn-primary waves-effect waves-light">
              <span class="tf-icons mdi mdi-pencil me-1"></span>
              Ubah
            </a>
          </td>
        </tr>
        <?php
            } else {
        ?>
        <tr>
          <td class="text-center" colspan="4">Tidak Ada Data</td>
        </tr>
        <?php
            }
          }
        ?>
      </tbody>
    </table>
  </div>
</div>