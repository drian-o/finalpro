<?php
session_start();
include('koneksi.php'); // Menyediakan $koneksi, $alamat_website, $alamat_admin
require_once 'functions_telegram.php'; // Memuat fungsi notifikasi Telegram

if (!isset($_SESSION['loggedin']) || !isset($_SESSION['id_anggota'])) {
    echo '<script>alert("Sesi Anda telah berakhir. Silakan login kembali."); window.location.href = "' . ($alamat_website ?? '') . 'login";</script>';
    exit();
}

$id_anggota = $_SESSION['id_anggota'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $asal_deposit = isset($_POST['asal_deposit']) ? mysqli_real_escape_string($koneksi, $_POST['asal_deposit']) : '';
    $tujuan_deposit = isset($_POST['tujuan_deposit']) ? mysqli_real_escape_string($koneksi, $_POST['tujuan_deposit']) : '';
    
    $prefix_kode = strtoupper(substr(str_replace(' ', '', $tujuan_deposit), 0, 3));
    $kode_deposit = $prefix_kode . time() . rand(10,99);

    $bonus_deposit = isset($_POST['bonus_deposit']) ? mysqli_real_escape_string($koneksi, $_POST['bonus_deposit']) : '0';
    $jumlah_deposit_input = isset($_POST['jumlah_deposit']) ? $_POST['jumlah_deposit'] : '';
    $tanggal_deposit = date('Y-m-d H:i:s'); 

    $jumlah_deposit = str_replace(['.', ','], '', $jumlah_deposit_input);
    if (!is_numeric($jumlah_deposit) || $jumlah_deposit <= 0) {
        $jumlah_deposit = 0;
    }

    $errors = [];
    if (empty($tujuan_deposit)) {
        $errors[] = "Pilih Tujuan Deposit.";
    }
    if (empty($asal_deposit)) {
        $errors[] = "Isi Asal Deposit (misalnya nama bank Anda).";
    }
    if ($jumlah_deposit <= 0) {
        $errors[] = "Isi Jumlah Deposit dengan angka yang valid.";
    }

    if (!empty($errors)) {
        $error_message = count($errors) > 1 ? "Harap isi form deposit dengan lengkap dan benar!" : implode("\\n", $errors);
        echo "<script>alert('$error_message'); window.history.back();</script>";
        exit();
    }

    // Query nama pengguna dan ID Sigma
    $query_data_anggota = mysqli_prepare($koneksi, "SELECT nama_pengguna_anggota, id_sigma FROM anggota WHERE id_anggota = ?");
    if (!$query_data_anggota) {
        error_log("Gagal mempersiapkan statement data anggota: " . mysqli_error($koneksi));
        echo '<script>alert("Terjadi kesalahan sistem. Silakan coba lagi nanti. [Code: S2]"); window.location.href = "deposit";</script>';
        exit();
    }
    mysqli_stmt_bind_param($query_data_anggota, 'i', $id_anggota);
    mysqli_stmt_execute($query_data_anggota);
    $result_data_anggota = mysqli_stmt_get_result($query_data_anggota);
    $data_anggota = mysqli_fetch_assoc($result_data_anggota);
    mysqli_stmt_close($query_data_anggota);

    if (!$data_anggota) {
        echo '<script>alert("Gagal mendapatkan data pengguna. Pastikan Anda login dengan benar."); window.location.href = "deposit";</script>';
        exit();
    }
    $nama_pengguna_anggota_deposit = $data_anggota['nama_pengguna_anggota'];
    $id_sigma = $data_anggota['id_sigma']; // Ambil ID Sigma

    // Query deposit terakhir
    $query_deposit_terakhir = mysqli_prepare($koneksi, "SELECT status_deposit, tanggal_deposit FROM deposit WHERE id_anggota_deposit = ? ORDER BY tanggal_deposit DESC LIMIT 1");
    if (!$query_deposit_terakhir) {
        error_log("Gagal mempersiapkan statement deposit terakhir: " . mysqli_error($koneksi));
        echo '<script>alert("Terjadi kesalahan sistem. Silakan coba lagi nanti. [Code: S3]"); window.location.href = "deposit";</script>';
        exit();
    }
    mysqli_stmt_bind_param($query_deposit_terakhir, 'i', $id_anggota);
    mysqli_stmt_execute($query_deposit_terakhir);
    $result_deposit_terakhir = mysqli_stmt_get_result($query_deposit_terakhir);
    $data_deposit_terakhir = mysqli_fetch_assoc($result_deposit_terakhir);
    mysqli_stmt_close($query_deposit_terakhir);

    if ($data_deposit_terakhir) {
        $lima_menit_lalu = strtotime("-1 minutes");
        $waktu_deposit_terakhir = strtotime($data_deposit_terakhir['tanggal_deposit']);

        if ($data_deposit_terakhir['status_deposit'] === 'diproses') {
            echo '<script>
                alert("Deposit terakhir Anda masih diproses. Silakan tunggu hingga proses tersebut selesai atau hubungi CS jika lebih dari 15 menit.");
                window.location.href = "home";
            </script>';
            exit();
        }
        if ($waktu_deposit_terakhir > $lima_menit_lalu) {
             echo '<script>
                alert("Anda baru saja melakukan permintaan deposit. Mohon tunggu beberapa saat sebelum mencoba lagi.");
                window.location.href = "home";
            </script>';
            exit();
        }
    }

    // Insert deposit baru
    $sql_insert = "INSERT INTO deposit (id_anggota_deposit, kode_deposit, nama_pengguna_anggota_deposit, asal_deposit, tujuan_deposit, bonus_deposit, jumlah_deposit, tanggal_deposit, status_deposit)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'diproses')";

    $stmt_insert = mysqli_prepare($koneksi, $sql_insert);
    if (!$stmt_insert) {
        error_log("Gagal mempersiapkan statement insert deposit: " . mysqli_error($koneksi));
        echo '<script>alert("Terjadi kesalahan sistem saat memproses deposit Anda. [Code: S4]"); window.location.href = "deposit";</script>';
        exit();
    }
    mysqli_stmt_bind_param($stmt_insert, 'isssssds', $id_anggota, $kode_deposit, $nama_pengguna_anggota_deposit, $asal_deposit, $tujuan_deposit, $bonus_deposit, $jumlah_deposit, $tanggal_deposit);

    if (mysqli_stmt_execute($stmt_insert)) {
        $new_deposit_id = mysqli_insert_id($koneksi);

        // Data untuk notifikasi Telegram
        $depositDataForTelegram = [
            'id_deposit' => $new_deposit_id,
            'id_anggota_deposit' => $id_anggota,
            'kode_deposit' => $kode_deposit,
            'nama_pengguna_anggota_deposit' => $nama_pengguna_anggota_deposit,
            'asal_deposit' => $asal_deposit,
            'tujuan_deposit' => $tujuan_deposit,
            'bonus_deposit' => $bonus_deposit,
            'jumlah_deposit' => $jumlah_deposit,
            'tanggal_deposit' => $tanggal_deposit,
            'status_deposit' => 'diproses',
            'id_sigma' => $id_sigma // Tambahkan id_sigma
        ];

        // Kirim notifikasi Telegram
        if (!sendNewDepositNotificationToTelegram($depositDataForTelegram, $alamat_admin)) {
            error_log("Gagal mengirim notifikasi Telegram untuk deposit ID: " . $new_deposit_id . " (proses_deposit)");
        }

        $_SESSION['valid_navigation_token_deposit'] = md5($kode_deposit . $jumlah_deposit);
        $_SESSION['last_deposit_id_for_progress'] = $new_deposit_id;

        echo '<script>window.location.href = "deposit";</script>';
        exit();

    } else {
        error_log("Gagal mengeksekusi insert deposit: " . mysqli_stmt_error($stmt_insert));
        echo '<script>alert("Deposit Gagal. Silakan coba lagi atau hubungi CS jika masalah berlanjut. [Code: DB2]"); window.location.href = "deposit";</script>';
    }
    mysqli_stmt_close($stmt_insert);

} else {
    echo '<script>alert("Metode request tidak valid."); window.location.href = "deposit";</script>';
}

mysqli_close($koneksi);
?>