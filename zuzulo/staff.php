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
      <div class="fw-bold fs-4 text-center text-md-start">Staff</div>
    </div>
    <div class="col-md-6">
      <div class="text-center text-md-end">
        <a href="<?php echo $alamat_admin.'tambah_staff'; ?>" class="btn btn-sm btn-primary waves-effect waves-light">
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
          <th scope="col">Nama Pengguna</th>
          <th scope="col">Status</th>
          <th scope="col">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php
          $nomor_staff = 1;
          $staff = mysqli_query($koneksi, "SELECT * FROM staff");
          while ($data_staff = mysqli_fetch_array($staff)) {
            if (mysqli_num_rows($staff) >= 1) {
              $id_staff = $data_staff['id_staff'];
              $nama_staff = $data_staff['nama_staff'];
              $nama_pengguna_staff = $data_staff['nama_pengguna_staff'];
              $kata_sandi_staff = $data_staff['kata_sandi_staff'];
              $pin_staff = $data_staff['pin_staff'];
              $status_staff = $data_staff['status_staff'];
        ?>
        <tr>
          <th scope="row"><?php echo $nomor_staff++; ?></th>
          <td><?php echo $nama_staff; ?></td>
          <td><?php echo $nama_pengguna_staff; ?></td>
          <td><?php echo $status_staff; ?></td>
          <td>
            <a href="<?php echo $alamat_admin.'ubah_staff/'.$id_staff; ?>" class="btn btn-sm btn-primary waves-effect waves-light">
              <span class="tf-icons mdi mdi-pencil me-1"></span>
              Ubah
            </a>
          </td>
        </tr>
        <?php
            } else {
        ?>
        <tr>
          <td class="text-center" colspan="5">Tidak Ada Data</td>
        </tr>
        <?php
            }
          }
        ?>
      </tbody>
    </table>
  </div>
</div>