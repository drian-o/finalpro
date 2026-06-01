<?php

// Sertakan file kelas Acenet
require_once __DIR__ . '/classes/class.acenet.php';

// Fungsi untuk menghasilkan userCode acak (contoh sederhana)
function generateRandomUserCode($length = 8) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return 'user_' . $randomString; // Menambahkan prefix untuk identifikasi
}

// Inisialisasi variabel untuk tampilan
$newUserCode = generateRandomUserCode();
$creation_status_message = '';
$raw_api_response_display = 'Tidak ada respon mentah.';
$api_request_details_display = 'Tidak ada detail request.';
$response = null; // Untuk menyimpan respon API

try {
    // --- Detail Request ---
    // Karena Acenet menggunakan POST dengan body JSON, kita akan menampilkan data yang dikirimkan.
    // Data ini adalah array yang akan digabungkan dan di-encode ke JSON di metode callApi.
    $requestData = [
        "action" => "user_create",
        "user_code" => $newUserCode,
        // Tambahkan identifier dan agent_voucher untuk kelengkapan, meskipun sudah di handle di class
        "identifier" => ACENET_IDENTIFIER,
        "agent_voucher" => ACENET_AGENT_VOUCHER,
    ];
    $api_request_details_display = "<pre>" . htmlspecialchars(json_encode($requestData, JSON_PRETTY_PRINT)) . "</pre>";

    // Memanggil metode createUser dari instance $WL
    $response = $WL->createUser($newUserCode);

    // --- Respon Mentah (Jika ada dan berhasil melewati tahap cURL/JSON decode dasar) ---
    // Penting: Respon mentah di sini adalah setelah json_decode, karena metode callApi sudah melempar exception untuk error JSON mentah.
    // Jika Anda ingin respon string mentah sebelum decode, Anda perlu memodifikasi kelas Acenet.
    $raw_api_response_display = "<pre>" . htmlspecialchars(json_encode($response, JSON_PRETTY_PRINT)) . "</pre>";

    // Memeriksa respon dari API
    if (isset($response['status']) && $response['status'] === 1) {
        $creation_status_message = "Pengguna '$newUserCode' berhasil dibuat!";
        $user_data = $response['data'] ?? null;
        if ($user_data) {
            $creation_status_message .= " Detail: " . htmlspecialchars(json_encode($user_data));
        }
    } else {
        $errorMessage = $response['msg'] ?? 'Kesalahan tidak diketahui.';
        $creation_status_message = "Gagal membuat pengguna '$newUserCode'. Pesan API: " . htmlspecialchars($errorMessage);
    }
} catch (Exception $e) {
    // Menangkap exception yang dilempar oleh kelas Acenet (misalnya, cURL error, JSON decode error)
    $creation_status_message = "Terjadi kesalahan saat memanggil API: " . htmlspecialchars($e->getMessage());
    // Jika terjadi exception, mungkin tidak ada $response yang valid untuk ditampilkan,
    // atau $response hanya berisi sebagian jika error terjadi saat json_decode.
    // Jika ingin menampilkan string mentah yang menyebabkan JSON error, Anda bisa memodifikasi callApi
    // untuk mengembalikan string mentah bersama dengan exception.
    // Untuk saat ini, kita akan asumsikan $raw_api_response_display sudah diatur sebelumnya atau kosong.
}

// Tampilan hasil dalam format HTML
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Pembuatan Pengguna Baru Acenet</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f4f4; color: #333; }
        .container { background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1, h2 { color: #0056b3; }
        pre { background-color: #e2e2e2; padding: 15px; border-radius: 5px; overflow-x: auto; white-space: pre-wrap; word-wrap: break-word; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Status Pembuatan Pengguna Baru Acenet</h1>

        <h2>Detail Request yang Dikirim:</h2>
        <?php echo $api_request_details_display; ?>

        <h2>Respon Mentah API:</h2>
        <?php echo $raw_api_response_display; ?>

        <h2>Status Pembuatan Pengguna:</h2>
        <p class="<?php echo (strpos($creation_status_message, 'berhasil dibuat') !== false) ? 'success' : 'error'; ?>">
            <?php echo $creation_status_message; ?>
        </p>

        <p>User Code yang Dicoba: <strong><?php echo htmlspecialchars($newUserCode); ?></strong></p>

    </div>
</body>
</html>