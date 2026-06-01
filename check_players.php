<?php

require_once __DIR__ . '/notify_player_bot.php';

$cacheFileDir = __DIR__ . '/cache/';
$cacheFilePath = $cacheFileDir . 'active_players.json';

if (!is_dir($cacheFileDir)) {
    @mkdir($cacheFileDir, 0775, true);
}

$cachedPlayers = [];
if (file_exists($cacheFilePath)) {
    $cachedPlayersJson = file_get_contents($cacheFilePath);
    if (!empty($cachedPlayersJson)) {
        $cachedPlayers = json_decode($cachedPlayersJson, true);
        if ($cachedPlayers === null) {
            $cachedPlayers = [];
        }
    }
}

$cachedPlayerKeys = [];
foreach ($cachedPlayers as $player) {
    $cachedPlayerKeys[] = $player['user_code'] . '_' . $player['game_code'];
}

$nexus_api_url = 'https://api.nexusggr.com';
$nexus_agent_code = 'yadii303';
$nexus_agent_token = 'f2ebcfb1c56d354e707c9d8bdb8ea22b';

$api_payload = json_encode([
    'method' => 'call_players',
    'agent_code' => $nexus_agent_code,
    'agent_token' => $nexus_agent_token
]);

$options = [
    'http' => [
        'header' => "Content-Type: application/json\r\n",
        'method' => 'POST',
        'content' => $api_payload,
        'ignore_errors' => true,
        'timeout' => 15
    ],
];
$context = stream_context_create($options);
$response_raw = @file_get_contents($nexus_api_url, false, $context);

if ($response_raw === FALSE) {
    exit;
}

$response_data = json_decode($response_raw, true);

if (isset($response_data['status']) && $response_data['status'] === 1) {
    $currentPlayers = $response_data['data'];
    $newlyActivePlayers = [];

    if (!empty($currentPlayers)) {
        foreach ($currentPlayers as $player) {
            $playerKey = $player['user_code'] . '_' . $player['game_code'];
            if (!in_array($playerKey, $cachedPlayerKeys)) {
                $newlyActivePlayers[] = $player;
            }
        }
    }

    if (count($newlyActivePlayers) > 0) {
        sendPlayingPlayersNotificationToTelegram($newlyActivePlayers);
    }

    file_put_contents($cacheFilePath, json_encode($currentPlayers));
}
?>