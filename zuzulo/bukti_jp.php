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
      <div class="fw-bold fs-4 text-center text-md-start">Bukti JP</div>
    </div>
    <div class="col-md-6">
      <div class="text-center text-md-end">
        <a href="<?php echo $alamat_admin.'tambah_bukti_jp'; ?>" class="btn btn-sm btn-primary waves-effect waves-light">
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
          <th scope="col">Deskripsi</th>
          <th scope="col">Tanggal</th>
          <th scope="col">Link</th>
          <th scope="col">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php
          $nomor_bukti_jp = 1;
          $bukti_jp = mysqli_query($koneksi, "SELECT * FROM bukti_jp");
          while ($data_bukti_jp = mysqli_fetch_array($bukti_jp)) {
            if (mysqli_num_rows($bukti_jp) >= 1) {
              $id_bukti_jp = $data_bukti_jp['id_bukti_jp'];
              $id_bukti_jp = $data_bukti_jp['id_bukti_jp'];
              $judul_bukti_jp = $data_bukti_jp['judul_bukti_jp'];
              $deskripsi_bukti_jp = $data_bukti_jp['deskripsi_bukti_jp'];
              $gambar_bukti_jp = $data_bukti_jp['gambar_bukti_jp'];
              $tanggal_bukti_jp = $data_bukti_jp['tanggal_bukti_jp'];
              $link_bukti_jp = $data_bukti_jp['link_bukti_jp'];
        ?>
        <tr>
          <th scope="row"><?php echo $nomor_bukti_jp++; ?></th>
          <td><?php echo $judul_bukti_jp; ?></td>
          <td><?php echo $deskripsi_bukti_jp; ?></td>
          <td><?php echo tanggalIndonesia($tanggal_bukti_jp, true); ?></td>
          <td><?php echo $link_bukti_jp; ?></td>
          <td>
            <a href="<?php echo $alamat_admin.'ubah_bukti_jp/'.$id_bukti_jp; ?>" class="btn btn-sm btn-primary waves-effect waves-light">
              <span class="tf-icons mdi mdi-pencil me-1"></span>
              Ubah
            </a>
          </td>
        </tr>
        <?php
            } else {
        ?>
        <tr>
          <td class="text-center" colspan="6">Tidak Ada Data</td>
        </tr>
        <?php
            }
          }
        ?>
      </tbody>
    </table>
  </div>
</div>