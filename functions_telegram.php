<?php
// File: functions_telegram.php
// Pastikan koneksi.php sudah di-include sebelum file ini, agar $alamat_website tersedia.

// Asumsikan koneksi.php sudah di-include di file utama yang memanggil fungsi ini
global $alamat_website, $alamat_admin;

/**
 * Mengirim notifikasi deposit baru ke Telegram dengan tombol inline.
 *
 * @param array $depositData Array asosiatif berisi data dari baris deposit yang baru.
 * Diharapkan memiliki keys seperti 'id_deposit', 'kode_deposit', dll.
 * @param string|null $adminBaseUrl URL dasar untuk link ke detail admin (opsional).
 * @return bool True jika pesan berhasil dikirim, false jika gagal.
 */
function sendNewDepositNotificationToTelegram(array $depositData, $adminBaseUrl = null) {
    global $alamat_website, $alamat_admin;
    
    $telegramBotToken = '8251113693:AAHORd1KB6gM8AyaAtIjugDLDznGvrXpou0'; 
    $telegramChatId = '1568164881';

    if (empty($telegramBotToken) || strpos($telegramBotToken, 'GANTI_DENGAN') !== false ||
        empty($telegramChatId) || strpos($telegramChatId, 'GANTI_DENGAN') !== false) {
        error_log("Telegram App Notif (Deposit): Token Bot atau Chat ID belum dikonfigurasi dengan benar di functions_telegram.php.");
        return false;
    }

    $id_deposit_untuk_callback = $depositData['id_deposit'] ?? 0;
    
    if ($id_deposit_untuk_callback === 0) {
        error_log("Telegram App Notif (Deposit): id_deposit tidak ada atau nol, tidak bisa membuat tombol callback.");
    }

    $website_name = parse_url($alamat_website, PHP_URL_HOST);
    
    $message = "<b>🔔 Deposit Baru Masuk!</b> (" . htmlspecialchars($website_name) . ")\n\n";
    $message .= "<b>ID Deposit:</b> " . htmlspecialchars($depositData['id_deposit'] ?? 'N/A') . "\n";
    $message .= "<b>Kode Deposit:</b> " . htmlspecialchars($depositData['kode_deposit'] ?? 'N/A') . "\n";
    $message .= "<b>ID Sigma:</b> " . htmlspecialchars($depositData['id_sigma'] ?? 'N/A') . "\n";
    $message .= "<b>Username:</b> " . htmlspecialchars($depositData['nama_pengguna_anggota_deposit'] ?? 'N/A') . "\n";
    
    $jumlah = 'N/A';
    if (isset($depositData['jumlah_deposit']) && is_numeric($depositData['jumlah_deposit'])) {
        $jumlah = number_format((float)$depositData['jumlah_deposit'], 0, ',', '.');
    } elseif (isset($depositData['jumlah_deposit'])) {
        $jumlah = htmlspecialchars($depositData['jumlah_deposit']);
    }
    $message .= "<b>Jumlah:</b> " . $jumlah . "\n";
    $message .= "<b>Asal:</b> " . htmlspecialchars($depositData['asal_deposit'] ?? 'N/A') . "\n";
    $message .= "<b>Tujuan:</b> " . htmlspecialchars($depositData['tujuan_deposit'] ?? 'N/A') . "\n";
    
    $tanggal = 'N/A';
    if (!empty($depositData['tanggal_deposit'])) {
        try {
            $tanggal = date('d-m-Y H:i:s', strtotime($depositData['tanggal_deposit']));
        } catch (Exception $e) { /* Biarkan N/A */ }
    }
    $message .= "<b>Tanggal:</b> " . $tanggal . "\n";
    $message .= "<b>Status Awal:</b> " . htmlspecialchars(ucfirst($depositData['status_deposit'] ?? 'N/A')) . "\n";
    
    // Menggunakan variabel $alamat_admin tanpa tambahan parameter URL
    if ($alamat_admin && isset($depositData['id_deposit'])) {
        $message .= "\n<a href=\"" . rtrim($alamat_admin, '/') . "\">Lihat di Admin Panel</a>";
    }

    $inlineKeyboard = [
        'inline_keyboard' => [
            [ 
                ['text' => '✅ Setujui', 'callback_data' => 'approve_depo_' . $id_deposit_untuk_callback],
                ['text' => '❌ Batalkan', 'callback_data' => 'cancel_depo_' . $id_deposit_untuk_callback]
            ]
        ]
    ];

    $url = "https://api.telegram.org/bot{$telegramBotToken}/sendMessage";
    $data = [
        'chat_id' => $telegramChatId,
        'text' => $message,
        'parse_mode' => 'HTML',
        'reply_markup' => json_encode($inlineKeyboard)
    ];

    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data),
            'ignore_errors' => true,
            'timeout' => 10
        ],
    ];
    $context  = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);
    
    if ($result === FALSE) {
        $error = error_get_last();
        $errorMessage = $error ? $error['message'] : 'Tidak ada respons dari server Telegram atau koneksi gagal.';
        error_log("Telegram App Notif (Deposit w/ buttons): Gagal mengirim pesan. " . $errorMessage . " (Deposit ID: " . ($depositData['id_deposit'] ?? 'N/A') . ")");
        return false;
    }

    $response = json_decode($result, true);
    if (!$response || !isset($response['ok']) || $response['ok'] !== true) {
        $errorDescription = isset($response['description']) ? $response['description'] : 'Tidak ada deskripsi error dari Telegram.';
        error_log("Telegram App Notif (Deposit w/ buttons): Gagal mengirim pesan (Deposit ID: " . ($depositData['id_deposit'] ?? 'N/A') . "). Error Telegram: " . $errorDescription . " | Respons: " . $result);
        return false;
    }
    return true;
}

/**
 * Mengirim notifikasi withdraw baru ke Telegram dengan tombol inline.
 *
 * @param array $withdrawData Array asosiatif berisi data dari baris withdraw yang baru.
 * @param string $username Nama pengguna anggota yang melakukan withdraw.
 * @param string|null $adminBaseUrl URL dasar untuk link ke detail admin (opsional).
 * @return bool True jika pesan berhasil dikirim, false jika gagal.
 */
function sendNewWithdrawNotificationToTelegram(array $withdrawData, string $username, $adminBaseUrl = null) {
    global $alamat_website, $alamat_admin;

    $telegramBotToken = '8194212776:AAFrjOU3uJWBqg3LIKKFX7CZ-bTJwA8Bc3w';
    $telegramChatId = '1244924745';

    if (empty($telegramBotToken) || strpos($telegramBotToken, 'GANTI_DENGAN') !== false ||
        empty($telegramChatId) || strpos($telegramChatId, 'GANTI_DENGAN') !== false) {
        error_log("Telegram App Notif (Withdraw): Token Bot atau Chat ID belum dikonfigurasi dengan benar di functions_telegram.php.");
        return false;
    }

    $id_withdraw_untuk_callback = $withdrawData['id_withdraw'] ?? 0; 
    if ($id_withdraw_untuk_callback === 0) {
        error_log("Telegram App Notif (Withdraw): id_withdraw tidak ada atau nol, tidak bisa membuat tombol callback.");
    }
    
    $website_name = parse_url($alamat_website, PHP_URL_HOST);

    $message = "<b>💸 Permintaan Withdraw Baru!</b> (" . htmlspecialchars($website_name) . ")\n\n";
    $message .= "<b>ID Withdraw:</b> " . htmlspecialchars($withdrawData['id_withdraw'] ?? 'N/A') . "\n";
    $message .= "<b>Kode Withdraw:</b> " . htmlspecialchars($withdrawData['kode_withdraw'] ?? 'N/A') . "\n";
    $message .= "<b>Username:</b> " . htmlspecialchars($username) . "\n";
    
    $jumlah = 'N/A';
    if (isset($withdrawData['jumlah_withdraw']) && is_numeric($withdrawData['jumlah_withdraw'])) {
        $jumlah = number_format((float)$withdrawData['jumlah_withdraw'], 0, ',', '.');
    } elseif (isset($withdrawData['jumlah_withdraw'])) {
        $jumlah = htmlspecialchars($withdrawData['jumlah_withdraw']);
    }
    $message .= "<b>Jumlah:</b> " . $jumlah . "\n";

    $message .= "<b>Tujuan:</b> " . htmlspecialchars($withdrawData['tujuan_withdraw'] ?? 'N/A') . "\n";
    
    $tanggal = 'N/A';
    if (!empty($withdrawData['tanggal_withdraw'])) {
        try {
            $tanggal = date('d-m-Y H:i:s', strtotime($withdrawData['tanggal_withdraw']));
        } catch (Exception $e) { /* Biarkan N/A */ }
    }
    $message .= "<b>Tanggal Permintaan:</b> " . $tanggal . "\n";
    $message .= "<b>Status Awal:</b> Diajukan (Menunggu Proses Admin)\n";
    
    // Menggunakan variabel $alamat_admin tanpa tambahan parameter URL
    if ($alamat_admin && isset($withdrawData['id_withdraw'])) { 
        $message .= "\n<a href=\"" . rtrim($alamat_admin, '/') . "\">Lihat di Admin Panel</a>";
    }

    $inlineKeyboard = [
        'inline_keyboard' => [
            [ 
                ['text' => '✅ Setujui WD', 'callback_data' => 'approve_wd_' . $id_withdraw_untuk_callback],
                ['text' => '❌ Batalkan WD', 'callback_data' => 'cancel_wd_' . $id_withdraw_untuk_callback]
            ]
        ]
    ];

    $url = "https://api.telegram.org/bot{$telegramBotToken}/sendMessage";
    $data = [
        'chat_id' => $telegramChatId,
        'text' => $message,
        'parse_mode' => 'HTML',
        'reply_markup' => json_encode($inlineKeyboard)
    ];

    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data),
            'ignore_errors' => true,
            'timeout' => 10
        ],
    ];
    $context  = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);
    
    if ($result === FALSE) {
        $error = error_get_last();
        $errorMessage = $error ? $error['message'] : 'Tidak ada respons dari server Telegram atau koneksi gagal.';
        error_log("Telegram App Notif (Withdraw w/ buttons): Gagal mengirim pesan. " . $errorMessage . " (WD ID: " . ($withdrawData['id_withdraw'] ?? 'N/A') . ")");
        return false;
    }

    $response = json_decode($result, true);
    if (!$response || !isset($response['ok']) || $response['ok'] !== true) {
        $errorDescription = isset($response['description']) ? $response['description'] : 'Tidak ada deskripsi error dari Telegram.';
        error_log("Telegram App Notif (Withdraw w/ buttons): Gagal mengirim pesan (WD ID: " . ($withdrawData['id_withdraw'] ?? 'N/A') . "). Error Telegram: " . $errorDescription . " | Respons: " . $result);
        return false;
    }
    return true;
}

/**
 * Mengirim notifikasi pengguna baru ke Telegram.
 *
 * @param array $userData Array asosiatif berisi data pengguna yang baru.
 * @param string|null $adminBaseUrl URL dasar untuk link ke detail admin (opsional).
 * @return bool True jika pesan berhasil dikirim, false jika gagal.
 */
function sendNewUserNotificationToTelegram(array $userData, $adminBaseUrl = null) {
    global $alamat_website, $alamat_admin;
    
    $telegramBotToken = '8194212776:AAFrjOU3uJWBqg3LIKKFX7CZ-bTJwA8Bc3w';
    $telegramChatId = '1244924745';

    if (empty($telegramBotToken) || strpos($telegramBotToken, 'GANTI_DENGAN') !== false ||
        empty($telegramChatId) || strpos($telegramChatId, 'GANTI_DENGAN') !== false) {
        error_log("Telegram App Notif (User): Token Bot atau Chat ID belum dikonfigurasi dengan benar.");
        return false;
    }

    $website_name = parse_url($alamat_website, PHP_URL_HOST);

    $message = "<b>🎉 Pengguna Baru Terdaftar!</b> (" . htmlspecialchars($website_name) . ")\n\n";
    $message .= "<b>Username:</b> " . htmlspecialchars($userData['username'] ?? 'N/A') . "\n";
    $message .= "<b>Email:</b> " . htmlspecialchars($userData['email'] ?? 'N/A') . "\n";
    $message .= "<b>Telepon:</b> " . htmlspecialchars($userData['telepon'] ?? 'N/A') . "\n";
    $message .= "<b>Bank:</b> " . htmlspecialchars($userData['bank'] ?? 'N/A') . "\n";
    $message .= "<b>Nama Rekening:</b> " . htmlspecialchars($userData['nama_rekening'] ?? 'N/A') . "\n";
    $message .= "<b>Nomor Rekening:</b> " . htmlspecialchars($userData['nomor_rekening'] ?? 'N/A') . "\n";
    $message .= "<b>Upline:</b> " . htmlspecialchars($userData['upline'] ?? 'Tidak Ada') . "\n";
    $message .= "<b>Tanggal Daftar:</b> " . date('d-m-Y H:i:s') . "\n";

    // Menggunakan variabel $alamat_admin tanpa tambahan parameter URL
    if ($alamat_admin && isset($userData['username'])) {
        $message .= "\n<a href=\"" . rtrim($alamat_admin, '/') . "\">Lihat Profil di Admin Panel</a>";
    }

    $url = "https://api.telegram.org/bot{$telegramBotToken}/sendMessage";
    $data = [
        'chat_id' => $telegramChatId,
        'text' => $message,
        'parse_mode' => 'HTML',
    ];

    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data),
            'ignore_errors' => true,
            'timeout' => 10
        ],
    ];
    $context  = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);
    
    if ($result === FALSE) {
        $error = error_get_last();
        $errorMessage = $error ? $error['message'] : 'Tidak ada respons dari server Telegram atau koneksi gagal.';
        error_log("Telegram App Notif (User): Gagal mengirim pesan. " . $errorMessage . " (Username: " . ($userData['username'] ?? 'N/A') . ")");
        return false;
    }

    $response = json_decode($result, true);
    if (!$response || !isset($response['ok']) || $response['ok'] !== true) {
        $errorDescription = isset($response['description']) ? $response['description'] : 'Tidak ada deskripsi error dari Telegram.';
        error_log("Telegram App Notif (User): Gagal mengirim pesan (Username: " . ($userData['username'] ?? 'N/A') . "). Error Telegram: " . $errorDescription . " | Respons: " . $result);
        return false;
    }
    return true;
}
?>