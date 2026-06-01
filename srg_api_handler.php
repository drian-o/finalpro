<?php
// Ensure this path is correct based on where you save class.srg.php
require_once 'classes/class.exa.php';

header('Content-Type: application/json');

$SRG = new SRGConnect();

$input = json_decode(file_get_contents('php://input'), true);

$command = $input['command'] ?? null;
$data = $input['data'] ?? null;

$response = [
    'code' => 500,
    'message' => 'Invalid Command or Internal Server Error',
    'data' => null
];

switch ($command) {
    case 'create_user':
        if (isset($data['username'])) {
            $result = $SRG->create_user($data['username']);
            $response = [
                'code' => $result ? 200 : 400,
                'message' => $result ? 'User Created Successfully' : 'Failed to create user (username might exist or invalid format)',
                'data' => $result
            ];
        } else {
            $response['message'] = 'Username is required for create_user.';
        }
        break;
    case 'get_balance_user':
        if (isset($data['username'])) {
            $result = $SRG->get_balance_user($data['username']);
            $response = [
                'code' => $result !== false ? 200 : 404,
                'message' => $result !== false ? 'Success' : 'User not found or failed to get balance',
                'data' => $result !== false ? ['balance' => $result] : null
            ];
        } else {
            $response['message'] = 'Username is required for get_balance_user.';
        }
        break;
    case 'user_info':
        if (isset($data['username'])) {
            $result = $SRG->user_info($data['username']);
            $response = [
                'code' => $result !== false ? 200 : 404,
                'message' => $result !== false ? 'Success' : 'User not found or failed to get user info',
                'data' => $result
            ];
        } else {
            $response['message'] = 'Username is required for user_info.';
        }
        break;
    case 'deposit':
        if (isset($data['username']) && isset($data['amount'])) {
            $result = $SRG->deposit($data['username'], $data['amount']);
            $response = [
                'code' => $result !== false ? 200 : 400,
                'message' => $result !== false ? 'Success' : 'Failed to deposit',
                'data' => $result
            ];
        } else {
            $response['message'] = 'Username and amount are required for deposit.';
        }
        break;
    case 'withdraw':
        if (isset($data['username']) && isset($data['amount'])) {
            $result = $SRG->withdraw($data['username'], $data['amount']);
            $response = [
                'code' => $result !== false ? 200 : 400,
                'message' => $result !== false ? 'Success' : 'Failed to withdraw',
                'data' => $result
            ];
        } else {
            $response['message'] = 'Username and amount are required for withdraw.';
        }
        break;
    case 'get_balance_agent':
        $result = $SRG->get_balance_agent();
        $response = [
            'code' => $result !== false ? 200 : 404,
            'message' => $result !== false ? 'Success' : 'Failed to get agent balance',
            'data' => $result !== false ? ['agent_balance' => $result] : null
        ];
        break;
    case 'get_providerlist':
        $result = $SRG->get_providerlist();
        $response = [
            'code' => $result !== false ? 200 : 400,
            'message' => $result !== false ? 'Success' : 'Failed to get provider list',
            'data' => $result
        ];
        break;
    case 'get_gamelist':
        $result = $SRG->get_gamelist();
        $response = [
            'code' => $result !== false ? 200 : 400,
            'message' => $result !== false ? 'Success' : 'Failed to get game list',
            'data' => $result
        ];
        break;
    case 'launchgame':
        if (isset($data['username']) && isset($data['game_code']) && isset($data['provider_code'])) {
            $result = $SRG->launchgame($data['username'], $data['game_code'], $data['provider_code']);
            $response = [
                'code' => $result !== false ? 200 : 400,
                'message' => $result !== false ? 'Success' : 'Failed to launch game',
                'data' => $result !== false ? ['url' => $result] : null
            ];
        } else {
            $response['message'] = 'Username, game_code, and provider_code are required for launchgame.';
        }
        break;
    case 'gamehistory':
        if (isset($data['username'])) {
            $page = $data['page'] ?? 1;
            $result = $SRG->gamehistory($data['username'], $page);
            $response = [
                'code' => $result !== false ? 200 : 400,
                'message' => $result !== false ? 'Success' : 'Failed to get game history',
                'data' => $result
            ];
        } else {
            $response['message'] = 'Username is required for gamehistory.';
        }
        break;
    case 'reset_users_balance':
        $result = $SRG->reset_users_balance();
        $response = [
            'code' => $result !== false ? 200 : 400,
            'message' => $result !== false ? 'Success' : 'Failed to reset user balances',
            'data' => $result
        ];
        break;
    case 'delete_all_user':
        $result = $SRG->delete_all_user();
        $response = [
            'code' => $result !== false ? 200 : 400,
            'message' => $result !== false ? 'Success' : 'Failed to delete all users',
            'data' => $result
        ];
        break;
    default:
        $response['message'] = 'Unknown API command.';
        break;
}

echo json_encode($response);
?>