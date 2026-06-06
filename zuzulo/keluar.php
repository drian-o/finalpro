<?php
// 1. Load koneksi terlebih dahulu (di dalamnya sudah ada konfigurasi variabel $alamat_admin)
include_once "../koneksi.php";

// 2. Cek status session agar tidak terjadi "Ignoring session_start()"
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 3. Bersihkan seluruh data session login admin
$_SESSION = array();
session_unset();
session_destroy();

// 4. Update data kode_admin di database
$perbarui_data_admin = mysqli_query($koneksi, "UPDATE admin SET kode_admin = NULL");

if ($perbarui_data_admin) {
    // 5. Redirect mulus tanpa terhalang output error
    header("Location: " . $alamat_admin);
    exit;
} else {
    echo 'Proses Gagal<br>Error : Gagal memperbarui data admin.<br>' . mysqli_error($koneksi);
}
?>
