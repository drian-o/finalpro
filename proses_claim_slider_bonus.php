<?php
// proses_claim_slider_bonus.php
header('Content-Type: application/json');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/koneksi.php';
include_once __DIR__ . '/classes/class.exa.php'; // Include GameXaAPI

$response = ['success' => false, 'message' => 'Permintaan tidak valid.'];
$logFileDir = __DIR__ . '/logs/';
$logFilePath = $logFileDir . 'proses_claim_slider_bonus.log';

if (!is_dir($logFileDir)) {
    @mkdir($logFileDir, 0775, true);
}

function log_claim_slider_proc($message) {
    global $logFilePath;
    $timestamp = date("Y-m-d H:i:s");
    file_put_contents($logFilePath, "[{$timestamp}] " . $message . PHP_EOL, FILE_APPEND | LOCK_EX);
}

// Validasi Sesi Pengguna
// Sesuaikan validasi untuk menggunakan id_sigma
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['id_anggota']) || !isset($_SESSION['nama_pengguna_anggota'])) {
    $response['message'] = 'Sesi tidak valid. Silakan login kembali.';
    log_claim_slider_proc("Akses ditolak: Sesi pengguna tidak ditemukan. IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'N/A'));
    echo json_encode($response);
    exit();
}
$id_anggota_login = $_SESSION['id_anggota'];
$username_login = $_SESSION['nama_pengguna_anggota'];
// Kita akan mendapatkan id_sigma dari database, bukan dari sesi langsung, untuk memastikan keakuratannya.

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Metode request tidak valid.';
    log_claim_slider_proc("Metode request bukan POST. Diterima: " . $_SERVER['REQUEST_METHOD']);
    echo json_encode($response);
    exit();
}

$jumlah_bonus_akan_diklaim = isset($_POST['jumlah_bonus_diklaim']) ? floatval($_POST['jumlah_bonus_diklaim']) : 0;
$keterangan_input = isset($_POST['keterangan']) ? trim($_POST['keterangan']) : 'Klaim bonus balance dari slider oleh ' . $username_login;
$tanggal_claim = date('Y-m-d H:i:s');
$reference_id = 'BONUSCLAIM_' . $id_anggota_login . '_' . time() . '_' . uniqid(); // Unique reference ID for EXA deposit

if ($jumlah_bonus_akan_diklaim <= 0) {
    $response['message'] = 'Jumlah bonus yang akan diklaim tidak valid atau nol.';
    log_claim_slider_proc("Input tidak valid: jumlah_bonus_diklaim={$jumlah_bonus_akan_diklaim} untuk user {$username_login}");
    echo json_encode($response);
    exit();
}

// Inisiasi GameXaAPI
$exaAPI = new GameXaAPI();

if (!isset($koneksi) || !($koneksi instanceof mysqli) || $koneksi->connect_error) {
    $response['message'] = "Kesalahan sistem: Koneksi database tidak tersedia.";
    log_claim_slider_proc("CRITICAL: Koneksi database tidak tersedia saat proses klaim bonus.");
    error_log("CRITICAL: Koneksi DB tidak tersedia di proses_claim_slider_bonus.php");
    echo json_encode($response);
    exit;
}

$koneksi->begin_transaction();
try {
    // STEP 1: Verifikasi bonus_balance pengguna di database dan kunci baris untuk update, serta ambil id_sigma
    $current_bonus_balance_db = 0;
    $id_sigma_anggota = null;
    $stmt_get_bonus = $koneksi->prepare("SELECT bonus_balance, id_sigma FROM anggota WHERE id_anggota = ? FOR UPDATE");
    if (!$stmt_get_bonus) throw new Exception("DB Error (prepare get bonus_balance and id_sigma): " . $koneksi->error);
    $stmt_get_bonus->bind_param("i", $id_anggota_login);
    $stmt_get_bonus->execute();
    $result_bonus = $stmt_get_bonus->get_result();
    if ($data_bonus = $result_bonus->fetch_assoc()) {
        $current_bonus_balance_db = (float)$data_bonus['bonus_balance'];
        $id_sigma_anggota = $data_bonus['id_sigma'];
    } else {
        throw new Exception("Anggota tidak ditemukan untuk klaim bonus.");
    }
    $stmt_get_bonus->close();

    if (empty($id_sigma_anggota)) {
        throw new Exception("ID Sigma tidak ditemukan untuk anggota ini. Tidak dapat memproses klaim.");
    }

    log_claim_slider_proc("User {$username_login} (ID: {$id_anggota_login}, ID Sigma: {$id_sigma_anggota}) mencoba klaim bonus balance sejumlah: {$jumlah_bonus_akan_diklaim}");

    if ($jumlah_bonus_akan_diklaim > $current_bonus_balance_db) {
        throw new Exception("Saldo bonus tidak mencukupi (Rp " . number_format($current_bonus_balance_db,0,',','.') . ") untuk diklaim sebesar Rp " . number_format($jumlah_bonus_akan_diklaim,0,',','.'));
    }
    log_claim_slider_proc("Verifikasi bonus_balance user {$username_login} sukses. DB Bonus Balance: {$current_bonus_balance_db}, Akan diklaim: {$jumlah_bonus_akan_diklaim}");

    // STEP 2: Lakukan deposit bonus ke API GameXa menggunakan id_sigma
    log_claim_slider_proc("Memanggil EXAAPI->depositToPlayer() untuk ID Sigma: {$id_sigma_anggota}, Amount: {$jumlah_bonus_akan_diklaim}, Reference ID: {$reference_id}.");
    $exa_deposit_response = $exaAPI->depositToPlayer($id_sigma_anggota, $jumlah_bonus_akan_diklaim, $reference_id);
    log_claim_slider_proc("Response EXAAPI Deposit: " . json_encode($exa_deposit_response));

    // Periksa respons GameXa API
    if (!($exa_deposit_response['success'] ?? false) || !isset($exa_deposit_response['data']['data']['balance_after'])) {
        throw new Exception("Gagal proses deposit bonus ke sistem GameXa API. Respon: " . json_encode($exa_deposit_response));
    }
    
    // STEP 3: Ambil saldo terbaru dari API GameXa setelah transaksi sukses
    // Menggunakan getPlayerBalance untuk mendapatkan saldo terkini
    log_claim_slider_proc("Memanggil EXAAPI->getPlayerBalance() untuk ID Sigma: {$id_sigma_anggota}.");
    $exa_getbalance_response = $exaAPI->getPlayerBalance($id_sigma_anggota);
    log_claim_slider_proc("Response EXAAPI GetBalance: " . json_encode($exa_getbalance_response));

    $saldo_baru_dari_exa = null;
    if (($exa_getbalance_response['success'] ?? false) && isset($exa_getbalance_response['data']['balance'])) {
        $saldo_baru_dari_exa = floatval($exa_getbalance_response['data']['balance']);
        log_claim_slider_proc("Saldo terbaru dari GameXa untuk ID Sigma {$id_sigma_anggota} adalah {$saldo_baru_dari_exa}.");
    } else {
        throw new Exception("Gagal ambil saldo terbaru dari GameXa setelah transaksi bonus sukses. Respon: " . json_encode($exa_getbalance_response));
    }

    // STEP 4: Update database lokal
    // Set bonus_balance menjadi 0 dan update saldo_anggota dengan saldo dari GameXa API
    log_claim_slider_proc("Mengupdate saldo_anggota di tabel lokal untuk id_anggota {$id_anggota_login} menjadi {$saldo_baru_dari_exa} dan bonus_balance menjadi 0.");
    $sql_update_balances = "UPDATE anggota SET bonus_balance = 0, saldo_anggota = ? WHERE id_anggota = ?";
    $stmt_update_balances = $koneksi->prepare($sql_update_balances);
    if (!$stmt_update_balances) throw new Exception("DB Error (prepare update balances): " . $koneksi->error);
    
    $stmt_update_balances->bind_param("di", $saldo_baru_dari_exa, $id_anggota_login);
    if (!$stmt_update_balances->execute()) throw new Exception("DB Error (execute update balances): " . $stmt_update_balances->error);
    log_claim_slider_proc("Update saldo_anggota dan set bonus_balance ke 0 untuk user ID {$id_anggota_login} berhasil. Affected rows: " . $stmt_update_balances->affected_rows);
    $stmt_update_balances->close();

    // Catat ke tabel claim_bonus
    log_claim_slider_proc("Mencatat klaim ke tabel claim_bonus. User: {$username_login}, Jumlah: {$jumlah_bonus_akan_diklaim}");
    // Menggunakan kolom yang ada di tabel claim_bonus
    $sql_insert_claim_log = "INSERT INTO claim_bonus 
                                (id_anggota_claim, nama_pengguna_claim, jumlah_bonus_diklaim, tanggal_claim, keterangan) 
                             VALUES (?, ?, ?, ?, ?)";
    $stmt_insert_claim_log = $koneksi->prepare($sql_insert_claim_log);
    if (!$stmt_insert_claim_log) throw new Exception("DB Error (prepare insert claim_bonus log): " . $koneksi->error);
    
    $stmt_insert_claim_log->bind_param("isdss", 
        $id_anggota_login, 
        $username_login, 
        $jumlah_bonus_akan_diklaim, 
        $tanggal_claim, 
        $keterangan_input
    );
    if (!$stmt_insert_claim_log->execute()) throw new Exception("DB Error (execute insert claim_bonus log): " . $stmt_insert_claim_log->error);
    $stmt_insert_claim_log->close();
    log_claim_slider_proc("Log klaim bonus dari slider berhasil dicatat ke tabel claim_bonus.");
    
    $koneksi->commit();
    $response['success'] = true;
    $response['message'] = 'Bonus sebesar Rp ' . number_format($jumlah_bonus_akan_diklaim, 0, ',', '.') . ' berhasil diklaim ke saldo utama.';
    $response['new_main_balance'] = $saldo_baru_dari_exa;
    $response['new_bonus_balance'] = 0;
    log_claim_slider_proc("Klaim bonus dari slider SUKSES dan COMMIT untuk user {$username_login}.");

    // Update session saldo anggota yang sedang login
    if (isset($_SESSION['id_anggota']) && $_SESSION['id_anggota'] == $id_anggota_login) {
        $_SESSION['saldo_anggota'] = $saldo_baru_dari_exa;
    }

} catch (Exception $e) {
    if (isset($koneksi) && $koneksi->ping()) { $koneksi->rollback(); }
    $response['message'] = 'Terjadi kesalahan: ' . $e->getMessage();
    log_claim_slider_proc("Exception saat proses klaim bonus slider untuk User ID: {$id_anggota_login}, Pesan: " . $e->getMessage() . ". Transaksi DB di-ROLLBACK.");
    error_log("Error proses_claim_slider_bonus.php: " . $e->getMessage() . " untuk User ID: {$id_anggota_login}");
}

if (isset($koneksi) && $koneksi instanceof mysqli && $koneksi->thread_id) {
    $koneksi->close();
}
echo json_encode($response);
exit();