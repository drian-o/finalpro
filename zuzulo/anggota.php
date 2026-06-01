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
      <div class="fw-bold fs-4 text-center text-md-start">Anggota</div>
    </div>
    <div class="col-md-6">
      <div class="text-center text-md-end">
        <div>
        <a href="<?php echo $alamat_admin.'tambah_anggota'; ?>" class="btn btn-sm btn-primary waves-effect waves-light">
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
          <th scope="col" class="text-center">#</th>
          <th scope="col" class="text-center">Nama Pengguna</th>
          <th scope="col" class="text-center">Email</th>
          <th scope="col" class="text-center">Telepon</th>
          <th scope="col" class="text-center">Bank</th>
          <th scope="col" class="text-center">Nama Rekening</th>
          <th scope="col" class="text-center">Nomor Rekening</th>
          <th scope="col" class="text-center">Saldo</th>
          <th scope="col" class="text-center">Status</th>
          <th scope="col" class="text-center">Game</th>
          <th scope="col" class="text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
        <?php
$nomor_anggota = 1;
$anggota = mysqli_query($koneksi, "SELECT * FROM anggota ORDER BY id_anggota DESC");
while ($data_anggota = mysqli_fetch_array($anggota)) {
  if (mysqli_num_rows($anggota) >= 1) {
    $id_anggota = $data_anggota['id_anggota'];
    $nama_pengguna_anggota = $data_anggota['nama_pengguna_anggota'];
    $kata_sandi_anggota = $data_anggota['kata_sandi_anggota'];
    $email_anggota = $data_anggota['email_anggota'];
    $telepon_anggota = $data_anggota['telepon_anggota'];
    $bank_anggota = $data_anggota['bank_anggota'];
    $nama_rekening_anggota = $data_anggota['nama_rekening_anggota'];
    $nomor_rekening_anggota = $data_anggota['nomor_rekening_anggota'];
    $saldo_anggota = $data_anggota['saldo_anggota'];
    $status_anggota = $data_anggota['status_anggota'];
    $status_game = $data_anggota['status_game']; // Menambahkan status_game
  ?>
          <tr>
            <th scope="row" class="text-center"><?php echo $nomor_anggota++; ?></th>
            <td class="text-center"><?php echo $nama_pengguna_anggota; ?></td>
            <td class="text-center"><?php echo $email_anggota; ?></td>
            <td class="text-center"><?php echo $telepon_anggota; ?></td>
            <td class="text-center"><?php echo $bank_anggota; ?></td>
            <td class="text-center"><?php echo $nama_rekening_anggota; ?></td>
            <td class="text-center"><?php echo $nomor_rekening_anggota; ?></td>
            <td class="text-center"><?php echo 'Rp.' . number_format($saldo_anggota, 0, ',', '.'); ?></td>
            <td class="text-center"><?php echo $status_anggota; ?></td>
            <td class="text-center"><?php echo $status_game; ?></td>


            <td class="text-center">
              <a href="<?php echo $alamat_admin.'ubah_anggota/'.$id_anggota; ?>" class="btn btn-sm btn-primary waves-effect waves-light">
                <span class="tf-icons mdi mdi-pencil me-1"></span>
                Ubah
              </a>
            </td>
          </tr>
          <?php
              } else {
          ?>
          <tr>
            <td class="text-center" colspan="10">Tidak Ada Data</td>
          </tr>
          <?php
              }
            }
          ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
