<?php
include 'koneksi.php'; // Pastikan path ini benar

if ($koneksi) {
    echo "<!DOCTYPE html>";
    echo "<html lang='id'>";
    echo "<head>";
    echo "    <meta charset='UTF-8'>";
    echo "    <meta name='viewport' content='width=device-width, initial-scale=1.0'>";
    echo "    <title>Preview Update game_image_local</title>";
    echo "    <style>";
    echo "        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f4f4; color: #333; }";
    echo "        h1 { color: #007bff; border-bottom: 2px solid #007bff; padding-bottom: 10px; }";
    echo "        h2 { color: #0056b3; margin-top: 30px; border-bottom: 1px solid #0056b3; padding-bottom: 5px;}";
    echo "        table { width: 100%; border-collapse: collapse; margin-top: 20px; margin-bottom: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); background-color: #fff; }";
    echo "        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }";
    echo "        th { background-color: #007bff; color: white; text-transform: uppercase; }";
    echo "        tr:nth-child(even) { background-color: #f2f2f2; }";
    echo "        tr:hover { background-color: #e9e9e9; }";
    echo "        .no-data { text-align: center; color: #666; padding: 20px; font-style: italic; }";
    echo "        .warning { color: orange; font-weight: bold; }";
    echo "        .info { color: gray; font-style: italic; }";
    echo "        .action-button { padding: 10px 20px; background-color: #28a745; color: white; border: none; cursor: pointer; font-size: 16px; margin-top: 10px; }";
    echo "        .action-button:hover { background-color: #218838; }";
    echo "    </style>";
    echo "</head>";
    echo "<body>";

    echo "<h1>Perbandingan dan Preview Update game_image_local</h1>";
    echo "<p>Halaman ini akan menampilkan data game dari <code>srg_gamelist</code> yang akan di-update kolom <code>game_image_local</code>-nya, serta data yang tidak akan di-update.</p>";

    // --- Bagian Pengambilan Data ---

    // Mengambil semua data dari srg_gamelist
    $query_srg_all = "SELECT id, game_name, game_image_local FROM srg_gamelist";
    $result_srg_all = mysqli_query($koneksi, $query_srg_all);

    // Mengambil semua data game_name dan game_image_local yang tidak kosong dari telo_gamelist
    $query_telo_full_image = "SELECT game_name, game_image_local FROM telo_gamelist WHERE game_image_local IS NOT NULL AND game_image_local != ''";
    $result_telo_full_image = mysqli_query($koneksi, $query_telo_full_image);

    $telo_game_images = [];
    if ($result_telo_full_image && mysqli_num_rows($result_telo_full_image) > 0) {
        while ($row_telo = mysqli_fetch_assoc($result_telo_full_image)) {
            // Gunakan game_name sebagai kunci untuk pencarian cepat
            $telo_game_images[$row_telo['game_name']] = $row_telo['game_image_local'];
        }
    }

    $games_to_update = [];
    $games_not_updated = []; // Untuk menyimpan data yang tidak akan di-update

    if ($result_srg_all && mysqli_num_rows($result_srg_all) > 0) {
        while ($row_srg = mysqli_fetch_assoc($result_srg_all)) {
            $srg_id = $row_srg['id'];
            $srg_game_name = $row_srg['game_name'];
            $srg_current_image = $row_srg['game_image_local'];

            // Cek apakah srg_game_image_local kosong/NULL DAN ada kecocokan game_name di telo_gamelist
            if ((empty($srg_current_image) || is_null($srg_current_image)) && isset($telo_game_images[$srg_game_name])) {
                $games_to_update[] = [
                    'id_srg' => $srg_id,
                    'game_name' => $srg_game_name,
                    'current_srg_image' => $srg_current_image,
                    'new_image_from_telo' => $telo_game_images[$srg_game_name]
                ];
            } else {
                // Jika tidak memenuhi kriteria update, masukkan ke daftar 'tidak di-update'
                $reason = "";
                if (!empty($srg_current_image)) {
                    $reason = "Game_image_local sudah terisi.";
                } elseif (!isset($telo_game_images[$srg_game_name])) {
                    $reason = "Tidak ada game_name yang cocok di telo_gamelist dengan gambar.";
                } else {
                     $reason = "Kondisi tidak terpenuhi."; // Fallback, seharusnya tidak tercapai
                }

                $games_not_updated[] = [
                    'id_srg' => $srg_id,
                    'game_name' => $srg_game_name,
                    'current_srg_image' => $srg_current_image,
                    'reason' => $reason
                ];
            }
        }
    }

    // --- Bagian Menampilkan Preview Data yang Akan Di-update ---
    echo "<h2>Data yang Akan Di-update di `srg_gamelist`</h2>";
    echo "<p>Berikut adalah daftar game di <code>srg_gamelist</code> yang memiliki <code>game_image_local</code> kosong dan akan diperbarui menggunakan data dari <code>telo_gamelist</code> berdasarkan kesamaan <code>game_name</code>.</p>";

    if (!empty($games_to_update)) {
        echo "<table>";
        echo "    <thead>";
        echo "        <tr>";
        echo "            <th>ID (srg_gamelist)</th>";
        echo "            <th>Game Name</th>";
        echo "            <th>Game Image Local (Sebelum Update)</th>";
        echo "            <th>Game Image Local (Akan Di-update dari telo_gamelist)</th>";
        echo "        </tr>";
        echo "    </thead>";
        echo "    <tbody>";
        foreach ($games_to_update as $game) {
            echo "        <tr>";
            echo "            <td>" . htmlspecialchars($game['id_srg']) . "</td>";
            echo "            <td>" . htmlspecialchars($game['game_name']) . "</td>";
            echo "            <td>" . (empty($game['current_srg_image']) ? "<span class='warning'>Kosong/NULL</span>" : htmlspecialchars($game['current_srg_image'])) . "</td>";
            echo "            <td>" . htmlspecialchars($game['new_image_from_telo']) . "</td>";
            echo "        </tr>";
        }
        echo "    </tbody>";
        echo "</table>";
        echo "<p>Total game yang akan di-update: **" . count($games_to_update) . "**</p>";

        // Tambahkan tombol untuk eksekusi update
        echo "<form method='POST'>";
        echo "<input type='hidden' name='confirm_update' value='true'>";
        // Serialisasi data games_to_update ke dalam hidden field agar bisa diproses saat POST
        echo "<input type='hidden' name='games_to_update_json' value='" . htmlspecialchars(json_encode($games_to_update)) . "'>";
        echo "<button type='submit' class='action-button'>Lakukan Update Sekarang</button>";
        echo "</form>";

    } else {
        echo "<p class='no-data'>Tidak ada game di <code>srg_gamelist</code> yang memiliki <code>game_image_local</code> kosong dan cocok dengan <code>game_name</code> di <code>telo_gamelist</code> yang memiliki gambar.</p>";
    }

    echo "<hr>";

    // --- Bagian Menampilkan Data yang Tidak Akan Di-update ---
    echo "<h2>Data yang **Tidak** Akan Di-update di `srg_gamelist`</h2>";
    echo "<p>Berikut adalah daftar game di <code>srg_gamelist</code> yang tidak memenuhi kriteria update (<code>game_image_local</code> sudah terisi atau tidak ditemukan kecocokan di <code>telo_gamelist</code>).</p>";

    if (!empty($games_not_updated)) {
        echo "<table>";
        echo "    <thead>";
        echo "        <tr>";
        echo "            <th>ID (srg_gamelist)</th>";
        echo "            <th>Game Name</th>";
        echo "            <th>Game Image Local Saat Ini</th>";
        echo "            <th>Alasan Tidak Di-update</th>";
        echo "        </tr>";
        echo "    </thead>";
        echo "    <tbody>";
        foreach ($games_not_updated as $game) {
            echo "        <tr>";
            echo "            <td>" . htmlspecialchars($game['id_srg']) . "</td>";
            echo "            <td>" . htmlspecialchars($game['game_name']) . "</td>";
            echo "            <td>" . (empty($game['current_srg_image']) ? "<span class='info'>Kosong</span>" : htmlspecialchars($game['current_srg_image'])) . "</td>";
            echo "            <td>" . htmlspecialchars($game['reason']) . "</td>";
            echo "        </tr>";
        }
        echo "    </tbody>";
        echo "</table>";
        echo "<p>Total game yang tidak di-update: **" . count($games_not_updated) . "**</p>";
    } else {
        echo "<p class='no-data'>Semua game di <code>srg_gamelist</code> telah memenuhi kriteria update atau tidak ada game sama sekali.</p>";
    }

    echo "</body>";
    echo "</html>";

    // --- Bagian untuk Melakukan Update Setelah Konfirmasi POST ---
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_update']) && $_POST['confirm_update'] === 'true') {
        echo "<h2>Melakukan Update...</h2>";
        $update_count = 0;
        $games_to_process = json_decode($_POST['games_to_update_json'], true); // Dekode data yang diserialisasi

        if (json_last_error() !== JSON_ERROR_NONE) {
            echo "<p style='color: red;'>Error decoding update data: " . json_last_error_msg() . "</p>";
        } else {
            foreach ($games_to_process as $game) {
                $id_srg = mysqli_real_escape_string($koneksi, $game['id_srg']);
                $new_image_local = mysqli_real_escape_string($koneksi, $game['new_image_from_telo']);

                $update_query = "UPDATE srg_gamelist SET game_image_local = '$new_image_local' WHERE id = '$id_srg'";
                if (mysqli_query($koneksi, $update_query)) {
                    $update_count++;
                } else {
                    echo "<p style='color: red;'>Gagal update ID " . htmlspecialchars($id_srg) . ": " . mysqli_error($koneksi) . "</p>";
                }
            }
            echo "<p style='color: green; font-weight: bold;'>Update Selesai! Berhasil mengupdate **" . $update_count . "** game.</p>";
            // Refresh halaman untuk melihat perubahan setelah update
            echo "<meta http-equiv='refresh' content='2;url='>";
        }
    }

    mysqli_close($koneksi);
} else {
    echo "<h1>Kesalahan Koneksi Database</h1>";
    echo "<p>Tidak dapat terhubung ke database. Silakan periksa file koneksi.php Anda.</p>";
    echo "<p>Pesan Kesalahan: " . mysqli_connect_error() . "</p>";
}
?>