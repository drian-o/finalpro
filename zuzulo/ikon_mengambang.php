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
      <div class="fw-bold fs-4 text-center text-md-start">Ikon Mengambang</div>
    </div>
    <div class="col-md-6">
      <div class="text-center text-md-end">
        <a href="<?php echo $alamat_admin.'tambah_ikon_mengambang'; ?>" class="btn btn-sm btn-primary waves-effect waves-light">
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
          <th scope="col">Nama</th>
          <th scope="col">Link</th>
          <th scope="col">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php
          $nomor_ikon_mengambang = 1;
          $ikon_mengambang = mysqli_query($koneksi, "SELECT * FROM floating");
          while ($data_ikon_mengambang = mysqli_fetch_array($ikon_mengambang)) {
            if (mysqli_num_rows($ikon_mengambang) >= 1) {
              $id_ikon_mengambang = $data_ikon_mengambang['id_floating'];
              $nama_ikon_mengambang = $data_ikon_mengambang['nama_floating'];
              $link_ikon_mengambang = $data_ikon_mengambang['link_floating'];
              $gambar_ikon_mengambang = $data_ikon_mengambang['gambar_floating'];
        ?>
        <tr>
          <th scope="row"><?php echo $nomor_ikon_mengambang++; ?></th>
          <td><?php echo $nama_ikon_mengambang; ?></td>
          <td><?php echo $link_ikon_mengambang; ?></td>
          <td>
            <a href="<?php echo $alamat_admin.'ubah_ikon_mengambang/'.$id_ikon_mengambang; ?>" class="btn btn-sm btn-primary waves-effect waves-light">
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