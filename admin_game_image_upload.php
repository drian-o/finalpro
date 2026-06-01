<?php
// Pastikan sesi dimulai (jika diperlukan untuk otorisasi)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Sertakan koneksi database
require_once __DIR__ . '/koneksi.php';

// --- KONFIGURASI KEAMANAN SEDERHANA ---
// UBAH INI DENGAN KUNCI RAHASIA YANG KUAT DAN SULIT DITEBAK!
$allowed_secret_key = 'game_image_secret_key_123';
$input_secret_key = isset($_GET['key']) ? $_GET['key'] : '';

if ($input_secret_key !== $allowed_secret_key) {
    header('HTTP/1.1 401 Unauthorized');
    echo '<h1>401 Unauthorized</h1>';
    echo '<p>Akses ditolak. Kunci keamanan tidak valid.</p>';
    exit();
}
// --- AKHIR KONFIGURASI KEAMANAN SEDERHANA ---

$message = ''; // Untuk pesan sukses/error
$upload_base_dir = __DIR__ . '/upload/game/'; // Base direktori upload fisik

// Tangani proses upload gambar dan update database
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_images'])) {
    $selected_provider_code = isset($_POST['selected_provider_code']) ? mysqli_real_escape_string($koneksi, $_POST['selected_provider_code']) : '';
    $selected_provider_server = isset($_POST['selected_provider_server']) ? mysqli_real_escape_string($koneksi, $_POST['selected_provider_server']) : '';
    $selected_provider_name = isset($_POST['selected_provider_name']) ? mysqli_real_escape_string($koneksi, $_POST['selected_provider_name']) : '';

    if (empty($selected_provider_code) || empty($selected_provider_server) || empty($selected_provider_name)) {
        $message = "<div class='alert alert-danger'>Provider tidak valid.</div>";
    } else {
        $provider_upload_dir = $upload_base_dir . preg_replace('/[^a-zA-Z0-9]/', '', strtolower($selected_provider_name)) . '/';

        // Buat direktori provider jika belum ada
        if (!is_dir($provider_upload_dir)) {
            mkdir($provider_upload_dir, 0755, true);
        }

        $success_count = 0;
        $fail_count = 0;
        $skipped_count = 0;

        // Loop melalui file yang diupload
        foreach ($_FILES as $input_name => $file_data) {
            // Periksa apakah ini adalah input file untuk game (contoh: game_image_GAMECODE)
            if (strpos($input_name, 'game_image_') === 0 && $file_data['error'] === UPLOAD_ERR_OK) {
                // Ekstrak game_code dari nama input
                $game_code_from_input = str_replace('game_image_', '', $input_name);
                
                // Amankan game_code jika perlu (walaupun sudah diasumsikan aman dari AJAX response)
                $game_code_from_input_safe = mysqli_real_escape_string($koneksi, $game_code_from_input);

                $file_tmp = $file_data['tmp_name'];
                $file_name = $file_data['name'];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                if (in_array($file_ext, $allowed_ext)) {
                    $new_file_name = $game_code_from_input_safe . '.' . $file_ext; // Gunakan game_code sebagai nama file
                    $target_file = $provider_upload_dir . $new_file_name;
                    // Path yang disimpan di DB adalah relatif dari ROOT WEBSITE
                    $relative_path_for_db = 'upload/game/' . preg_replace('/[^a-zA-Z0-9]/', '', strtolower($selected_provider_name)) . '/' . $new_file_name;

                    // Hapus gambar lama jika sudah ada
                    $table_name = ($selected_provider_server === 'server2') ? 'srg_gamelist' : 'telo_gamelist';
                    $query_old_image = mysqli_query($koneksi, "SELECT game_image_local FROM {$table_name} WHERE game_code = '{$game_code_from_input_safe}' AND provider_code = '{$selected_provider_code}'");
                    if ($query_old_image && mysqli_num_rows($query_old_image) > 0) {
                        $old_image_row = mysqli_fetch_assoc($query_old_image);
                        $old_image_path = $old_image_row['game_image_local'];
                        if (!empty($old_image_path) && strpos($old_image_path, 'upload/game/') === 0 && file_exists(__DIR__ . '/../' . $old_image_path) && $old_image_path !== $relative_path_for_db) {
                            unlink(__DIR__ . '/../' . $old_image_path); // Hapus file lama
                        }
                    }

                    if (move_uploaded_file($file_tmp, $target_file)) {
                        $query_update = "UPDATE {$table_name} SET game_image_local = '{$relative_path_for_db}' WHERE game_code = '{$game_code_from_input_safe}' AND provider_code = '{$selected_provider_code}'";
                        if (mysqli_query($koneksi, $query_update)) {
                            $success_count++;
                        } else {
                            $fail_count++;
                            error_log("Failed to update DB for game {$game_code_from_input_safe}: " . mysqli_error($koneksi));
                            unlink($target_file); // Hapus file jika update DB gagal
                        }
                    } else {
                        $fail_count++;
                        error_log("Failed to move uploaded file for game {$game_code_from_input_safe}");
                    }
                } else {
                    $fail_count++;
                    error_log("Invalid file extension for game {$game_code_from_input_safe}");
                }
            } else {
                // If no file uploaded for this input or other error, skip.
                if ($file_data['error'] !== UPLOAD_ERR_NO_FILE) {
                     error_log("File upload error for {$input_name}: " . $file_data['error']);
                }
                $skipped_count++;
            }
        }
        $message = "<div class='alert alert-success'>Proses upload selesai. Berhasil: {$success_count}, Gagal: {$fail_count}, Dilewati: {$skipped_count}.</div>";
    }
}


// Ambil daftar provider dari database
$providers = [];
$query_srg_providers = mysqli_query($koneksi, "SELECT provider_code, provider_name FROM srg_provider WHERE provider_status = 'active' ORDER BY provider_name ASC");
if ($query_srg_providers) {
    while ($p = mysqli_fetch_assoc($query_srg_providers)) {
        $p['server'] = 'server2'; // SRG = server2
        $providers[] = $p;
    }
}

$query_telo_providers = mysqli_query($koneksi, "SELECT provider_code, provider_name FROM telo_provider WHERE provider_status = 'active' ORDER BY provider_name ASC");
if ($query_telo_providers) {
    while ($p = mysqli_fetch_assoc($query_telo_providers)) {
        $p['server'] = 'server1'; // Telo = server1
        $providers[] = $p;
    }
}

// Urutkan semua provider
usort($providers, function($a, $b) {
    return strcasecmp($a['provider_name'], $b['provider_name']);
});

// Ambil data statistik untuk setiap provider
foreach ($providers as &$p) {
    $table_name = ($p['server'] === 'server2') ? 'srg_gamelist' : 'telo_gamelist';
    $query_stats = "SELECT
                        COUNT(*) AS total,
                        COUNT(game_image_local) AS with_image
                    FROM {$table_name}
                    WHERE provider_code = '{$p['provider_code']}'";
    $result_stats = mysqli_query($koneksi, $query_stats);
    if ($result_stats && $stats = mysqli_fetch_assoc($result_stats)) {
        $p['total_games'] = $stats['total'];
        $p['with_image'] = $stats['with_image'];
        $p['without_image'] = $stats['total'] - $stats['with_image'];
        $p['percentage_with_image'] = ($stats['total'] > 0) ? round(($stats['with_image'] / $stats['total']) * 100, 2) : 0;
    } else {
        $p['total_games'] = 0;
        $p['with_image'] = 0;
        $p['without_image'] = 0;
        $p['percentage_with_image'] = 0;
    }
}
unset($p); // Break the reference
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Gambar Game Provider</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --dark-bg: #121212;
            --dark-card: #1e1e1e;
            --dark-border: #333;
            --dark-text: #e0e0e0;
            --dark-text-muted: #aaaaaa;
            --primary-color: #0d6efd;
            --success-color: #198754;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--dark-bg);
            color: var(--dark-text);
            padding-top: 20px;
        }

        .container {
            max-width: 1000px;
            margin: auto;
            background-color: var(--dark-card);
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
        }

        h2, h5, h6 {
            color: #fff;
        }

        .alert {
            border: none;
            color: #fff;
            margin-bottom: 1.5rem;
            border-radius: 8px;
        }
        .alert-warning { background-color: #5d4000; color: #ffc107; }
        .alert-success { background-color: #0c4a2a; }
        .alert-danger { background-color: #5c181f; }

        .form-label {
            color: var(--dark-text);
            margin-bottom: 0.5rem;
        }
        
        .form-select, .form-control {
            background-color: var(--dark-bg);
            color: var(--dark-text);
            border: 1px solid var(--dark-border);
        }
        .form-select:focus, .form-control:focus {
            background-color: var(--dark-bg);
            color: var(--dark-text);
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        .game-card {
            background-color: var(--dark-bg);
            border: 1px solid var(--dark-border);
            border-radius: 8px;
            margin-bottom: 1rem;
            padding: 1rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        @media (min-width: 768px) {
            .game-card {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }
        }
        
        .game-card .game-info {
            flex-grow: 1;
            margin-right: 1rem;
        }
        
        .game-card h6 {
            margin-bottom: 0.5rem;
            font-size: 1.1em;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .game-card small {
            color: var(--dark-text-muted);
        }

        .game-card .action-buttons {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .game-card .input-group {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        @media (min-width: 768px) {
            .game-card .input-group {
                flex-wrap: nowrap;
            }
        }
        .game-card .input-group .form-control {
            flex-grow: 1;
        }
        
        .game-card .action-button-group {
            display: flex;
            gap: 5px;
        }
        
        .game-card .action-button,
        .game-card .reset-file-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 0.75rem;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease-in-out;
        }
        .game-card .action-button.btn-primary {
            background-color: var(--primary-color);
            border: 1px solid var(--primary-color);
            color: #fff;
        }
        .game-card .action-button.btn-primary:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
        }
        .game-card .action-button.btn-success {
            background-color: var(--success-color);
            border: 1px solid var(--success-color);
            color: #fff;
        }
        .game-card .action-button.btn-success:hover {
            background-color: #157347;
            border-color: #146c43;
        }
        .game-card .reset-file-btn {
            background-color: var(--danger-color);
            border: 1px solid var(--danger-color);
            color: #fff;
        }
        .game-card .reset-file-btn:hover {
            background-color: #bb2d3b;
            border-color: #b02a37;
        }
        .game-card .action-button i,
        .game-card .reset-file-btn i {
            margin-right: 5px;
        }
        .game-card .action-button:hover,
        .game-card .reset-file-btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }
        
        /* New styles for provider stats */
        .provider-stats {
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 0.9em;
            color: var(--dark-text-muted);
            margin-left: auto;
        }
        .provider-stats .stat-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .provider-stats .stat-item i {
            font-size: 1.1em;
        }
        .stat-item.complete i { color: var(--success-color); }
        .stat-item.incomplete i { color: var(--warning-color); }
        .form-select option {
            background-color: var(--dark-card);
            color: var(--dark-text);
        }
    </style>
</head>
<body>

<div class="container">
    <h2 class="mb-4 text-center">Upload Gambar Game Provider</h2>
    
    <?php if (!empty($message)): ?>
        <?php echo $message; ?>
    <?php endif; ?>

    <div class="mb-4">
        <label for="providerSelect" class="form-label">Pilih Provider:</label>
        <select class="form-select" id="providerSelect">
            <option value="">-- Pilih Provider --</option>
            <?php foreach ($providers as $provider): ?>
                <option value="<?php echo htmlspecialchars($provider['provider_code']); ?>" 
                        data-server="<?php echo htmlspecialchars($provider['server']); ?>"
                        data-name="<?php echo htmlspecialchars($provider['provider_name']); ?>"
                        data-total="<?php echo htmlspecialchars($provider['total_games']); ?>"
                        data-with-image="<?php echo htmlspecialchars($provider['with_image']); ?>"
                        data-without-image="<?php echo htmlspecialchars($provider['without_image']); ?>"
                        data-percentage="<?php echo htmlspecialchars($provider['percentage_with_image']); ?>">
                    <?php echo htmlspecialchars($provider['provider_name']); ?> 
                    (<?php echo htmlspecialchars($provider['server']); ?>)
                    <?php if ($provider['total_games'] > 0): ?>
                        - Selesai: <?php echo $provider['with_image']; ?> (<?php echo $provider['percentage_with_image']; ?>%)
                    <?php endif; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <form id="uploadGameImagesForm" action="" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="selected_provider_code" id="selectedProviderCode">
        <input type="hidden" name="selected_provider_server" id="selectedProviderServer">
        <input type="hidden" name="selected_provider_name" id="selectedProviderName">
        <input type="hidden" name="submit_images" value="1">

        <div id="gameListContainer" class="mt-4">
            <p class="text-center text-muted">Pilih provider untuk menampilkan daftar game.</p>
        </div>

        <button type="submit" class="btn btn-success mt-4 w-100" id="submitImagesBtn" style="display: none;">
            <i class="fas fa-cloud-upload-alt"></i> Perbarui Gambar Game
        </button>
    </form>
</div>

<div class="loading-overlay" id="loadingOverlay">
    <div class="spinner-border text-light" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
    <div class="loading-text">Memproses Gambar...</div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
    const providerSelect = $('#providerSelect');
    const gameListContainer = $('#gameListContainer');
    const submitImagesBtn = $('#submitImagesBtn');
    const selectedProviderCode = $('#selectedProviderCode');
    const selectedProviderServer = $('#selectedProviderServer');
    const selectedProviderName = $('#selectedProviderName');
    const loadingOverlay = $('#loadingOverlay');

    providerSelect.on('change', function() {
        const providerCode = $(this).val();
        const providerServer = $(this).find('option:selected').data('server');
        const providerName = $(this).find('option:selected').data('name');
        
        // Ambil data statistik dari atribut data
        const totalGames = $(this).find('option:selected').data('total');
        const gamesWithImage = $(this).find('option:selected').data('with-image');
        const gamesWithoutImage = $(this).find('option:selected').data('without-image');
        const percentageWithImage = $(this).find('option:selected').data('percentage');

        if (providerCode) {
            loadingOverlay.css('display', 'flex'); // Show loading
            gameListContainer.html('<p class="text-center text-muted">Memuat game...</p>');
            submitImagesBtn.hide();

            selectedProviderCode.val(providerCode);
            selectedProviderServer.val(providerServer);
            selectedProviderName.val(providerName);

            // AJAX call to get games for upload
            $.ajax({
                url: 'ajax_get_games_for_upload.php',
                method: 'GET',
                data: {
                    provider_code: providerCode,
                    provider_server: providerServer,
                    key: '<?php echo $allowed_secret_key; ?>' // Pass security key
                },
                dataType: 'json',
                success: function(response) {
                    loadingOverlay.hide(); // Hide loading
                    if (response.success) {
                        let gamesHtml = `
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="m-0">Daftar Game (${providerName})</h5>
                                <div class="provider-stats">
                                    <div class="stat-item complete">
                                        <i class="fas fa-check-circle"></i> Selesai: ${gamesWithImage} (${percentageWithImage}%)
                                    </div>
                                    <div class="stat-item incomplete">
                                        <i class="fas fa-times-circle"></i> Belum: ${gamesWithoutImage}
                                    </div>
                                    <div class="stat-item total">
                                        <i class="fas fa-database"></i> Total: ${totalGames}
                                    </div>
                                </div>
                            </div>
                            <hr class="text-muted">
                        `;
                        if (response.games.length > 0) {
                            gamesHtml += '<p class="text-muted">Game yang memerlukan gambar:</p>';
                            $.each(response.games, function(index, game) {
                                const googleSearchQuery = encodeURIComponent(`${game.game_name} ${providerName} Logo`);
                                const googleSearchLink = `https://www.google.com/search?q=${googleSearchQuery}&tbm=isch&udm=2`;

                                gamesHtml += `
                                    <div class="game-card">
                                        <div class="game-info">
                                            <h6>
                                                ${game.game_name} (<small>${game.game_code}</small>)
                                            </h6>
                                            <p class="m-0"><small>Tipe: ${game.game_type} | Status: ${game.game_status}</small></p>
                                            <div class="action-button-group mt-2">
                                                <a href="${googleSearchLink}" target="_blank" class="btn btn-primary action-button" title="Cari Logo ${game.game_name} di Google">
                                                    <i class="fas fa-search"></i> Cari Logo
                                                </a>
                                                <button type="button" class="btn btn-success action-button copy-button" title="Salin Nama Game" data-gamename="${game.game_name}">
                                                    <i class="fas fa-copy"></i> Salin
                                                </button>
                                            </div>
                                        </div>
                                        <div class="form-group input-group">
                                            <label for="image_${game.game_code}" class="form-label visually-hidden">Gambar Baru:</label>
                                            <input type="file" class="form-control" id="image_${game.game_code}" name="game_image_${game.game_code}" accept="image/jpeg, image/png, image/gif, image/webp">
                                            <button type="button" class="btn btn-danger reset-file-btn" title="Hapus Gambar" onclick="resetFileInput(this)">
                                                <i class="fas fa-trash-alt"></i> Hapus
                                            </button>
                                        </div>
                                    </div>
                                `;
                            });
                            gameListContainer.html(gamesHtml);
                            submitImagesBtn.show();
                        } else {
                            gamesHtml += '<p class="text-center text-success">Semua game dari provider ini sudah memiliki gambar lokal yang diupload atau tidak ditemukan game.</p>';
                            gameListContainer.html(gamesHtml);
                            submitImagesBtn.hide();
                        }
                    } else {
                        gameListContainer.html('<p class="text-center text-danger">Error: ' + response.message + '</p>');
                        submitImagesBtn.hide();
                    }
                },
                error: function(xhr, status, error) {
                    loadingOverlay.hide(); // Hide loading
                    gameListContainer.html('<p class="text-center text-danger">Terjadi kesalahan saat memuat game: ' + error + '</p>');
                    submitImagesBtn.hide();
                    console.error("AJAX Error:", status, error, xhr.responseText);
                }
            });
        } else {
            gameListContainer.html('<p class="text-center text-muted">Pilih provider untuk menampilkan daftar game.</p>');
            submitImagesBtn.hide();
            selectedProviderCode.val('');
            selectedProviderServer.val('');
            selectedProviderName.val('');
        }
    });

    // Handle form submission with loading overlay
    $('#uploadGameImagesForm').on('submit', function() {
        loadingOverlay.css('display', 'flex'); // Show loading on form submit
        submitImagesBtn.prop('disabled', true);
    });

    // Handle copy button click
    $(document).on('click', '.copy-button', function() {
        const gameName = $(this).data('gamename');
        navigator.clipboard.writeText(gameName).then(() => {
            const originalTitle = $(this).attr('title');
            const originalHtml = $(this).html();
            $(this).html('<i class="fas fa-check"></i> Tersalin!');
            setTimeout(() => {
                $(this).attr('title', originalTitle);
                $(this).html(originalHtml);
            }, 1500);
        }).catch(err => {
            console.error('Failed to copy text: ', err);
        });
    });
});

/**
 * Resets the file input associated with the clicked button.
 * @param {HTMLButtonElement} button The button element that was clicked.
 */
function resetFileInput(button) {
    // Find the parent .input-group div
    const inputGroup = $(button).closest('.input-group');
    // Find the input[type="file"] within that group
    const fileInput = inputGroup.find('input[type="file"]');
    
    // Clone the input and replace it to clear the selected file
    const newFileInput = fileInput.clone(true);
    fileInput.replaceWith(newFileInput);
}
</script>

</body>
</html>