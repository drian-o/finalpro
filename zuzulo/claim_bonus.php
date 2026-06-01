<?php
include_once __DIR__ . '/../koneksi.php';
  // claim_bonus.php
  // Asumsi file ini di-include oleh index.php, jadi tidak perlu session_start() atau koneksi lagi.

  /**
   * Fungsi untuk mendapatkan rentang tanggal Senin - Minggu untuk N minggu ke belakang.
   */
  if (!function_exists('getWeekDates')) { 
      function getWeekDates($weeks_ago = 0) {
          $today = new DateTime("now", new DateTimeZone('Asia/Jakarta'));
          if ($weeks_ago > 0) {
              $today->modify("-{$weeks_ago} weeks");
          }
          
          $day_of_week = $today->format('N'); 
          $start_date = clone $today;
          $start_date->modify('-' . ($day_of_week - 1) . ' days')->setTime(0, 0, 0);
          
          $end_date = clone $start_date;
          $end_date->modify('+6 days')->setTime(23, 59, 59);
          
          return [
              'start_date' => $start_date->format('Y-m-d H:i:s'),
              'end_date' => $end_date->format('Y-m-d H:i:s'),
              'week_number' => (int)$start_date->format('W'), 
              'year' => (int)$start_date->format('Y')
          ];
      }
  }

  /**
   * Mengambil data bonus mingguan untuk semua anggota.
   */
  function getAllAnggotaWeeklyBonusData($koneksi, $jumlah_minggu_ke_belakang = 4) {
    $data_per_minggu_per_anggota = [];
    
    $persentase_bonus = 0.03;
    $stmt_get_bonus_rate = $koneksi->prepare("SELECT isi_2_pengaturan FROM pengaturan WHERE nama_pengaturan = 'bonus_mingguan'");
    if ($stmt_get_bonus_rate) {
        $stmt_get_bonus_rate->execute();
        $result_bonus_rate = $stmt_get_bonus_rate->get_result();
        if ($data_bonus_rate = $result_bonus_rate->fetch_assoc()) {
            $persentase_bonus = floatval($data_bonus_rate['isi_2_pengaturan']);
        }
        $stmt_get_bonus_rate->close();
    }

    $sql_anggota = "SELECT id_anggota, nama_pengguna_anggota, bonus_balance FROM anggota ORDER BY nama_pengguna_anggota ASC";
    $query_anggota = mysqli_query($koneksi, $sql_anggota);

    if (!$query_anggota) {
        error_log("Claim Bonus Admin: Gagal query data anggota: " . mysqli_error($koneksi));
        return $data_per_minggu_per_anggota;
    }

    while ($row_anggota = mysqli_fetch_assoc($query_anggota)) {
        $id_anggota = $row_anggota['id_anggota'];
        $nama_pengguna = $row_anggota['nama_pengguna_anggota'];
        $current_bonus_balance = (float)($row_anggota['bonus_balance'] ?? 0);

        for ($i = 0; $i < $jumlah_minggu_ke_belakang; $i++) {
            $minggu = getWeekDates($i);
            $start_date_minggu = $minggu['start_date'];
            $end_date_minggu = $minggu['end_date'];
            $minggu_ke = $minggu['week_number'];
            $tahun_minggu = $minggu['year'];

            $total_deposit_mingguan = 0;
            $total_withdraw_mingguan = 0;

            $sql_deposit = "SELECT SUM(CAST(REPLACE(jumlah_deposit, ',', '') AS DECIMAL(15,2))) AS total_depo 
                            FROM deposit 
                            WHERE id_anggota_deposit = ? AND status_deposit = 'disetujui'
                            AND tanggal_deposit BETWEEN ? AND ?";
            $stmt_deposit = $koneksi->prepare($sql_deposit);
            if ($stmt_deposit) {
                $stmt_deposit->bind_param("iss", $id_anggota, $start_date_minggu, $end_date_minggu);
                $stmt_deposit->execute();
                $result_deposit = $stmt_deposit->get_result();
                if ($data_depo = $result_deposit->fetch_assoc()) {
                    $total_deposit_mingguan = (float)($data_depo['total_depo'] ?? 0);
                }
                $stmt_deposit->close();
            } else {
                 error_log("Claim Bonus Admin: Gagal prepare statement total deposit mingguan untuk anggota ID {$id_anggota}, Minggu {$minggu_ke}-{$tahun_minggu}: " . $koneksi->error);
            }
            
            $sql_withdraw = "SELECT SUM(CAST(REPLACE(jumlah_withdraw, ',', '') AS DECIMAL(15,2))) AS total_wd 
                             FROM withdraw 
                             WHERE id_anggota_withdraw = ? AND status_withdraw = 'disetujui'
                             AND tanggal_withdraw BETWEEN ? AND ?";
            $stmt_withdraw = $koneksi->prepare($sql_withdraw);
            if ($stmt_withdraw) {
                $stmt_withdraw->bind_param("iss", $id_anggota, $start_date_minggu, $end_date_minggu);
                $stmt_withdraw->execute();
                $result_withdraw = $stmt_withdraw->get_result();
                if ($data_wd = $result_withdraw->fetch_assoc()) {
                    $total_withdraw_mingguan = (float)($data_wd['total_wd'] ?? 0);
                }
                $stmt_withdraw->close();
            } else {
                error_log("Claim Bonus Admin: Gagal prepare statement total withdraw mingguan untuk anggota ID {$id_anggota}, Minggu {$minggu_ke}-{$tahun_minggu}: " . $koneksi->error);
            }

            $potensi_bonus_mingguan = $total_deposit_mingguan * $persentase_bonus;
            $status_penambahan_mingguan = "Tidak Ada Potensi Bonus";

            if ($potensi_bonus_mingguan > 0.009) {
                $sql_check_claim = "SELECT id_claim FROM claim_bonus 
                                    WHERE id_anggota_claim = ? AND periode_tahun = ? AND periode_minggu_ke = ?";
                $stmt_check_claim = $koneksi->prepare($sql_check_claim);
                $sudah_ditambahkan_minggu_ini = false;
                if($stmt_check_claim){
                    $stmt_check_claim->bind_param("iii", $id_anggota, $tahun_minggu, $minggu_ke);
                    $stmt_check_claim->execute();
                    $stmt_check_claim->store_result();
                    if ($stmt_check_claim->num_rows > 0) {
                        $sudah_ditambahkan_minggu_ini = true;
                    }
                    $stmt_check_claim->close();
                } else {
                    error_log("Claim Bonus Admin: Gagal prepare statement cek klaim bonus mingguan untuk ID {$id_anggota}, Minggu {$minggu_ke}-{$tahun_minggu}: " . $koneksi->error);
                }

                if ($sudah_ditambahkan_minggu_ini) {
                    $status_penambahan_mingguan = "Sudah Ditambahkan";
                } else {
                    $status_penambahan_mingguan = "Bisa Ditambahkan";
                }
            }
            
            if ($total_deposit_mingguan > 0 || $potensi_bonus_mingguan > 0.009 || $status_penambahan_mingguan === "Sudah Ditambahkan") {
                 $data_per_minggu_per_anggota[] = [
                    'id_anggota' => $id_anggota,
                    'nama_pengguna' => $nama_pengguna,
                    'minggu_ke' => $minggu_ke,
                    'tahun' => $tahun_minggu,
                    'rentang_tanggal' => date('d M', strtotime($start_date_minggu)) . " - " . date('d M Y', strtotime($end_date_minggu)),
                    'total_deposit_mingguan' => $total_deposit_mingguan,
                    'total_withdraw_mingguan' => $total_withdraw_mingguan,
                    'potensi_bonus_mingguan' => $potensi_bonus_mingguan,
                    'status_penambahan_mingguan' => $status_penambahan_mingguan, 
                    'bonus_balance_saat_ini' => $current_bonus_balance 
                ];
            }
        }
    }
    mysqli_free_result($query_anggota);
    return $data_per_minggu_per_anggota;
  }

  $jumlah_minggu_tampil = 4; 
  $data_bonus_mingguan_anggota = getAllAnggotaWeeklyBonusData($koneksi, $jumlah_minggu_tampil);

  $persentase_bonus_display = 3; 
  $stmt_get_bonus_rate_display = $koneksi->prepare("SELECT isi_2_pengaturan FROM pengaturan WHERE nama_pengaturan = 'bonus_mingguan'");
  if ($stmt_get_bonus_rate_display) {
      $stmt_get_bonus_rate_display->execute();
      $result_bonus_rate_display = $stmt_get_bonus_rate_display->get_result();
      if ($data_bonus_rate_display = $result_bonus_rate_display->fetch_assoc()) {
          $persentase_bonus_display = floatval($data_bonus_rate_display['isi_2_pengaturan']) * 100;
      }
      $stmt_get_bonus_rate_display->close();
  }

  $pesan_notifikasi = '';
  $tipe_notifikasi = 'info';
  if (isset($_SESSION['pesan_tambah_bonus_semua'])) { 
      $pesan_notifikasi = $_SESSION['pesan_tambah_bonus_semua']['teks'];
      $tipe_notifikasi = $_SESSION['pesan_tambah_bonus_semua']['tipe'];
      unset($_SESSION['pesan_tambah_bonus_semua']); 
  }
?>

<div class="container-xxl flex-grow-1 container-p-y">
  <h4 class="py-3 mb-4">
    <span class="text-muted fw-light">Menu Utama /</span> Manajemen Bonus Mingguan Anggota
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
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
            <h5 class="mb-2 mb-md-0">Data Bonus Mingguan (<?php echo $jumlah_minggu_tampil; ?> Minggu Terakhir)</h5>
            <?php
            $url_tambah_semua_bonus_mingguan = rtrim($alamat_admin, '/') . '/proses_tambah_semua_bonus_mingguan.php?target=alluser&confirm=true'; 
            ?>
            <a href="<?php echo htmlspecialchars($url_tambah_semua_bonus_mingguan); ?>" 
               class="btn btn-success btn-sm mt-2 mt-md-0" 
               onclick="return confirm('Anda yakin ingin menambahkan SEMUA potensi bonus mingguan yang BELUM ditambahkan ke saldo bonus masing-masing anggota untuk periode yang ditampilkan? Proses ini akan memakan waktu.');">
                <i class="mdi mdi-check-all me-1"></i> Tambah Semua Bonus (Mingguan)
            </a>
        </div>
        <div class="card-body">
          <?php if (!empty($data_bonus_mingguan_anggota)): ?>
          <div class="table-responsive text-nowrap">
            <table id="tabelBonusMingguan" class="table table-striped table-hover">
              <thead>
                <tr>
                  <th>No.</th>
                  <th>Minggu Ke- (Tahun)</th>
                  <th>Nama Anggota</th>
                  <th>Total Deposit Mingguan</th>
                  <th>Potensi Bonus (<?php echo htmlspecialchars($persentase_bonus_display); ?>%)</th>
                  <th>Status Penambahan Minggu Ini</th>
                  <th>Bonus Balance Anggota (Global)</th>
                  </tr>
              </thead>
              <tbody>
                <?php 
                $nomor = 1;
                foreach ($data_bonus_mingguan_anggota as $data): ?>
                <tr>
                  <td><?php echo $nomor++; ?></td>
                  <td><?php echo htmlspecialchars($data['minggu_ke'] . " (" . $data['tahun'] . ")"); ?><br><small><?php echo $data['rentang_tanggal']; ?></small></td>
                  <td><?php echo htmlspecialchars($data['nama_pengguna']); ?></td>
                  <td>Rp <?php echo number_format($data['total_deposit_mingguan'], 0, ',', '.'); ?></td>
                  <td>Rp <?php echo number_format($data['potensi_bonus_mingguan'], 0, ',', '.'); ?></td>
                  <td>
                    <?php 
                        if ($data['status_penambahan_mingguan'] === "Bisa Ditambahkan") {
                            echo '<span class="badge bg-label-success">Bisa Ditambahkan</span>';
                        } elseif ($data['status_penambahan_mingguan'] === "Sudah Ditambahkan") {
                            echo '<span class="badge bg-label-info">Sudah Ditambahkan</span>';
                        } else { 
                            echo '<span class="badge bg-label-secondary">Tidak Ada Potensi</span>';
                        }
                    ?>
                  </td>
                  <td>Rp <?php echo number_format($data['bonus_balance_saat_ini'], 0, ',', '.'); ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <?php else: ?>
            <p class="text-center">Tidak ada data bonus mingguan untuk ditampilkan.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
    if ($.fn.DataTable) { 
        $('#tabelBonusMingguan').DataTable({
            "language": { "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json" },
            "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
            "order": [[1, "desc"], [2, "asc"]] 
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
?>