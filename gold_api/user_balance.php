<?php

function getUserBalance($requestData) {
  include '../config/koneksi.php';
  
  if (!isset($requestData["user_code"])) {
    return json_encode([
      "status" => 0,
      "msg" => "USER_CODE_MISSING"
    ]);
  }
  
  $user_code = $conn->real_escape_string($requestData["user_code"]);
  $userQuery = "SELECT * FROM tb_user WHERE username='$user_code'";
  $userResult = $conn->query($userQuery);
  
  if ($userResult->num_rows == 0) {
    return json_encode([
      "status" => 0,
      "msg" => "INVALID_USER"
    ]);	
  }
  
  $userRow = $userResult->fetch_assoc();
  $cuid = $userRow['cuid'];
  
  $balanceQuery = "SELECT * FROM tb_balance WHERE userID='$cuid'";
  $balanceResult = $conn->query($balanceQuery);
  
  if ($balanceResult->num_rows == 0) {
    return json_encode([
      "status" => 0,
      "msg" => "USER_BALANCE_NOT_FOUND"
    ]);
  }
  
  $balanceRow = $balanceResult->fetch_assoc();
  $userBalance = $balanceRow["active"];
  
  if ($userBalance <= 0) {
    return json_encode([
      "status" => 0,
      "msg" => "INSUFFICIENT_USER_FUNDS",
      "user_balance" => $userBalance
    ]);    
  }
  return json_encode([
    "status" => 1,
    "user_balance" => $userBalance
  ]);  
}
$requestData = json_decode(file_get_contents('php://input'), true);

//ini cuma contoh
$response = getUserBalance($requestData);
echo $response;

?>
