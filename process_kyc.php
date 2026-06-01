<?php
session_start();
include 'koneksi.php';

// Cek apakah pengguna sudah login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo "<script>alert('Silakan login terlebih dahulu.'); window.location.href='login.php';</script>";
    exit;
}

// Ambil ID anggota dari session
$nama_anggota = $_SESSION['nama_pengguna_anggota'];

// Ambil data pengguna dari database
$stmt = $koneksi->prepare("SELECT email_anggota, telepon_anggota, kyc_status FROM anggota WHERE nama_pengguna_anggota = ?");
$stmt->bind_param("s", $nama_anggota);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows === 1) {
    $data = $result->fetch_assoc();
} else {
    echo "<script>alert('Pengguna tidak ditemukan.'); window.location.href='login.php';</script>";
    exit;
}

// Proses pengiriman form KYC
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Update status KYC
    $stmt = $koneksi->prepare("UPDATE anggota SET kyc_status = 2 WHERE nama_pengguna_anggota = ?");
    $stmt->bind_param("s", $nama_anggota);
    
    if ($stmt->execute()) {
        echo "<script>alert('KYC berhasil diverifikasi.'); window.location.href='referal.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui status KYC.'); window.location.href='kyc.php';</script>";
    }
}
?>