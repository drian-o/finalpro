<?php
// process-launch.php

// Header untuk memastikan respons adalah JSON
header('Content-Type: application/json');

// Memulai sesi untuk memastikan autentikasi jika diperlukan
session_start();

// Sertakan file koneksi database dan class API
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/classes/class.exa.php';

// Cek apakah user sudah login atau memiliki ID yang valid
// Di sini kita menggunakan ID 261, jadi kita tidak perlu cek session
// Jika Anda ingin menggunakan user yang login, Anda bisa menggunakan $_SESSION['id_anggota']
$playerId = (int)($_POST['playerId'] ?? 0);
if ($playerId <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID Pemain tidak valid.']);
    exit;
}

// Pastikan request method adalah POST dan ada action
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action'])) {
    echo json_encode(['success' => false, 'message' => 'Metode request tidak valid.']);
    exit;
}

$action = $_POST['action'];

if ($action === 'launchGame') {
    $gameCode = $_POST['gameCode'] ?? '';
    $currency = $_POST['currency'] ?? 'IDR';

    if (empty($gameCode)) {
        echo json_encode(['success' => false, 'message' => 'Game Code tidak valid.']);
        exit;
    }

    try {
        $api = new GameXaAPI();
        $response = $api->launchGame($playerId, $gameCode, $currency);

        // Jika API sukses, kirim respons GameXa langsung
        echo json_encode($response);
    } catch (Exception $e) {
        // Tangani jika ada exception dari class API
        echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan saat memanggil API: ' . $e->getMessage()]);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Aksi tidak dikenal.']);
}
?>