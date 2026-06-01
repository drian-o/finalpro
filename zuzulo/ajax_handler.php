<?php
// ajax_handler.php

// Helper function untuk mengirim respons JSON dan menghentikan skrip
function send_json_response($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

// State file untuk menyimpan progres antar request
$state_file = __DIR__ . '/proses_update_game.json';

// Routing berdasarkan parameter 'action'
$action = $_GET['action'] ?? '';

// Memuat file-file inti
if (session_status() == PHP_SESSION_NONE) session_start();
set_time_limit(600);
include_once '../koneksi.php';
include_once '../classes/chaos.php';

// Validasi dasar sebelum menjalankan action apapun
if (!isset($koneksi) || !($koneksi instanceof mysqli) || !isset($WL) || !($WL instanceof zulhayker) || !isset($_SESSION['kode_admin'])) {
    send_json_response(['status' => 'error', 'message' => 'Validasi sistem gagal. Pastikan Anda login dan konfigurasi benar.']);
}

// Fungsi-fungsi pembantu (logika inti Anda)
function normalize_api_game_type_for_mapping($api_game_type) {
    $normalized = strtoupper(trim($api_game_type));
    $normalized = str_replace(' ', '', $normalized);
    $specific_normalizations = ['LIVECASINO' => 'LC', 'E-GAMES' => 'ES', 'EGAMES' => 'ES', 'LOTTERY' => 'LK', 'KENO' => 'LK', 'TABLEGAMES' => 'TABLE', 'VIDEOPOKER' => 'VIDEOPOKER', 'MINIGAME' => 'MINIGAME', 'CLASSICGAMES' => 'CLASSICGAMES', 'SCRATCHCARDS' => 'SCRATCHCARDS', 'VIRTUALGAMES' => 'VIRTUALGAMES', 'FISH' => 'FH', 'FISHING' => 'FH'];
    return $specific_normalizations[$normalized] ?? $normalized;
}

function get_target_table_from_api_type($api_game_type_normalized) {
    $type_to_table_map = ['SL' => 'gamelist_slot', 'LC' => 'gamelist_livecasino', 'SB' => 'gamelist_sportsbook', 'ES' => 'gamelist_esports', 'LK' => 'gamelist_lotterykeno', 'PK' => 'gamelist_pokercard', 'FH' => 'gamelist_fishinghunter'];
    $table_name = $type_to_table_map[$api_game_type_normalized] ?? 'gamelist_future';
    $subfolder = str_replace('gamelist_', '', $table_name);
    return ['table' => $table_name, 'subfolder' => $subfolder];
}

function downloadGameBanner($api_image_url, $subfolder, $game_code) {
    if (empty($api_image_url) || empty($game_code)) return '';
    $base_upload_dir = '../uploads/';
    $target_dir = $base_upload_dir . $subfolder . '/';
    if (!is_dir($target_dir)) { if (!mkdir($target_dir, 0775, true)) return $api_image_url; }
    $file_extension = pathinfo(parse_url($api_image_url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
    $safe_game_code = preg_replace('/[^a-zA-Z0-9_-]/', '_', $game_code);
    $local_filepath = $target_dir . $safe_game_code . '.' . $file_extension;
    if (file_exists($local_filepath)) return $local_filepath;
    $context = stream_context_create(['http' => ['ignore_errors' => true]]);
    $image_data = @file_get_contents($api_image_url, false, $context);
    if ($image_data !== false && isset($http_response_header) && strpos($http_response_header[0], '200') !== false) {
        if (@file_put_contents($local_filepath, $image_data)) return $local_filepath;
    }
    return $api_image_url;
}

switch ($action) {
    case 'start':
        $provider_code = 'PR';
        $api_response = $WL->GetGameList($provider_code);

        if ($api_response && isset($api_response['status']) && $api_response['status'] === 'success' && isset($api_response['games'])) {
            $state = [
                'status' => 'processing',
                'provider_code' => $provider_code,
                'total_games' => count($api_response['games']),
                'processed_count' => 0,
                'games' => $api_response['games'],
                'logs' => []
            ];
            file_put_contents($state_file, json_encode($state, JSON_PRETTY_PRINT));
            send_json_response(['status' => 'started', 'total_games' => $state['total_games'], 'message' => 'Proses dimulai. Mengambil ' . $state['total_games'] . ' game dari API.']);
        } else {
            send_json_response(['status' => 'error', 'message' => 'Gagal mengambil daftar game dari API. Pesan: ' . ($api_response['msg'] ?? 'Tidak diketahui')]);
        }
        break;

    case 'process_batch':
        if (!file_exists($state_file)) {
            send_json_response(['status' => 'error', 'message' => 'File state tidak ditemukan. Mohon mulai ulang.']);
        }

        $state = json_decode(file_get_contents($state_file), true);
        $batch_size = 5; // Proses 5 game per request untuk menghindari timeout
        $games_to_process = array_slice($state['games'], $state['processed_count'], $batch_size);
        $logs_for_this_batch = [];

        if (empty($games_to_process)) {
            $state['status'] = 'complete';
            file_put_contents($state_file, json_encode($state));
            send_json_response(['status' => 'complete', 'processed_count' => $state['processed_count'], 'total_games' => $state['total_games'], 'logs' => ["Semua game telah diproses."]]);
            break;
        }
        
        foreach ($games_to_process as $game) {
            $game_code_api = $game['game_code'] ?? null;
            $game_name_api = $game['game_name'] ?? 'N/A';

            if (empty($game_code_api)) {
                $logs_for_this_batch[] = "DILEWATI: Game '" . htmlspecialchars($game_name_api) . "' tidak memiliki game_code.";
                $state['processed_count']++;
                continue;
            }

            $log_prefix = "Memproses '" . htmlspecialchars($game_name_api) . "': ";
            
            $table_info = get_target_table_from_api_type(normalize_api_game_type_for_mapping($game['game_type'] ?? ''));
            $target_table = $table_info['table'];
            
            $banner_for_db = downloadGameBanner($game['game_image'] ?? '', $table_info['subfolder'], $game_code_api);
            
            $sql_upsert_game = "INSERT INTO `{$target_table}` (sort, lang, game_code, game_name, banner, status, provider, frbavailable, provideragent, game_vendor) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE game_name = VALUES(game_name), status = VALUES(status), banner = IF(banner LIKE '../uploads/%', banner, VALUES(banner)), game_vendor = VALUES(game_vendor)";
            $stmt = $koneksi->prepare($sql_upsert_game);
            
            $status_api = (string)($game['game_status'] ?? '0');
            $provider_api = $game['game_provider'] ?? $state['provider_code'];

            $stmt->bind_param("ssssssssss", $sort, $lang, $game_code, $name, $banner, $status, $provider, $frb, $p_agent, $vendor);
            $sort = '0'; $lang = 'id'; $game_code = $game_code_api; $name = $game_name_api; $banner = $banner_for_db; $status = $status_api; $provider = $provider_api; $frb = $status_api; $p_agent = $provider_api; $vendor = $game['game_vendor'] ?? null;
            
            if ($stmt->execute()) {
                if ($stmt->affected_rows == 1) $logs_for_this_batch[] = $log_prefix . "Game baru berhasil ditambahkan.";
                else if ($stmt->affected_rows > 1) $logs_for_this_batch[] = $log_prefix . "Data game berhasil diperbarui.";
            } else {
                $logs_for_this_batch[] = $log_prefix . "ERROR Database: " . $stmt->error;
            }
            $stmt->close();
            $state['processed_count']++;
        }

        $state['logs'] = array_merge($state['logs'], $logs_for_this_batch);
        file_put_contents($state_file, json_encode($state));

        send_json_response(['status' => 'processing', 'processed_count' => $state['processed_count'], 'total_games' => $state['total_games'], 'logs' => $logs_for_this_batch]);
        break;

    case 'finalize':
        if (file_exists($state_file)) {
            unlink($state_file);
        }
        send_json_response(['status' => 'finalized', 'message' => 'Proses selesai dan file sementara telah dibersihkan.']);
        break;

    default:
        send_json_response(['status' => 'error', 'message' => 'Action tidak valid.']);
        break;
}