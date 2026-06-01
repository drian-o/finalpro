<?php
session_start();
include_once '../koneksi.php';

if (!isset($_SESSION['kode_admin'])) {
    echo '
      <script>
        alert("Terjadi kesalahan, harap masuk kembali!");
        window.location.replace("'.$alamat_admin.'keluar.php");
      </script>
    ';
    exit();
}

$providers = [];
$query_providers = mysqli_query($koneksi, "SELECT * FROM nexus_provider ORDER BY provider_name ASC");

if ($query_providers && mysqli_num_rows($query_providers) > 0) {
    while ($row = mysqli_fetch_assoc($query_providers)) {
        $providers[] = $row;
    }
}
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">Manajemen Game /</span> Nexus Providers
    </h4>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Daftar Provider</h5>
            <button id="update-providers-btn" class="btn btn-primary" type="button">
                Perbarui Provider Nexus
            </button>
        </div>
        <div class="card-body">
            <div id="loading-indicator" class="text-center" style="display: none;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Memproses pembaruan, mohon tunggu...</p>
            </div>
            <div id="provider-table-container" class="table-responsive">
                <table id="providers-table" class="table table-bordered table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <th>Kode Provider</th>
                            <th>Nama Provider</th>
                            <th>Tipe</th>
                            <th>Status</th>
                            <th>Gambar</th>
                            <th>Terakhir Diperbarui</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($providers)): ?>
                            <tr>
                                <td colspan="6" class="text-center">Tidak ada data provider yang ditemukan.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($providers as $provider): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($provider['provider_code']); ?></td>
                                    <td><?php echo htmlspecialchars($provider['provider_name']); ?></td>
                                    <td><?php echo htmlspecialchars($provider['provider_type'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($provider['provider_status']); ?></td>
                                    <td>
                                        <?php 
                                            $image_url = !empty($provider['provider_image']) ? htmlspecialchars($provider['provider_image']) : 'assets/img/default-provider-no-image.jpg';
                                        ?>
                                        <img src="<?php echo $image_url; ?>" alt="<?php echo htmlspecialchars($provider['provider_name']); ?>" width="50" style="object-fit: contain;">
                                    </td>
                                    <td><?php echo htmlspecialchars($provider['last_updated']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.3/css/dataTables.bootstrap5.css" />
<script src="https://cdn.datatables.net/2.0.3/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.0.3/js/dataTables.bootstrap5.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const updateProvidersBtn = document.getElementById('update-providers-btn');
    const loadingIndicator = document.getElementById('loading-indicator');
    const providerTableContainer = document.getElementById('provider-table-container');

    if (updateProvidersBtn) {
        updateProvidersBtn.addEventListener('click', function() {
            loadingIndicator.style.display = 'block';
            providerTableContainer.style.display = 'none';
            updateProvidersBtn.disabled = true;

            fetch('../process_update_provider.php', {
                method: 'POST'
            })
            .then(response => response.text())
            .then(text => {
                alert("Proses pembaruan selesai! Silakan periksa log atau refresh halaman.");
                console.log(text); // Tampilkan log dari proses update
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat memperbarui provider.');
            })
            .finally(() => {
                loadingIndicator.style.display = 'none';
                providerTableContainer.style.display = 'block';
                updateProvidersBtn.disabled = false;
                window.location.reload(); // Muat ulang halaman untuk menampilkan data baru
            });
        });
    }

    // Inisialisasi DataTables
    if ($.fn.DataTable) {
        $('#providers-table').DataTable({
            "pageLength": 10
        });
    }
});
</script>