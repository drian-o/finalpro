<?php
// FILE: proses/get_balance.php

// Sertakan kelas API dari direktori yang benar
include_once __DIR__ . '/../../classes/class.nexusggr.php';
include_once __DIR__ . '/../../classes/class.exa.php';

// Sertakan file kredensial untuk Nexus
include_once __DIR__ . '/../../classes/connectAPI.php';

header('Content-Type: application/json');

$response = [
    'status' => 'error',
    'message' => 'Invalid request'
];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_api']) && isset($_POST['id_user'])) {
    $id_api = $_POST['id_api'];
    $id_user = $_POST['id_user'];
    
    try {
        if ($id_api == 'nexus') {
            // Instansiasi API Nexus dengan kredensial yang diambil dari connectAPI.php
            $nexus = new API($user_agent, $signature);
            $apiResponse = $nexus->money_info_user($id_user);
            echo json_encode($apiResponse);
            exit;
        } else if ($id_api == 'exa') {
            // Instansiasi API Exa tanpa parameter (sesuai dengan cara kerjanya)
            $exa = new GameXaAPI();
            $apiResponse = $exa->getPlayerBalance($id_user);
            echo json_encode($apiResponse);
            exit;
        }
    } catch (Exception $e) {
        $response['message'] = 'API Error: ' . $e->getMessage();
        echo json_encode($response);
        exit;
    }
}

echo json_encode($response);
?>