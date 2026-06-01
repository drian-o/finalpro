<?php
include_once 'koneksi.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && isset($_POST['urutan'])) {
    $id = mysqli_real_escape_string($koneksi, $_POST['id']);
    $urutan = mysqli_real_escape_string($koneksi, $_POST['urutan']);

    $update_sql = "UPDATE srg_gamelist SET urutan = '$urutan' WHERE id = '$id'";
    
    if (mysqli_query($koneksi, $update_sql)) {
        echo json_encode([
            'success' => true,
            'message' => 'Urutan berhasil diperbarui!'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Gagal memperbarui urutan: ' . mysqli_error($koneksi)
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Permintaan tidak valid.'
    ]);
}
?>