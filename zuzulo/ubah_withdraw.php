<?php
/* Pastikan session_start() sudah dipanggil di awal */
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once '../koneksi.php';
include_once '../classes/class.exa.php'; // Menggunakan kelas GameXaAPI

if (!isset($_SESSION['kode_admin'])) {
    echo '
        <script>
            alert("Terjadi kesalahan, harap masuk kembali!");
            window.location.replace("'.$alamat_admin.'keluar.php");
        </script>
    ';
    exit();
}

$logFileDir = __DIR__ . '/../logs/';
$logFilePath = $logFileDir . 'debug_admin_withdraw_status_change.log';

if (!is_dir($logFileDir)) {
    @mkdir($logFileDir, 0775, true);
}

$id_withdraw = null;
$data_withdraw = null;
$status_withdraw_sebelumnya = '';
$nama_pengguna_anggota_withdraw = '';
$jumlah_withdraw_display = 0;
$id_anggota_withdraw = null;
$id_sigma_anggota = null; // Menambahkan variabel untuk id_sigma
$reference_id = ''; // Untuk menyimpan ID referensi API

// Instansiasi GameXaAPI
$exaAPI = new GameXaAPI();

if (isset($_GET['id_withdraw'])) {
    $id_withdraw = $_GET['id_withdraw'];
    $stmt_get_withdraw = $koneksi->prepare("SELECT w.*, a.nama_pengguna_anggota, a.id_sigma 
                                           FROM withdraw w 
                                           JOIN anggota a ON w.id_anggota_withdraw = a.id_anggota 
                                           WHERE w.id_withdraw = ?");
    $stmt_get_withdraw->bind_param("s", $id_withdraw);
    $stmt_get_withdraw->execute();
    $result_withdraw = $stmt_get_withdraw->get_result();
    $data_withdraw = $result_withdraw->fetch_assoc();
    $stmt_get_withdraw->close();

    if ($data_withdraw) {
        $id_anggota_withdraw = $data_withdraw['id_anggota_withdraw'];
        $status_withdraw_sebelumnya = $data_withdraw['status_withdraw'];
        $nama_pengguna_anggota_withdraw = $data_withdraw['nama_pengguna_anggota'];
        $jumlah_withdraw_display = floatval($data_withdraw['jumlah_withdraw']);
        $id_sigma_anggota = $data_withdraw['id_sigma'];
        $reference_id = $data_withdraw['reference_id'] ?? '';
    } else {
        echo '
            <script>
                alert("Data withdraw tidak ditemukan!");
                window.location.replace("'.$alamat_admin.'withdraw");
            </script>
        ';
        exit();
    }
} else {
    echo '
        <script>
            alert("Pilih withdraw yang ingin diubah!");
            window.location.replace("'.$alamat_admin.'withdraw");
        </script>
    ';
    exit();
}

if (isset($_POST['ubah_data'])) {
    $status_withdraw_baru = $_POST['status_withdraw'];
    $admin_code = $_SESSION['kode_admin'] ?? 'UNKNOWN_ADMIN';
    $debug_log_entry = "";

    // Hanya lakukan tindakan jika ada perubahan status
    if ($status_withdraw_baru != $status_withdraw_sebelumnya) {
        $debug_log_entry .= "[" . date("Y-m-d H:i:s") . "] ==== ADMIN WITHDRAW STATUS CHANGE ====\n";
        $debug_log_entry .= "Admin: " . $admin_code . "\n";
        $debug_log_entry .= "Withdraw ID: " . $id_withdraw . "\n";
        $debug_log_entry .= "Target User: " . $nama_pengguna_anggota_withdraw . "\n";
        $debug_log_entry .= "Jumlah Withdraw: " . $jumlah_withdraw_display . "\n";
        $debug_log_entry .= "Status Lama: " . $status_withdraw_sebelumnya . ", Status Baru: " . $status_withdraw_baru . "\n";
        $debug_log_entry .= "ID Sigma Anggota: " . $id_sigma_anggota . "\n";

        $koneksi->begin_transaction();
        try {
            // Logika untuk status "dibatalkan"
            if ($status_withdraw_baru == "dibatalkan" && $status_withdraw_sebelumnya != "dibatalkan") {
                if (empty($id_sigma_anggota)) {
                    throw new Exception("ID Sigma (Player ID) anggota tidak ditemukan. Tidak dapat mengembalikan dana.");
                }

                // Cek apakah reference_id sudah ada, jika belum buat yang baru
                if (empty($reference_id)) {
                    $reference_id = 'cancel_wd_' . uniqid(rand(), true);
                }
                $debug_log_entry .= "Generated new reference_id for cancellation: " . $reference_id . "\n";
                
                $request_params_for_log = [
                    'playerId' => $id_sigma_anggota,
                    'amount' => $jumlah_withdraw_display,
                    'referenceId' => $reference_id
                ];
                $debug_log_entry .= "Request Params (depositToPlayer for cancellation): " . json_encode($request_params_for_log) . "\n";

                // Panggil API depositToPlayer untuk mengembalikan dana
                $api_transaksi_response = $exaAPI->depositToPlayer($id_sigma_anggota, $jumlah_withdraw_display, $reference_id);
                
                if ($api_transaksi_response !== null && isset($api_transaksi_response['success']) && $api_transaksi_response['success'] === true) {
                    $debug_log_entry .= "API Deposit (untuk pembatalan withdraw) sukses.\n";

                    // Ambil saldo terbaru dari API setelah transaksi
                    $getBalance_response = $exaAPI->getPlayerBalance($id_sigma_anggota);
                    if ($getBalance_response !== null && isset($getBalance_response['success']) && $getBalance_response['success'] === true && isset($getBalance_response['data']['balance'])) {
                        $saldo_final_untuk_db = floatval($getBalance_response['data']['balance']);
                        $debug_log_entry .= "Saldo final dari getPlayerBalance: " . $saldo_final_untuk_db . "\n";

                        // Perbarui saldo di database lokal
                        $stmt_update_saldo = $koneksi->prepare("UPDATE anggota SET saldo_anggota = ? WHERE id_anggota = ?");
                        $stmt_update_saldo->bind_param("ds", $saldo_final_untuk_db, $id_anggota_withdraw);
                        if (!$stmt_update_saldo->execute()) {
                            throw new Exception("Gagal update saldo anggota di DB: " . $stmt_update_saldo->error);
                        }
                        $stmt_update_saldo->close();
                        $debug_log_entry .= "DB Update: Sukses update saldo anggota.\n";
                    } else {
                        throw new Exception("Gagal mendapatkan saldo definitif dari API setelah pembatalan.");
                    }
                } else {
                    $pesan_error_api = "Proses pengembalian dana via API gagal.";
                    if ($api_transaksi_response && isset($api_transaksi_response['message'])) {
                        $pesan_error_api .= " Pesan: " . htmlspecialchars($api_transaksi_response['message']);
                    }
                    throw new Exception($pesan_error_api);
                }
            }

            // Selalu update status withdraw di database lokal setelah semua proses selesai
            $stmt_update_withdraw = $koneksi->prepare("UPDATE withdraw SET status_withdraw = ?, reference_id = ? WHERE id_withdraw = ?");
            $stmt_update_withdraw->bind_param("sss", $status_withdraw_baru, $reference_id, $id_withdraw);
            if (!$stmt_update_withdraw->execute()) {
                 throw new Exception("Gagal update status withdraw di database: " . $stmt_update_withdraw->error);
            }
            $stmt_update_withdraw->close();

            $koneksi->commit();
            $debug_log_entry .= "DB Update: Sukses update status withdraw di database lokal.\n";
            file_put_contents($logFilePath, $debug_log_entry . "==== DEBUG END ====\n\n", FILE_APPEND | LOCK_EX);
            echo '<script>alert("Status withdraw berhasil diubah menjadi '.$status_withdraw_baru.' dan saldo dikembalikan."); window.location.replace("'.$alamat_admin.'withdraw");</script>';
            
        } catch (Exception $e) {
            $koneksi->rollback();
            $debug_log_entry .= "DB/API Exception: " . $e->getMessage() . "\n";
            file_put_contents($logFilePath, $debug_log_entry . "==== DEBUG END ====\n\n", FILE_APPEND | LOCK_EX);
            error_log("Exception saat update status withdraw: " . $id_withdraw . " - " . $e->getMessage());
            echo '<script>alert("Terjadi kesalahan saat memproses withdraw: '.htmlspecialchars($e->getMessage()).'"); window.location.replace("'.$alamat_admin.'ubah_withdraw/'.$id_withdraw.'");</script>';
        }

    } else {
         echo '<script>alert("Tidak ada perubahan status, tidak ada tindakan yang diambil."); window.location.replace("'.$alamat_admin.'withdraw");</script>';
    }
    exit();
} else if (isset($_POST['hapus_data'])) {
    $admin_code = $_SESSION['kode_admin'] ?? 'UNKNOWN_ADMIN';
    $debug_log_entry = "[" . date("Y-m-d H:i:s") . "] ==== ADMIN WITHDRAW DELETE ATTEMPT (LOCAL ONLY) ====\n";
    $debug_log_entry .= "Admin: " . $admin_code . "\n";
    $debug_log_entry .= "Withdraw ID: " . $id_withdraw . "\n";
    $debug_log_entry .= "Target User: " . $nama_pengguna_anggota_withdraw . "\n";
    $debug_log_entry .= "Jumlah Withdraw: " . $jumlah_withdraw_display . "\n";
    $debug_log_entry .= "Status Saat Dihapus: " . $status_withdraw_sebelumnya . "\n";

    $stmt_hapus = $koneksi->prepare("DELETE FROM withdraw WHERE id_withdraw = ?");
    $stmt_hapus->bind_param("s", $id_withdraw);
    if ($stmt_hapus->execute()) {
        $affected_rows_delete = $stmt_hapus->affected_rows;
        $stmt_hapus->close();
        $debug_log_entry .= "DB Delete: Sukses hapus data withdraw. Rows affected: " . $affected_rows_delete . "\n";
        file_put_contents($logFilePath, $debug_log_entry . "==== DEBUG END ====\n\n", FILE_APPEND | LOCK_EX);
        echo '<script>alert("Berhasil hapus data withdraw dari sistem lokal."); window.location.replace("'.$alamat_admin.'withdraw");</script>';
    } else {
        $error_hapus = $stmt_hapus->error;
        $stmt_hapus->close();
        $debug_log_entry .= "DB Delete Error: Gagal hapus data withdraw - " . $error_hapus . "\n";
        file_put_contents($logFilePath, $debug_log_entry . "==== DEBUG END ====\n\n", FILE_APPEND | LOCK_EX);
        echo "Proses Gagal Hapus Data Withdraw<br>Error : ".htmlspecialchars($error_hapus);
    }
    exit();
}
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row gy-4 mb-4">
        <div class="col-md-6">
            <div class="fw-bold fs-4 text-center text-md-start">Ubah Status Withdraw</div>
        </div>
        <div class="col-md-6">
            <div class="text-center text-md-end">
                <a href="<?php echo htmlspecialchars($alamat_admin.'withdraw'); ?>" class="btn btn-sm btn-primary waves-effect waves-light">
                    <span class="tf-icons mdi mdi-chevron-double-left me-1"></span>
                    Kembali
                </a>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <h5 class="card-header">Detail Withdraw (Informasi dari Database Lokal)</h5>
        <form method="post" class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="form-floating form-floating-outline">
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($nama_pengguna_anggota_withdraw ?? ''); ?>" readonly disabled>
                        <label>Nama Pengguna</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating form-floating-outline">
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars(number_format($jumlah_withdraw_display, 2, ',', '.')); ?>" readonly disabled>
                        <label>Jumlah Withdraw</label>
                    </div>
                </div>
                <div class="col-md-12 mt-3">
                    <div class="form-floating form-floating-outline">
                        <textarea class="form-control" readonly disabled style="height: 80px;"><?php echo htmlspecialchars($data_withdraw['tujuan_withdraw'] ?? ''); ?></textarea>
                        <label>Tujuan Withdraw</label>
                    </div>
                </div>
                <div class="col-md-12 mt-3">
                    <div class="form-floating form-floating-outline mb-4">
                        <select name="status_withdraw" class="form-select" required>
                            <option value="diproses" <?php if ($status_withdraw_sebelumnya == 'diproses') echo 'selected'; ?>>Diproses</option>
                            <option value="dibatalkan" <?php if ($status_withdraw_sebelumnya == 'dibatalkan') echo 'selected'; ?>>Dibatalkan</option>
                            <option value="disetujui" <?php if ($status_withdraw_sebelumnya == 'disetujui') echo 'selected'; ?>>Disetujui</option>
                        </select>
                        <label>Ubah Status Withdraw</label>
                    </div>
                </div>
            </div>
            <div class="pt-4 text-end">
                <button type="button" class="btn btn-danger waves-effect waves-light me-sm-3 me-1" data-bs-toggle="modal" data-bs-target="#hapus_data">
                    <span class="tf-icons mdi mdi-delete me-1"></span>
                    Hapus Data Withdraw Lokal
                </button>
                <button type="submit" name="ubah_data" class="btn btn-primary waves-effect waves-light">
                    <span class="tf-icons mdi mdi-content-save me-1"></span>
                    Simpan Perubahan Status
                </button>
            </div>
        </form>
    </div>
</div>
<div class="modal fade" id="hapus_data" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5">Hapus Data Withdraw Lokal</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    Yakin ingin menghapus data withdraw ini dari sistem lokal? Tindakan ini tidak memengaruhi status di provider API atau saldo anggota di API.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="hapus_data" class="btn btn-danger">Hapus Data Lokal</button>
                </div>
            </form>
        </div>
    </div>
</div>