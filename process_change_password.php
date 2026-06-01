<?php
session_start();

// Sertakan file koneksi dan fungsi bantuan redirect (jika ada)
require_once __DIR__ . '/koneksi.php';

// Fungsi untuk redirect dengan flash message (jika belum ada di file terpusat)
if (!function_exists('redirect_with_flash_message')) {
    function redirect_with_flash_message($type, $message, $location) {
        global $alamat_website;
        $_SESSION['flash_message'] = ['type' => $type, 'message' => $message];
        header("Location: " . rtrim($alamat_website, '/') . '/' . ltrim($location, '/'));
        exit;
    }
}

// Keamanan: Hanya proses jika metodenya POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405); // Method Not Allowed
    exit('Metode tidak diizinkan.');
}

// Keamanan: Pastikan pengguna sudah login untuk bisa mengganti password
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    redirect_with_flash_message('error', 'Anda harus login untuk mengganti password.', 'auth-login.php');
}

// 1. Ambil dan validasi input
$id_anggota = $_SESSION['id_anggota'];
$old_password = trim($_POST['old_password'] ?? '');
$new_password = trim($_POST['new_password'] ?? '');
$confirm_new_password = trim($_POST['confirm_new_password'] ?? '');

if (empty($old_password) || empty($new_password) || empty($confirm_new_password)) {
    redirect_with_flash_message('error', 'Semua kolom password harus diisi.', 'change-password.php');
}

if (strlen($new_password) < 6 || strlen($new_password) > 14) {
    redirect_with_flash_message('error', 'Password baru harus terdiri dari 6 hingga 14 karakter.', 'change-password.php');
}

if ($new_password !== $confirm_new_password) {
    redirect_with_flash_message('error', 'Password baru dan konfirmasi tidak cocok.', 'change-password.php');
}

try {
    // 2. Verifikasi password lama
    $stmt = $koneksi->prepare("SELECT kata_sandi_anggota FROM anggota WHERE id_anggota = ?");
    $stmt->bind_param("i", $id_anggota);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user || !password_verify($old_password, $user['kata_sandi_anggota'])) {
        redirect_with_flash_message('error', 'Password lama yang Anda masukkan salah.', 'change-password.php');
    }

    if (password_verify($new_password, $user['kata_sandi_anggota'])) {
        redirect_with_flash_message('error', 'Password baru tidak boleh sama dengan password lama.', 'change-password.php');
    }

    // 3. Jika password lama benar, hash dan update password baru
    $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    $stmt_update = $koneksi->prepare("UPDATE anggota SET kata_sandi_anggota = ? WHERE id_anggota = ?");
    $stmt_update->bind_param("si", $new_hashed_password, $id_anggota);
    
    if ($stmt_update->execute()) {
        // Sukses
        redirect_with_flash_message('success', 'Password Anda telah berhasil diperbarui.', 'change-password.php');
    } else {
        // Gagal update database
        throw new Exception("Gagal memperbarui password di database.");
    }
    $stmt_update->close();

} catch (Exception $e) {
    // Tangani error database atau lainnya
    error_log("Change Password Error: " . $e->getMessage());
    redirect_with_flash_message('error', 'Terjadi kesalahan pada sistem. Silakan coba lagi.', 'change-password.php');
} finally {
    if (isset($koneksi)) {
        $koneksi->close();
    }
}
