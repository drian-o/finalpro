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
      <div class="fw-bold fs-4 text-center text-md-start">Rekening</div>
    </div>
    <div class="col-md-6">
      <div class="text-center text-md-end">
        <a href="<?php echo $alamat_admin.'tambah_rekening'; ?>" class="btn btn-sm btn-primary waves-effect waves-light">
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
          <th scope="col">Jenis</th>
          <th scope="col">Atas Nama</th>
          <th scope="col">Nomor</th>
          <th scope="col">Status</th>
          <th scope="col">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php
          $nomor_rekening = 1;
          $rekening = mysqli_query($koneksi, "SELECT * FROM bank");
          while ($data_rekening = mysqli_fetch_array($rekening)) {
            if (mysqli_num_rows($rekening) >= 1) {
              $id_rekening = $data_rekening['id_bank'];
              $gambar_rekening = $data_rekening['gambar_bank'];
              $jenis_rekening = $data_rekening['jenis_bank'];
              $atas_nama_rekening = $data_rekening['atas_nama_bank'];
              $nomor_rekening_rekening = $data_rekening['nomor_rekening_bank'];
              $status_rekening = $data_rekening['status_bank'];
        ?>
        <tr>
          <th scope="row"><?php echo $nomor_rekening++; ?></th>
          <td><?php echo $jenis_rekening; ?></td>
          <td><?php echo $atas_nama_rekening; ?></td>
          <td><?php echo $nomor_rekening_rekening; ?></td>
          <td><?php echo $status_rekening; ?></td>
          <td>
            <a href="<?php echo $alamat_admin.'ubah_rekening/'.$id_rekening; ?>" class="btn btn-sm btn-primary waves-effect waves-light">
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