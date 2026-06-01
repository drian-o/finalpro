<?php
include_once 'koneksi.php'; // Menyediakan $koneksi, $alamat_website, $alamat_admin
require_once 'functions_telegram.php'; // Memuat fungsi notifikasi Telegram
session_start();

if (!isset($_SESSION['loggedin']) || !isset($_SESSION['id_anggota']) || !isset($_SESSION['nama_pengguna_anggota'])) {
    echo '<script>alert("Silakan login terlebih dahulu"); window.location.replace("' . $alamat_website . 'qris");</script>';
    exit();
}

$api_url = "https://phoenix-node.hokipay.online/api/v2/transactions/initiate";
$fallback_ip_url = "https://103.28.55.123/api/v2/transactions/initiate";
$x_api_key = "HP-C050F596-B87B7B0F"; // Harap pertimbangkan keamanan penyimpanan key ini
$x_secret_key = "10e282b9fc88a8b66f180a7a558fa2ff65a5863c40f499bacd6f4e0299dc4394"; // Harap pertimbangkan keamanan penyimpanan key ini
$callback_url = rtrim($alamat_website, '/') . "/callback.php"; // Menggunakan $alamat_website
$qr_folder = "qr/"; 
$timeout_duration = 600; 

if (!file_exists($qr_folder)) {
    if (!mkdir($qr_folder, 0755, true)) {
        // Gagal membuat folder, mungkin ada masalah izin
        echo '<script>alert("Error: Gagal membuat direktori QR. Hubungi administrator."); window.location.replace("' . $alamat_website . 'qris");</script>';
        exit();
    }
}
if (!is_writable($qr_folder)) {
     // Folder tidak dapat ditulis
    error_log("Peringatan: Folder QR ('" . $qr_folder . "') tidak dapat ditulis oleh server.");
    // Anda bisa memilih untuk menghentikan skrip atau hanya mencatat peringatan
}


$jumlah_deposit_input = isset($_POST['jumlah_deposit']) ? filter_var($_POST['jumlah_deposit'], FILTER_VALIDATE_INT) : null;
$transaction_id = null;
$qris_image_url_from_api = null; // Mengganti nama variabel agar lebih jelas
$qris_file_path = null; // Mengganti nama variabel agar lebih jelas
$waktu_mulai = null;
$existing_deposit_data = null; // Untuk menyimpan data deposit yang sudah ada

// Cek apakah ada deposit yang sedang diproses untuk user ini
$stmt_check = $koneksi->prepare("SELECT id_deposit, transaction_id, jumlah_deposit, kode_deposit, nama_pengguna_anggota_deposit, asal_deposit, tujuan_deposit, tanggal_deposit, status_deposit, qris_file, waktu_mulai FROM deposit WHERE id_anggota_deposit = ? AND status_deposit = 'diproses' ORDER BY tanggal_deposit DESC LIMIT 1");
if (!$stmt_check) {
    error_log("Gagal mempersiapkan statement cek deposit: " . $koneksi->error);
    echo '<script>alert("Terjadi kesalahan sistem. Silakan coba lagi nanti. [Code: S1]"); window.location.replace("' . $alamat_website . 'qris");</script>';
    exit();
}
$stmt_check->bind_param('i', $_SESSION['id_anggota']);
$stmt_check->execute();
$result_check = $stmt_check->get_result();


if ($result_check->num_rows > 0) {
    $existing_deposit_data = $result_check->fetch_assoc();
    $transaction_id = $existing_deposit_data['transaction_id'];
    $jumlah_deposit = (int)$existing_deposit_data['jumlah_deposit']; // Pastikan ini integer
    $qris_file_path = $existing_deposit_data['qris_file'];
    $waktu_mulai = $existing_deposit_data['waktu_mulai'];
    // Gunakan qris_file jika ada, jika tidak, bentuk URL dari API (sebagai fallback jika penyimpanan gagal)
    $qris_display_image = $qris_file_path ? $alamat_website . $qris_file_path : "https://cdn.hokipay.link/qris_image/$transaction_id.png";

} elseif ($jumlah_deposit_input && $jumlah_deposit_input >= 1000) { // Minimal deposit 1000 (sesuaikan jika perlu)
    $id_anggota = $_SESSION['id_anggota'];
    $nama_pengguna = $_SESSION['nama_pengguna_anggota'];
    $kode_deposit = "AUTO" . time() . rand(100,999); // Tambahkan random untuk keunikan
    $asal_deposit = "QRIS - " . $nama_pengguna; // Lebih spesifik
    $tujuan_deposit = "SALDO UTAMA"; // Atau sesuai tujuan sebenarnya
    $bonus_deposit = "BONUS NEW MEMBER 100% | TANPA TO"; // Pastikan ini sesuai
    $waktu_mulai = date('Y-m-d H:i:s');
    $jumlah_deposit = $jumlah_deposit_input; // Gunakan jumlah dari input

    $body = [
        'amount' => $jumlah_deposit,
        'callback_url' => $callback_url,
        'timeout' => 60, // Timeout di sisi Hokipay (detik)
        'custom_meta' => [
            'id_anggota' => $id_anggota,
            'kode_deposit' => $kode_deposit,
            'nama_pengguna' => $nama_pengguna
        ]
    ];

    function callHokipayApi($url, $fallback_url, $body, $x_api_key, $x_secret_key, $max_retries = 2) { // Kurangi retries
        $attempt = 0;
        $use_fallback = false;
        $last_error = '';
        $last_http_code = null;

        while ($attempt < $max_retries) {
            $attempt++;
            $current_url = $use_fallback ? $fallback_url : $url;
            $ch = curl_init($current_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "x-api-key: $x_api_key",
                "x-secret-key: $x_secret_key",
                "Content-Type: application/json",
                "Host: phoenix-node.hokipay.online" // Penting jika ada pengecekan Host header
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
            curl_setopt($ch, CURLOPT_TIMEOUT, 20); // Kurangi timeout cURL
            // Untuk debugging SSL jika ada masalah (biasanya tidak perlu di server produksi yang benar)
            // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);


            $response_content = curl_exec($ch);
            $last_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_error_msg = curl_error($ch);
            curl_close($ch);

            if ($response_content !== false && $last_http_code === 200) {
                return ['response' => $response_content, 'http_code' => $last_http_code, 'error' => ''];
            }
            
            $last_error = $curl_error_msg ?: "HTTP Code: $last_http_code";
            error_log("Hokipay API call attempt $attempt to $current_url failed. Error: $last_error. HTTP Code: $last_http_code. Response: $response_content");


            if (stripos($curl_error_msg, 'Could not resolve host') !== false && !$use_fallback) {
                $use_fallback = true; // Coba fallback jika gagal resolve host
                error_log("Hokipay API: Could not resolve host, trying fallback URL.");
            } elseif ($last_http_code == 0 || $last_http_code >= 500) { // Retry on timeout or server errors
                 // Tetap coba URL yang sama atau fallback jika belum
            } else {
                // Untuk error lain (misal 4xx), jangan retry
                break;
            }
            if ($attempt < $max_retries) sleep(1); // Tunggu sebelum retry
        }
        return ['response' => false, 'http_code' => $last_http_code, 'error' => $last_error];
    }

    $api_result = callHokipayApi($api_url, $fallback_ip_url, $body, $x_api_key, $x_secret_key);

    if ($api_result['response'] === false) {
        $error_message = $api_result['error'] ?: "Gagal terhubung ke API Pembayaran (HTTP {$api_result['http_code']}) setelah beberapa percobaan.";
        error_log("Gagal final callHokipayApi: " . $error_message);
        echo '<script>alert("Gagal membuat QRIS: ' . htmlspecialchars($error_message) . ' Silakan coba beberapa saat lagi atau hubungi CS. [Code: HPA1]"); window.location.replace("' . $alamat_website . 'qris");</script>';
        exit();
    }

    $response_data_api = json_decode($api_result['response'], true);
    if (!is_array($response_data_api) || !isset($response_data_api['status']) || $response_data_api['status'] !== 'success') {
        $error_message = $response_data_api['message'] ?? 'Respon API Pembayaran tidak valid atau gagal.';
        error_log("Respon API Hokipay tidak sukses: " . ($api_result['response'] ?? 'No response content'));
        echo '<script>alert("Gagal membuat QRIS: ' . htmlspecialchars($error_message) . ' [Code: HPA2]"); window.location.replace("' . $alamat_website . 'qris");</script>';
        exit();
    }

    $transaction_id = $response_data_api['data']['transaction_id'] ?? null;
    $qris_image_url_from_api = $response_data_api['data']['qris_image'] ?? null;

    if (!$transaction_id || !$qris_image_url_from_api) {
        error_log("Data transaksi tidak lengkap dari API Hokipay. TransID: $transaction_id, QRIS_URL: $qris_image_url_from_api");
        echo '<script>alert("Gagal membuat QRIS: Data transaksi dari API tidak lengkap. [Code: HPA3]"); window.location.replace("' . $alamat_website . 'qris");</script>';
        exit();
    }

    // Simpan gambar QRIS ke folder lokal
    $random_filename = uniqid('qris_hokipay_') . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $transaction_id) . '.png';
    $qris_file_path = $qr_folder . $random_filename;
    
    // Coba ambil konten gambar
    $qris_content = @file_get_contents($qris_image_url_from_api, false, stream_context_create(["ssl"=>["verify_peer"=>false, "verify_peer_name"=>false]])); // Tambahkan konteks SSL jika perlu
    if ($qris_content === false) {
        error_log("Gagal mengambil konten gambar QRIS dari URL: " . $qris_image_url_from_api . ". Error: " . (error_get_last()['message'] ?? 'Unknown error'));
        // Jangan hentikan proses, tapi catat bahwa penyimpanan lokal gagal.
        // QRIS akan ditampilkan dari URL API sebagai fallback.
        $qris_file_path = null; // Set path ke null jika gagal simpan
    } else {
        if (!file_put_contents($qris_file_path, $qris_content)) {
            error_log("Gagal menyimpan gambar QRIS ke '" . $qris_file_path . "'. Periksa izin folder.");
            $qris_file_path = null; // Set path ke null jika gagal simpan
        }
    }
    
    $qris_display_image = $qris_file_path ? $alamat_website . $qris_file_path : $qris_image_url_from_api;


    // Data untuk notifikasi Telegram
    $depositDataForTelegram = [
        'id_anggota_deposit' => $id_anggota,
        'kode_deposit' => $kode_deposit,
        'nama_pengguna_anggota_deposit' => $nama_pengguna,
        'asal_deposit' => $asal_deposit,
        'tujuan_deposit' => $tujuan_deposit,
        'bonus_deposit' => $bonus_deposit,
        'jumlah_deposit' => $jumlah_deposit,
        'tanggal_deposit' => $waktu_mulai, // Gunakan waktu_mulai sebagai tanggal awal
        'transaction_id' => $transaction_id,
        'status_deposit' => 'diproses',
        'qris_file' => $qris_file_path // qris_file yang disimpan lokal
    ];

    try {
        $koneksi->begin_transaction();
        $stmt_insert = $koneksi->prepare("INSERT INTO deposit (id_anggota_deposit, kode_deposit, nama_pengguna_anggota_deposit, asal_deposit, tujuan_deposit, bonus_deposit, jumlah_deposit, tanggal_deposit, transaction_id, status_deposit, waktu_mulai, qris_file) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'diproses', ?, ?)");
        if (!$stmt_insert) {
            throw new Exception("Gagal mempersiapkan statement insert: " . $koneksi->error);
        }
        $stmt_insert->bind_param('isssssdssss', $id_anggota, $kode_deposit, $nama_pengguna, $asal_deposit, $tujuan_deposit, $bonus_deposit, $jumlah_deposit, $waktu_mulai, $transaction_id, $waktu_mulai, $qris_file_path);
        
        if (!$stmt_insert->execute()) {
            throw new Exception("Gagal mengeksekusi statement insert: " . $stmt_insert->error);
        }
        $new_deposit_id = $stmt_insert->insert_id; // Dapatkan ID deposit baru
        $koneksi->commit();
        $stmt_insert->close();

        // Kirim notifikasi Telegram setelah commit berhasil
        $depositDataForTelegram['id_deposit'] = $new_deposit_id; // Tambahkan ID deposit yang sebenarnya
        // $alamat_admin diambil dari koneksi.php
        if (!sendNewDepositNotificationToTelegram($depositDataForTelegram, $alamat_admin)) {
            error_log("Gagal mengirim notifikasi Telegram untuk deposit ID: " . $new_deposit_id . " (auto_deposit)");
            // Lanjutkan proses meskipun notifikasi gagal, tapi catat errornya
        }

    } catch (Exception $e) {
        $koneksi->rollback();
        if ($qris_file_path && file_exists($qris_file_path)) { // Hapus file jika transaksi DB gagal
            unlink($qris_file_path); 
        }
        error_log("Error database saat insert deposit (auto_deposit): " . $e->getMessage());
        echo '<script>alert("Error sistem saat menyimpan deposit: ' . htmlspecialchars($e->getMessage()) . ' Silakan coba lagi. [Code: DB1]"); window.location.replace("' . $alamat_website . 'qris");</script>';
        exit();
    }
    // $qris_display_image sudah di-set sebelumnya
} else if (!$existing_deposit_data) { // Jika tidak ada deposit diproses DAN input jumlah tidak valid
    echo '<script>alert("Nominal deposit tidak valid. Minimum deposit adalah IDR 1.000."); window.location.replace("' . $alamat_website . 'qris");</script>';
    exit();
}
$stmt_check->close();


// Hitung sisa waktu untuk countdown
$sisa_waktu = $timeout_duration; // Default jika deposit baru
if ($waktu_mulai) { // Jika ada deposit yang sedang diproses atau baru dibuat
    $waktu_sekarang_ts = time();
    $waktu_mulai_ts = strtotime($waktu_mulai);
    if ($waktu_mulai_ts !== false) {
        $selisih = $waktu_sekarang_ts - $waktu_mulai_ts;
        if ($selisih < $timeout_duration) {
            $sisa_waktu = $timeout_duration - $selisih;
        } else {
            $sisa_waktu = 0; // Waktu habis
        }
    } else {
        $sisa_waktu = 0; // Waktu mulai tidak valid
         error_log("Format waktu_mulai tidak valid: " . $waktu_mulai);
    }
}

if ($sisa_waktu <= 0 && $transaction_id) { // Hanya redirect jika ada transaksi aktif yang waktunya habis
    // Di sini Anda mungkin ingin mengupdate status deposit menjadi 'dibatalkan' jika waktunya habis
    // Namun, callback dari Hokipay yang seharusnya menangani ini.
    // Untuk UI, kita bisa langsung redirect.
    error_log("Waktu pembayaran habis untuk TransID: $transaction_id, UserID: {$_SESSION['id_anggota']}. Sisa waktu: $sisa_waktu");
    echo '<script>alert("Waktu pembayaran untuk deposit sebelumnya telah habis. Silakan buat permintaan deposit baru jika belum terbayar."); window.location.replace("' . $alamat_website . 'qris");</script>';
    exit();
}

// $koneksi->close(); // Jangan tutup koneksi di sini jika halaman HTML di bawah masih membutuhkannya (meskipun di contoh ini tidak)
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Deposit QRIS - SigmaBet.pro</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        /* CSS Anda tetap sama */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            max-width: 450px;
            width: 100%;
            padding: 30px;
            text-align: center;
        }

        h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 24px;
            font-weight: 600;
        }

        .qris-image {
            max-width: 300px;
            height: auto;
            margin: 20px auto;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 10px;
            background: #f9f9f9;
        }

        .info {
            margin: 15px 0;
            font-size: 16px;
            color: #555;
        }

        .info span {
            font-weight: 500;
            color: #333;
        }

        .countdown {
            font-size: 18px;
            font-weight: 600;
            color: #d32f2f; /* Merah untuk countdown */
            margin: 15px 0;
        }
         .countdown.almost-expired {
            color: #ffA500; /* Oranye jika hampir habis */
        }
        .countdown.expired {
            color: #757575; /* Abu-abu jika sudah habis */
            text-decoration: line-through;
        }

        .status { /* Tidak terpakai di HTML, tapi jaga jika ada */
            font-size: 16px;
            font-weight: 500;
            color: #0288d1;
            margin: 10px 0;
        }
        #deposit-status { /* Status yang diupdate JS */
             font-weight: bold;
        }
        #deposit-status.pending { color: #0288d1; } /* Biru untuk menunggu */
        #deposit-status.success { color: #2e7d32; } /* Hijau untuk sukses */
        #deposit_status.failed { color: #d32f2f; } /* Merah untuk gagal/batal */


        .redirecting {
            display: none;
            color: #2e7d32;
            font-weight: 500;
            margin-top: 20px;
            font-size: 16px;
        }

        .button-group {
            margin-top: 20px;
        }

        .button {
            display: inline-block;
            padding: 12px 24px;
            background: #0288d1; /* Biru */
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            transition: background 0.3s ease;
            margin: 5px;
            border: none;
            cursor: pointer;
        }

        .button:hover {
            background: #0277bd;
        }

        .download-button {
            background: #4caf50; /* Hijau */
        }

        .download-button:hover {
            background: #45a049;
        }
         .cancel-button {
            background: #d32f2f; /* Merah */
        }
        .cancel-button:hover {
            background: #c62828;
        }


        @media (max-width: 480px) {
            .container {
                padding: 20px;
            }
            h2 {
                font-size: 20px;
            }
            .qris-image {
                max-width: 250px;
            }
            .info, .status, .countdown { /* .status tidak terpakai langsung di HTML */
                font-size: 14px;
            }
            .button {
                padding: 10px 20px;
                font-size: 14px;
                width: calc(50% - 10px); /* Buat tombol responsif */
            }
            .button-group {
                display: flex;
                flex-wrap: wrap;
                justify-content: space-between;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($transaction_id && $qris_display_image): ?>
            <h2>Scan QRIS untuk Deposit</h2>
            <img src="<?php echo htmlspecialchars($qris_display_image); ?>" alt="QRIS SigmaBet.pro" class="qris-image" id="qris-image">
            <div class="info">Nominal: <span>IDR <?php echo number_format($jumlah_deposit, 0, ',', '.'); ?></span></div>
            <div class="info">Status: <span id="deposit-status" class="pending">Menunggu pembayaran...</span></div>
            <div class="info">Kode Deposit: <span><?php echo htmlspecialchars($existing_deposit_data['kode_deposit'] ?? $kode_deposit ?? 'N/A'); ?></span></div>
            <div class="info">Waktu Pembayaran: <span class="countdown" id="countdown">--:--</span></div>
            
            <div id="redirecting" class="redirecting">Pembayaran berhasil diterima! Anda akan dialihkan...</div>

            <div class="button-group">
                <a href="<?php echo $alamat_website . 'qris'; ?>" class="button">Deposit Lain</a>
                <a href="<?php echo htmlspecialchars($qris_display_image); ?>" download="qris_sigmabet_<?php echo htmlspecialchars($transaction_id); ?>.png" class="button download-button">Download QRIS</a>
            </div>
             <!-- 
            <form action="<?php echo $alamat_website . 'cancel_deposit.php'; ?>" method="POST" style="margin-top:10px;">
                <input type="hidden" name="transaction_id" value="<?php echo htmlspecialchars($transaction_id); ?>">
                <input type="hidden" name="kode_deposit" value="<?php echo htmlspecialchars($existing_deposit_data['kode_deposit'] ?? $kode_deposit ?? ''); ?>">
                <button type="submit" class="button cancel-button" onclick="return confirm('Anda yakin ingin membatalkan deposit ini?');">Batalkan Deposit</button>
            </form>
            -->

        <?php else: ?>
            <h2>Informasi Deposit Tidak Ditemukan</h2>
            <p>Tidak ada transaksi deposit yang aktif atau terjadi kesalahan saat memuat data.</p>
            <div class="button-group">
                <a href="<?php echo $alamat_website . 'qris'; ?>" class="button">Kembali ke Halaman Deposit</a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        const transactionId = "<?php echo htmlspecialchars($transaction_id ?? ''); ?>";
        const alamatWebsite = "<?php echo rtrim($alamat_website, '/'); ?>"; // Pastikan tidak ada trailing slash ganda
        let initialCountdown = <?php echo json_encode($sisa_waktu); ?>;
        const countdownEl = document.getElementById('countdown');
        const statusEl = document.getElementById('deposit-status');
        const redirectingEl = document.getElementById('redirecting');
        let countdownInterval;

        function formatTime(seconds) {
            if (isNaN(seconds) || seconds < 0) return "00:00";
            const minutes = Math.floor(seconds / 60);
            const remainingSeconds = seconds % 60;
            return `${minutes.toString().padStart(2, '0')}:${remainingSeconds.toString().padStart(2, '0')}`;
        }

        function updateCountdownDisplay() {
            countdownEl.textContent = formatTime(initialCountdown);
            if (initialCountdown <= 0) {
                countdownEl.classList.add('expired');
                countdownEl.classList.remove('almost-expired');
                if (statusEl.textContent.toLowerCase().includes('menunggu')) { // Hanya jika masih menunggu
                   // statusEl.textContent = "Waktu pembayaran habis.";
                   // statusEl.className = 'failed'; // Ganti kelas jika perlu
                }
                clearInterval(countdownInterval);
            } else if (initialCountdown <= 60) { // Kurang dari 1 menit
                countdownEl.classList.add('almost-expired');
            } else {
                countdownEl.classList.remove('almost-expired');
                countdownEl.classList.remove('expired');
            }
        }
        
        function startCountdown() {
            if (initialCountdown <=0) { // Jika sudah habis dari awal
                updateCountdownDisplay();
                return;
            }
            updateCountdownDisplay(); // Tampilkan pertama kali
            countdownInterval = setInterval(() => {
                initialCountdown--;
                updateCountdownDisplay();
                if (initialCountdown <= 0) {
                    clearInterval(countdownInterval);
                     // Tidak otomatis redirect, biarkan checkDepositStatus yang menangani status akhir
                     // atau tampilkan pesan bahwa waktu habis dan user harus buat ulang.
                }
            }, 1000);
        }

        function checkDepositStatus() {
            if (!transactionId) return;

            fetch(alamatWebsite + '/check_deposit_status.php', { // Pastikan path benar
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ transaction_id: transactionId })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.statusText);
                }
                return response.json();
            })
            .then(data => {
                if (data && data.message) { // Cek apakah data.message ada
                    statusEl.textContent = data.message.charAt(0).toUpperCase() + data.message.slice(1); // Kapitalisasi
                    if (data.status_deposit === 'disetujui' || data.message.toLowerCase() === 'disetujui') {
                        statusEl.className = 'success';
                        redirectingEl.style.display = 'block';
                        clearInterval(countdownInterval); // Hentikan countdown
                        countdownEl.textContent = "Selesai";
                        setTimeout(() => {
                            window.location.href = alamatWebsite + '/home'; // Pastikan path benar
                        }, 3000);
                    } else if (data.status_deposit === 'dibatalkan' || data.status_deposit === 'gagal' || data.message.toLowerCase().includes('gagal') || data.message.toLowerCase().includes('dibatalkan')) {
                        statusEl.className = 'failed';
                         clearInterval(countdownInterval);
                         countdownEl.textContent = "Kadaluarsa";
                    } else { // diproses atau status lain
                        statusEl.className = 'pending';
                    }
                } else {
                     // Biarkan status "Menunggu pembayaran..." jika tidak ada update signifikan
                     // atau jika respons tidak sesuai format
                     console.warn('Respon status deposit tidak sesuai format atau tidak ada pesan:', data);
                }
            })
            .catch(error => {
                console.error('Error saat memeriksa status deposit:', error);
                // Jangan ubah status jika ada error koneksi, biarkan user tahu masih menunggu
                // statusEl.textContent = 'Gagal memeriksa status';
            });
        }

        if (transactionId) {
            // Panggil checkDepositStatus beberapa kali di awal untuk update cepat
            checkDepositStatus(); 
            const statusCheckInterval = setInterval(() => {
                if (statusEl.className === 'success' || statusEl.className === 'failed' || initialCountdown <= 0) {
                    clearInterval(statusCheckInterval); // Hentikan jika sudah final atau waktu habis
                    return;
                }
                checkDepositStatus();
            }, 7000); // Cek setiap 7 detik

            if (initialCountdown > 0) {
                 startCountdown();
            } else {
                updateCountdownDisplay(); // Tampilkan "00:00" atau "Kadaluarsa" jika sudah habis
                if (statusEl.className === 'pending') { // Jika masih pending dan waktu habis
                    statusEl.textContent = "Waktu pembayaran habis.";
                    // Anda bisa memilih untuk tidak mengubah kelas agar tidak merah,
                    // atau biarkan saja 'pending' karena callback mungkin masih bisa masuk.
                }
            }
        } else {
            // Tidak ada transaction_id, mungkin halaman diakses langsung atau error sebelumnya
            countdownEl.textContent = "--:--";
            statusEl.textContent = "Data transaksi tidak tersedia.";
        }
    </script>
</body>
</html>
