<?php
session_start();
include 'koneksi.php'; // Pastikan koneksi database sudah ada

class KYCVerification {
    private $koneksi;

    public function __construct($db_connection) {
        $this->koneksi = $db_connection;
    }

    public function uploadDocument($user_id, $document_type, $document) {
        // Validasi
        if (empty($document_type) || empty($document['name'])) {
            return ['status' => false, 'message' => 'Mohon pilih jenis dokumen dan upload file.'];
        }

        // Cek apakah file berhasil di-upload
        if ($document['error'] !== UPLOAD_ERR_OK) {
            return ['status' => false, 'message' => 'Terjadi kesalahan saat meng-upload file.'];
        }

        // Tentukan direktori upload
        $upload_dir = 'uploads/kyc/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        // Generate nama file baru
        $file_name = time() . '_' . basename($document['name']);
        $target_file = $upload_dir . $file_name;

        // Pindahkan file ke direktori upload
        if (!move_uploaded_file($document['tmp_name'], $target_file)) {
            return ['status' => false, 'message' => 'Gagal menyimpan file.'];
        }

        // Simpan data verifikasi KYC ke database
        $stmt = mysqli_prepare($this->koneksi, "INSERT INTO kyc_verification (user_id, document_type, document, status) VALUES (?, ?, ?, 'pending')");
        mysqli_stmt_bind_param($stmt, "iss", $user_id, $document_type, $target_file);

        if (mysqli_stmt_execute($stmt)) {
            // Update status KYC di tabel users
            $update_stmt = mysqli_prepare($this->koneksi, "UPDATE users SET kyc_status = 'pending' WHERE id = ?");
            mysqli_stmt_bind_param($update_stmt, "i", $user_id);
            mysqli_stmt_execute($update_stmt);
            mysqli_stmt_close($update_stmt);

            return ['status' => true, 'message' => 'Dokumen KYC berhasil di-upload.'];
        } else {
            return ['status' => false, 'message' => 'Gagal menyimpan data KYC.'];
        }
    }

    public function closeConnection() {
        mysqli_close($this->koneksi);
    }
}

// Proses pengunggahan dokumen jika metode POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id']; // Ambil user_id dari session
    $document_type = mysqli_real_escape_string($koneksi, $_POST['document_type']);
    $document = $_FILES['document'];

    // Buat instance dari KYCVerification
    $kyc = new KYCVerification($koneksi);

    // Panggil metode uploadDocument
    $result = $kyc->uploadDocument($user_id, $document_type, $document);

    // Tampilkan pesan hasil
    echo "<script>alert('" . $result['message'] . "'); window.location.replace('kyc.php');</script>";

    // Tutup koneksi database
    $kyc->closeConnection();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi KYC</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>

<div class="container mt-5">
    <h2>Verifikasi KYC</h2>
    <form action="" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="document_type">Jenis Dokumen</label>
            <select class="form-control" id="document_type" name="document_type" required>
                <option value="">Pilih jenis dokumen</option>
                <option value="KTP">KTP</option>
                <option value="SIM">SIM</option>
                <option value="Paspor">Paspor</option>
                <option value="Dokumen Lainnya">Dokumen Lainnya</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="document">Unggah Dokumen</label>
            <input type="file" class="form-control-file" id="document" name="document" accept=".jpg,.jpeg,.png,.pdf" required>
            <small class="form-text text-muted">Hanya menerima file JPG, PNG, atau PDF.</small>
        </div>

        <button type="submit" class="btn btn-primary">Kirim</button>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

