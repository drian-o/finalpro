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
      <div class="fw-bold fs-4 text-center text-md-start">Refferal</div>
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
                <th scope="col" class="text-center">User Referral</th>
                <th scope="col" class="text-center">Keterangan</th>
                <th scope="col" class="text-center">ID User</th>
                <th scope="col" class="text-center">Deposit</th>
               
            </tr>
        </thead>
        <tbody>
          <?php
$nomor_anggota = 1;
$anggota = mysqli_query($koneksi, "SELECT * FROM tb_refferal ORDER BY id DESC");
while ($data_anggota = mysqli_fetch_array($anggota)) {
  if (mysqli_num_rows($anggota) >= 1) {
    $id = $data_anggota['id'];
    $user_refferal = $data_anggota['user_refferal'];
    $keterangan = $data_anggota['keterangan'];
    $id_user = $data_anggota['id_user'];
    $deposit = $data_anggota['Deposit'];
   
    ?>
  <tr>
            <th scope="row" class="text-center"><?php echo $nomor_anggota++; ?></th>
            <td class="text-center"><?php echo $user_refferal ; ?></td>
            <td class="text-center"><?php echo $keterangan ; ?></td>
            <td class="text-center"><?php echo $id_user ; ?></td>
            <td class="text-center"><?php echo $deposit ; ?></td>
            
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