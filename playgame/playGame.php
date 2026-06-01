<?php

session_start();
require_once '../koneksi.php';
require_once '../classes/class.exa.php';
require_once '../classes/class.nexusggr.php';
require_once '../classes/connectAPI.php';

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

$game_uid = isset($_GET['game_uid']) ? $_GET['game_uid'] : null;
$provider_code = isset($_GET['provider_code']) ? $_GET['provider_code'] : null;
$game_type = isset($_GET['game_type']) ? $_GET['game_type'] : null;
$server = isset($_GET['server']) ? $_GET['server'] : null;

if (!isset($_SESSION['id_anggota'])) {
    $msg = "Silakan login untuk bermain game.";
    header("Location: ../login?msg=" . urlencode($msg));
    exit();
}

$id_anggota_session = $_SESSION['id_anggota'];

$player_id_gamexa = null;

if (!$koneksi) {
    echo htmlspecialchars("Koneksi database gagal.");
    exit();
}

$query = "SELECT id_sigma, id_nexus, saldo_anggota FROM anggota WHERE id_anggota = ?";
if ($stmt = mysqli_prepare($koneksi, $query)) {
    mysqli_stmt_bind_param($stmt, "i", $id_anggota_session);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $id_sigma_from_db, $id_nexus_from_db, $local_db_balance);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    if ($id_sigma_from_db !== null) {
        $player_id_gamexa = $id_sigma_from_db;
    }
} else {
    echo htmlspecialchars("Gagal menyiapkan query database: " . mysqli_error($koneksi));
    exit();
}

if (!$player_id_gamexa) {
    echo htmlspecialchars("id_sigma tidak ditemukan untuk akun ini. Pastikan pemain terdaftar di GameXa.");
    exit();
}
if (!$id_nexus_from_db) {
    echo htmlspecialchars("id_nexus tidak ditemukan untuk akun ini. Pastikan pemain terdaftar di Nexus.");
    exit();
}

if (!$game_uid || !$provider_code || !$game_type) {
    echo htmlspecialchars("Parameter game tidak lengkap.");
    exit();
}

// --- Logika Sinkronisasi Saldo (disalin dari update_saldo.php) ---
try {
    $GameXaAPI = new GameXaAPI();
    $NexusAPI = new API($user_agent, $signature);

    $koneksi->begin_transaction();

    $nexus_balance_response = $NexusAPI->money_info_user($id_nexus_from_db);
    
    $nexus_current_balance = 0;
    if (($nexus_balance_response['status'] ?? 0) == 1 && isset($nexus_balance_response['user']['balance'])) {
        $nexus_current_balance = floatval($nexus_balance_response['user']['balance']);
    }
    
    $transferred_amount = 0;
    if ($nexus_current_balance > 0) {
        $reference_id_nexus = "withdraw_" . uniqid(time());
        $nexus_withdraw_response = $NexusAPI->user_withdraw($id_nexus_from_db, $nexus_current_balance);

        if (($nexus_withdraw_response['status'] ?? 0) == 1) {
            $transferred_amount = $nexus_current_balance;
        } else {
            throw new Exception("Withdraw dari Nexus gagal: " . ($nexus_withdraw_response['msg'] ?? 'Error tidak diketahui.'));
        }
    }
    
    $exa_total_deposit_amount = $transferred_amount;
    
    if ($exa_total_deposit_amount > 0) {
        $reference_id_exa = "deposit_" . uniqid(time());
        $exa_deposit_response = $GameXaAPI->depositToPlayer($player_id_gamexa, $exa_total_deposit_amount, $reference_id_exa);

        if (!($exa_deposit_response['success'] ?? false)) {
            throw new Exception("Deposit ke GameXa gagal: " . ($exa_deposit_response['message'] ?? 'Error tidak diketahui.'));
        }
    }

    $gamexa_balance_response = $GameXaAPI->getPlayerBalance($player_id_gamexa);
    
    $gamexa_final_balance = 0;
    if (($gamexa_balance_response['success'] ?? false) && isset($gamexa_balance_response['data']['balance'])) {
        $gamexa_final_balance = floatval($gamexa_balance_response['data']['balance']);
    } else {
        throw new Exception("Gagal mendapatkan saldo akhir dari GameXa.");
    }
    
    $stmt_update_local_saldo = $koneksi->prepare("UPDATE anggota SET saldo_anggota = ? WHERE id_sigma = ?");
    $stmt_update_local_saldo->bind_param("ds", $gamexa_final_balance, $player_id_gamexa); 
    
    if (!$stmt_update_local_saldo->execute()) {
        throw new Exception("DB Error (execute update local saldo): " . $stmt_update_local_saldo->error);
    }
    $stmt_update_local_saldo->close();
    
    $koneksi->commit();

    $_SESSION['saldo_anggota'] = $gamexa_final_balance; 

} catch (Exception $e) {
    $koneksi->rollback();
    echo htmlspecialchars("Terjadi kesalahan sistem saat sinkronisasi saldo: " . $e->getMessage());
    exit();
}

// --- Logika Peluncuran Game ---
try {
    $currency_for_game = 'IDR';
    
    $gameXaAPI = new GameXaAPI();
    $launch_response = $gameXaAPI->launchGame($player_id_gamexa, $game_uid, $currency_for_game);

    if ($launch_response['success'] && isset($launch_response['data']['game_launch_url'])) {
        $game_url = $launch_response['data']['game_launch_url'];
        header("Location: " . $game_url);
        exit();
    } else {
        $errorMessage = $launch_response['message'] ?? 'Error tidak diketahui saat meluncurkan game.';
        echo "Gagal meluncurkan game: " . htmlspecialchars($errorMessage);
        exit();
    }
} catch (Exception $e) {
    echo "Terjadi kesalahan saat meluncurkan game: " . htmlspecialchars($e->getMessage());
    exit();
}

?>