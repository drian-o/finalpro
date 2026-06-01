<?php
// qris_callback.php

// --- Konfigurasi ---
$logFileDir = __DIR__ . '/logs/';
$logFilePath = $logFileDir . 'qris_callback.log';
$statusDir = __DIR__ . '/status_pembayaran/';
define('YOUR_QRIS_MERCHANT_UID', 'f0470e36-3904-4488-82e8-259d9acbe879');

if (!is_dir($logFileDir)) { @mkdir($logFileDir, 0775, true); }
if (!is_dir($statusDir)) { @mkdir($statusDir, 0775, true); }

include_once __DIR__ . '/koneksi.php';
include_once __DIR__ . '/classes/class.exa.php';
require_once __DIR__ . '/functions_telegram.php'; // Tambahkan ini

function log_qris_cb($message) {
    global $logFilePath;
    $timestamp = date("Y-m-d H:i:s");
    file_put_contents($logFilePath, "[{$timestamp}] " . $message . PHP_EOL, FILE_APPEND | LOCK_EX);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    log_qris_cb("Metode request bukan POST. Diterima: " . $_SERVER['REQUEST_METHOD']);
    http_response_code(405); 
    echo json_encode(['status' => false, 'error' => 'Metode tidak diizinkan.']);
    exit;
}

$rawData = file_get_contents('php://input');
log_qris_cb("Raw data callback QRIS diterima: " . $rawData);

if (empty($rawData)) {
    log_qris_cb("Tidak ada data POST (raw) diterima dari callback QRIS.");
    http_response_code(400); 
    echo json_encode(['status' => false, 'error' => 'Tidak ada data diterima.']);
    exit;
}

$qrisCallbackData = json_decode($rawData, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    log_qris_cb("Data JSON tidak valid dari callback QRIS: " . json_last_error_msg() . ". Data mentah: " . $rawData);
    http_response_code(400); 
    echo json_encode(['status' => false, 'error' => 'Format JSON tidak valid.']);
    exit;
}

log_qris_cb("Data JSON callback QRIS berhasil di-decode: " . print_r($qrisCallbackData, true));

if (!isset($qrisCallbackData['status'], $qrisCallbackData['trx_id'], $qrisCallbackData['merchant_id'], $qrisCallbackData['terminal_id'], $qrisCallbackData['amount'])) {
    log_qris_cb("Data callback QRIS tidak lengkap. Data: " . print_r($qrisCallbackData, true));
    http_response_code(400); 
    echo json_encode(['status' => false, 'error' => 'Data callback QRIS tidak lengkap.']);
    exit;
}

if ($qrisCallbackData['merchant_id'] !== YOUR_QRIS_MERCHANT_UID) {
    log_qris_cb("Merchant ID QRIS tidak cocok. Diterima: " . $qrisCallbackData['merchant_id'] . ", Diharapkan: " . YOUR_QRIS_MERCHANT_UID);
    http_response_code(403); 
    echo json_encode(['status' => false, 'error' => 'Merchant ID QRIS tidak valid.']);
    exit;
}

$trx_id_qris_provider = $qrisCallbackData['trx_id'];
$payment_status_qris = strtoupper(trim($qrisCallbackData['status']));
$username_pemain = $qrisCallbackData['terminal_id'];
$amount_paid_qris = floatval($qrisCallbackData['amount']);

$trx_id_filename_polling = basename(preg_replace("/[^a-zA-Z0-9_-]/", "", $trx_id_qris_provider));

if (empty($trx_id_filename_polling)) {
    log_qris_cb("TRX ID QRIS tidak valid setelah sanitasi untuk file polling: '{$trx_id_qris_provider}' menjadi '{$trx_id_filename_polling}'");
    http_response_code(400); echo json_encode(['status' => false, 'error' => 'TRX ID QRIS tidak valid.']); exit;
}
$statusPollingFilePath = $statusDir . $trx_id_filename_polling . '.txt';
$qrImageFilePathOnServer = __DIR__ . '/folder_qr/' . $trx_id_filename_polling . '.png';

$final_statuses_qris = ['SUCCESS', 'PAID', 'FAILED', 'EXPIRED'];
if (in_array($payment_status_qris, $final_statuses_qris)) {
    if (file_put_contents($statusPollingFilePath, $payment_status_qris)) {
        log_qris_cb("Status Polling '{$payment_status_qris}' untuk TRX ID QRIS {$trx_id_qris_provider} disimpan.");
        if (file_exists($qrImageFilePathOnServer)) {
            if (@unlink($qrImageFilePathOnServer)) {
                log_qris_cb("Gambar QR {$qrImageFilePathOnServer} dihapus untuk TRX ID QRIS {$trx_id_qris_provider}.");
            } else {
                log_qris_cb("GAGAL hapus gambar QR {$qrImageFilePathOnServer} untuk TRX ID QRIS {$trx_id_qris_provider}.");
            }
        }
    } else {
        log_qris_cb("GAGAL simpan status polling '{$payment_status_qris}' untuk TRX ID QRIS {$trx_id_qris_provider} ke {$statusPollingFilePath}.");
        error_log("CRITICAL: Gagal tulis file status polling: {$trx_id_qris_provider}, Status: {$payment_status_qris}");
    }
} else {
    log_qris_cb("Status callback QRIS '{$payment_status_qris}' bukan status final. TRX ID QRIS: {$trx_id_qris_provider}");
}

if ($payment_status_qris === 'SUCCESS') {
    log_qris_cb("Pembayaran QRIS BERHASIL: TRX_ID_QRIS={$trx_id_qris_provider}, User={$username_pemain}, Amount={$amount_paid_qris}");

    $GameXaAPI = new GameXaAPI(); 

    if (!isset($GameXaAPI) || !is_object($GameXaAPI)) {
        log_qris_cb("CRITICAL: Objek \$GameXaAPI tidak tersedia."); 
        error_log("CRITICAL: \$GameXaAPI not avail in qris_callback for {$username_pemain}, QRIS_TRX_ID {$trx_id_qris_provider}");
        http_response_code(200); echo json_encode(['status' => true, 'message' => 'Callback QRIS OK, error internal (GameXaAPI).']); exit;
    }
    if (!isset($koneksi) || !($koneksi instanceof mysqli) || $koneksi->connect_error) {
        log_qris_cb("CRITICAL: Koneksi DB tidak tersedia.");
        error_log("CRITICAL: Koneksi DB not avail in qris_callback for {$username_pemain}, QRIS_TRX_ID {$trx_id_qris_provider}");
        http_response_code(200); echo json_encode(['status' => true, 'message' => 'Callback QRIS OK, error internal (DB).']); exit;
    }

    $id_anggota_db = null;
    $id_sigma = null;
    $referer_username = null; 
    
    $stmt_get_id = $koneksi->prepare("SELECT id_anggota, id_sigma, refferal FROM anggota WHERE nama_pengguna_anggota = ?");
    if ($stmt_get_id) {
        $stmt_get_id->bind_param("s", $username_pemain); $stmt_get_id->execute();
        $result_id = $stmt_get_id->get_result();
        if($data_id = $result_id->fetch_assoc()) { 
            $id_anggota_db = $data_id['id_anggota']; 
            $id_sigma = $data_id['id_sigma'];
            $referer_username = $data_id['refferal']; 
        }
        $stmt_get_id->close();
    }

    if (!$id_anggota_db || !$id_sigma) {
        log_qris_cb("CRITICAL: id_anggota atau id_sigma tidak ditemukan untuk '{$username_pemain}'. Tidak bisa proses. QRIS_TRX_ID: {$trx_id_qris_provider}");
        error_log("CRITICAL: id_anggota or id_sigma not found for '{$username_pemain}'. QRIS_TRX_ID: {$trx_id_qris_provider}");
        http_response_code(200); echo json_encode(['status' => true, 'message' => 'Callback QRIS OK, user tidak ditemukan atau ID Sigma belum terdaftar.']); exit;
    }

    // Cek duplikasi di tabel deposit menggunakan transaction_id dari QRIS provider
    $status_deposit_lokal = null;
    $id_deposit_lokal = null; // Tambahkan ini
    $kode_deposit_lokal = null; // Tambahkan ini
    $stmt_check_deposit = $koneksi->prepare("SELECT id_deposit, kode_deposit, status_deposit FROM deposit WHERE transaction_id = ? AND id_anggota_deposit = ?"); // Tambahkan id_deposit dan kode_deposit
    if ($stmt_check_deposit) {
        $stmt_check_deposit->bind_param("si", $trx_id_qris_provider, $id_anggota_db);
        $stmt_check_deposit->execute(); 
        $stmt_check_deposit->bind_result($id_deposit_lokal, $kode_deposit_lokal, $status_deposit_lokal); // Bind hasilnya
        $stmt_check_deposit->fetch(); 
        $stmt_check_deposit->close();
    }
    log_qris_cb("Current status_deposit_lokal for {$trx_id_qris_provider}: " . ($status_deposit_lokal ?? 'NULL'));

    if ($status_deposit_lokal === 'disetujui') {
        log_qris_cb("Deposit untuk QRIS TRX ID {$trx_id_qris_provider} sudah 'disetujui'. Skipping duplicate.");
        http_response_code(200); echo json_encode(['status' => true, 'message' => 'Deposit sudah diproses.']); exit;
    }
    
    // Hanya proses jika status lokal 'diproses' atau belum ada (jika insert deposit saat callback)
    if ($status_deposit_lokal === 'diproses' || $status_deposit_lokal === null) {
        try {
            $koneksi->begin_transaction();
            log_qris_cb("Memulai transaksi DB. QRIS_TRX_ID: {$trx_id_qris_provider}");

            log_qris_cb("GameXa Call: DepositToPlayer untuk ID Sigma '{$id_sigma}', Amount {$amount_paid_qris}, Reference ID: {$trx_id_qris_provider}.");
            $gamexa_deposit_response = $GameXaAPI->depositToPlayer($id_sigma, $amount_paid_qris, $trx_id_qris_provider);
            log_qris_cb("GameXa Resp (Deposit): " . json_encode($gamexa_deposit_response));

            if (!($gamexa_deposit_response['success'] ?? false) || !isset($gamexa_deposit_response['data']['data']['balance_after'])) {
                throw new Exception("Gagal proses deposit di GameXa API (struktur respon GameXa tidak sesuai harapan). Respon: " . json_encode($gamexa_deposit_response));
            }
            log_qris_cb("GameXa: Deposit sukses untuk {$username_pemain}. Amount: {$gamexa_deposit_response['data']['data']['amount']}, Before: {$gamexa_deposit_response['data']['data']['balance_before']}, After: {$gamexa_deposit_response['data']['data']['balance_after']}.");

            // --- PANGGIL API GameXa UNTUK GET BALANCE (untuk konfirmasi saldo terbaru) ---
            log_qris_cb("GameXa Call: GetPlayerBalance untuk ID Sigma '{$id_sigma}'.");
            $gamexa_getbalance_response = $GameXaAPI->getPlayerBalance($id_sigma);
            log_qris_cb("GameXa Resp (GetPlayerBalance): " . json_encode($gamexa_getbalance_response));

            $saldo_baru_dari_gamexa = null;
            if (($gamexa_getbalance_response['success'] ?? false) && isset($gamexa_getbalance_response['data']['balance'])) {
                $saldo_baru_dari_gamexa = floatval($gamexa_getbalance_response['data']['balance']);
                log_qris_cb("GameXa: Saldo terbaru dari GameXa untuk {$username_pemain} adalah {$saldo_baru_dari_gamexa}.");
            } else { 
                throw new Exception("Gagal ambil saldo terbaru dari GameXa setelah deposit sukses. Respon: " . json_encode($gamexa_getbalance_response));
            }

            $new_deposit_status_db = 'disetujui';
            $tanggal_konfirmasi_db = date("Y-m-d H:i:s");
            
            // Update tabel 'deposit'
            $stmt_update_deposit = $koneksi->prepare("UPDATE deposit SET status_deposit = ?, tanggal_deposit = ? WHERE transaction_id = ? AND id_anggota_deposit = ? AND (status_deposit = 'diproses' OR status_deposit IS NULL)");
            if (!$stmt_update_deposit) throw new Exception("DB Error (prepare update deposit): " . $koneksi->error);
            $stmt_update_deposit->bind_param("sssi", $new_deposit_status_db, $tanggal_konfirmasi_db, $trx_id_qris_provider, $id_anggota_db);
            if (!$stmt_update_deposit->execute()) throw new Exception("DB Error (execute update deposit): " . $stmt_update_deposit->error);
            $affected_rows_deposit = $stmt_update_deposit->affected_rows;
            $stmt_update_deposit->close();

            if ($affected_rows_deposit > 0) {
                log_qris_cb("DB: Status deposit QRIS TRX ID {$trx_id_qris_provider} diupdate ke '{$new_deposit_status_db}'.");

                $stmt_update_saldo_anggota = $koneksi->prepare("UPDATE anggota SET saldo_anggota = ? WHERE id_anggota = ?");
                if (!$stmt_update_saldo_anggota) throw new Exception("DB Error (prepare update saldo anggota): " . $koneksi->error);
                $stmt_update_saldo_anggota->bind_param("di", $saldo_baru_dari_gamexa, $id_anggota_db);
                if (!$stmt_update_saldo_anggota->execute()) throw new Exception("DB Error (execute update saldo anggota): " . $stmt_update_saldo_anggota->error);
                $stmt_update_saldo_anggota->close();
                log_qris_cb("DB: Saldo anggota {$username_pemain} diupdate ke {$saldo_baru_dari_gamexa} (dari GameXa).");
                
                // --- Tambahkan logika notifikasi Telegram di sini ---
                log_qris_cb("Mempersiapkan notifikasi Telegram untuk deposit sukses.");
                $depositDataForTelegram = [
                    'id_deposit' => $id_deposit_lokal,
                    'kode_deposit' => $kode_deposit_lokal,
                    'id_sigma' => $id_sigma,
                    'nama_pengguna_anggota_deposit' => $username_pemain,
                    'jumlah_deposit' => $amount_paid_qris,
                    'asal_deposit' => 'QRIS Payment (Callback)', // Asal yang lebih spesifik
                    'tujuan_deposit' => 'QRIS Payment',
                    'tanggal_deposit' => $tanggal_konfirmasi_db,
                    'status_deposit' => $new_deposit_status_db,
                ];
                
                if (function_exists('sendNewDepositNotificationToTelegram')) {
                    if (!sendNewDepositNotificationToTelegram($depositDataForTelegram, isset($alamat_admin) ? $alamat_admin : null)) {
                        log_qris_cb("Peringatan: Gagal mengirim notifikasi Telegram untuk deposit sukses ID: " . $id_deposit_lokal);
                    } else {
                        log_qris_cb("Notifikasi Telegram berhasil dikirim untuk deposit sukses ID: " . $id_deposit_lokal);
                    }
                } else {
                    log_qris_cb("Peringatan Kritis: Fungsi sendNewDepositNotificationToTelegram tidak ditemukan.");
                    error_log("CRITICAL: functions_telegram.php or sendNewDepositNotificationToTelegram() not found in qris_callback.php");
                }
                // --- Akhir logika notifikasi Telegram ---

                // --- LOGIKA REFERRAL BONUS ---
                if (!empty($referer_username)) {
                    log_qris_cb("Referral found for {$username_pemain}: {$referer_username}. Calculating bonus.");
                    
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
                    log_qris_cb("Referral rate from DB/default: {$rate_refferal_bonus}.");
                    // --------------------------------------------------------

                    $bonus_referral_amount = $amount_paid_qris * $rate_refferal_bonus;
                    
                    if ($bonus_referral_amount > 0) {
                        $existing_referral_id = null;
                        $existing_bonus_amount = 0.00;

                        $stmt_check_referral = $koneksi->prepare("SELECT id, bonus FROM tb_refferal WHERE user_refferal = ? AND id_user = ?");
                        if ($stmt_check_referral) {
                            $stmt_check_referral->bind_param("ss", $referer_username, $username_pemain);
                            $stmt_check_referral->execute();
                            $result_check_referral = $stmt_check_referral->get_result();
                            if ($data_referral_exist = $result_check_referral->fetch_assoc()) {
                                $existing_referral_id = $data_referral_exist['id'];
                                $existing_bonus_amount = floatval($data_referral_exist['bonus']);
                            }
                            $stmt_check_referral->close();
                        } else {
                            log_qris_cb("DB Error (prepare check tb_refferal): " . $koneksi->error);
                            throw new Exception("DB Error (prepare check tb_refferal): " . $koneksi->error);
                        }

                        $new_total_bonus = $existing_bonus_amount + $bonus_referral_amount;
                        $keterangan_referral_updated = "Bonus dari deposit {$trx_id_qris_provider} (Rp " . number_format($amount_paid_qris, 0, ',', '.') . ")";

                        if ($existing_referral_id) {
                            $stmt_update_referral = $koneksi->prepare("UPDATE tb_refferal SET bonus = ?, keterangan = ?, tanggal = NOW() WHERE id = ?");
                            if (!$stmt_update_referral) {
                                log_qris_cb("DB Error (prepare update tb_refferal): " . $koneksi->error);
                                throw new Exception("DB Error (prepare update tb_refferal): " . $koneksi->error);
                            }
                            $stmt_update_referral->bind_param("dsi", $new_total_bonus, $keterangan_referral_updated, $existing_referral_id);
                            if (!$stmt_update_referral->execute()) {
                                log_qris_cb("DB Error (execute update tb_refferal): " . $stmt_update_referral->error);
                                throw new Exception("DB Error (execute update tb_refferal): " . $stmt_update_referral->error);
                            }
                            $stmt_update_referral->close();
                            log_qris_cb("Referral Bonus: Updated bonus for referer '{$referer_username}' from '{$username_pemain}'. New total bonus: {$new_total_bonus}. Keterangan: '{$keterangan_referral_updated}'.");
                        } else {
                            $stmt_insert_referral = $koneksi->prepare("INSERT INTO tb_refferal (user_refferal, keterangan, bonus, id_user, tanggal) VALUES (?, ?, ?, ?, NOW())");
                            if (!$stmt_insert_referral) {
                                log_qris_cb("DB Error (prepare insert tb_refferal): " . $koneksi->error);
                                throw new Exception("DB Error (prepare insert tb_refferal): " . $koneksi->error);
                            }
                            $stmt_insert_referral->bind_param("ssds", $referer_username, $keterangan_referral_updated, $bonus_referral_amount, $username_pemain);
                            if (!$stmt_insert_referral->execute()) {
                                log_qris_cb("DB Error (execute insert tb_refferal): " . $stmt_insert_referral->error);
                                throw new Exception("DB Error (execute insert tb_refferal): " . $stmt_insert_referral->error);
                            }
                            $stmt_insert_referral->close();
                            log_qris_cb("Referral Bonus: Inserted new entry for referer '{$referer_username}' from '{$username_pemain}'. Initial bonus: {$bonus_referral_amount}. Keterangan: '{$keterangan_referral_updated}'.");
                        }
                    } else {
                        log_qris_cb("Referral Bonus: Bonus amount is zero, skipping insert/update for referer '{$referer_username}'. Amount paid: {$amount_paid_qris}");
                    }
                } else {
                    log_qris_cb("No referer found for user '{$username_pemain}'. Skipping referral bonus.");
                }
                // --- AKHIR LOGIKA REFERRAL BONUS ---

                $koneksi->commit();
                log_qris_cb("DB: Transaksi COMMIT sukses untuk QRIS TRX ID: {$trx_id_qris_provider}. Termasuk bonus referral.");
            } else {
                log_qris_cb("DB Warning: Tidak ada baris deposit diupdate untuk QRIS TRX ID {$trx_id_qris_provider}. Mungkin sudah diproses atau record tidak memenuhi kriteria update. Proses GameXa tetap berhasil.");
                $koneksi->commit(); // Tetap commit jika GameXa berhasil, meski update deposit lokal tidak terjadi.
            }
        } catch (Exception $e) {
            if (isset($koneksi) && $koneksi->ping()) { $koneksi->rollback(); }
            log_qris_cb("CRITICAL Exception QRIS SUCCESS processing. QRIS_TRX_ID {$trx_id_qris_provider}, User {$username_pemain}: " . $e->getMessage() . ". DB di-ROLLBACK.");
            error_log("CRITICAL Exception qris_callback.php: " . $e->getMessage() . ". QRIS_TRX_ID: {$trx_id_qris_provider}, User: {$username_pemain}");
            http_response_code(200); echo json_encode(['status' => true, 'message' => 'Callback QRIS diterima, error sinkronisasi dana game atau referral.']); exit;
        }
    } else {
         log_qris_cb("Deposit untuk QRIS TRX ID {$trx_id_qris_provider} tidak berstatus 'diproses' atau null ('{$status_deposit_lokal}'). Tidak diproses ulang.");
    }

} elseif ($payment_status_qris === 'FAILED' || $payment_status_qris === 'EXPIRED') {
    log_qris_cb("Pembayaran QRIS {$payment_status_qris} untuk TRX ID: {$trx_id_qris_provider}, User: {$username_pemain}.");
    if (isset($koneksi) && $koneksi instanceof mysqli && !$koneksi->connect_error) {
        $stmt_update_failed = $koneksi->prepare("UPDATE deposit SET status_deposit = ? WHERE transaction_id = ? AND id_anggota_deposit = (SELECT id_anggota FROM anggota WHERE nama_pengguna_anggota = ?) AND status_deposit = 'diproses'");
        if($stmt_update_failed) {
            $status_db_failed = ($payment_status_qris === 'FAILED' ? 'dibatalkan' : 'dibatalkan');
            $stmt_update_failed->bind_param("sss", $status_db_failed, $trx_id_qris_provider, $username_pemain);
            $stmt_update_failed->execute();
            if ($stmt_update_failed->affected_rows > 0) {
                log_qris_cb("DB: Status deposit QRIS TRX ID {$trx_id_qris_provider} diupdate ke '{$status_db_failed}'.");
            }
            $stmt_update_failed->close();
        } else { log_qris_cb("DB Error (prepare update deposit FAILED/EXPIRED): " . $koneksi->error); }
    }
} else {
    log_qris_cb("Status callback QRIS tidak dikenal: '{$payment_status_qris}'. TRX ID: {$trx_id_qris_provider}.");
}

if (isset($koneksi) && $koneksi instanceof mysqli && $koneksi->thread_id) { $koneksi->close(); }
http_response_code(200); 
echo json_encode(['status' => true, 'message' => 'Callback QRIS diterima dan diproses.']);
exit;
?>