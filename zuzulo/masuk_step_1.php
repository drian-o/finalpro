<?php
session_start();
include_once "koneksi.php";

if (isset($_POST['nama_pengguna_admin']) && isset($_POST['kata_sandi_admin'])) {
    $nama_pengguna_admin = $_POST['nama_pengguna_admin'];
    $kata_sandi_admin = $_POST['kata_sandi_admin'];

    // Gunakan prepared statement untuk menghindari SQL injection
    $stmt = $koneksi->prepare("SELECT * FROM admin WHERE nama_pengguna_admin = ?");
    $stmt->bind_param("s", $nama_pengguna_admin);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows >= 1) {
        $data_admin = $result->fetch_assoc();
        $id_admin = $data_admin['id_admin'];
        $kata_sandi_admin_hash = $data_admin['kata_sandi_admin'];

        if (password_verify($kata_sandi_admin, $kata_sandi_admin_hash)) {
            // Perbarui posisi admin
            $stmt_update = $koneksi->prepare("UPDATE admin SET posisi_admin = 'masuk' WHERE id_admin = ?");
            $stmt_update->bind_param("i", $id_admin);
            $update_success = $stmt_update->execute();

            if ($update_success) {
                echo 'success';
                $_SESSION['posisi_admin'] = $data_admin['posisi_admin'];
                $_SESSION['nama_pengguna_admin'] = $nama_pengguna_admin;
            } else {
                echo 'error';
            }

            $stmt_update->close();
        } else {
            echo 'error';
        }
    } else {
        echo 'error';
    }

    $stmt->close();
} else {
    echo 'error';
}
?>
