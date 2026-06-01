<?php
// Pastikan tidak ada output sebelum json_encode di akhir
header('Content-Type: application/json');

// Memulai session, jika belum dimulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once '../../koneksi.php'; // Sesuaikan path

// Pastikan admin sudah login
if (!isset($_SESSION['kode_admin'])) {
    echo json_encode(['status' => 'error', 'message' => 'Sesi tidak valid, harap login kembali.']);
    exit();
}

// Periksa apakah request adalah POST dan memiliki kode provider dan file
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['provider_code']) || empty($_POST['provider_code']) || !isset($_FILES['provider_image'])) {
    echo json_encode(['status' => 'error', 'message' => 'Permintaan tidak valid. Data tidak lengkap.']);
    exit();
}

$provider_code = $_POST['provider_code'];
$uploaded_file = $_FILES['provider_image'];
$response_data = ['status' => 'error', 'message' => 'Terjadi kesalahan tidak diketahui.'];

if ($uploaded_file['error'] !== UPLOAD_ERR_OK) {
    $response_data['message'] = 'Gagal upload file. Kode error: ' . $uploaded_file['error'];
    echo json_encode($response_data);
    exit();
}

// Dapatkan nama provider dari database untuk membuat folder
$provider_name_for_folder = 'unknown_provider';
if (isset($koneksi) && $koneksi instanceof mysqli) {
    $stmt_name = $koneksi->prepare("SELECT provider_name FROM ace_provider WHERE provider_code = ?");
    $stmt_name->bind_param("s", $provider_code);
    $stmt_name->execute();
    $result_name = $stmt_name->get_result();
    if ($row_name = $result_name->fetch_assoc()) {
        $provider_name_for_folder = preg_replace('/[^a-zA-Z0-9_\-]/', '', $row_name['provider_name']);
    }
    $stmt_name->close();
}

$upload_dir = '../upload/provider/' . $provider_name_for_folder . '/';
$upload_url_path = 'upload/provider/' . $provider_name_for_folder . '/'; // Path relatif untuk disimpan di DB

if (!is_dir($upload_dir)) {
    if(!mkdir($upload_dir, 0777, true)) {
        $response_data['message'] = 'Gagal membuat direktori upload: ' . $upload_dir;
        echo json_encode($response_data);
        exit();
    }
}

// Validasi ekstensi file (contoh: hanya JPG, JPEG, PNG, GIF)
$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
$file_extension = strtolower(pathinfo($uploaded_file['name'], PATHINFO_EXTENSION));
if (!in_array($file_extension, $allowed_extensions)) {
    $response_data['message'] = 'Jenis file tidak diizinkan. Hanya JPG, JPEG, PNG, GIF.';
    echo json_encode($response_data);
    exit();
}

// Tentukan nama file unik (misalnya, provider_code.ext) atau gunakan nama aslinya
$new_file_name = $provider_code . '.' . $file_extension;
$destination_path = $upload_dir . $new_file_name;
$db_image_path = $upload_url_path . $new_file_name; // Path yang akan disimpan di database

if (move_uploaded_file($uploaded_file['tmp_name'], $destination_path)) {
    // Update database
    if (isset($koneksi) && $koneksi instanceof mysqli) {
        $stmt = $koneksi->prepare("UPDATE ace_provider SET provider_image = ?, last_updated = CURRENT_TIMESTAMP WHERE provider_code = ?");
        if ($stmt === false) {
            $response_data['message'] = 'Gagal menyiapkan statement database: ' . htmlspecialchars($koneksi->error);
        } else {
            $stmt->bind_param("ss", $db_image_path, $provider_code);
            if ($stmt->execute()) {
                $response_data['status'] = 'success';
                $response_data['message'] = 'Gambar provider berhasil diunggah dan diperbarui.';
                $response_data['new_image_url'] = '../' . $db_image_path; // URL untuk ditampilkan di frontend
            } else {
                $response_data['message'] = 'Gagal memperbarui database: ' . htmlspecialchars($stmt->error);
            }
            $stmt->close();
        }
    } else {
        $response_data['message'] = 'Koneksi database tidak valid.';
    }
} else {
    $response_data['message'] = 'Gagal memindahkan file yang diunggah.';
}

echo json_encode($response_data);
exit();
?>