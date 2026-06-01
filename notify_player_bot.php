<?php

function sendPlayingPlayersNotificationToTelegram(array $playersData) {
    $telegramBotToken = '8251113693:AAHORd1KB6gM8AyaAtIjugDLDznGvrXpou0';
    $telegramChatId = '1568164881';

    if (empty($telegramBotToken) || empty($telegramChatId)) {
        return false;
    }

    $message = "<b>🎮 Pemain Aktif Terdeteksi!</b>\n\n";
    $message .= "Berikut daftar pemain yang sedang bermain:\n\n";

    foreach ($playersData as $player) {
        $username = htmlspecialchars($player['user_code'] ?? 'N/A');
        $game_code = htmlspecialchars($player['game_code'] ?? 'N/A');
        $provider = htmlspecialchars($player['provider_code'] ?? 'N/A');
        $bet = isset($player['bet']) ? number_format($player['bet'], 0, ',', '.') : 'N/A';
        $balance = isset($player['balance']) ? number_format($player['balance'], 2, ',', '.') : 'N/A';

        $message .= "<b>Username:</b> {$username}\n";
        $message .= "<b>Game:</b> {$game_code} ({$provider})\n";
        $message .= "<b>Bet:</b> Rp {$bet}\n";
        $message .= "<b>Saldo Saat Ini:</b> Rp {$balance}\n";
        $message .= "----------------------------------\n";
    }

    $url = "https://api.telegram.org/bot{$telegramBotToken}/sendMessage";
    $data = [
        'chat_id' => $telegramChatId,
        'text' => $message,
        'parse_mode' => 'HTML',
    ];

    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data),
            'ignore_errors' => true,
            'timeout' => 10
        ],
    ];
    $context = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);

    if ($result === FALSE) {
        return false;
    }

    $response = json_decode($result, true);
    if (!$response || !isset($response['ok']) || $response['ok'] !== true) {
        return false;
    }
    return true;
}
?>