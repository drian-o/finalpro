<?php
session_start();
include_once "koneksi.php";

if (isset($_POST['nama_pengguna_staff']) && isset($_POST['kata_sandi_staff'])) {
    $nama_pengguna_staff = $_POST['nama_pengguna_staff'];
    $kata_sandi_staff = $_POST['kata_sandi_staff'];

    // Gunakan prepared statement untuk menghindari SQL injection
    $stmt = $koneksi->prepare("SELECT * FROM staff WHERE nama_pengguna_staff = ?");
    $stmt->bind_param("s", $nama_pengguna_staff);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows >= 1) {
        $data_staff = $result->fetch_assoc();
        $id_staff = $data_staff['id_staff'];
        $kata_sandi_staff_hash = $data_staff['kata_sandi_staff'];

        if (password_verify($kata_sandi_staff, $kata_sandi_staff_hash)) {
            // Perbarui posisi staff
            $stmt_update = $koneksi->prepare("UPDATE staff SET posisi_staff = 'masuk' WHERE id_staff = ?");
            $stmt_update->bind_param("i", $id_staff);
            $update_success = $stmt_update->execute();

            if ($update_success) {
                echo 'success';
                $_SESSION['posisi_staff'] = $data_staff['posisi_staff'];
                $_SESSION['nama_pengguna_staff'] = $nama_pengguna_staff;
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
