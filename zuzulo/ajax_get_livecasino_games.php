<?php
include_once '../koneksi.php'; // Sesuaikan path jika perlu

// Path untuk menampilkan gambar dari file PHP ini (relatif dari root)
$banner_display_path = "../uploads/livecasino_banners/"; // Harus konsisten dengan manage_livecasino_games.php

$provider = isset($_GET['provider']) ? mysqli_real_escape_string($koneksi, $_GET['provider']) : '';

if (empty($provider)) {
    echo '<tr><td colspan="3" class="text-center">Provider tidak valid.</td></tr>';
    exit;
}

$sql = "SELECT id, game_name, banner FROM gamelist_livecasino WHERE provider = '$provider' ORDER BY game_name ASC";
$result = mysqli_query($koneksi, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $banner_url = '';
        if (!empty($row['banner'])) {
            // Cek apakah banner adalah URL absolut atau hanya nama file
            if (filter_var($row['banner'], FILTER_VALIDATE_URL)) {
                $banner_url = htmlspecialchars($row['banner']);
            } else {
                $banner_url = $banner_display_path . htmlspecialchars($row['banner']);
            }
        } else {
            $banner_url = $banner_display_path . 'default_banner.png'; // Sediakan gambar default jika banner kosong
        }
        
        // Tambahkan cache buster ke URL banner untuk memastikan gambar terbaru ditampilkan
        $banner_url_with_cb = $banner_url . '?' . time();

        echo '<tr>';
        echo '<td>' . htmlspecialchars($row['game_name']) . '</td>';
        echo '<td><img src="' . $banner_url_with_cb . '" alt="' . htmlspecialchars($row['game_name']) . '" class="banner-img" data-gameid="'.htmlspecialchars($row['id']).'" onerror="this.onerror=null;this.src=\'../uploads/livecasino_banners/placeholder.png\';"></td>'; // Tambahkan placeholder jika gambar error
        echo '<td>';
        echo '<button class="btn btn-sm btn-primary edit-banner-btn" data-id="' . htmlspecialchars($row['id']) . '" data-gamename="' . htmlspecialchars($row['game_name']) . '" data-bannerfile="' . htmlspecialchars($row['banner'] ?? '') . '">Edit Banner</button>';
        echo '</td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="3" class="text-center">Tidak ada game ditemukan untuk provider ini.</td></tr>';
}

mysqli_close($koneksi);
?>
