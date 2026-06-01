<?php
/* Pastikan session_start() sudah dipanggil di awal */
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once '../koneksi.php';
include_once '../classes/class.exa.php'; // Menggunakan kelas GameXaAPI dari class.exa.php

if (!isset($_SESSION['kode_staff'])) {
    echo '
      <script>
        alert("Terjadi kesalahan, harap masuk kembali!");
        window.location.replace("'.$alamat_staff.'keluar.php");
      </script>
    ';
    exit();
}

$logFileDir = __DIR__ . '/../logs/';
$logFilePath = $logFileDir . 'debug_staff_deposit_approval.log';

if (!is_dir($logFileDir)) {
    @mkdir($logFileDir, 0775, true);
}

$id_deposit = null;
$data_deposit = null;
$id_anggota_deposit = null;
$id_sigma_anggota = null; // Menambahkan variabel untuk id_sigma
$saldo_anggota_sebelum_proses = 0;
$nama_anggota_pengguna_deposit = '';
$jumlah_deposit = 0;
$status_deposit_sebelumnya = '';
$reference_id = ''; // Menambahkan variabel untuk reference_id
$referer_username = null; // Menambahkan variabel untuk username referer

// Instansiasi GameXaAPI
$exaAPI = new GameXaAPI();

if (isset($_GET['id_deposit'])) {
    $id_deposit = $_GET['id_deposit'];
    $stmt_get_deposit = $koneksi->prepare("SELECT * FROM deposit WHERE id_deposit = ?");
    $stmt_get_deposit->bind_param("s", $id_deposit);
    $stmt_get_deposit->execute();
    $result_deposit = $stmt_get_deposit->get_result();
    $data_deposit = $result_deposit->fetch_assoc();
    $stmt_get_deposit->close();

    if ($data_deposit) {
        $id_anggota_deposit = $data_deposit['id_anggota_deposit'];
        $nama_anggota_pengguna_deposit = $data_deposit['nama_pengguna_anggota_deposit'];
        $jumlah_deposit = floatval($data_deposit['jumlah_deposit']);
        $status_deposit_sebelumnya = $data_deposit['status_deposit'];
        $reference_id = $data_deposit['reference_id'] ?? ''; // Ambil reference_id jika ada

        // Ambil id_sigma dan refferal dari tabel anggota
        $stmt_get_anggota = $koneksi->prepare("SELECT saldo_anggota, id_sigma, refferal FROM anggota WHERE id_anggota = ?");
        $stmt_get_anggota->bind_param("s", $id_anggota_deposit);
        $stmt_get_anggota->execute();
        $result_anggota = $stmt_get_anggota->get_result();
        $data_anggota_db = $result_anggota->fetch_assoc(); // Menggunakan nama variabel berbeda agar tidak bentrok
        $stmt_get_anggota->close();

        if ($data_anggota_db) {
            $saldo_anggota_sebelum_proses = floatval($data_anggota_db['saldo_anggota']);
            $id_sigma_anggota = $data_anggota_db['id_sigma']; // Simpan id_sigma
            $referer_username = $data_anggota_db['refferal']; // Simpan username referer

            if (empty($id_sigma_anggota)) {
                echo '
                    <script>
                        alert("ID Sigma (Player ID) anggota tidak ditemukan untuk deposit ini! Harap pastikan anggota ini memiliki ID Sigma yang valid.");
                        window.location.replace("'.$alamat_staff.'deposit");
                    </script>
                ';
                exit();
            }
        } else {
             echo '
                <script>
                    alert("Data anggota tidak ditemukan untuk deposit ini!");
                    window.location.replace("'.$alamat_staff.'deposit");
                </script>
            ';
            exit();
        }
    } else {
        echo '
            <script>
                alert("Data deposit tidak ditemukan!");
                window.location.replace("'.$alamat_staff.'deposit");
            </script>
        ';
        exit();
    }
} else {
    echo '
      <script>
        alert("Pilih deposit yang ingin diubah!");
        window.location.replace("'.$alamat_staff.'deposit");
      </script>
    ';
    exit();
}

if (isset($_POST['ubah_data'])) {
    $status_deposit_baru = $_POST['status_deposit'];
    $debug_log_entry = "";
    $staff_code = $_SESSION['kode_staff'] ?? 'UNKNOWN_staff';

    if ($status_deposit_baru != $status_deposit_sebelumnya) {
        $debug_log_entry .= "[" . date("Y-m-d H:i:s") . "] ==== staff DEPOSIT APPROVAL DEBUG ====\n";
        $debug_log_entry .= "staff: " . $staff_code . "\n";
        $debug_log_entry .= "Deposit ID: " . $id_deposit . "\n";
        $debug_log_entry .= "Player ID (id_sigma): " . $id_sigma_anggota . "\n";
        $debug_log_entry .= "Target User: " . $nama_anggota_pengguna_deposit . "\n";
        $debug_log_entry .= "Jumlah Deposit: " . $jumlah_deposit . "\n";
        $debug_log_entry .= "Status Lama: " . $status_deposit_sebelumnya . ", Status Baru: " . $status_deposit_baru . "\n";

        if ($status_deposit_baru == "disetujui" && $status_deposit_sebelumnya != "disetujui") {
            // Generate unique reference ID if not already set
            if (empty($reference_id)) {
                $reference_id = 'dep_' . uniqid(rand(), true); // Lebih unik
                $debug_log_entry .= "Generated new reference_id: " . $reference_id . "\n";
            } else {
                $debug_log_entry .= "Using existing reference_id: " . $reference_id . "\n";
            }

            $request_params_for_log = [
                'playerId' => $id_sigma_anggota,
                'amount' => $jumlah_deposit,
                'referenceId' => $reference_id
            ];
            $debug_log_entry .= "Request Params (depositToPlayer): " . json_encode($request_params_for_log) . "\n";

            $koneksi->begin_transaction(); // Mulai transaksi database
            try {
                // Panggil fungsi depositToPlayer dari GameXaAPI
                $api_transaksi_response = $exaAPI->depositToPlayer($id_sigma_anggota, $jumlah_deposit, $reference_id);
                
                if ($api_transaksi_response !== null) {
                    $debug_log_entry .= "API Response (depositToPlayer): " . json_encode($api_transaksi_response) . "\n";
                } else {
                    $debug_log_entry .= "API Response (depositToPlayer): NULL (kemungkinan gagal terhubung atau respons tidak valid).\n";
                    throw new Exception("Gagal menghubungi API deposit atau API mengembalikan respons null.");
                }

                // Periksa respons dari depositToPlayer
                if (isset($api_transaksi_response['success']) && $api_transaksi_response['success'] === true) {
                    $debug_log_entry .= "Deposit API sukses. Memanggil getPlayerBalance untuk player ID: " . $id_sigma_anggota . "\n";
                    
                    // Panggil getPlayerBalance untuk mendapatkan saldo terbaru dari API
                    $getBalance_response = $exaAPI->getPlayerBalance($id_sigma_anggota);

                    if ($getBalance_response !== null) {
                        $debug_log_entry .= "API Response (getPlayerBalance): " . json_encode($getBalance_response) . "\n";
                    } else {
                        $debug_log_entry .= "API Response (getPlayerBalance): NULL.\n";
                        throw new Exception("Gagal menghubungi API GetBalance atau API mengembalikan respons null.");
                    }

                    if (isset($getBalance_response['success']) && $getBalance_response['success'] === true) {
                        if (isset($getBalance_response['data']['balance'])) {
                            $saldo_final_untuk_db = floatval($getBalance_response['data']['balance']);
                            $debug_log_entry .= "Saldo final dari getPlayerBalance: " . $saldo_final_untuk_db . "\n";
                        } else {
                            throw new Exception("getPlayerBalance API sukses, tapi 'data.balance' tidak ditemukan. Respon: " . json_encode($getBalance_response));
                        }
                    } else {
                        $errMsgGetBalance = "Gagal mengambil saldo definitif dari getPlayerBalance API setelah transaksi deposit sukses.";
                        if($getBalance_response && isset($getBalance_response['message'])) {
                            $errMsgGetBalance .= " Pesan GetBalance API: " . htmlspecialchars($getBalance_response['message']);
                        }
                        throw new Exception($errMsgGetBalance);
                    }

                    // Lanjutkan jika saldo final berhasil didapatkan
                    $stmt_update_saldo = $koneksi->prepare("UPDATE anggota SET saldo_anggota = ? WHERE id_anggota = ?");
                    $stmt_update_saldo->bind_param("ds", $saldo_final_untuk_db, $id_anggota_deposit);
                    if (!$stmt_update_saldo->execute()) {
                        throw new Exception("Gagal update saldo anggota di DB. Error: ".$stmt_update_saldo->error);
                    }
                    $stmt_update_saldo->close();
                    
                    // Update status deposit dan reference_id di tabel deposit
                    $stmt_update_deposit = $koneksi->prepare("UPDATE deposit SET status_deposit = ?, reference_id = ? WHERE id_deposit = ?");
                    if (!$stmt_update_deposit) throw new Exception("DB Error (prepare update deposit): " . $koneksi->error); // Check prepare
                    $stmt_update_deposit->bind_param("sss", $status_deposit_baru, $reference_id, $id_deposit);
                    if (!$stmt_update_deposit->execute()) {
                        throw new Exception("Gagal update status deposit di DB. Error: ".$stmt_update_deposit->error);
                    }
                    $affected_rows_deposit = $stmt_update_deposit->affected_rows;
                    $stmt_update_deposit->close();
                    
                    if ($affected_rows_deposit > 0) {
                        $debug_log_entry .= "DB Update: Sukses update saldo anggota, reference_id, dan status deposit.\n";

                        // --- LOGIKA REFERRAL BONUS ---
                        if (!empty($referer_username)) {
                            $debug_log_entry .= "Referral found for {$nama_anggota_pengguna_deposit}: {$referer_username}. Calculating bonus.\n";
                            
                            // --- Ambil nilai rate_refferal dari tabel pengaturan ---
                            $rate_refferal_bonus = 0.01; // Default fallback
                            $stmt_get_rate = $koneksi->prepare("SELECT rate_refferal FROM pengaturan WHERE nama_pengaturan = 'rate_refferal'");
                            if ($stmt_get_rate) {
                                $stmt_get_rate->execute();
                                $result_rate = $stmt_get_rate->get_result();
                                if($data_rate = $result_rate->fetch_assoc()) {
                                    $rate_refferal_bonus_from_db = floatval($data_rate['rate_refferal']);
                                    if ($rate_refferal_bonus_from_db > 0) {
                                        $rate_refferal_bonus = $rate_refferal_bonus_from_db;
                                    }
                                }
                                $stmt_get_rate->close();
                            }
                            $debug_log_entry .= "Referral rate from DB/default: {$rate_refferal_bonus}.\n";
                            // --------------------------------------------------------

                            $bonus_referral_amount = $jumlah_deposit * $rate_refferal_bonus;

                            if ($bonus_referral_amount > 0) {
                                $existing_referral_id = null;
                                $existing_bonus_amount = 0.00;

                                $stmt_check_referral = $koneksi->prepare("SELECT id, bonus FROM tb_refferal WHERE user_refferal = ? AND id_user = ?");
                                if ($stmt_check_referral) {
                                    $stmt_check_referral->bind_param("ss", $referer_username, $nama_anggota_pengguna_deposit);
                                    $stmt_check_referral->execute();
                                    $result_check_referral = $stmt_check_referral->get_result();
                                    if ($data_referral_exist = $result_check_referral->fetch_assoc()) {
                                        $existing_referral_id = $data_referral_exist['id'];
                                        $existing_bonus_amount = floatval($data_referral_exist['bonus']);
                                    }
                                    $stmt_check_referral->close();
                                } else {
                                    $debug_log_entry .= "DB Error (prepare check tb_refferal): " . $koneksi->error . "\n";
                                    throw new Exception("DB Error (prepare check tb_refferal): " . $koneksi->error);
                                }

                                $new_total_bonus = $existing_bonus_amount + $bonus_referral_amount;
                                $keterangan_referral_updated = "Bonus dari deposit " . $reference_id . " (Rp " . number_format($jumlah_deposit, 0, ',', '.') . ")";

                                if ($existing_referral_id) {
                                    $stmt_update_referral = $koneksi->prepare("UPDATE tb_refferal SET bonus = ?, keterangan = ?, tanggal = NOW() WHERE id = ?");
                                    if (!$stmt_update_referral) {
                                        $debug_log_entry .= "DB Error (prepare update tb_refferal): " . $koneksi->error . "\n";
                                        throw new Exception("DB Error (prepare update tb_refferal): " . $koneksi->error);
                                    }
                                    $stmt_update_referral->bind_param("dsi", $new_total_bonus, $keterangan_referral_updated, $existing_referral_id);
                                    if (!$stmt_update_referral->execute()) {
                                        $debug_log_entry .= "DB Error (execute update tb_refferal): " . $stmt_update_referral->error . "\n";
                                        throw new Exception("DB Error (execute update tb_refferal): " . $stmt_update_referral->error);
                                    }
                                    $stmt_update_referral->close();
                                    $debug_log_entry .= "Referral Bonus: Updated bonus for referer '{$referer_username}' from '{$nama_anggota_pengguna_deposit}'. New total bonus: {$new_total_bonus}. Keterangan: '{$keterangan_referral_updated}'.\n";
                                } else {
                                    $stmt_insert_referral = $koneksi->prepare("INSERT INTO tb_refferal (user_refferal, keterangan, bonus, id_user, tanggal) VALUES (?, ?, ?, ?, NOW())");
                                    if (!$stmt_insert_referral) {
                                        $debug_log_entry .= "DB Error (prepare insert tb_refferal): " . $koneksi->error . "\n";
                                        throw new Exception("DB Error (prepare insert tb_refferal): " . $koneksi->error);
                                    }
                                    $stmt_insert_referral->bind_param("ssds", $referer_username, $keterangan_referral_updated, $bonus_referral_amount, $nama_anggota_pengguna_deposit);
                                    if (!$stmt_insert_referral->execute()) {
                                        $debug_log_entry .= "DB Error (execute insert tb_refferal): " . $stmt_insert_referral->error . "\n";
                                        throw new Exception("DB Error (execute insert tb_refferal): " . $stmt_insert_referral->error);
                                    }
                                    $stmt_insert_referral->close();
                                    $debug_log_entry .= "Referral Bonus: Inserted new entry for referer '{$referer_username}' from '{$nama_anggota_pengguna_deposit}'. Initial bonus: {$bonus_referral_amount}. Keterangan: '{$keterangan_referral_updated}'.\n";
                                }
                            } else {
                                $debug_log_entry .= "Referral Bonus: Bonus amount is zero, skipping insert/update for referer '{$referer_username}'. Amount paid: {$jumlah_deposit}.\n";
                            }
                        } else {
                            $debug_log_entry .= "No referer found for user '{$nama_anggota_pengguna_deposit}'. Skipping referral bonus.\n";
                        }
                        // --- AKHIR LOGIKA REFERRAL BONUS ---

                        $koneksi->commit(); // Commit transaksi jika semua berhasil, termasuk referral
                        $debug_log_entry .= "DB Update: Transaksi COMMIT sukses.\n";
                        file_put_contents($logFilePath, $debug_log_entry . "==== DEBUG END ====\n\n", FILE_APPEND | LOCK_EX);
                        echo '<script>alert("Berhasil setujui deposit, perbarui saldo, dan ubah status."); window.location.replace("'.$alamat_staff.'deposit");</script>';

                    } else { // Jika affected_rows_deposit <= 0
                        $koneksi->rollback(); // Rollback jika tidak ada baris deposit yang diupdate (karena sudah disetujui sebelumnya, dll.)
                        $debug_log_entry .= "DB Warning: Tidak ada baris deposit diupdate untuk Deposit ID {$id_deposit}. Mungkin sudah diproses. DB di-ROLLBACK.\n";
                        file_put_contents($logFilePath, $debug_log_entry . "==== DEBUG END ====\n\n", FILE_APPEND | LOCK_EX);
                        echo '<script>alert("Deposit mungkin sudah diproses atau tidak memenuhi kriteria update."); window.location.replace("'.$alamat_staff.'ubah_deposit/'.$id_deposit.'");</script>';
                    }

                } else { // Jika API deposit GameXa tidak sukses
                    $pesan_error_api = "Proses transaksi deposit via API gagal.";
                    if ($api_transaksi_response && isset($api_transaksi_response['message'])) {
                        $pesan_error_api .= " Pesan: " . htmlspecialchars($api_transaksi_response['message']);
                    }
                    throw new Exception($pesan_error_api);
                }
            } catch (Exception $e) {
                if (isset($koneksi) && $koneksi->ping()) { $koneksi->rollback(); }
                $debug_log_entry .= "Transaction Rollback: " . $e->getMessage() . "\n";
                file_put_contents($logFilePath, $debug_log_entry . "==== DEBUG END ====\n\n", FILE_APPEND | LOCK_EX);
                error_log("Exception saat memproses deposit ID: " . $id_deposit . " - " . $e->getMessage());
                echo '<script>alert("Terjadi kesalahan saat memproses deposit: '.$e->getMessage().'"); window.location.replace("'.$alamat_staff.'ubah_deposit/'.$id_deposit.'");</script>';
            }
        } else {
            /* Untuk status "dibatalkan" atau "diproses" (jika tidak ada API call khusus untuk pembatalan) */
            // Implementasi ini tergantung pada kebijakan bisnis Anda. Saat ini, hanya status yang berubah.
            $stmt_update_deposit_status_only = $koneksi->prepare("UPDATE deposit SET status_deposit = ? WHERE id_deposit = ?");
            if (!$stmt_update_deposit_status_only) throw new Exception("DB Error (prepare update status only): " . $koneksi->error); // Check prepare
            $stmt_update_deposit_status_only->bind_param("ss", $status_deposit_baru, $id_deposit);
            if ($stmt_update_deposit_status_only->execute()) {
                 $debug_log_entry .= "DB Update: Sukses update status deposit (tanpa interaksi API) ke " . $status_deposit_baru . "\n";
                 file_put_contents($logFilePath, $debug_log_entry . "==== DEBUG END ====\n\n", FILE_APPEND | LOCK_EX);
                 echo '<script>alert("Berhasil ubah status deposit menjadi '.$status_deposit_baru.'."); window.location.replace("'.$alamat_staff.'deposit");</script>';
            } else {
                 $error_db_status_only = $stmt_update_deposit_status_only->error;
                 $debug_log_entry .= "DB Update Error: Gagal update status deposit (tanpa interaksi API) - " . $error_db_status_only . "\n";
                 file_put_contents($logFilePath, $debug_log_entry . "==== DEBUG END ====\n\n", FILE_APPEND | LOCK_EX);
                 error_log("Gagal update status deposit di DB untuk ID: " . $id_deposit . " - " . $error_db_status_only);
                 echo '<script>alert("Gagal ubah status data. Error: '.$error_db_status_only.'"); window.location.replace("'.$alamat_staff.'ubah_deposit/'.$id_deposit.'");</script>';
            }
            $stmt_update_deposit_status_only->close();
        }
    } else {
         echo '<script>alert("Tidak ada perubahan status, tidak ada tindakan yang diambil."); window.location.replace("'.$alamat_staff.'deposit");</script>';
    }
    exit();
} else if (isset($_POST['hapus_data'])) {
    $stmt_hapus = $koneksi->prepare("DELETE FROM deposit WHERE id_deposit = ?");
    $stmt_hapus->bind_param("s", $id_deposit);
    if ($stmt_hapus->execute()) {
        $stmt_hapus->close();
        echo '<script>alert("Berhasil hapus data deposit."); window.location.replace("'.$alamat_staff.'deposit");</script>';
    } else {
        $error_hapus = $stmt_hapus->error;
        $stmt_hapus->close();
        echo "Proses Gagal Hapus Data Deposit<br>Error : ".htmlspecialchars($error_hapus);
    }
    exit();
}
?>

<div class="container-xxl flex-grow-1 container-p-y">
  <div class="row gy-4 mb-4">
    <div class="col-md-6">
      <div class="fw-bold fs-4 text-center text-md-start">Ubah Data Deposit</div>
    </div>
    <div class="col-md-6">
      <div class="text-center text-md-end">
        <a href="<?php echo htmlspecialchars($alamat_staff.'deposit'); ?>" class="btn btn-sm btn-primary waves-effect waves-light">
          <span class="tf-icons mdi mdi-chevron-double-left me-1"></span>
          Kembali
        </a>
      </div>
    </div>
  </div>

  <div class="card mb-4">
    <h5 class="card-header">Detail Deposit</h5>
    <form method="post" class="card-body">
      <div class="row g-3">
        <div class="col-md-4">
            <div class="form-floating form-floating-outline">
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($nama_anggota_pengguna_deposit ?? ''); ?>" readonly disabled>
                <label>Nama Pengguna</label>
            </div>
        </div>
        <div class="col-md-4">
          <div class="form-floating form-floating-outline">
            <input type="text" class="form-control" value="<?php echo htmlspecialchars(number_format($jumlah_deposit, 2, ',', '.')); ?>" readonly disabled>
            <label>Jumlah Deposit</label>
          </div>
        </div>
        <div class="col-md-4">
            <div class="form-floating form-floating-outline">
                <input type="text" class="form-control" value="<?php echo htmlspecialchars(number_format($saldo_anggota_sebelum_proses, 2, ',', '.')); ?>" readonly disabled>
                <label>Saldo Anggota Saat Ini (Lokal)</label>
            </div>
        </div>
        <div class="col-md-12 mt-3">
          <div class="form-floating form-floating-outline mb-4">
            <?php
              $disable_select = ($status_deposit_sebelumnya == "disetujui" || $status_deposit_sebelumnya == "dibatalkan") ? "disabled" : "";
            ?>
            <select name="status_deposit" class="form-select" required <?php echo $disable_select; ?>>
              <option value="diproses" <?php if ($status_deposit_sebelumnya == 'diproses') echo 'selected'; ?>>Diproses</option>
              <option value="dibatalkan" <?php if ($status_deposit_sebelumnya == 'dibatalkan') echo 'selected'; ?>>Dibatalkan</option>
              <option value="disetujui" <?php if ($status_deposit_sebelumnya == 'disetujui') echo 'selected'; ?>>Disetujui</option>
            </select>
            <label>Ubah Status Deposit</label>
          </div>
        </div>
      </div>
      <div class="pt-4 text-end">
        <button type="button" class="btn btn-danger waves-effect waves-light me-sm-3 me-1" data-bs-toggle="modal" data-bs-target="#hapus_data" <?php if($disable_select) echo 'disabled'; ?>>
          <span class="tf-icons mdi mdi-delete me-1"></span>
          Hapus
        </button>
        <?php if ($disable_select == ""): ?>
           <button type="submit" name="ubah_data" class="btn btn-primary waves-effect waves-light">
             <span class="tf-icons mdi mdi-content-save me-1"></span>
             Simpan Perubahan Status
           </button>
        <?php else: ?>
            <button type="button" class="btn btn-secondary waves-effect waves-light" disabled>Status Final</button>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>
<div class="modal fade" id="hapus_data" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5">Hapus Data</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="post">
        <div class="modal-body">
          Yakin ingin menghapus data deposit ini? Saldo anggota yang terkait dengan deposit ini tidak akan dikembalikan secara otomatis jika statusnya sudah disetujui.
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" name="hapus_data" class="btn btn-danger">Hapus Permanen</button>
        </div>
      </form>
    </div>
  </div>
</div>