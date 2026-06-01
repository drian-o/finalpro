<?php
include '../config/koneksi.php';

function gameCallback($req) { 
    $user_code = $req["user_code"];
    $game_type = $req["game_type"];
    $user_total_credit = $req["user_total_credit"];
    $user_total_debit = $req["user_total_debit"];
    $user_balance = $req["user_balance"];
    global $conn;
    $sql = "SELECT * FROM tb_user WHERE username='$user_code'";
    $result = $conn->query($sql);
    if ($result->num_rows == 0) {
        return json_encode(["status"=>0, "msg"=>"INVALID_USER"]);
    }
    $row = $result->fetch_assoc();
    $cuid = $row['cuid'];
    
    
    if($game_type == "slot") {
        $slot_data = $req["slot"];
        $txn_id = $slot_data["txn_id"];
        $txn_type = $slot_data["txn_type"];
        $bet = $slot_data["bet"];
        $win = $slot_data["win"];
        $round_id = $slot_data["round_id"];
        $provider_code = $slot_data["provider_code"];
        $game_code = $slot_data["game_code"];
        $type = $slot_data["type"];
        $is_buy = 0;
        $is_call = 0;
        $user_before_balance = $slot_data["user_before_balance"];
        $user_after_balance = $slot_data["user_after_balance"];
        $agent_before_balance = $slot_data["agent_before_balance"];
        $agent_after_balance = $slot_data["agent_after_balance"];
        $created_at = $slot_data["created_at"];
        $created_at = substr(str_replace("T"," ",$created_at), 0, 19);
        $result_user_balance = $user_balance - $bet + $win;
        $sql_user_balance = "UPDATE tb_balance SET active='$result_user_balance' WHERE userID='$cuid'";
        $conn->query($sql_user_balance);
        $sql = "INSERT INTO slot_game_histories(roundId, userCode, providerCode, 
            gameCode, spinType, bet, win, userBalance, userTotalDebit, 
            userTotalCredit, txnId, txnType, isBuy, isCall, userBeforeBalance, 
            userAfterBalance, agentBeforeBalance, agentAfterBalance, spinedAt, 
            createdAt, updatedAt) VALUES('$round_id','$user_code','$provider_code',
            '$game_code','$type','$bet','$win','$user_balance','$user_total_debit',
            '$user_total_credit','$txn_id','$txn_type','$is_buy','$is_call',
            '$user_before_balance','$user_after_balance','$agent_before_balance',
            '$agent_after_balance', NOW(), '$created_at', NOW())";		

if ($conn->query($sql) === TRUE) {
    return json_encode([
        "status" => 1,
        "user_balance" => $result_user_balance
    ]);
} else {
    return json_encode([
        "status" => 0,
        "error" => $conn->error
    ]);		 
    }
}
    
}

//CONTOH 
$req = json_decode(file_get_contents('php://input'), true);
$response = gameCallback($req);
echo $response;
?>
