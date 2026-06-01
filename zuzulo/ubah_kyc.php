<?php
include_once '../koneksi.php';
session_start();

// Cek apakah pengguna sudah masuk
if (!isset($_SESSION['kode_admin'])) {
    echo '
      <script>
        alert("Terjadi kesalahan, harap masuk kembali!");
        window.location.replace("'.$alamat_admin.'keluar.php");
      </script>
    ';
    exit;
}

// Cek jika form di-submit
if (isset($_POST['update_kyc'])) {
    $id_anggota = $_POST['id_anggota'];
    $new_status = $_POST['new_status'];

    // Validasi status yang diterima
    if (in_array($new_status, ['0', '1', '2'])) {
        // Fungsi untuk memperbarui status KYC
        function updateKYCStatus($id_anggota, $new_status) {
            global $koneksi;
            $query = "UPDATE anggota SET kyc_status = ? WHERE id_anggota = ?";
            $stmt = $koneksi->prepare($query);

            if ($stmt) {
                $stmt->bind_param("ii", $new_status, $id_anggota);
                return $stmt->execute();
            }
            return false;
        }

        // Panggil fungsi untuk memperbarui status KYC
        if (updateKYCStatus($id_anggota, $new_status)) {
            echo '<script>alert("Status KYC berhasil diperbarui!"); window.location.replace("' . $alamat_admin . 'kyc");</script>';
        } else {
            echo '<script>alert("Gagal memperbarui status KYC!"); window.history.back();</script>';
        }
    } else {
        echo '<script>alert("Status yang diberikan tidak valid!"); window.history.back();</script>';
    }
}
?>
