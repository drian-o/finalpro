<?php
// Tampilkan semua error untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Sertakan file koneksi.php
include 'koneksi.php';

// Sertakan class API dan kredensial
include 'classes/connectAPI.php';
include 'classes/class.nexusggr.php';

// Cek apakah class API berhasil diinisialisasi
if (!isset($DEV)) {
    die("Error: API class not initialized.");
}

try {
    // Panggil metode provider_list() dari class API
    $response = $DEV->provider_list();

    // Periksa apakah respons berhasil dan memiliki data providers
    if (isset($response['status']) && $response['status'] == 1 && isset($response['providers'])) {
        $providers = $response['providers'];

        // Loop melalui setiap provider yang diterima
        foreach ($providers as $provider) {
            $code = mysqli_real_escape_string($koneksi, $provider['code']);
            $name = mysqli_real_escape_string($koneksi, $provider['name']);
            // Ubah status angka (1/0) menjadi string ('open'/'maintenancing') sesuai struktur tabel Anda
            $status = ($provider['status'] == 1) ? 'open' : 'maintenancing';

            // Query untuk melakukan UPSERT (UPDATE atau INSERT)
            $query = "INSERT INTO `nexus_provider` (`provider_code`, `provider_name`, `provider_status`)
                      VALUES ('$code', '$name', '$status')
                      ON DUPLICATE KEY UPDATE
                      `provider_name` = VALUES(`provider_name`),
                      `provider_status` = VALUES(`provider_status`)";

            // Jalankan query
            if (mysqli_query($koneksi, $query)) {
                echo "Data untuk provider '{$name}' berhasil di-update/insert.<br>";
            } else {
                echo "Error saat memproses provider '{$name}': " . mysqli_error($koneksi) . "<br>";
            }
        }

    } else {
        echo "Error: API call failed. Message: " . ($response['msg'] ?? 'Unknown error') . "<br>";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}

// Tutup koneksi database
mysqli_close($koneksi);
?>