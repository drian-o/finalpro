<?php

// Pastikan file class.exa.php di-include
require_once __DIR__ . '/classes/class.exa.php';

$api = new GameXaAPI(); // Instansiasi kelas API Anda

$response = null;
$error = null;

// Tangani pengiriman form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        switch ($action) {
            case 'authenticateAgent':
                $response = $api->authenticateAgent();
                break;

            case 'getCurrentAgentInfo':
                $response = $api->getCurrentAgentInfo();
                break;

            case 'createPlayer':
                $username = $_POST['create_player_username'] ?? '';
                $email = $_POST['create_player_email'] ?? '';
                $password = $_POST['create_player_password'] ?? '';
                $fullName = $_POST['create_player_fullname'] ?? '';
                $phone = $_POST['create_player_phone'] ?? '';
                $currency = $_POST['create_player_currency'] ?? '';
                $response = $api->createPlayer($username, $email, $password, $fullName, $phone, $currency);
                break;

            case 'getPlayers':
                $page = (int)($_POST['get_players_page'] ?? 1);
                $limit = (int)($_POST['get_players_limit'] ?? 10);
                $search = $_POST['get_players_search'] ?? null;
                $status = $_POST['get_players_status'] ?? null;
                $response = $api->getPlayers($page, $limit, $search, $status);
                break;

            case 'getPlayerBalance':
                $playerId = (int)($_POST['get_player_balance_player_id'] ?? 0);
                if ($playerId > 0) {
                    $response = $api->getPlayerBalance($playerId);
                } else {
                    $error = "ID Pemain tidak valid untuk Get Player Balance.";
                }
                break;

            case 'depositToPlayer':
                $playerId = (int)($_POST['deposit_player_id'] ?? 0);
                $amount = (float)($_POST['deposit_amount'] ?? 0.0);
                $referenceId = $_POST['deposit_reference_id'] ?? '';
                if ($playerId > 0 && $amount > 0 && !empty($referenceId)) {
                    $response = $api->depositToPlayer($playerId, $amount, $referenceId);
                } else {
                    $error = "Data tidak lengkap atau tidak valid untuk Deposit.";
                }
                break;

            case 'withdrawFromPlayer':
                $playerId = (int)($_POST['withdraw_player_id'] ?? 0);
                $amount = (float)($_POST['withdraw_amount'] ?? 0.0);
                $referenceId = $_POST['withdraw_reference_id'] ?? '';
                if ($playerId > 0 && $amount > 0 && !empty($referenceId)) {
                    $response = $api->withdrawFromPlayer($playerId, $amount, $referenceId);
                } else {
                    $error = "Data tidak lengkap atau tidak valid untuk Withdraw.";
                }
                break;

            case 'getPlayerTransactions':
                $playerId = (int)($_POST['get_player_transactions_player_id'] ?? 0);
                $page = (int)($_POST['get_player_transactions_page'] ?? 1);
                $limit = (int)($_POST['get_player_transactions_limit'] ?? 50);
                $type = $_POST['get_player_transactions_type'] ?? null;
                if ($playerId > 0) {
                    $response = $api->getPlayerTransactions($playerId, $page, $limit, $type);
                } else {
                    $error = "ID Pemain tidak valid untuk Get Player Transactions.";
                }
                break;

            case 'getGameProviders':
                $response = $api->getGameProviders();
                break;

            case 'getGamesByProvider':
                $providerCode = $_POST['get_games_by_provider_code'] ?? '';
                $page = (int)($_POST['get_games_by_provider_page'] ?? 1);
                $limit = (int)($_POST['get_games_by_provider_limit'] ?? 50000);
                $status = $_POST['get_games_by_provider_status'] ?? null;
                if (!empty($providerCode)) {
                    $response = $api->getGamesByProvider($providerCode, $page, $limit, $status);
                } else {
                    $error = "Kode Penyedia Game tidak boleh kosong.";
                }
                break;

            case 'getAllGames':
                $page = (int)($_POST['get_all_games_page'] ?? 1);
                $limit = (int)($_POST['get_all_games_limit'] ?? 50000);
                $search = $_POST['get_all_games_search'] ?? null;
                $provider = $_POST['get_all_games_provider'] ?? null;
                $type = $_POST['get_all_games_type'] ?? null;
                $status = $_POST['get_all_games_status'] ?? null;
                $response = $api->getAllGames($page, $limit, $search, $provider, $type, $status);
                break;

            case 'launchGame':
                $playerId = (int)($_POST['launch_game_player_id'] ?? 0);
                $gameUid = $_POST['launch_game_uid'] ?? '';
                $currency = $_POST['launch_game_currency'] ?? 'IDR'; // Default currency
                if ($playerId > 0 && !empty($gameUid)) {
                    $response = $api->launchGame($playerId, $gameUid, $currency);
                } else {
                    $error = "ID Pemain atau Game UID tidak boleh kosong.";
                }
                break;

            case 'getAllTransactions':
                $page = (int)($_POST['get_all_transactions_page'] ?? 1);
                $limit = (int)($_POST['get_all_transactions_limit'] ?? 500);
                $search = $_POST['get_all_transactions_search'] ?? null;
                $type = $_POST['get_all_transactions_type'] ?? null;
                $status = $_POST['get_all_transactions_status'] ?? null;
                $startDate = $_POST['get_all_transactions_start_date'] ?? null;
                $endDate = $_POST['get_all_transactions_end_date'] ?? null;
                $response = $api->getAllTransactions($page, $limit, $search, $type, $status, $startDate, $endDate);
                break;

            case 'getTransactionStats':
                $startDate = $_POST['get_transaction_stats_start_date'] ?? '';
                $endDate = $_POST['get_transaction_stats_end_date'] ?? '';
                if (!empty($startDate) && !empty($endDate)) {
                    $response = $api->getTransactionStats($startDate, $endDate);
                } else {
                    $error = "Tanggal mulai dan akhir harus diisi.";
                }
                break;

            case 'CreateUser':
                $username = $_POST['create_user_username'] ?? '';
                $email = $_POST['create_user_email'] ?? null;
                $password = $_POST['create_user_password'] ?? null;
                $fullName = $_POST['create_user_fullname'] ?? null;
                $phone = $_POST['create_user_phone'] ?? null;
                $currency = $_POST['create_user_currency'] ?? 'IDR';
                if (!empty($username)) {
                    $response = $api->CreateUser($username, $email, $password, $fullName, $phone, $currency);
                } else {
                    $error = "Username tidak boleh kosong.";
                }
                break;

            default:
                $error = "Aksi tidak dikenal.";
                break;
        }
    } catch (Exception $e) {
        $error = "Terjadi kesalahan: " . $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GameXaAPI Tester</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f4f4; color: #333; }
        .container { max-width: 1000px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1, h2 { color: #0056b3; }
        form { margin-bottom: 20px; padding: 15px; border: 1px solid #ddd; border-radius: 5px; background-color: #f9f9f9; }
        form label { display: block; margin-bottom: 5px; font-weight: bold; }
        form input[type="text"],
        form input[type="email"],
        form input[type="password"],
        form input[type="number"],
        form input[type="date"],
        form select {
            width: calc(100% - 22px);
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        form button {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        form button:hover {
            background-color: #0056b3;
        }
        .response-section, .error-section {
            background-color: #e9e9e9;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            overflow-x: auto;
        }
        .response-section h3, .error-section h3 {
            margin-top: 0;
            color: #0056b3;
        }
        pre {
            background-color: #eee;
            padding: 10px;
            border-radius: 4px;
            white-space: pre-wrap; /* Ensures long lines wrap */
            word-wrap: break-word; /* Ensures long words break */
        }
        .error {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>GameXaAPI Tester</h1>

        <?php if ($error): ?>
            <div class="error-section">
                <h3>Error:</h3>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php endif; ?>

        <?php if ($response): ?>
            <div class="response-section">
                <h3>API Response:</h3>
                <pre><?php print_r($response); ?></pre>
            </div>
        <?php endif; ?>

        <hr>

        <h2>Test API Functions</h2>

        <form action="" method="POST">
            <h3>Authenticate Agent</h3>
            <input type="hidden" name="action" value="authenticateAgent">
            <button type="submit">Authenticate Agent</button>
        </form>

        <form action="" method="POST">
            <h3>Get Current Agent Info</h3>
            <input type="hidden" name="action" value="getCurrentAgentInfo">
            <button type="submit">Get Current Agent Info</button>
        </form>

        <form action="" method="POST">
            <h3>Create Player</h3>
            <input type="hidden" name="action" value="createPlayer">
            <label for="create_player_username">Username:</label>
            <input type="text" id="create_player_username" name="create_player_username" required>

            <label for="create_player_email">Email:</label>
            <input type="email" id="create_player_email" name="create_player_email" value="test@example.com">

            <label for="create_player_password">Password:</label>
            <input type="password" id="create_player_password" name="create_player_password" value="Password123!">

            <label for="create_player_fullname">Full Name:</label>
            <input type="text" id="create_player_fullname" name="create_player_fullname" value="Test User">

            <label for="create_player_phone">Phone:</label>
            <input type="text" id="create_player_phone" name="create_player_phone" value="+6281234567890">

            <label for="create_player_currency">Currency (e.g., IDR):</label>
            <input type="text" id="create_player_currency" name="create_player_currency" value="IDR">

            <button type="submit">Create Player</button>
        </form>

        <form action="" method="POST">
            <h3>Get Players</h3>
            <input type="hidden" name="action" value="getPlayers">
            <label for="get_players_page">Page:</label>
            <input type="number" id="get_players_page" name="get_players_page" value="1" min="1">

            <label for="get_players_limit">Limit:</label>
            <input type="number" id="get_players_limit" name="get_players_limit" value="10" min="1">

            <label for="get_players_search">Search (Username, Email, Full Name):</label>
            <input type="text" id="get_players_search" name="get_players_search">

            <label for="get_players_status">Status:</label>
            <select id="get_players_status" name="get_players_status">
                <option value="">All</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
            <button type="submit">Get Players</button>
        </form>

        <form action="" method="POST">
            <h3>Get Player Balance</h3>
            <input type="hidden" name="action" value="getPlayerBalance">
            <label for="get_player_balance_player_id">Player ID:</label>
            <input type="number" id="get_player_balance_player_id" name="get_player_balance_player_id" required>
            <button type="submit">Get Player Balance</button>
        </form>

        <form action="" method="POST">
            <h3>Deposit To Player</h3>
            <input type="hidden" name="action" value="depositToPlayer">
            <label for="deposit_player_id">Player ID:</label>
            <input type="number" id="deposit_player_id" name="deposit_player_id" required>

            <label for="deposit_amount">Amount:</label>
            <input type="number" step="0.01" id="deposit_amount" name="deposit_amount" required>

            <label for="deposit_reference_id">Reference ID:</label>
            <input type="text" id="deposit_reference_id" name="deposit_reference_id" value="<?php echo uniqid('deposit_'); ?>" required>
            <button type="submit">Deposit To Player</button>
        </form>

        <form action="" method="POST">
            <h3>Withdraw From Player</h3>
            <input type="hidden" name="action" value="withdrawFromPlayer">
            <label for="withdraw_player_id">Player ID:</label>
            <input type="number" id="withdraw_player_id" name="withdraw_player_id" required>

            <label for="withdraw_amount">Amount:</label>
            <input type="number" step="0.01" id="withdraw_amount" name="withdraw_amount" required>

            <label for="withdraw_reference_id">Reference ID:</label>
            <input type="text" id="withdraw_reference_id" name="withdraw_reference_id" value="<?php echo uniqid('withdraw_'); ?>" required>
            <button type="submit">Withdraw From Player</button>
        </form>

        <form action="" method="POST">
            <h3>Get Player Transactions</h3>
            <input type="hidden" name="action" value="getPlayerTransactions">
            <label for="get_player_transactions_player_id">Player ID:</label>
            <input type="number" id="get_player_transactions_player_id" name="get_player_transactions_player_id" required>

            <label for="get_player_transactions_page">Page:</label>
            <input type="number" id="get_player_transactions_page" name="get_player_transactions_page" value="1" min="1">

            <label for="get_player_transactions_limit">Limit:</label>
            <input type="number" id="get_player_transactions_limit" name="get_player_transactions_limit" value="50" min="1">

            <label for="get_player_transactions_type">Type:</label>
            <select id="get_player_transactions_type" name="get_player_transactions_type">
                <option value="">All</option>
                <option value="deposit">Deposit</option>
                <option value="bet">Bet</option>
                <option value="win">win</option>
                <option value="withdrawal">Withdrawal</option>
            </select>
            <button type="submit">Get Player Transactions</button>
        </form>

        <form action="" method="POST">
            <h3>Get Game Providers</h3>
            <input type="hidden" name="action" value="getGameProviders">
            <button type="submit">Get Game Providers</button>
        </form>

        <form action="" method="POST">
            <h3>Get Games By Provider</h3>
            <input type="hidden" name="action" value="getGamesByProvider">
            <label for="get_games_by_provider_code">Provider Code (e.g., PRAGMATIC):</label>
            <input type="text" id="get_games_by_provider_code" name="get_games_by_provider_code" required>

            <label for="get_games_by_provider_page">Page:</label>
            <input type="number" id="get_games_by_provider_page" name="get_games_by_provider_page" value="1" min="1">

            <label for="get_games_by_provider_limit">Limit:</label>
            <input type="number" id="get_games_by_provider_limit" name="get_games_by_provider_limit" value="50000" min="1">

            <label for="get_games_by_provider_status">Status:</label>
            <select id="get_games_by_provider_status" name="get_games_by_provider_status">
                <option value="">All</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="maintenance">Maintenance</option>
            </select>
            <button type="submit">Get Games By Provider</button>
        </form>

        <form action="" method="POST">
            <h3>Get All Games</h3>
            <input type="hidden" name="action" value="getAllGames">
            <label for="get_all_games_page">Page:</label>
            <input type="number" id="get_all_games_page" name="get_all_games_page" value="1" min="1">

            <label for="get_all_games_limit">Limit:</label>
            <input type="number" id="get_all_games_limit" name="get_all_games_limit" value="50000" min="1">

            <label for="get_all_games_search">Search (Game Name or UID):</label>
            <input type="text" id="get_all_games_search" name="get_all_games_search">

            <label for="get_all_games_provider">Provider Code:</label>
            <input type="text" id="get_all_games_provider" name="get_all_games_provider">

            <label for="get_all_games_type">Game Type:</label>
            <select id="get_all_games_type" name="get_all_games_type">
                <option value="">All</option>
                <option value="slot">Slot</option>
                <option value="table">Table</option>
                <option value="card">Card</option>
                <option value="lottery">Lottery</option>
                <option value="sports">Sports</option>
            </select>

            <label for="get_all_games_status">Status:</label>
            <select id="get_all_games_status" name="get_all_games_status">
                <option value="">All</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="maintenance">Maintenance</option>
            </select>
            <button type="submit">Get All Games</button>
        </form>

        <form action="" method="POST">
            <h3>Launch Game</h3>
            <input type="hidden" name="action" value="launchGame">
            <label for="launch_game_player_id">Player ID:</label>
            <input type="number" id="launch_game_player_id" name="launch_game_player_id" required>

            <label for="launch_game_uid">Game UID (e.g., GATE_OF_OLYMPUS):</label>
            <input type="text" id="launch_game_uid" name="launch_game_uid" required>

            <label for="launch_game_currency">Currency (e.g., IDR, IDR):</label>
            <input type="text" id="launch_game_currency" name="launch_game_currency" value="IDR">
            <button type="submit">Launch Game</button>
        </form>

        <form action="" method="POST">
            <h3>Get All Transactions</h3>
            <input type="hidden" name="action" value="getAllTransactions">
            <label for="get_all_transactions_page">Page:</label>
            <input type="number" id="get_all_transactions_page" name="get_all_transactions_page" value="1" min="1">

            <label for="get_all_transactions_limit">Limit:</label>
            <input type="number" id="get_all_transactions_limit" name="get_all_transactions_limit" value="500" min="1">

            <label for="get_all_transactions_search">Search:</label>
            <input type="text" id="get_all_transactions_search" name="get_all_transactions_search">

            <label for="get_all_transactions_type">Type:</label>
            <select id="get_all_transactions_type" name="get_all_transactions_type">
                <option value="">All</option>
                <option value="deposit">Deposit</option>
                <option value="withdrawal">Withdrawal</option>
                <option value="bet">Bet</option>
                <option value="win">Win</option>
            </select>

            <label for="get_all_transactions_status">Status:</label>
            <select id="get_all_transactions_status" name="get_all_transactions_status">
                <option value="">All</option>
                <option value="completed">Completed</option>
                <option value="pending">Pending</option>
                <option value="failed">Failed</option>
            </select>

            <label for="get_all_transactions_start_date">Start Date (YYYY-MM-DD):</label>
            <input type="date" id="get_all_transactions_start_date" name="get_all_transactions_start_date">

            <label for="get_all_transactions_end_date">End Date (YYYY-MM-DD):</label>
            <input type="date" id="get_all_transactions_end_date" name="get_all_transactions_end_date">
            <button type="submit">Get All Transactions</button>
        </form>

        <form action="" method="POST">
            <h3>Get Transaction Stats</h3>
            <input type="hidden" name="action" value="getTransactionStats">
            <label for="get_transaction_stats_start_date">Start Date (YYYY-MM-DD):</label>
            <input type="date" id="get_transaction_stats_start_date" name="get_transaction_stats_start_date" required>

            <label for="get_transaction_stats_end_date">End Date (YYYY-MM-DD):</label>
            <input type="date" id="get_transaction_stats_end_date" name="get_transaction_stats_end_date" required>
            <button type="submit">Get Transaction Stats</button>
        </form>

        <form action="" method="POST">
            <h3>Create User (Alias)</h3>
            <input type="hidden" name="action" value="CreateUser">
            <label for="create_user_username">Username:</label>
            <input type="text" id="create_user_username" name="create_user_username" required>

            <label for="create_user_email">Email (Optional, will default):</label>
            <input type="email" id="create_user_email" name="create_user_email">

            <label for="create_user_password">Password (Optional, will default):</label>
            <input type="password" id="create_user_password" name="create_user_password">

            <label for="create_user_fullname">Full Name (Optional, will default):</label>
            <input type="text" id="create_user_fullname" name="create_user_fullname">

            <label for="create_user_phone">Phone (Optional, will default):</label>
            <input type="text" id="create_user_phone" name="create_user_phone">

            <label for="create_user_currency">Currency (e.g., IDR, default IDR):</label>
            <input type="text" id="create_user_currency" name="create_user_currency" value="IDR">
            <button type="submit">Create User</button>
        </form>

    </div>
</body>
</html>