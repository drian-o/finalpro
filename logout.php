<?php
// 1. Cek apakah session sudah aktif sebelum memanggil session_start()
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Bersihkan semua data session
session_unset();

// 3. Hancurkan session
session_destroy();

// 4. Pastikan tidak ada output (echo/HTML) sebelum header redirect
header("Location: index.php"); // Ganti index.php dengan halaman login Anda
exit();
?>
