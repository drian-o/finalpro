<?php
// test_all_nexus_api.php
// Halaman untuk menguji semua fungsi dari Nexus API

session_start();

// --- DEPENDENSI & SETUP ---
include_once 'koneksi.php'; 
include_once 'classes/class.nexusggr.php';
include_once 'classes/connectAPI.php'; // Sertakan ini untuk kredensial Nexus

$message = "";
$response_data = null;
$nexus_api = null;
$error = null;

// Ganti dengan user_code yang sudah ada di database Anda untuk pengujian
$default_user_code = "demouser";
$default_provider_code = "PRAGMATIC"; // Ganti dengan kode provider yang valid
$default_game_code = "vs20doghouse"; // Ganti dengan kode game yang valid

try {
    // Inisialisasi API Nexus dengan kredensial dari connectAPI.php
    $nexus_api = new API($user_agent, $signature);
} catch (Exception $e) {
    $error = "Kesalahan inisialisasi API: " . $e->getMessage();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($nexus_api)) {
    $action = $_POST['action'] ?? '';
    $user_code = $_POST['user_code'] ?? $default_user_code;
    $amount = (float)($_POST['amount'] ?? 0);
    $provider_code = $_POST['provider_code'] ?? $default_provider_code;
    $game_code = $_POST['game_code'] ?? $default_game_code;
    $lang = $_POST['lang'] ?? 'idr';

    try {
        switch ($action) {
            case 'money_info':
                $response_data = $nexus_api->money_info();
                break;
            case 'money_info_user':
                $response_data = $nexus_api->money_info_user($user_code);
                break;
            case 'user_deposit':
                $response_data = $nexus_api->user_deposit($user_code, $amount);
                break;
            case 'user_create':
                $response_data = $nexus_api->user_create($user_code);
                break;
            case 'game_launch':
                $response_data = $nexus_api->game_launch($user_code, $provider_code, $game_code, $lang);
                break;
            case 'user_withdraw':
                $response_data = $nexus_api->user_withdraw($user_code, $amount);
                break;
            case 'provider_list':
                $response_data = $nexus_api->provider_list();
                break;
            case 'game_list':
                $response_data = $nexus_api->game_list($provider_code);
                break;
            case 'history_bet':
                $response_data = $nexus_api->history_bet();
                break;
            default:
                $message = "Aksi tidak valid.";
                break;
        }
    } catch (Exception $e) {
        $error = "Terjadi kesalahan saat memanggil fungsi {$action}: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nexus API Tester</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f4f4; color: #333; }
        .container { max-width: 900px; margin: auto; padding: 20px; background-color: #fff; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { text-align: center; color: #0056b3; }
        .function-section { margin-bottom: 30px; padding: 15px; border: 1px solid #ddd; border-radius: 8px; background-color: #fafafa; }
        .function-section h2 { margin-top: 0; color: #007BFF; }
        .form-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 10px; align-items: end; }
        .form-container label { font-weight: bold; }
        .form-container input, .form-container select { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .form-container button { width: auto; padding: 10px 15px; border: none; border-radius: 4px; color: white; cursor: pointer; transition: background-color 0.2s; }
        .form-container button:hover { opacity: 0.9; }
        .btn-primary { background-color: #007BFF; }
        .btn-success { background-color: #28a745; }
        .btn-warning { background-color: #ffc107; color: #333; }
        .btn-danger { background-color: #dc3545; }
        .response-box { margin-top: 20px; padding: 15px; background-color: #333; color: white; border-radius: 8px; overflow-x: auto; }
        pre { margin: 0; white-space: pre-wrap; word-wrap: break-word; }
        .error { color: red; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Nexus API Tester</h1>
        <p style="text-align: center;">Halaman ini membantu Anda menguji semua fungsi di `class.nexusggr.php`.</p>
        <hr>

        <?php if ($error): ?>
            <div class="response-box error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="function-section">
            <h2>Agent Balance</h2>
            <p><strong>Fungsi:</strong> <code>money_info</code></p>
            <form method="post" action="" class="form-container">
                <button type="submit" name="action" value="money_info" class="btn-primary">money_info</button>
            </form>
        </div>

        <div class="function-section">
            <h2>User Balance & Transactions</h2>
            <p><strong>Fungsi:</strong> <code>money_info_user</code>, <code>user_deposit</code>, <code>user_withdraw</code></p>
            <form method="post" action="" class="form-container">
                <div>
                    <label for="user_code">User Code:</label>
                    <input type="text" name="user_code" value="<?php echo htmlspecialchars($default_user_code); ?>" required>
                </div>
                <div>
                    <label for="amount">Amount:</label>
                    <input type="number" name="amount" value="1000" required>
                </div>
                <button type="submit" name="action" value="money_info_user" class="btn-primary">money_info_user</button>
                <button type="submit" name="action" value="user_deposit" class="btn-success">user_deposit</button>
                <button type="submit" name="action" value="user_withdraw" class="btn-danger">user_withdraw</button>
            </form>
        </div>
        
        <div class="function-section">
            <h2>Create New User</h2>
            <p><strong>Fungsi:</strong> <code>user_create</code></p>
            <form method="post" action="" class="form-container">
                <div>
                    <label for="user_code_create">User Code:</label>
                    <input type="text" name="user_code" id="user_code_create" placeholder="contoh: testuser123" required>
                </div>
                <button type="submit" name="action" value="user_create" class="btn-success">user_create</button>
            </form>
        </div>

        <div class="function-section">
            <h2>Get Provider List</h2>
            <p><strong>Fungsi:</strong> <code>provider_list</code></p>
            <form method="post" action="" class="form-container">
                <button type="submit" name="action" value="provider_list" class="btn-primary">provider_list</button>
            </form>
        </div>

        <div class="function-section">
            <h2>Get Game List</h2>
            <p><strong>Fungsi:</strong> <code>game_list</code></p>
            <form method="post" action="" class="form-container">
                <div>
                    <label for="provider_code">Provider Code:</label>
                    <input type="text" name="provider_code" value="<?php echo htmlspecialchars($default_provider_code); ?>" required>
                </div>
                <button type="submit" name="action" value="game_list" class="btn-primary">game_list</button>
            </form>
        </div>
        
        <div class="function-section">
            <h2>Launch Game</h2>
            <p><strong>Fungsi:</strong> <code>game_launch</code></p>
            <form method="post" action="" class="form-container">
                <div>
                    <label for="user_code_launch">User Code:</label>
                    <input type="text" name="user_code" id="user_code_launch" value="<?php echo htmlspecialchars($default_user_code); ?>" required>
                </div>
                <div>
                    <label for="provider_code_launch">Provider Code:</label>
                    <input type="text" name="provider_code" id="provider_code_launch" value="<?php echo htmlspecialchars($default_provider_code); ?>" required>
                </div>
                <div>
                    <label for="game_code_launch">Game Code:</label>
                    <input type="text" name="game_code" id="game_code_launch" value="<?php echo htmlspecialchars($default_game_code); ?>" required>
                </div>
                <div>
                    <label for="lang">Language:</label>
                    <input type="text" name="lang" id="lang" value="idr">
                </div>
                <button type="submit" name="action" value="game_launch" class="btn-success">game_launch</button>
            </form>
        </div>
        
        <div class="function-section">
            <h2>Get Bet History</h2>
            <p><strong>Fungsi:</strong> <code>history_bet</code></p>
            <form method="post" action="" class="form-container">
                <button type="submit" name="action" value="history_bet" class="btn-primary">history_bet</button>
            </form>
        </div>

        <?php if ($response_data !== null): ?>
            <hr>
            <div class="response-box">
                <h3>Respons API:</h3>
                <pre><?php echo htmlspecialchars(json_encode($response_data, JSON_PRETTY_PRINT)); ?></pre>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>