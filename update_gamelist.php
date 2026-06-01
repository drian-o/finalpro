<?php
// Tampilkan semua error untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Sertakan file koneksi.php dan class API
include 'koneksi.php';
include 'classes/connectAPI.php';
include 'classes/class.nexusggr.php';

// Ambil daftar provider dari database untuk dropdown
$providers = [];
$query = "SELECT `provider_code`, `provider_name` FROM `nexus_provider` ORDER BY `provider_name` ASC";
$result = mysqli_query($koneksi, $query);
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $providers[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Game List</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f4f4; }
        .container { max-width: 800px; margin: auto; padding: 20px; background-color: #fff; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { text-align: center; color: #333; }
        form { display: flex; flex-direction: column; gap: 15px; margin-bottom: 20px; }
        select, button { padding: 12px; font-size: 16px; border-radius: 5px; border: 1px solid #ccc; }
        button { background-color: #007BFF; color: white; border: none; cursor: pointer; transition: background-color 0.3s ease; }
        button:hover { background-color: #0056b3; }
        .log-box { margin-top: 20px; padding: 15px; background-color: #e9ecef; border: 1px solid #ced4da; border-radius: 5px; max-height: 400px; overflow-y: auto; white-space: pre-wrap; word-wrap: break-word; }
        .success { color: green; }
        .warning { color: orange; }
        .error { color: red; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Update Daftar Game</h1>
        <form action="" method="POST">
            <label for="provider_code">Pilih Provider:</label>
            <select name="provider_code" id="provider_code" required>
                <option value="">-- Pilih Provider --</option>
                <?php foreach ($providers as $provider): ?>
                    <option value="<?php echo htmlspecialchars($provider['provider_code']); ?>">
                        <?php echo htmlspecialchars($provider['provider_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Mulai Update Game List</button>
        </form>

        <div class="log-box">
            <?php
            // Logika pemrosesan akan berjalan jika form disubmit
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['provider_code'])) {
                // Aktifkan output buffering untuk mengirim output secara bertahap
                @ob_end_flush();
                @ob_implicit_flush(true);
                echo "<h2>Proses Update Dimulai...</h2>";

                $provider_code = $_POST['provider_code'];
                $success_count = 0;
                $skipped_count = 0;
                $error_count = 0;
                $base_upload_dir = 'upload/cdn/';
                
                // Pastikan class API berhasil diinisialisasi
                if (!isset($DEV)) {
                    echo "<p class='error'>Error: API class not initialized.</p>";
                    goto end_script;
                }

                try {
                    echo "<p>Menghubungi API untuk provider: <strong>{$provider_code}</strong>...</p>";
                    $response = $DEV->game_list($provider_code);

                    if (isset($response['status']) && $response['status'] == 1 && isset($response['games'])) {
                        $games = $response['games'];
                        echo "<p class='success'>Berhasil mendapatkan " . count($games) . " game dari API.</p>";

                        $provider_dir = $base_upload_dir . $provider_code;
                        if (!is_dir($provider_dir)) {
                            echo "<p>Membuat direktori lokal: <strong>{$provider_dir}</strong></p>";
                            mkdir($provider_dir, 0755, true);
                        }

                        foreach ($games as $game) {
                            $game_code = mysqli_real_escape_string($koneksi, $game['game_code']);
                            $game_name = mysqli_real_escape_string($koneksi, $game['game_name']);
                            $game_banner_url_api = mysqli_real_escape_string($koneksi, $game['banner']);
                            $game_status = ($game['status'] == 1) ? 'open' : 'maintenancing';

                            echo "<p>Memproses game: <strong>{$game_name}</strong>...</p>";
                            
                            // --- Proses Unduh Gambar ---
                            $local_image_path = NULL;
                            $file_name = $game_code . '.jpg';
                            $local_file_path = $provider_dir . '/' . $file_name;

                            if (file_exists($local_file_path)) {
                                echo "<span class='warning'>Gambar sudah ada di lokal: {$local_file_path}. Melewati proses unduh.</span><br>";
                                $local_image_path = mysqli_real_escape_string($koneksi, $local_file_path);
                            } else {
                                echo "Mendownload gambar dari API...";
                                $image_data = @file_get_contents($game['banner']);

                                if ($image_data !== false) {
                                    if (file_put_contents($local_file_path, $image_data) !== false) {
                                        echo "<span class='success'> Berhasil disimpan ke: {$local_file_path}.</span><br>";
                                        $local_image_path = mysqli_real_escape_string($koneksi, $local_file_path);
                                    } else {
                                        echo "<span class='error'> Gagal menyimpan gambar ke lokal.</span><br>";
                                    }
                                } else {
                                    echo "<span class='error'> Gagal mengunduh gambar dari URL API.</span><br>";
                                }
                            }
                            
                            // Query untuk melakukan UPSERT (Update/Insert)
                            $query = "INSERT INTO `nexus_gamelist` (`provider_code`, `game_code`, `game_name`, `game_image_local`, `game_image_url_api`, `game_status`) 
                                      VALUES ('$provider_code', '$game_code', '$game_name', " . ($local_image_path ? "'$local_image_path'" : "NULL") . ", '$game_banner_url_api', '$game_status')
                                      ON DUPLICATE KEY UPDATE 
                                      `game_name` = VALUES(`game_name`),
                                      `game_image_local` = VALUES(`game_image_local`),
                                      `game_image_url_api` = VALUES(`game_image_url_api`),
                                      `game_status` = VALUES(`game_status`)";

                            if (mysqli_query($koneksi, $query)) {
                                echo "<span class='success'>Data game berhasil diperbarui di database.</span><hr>";
                                $success_count++;
                            } else {
                                echo "<span class='error'>Error saat memperbarui database: " . mysqli_error($koneksi) . "</span><hr>";
                                $error_count++;
                            }
                        }
                        
                        echo "<h3>Proses Selesai!</h3>";
                        echo "<p class='success'>Total game yang berhasil di-update: {$success_count}</p>";
                        echo "<p class='warning'>Total game yang gagal: {$error_count}</p>";
                        
                    } else {
                        echo "<p class='error'>Error: API call failed. Message: " . ($response['msg'] ?? 'Unknown error') . "</p>";
                    }

                } catch (Exception $e) {
                    echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
                }
                
                end_script:
                mysqli_close($koneksi);
            }
            ?>
        </div>
    </div>
</body>
</html>