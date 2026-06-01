<?php
// process-log.php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metode request tidak valid.']);
    exit;
}

// Ambil data JSON dari body permintaan
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['logs']) || !is_array($data['logs'])) {
    echo json_encode(['success' => false, 'message' => 'Data log tidak ditemukan atau format tidak valid.']);
    exit;
}

$logFile = 'log_game.log';
$timestamp = date('Y-m-d H:i:s');
$logData = [
    'timestamp' => $timestamp,
    'logs' => $data['logs']
];

// Ubah array PHP menjadi string JSON dengan format yang rapi
$jsonLog = json_encode($logData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

// Tambahkan ke file log
if (file_put_contents($logFile, $jsonLog . "\n", FILE_APPEND | LOCK_EX) !== false) {
    echo json_encode(['success' => true, 'message' => 'Log berhasil disimpan.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menulis ke file log.']);
}
?>