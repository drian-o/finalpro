<?php
  include_once '../koneksi.php';

  if (!isset($_SESSION['kode_admin'])) {
    echo '
      <script>
        alert("Terjadi kesalahan, harap masuk kembali!");
        window.location.replace("'.$alamat_admin.'keluar.php");
      </script>
    ';
    exit();
  }

  // --- LOGIKA MENGAMBIL DATA AWAL DARI DATABASE ---
  $rate_refferal_db = 0;
  $bonus_mingguan_db = 0;
  $error_message = '';
  $success_message = '';

  // Ambil nilai rate referral
  $stmt_get_refferal = $koneksi->prepare("SELECT isi_1_pengaturan FROM pengaturan WHERE nama_pengaturan = 'rate_refferal'");
  if ($stmt_get_refferal) {
    $stmt_get_refferal->execute();
    $result_refferal = $stmt_get_refferal->get_result();
    if($data_refferal = $result_refferal->fetch_assoc()) {
      $rate_refferal_db = floatval($data_refferal['isi_1_pengaturan']);
    }
    $stmt_get_refferal->close();
  }
  $rate_refferal_input = $rate_refferal_db * 100;

  // Ambil nilai bonus mingguan
  $stmt_get_bonus = $koneksi->prepare("SELECT isi_2_pengaturan FROM pengaturan WHERE nama_pengaturan = 'bonus_mingguan'");
  if ($stmt_get_bonus) {
    $stmt_get_bonus->execute();
    $result_bonus = $stmt_get_bonus->get_result();
    if($data_bonus = $result_bonus->fetch_assoc()) {
      $bonus_mingguan_db = floatval($data_bonus['isi_2_pengaturan']);
    }
    $stmt_get_bonus->close();
  }
  $bonus_mingguan_input = $bonus_mingguan_db * 100;


  // --- LOGIKA MENGATASI SUBMIT FORM ---
  if (isset($_POST['simpan_refferal'])) {
    $user_input = $_POST['rate_refferal_baru'];
    if (is_numeric($user_input)) {
        $new_rate_integer = intval($user_input);
        if ($new_rate_integer >= 0) {
            $new_rate_for_db = $new_rate_integer / 100;
            $stmt_update_rate = $koneksi->prepare("UPDATE pengaturan SET isi_1_pengaturan = ? WHERE nama_pengaturan = 'rate_refferal'");
            if ($stmt_update_rate) {
                $stmt_update_rate->bind_param("d", $new_rate_for_db);
                if ($stmt_update_rate->execute()) {
                    $stmt_update_rate->close();
                    $success_message = "Berhasil memperbarui **Rate Referral** menjadi {$new_rate_integer}% (Nilai DB: {$new_rate_for_db}).";
                    $rate_refferal_db = $new_rate_for_db;
                    $rate_refferal_input = $new_rate_integer;
                } else {
                    $error_db = $stmt_update_rate->error;
                    $stmt_update_rate->close();
                    $error_message = "Gagal memperbarui data Rate Referral: " . htmlspecialchars($error_db);
                }
            } else {
                $error_message = "Terjadi kesalahan persiapan query untuk Rate Referral.";
            }
        } else {
            $error_message = "Rate Referral tidak boleh bernilai negatif.";
        }
    } else {
        $error_message = "Input Rate Referral tidak valid. Harap masukkan nilai angka.";
    }
  }

  if (isset($_POST['simpan_bonus_mingguan'])) {
    $user_input = $_POST['bonus_mingguan_baru'];
    if (is_numeric($user_input)) {
        $new_bonus_integer = intval($user_input);
        if ($new_bonus_integer >= 0) {
            $new_bonus_for_db = $new_bonus_integer / 100;
            $stmt_update_bonus = $koneksi->prepare("UPDATE pengaturan SET isi_2_pengaturan = ? WHERE nama_pengaturan = 'bonus_mingguan'");
            if ($stmt_update_bonus) {
                $stmt_update_bonus->bind_param("d", $new_bonus_for_db);
                if ($stmt_update_bonus->execute()) {
                    $stmt_update_bonus->close();
                    $success_message = "Berhasil memperbarui **Bonus Mingguan** menjadi {$new_bonus_integer}% (Nilai DB: {$new_bonus_for_db}).";
                    $bonus_mingguan_db = $new_bonus_for_db;
                    $bonus_mingguan_input = $new_bonus_integer;
                } else {
                    $error_db = $stmt_update_bonus->error;
                    $stmt_update_bonus->close();
                    $error_message = "Gagal memperbarui data Bonus Mingguan: " . htmlspecialchars($error_db);
                }
            } else {
                $error_message = "Terjadi kesalahan persiapan query untuk Bonus Mingguan.";
            }
        } else {
            $error_message = "Bonus Mingguan tidak boleh bernilai negatif.";
        }
    } else {
        $error_message = "Input Bonus Mingguan tidak valid. Harap masukkan nilai angka.";
    }
  }
?>

<div class="container-xxl flex-grow-1 container-p-y">
  <h4 class="py-3 mb-4">
    <span class="text-muted fw-light">Pengaturan /</span> Bonus
  </h4>

  <div class="row">
    <div class="col-md-12">
      <?php if (!empty($success_message)): ?>
        <div class="alert alert-success d-flex align-items-center" role="alert">
          <span class="alert-icon rounded-3 me-3">
            <i class="mdi mdi-check mdi-24px"></i>
          </span>
          <?php echo htmlspecialchars($success_message); ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger d-flex align-items-center" role="alert">
          <span class="alert-icon rounded-3 me-3">
            <i class="mdi mdi-alert-circle-outline mdi-24px"></i>
          </span>
          <?php echo htmlspecialchars($error_message); ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="row">
    <div class="col-md-6">
      <div class="card mb-4">
        <h5 class="card-header">Pengaturan Rate Referral</h5>
        <div class="card-body">
          <div class="alert alert-info" role="alert">
            Nilai Rate Referral saat ini di database: <code><?php echo htmlspecialchars($rate_refferal_db); ?></code> (Setara dengan <code><?php echo htmlspecialchars($rate_refferal_input); ?>%</code>).
          </div>
          <form method="post">
            <div class="row g-3">
              <div class="col-md-12">
                <div class="form-floating form-floating-outline">
                  <input type="number" class="form-control" name="rate_refferal_baru" step="1" min="0" value="<?php echo htmlspecialchars($rate_refferal_input); ?>" required>
                  <label>Rate Referral Baru (%)</label>
                </div>
                <div class="form-text mt-2">
                  Masukkan nilai persentase tanpa simbol %. Contoh: Jika Anda ingin 1%, masukkan <code>1</code>. Jika Anda memasukkan 1,05, sistem akan membulatkannya menjadi <code>1</code>.
                </div>
              </div>
            </div>
            <div class="pt-4 text-end">
              <button type="submit" name="simpan_refferal" class="btn btn-primary waves-effect waves-light">
                <span class="tf-icons mdi mdi-content-save me-1"></span>
                Simpan Perubahan
              </button>
            </div>
          </form>

          <hr class="my-4">

          <div>
            <h6 class="fw-bold">Contoh Perhitungan Live Referral</h6>
            <p class="text-muted">Berikut adalah contoh bagaimana rate referral yang Anda tetapkan akan dihitung.</p>
            <div class="table-responsive">
              <table class="table table-bordered">
                <thead>
                  <tr>
                    <th>Contoh Deposit User A</th>
                    <th>Rate Referral (%)</th>
                    <th>Bonus untuk User B</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>Rp 100.000</td>
                    <td><code><?php echo htmlspecialchars($rate_refferal_input); ?>%</code></td>
                    <td>
                      <?php
                        $deposit1 = 100000;
                        $bonus1 = $deposit1 * $rate_refferal_db;
                        echo "Rp " . number_format($deposit1, 0, ',', '.') . " &times; " . htmlspecialchars($rate_refferal_db) . " = Rp " . number_format($bonus1, 0, ',', '.');
                      ?>
                    </td>
                  </tr>
                  <tr>
                    <td>Rp 500.000</td>
                    <td><code><?php echo htmlspecialchars($rate_refferal_input); ?>%</code></td>
                    <td>
                      <?php
                        $deposit2 = 500000;
                        $bonus2 = $deposit2 * $rate_refferal_db;
                        echo "Rp " . number_format($deposit2, 0, ',', '.') . " &times; " . htmlspecialchars($rate_refferal_db) . " = Rp " . number_format($bonus2, 0, ',', '.');
                      ?>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card mb-4">
        <h5 class="card-header">Pengaturan Bonus Mingguan</h5>
        <div class="card-body">
          <div class="alert alert-info" role="alert">
            Nilai Bonus Mingguan saat ini di database: <code><?php echo htmlspecialchars($bonus_mingguan_db); ?></code> (Setara dengan <code><?php echo htmlspecialchars($bonus_mingguan_input); ?>%</code>).
          </div>
          <form method="post">
            <div class="row g-3">
              <div class="col-md-12">
                <div class="form-floating form-floating-outline">
                  <input type="number" class="form-control" name="bonus_mingguan_baru" step="1" min="0" value="<?php echo htmlspecialchars($bonus_mingguan_input); ?>" required>
                  <label>Bonus Mingguan Baru (%)</label>
                </div>
                <div class="form-text mt-2">
                  Masukkan nilai persentase tanpa simbol %. Contoh: Jika Anda ingin 3%, masukkan <code>3</code>. Jika Anda memasukkan 3,75, sistem akan membulatkannya menjadi <code>3</code>.
                </div>
              </div>
            </div>
            <div class="pt-4 text-end">
              <button type="submit" name="simpan_bonus_mingguan" class="btn btn-primary waves-effect waves-light">
                <span class="tf-icons mdi mdi-content-save me-1"></span>
                Simpan Perubahan
              </button>
            </div>
          </form>

          <hr class="my-4">

          <div>
            <h6 class="fw-bold">Contoh Perhitungan Live Bonus Mingguan</h6>
            <p class="text-muted">Berikut adalah contoh bagaimana bonus mingguan akan dihitung berdasarkan total deposit anggota dalam satu minggu.</p>
            <div class="table-responsive">
              <table class="table table-bordered">
                <thead>
                  <tr>
                    <th>Total Deposit Mingguan</th>
                    <th>Bonus Mingguan (%)</th>
                    <th>Bonus yang Didapat</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>Rp 1.000.000</td>
                    <td><code><?php echo htmlspecialchars($bonus_mingguan_input); ?>%</code></td>
                    <td>
                      <?php
                        $deposit_mingguan1 = 1000000;
                        $bonus_mingguan1 = $deposit_mingguan1 * $bonus_mingguan_db;
                        echo "Rp " . number_format($deposit_mingguan1, 0, ',', '.') . " &times; " . htmlspecialchars($bonus_mingguan_db) . " = Rp " . number_format($bonus_mingguan1, 0, ',', '.');
                      ?>
                    </td>
                  </tr>
                  <tr>
                    <td>Rp 3.500.000</td>
                    <td><code><?php echo htmlspecialchars($bonus_mingguan_input); ?>%</code></td>
                    <td>
                      <?php
                        $deposit_mingguan2 = 3500000;
                        $bonus_mingguan2 = $deposit_mingguan2 * $bonus_mingguan_db;
                        echo "Rp " . number_format($deposit_mingguan2, 0, ',', '.') . " &times; " . htmlspecialchars($bonus_mingguan_db) . " = Rp " . number_format($bonus_mingguan2, 0, ',', '.');
                      ?>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>