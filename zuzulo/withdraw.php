<div class="container-xxl flex-grow-1 container-p-y">
  <div class="row gy-4 mb-4">
    <div class="col-md-6">
      <div class="fw-bold fs-4 text-center text-md-start">Withdraw</div>
    </div>
    <div class="col-md-6">
      <div class="text-center text-md-end">
        <span><?php echo ucapan().', '.tanggalIndonesia(date('Y-m-d'), true).', '; ?></span>
        <span id="jam_sekarang">Jam </span>
      </div>
    </div>
  </div>
  <div class="card p-3">
    <?php
    // Query untuk mendapatkan data withdraw terurut berdasarkan tanggal withdraw descending
    $withdraw = mysqli_query($koneksi, "SELECT * FROM withdraw ORDER BY tanggal_withdraw DESC");
    ?>
    <div class="table-responsive">
      <table class="table" id="example">
        <thead>
          <tr>
            <th scope="col" class="text-center">#</th>
            <th scope="col" class="text-center">Kode</th>
            <th scope="col" class="text-center">Nama Pengguna</th>
            <th scope="col" class="text-center">Tujuan</th>
            <th scope="col" class="text-center">Jumlah</th>
            <th scope="col" class="text-center">Tanggal</th>
            <th scope="col" class="text-center">Status</th>
            <th scope="col" class="text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $nomor_withdraw = 1;
          if (mysqli_num_rows($withdraw) > 0) {
            while ($data_withdraw = mysqli_fetch_array($withdraw)) {
              $id_withdraw = $data_withdraw['id_withdraw'];
              $id_anggota_withdraw = $data_withdraw['id_anggota_withdraw'];
              $kode_withdraw = $data_withdraw['kode_withdraw'];
              $tujuan_withdraw = $data_withdraw['tujuan_withdraw'];
              $jumlah_withdraw = $data_withdraw['jumlah_withdraw'];
              $tanggal_withdraw = $data_withdraw['tanggal_withdraw'];
              $status_withdraw = $data_withdraw['status_withdraw'];

              // Ambil nama pengguna anggota withdraw
              $anggota_withdraw = mysqli_query($koneksi, "SELECT * FROM anggota WHERE id_anggota = '$id_anggota_withdraw'");
              if (mysqli_num_rows($anggota_withdraw) == 0) {
                $nama_pengguna_anggota_withdraw = "Data anggota tidak ada atau telah dihapus";
              } else {
                $data_anggota_withdraw = mysqli_fetch_array($anggota_withdraw);
                $nama_pengguna_anggota_withdraw = $data_anggota_withdraw['nama_pengguna_anggota'];
              }

              // Tentukan warna berdasarkan status withdraw
              $status_class = '';
              switch ($status_withdraw) {
                case 'diproses':
                  $status_class = 'text-warning';
                  break;
                case 'dibatalkan':
                  $status_class = 'text-danger';
                  break;
                case 'disetujui':
                  $status_class = 'text-success';
                  break;
                default:
                  $status_class = '';
                  break;
              }
              ?>
              <tr>
                <th scope="row" class="text-center"><?php echo $nomor_withdraw++; ?></th>
                <td class="text-center"><?php echo $kode_withdraw; ?></td>
                <td class="text-center"><?php echo $nama_pengguna_anggota_withdraw; ?></td>
                <td class="text-center"><?php echo $tujuan_withdraw; ?></td>
                <td class="text-center"><?php echo 'Rp.' . number_format($jumlah_withdraw, 0, ',', '.'); ?></td>
                <td class="text-center"><?php echo jamTanggalIndonesia($tanggal_withdraw); ?></td>
                <td class="text-center <?php echo $status_class; ?>"><?php echo ucfirst($status_withdraw); ?></td>
                <td class="text-center">
                  <a href="<?php echo $alamat_admin.'ubah_withdraw/'.$id_withdraw; ?>" class="btn btn-sm btn-primary waves-effect waves-light">
                    <span class="tf-icons mdi mdi-pencil me-1"></span>
                    Ubah
                  </a>
                </td>
              </tr>
              <?php
            }
          } else {
            ?>
          
          <?php
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>
</div>