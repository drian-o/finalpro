<?php
ob_start();

include_once '../koneksi.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['kode_admin'])) {
    echo '
    <script>
        alert("Terjadi kesalahan, harap masuk kembali!");
        window.location.replace("' . $alamat_admin . 'keluar.php");
    </script>
    ';
    exit();
}
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row gy-4 mb-4">
        <div class="col-md-6">
            <div class="fw-bold fs-4 text-center text-md-start">Manajemen Turnover</div>
        </div>
        <div class="col-md-6">
            <div class="text-center text-md-end">
                <span><?php echo ucapan() . ', ' . tanggalIndonesia(date('Y-m-d'), true) . ', '; ?></span>
                <span id="jam_sekarang">Jam </span>
            </div>
        </div>
    </div>
    <div class="card table-responsive p-3 mb-4">
        <?php
        $anggota = mysqli_query($koneksi, "SELECT * FROM anggota ORDER BY tanggal_bergabung DESC");
        ?>
        <h5 class="card-header">Data Turnover Anggota</h5>
        <table class="table" id="table-turnover">
            <thead>
            <tr>
                <th scope="col" class="text-center">No.</th>
                <th scope="col" class="text-center">Nama Pengguna</th>
                <th scope="col" class="text-center">Turnover</th>
                <th scope="col" class="text-center">Terakhir Diperbarui</th>
                <th scope="col" class="text-center">Aksi</th>
            </tr>
            </thead>
            <tbody>
                <?php
                $nomor_anggota = 1;
                while ($data_anggota = mysqli_fetch_array($anggota)) {
                    $nama_pengguna = $data_anggota['nama_pengguna_anggota'];
                    $turnover_amount = $data_anggota['turnover_amount'];
                    $last_turnover_update = $data_anggota['last_turnover_update'];

                    $formatted_last_update = 'Belum ada';
                    if (!empty($last_turnover_update)) {
                        $datetime = new DateTime($last_turnover_update);
                        $formatted_last_update = $datetime->format('Y-m-d H:i:s');
                    }
                ?>
                    <tr>
                        <th scope="row" class="text-center"><?php echo $nomor_anggota++; ?></th>
                        <td class="text-center"><?php echo htmlspecialchars($nama_pengguna); ?></td>
                        <td class="text-center"><?php echo 'Rp.' . number_format($turnover_amount, 0, ',', '.'); ?></td>
                        <td class="text-center"><?php echo htmlspecialchars($formatted_last_update); ?></td>
                        <td class="text-center">
                            <a href="<?php echo $alamat_admin . 'ubah_turnover.php?nama=' . urlencode($nama_pengguna); ?>" class="btn btn-sm btn-primary waves-effect waves-light" aria-label="Ubah Turnover">
                                <span class="tf-icons mdi mdi-cog me-1"></span> Ubah
                            </a>
                        </td>
                    </tr>
                <?php
                }
                ?>
            </tbody>
        </table>
    </div>

    <div class="card table-responsive p-3">
        <h5 class="card-header">Status Cronjob Turnover</h5>
        <?php
        $logs = mysqli_query($koneksi, "SELECT * FROM cron_log WHERE process_name = 'update_turnover' ORDER BY created_at DESC");
        ?>
        <table class="table" id="table-log">
            <thead>
                <tr>
                    <th scope="col" class="text-center">Waktu</th>
                    <th scope="col" class="text-center">Status</th>
                    <th scope="col" class="text-center">Pesan</th>
                </tr>
            </thead>
            <tbody>
                <?php
                while ($log = mysqli_fetch_array($logs)) {
                    $status_class = '';
                    if ($log['status'] == 'ERROR') $status_class = 'text-danger';
                    else if ($log['status'] == 'SUCCESS') $status_class = 'text-success';
                    else $status_class = 'text-info';
                ?>
                    <tr>
                        <td class="text-center"><?php echo htmlspecialchars($log['created_at']); ?></td>
                        <td class="text-center <?php echo $status_class; ?>"><?php echo htmlspecialchars($log['status']); ?></td>
                        <td><?php echo htmlspecialchars($log['message']); ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>
<script src="assets/vendor/libs/jquery/jquery.js"></script>
<script src="https://cdn.datatables.net/2.0.3/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.0.3/js/dataTables.bootstrap5.js"></script>
<script>
    $(document).ready(function() {
        $('#table-turnover').DataTable();
        $('#table-log').DataTable({
            "order": [[0, "desc"]]
        });
    });
</script>