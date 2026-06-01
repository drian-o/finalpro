<?php
include 'koneksi.php'; // Pastikan file ini berisi koneksi $koneksi ke database Anda

header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Permintaan tidak valid.', 'field' => 'unknown'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['field_name']) && isset($_POST['field_value'])) {
    $fieldName = $_POST['field_name'];
    $fieldValue = trim($_POST['field_value']);
    $sanitizedValue = mysqli_real_escape_string($koneksi, $fieldValue);

    $response['field'] = $fieldName;

    if (empty($fieldValue)) {
        $response = ['status' => 'empty', 'message' => ucfirst($fieldName) . ' tidak boleh kosong.', 'field' => $fieldName];
        echo json_encode($response);
        exit;
    }

    // Anda bisa menambahkan validasi panjang minimal di sini jika perlu
    // Contoh: if (strlen($fieldValue) < 4 && $fieldName === 'username') { ... }


    $query = "";
    $param_type = "s";
    $param_value = $sanitizedValue;

    switch ($fieldName) {
        case 'username':
            if (strlen($fieldValue) < 6 || strlen($fieldValue) > 14) {
                 $response = ['status' => 'invalid_length', 'message' => 'Username harus 6-14 karakter.', 'field' => $fieldName];
                 echo json_encode($response);
                 mysqli_close($koneksi);
                 exit;
            }
            $db_column_name = 'nama_pengguna_anggota';
            $query = "SELECT {$db_column_name} FROM anggota WHERE {$db_column_name} = ?";
            break;
        case 'email':
             if (!empty($fieldValue) && !filter_var($fieldValue, FILTER_VALIDATE_EMAIL)) {
                $response = ['status' => 'invalid_format', 'message' => 'Format email tidak valid.', 'field' => $fieldName];
                echo json_encode($response);
                mysqli_close($koneksi);
                exit;
            }
            $db_column_name = 'email_anggota';
            $query = "SELECT {$db_column_name} FROM anggota WHERE {$db_column_name} = ? AND {$db_column_name} != '' AND {$db_column_name} IS NOT NULL"; // Hanya cek jika email diisi
            break;
        case 'telepon':
            $db_column_name = 'telepon_anggota';
            $query = "SELECT {$db_column_name} FROM anggota WHERE {$db_column_name} = ?";
            break;
        case 'nomor_rekening':
            $db_column_name = 'nomor_rekening_anggota';
            $query = "SELECT {$db_column_name} FROM anggota WHERE {$db_column_name} = ?";
            break;
        // Tambahkan case lain jika diperlukan untuk field lain
        default:
            $response = ['status' => 'error', 'message' => 'Field tidak dikenal.', 'field' => $fieldName];
            echo json_encode($response);
            mysqli_close($koneksi);
            exit;
    }

    if (empty($fieldValue) && $fieldName === 'email') { // Jangan cek email kosong
        $response = ['status' => 'available', 'message' => 'Email tersedia (opsional).', 'field' => $fieldName];
    } else {
        $stmt = mysqli_prepare($koneksi, $query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, $param_type, $param_value);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) > 0) {
                $response = ['status' => 'exists', 'message' => ucfirst($fieldName) . ' sudah digunakan.', 'field' => $fieldName];
            } else {
                $response = ['status' => 'available', 'message' => ucfirst($fieldName) . ' tersedia.', 'field' => $fieldName];
            }
            mysqli_stmt_close($stmt);
        } else {
            $response = ['status' => 'error', 'message' => 'Query database gagal.', 'field' => $fieldName];
        }
    }
}

echo json_encode($response);
mysqli_close($koneksi);
?>