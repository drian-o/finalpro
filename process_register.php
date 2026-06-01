<?php
// process_register.php

// Mulai sesi di awal untuk bisa menyimpan data login jika pendaftaran berhasil.
session_start();

// Header untuk menandakan respons adalah JSON
header('Content-Type: application/json');

// --- DEPENDENSI & SETUP ---
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/classes/class.exa.php';
require_once __DIR__ . '/classes/class.nexusggr.php';
require_once __DIR__ . '/classes/connectAPI.php';
require_once __DIR__ . '/functions_telegram.php';

// --- FUNGSI HELPER (dimodifikasi untuk JSON response dan logging) ---
function send_json_response($success, $message, $redirect_url = null) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'redirect' => $redirect_url
    ]);
    exit;
}

function log_to_file($message) {
    $log_dir = __DIR__ . '/logs/';
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    $log_file = $log_dir . 'proses_register.log';
    $timestamp = date("Y-m-d H:i:s");
    file_put_contents($log_file, "[{$timestamp}] {$message}" . PHP_EOL, FILE_APPEND);
}

// --- LOGIKA UTAMA ---
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    send_json_response(false, 'Metode request tidak diizinkan.');
}

// 1. Ambil dan bersihkan data input
$username       = trim($_POST['nama_pengguna_anggota'] ?? '');
$password       = trim($_POST['kata_sandi_anggota'] ?? '');
$confirm_pass   = trim($_POST['konfirmasi_kata_sandi_anggota'] ?? '');
$bank_label     = trim($_POST['bank_anggota_key'] ?? '');
$nama_rekening  = trim($_POST['nama_rekening_anggota'] ?? '');
$nomor_rekening = trim($_POST['nomor_rekening_anggota'] ?? '');
$telepon        = trim($_POST['telepon_anggota'] ?? '');
$email          = trim($_POST['email_anggota'] ?? '');
$refferal_code  = trim($_POST['upline'] ?? null);

// 2. Validasi Input
if (empty($username) || empty($password) || empty($bank_label) || $bank_label == '-- Memilih --' || empty($nama_rekening) || empty($nomor_rekening) || empty($telepon)) {
    send_json_response(false, 'Mohon isi semua bidang wajib (Username, Password, Bank, Nama & Nomor Rekening, Telepon).');
}
if ($password !== $confirm_pass) {
    send_json_response(false, 'Password dan Konfirmasi Password tidak cocok.');
}
if (strlen($username) < 6 || strlen($username) > 14) {
    send_json_response(false, 'Username harus terdiri dari 6 hingga 14 karakter.');
}
if (strpos($username, ' ') !== false) {
    send_json_response(false, 'Username tidak boleh mengandung spasi.');
}
if (!preg_match('/^[a-zA-Z0-9]+$/', $username)) {
    send_json_response(false, 'Username hanya boleh mengandung huruf dan angka, tanpa spasi atau karakter khusus.');
}
if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    send_json_response(false, 'Format email tidak valid.');
}

// --- LOGIKA UTAMA DENGAN DUA API ---
try {
    log_to_file("Pendaftaran user {$username}...");

    $stmt = $koneksi->prepare("SELECT id_anggota, id_sigma, id_nexus FROM anggota WHERE nama_pengguna_anggota = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    $existing_member_id = null;
    $existing_id_sigma = null;
    $existing_id_nexus = null;

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($existing_member_id, $existing_id_sigma, $existing_id_nexus);
        $stmt->fetch();
        $is_new_user = false;
    } else {
        $stmt->close();
        $stmt = $koneksi->prepare("SELECT nama_pengguna_anggota FROM anggota WHERE telepon_anggota = ? OR nomor_rekening_anggota = ?");
        $stmt->bind_param("ss", $telepon, $nomor_rekening);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            send_json_response(false, 'Telepon atau Nomor Rekening sudah terdaftar. Silakan gunakan data lain.');
        }
        $is_new_user = true;
    }
    $stmt->close();

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $player_email = !empty($email) ? $email : $username . "@example.com";
    $player_full_name = !empty($nama_rekening) ? $nama_rekening : ucfirst($username);
    $player_phone = !empty($telepon) ? $telepon : "+62" . rand(100000000, 999999999);
    $player_currency = "IDR";
    
    $id_sigma = $existing_id_sigma;
    $id_nexus = $existing_id_nexus;
    $pendaftaran_berhasil = true;
    $pesan_error_api = [];
    
    // A. Panggil API GameXa (Exa) untuk membuat user (jika belum terdaftar)
    if (empty($id_sigma)) {
        try {
            $GameXaAPI = new GameXaAPI();
            $gamexa_response = $GameXaAPI->createPlayer(
                $username,
                $player_email,
                $password,
                $player_full_name,
                $player_phone,
                $player_currency
            );

            if (($gamexa_response['success'] ?? false) && isset($gamexa_response['data']['player']['id'])) {
                $id_sigma = $gamexa_response['data']['player']['id'];
                log_to_file("Exa: berhasil");
            } else {
                $api_error_message = $gamexa_response['data']['error'] ?? $gamexa_response['message'] ?? 'Unknown error from Exa.';
                $pesan_error_api[] = 'Pendaftaran gagal (API Exa): ' . $api_error_message;
                $pendaftaran_berhasil = false;
                log_to_file("Exa: gagal ({$api_error_message})");
            }
        } catch (Exception $e) {
            $pesan_error_api[] = 'Pendaftaran gagal (API Exa): ' . $e->getMessage();
            $pendaftaran_berhasil = false;
            log_to_file("Exa: gagal ({$e->getMessage()})");
        }
    } else {
        log_to_file("Exa: berhasil (data sudah ada)");
    }
    
    // B. Panggil API Nexus untuk membuat user (jika belum terdaftar)
    if (empty($id_nexus)) {
        try {
            if (!isset($user_agent) || !isset($signature)) {
                throw new Exception("Kredensial API Nexus tidak ditemukan.");
            }
            $NexusAPI = new API($user_agent, $signature);
            $nexus_response = $NexusAPI->user_create($username);
            
            if (isset($nexus_response['status']) && $nexus_response['status'] == 1 && isset($nexus_response['user_code'])) {
                // Di sini kita mengisi id_nexus dari respons API Nexus
                $id_nexus = $nexus_response['user_code'];
                log_to_file("Nexus: berhasil");
            } else {
                $api_error_message = $nexus_response['msg'] ?? 'Unknown error from Nexus.';
                $pesan_error_api[] = 'Pendaftaran gagal (API Nexus): ' . $api_error_message;
                $pendaftaran_berhasil = false;
                log_to_file("Nexus: gagal ({$api_error_message})");
            }
        } catch (Exception $e) {
            $pesan_error_api[] = 'Pendaftaran gagal (API Nexus): ' . $e->getMessage();
            $pendaftaran_berhasil = false;
            log_to_file("Nexus: gagal ({$e->getMessage()})");
        }
    } else {
        log_to_file("Nexus: berhasil (data sudah ada)");
    }

    // 4. Update atau Insert data anggota ke database lokal
    if ($pendaftaran_berhasil) {
        log_to_file("Keterangan: tidak ada error");
    } else {
        log_to_file("Keterangan: " . implode('; ', $pesan_error_api));
    }
    
    $koneksi->begin_transaction();

    $default_saldo = 0.00;
    $default_bonus = 0.00;
    $default_status_anggota = 'aktif';
    $default_status_game = 'Aktif';
    $default_kyc_status = 0;

    if ($is_new_user) {
        $stmt = $koneksi->prepare("INSERT INTO anggota (refferal, nama_pengguna_anggota, kata_sandi_anggota, email_anggota, telepon_anggota, bank_anggota, nama_rekening_anggota, nomor_rekening_anggota, saldo_anggota, bonus_balance, status_anggota, status_game, kyc_status, id_sigma, id_nexus) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->bind_param("ssssssssddssiis",
            $refferal_code,
            $username,
            $hashed_password,
            $player_email,
            $player_phone,
            $bank_label,
            $nama_rekening,
            $nomor_rekening,
            $default_saldo,
            $default_bonus,
            $default_status_anggota,
            $default_status_game,
            $default_kyc_status,
            $id_sigma,
            $id_nexus
        );

        if (!$stmt->execute()) {
            $koneksi->rollback();
            send_json_response(false, 'Gagal mendaftarkan anggota baru. Silakan coba lagi.');
        }
        $new_member_id = $koneksi->insert_id;
        $stmt->close();
    } else {
        $update_fields = [];
        $params_types = "";
        $params = [];
        
        if (empty($existing_id_sigma) && !empty($id_sigma)) {
            $update_fields[] = "id_sigma = ?";
            $params_types .= "i";
            $params[] = $id_sigma;
        }
        if (empty($existing_id_nexus) && !empty($id_nexus)) {
            $update_fields[] = "id_nexus = ?";
            $params_types .= "s";
            $params[] = $id_nexus;
        }

        if (!empty($update_fields)) {
            $update_query = "UPDATE anggota SET " . implode(', ', $update_fields) . " WHERE id_anggota = ?";
            $params_types .= "i";
            $params[] = $existing_member_id;

            $stmt = $koneksi->prepare($update_query);
            $stmt->bind_param($params_types, ...$params);

            if (!$stmt->execute()) {
                $koneksi->rollback();
                send_json_response(false, 'Gagal memperbarui data anggota. Silakan hubungi CS.');
            }
            $stmt->close();
        }
        $new_member_id = $existing_member_id;
    }
    
    // Commit transaksi
    $koneksi->commit();
    
    // Set Sesi Login
    $_SESSION['loggedin'] = true;
    $_SESSION['id_anggota'] = $new_member_id;
    $_SESSION['nama_pengguna_anggota'] = $username;
    $_SESSION['saldo_anggota'] = $default_saldo;

    // Kirim notifikasi Telegram
    if (function_exists('sendNewUserNotificationToTelegram')) {
        global $alamat_website;
        sendNewUserNotificationToTelegram([
            'username' => $username, 'email' => $player_email, 'telepon' => $player_phone,
            'bank' => $bank_label, 'nama_rekening' => $nama_rekening, 'nomor_rekening' => $nomor_rekening,
            'id_sigma' => $id_sigma, 'id_nexus' => $id_nexus
        ], $alamat_website);
    }

    global $alamat_website;
    $final_message = $pendaftaran_berhasil ? 'Pendaftaran berhasil!' : 'Pendaftaran berhasil sebagian, silakan coba lagi atau hubungi CS.';
    send_json_response(true, $final_message, $alamat_website . 'home');

} catch (Exception $e) {
    if (isset($koneksi) && $koneksi->ping() && !$koneksi->autocommit(true)) {
        $koneksi->rollback();
    }
    log_to_file("Keterangan: Terjadi kesalahan fatal: " . $e->getMessage());
    send_json_response(false, 'Terjadi kesalahan pada sistem: ' . $e->getMessage());
} finally {
    if (isset($koneksi)) {
        $koneksi->close();
    }
}
?>