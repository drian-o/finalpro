<?php
ob_start(); // Mulai buffering output

include_once '../koneksi.php';

// Mulai sesi jika belum ada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Periksa apakah sesi admin aktif
if (!isset($_SESSION['kode_admin'])) {
    echo '
    <script>
        alert("Terjadi kesalahan, harap masuk kembali!");
        window.location.replace("' . $alamat_admin . 'keluar.php");
    </script>
    ';
    exit(); // Menghentikan eksekusi skrip lebih lanjut
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_anggota_deposit = $_POST['id_anggota_deposit'];
    $nama_pengguna_anggota_deposit = $_POST['nama_pengguna_anggota'];
    $kode_deposit = $_POST['kode_deposit'];
    $asal_deposit = $_POST['asal_deposit'];
    $tujuan_deposit = $_POST['tujuan_deposit'];
    $bonus_deposit = $_POST['bonus_deposit'];
    $jumlah_deposit = $_POST['jumlah_deposit'];
    $tanggal_deposit = $_POST['tanggal_deposit'];
    $status_deposit = $_POST['status_deposit'];

    // Simpan data deposit
    $query_copy = mysqli_query($koneksi, "INSERT INTO deposit (id_anggota_deposit, nama_pengguna_anggota_deposit, kode_deposit, asal_deposit, tujuan_deposit, bonus_deposit, jumlah_deposit, tanggal_deposit, status_deposit) 
                        VALUES ('$id_anggota_deposit', '$nama_pengguna_anggota_deposit', '$kode_deposit', '$asal_deposit', '$tujuan_deposit', '$bonus_deposit', '$jumlah_deposit', '$tanggal_deposit', '$status_deposit')");

    if (!$query_copy) {
        die('Gagal menyalin data deposit: ' . mysqli_error($koneksi));
    } else {
        echo '
          <script>
            alert("Data deposit berhasil disalin.");
            window.location.replace("'.$alamat_admin.'deposit");
          </script>
        ';
    }
}
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row gy-4 mb-4">
        <div class="col-md-6">
            <div class="fw-bold fs-4 text-center text-md-start">Deposit</div>
        </div>
        <div class="col-md-6">
            <div class="text-center text-md-end">
                <span><?php echo ucapan() . ', ' . tanggalIndonesia(date('Y-m-d'), true) . ', '; ?></span>
                <span id="jam_sekarang">Jam </span>
            </div>
        </div>
    </div>
    <div class="card table-responsive p-3">
    <?php
    // Query untuk mendapatkan data deposit terurut berdasarkan tanggal deposit descending
    $deposit = mysqli_query($koneksi, "SELECT * FROM deposit ORDER BY tanggal_deposit DESC");
    ?>
    <table class="table" id="example">
        <thead>
        <tr>
            <th scope="col" class="text-center">No.</th>
            <th scope="col" class="text-center">Kode</th>
            <th scope="col" class="text-center">UserName</th>
            <th scope="col" class="text-center">Asal</th>
            <th scope="col" class="text-center">Tujuan</th>
            <th scope="col" class="text-center">Bonus</th>
            <th scope="col" class="text-center">Jumlah</th>
            <th scope="col" class="text-center">Tanggal</th>
            <th scope="col" class="text-center">Status</th>
            <th scope="col" class="text-center">Aksi</th>
        </tr>
        </thead>

        <tbody>
            <?php
            $nomor_deposit = 1;
            while ($data_deposit = mysqli_fetch_array($deposit)) {
                $id_deposit = $data_deposit['id_deposit'];
                $id_anggota_deposit = $data_deposit['id_anggota_deposit'];
                $kode_deposit = $data_deposit['kode_deposit'];
                $asal_deposit = $data_deposit['asal_deposit'];
                $tujuan_deposit = $data_deposit['tujuan_deposit'];
                $bonus_deposit = $data_deposit['bonus_deposit'];
                $jumlah_deposit = $data_deposit['jumlah_deposit'];
                $tanggal_deposit = $data_deposit['tanggal_deposit'];
                $status_deposit = $data_deposit['status_deposit'];

                // Ambil nama pengguna anggota deposit
                $anggota_deposit = mysqli_query($koneksi, "SELECT * FROM anggota WHERE id_anggota = '$id_anggota_deposit'");
                if (mysqli_num_rows($anggota_deposit) == 0) {
                    $nama_pengguna_anggota_deposit = "Data anggota tidak ada atau telah dihapus";
                } else {
                    $data_anggota_deposit = mysqli_fetch_array($anggota_deposit);
                    $nama_pengguna_anggota_deposit = $data_anggota_deposit['nama_pengguna_anggota'];
                }

                // Tentukan kelas status untuk warna latar belakang
                switch ($status_deposit) {
                    case 'diproses':
                        $status_class = 'text-warning';
                        break;
                    case 'dibatalkan':
                        $status_class = 'text-danger';
                        break;
                    case 'disetujui':
                        $status_class = 'text-success';
                        break;
                    default:
                        $status_class = '';
                        break;
                }
            ?>
                <tr>
                    <th scope="row" class="text-center"><?php echo $nomor_deposit++; ?></th>
                    <td class="text-center"><?php echo $kode_deposit; ?></td>
                    <td class="text-center"><?php echo $nama_pengguna_anggota_deposit; ?></td>
                    <td class="text-center"><?php echo $asal_deposit; ?></td>
                    <td class="text-center"><?php echo $tujuan_deposit; ?></td>
                    <td class="text-center"><?php echo $bonus_deposit; ?></td>
                    <td class="text-center"><?php echo 'Rp.' . number_format($jumlah_deposit, 0, ',', '.'); ?></td>
                    <td class="text-center"><?php echo jamTanggalIndonesia($tanggal_deposit); ?></td>
                    <td class="<?php echo $status_class; ?> text-center" style="padding: 6px;"><?php echo $status_deposit; ?></td>
                    <td class="text-center">
                        <div class="btn-group" role="group" aria-label="Aksi Ubah dan Copy">
                            <a href="<?php echo $alamat_admin . 'ubah_deposit/' . $id_deposit; ?>" class="btn btn-sm btn-primary waves-effect waves-light" aria-label="Ubah">
                                <span class="tf-icons mdi mdi-cog me-1"></span> 
                            </a>
                            <button type="button" class="btn btn-sm btn-success waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#modalUbahDeposit<?php echo $id_deposit; ?>" aria-label="Ubah Salinan Data Deposit">
                                <span class="tf-icons mdi mdi-content-copy me-1"></span> 
                            </button>
                        </div>
                    </td>
                </tr>

                <!-- Modal Ubah Data Deposit -->
                <div class="modal fade" id="modalUbahDeposit<?php echo $id_deposit; ?>" tabindex="-1" aria-labelledby="modalUbahDepositLabel<?php echo $id_deposit; ?>" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalUbahDepositLabel<?php echo $id_deposit; ?>">Ubah Salinan Data Deposit</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="card p-4">
                                    <form method="POST" action="">
                                        <div class="mb-3">
                                            <label for="id_anggota_deposit" class="form-label">ID Anggota Deposit</label>
                                            <input type="text" class="form-control" id="id_anggota_deposit" name="id_anggota_deposit" value="<?php echo $data_deposit['id_anggota_deposit']; ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="nama_pengguna_anggota" class="form-label">Nama Pengguna Deposit</label>
                                            <input type="text" class="form-control" id="nama_pengguna_anggota" name="nama_pengguna_anggota" value="<?php echo $nama_pengguna_anggota_deposit; ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="kode_deposit" class="form-label">Kode Deposit</label>
                                            <input type="text" class="form-control" id="kode_deposit" name="kode_deposit" value="<?php echo $data_deposit['kode_deposit']; ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="asal_deposit" class="form-label">Asal Deposit</label>
                                            <input type="text" class="form-control" id="asal_deposit" name="asal_deposit" value="<?php echo $data_deposit['asal_deposit']; ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="tujuan_deposit" class="form-label">Tujuan Deposit</label>
                                            <input type="text" class="form-control" id="tujuan_deposit" name="tujuan_deposit" value="<?php echo $data_deposit['tujuan_deposit']; ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="bonus_deposit" class="form-label">Bonus Deposit</label>
                                            <input type="text" class="form-control" id="bonus_deposit" name="bonus_deposit" value="<?php echo $data_deposit['bonus_deposit']; ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="jumlah_deposit" class="form-label">Jumlah Deposit</label>
                                            <input type="text" class="form-control" id="jumlah_deposit" name="jumlah_deposit" value="<?php echo $jumlah_deposit; ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="tanggal_deposit" class="form-label">Tanggal Deposit</label>
                                            <input type="text" class="form-control" id="tanggal_deposit" name="tanggal_deposit" value="<?php echo $data_deposit['tanggal_deposit']; ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="status_deposit" class="form-label">Status Deposit</label>
                                            <select class="form-control" id="status_deposit" name="status_deposit" required>
                                                <option value="diproses" <?php if ($data_deposit['status_deposit'] == 'diproses') echo 'selected'; ?>>Diproses</option>
                                                <option value="disetujui" <?php if ($data_deposit['status_deposit'] == 'disetujui') echo 'selected'; ?>>Disetujui</option>
                                                <option value="dibatalkan" <?php if ($data_deposit['status_deposit'] == 'dibatalkan') echo 'selected'; ?>>Dibatalkan</option>
                                            </select>
                                        </div>
                                        <div class="text-end">
                                            <button type="submit" class="btn btn-primary waves-effect waves-light">Simpan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            <?php
            }
            ?>
        </tbody>
    </table>
    </div>
</div>
