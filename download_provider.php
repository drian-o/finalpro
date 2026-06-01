<?php
// download_images.php

// Sertakan file koneksi database Anda
// Ini akan membuat variabel $koneksi yang siap digunakan
include 'koneksi.php';

// Pastikan koneksi berhasil sebelum melanjutkan
if (!$koneksi) {
    die("Koneksi database gagal dari koneksi.php.");
}

// Path dasar folder untuk menyimpan gambar
$base_upload_dir = __DIR__ . '/upload/';

// Buat folder dasar jika belum ada
if (!is_dir($base_upload_dir)) {
    mkdir($base_upload_dir, 0777, true);
    echo "Folder 'upload/' berhasil dibuat.<br>";
}

// Query untuk mengambil data provider
// Sekarang kita juga mengambil kolom 'type'
$sql = "SELECT providerid, providerimage, type FROM tb_providerbaru";
$result = mysqli_query($koneksi, $sql); // Menggunakan $koneksi dan mysqli_query()

if ($result) { // Periksa apakah query berhasil
    if (mysqli_num_rows($result) > 0) { // Menggunakan mysqli_num_rows()
        while ($row = mysqli_fetch_assoc($result)) { // Menggunakan mysqli_fetch_assoc()
            $provider_id = $row['providerid'];
            $image_url = $row['providerimage'];
            $type = $row['type']; // Ambil nilai type

            // Dapatkan nama file dari URL
            $path_parts = pathinfo($image_url);
            // Default ke 'webp' jika tidak ada ekstensi atau ekstensi tidak valid
            $file_extension = isset($path_parts['extension']) && !empty($path_parts['extension']) ? $path_parts['extension'] : 'webp';
            $file_name = $provider_id . '.' . $file_extension; // Gunakan providerid sebagai nama file

            // Buat path folder spesifik untuk type ini
            $type_upload_dir = $base_upload_dir . strtolower($type) . '/'; // Contoh: upload/sl/, upload/lc/, dll.

            // Buat folder type jika belum ada
            if (!is_dir($type_upload_dir)) {
                mkdir($type_upload_dir, 0777, true);
                echo "Folder '{$type_upload_dir}' berhasil dibuat.<br>";
            }

            $save_path = $type_upload_dir . $file_name;

            // Download gambar
            // Menggunakan @ untuk menekan error jika URL tidak valid atau timeout
            $image_content = @file_get_contents($image_url);

            if ($image_content !== FALSE) {
                if (file_put_contents($save_path, $image_content)) {
                    echo "Gambar untuk Provider ID '{$provider_id}' (Type: {$type}) berhasil diunduh dan disimpan sebagai '{$type_upload_dir}{$file_name}'<br>";
                } else {
                    echo "Gagal menyimpan gambar untuk Provider ID '{$provider_id}' ke '{$save_path}'<br>";
                }
            } else {
                echo "Gagal mengunduh gambar dari URL: '{$image_url}' untuk Provider ID '{$provider_id}'<br>";
            }
        }
    } else {
        echo "Tidak ada data provider ditemukan.";
    }
} else {
    echo "Error dalam query database: " . mysqli_error($koneksi) . "<br>";
}

// Tutup koneksi database
mysqli_close($koneksi); // Menggunakan mysqli_close()
?>