<?php
// update_ace_provider_images.php

// Sertakan file koneksi database Anda
include 'koneksi.php';

// Pastikan koneksi berhasil sebelum melanjutkan
if (!$koneksi) {
    die("Koneksi database gagal dari koneksi.php.");
}

// Path dasar folder tempat gambar disimpan
$base_local_image_path = '/upload/'; // Path relatif untuk disimpan di database

// Path lengkap ke gambar placeholder
$null_image_path = $base_local_image_path . 'null.png';

echo "Memulai proses pembaruan tabel ace_provider...<br><br>";

// 1. Ambil semua provider dari ace_provider
// Kita akan iterasi melalui ace_provider dan mencari kecocokan di tb_providerbaru
$sql_ace_provider = "SELECT id, provider_code, provider_name FROM ace_provider";
$result_ace_provider = mysqli_query($koneksi, $sql_ace_provider);

if (!$result_ace_provider) {
    die("Error dalam query ace_provider: " . mysqli_error($koneksi) . "<br>");
}

if (mysqli_num_rows($result_ace_provider) > 0) {
    while ($row_ace = mysqli_fetch_assoc($result_ace_provider)) {
        $ace_id = $row_ace['id'];
        $ace_provider_code = $row_ace['provider_code']; // Misalnya 'PRAGMATIC'
        $ace_provider_name = $row_ace['provider_name'];

        $found_match = false;
        $local_image_to_save = $null_image_path; // Default ke gambar null

        // Cari kecocokan di tb_providerbaru berdasarkan ace_provider_code
        // Menggunakan providername dari tb_providerbaru karena lebih cocok dengan provider_code ace_provider
        $sql_match_tb = "SELECT providerid, type, providerimage FROM tb_providerbaru
                         WHERE providername = '" . mysqli_real_escape_string($koneksi, $ace_provider_code) . "'";
        $result_match_tb = mysqli_query($koneksi, $sql_match_tb);

        if ($result_match_tb && mysqli_num_rows($result_match_tb) > 0) {
            $row_match_tb = mysqli_fetch_assoc($result_match_tb);
            $provider_id_tb = $row_match_tb['providerid']; // Misalnya 'PR'
            $type_tb = $row_match_tb['type'];
            $original_image_url_tb = $row_match_tb['providerimage'];

            // Dapatkan ekstensi file dari URL asli
            $path_parts = pathinfo($original_image_url_tb);
            $file_extension = isset($path_parts['extension']) && !empty($path_parts['extension']) ? $path_parts['extension'] : 'webp';

            // Bentuk path gambar lokal yang diharapkan
            $expected_local_file = __DIR__ . $base_local_image_path . strtolower($type_tb) . '/' . $provider_id_tb . '.' . $file_extension;

            // Periksa apakah file gambar lokal benar-benar ada
            if (file_exists($expected_local_file)) {
                $local_image_to_save = $base_local_image_path . strtolower($type_tb) . '/' . $provider_id_tb . '.' . $file_extension;
                $found_match = true;
            } else {
                echo "Gambar lokal tidak ditemukan untuk {$ace_provider_code} (URL asli: {$original_image_url_tb}, diharapkan: {$expected_local_file}). Menggunakan null.png.<br>";
            }
        } else {
            echo "Tidak ditemukan kecocokan di tb_providerbaru untuk provider_code '{$ace_provider_code}'. Menggunakan null.png.<br>";
        }

        // Perbarui tabel ace_provider dengan path yang sesuai (gambar asli atau null.png)
        $sql_update_ace = "UPDATE ace_provider
                           SET provider_image = '" . mysqli_real_escape_string($koneksi, $local_image_to_save) . "'
                           WHERE id = {$ace_id}";

        if (mysqli_query($koneksi, $sql_update_ace)) {
            if (mysqli_affected_rows($koneksi) > 0) {
                echo "Berhasil memperbarui provider_image untuk '{$ace_provider_code}' menjadi '{$local_image_to_save}'<br>";
            } else {
                echo "Provider_image untuk '{$ace_provider_code}' sudah sama atau tidak ada perubahan.<br>";
            }
        } else {
            echo "Gagal memperbarui provider_image untuk '{$ace_provider_code}': " . mysqli_error($koneksi) . "<br>";
        }
    }
} else {
    echo "Tidak ada data ditemukan di tabel 'ace_provider'.";
}

echo "<br>Proses pembaruan selesai.";

// Tutup koneksi database
mysqli_close($koneksi);
?>