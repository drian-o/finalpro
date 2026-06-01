<?php
/* Pastikan session_start() sudah dipanggil di awal */
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once '../koneksi.php';
include_once '../classes/class.exa.php'; // Pastikan path ke class.exa.php sudah benar

// Aktifkan pelaporan error untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['kode_admin'])) {
    echo '
        <script>
            alert("Terjadi kesalahan, harap masuk kembali!");
            window.location.replace("' . $alamat_admin . 'keluar.php");
        </script>
    ';
    exit();
}

$logFileDir = __DIR__ . '/../logs/';
$logFilePath = $logFileDir . 'debug_admin_saldo_ops.log';

if (!is_dir($logFileDir)) {
    @mkdir($logFileDir, 0775, true);
}

$id_anggota = null;
$data_anggota = null;
$saldo_anggota_sebelum_proses = 0;
$nama_pengguna_anggota = '';
$id_sigma_anggota = null;

if (isset($_GET['id_anggota'])) {
    $id_anggota = $_GET['id_anggota'];
    // Gunakan prepared statement untuk keamanan
    $stmt_anggota = $koneksi->prepare("SELECT saldo_anggota, nama_pengguna_anggota, id_sigma FROM anggota WHERE id_anggota = ?");
    $stmt_anggota->bind_param("i", $id_anggota);
    $stmt_anggota->execute();
    $result_anggota = $stmt_anggota->get_result();
    $data_anggota = $result_anggota->fetch_assoc();
    $stmt_anggota->close();

    if ($data_anggota) {
        $saldo_anggota_sebelum_proses = floatval($data_anggota['saldo_anggota']);
        $nama_pengguna_anggota = $data_anggota['nama_pengguna_anggota'];
        $id_sigma_anggota = $data_anggota['id_sigma'];

        if (empty($id_sigma_anggota)) {
             echo '
                <script>
                    alert("ID Sigma untuk anggota ini tidak ditemukan! Mohon periksa data anggota.");
                    window.location.replace("' . $alamat_admin . 'saldo");
                </script>
            ';
            exit();
        }

    } else {
        echo '
            <script>
                alert("Data anggota tidak ditemukan!");
                window.location.replace("' . $alamat_admin . 'saldo");
            </script>
        ';
        exit();
    }
} else {
    echo '
        <script>
            alert("Pilih anggota yang ingin diubah saldonya!");
            window.location.replace("' . $alamat_admin . 'saldo");
        </script>
    ';
    exit();
}

if (isset($_POST['ubah_data'])) {
    $jumlah_transaksi = isset($_POST['jumlah_transaksi']) ? floatval($_POST['jumlah_transaksi']) : 0;
    $jenis_transaksi_form = $_POST['status_transaksi'];

    if ($jumlah_transaksi <= 0) {
        echo '
            <script>
                alert("Jumlah transaksi harus lebih dari 0.");
                window.location.replace("' . $alamat_admin . 'ubah_saldo/' . $id_anggota . '");
            </script>
        ';
        exit();
    }

    $api_transaksi_response = null;
    $label_operasi = "";
    $debug_log_entry = "";
    $admin_code = $_SESSION['kode_admin'] ?? 'UNKNOWN_ADMIN';
    
    $gameXaAPI = new GameXaAPI();
    
    $auth_response = $gameXaAPI->authenticateAgent();
    if (!$auth_response['success']) {
        $debug_log_entry .= "[" . date("Y-m-d H:i:s") . "] Autentikasi API GameXa GAGAL: " . ($auth_response['message'] ?? 'Respon tidak diketahui') . "\n";
        file_put_contents($logFilePath, $debug_log_entry . "==== DEBUG END ====\n\n", FILE_APPEND | LOCK_EX);
        echo '
            <script>
                alert("Gagal terhubung ke API GameXa: ' . ($auth_response['message'] ?? 'Autentikasi gagal') . '");
                window.location.replace("' . $alamat_admin . 'ubah_saldo/' . $id_anggota . '");
            </script>
        ';
        exit();
    }

    $reference_id = 'ADM-' . strtoupper($jenis_transaksi_form) . '-' . $nama_pengguna_anggota . '-' . time();
    $kode_transaksi = 'ADM-' . strtoupper($jenis_transaksi_form) . '-' . time();
    $waktu_sekarang = date('Y-m-d H:i:s');


    if ($jenis_transaksi_form == 'deposit') {
        $label_operasi = "Deposit";
        $api_transaksi_response = $gameXaAPI->depositToPlayer($id_sigma_anggota, $jumlah_transaksi, $reference_id);
    } elseif ($jenis_transaksi_form == 'withdraw') {
        if ($saldo_anggota_sebelum_proses < $jumlah_transaksi) {
            echo '
                <script>
                    alert("Saldo anggota lokal (Rp. ' . number_format($saldo_anggota_sebelum_proses, 2, ',', '.') . ') tidak mencukupi untuk withdraw sebesar Rp. ' . number_format($jumlah_transaksi, 2, ',', '.') . '.");
                    window.location.replace("' . $alamat_admin . 'ubah_saldo/' . $id_anggota . '");
                </script>
            ';
            exit();
        }
        $label_operasi = "Withdraw";
        $api_transaksi_response = $gameXaAPI->withdrawFromPlayer($id_sigma_anggota, $jumlah_transaksi, $reference_id);
    } else {
        echo '
            <script>
                alert("Jenis transaksi tidak valid!");
                window.location.replace("' . $alamat_admin . 'ubah_saldo/' . $id_anggota . '");
            </script>
        ';
        exit();
    }
    
    $request_params_for_log = [
        'player_id_api' => $id_sigma_anggota,
        'amount' => $jumlah_transaksi,
        'reference_id' => $reference_id,
        'type' => $jenis_transaksi_form
    ];
    
    $debug_log_entry .= "[" . date("Y-m-d H:i:s") . "] ==== ADMIN SALDO OPERATION DEBUG ====\n";
    $debug_log_entry .= "Admin: " . $admin_code . "\n";
    $debug_log_entry .= "Target User DB ID: " . $id_anggota . " (Username: " . $nama_pengguna_anggota . ")\n";
    $debug_log_entry .= "Target API Player ID (id_sigma): " . $id_sigma_anggota . "\n";
    $debug_log_entry .= "Operation: " . $label_operasi . " Sejumlah: " . $jumlah_transaksi . "\n";
    $debug_log_entry .= "Request Params (Transaksi): " . json_encode($request_params_for_log) . "\n";

    if ($api_transaksi_response !== null) {
        $debug_log_entry .= "API Response (Transaksi): " . json_encode($api_transaksi_response) . "\n";
    } else {
        $debug_log_entry .= "API Response (Transaksi): NULL\n";
    }

    $saldo_final_untuk_db = null;

    if ($api_transaksi_response && $api_transaksi_response['success']) {
        $debug_log_entry .= "Transaksi API sukses. Memanggil getPlayerBalance untuk player ID: " . $id_sigma_anggota . "\n";
        
        // Gunakan balance_after dari respons API jika tersedia
        if (isset($api_transaksi_response['data']['data']['balance_after'])) {
            $saldo_final_untuk_db = floatval($api_transaksi_response['data']['data']['balance_after']);
            $debug_log_entry .= "Saldo final dari API Response: " . $saldo_final_untuk_db . "\n";
        } else {
             $debug_log_entry .= "Warning: 'balance_after' tidak ditemukan di respons API. Memanggil getPlayerBalance sebagai fallback.\n";
            $getBalance_response = $gameXaAPI->getPlayerBalance($id_sigma_anggota);
            
            if ($getBalance_response !== null) {
                $debug_log_entry .= "API Response (getPlayerBalance): " . json_encode($getBalance_response) . "\n";
            } else {
                $debug_log_entry .= "API Response (getPlayerBalance): NULL\n";
            }
            if ($getBalance_response && $getBalance_response['success'] && isset($getBalance_response['data']['balance'])) {
                $saldo_final_untuk_db = floatval($getBalance_response['data']['balance']);
                $debug_log_entry .= "Saldo final dari getPlayerBalance: " . $saldo_final_untuk_db . "\n";
            } else {
                $errMsgGetBalance = "Gagal mengambil saldo definitif dari getPlayerBalance API setelah transaksi sukses.";
                if($getBalance_response && isset($getBalance_response['message'])) {
                    $errMsgGetBalance .= " Pesan getPlayerBalance API: " . htmlspecialchars($getBalance_response['message']);
                }
                $debug_log_entry .= "Error: " . $errMsgGetBalance . "\n";
            }
        }

        if ($saldo_final_untuk_db !== null) {
            // Mulai transaksi database untuk memastikan kedua operasi berhasil atau gagal bersamaan
            mysqli_begin_transaction($koneksi);
            $db_update_success = false;

            // Step 1: Update saldo anggota di tabel `anggota`
            $query_update_saldo = "UPDATE anggota SET saldo_anggota = ? WHERE id_anggota = ?";
            $stmt_update = $koneksi->prepare($query_update_saldo);
            $stmt_update->bind_param("di", $saldo_final_untuk_db, $id_anggota); 
            $update_anggota_ok = $stmt_update->execute();
            $stmt_update->close();

            // Step 2: Masukkan catatan transaksi ke tabel yang sesuai
            $insert_transaksi_ok = false;
            if ($update_anggota_ok) {
                if ($jenis_transaksi_form == 'deposit') {
                    $query_insert = "INSERT INTO deposit (id_anggota_deposit, kode_deposit, nama_pengguna_anggota_deposit, jumlah_deposit, tanggal_deposit, status_deposit, reference_id) VALUES (?, ?, ?, ?, ?, 'disetujui', ?)";
                    $stmt_insert = $koneksi->prepare($query_insert);
                    $stmt_insert->bind_param("isssss", $id_anggota, $kode_transaksi, $nama_pengguna_anggota, $jumlah_transaksi, $waktu_sekarang, $reference_id);
                    $insert_transaksi_ok = $stmt_insert->execute();
                    $stmt_insert->close();
                } elseif ($jenis_transaksi_form == 'withdraw') {
                    $query_insert = "INSERT INTO withdraw (id_anggota_withdraw, kode_withdraw, jumlah_withdraw, tanggal_withdraw, status_withdraw, reference_id) VALUES (?, ?, ?, ?, 'disetujui', ?)";
                    $stmt_insert = $koneksi->prepare($query_insert);
                    $stmt_insert->bind_param("issss", $id_anggota, $kode_transaksi, $jumlah_transaksi, $waktu_sekarang, $reference_id);
                    $insert_transaksi_ok = $stmt_insert->execute();
                    $stmt_insert->close();
                }
            }
            
            // Periksa hasil dari kedua operasi dan commit atau rollback
            if ($update_anggota_ok && $insert_transaksi_ok) {
                mysqli_commit($koneksi);
                $db_update_success = true;
                $debug_log_entry .= "DB Update & Insert: SUKSES, transaksi commit.\n";
            } else {
                mysqli_rollback($koneksi);
                $debug_log_entry .= "DB Update & Insert: GAGAL, transaksi rollback.\n";
                if (!$update_anggota_ok) {
                    $debug_log_entry .= "Error: Gagal update saldo anggota di tabel `anggota`.\n";
                }
                if (!$insert_transaksi_ok) {
                    $debug_log_entry .= "Error: Gagal insert ke tabel transaksi (`deposit` atau `withdraw`).\n";
                }
            }

            if ($db_update_success) {
                $debug_log_entry .= "DB Update: Sukses update saldo anggota ke: " . $saldo_final_untuk_db . "\n";
                file_put_contents($logFilePath, $debug_log_entry . "==== DEBUG END ====\n\n", FILE_APPEND | LOCK_EX);
                $pesan_sukses = "Berhasil proses " . strtolower($label_operasi) . " via API dan sinkronisasi saldo.";
                echo '
                    <script>
                        alert("' . $pesan_sukses . ' Saldo baru di sistem: Rp. ' . number_format($saldo_final_untuk_db, 2, ',', '.') . '");
                        window.location.replace("' . $alamat_admin . 'saldo");
                    </script>
                ';
            } else {
                $error_db = mysqli_error($koneksi);
                $debug_log_entry .= "DB Operation Error: Gagal update saldo anggota atau insert transaksi - " . $error_db . "\n";
                file_put_contents($logFilePath, $debug_log_entry . "==== DEBUG END ====\n\n", FILE_APPEND | LOCK_EX);
                error_log("Gagal update saldo DB lokal atau insert transaksi setelah API sukses untuk player ID " . $id_sigma_anggota . " - " . $error_db);
                echo '
                    <script>
                        alert("Transaksi (' . $label_operasi . ') dan getPlayerBalance API sukses, tapi gagal update saldo atau insert transaksi di database lokal. Error: ' . $error_db . '");
                        window.location.replace("' . $alamat_admin . 'ubah_saldo/' . $id_anggota . '");
                    </script>
                ';
            }
        } else {
            $pesan_kritis_getbalance = "Transaksi " . $label_operasi . " di API provider SUKSES, tetapi GAGAL mendapatkan saldo terbaru dari getPlayerBalance API untuk sinkronisasi ke database lokal. Saldo di database LOKAL BELUM DIUBAH. Harap periksa saldo pengguna di provider dan log.";
            $debug_log_entry .= "Critical Alert: " . $pesan_kritis_getbalance . "\n";
            file_put_contents($logFilePath, $debug_log_entry . "==== DEBUG END ====\n\n", FILE_APPEND | LOCK_EX);
            echo '<script>alert("' . $pesan_kritis_getbalance . '"); window.location.replace("' . $alamat_admin . 'ubah_saldo/' . $id_anggota . '");</script>';
        }
    } else {
        $pesan_error_api_lengkap = "Proses Transaksi (" . $label_operasi . ") via API gagal.";
        if ($api_transaksi_response && isset($api_transaksi_response['message'])) {
            $pesan_error_api_lengkap .= " Pesan: " . htmlspecialchars($api_transaksi_response['message']);
        } elseif ($api_transaksi_response === null) {
            $pesan_error_api_lengkap = "Gagal menghubungi API (" . $label_operasi . ") atau API mengembalikan respons null.";
        } else {
            $pesan_error_api_lengkap .= " Status API: " . (isset($api_transaksi_response['success']) ? ($api_transaksi_response['success'] ? 'Sukses' : 'Gagal') : 'Tidak diketahui atau NULL') . ".";
        }
        $debug_log_entry .= "API Call (Transaksi) Failed: " . $pesan_error_api_lengkap . "\n";
        file_put_contents($logFilePath, $debug_log_entry . "==== DEBUG END ====\n\n", FILE_APPEND | LOCK_EX);
        echo '
            <script>
                alert("' . $pesan_error_api_lengkap . '");
                window.location.replace("' . $alamat_admin . 'ubah_saldo/' . $id_anggota . '");
            </script>
        ';
    }
    exit();
}
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row gy-4 mb-4">
        <div class="col-md-6">
            <div class="fw-bold fs-4 text-center text-md-start">Ubah Saldo Anggota</div>
        </div>
        <div class="col-md-6">
            <div class="text-center text-md-end">
                <a href="<?php echo htmlspecialchars($alamat_admin . 'saldo'); ?>" class="btn btn-sm btn-primary waves-effect waves-light">
                    <span class="tf-icons mdi mdi-chevron-double-left me-1"></span>
                    Kembali
                </a>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <h5 class="card-header">Formulir Ubah Saldo</h5>
        <form method="post" class="card-body">
            <hr class="my-4 mx-n4">
            <h6>Informasi Anggota</h6>
             <div class="row g-3 mb-4">
                 <div class="col-md-6">
                     <div class="form-floating form-floating-outline">
                         <input type="text" class="form-control" value="<?php echo htmlspecialchars($nama_pengguna_anggota); ?>" readonly disabled>
                         <label>Nama Pengguna</label>
                     </div>
                 </div>
                  <div class="col-md-6">
                     <div class="form-floating form-floating-outline">
                         <input type="text" class="form-control" value="<?php echo htmlspecialchars(number_format($saldo_anggota_sebelum_proses, 2, ',', '.')); ?>" readonly disabled>
                         <label>Saldo Saat Ini (Lokal)</label>
                     </div>
                 </div>
             </div>

            <h6>Transaksi Saldo</h6>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="form-floating form-floating-outline">
                        <input type="number" name="jumlah_transaksi" class="form-control" placeholder="Masukkan jumlah" required min="1" step="any">
                        <label>Jumlah Transaksi</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating form-floating-outline">
                        <select name="status_transaksi" class="form-select" required>
                            <option value="" disabled selected>Pilih Jenis Transaksi</option>
                            <option value="deposit">Tambah Saldo (Deposit)</option>
                            <option value="withdraw">Kurangi Saldo (Withdraw)</option>
                        </select>
                        <label>Jenis Transaksi</label>
                    </div>
                </div>
            </div>
            <div class="pt-4 text-end">
                <button type="submit" name="ubah_data" class="btn btn-primary waves-effect waves-light">
                    <span class="tf-icons mdi mdi-content-save me-1"></span>
                    Proses Transaksi
                </button>
            </div>
        </form>
    </div>
</div>