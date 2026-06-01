<?php
session_start();
include_once '../koneksi.php';

$result = ['dataChanges' => false];

// Periksa apakah ada perubahan pada data deposit
$query_deposit = mysqli_query($koneksi, "SELECT SUM(jumlah_deposit) AS total_jumlah_deposit FROM deposit");
if ($query_deposit) {
    $data_deposit = mysqli_fetch_assoc($query_deposit);
    $total_jumlah_deposit = $data_deposit['total_jumlah_deposit'];

    if (!isset($_SESSION['lastDepositCount'])) {
        $_SESSION['lastDepositCount'] = $total_jumlah_deposit;
    } elseif ($_SESSION['lastDepositCount'] != $total_jumlah_deposit) {
        $result['dataChanges'] = true;
        $_SESSION['lastDepositCount'] = $total_jumlah_deposit;
    }
} else {
    $result['error'] = 'Failed to fetch deposit data';
}

// Periksa apakah ada perubahan pada data withdraw
$query_withdraw = mysqli_query($koneksi, "SELECT SUM(jumlah_withdraw) AS total_jumlah_withdraw FROM withdraw");
if ($query_withdraw) {
    $data_withdraw = mysqli_fetch_assoc($query_withdraw);
    $total_jumlah_withdraw = $data_withdraw['total_jumlah_withdraw'];

    if (!isset($_SESSION['lastWithdrawCount'])) {
        $_SESSION['lastWithdrawCount'] = $total_jumlah_withdraw;
    } elseif ($_SESSION['lastWithdrawCount'] != $total_jumlah_withdraw) {
        $result['dataChanges'] = true;
        $_SESSION['lastWithdrawCount'] = $total_jumlah_withdraw;
    }
} else {
    $result['error'] = 'Failed to fetch withdraw data';
}

// Periksa apakah ada perubahan pada data anggota
$query_anggota = mysqli_query($koneksi, "SELECT COUNT(*) AS jumlah_anggota FROM anggota");
if ($query_anggota) {
    $data_anggota = mysqli_fetch_assoc($query_anggota);
    $jumlah_anggota = $data_anggota['jumlah_anggota'];

    if (!isset($_SESSION['lastAnggotaCount'])) {
        $_SESSION['lastAnggotaCount'] = $jumlah_anggota;
    } elseif ($_SESSION['lastAnggotaCount'] != $jumlah_anggota) {
        $result['dataChanges'] = true;
        $_SESSION['lastAnggotaCount'] = $jumlah_anggota;
    }
} else {
    $result['error'] = 'Failed to fetch anggota data';
}

// Periksa apakah ada perubahan pada data promosi
$query_promosi = mysqli_query($koneksi, "SELECT COUNT(*) AS jumlah_promosi FROM promosi");
if ($query_promosi) {
    $data_promosi = mysqli_fetch_assoc($query_promosi);
    $jumlah_promosi = $data_promosi['jumlah_promosi'];

    if (!isset($_SESSION['lastPromosiCount'])) {
        $_SESSION['lastPromosiCount'] = $jumlah_promosi;
    } elseif ($_SESSION['lastPromosiCount'] != $jumlah_promosi) {
        $result['dataChanges'] = true;
        $_SESSION['lastPromosiCount'] = $jumlah_promosi;
    }
} else {
    $result['error'] = 'Failed to fetch promosi data';
}

// Periksa apakah ada perubahan pada data staff
$query_staff = mysqli_query($koneksi, "SELECT COUNT(*) AS jumlah_staff FROM staff");
if ($query_staff) {
    $data_staff = mysqli_fetch_assoc($query_staff);
    $jumlah_staff = $data_staff['jumlah_staff'];

    if (!isset($_SESSION['lastStaffCount'])) {
        $_SESSION['lastStaffCount'] = $jumlah_staff;
    } elseif ($_SESSION['lastStaffCount'] != $jumlah_staff) {
        $result['dataChanges'] = true;
        $_SESSION['lastStaffCount'] = $jumlah_staff;
    }
} else {
    $result['error'] = 'Failed to fetch staff data';
}

// Keluarkan hasil sebagai JSON
echo json_encode($result);
?>
