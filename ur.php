<?php
// Include your database connection file
include 'koneksi.php'; // Pastikan path ini benar

// Definisikan path
$image_base_dir = __DIR__ . '/upload/game/playngo/'; // Path untuk menyimpan gambar Microgaming
$data_file = 'hasil_data_game_lengkap.txt'; // Path ke file data yang diekstrak

// Definisikan kode provider spesifik untuk filtering
$target_provider_code = 'playngo';

// Pastikan direktori ada
if (!is_dir($image_base_dir)) {
    if (!mkdir($image_base_dir, 0777, true)) {
        die("Error: Tidak dapat membuat direktori untuk gambar: " . $image_base_dir);
    }
}

// --- Fungsi untuk mem-parsing hasil_data_game_lengkap.txt ---
function parse_extracted_data($file_path) {
    $extracted_games = [];
    $lines = file($file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if ($lines === false) {
        return []; // Kembalikan array kosong jika file tidak dapat dibaca
    }

    $current_game = [];
    foreach ($lines as $line) {
        if (strpos($line, 'Nama Game : ') === 0) {
            if (!empty($current_game)) {
                $extracted_games[] = $current_game;
            }
            $current_game = ['game_name' => trim(substr($line, strlen('Nama Game : ')))];
        } elseif (strpos($line, 'URL Gambar : ') === 0) {
            $current_game['image_url'] = trim(substr($line, strlen('URL Gambar : ')));
        } elseif (strpos($line, 'Provider : ') === 0) {
            $current_game['provider'] = trim(substr($line, strlen('Provider : ')));
        }
    }
    if (!empty($current_game)) {
        $extracted_games[] = $current_game;
    }
    return $extracted_games;
}

// --- Logika Utama ---
echo "<!DOCTYPE html>";
echo "<html lang='id'>";
echo "<head>";
echo "    <meta charset='UTF-8'>";
echo "    <meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "    <title>Update Microgaming Slot Game Images</title>";
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
echo "        .error-message { color: red; font-weight: bold; }";
echo "        .success-message { color: green; font-weight: bold; }";
echo "    </style>";
echo "</head>";
echo "<body>";

echo "<h1>Update Gambar Game untuk " . htmlspecialchars(ucwords(str_replace('_', ' ', $target_provider_code))) . " Slot</h1>";
echo "<p>Halaman ini akan membandingkan game " . htmlspecialchars(ucwords(str_replace('_', ' ', $target_provider_code))) . " di <code>srg_gamelist</code> (yang <code>game_image_local</code>-nya kosong) dengan data dari <code>" . htmlspecialchars($data_file) . "</code>.</p>";

if ($koneksi) {
    // 1. Baca data dari hasil_data_game_lengkap.txt
    $extracted_games_data = parse_extracted_data($data_file);
    $extracted_games_map = []; // Map untuk pencarian cepat: game_name => image_url
    foreach ($extracted_games_data as $game) {
        $extracted_games_map[$game['game_name']] = $game['image_url'];
    }

    // 2. Kueri srg_gamelist untuk game target provider dengan game_image_local kosong
    $query_srg = "SELECT id, game_name, game_code, game_image_local, game_image_url_api FROM srg_gamelist WHERE provider_code = '" . mysqli_real_escape_string($koneksi, $target_provider_code) . "' AND (game_image_local IS NULL OR game_image_local = '')";
    $result_srg = mysqli_query($koneksi, $query_srg);

    if (!$result_srg) {
        echo "<p class='error-message'>Error mengkueri srg_gamelist: " . mysqli_error($koneksi) . "</p>";
        if (empty($extracted_games_data)) {
             echo "<p class='error-message'>Tidak dapat membaca data dari '" . htmlspecialchars($data_file) . "'. Pastikan file ada dan formatnya benar.</p>";
        }
        echo "</body></html>";
        mysqli_close($koneksi);
        exit;
    }

    $games_to_update = [];
    $games_not_updated = [];

    if (mysqli_num_rows($result_srg) > 0) {
        while ($row_srg = mysqli_fetch_assoc($result_srg)) {
            $srg_id = $row_srg['id'];
            $srg_game_name = $row_srg['game_name'];
            $srg_game_code = $row_srg['game_code'];
            $srg_current_image_local = $row_srg['game_image_local'];

            if (isset($extracted_games_map[$srg_game_name])) {
                $new_image_url_from_file = $extracted_games_map[$srg_game_name];

                // --- LOGIKA PEMBUATAN NAMA FILE ---
                $path_parts = pathinfo($new_image_url_from_file);
                $original_extension = isset($path_parts['extension']) ? '.' . $path_parts['extension'] : '';

                // Format game_name untuk nama file: huruf kecil, ganti spasi dengan underscore, hapus karakter spesial
                $formatted_game_name = strtolower($srg_game_name);
                $formatted_game_name = str_replace(' ', '_', $formatted_game_name);
                $formatted_game_name = preg_replace('/[^a-z0-9_]/', '', $formatted_game_name);
                
                // Bangun nama file baru
                $new_file_name = $formatted_game_name . $original_extension;

                $local_image_path_to_save = $image_base_dir . $new_file_name;
                $db_image_path = 'upload/game/playngo/' . $new_file_name; // Path DB yang diperbarui

                $games_to_update[] = [
                    'id' => $srg_id,
                    'game_name' => $srg_game_name,
                    'current_image_local' => $srg_current_image_local,
                    'source_image_url' => $new_image_url_from_file,
                    'local_save_path' => $local_image_path_to_save,
                    'db_path' => $db_image_path
                ];
            } else {
                $games_not_updated[] = [
                    'id' => $srg_id,
                    'game_name' => $srg_game_name,
                    'current_image_local' => $srg_current_image_local,
                    'reason' => 'Tidak ditemukan kecocokan game_name di ' . htmlspecialchars($data_file)
                ];
            }
        }
    } else {
        echo "<p class='no-data'>Tidak ada game " . htmlspecialchars(ucwords(str_replace('_', ' ', $target_provider_code))) . " di <code>srg_gamelist</code> yang memiliki <code>game_image_local</code> kosong.</p>";
    }

    // --- Bagian Preview ---
    echo "<h2>Preview Data yang Akan Di-update</h2>";
    echo "<p>Berikut adalah daftar game di <code>srg_gamelist</code> dengan <code>provider_code = '" . htmlspecialchars($target_provider_code) . "'</code> yang <code>game_image_local</code>-nya kosong dan akan diperbarui dengan data dari <code>" . htmlspecialchars($data_file) . "</code>.</p>";

    if (!empty($games_to_update)) {
        echo "<table>";
        echo "    <thead>";
        echo "        <tr>";
        echo "            <th>ID</th>";
        echo "            <th>Nama Game</th>";
        echo "            <th>URL Gambar Saat Ini (srg_gamelist)</th>";
        echo "            <th>URL Sumber Gambar (dari file)</th>";
        echo "            <th>Akan Disimpan Lokal di</th>";
        echo "            <th>Akan Disimpan di DB sebagai</th>";
        echo "        </tr>";
        echo "    </thead>";
        echo "    <tbody>";
        foreach ($games_to_update as $game) {
            echo "        <tr>";
            echo "            <td>" . htmlspecialchars($game['id']) . "</td>";
            echo "            <td>" . htmlspecialchars($game['game_name']) . "</td>";
            echo "            <td>" . (empty($game['current_image_local']) ? "<span class='warning'>Kosong/NULL</span>" : htmlspecialchars($game['current_image_local'])) . "</td>";
            echo "            <td>" . htmlspecialchars($game['source_image_url']) . "</td>";
            echo "            <td>" . htmlspecialchars($game['local_save_path']) . "</td>";
            echo "            <td>" . htmlspecialchars($game['db_path']) . "</td>";
            echo "        </tr>";
        }
        echo "    </tbody>";
        echo "</table>";
        echo "<p>Total game yang akan di-update: **" . count($games_to_update) . "**</p>";

        echo "<h2>Konfirmasi Update</h2>";
        echo "<p>Jika daftar di atas sudah benar, klik tombol di bawah ini untuk memulai proses download gambar dan update database.</p>";
        echo "<form method='POST'>";
        echo "<input type='hidden' name='confirm_update' value='true'>";
        echo "<input type='hidden' name='games_to_update_json' value='" . htmlspecialchars(json_encode($games_to_update)) . "'>";
        echo "<button type='submit' class='action-button'>Lakukan Update & Download Sekarang</button>";
        echo "</form>";

    } else {
        echo "<p class='no-data'>Tidak ada game yang perlu di-update (semua <code>game_image_local</code> " . htmlspecialchars(ucwords(str_replace('_', ' ', $target_provider_code))) . " sudah terisi atau tidak ada kecocokan di file data).</p>";
    }

    echo "<hr>";

    echo "<h2>Data yang Tidak Akan Di-update (Game " . htmlspecialchars(ucwords(str_replace('_', ' ', $target_provider_code))) . ")</h2>";
    echo "<p>Berikut adalah daftar game " . htmlspecialchars(ucwords(str_replace('_', ' ', $target_provider_code))) . " di <code>srg_gamelist</code> yang <code>game_image_local</code>-nya sudah terisi atau tidak ditemukan kecocokan di file <code>" . htmlspecialchars($data_file) . "</code>.</p>";

    if (!empty($games_not_updated)) {
        echo "<table>";
        echo "    <thead>";
        echo "        <tr>";
        echo "            <th>ID</th>";
            echo "            <th>Nama Game</th>";
            echo "            <th>URL Gambar Saat Ini</th>";
            echo "            <th>Alasan Tidak Di-update</th>";
        echo "        </tr>";
        echo "    </thead>";
        echo "    <tbody>";
        foreach ($games_not_updated as $game) {
            echo "        <tr>";
                echo "            <td>" . htmlspecialchars($game['id']) . "</td>";
                echo "            <td>" . htmlspecialchars($game['game_name']) . "</td>";
                echo "            <td>" . (empty($game['current_image_local']) ? "<span class='info'>Kosong</span>" : htmlspecialchars($game['current_image_local'])) . "</td>";
                echo "            <td>" . htmlspecialchars($game['reason']) . "</td>";
            echo "        </tr>";
        }
        echo "    </tbody>";
        echo "</table>";
        echo "<p>Total game yang tidak di-update: **" . count($games_not_updated) . "**</p>";
    } else {
        echo "<p class='no-data'>Semua game " . htmlspecialchars(ucwords(str_replace('_', ' ', $target_provider_code))) . " di <code>srg_gamelist</code> telah memenuhi kriteria update atau tidak ada game yang belum terupdate.</p>";
    }

    // --- Bagian Eksekusi Update (setelah konfirmasi POST) ---
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_update']) && $_POST['confirm_update'] === 'true') {
        echo "<h2>Melakukan Update & Download Gambar...</h2>";
        $games_to_process = json_decode($_POST['games_to_update_json'], true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            echo "<p class='error-message'>Error mendekode data update: " . json_last_error_msg() . "</p>";
        } else {
            $update_count = 0;
            $download_success_count = 0;
            $download_fail_count = 0;

            foreach ($games_to_process as $game) {
                $id_srg = mysqli_real_escape_string($koneksi, $game['id']);
                $source_image_url = $game['source_image_url'];
                $local_save_path = $game['local_save_path'];
                $db_path = mysqli_real_escape_string($koneksi, $game['db_path']);

                // 1. Download gambar
                echo "<p>Mendownload: " . htmlspecialchars($source_image_url) . " ke " . htmlspecialchars($local_save_path) . "... ";
                $context = stream_context_create([
                    'http' => [
                        'timeout' => 10 // Timeout dalam detik
                    ]
                ]);
                $image_content = @file_get_contents($source_image_url, false, $context);
                if ($image_content === false) {
                    echo "<span style='color: red;'>GAGAL (Download)</span></p>";
                    $download_fail_count++;
                    continue;
                }

                $directory_for_file = dirname($local_save_path);
                if (!is_dir($directory_for_file)) {
                    if (!mkdir($directory_for_file, 0777, true)) {
                        echo "<span style='color: red;'>GAGAL (Buat Dir: " . htmlspecialchars($directory_for_file) . ")</span></p>";
                        $download_fail_count++;
                        continue;
                    }
                }

                if (@file_put_contents($local_save_path, $image_content) === false) {
                    echo "<span style='color: red;'>GAGAL (Simpan Lokal)</span></p>";
                    $download_fail_count++;
                    continue;
                }
                echo "<span style='color: green;'>BERHASIL</span></p>";
                $download_success_count++;

                // 2. Update database
                $update_query = "UPDATE srg_gamelist SET game_image_local = '$db_path' WHERE id = '$id_srg'";
                if (mysqli_query($koneksi, $update_query)) {
                    $update_count++;
                } else {
                    echo "<p class='error-message'>Gagal update ID " . htmlspecialchars($id_srg) . ": " . mysqli_error($koneksi) . "</p>";
                }
            }
            echo "<p class='success-message'>Proses Selesai!</p>";
            echo "<p>Berhasil mengupdate database: **" . $update_count . "** game.</p>";
            echo "<p>Berhasil download gambar: **" . $download_success_count . "**.</p>";
            if ($download_fail_count > 0) {
                echo "<p class='error-message'>Gagal download/simpan gambar: **" . $download_fail_count . "**.</p>";
            }

            echo "<meta http-equiv='refresh' content='3;url='>"; // Refresh halaman untuk melihat perubahan
        }
    }

    mysqli_close($koneksi);
} else {
    // Blok ini untuk kegagalan koneksi database umum dari koneksi.php
    // Pesan sudah ditangani di koneksi.php
}
echo "</body>";
echo "</html>";
?>