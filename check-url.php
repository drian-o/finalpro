<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['url'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method or missing URL.'
    ]);
    exit;
}

$url = $_POST['url'];

// Daftar User-Agent populer
$user_agents = [
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36',
    'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36',
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:107.0) Gecko/20100101 Firefox/107.0',
    'Mozilla/5.0 (iPhone; CPU iPhone OS 16_1_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.1 Mobile/15E148 Safari/604.1',
];
$random_user_agent = $user_agents[array_rand($user_agents)];

// URL referer
$referer_url = "https://www.google.com/";

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_NOBODY, true);

// Opsi canggih
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
curl_setopt($ch, CURLOPT_USERAGENT, $random_user_agent);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Referer: ' . $referer_url
]);

// Eksekusi cURL
$response_data = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$final_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
$curl_info = curl_getinfo($ch);
$error_msg = curl_error($ch);

curl_close($ch);

$response = [
    'success' => false,
    'message' => '',
    'http_code' => $http_code,
    'final_url' => $final_url,
    'curl_info' => $curl_info
];

if ($error_msg) {
    $response['message'] = 'cURL Error: ' . $error_msg;
} elseif ($http_code >= 200 && $http_code < 400) {
    // Pengecekan ekstra untuk URL final setelah redirect
    $final_check_success = false;
    $final_check_http_code = 0;
    
    if ($final_url) {
        $ch_final = curl_init();
        curl_setopt($ch_final, CURLOPT_URL, $final_url);
        curl_setopt($ch_final, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch_final, CURLOPT_HEADER, false);
        curl_setopt($ch_final, CURLOPT_NOBODY, true);
        curl_setopt($ch_final, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch_final, CURLOPT_USERAGENT, $random_user_agent);
        curl_exec($ch_final);
        $final_check_http_code = curl_getinfo($ch_final, CURLINFO_HTTP_CODE);
        curl_close($ch_final);

        if ($final_check_http_code >= 200 && $final_check_http_code < 400) {
            $final_check_success = true;
        }
    }
    
    if ($final_check_success) {
        $response['success'] = true;
        $response['message'] = 'URL is valid and final URL is accessible.';
    } else {
        $response['message'] = 'URL is valid but final URL returned a non-successful status code.';
        $response['http_code'] = $final_check_http_code;
    }
    
} else {
    if ($http_code == 403) {
        $response['message'] = 'Forbidden. Akses ke URL ditolak.';
    } elseif ($http_code == 404) {
        $response['message'] = 'Not Found. Halaman tidak ditemukan.';
    } elseif ($http_code == 450) {
        $response['message'] = 'Blocked. Akses ke URL diblokir.';
    } elseif ($http_code >= 500) {
        $response['message'] = 'Server Error. Terjadi kesalahan pada server game.';
    } else {
        $response['message'] = 'URL returned a non-successful status code.';
    }
}

echo json_encode($response, JSON_PRETTY_PRINT);
?>