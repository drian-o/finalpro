<?php
// 1. Sertakan file koneksi
include_once '../koneksi.php';

// 2. Gunakan pengecekan status sesi agar tidak double start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 3. Cek apakah pengguna sudah masuk
if (!isset($_SESSION['kode_admin'])) {
    echo '
      <script>
        alert("Terjadi kesalahan, harap masuk kembali!");
        window.location.replace("'.$alamat_admin.'keluar.php");
      </script>
    ';
    exit;
}
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row gy-4 mb-4">
        <div class="col-md-6">
            <div class="fw-bold fs-4 text-center text-md-start">KYC</div>
        </div>
        <div class="col-md-6">
            <div class="text-center text-md-end">
                <div>
                    <a href="<?php echo $alamat_admin.'tambah_anggota'; ?>" class="btn btn-sm btn-primary waves-effect waves-light">
                        <span class="tf-icons mdi mdi-plus me-1"></span>
                        Tambah Data
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="card table-responsive p-3">
        <table class="table" id="example">
            <thead>
                <tr>
                    <th scope="col" class="text-center">#</th>
                    <th scope="col" class="text-center">Nama Pengguna</th>
                    <th scope="col" class="text-center">Email</th>
                    <th scope="col" class="text-center">Telepon</th>
                    <th scope="col" class="text-center">Status KYC</th>
                    <th scope="col" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
<?php
$nomor_anggota = 1;
$anggota = mysqli_query($koneksi, "SELECT * FROM anggota ORDER BY id_anggota DESC");

if (mysqli_num_rows($anggota) < 1) {
    echo '<tr><td class="text-center" colspan="6">Tidak Ada Data</td></tr>';
} else {
    while ($data_anggota = mysqli_fetch_array($anggota)) {
        $id_anggota = $data_anggota['id_anggota'];
        $nama_pengguna_anggota = $data_anggota['nama_pengguna_anggota'];
        $email_anggota = $data_anggota['email_anggota'];
        $telepon_anggota = $data_anggota['telepon_anggota'];
        $status_kyc = $data_anggota['kyc_status'];

        // Konversi status numerik ke teks
        switch ($status_kyc) {
            case 0:
                $status_text = 'Not-verified';
                break;
            case 1:
                $status_text = 'Approved';
                break;
            case 2:
                $status_text = 'Pending';
                break;
            default:
                $status_text = 'Unknown'; // untuk status yang tidak valid
                break;
        }
?>
        <tr>
            <th scope="row" class="text-center"><?php echo $nomor_anggota++; ?></th>
            <td class="text-center"><?php echo $nama_pengguna_anggota; ?></td>
            <td class="text-center"><?php echo $email_anggota; ?></td>
            <td class="text-center"><?php echo $telepon_anggota; ?></td>
            <td class="text-center"><?php echo $status_text; ?></td>
            <td class="text-center">
                <form method="POST" action="ubah_kyc.php" id="form-<?php echo $id_anggota; ?>">
                    <input type="hidden" name="id_anggota" value="<?php echo $id_anggota; ?>">
                    <select name="new_status" class="form-select" onchange="document.getElementById('form-<?php echo $id_anggota; ?>').dataset.changed = 'true';">
                        <option value="2" <?php echo ($status_kyc == 2) ? 'selected' : ''; ?>>Pending</option>
                        <option value="1" <?php echo ($status_kyc == 1) ? 'selected' : ''; ?>>Approved</option>
                        <option value="0" <?php echo ($status_kyc == 0) ? 'selected' : ''; ?>>Not-Verified</option>
                    </select>
                    <input type="submit" name="update_kyc" value="Update" class="btn btn-sm btn-primary mt-2" onclick="if(!document.getElementById('form-<?php echo $id_anggota; ?>').dataset.changed) { return false; }">
                </form>
            </td>
        </tr>
<?php
    }
}
?>
            </tbody>    
        </table>
    </div>
</div>
