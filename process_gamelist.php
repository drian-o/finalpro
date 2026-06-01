<?php
// process_gamelist.php

// Pastikan skrip hanya dapat diakses melalui POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['provider_code'])) {
    die("Akses tidak valid.");
}

// Aktifkan output buffering untuk mengirim output secara bertahap
ob_implicit_flush(true);
ob_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- DEPENDENSI & SETUP ---
include_once 'koneksi.php';
include_once 'classes/class.nexusggr.php';
include_once 'classes/connectAPI.php'; // Berisi $user_agent dan $signature

echo "<h2>Proses Pembaruan Game Dimulai</h2>";
echo "Menghubungi API Nexus...<br>";

// Dapatkan kode provider dari POST
$provider_code = $_POST['provider_code'];

try {
    // Inisialisasi API Nexus
    $NexusAPI = new API($user_agent, $signature);
    
    // Panggil metode game_list() dari class API
    $response = $NexusAPI->game_list($provider_code);
    
    echo "Memproses respons dari API...<br>";
    ob_flush(); flush();

    // Periksa apakah respons berhasil dan memiliki data games
    if (isset($response['status']) && $response['status'] == 1 && isset($response['games'])) {
        $games = $response['games'];
        echo "Berhasil mendapatkan " . count($games) . " game dari API.<br>";
        
        $base_upload_dir = 'upload/cdn/';
        $provider_dir = $base_upload_dir . $provider_code;
        
        // Buat direktori provider jika belum ada
        if (!is_dir($provider_dir)) {
            echo "Membuat direktori lokal: <strong>{$provider_dir}</strong><br>";
            mkdir($provider_dir, 0755, true);
        }
        ob_flush(); flush();

        // Loop melalui setiap game yang diterima
        foreach ($games as $game) {
            $game_code = mysqli_real_escape_string($koneksi, $game['game_code']);
            $game_name = mysqli_real_escape_string($koneksi, $game['game_name']);
            $game_image_url_api = mysqli_real_escape_string($koneksi, $game['banner']);
            $game_status = mysqli_real_escape_string($koneksi, ($game['status'] == 1) ? 'open' : 'maintenance');

            echo "Memproses game: <strong>{$game_name}</strong>...<br>";
            
            // --- Proses Download Gambar ---
            $local_image_path = NULL;
            $file_name = $game_code . '.jpg';
            $local_file_path = $provider_dir . '/' . $file_name;

            if (file_exists($local_file_path)) {
                echo "<span style='color:orange;'>Gambar sudah ada di lokal, melewati unduh.</span><br>";
                $local_image_path = mysqli_real_escape_string($koneksi, $local_file_path);
            } else {
                echo "Mendownload gambar...";
                $image_data = @file_get_contents($game['banner']);

                if ($image_data !== false) {
                    if (file_put_contents($local_file_path, $image_data) !== false) {
                        echo "<span style='color:green;'> Berhasil disimpan ke: {$local_file_path}.</span><br>";
                        $local_image_path = mysqli_real_escape_string($koneksi, $local_file_path);
                    } else {
                        echo "<span style='color:red;'> Gagal menyimpan gambar ke lokal.</span><br>";
                    }
                } else {
                    echo "<span style='color:red;'> Gagal mengunduh gambar dari URL API.</span><br>";
                }
            }
            ob_flush(); flush();

            // Query untuk melakukan UPSERT (UPDATE atau INSERT)
            $query = "INSERT INTO `nexus_gamelist` (`provider_code`, `game_code`, `game_name`, `game_image_local`, `game_image_url_api`, `game_status`, `game_source`) 
                      VALUES ('$provider_code', '$game_code', '$game_name', " . ($local_image_path ? "'$local_image_path'" : "NULL") . ", '$game_image_url_api', '$game_status', 'nexus')
                      ON DUPLICATE KEY UPDATE 
                      `game_name` = VALUES(`game_name`),
                      `game_image_local` = VALUES(`game_image_local`),
                      `game_image_url_api` = VALUES(`game_image_url_api`),
                      `game_status` = VALUES(`game_status`)";

            if (mysqli_query($koneksi, $query)) {
                echo "<span style='color:green;'>Data game berhasil diperbarui di database.</span><hr>";
            } else {
                echo "<span style='color:red;'>Error saat memperbarui database: " . mysqli_error($koneksi) . "</span><hr>";
            }
            ob_flush(); flush();
        }
        
    } else {
        echo "<span style='color:red;'>Error: Panggilan API gagal. Pesan: " . ($response['msg'] ?? 'Error tidak diketahui') . "</span><br>";
    }

} catch (Exception $e) {
    echo "<span style='color:red;'>Error: " . $e->getMessage() . "</span><br>";
}

echo "<h2>Proses Pembaruan Game Selesai!</h2>";
mysqli_close($koneksi);
ob_end_flush();
?>