<?php
  // rekap.php
  if (session_status() == PHP_SESSION_NONE) {
      session_start();
  }
  // Asumsikan koneksi.php ada satu level di atas jika file ini ada di dalam subfolder admin
  include_once __DIR__ . '/../koneksi.php'; 

  // Variabel $alamat_admin diharapkan dari koneksi.php
  if (!isset($alamat_admin)) {
      // Fallback jika $alamat_admin tidak terdefinisi (sesuaikan jika perlu)
      $current_dir_url_path = dirname($_SERVER['SCRIPT_NAME']);
      $alamat_admin = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . $current_dir_url_path . '/';
  }

  if (!isset($_SESSION['kode_admin'])) {
    echo '<script>alert("Terjadi kesalahan, harap masuk kembali!"); window.location.replace("'.rtrim($alamat_admin, '/').'/keluar.php");</script>';
    exit();
  }

  /**
   * Mengambil data rekapitulasi total deposit dan withdraw per anggota.
   *
   * @param mysqli $koneksi Objek koneksi database.
   * @return array Array berisi data rekapitulasi anggota.
   */
  function getRekapTransaksiAnggota($koneksi) {
    $rekap_data = [];
    // Ambil semua anggota
    $sql_anggota = "SELECT id_anggota, nama_pengguna_anggota FROM anggota ORDER BY nama_pengguna_anggota ASC";
    $query_anggota = mysqli_query($koneksi, $sql_anggota);

    if (!$query_anggota) {
        error_log("Rekap Transaksi Admin: Gagal query data anggota: " . mysqli_error($koneksi));
        return $rekap_data; // Kembalikan array kosong jika query gagal
    }

    while ($row_anggota = mysqli_fetch_assoc($query_anggota)) {
        $id_anggota = $row_anggota['id_anggota'];
        $nama_pengguna = $row_anggota['nama_pengguna_anggota'];
        $total_deposit_disetujui = 0;
        $total_withdraw_disetujui = 0;

        // Hitung total deposit yang disetujui untuk anggota ini
        // Pastikan kolom jumlah_deposit adalah numerik atau di-CAST dengan benar
        $sql_deposit = "SELECT SUM(CAST(REPLACE(jumlah_deposit, ',', '') AS DECIMAL(15,2))) AS total_depo 
                        FROM deposit 
                        WHERE id_anggota_deposit = ? AND status_deposit = 'disetujui'";
        $stmt_deposit = $koneksi->prepare($sql_deposit);
        if ($stmt_deposit) {
            $stmt_deposit->bind_param("i", $id_anggota);
            $stmt_deposit->execute();
            $result_deposit = $stmt_deposit->get_result();
            if ($data_depo = $result_deposit->fetch_assoc()) {
                $total_deposit_disetujui = (float)($data_depo['total_depo'] ?? 0);
            }
            $stmt_deposit->close();
        } else {
            error_log("Rekap Transaksi Admin: Gagal prepare statement total deposit untuk anggota ID {$id_anggota}: " . $koneksi->error);
        }
            
        // Hitung total withdraw yang disetujui untuk anggota ini
        // Pastikan kolom jumlah_withdraw adalah numerik atau di-CAST dengan benar
        $sql_withdraw = "SELECT SUM(CAST(REPLACE(jumlah_withdraw, ',', '') AS DECIMAL(15,2))) AS total_wd 
                         FROM withdraw 
                         WHERE id_anggota_withdraw = ? AND status_withdraw = 'disetujui'";
        $stmt_withdraw = $koneksi->prepare($sql_withdraw);
        if ($stmt_withdraw) {
            $stmt_withdraw->bind_param("i", $id_anggota);
            $stmt_withdraw->execute();
            $result_withdraw = $stmt_withdraw->get_result();
            if ($data_wd = $result_withdraw->fetch_assoc()) {
                $total_withdraw_disetujui = (float)($data_wd['total_wd'] ?? 0);
            }
            $stmt_withdraw->close();
        } else {
            error_log("Rekap Transaksi Admin: Gagal prepare statement total withdraw untuk anggota ID {$id_anggota}: " . $koneksi->error);
        }
            
        $rekap_data[] = [
            'id_anggota' => $id_anggota,
            'nama_pengguna' => $nama_pengguna,
            'total_deposit_disetujui' => $total_deposit_disetujui,
            'total_withdraw_disetujui' => $total_withdraw_disetujui,
        ];
    }
    mysqli_free_result($query_anggota);
    return $rekap_data;
  }

  $data_rekap_transaksi = getRekapTransaksiAnggota($koneksi);

  // Cek pesan notifikasi dari session (jika ada redirect dari halaman lain)
  $pesan_notifikasi = '';
  $tipe_notifikasi = 'info'; 
  if (isset($_SESSION['pesan_rekap_admin'])) { 
      $pesan_notifikasi = $_SESSION['pesan_rekap_admin']['teks'];
      $tipe_notifikasi = $_SESSION['pesan_rekap_admin']['tipe'];
      unset($_SESSION['pesan_rekap_admin']); 
  }
?>

<div class="container-xxl flex-grow-1 container-p-y">
  <h4 class="py-3 mb-4">
    <span class="text-muted fw-light">Menu Utama /</span> Rekapitulasi Transaksi Anggota
  </h4>

  <?php if (!empty($pesan_notifikasi)): ?>
    <div class="alert alert-<?php echo htmlspecialchars($tipe_notifikasi); ?> alert-dismissible fade show" role="alert">
      <?php echo htmlspecialchars($pesan_notifikasi); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <h5 class="card-header">Rekap Total Deposit & Withdraw (Disetujui)</h5>
        <div class="card-body">
          <?php if (!empty($data_rekap_transaksi)): ?>
          <div class="table-responsive text-nowrap">
            <table id="tabelRekapTransaksi" class="table table-striped table-hover">
              <thead>
                <tr>
                  <th>No.</th>
                  <th>Nama Anggota</th>
                  <th>Total Deposit</th>
                  <th>Total Withdraw</th>
                  </tr>
              </thead>
              <tbody>
                <?php 
                $nomor = 1;
                foreach ($data_rekap_transaksi as $data): ?>
                <tr>
                  <td><?php echo $nomor++; ?></td>
                  <td><?php echo htmlspecialchars($data['nama_pengguna']); ?></td>
                  <td>Rp <?php echo number_format($data['total_deposit_disetujui'], 0, ',', '.'); ?></td>
                  <td>Rp <?php echo number_format($data['total_withdraw_disetujui'], 0, ',', '.'); ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <?php else: ?>
            <p class="text-center">Tidak ada data transaksi anggota untuk ditampilkan.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
    if ($.fn.DataTable) { // Cek apakah fungsi DataTable sudah ada (jQuery DataTables sudah di-load)
        $('#tabelRekapTransaksi').DataTable({
            "language": {
                // "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json" // Untuk Bahasa Indonesia
                // Jika ingin bahasa Inggris default, hapus atau komentari baris 'url'
                 "search": "Cari:",
                 "lengthMenu": "Tampilkan _MENU_ entri",
                 "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                 "infoEmpty": "Menampilkan 0 sampai 0 dari 0 entri",
                 "infoFiltered": "(difilter dari _MAX_ total entri)",
                 "zeroRecords": "Tidak ada data yang cocok ditemukan",
                 "paginate": {
                     "first": "Pertama",
                     "last": "Terakhir",
                     "next": "Berikutnya",
                     "previous": "Sebelumnya"
                 }
            },
            "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
            "order": [[1, "asc"]], // Urutkan berdasarkan Nama Anggota (kolom ke-2) secara ascending
            // Contoh menambahkan kemampuan sorting untuk kolom numerik yang diformat dengan 'Rp'
            // Ini memerlukan plugin DataTables atau parsing kustom jika sorting tidak berjalan baik untuk kolom uang
            // "columnDefs": [
            //    { "type": "num-fmt", "targets": [2, 3] } // Jika Anda menggunakan plugin untuk sorting angka yang diformat
            // ]
        });
    } else {
        console.warn("DataTables library not loaded. Table will not be interactive.");
    }
});
</script>

<?php
if (isset($koneksi) && $koneksi instanceof mysqli && $koneksi->thread_id) {
    $koneksi->close();
}
// Jika Anda menggunakan file footer admin yang di-include, letakkan di sini
// include_once '../footer_admin.php'; 
?>