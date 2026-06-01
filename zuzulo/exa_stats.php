<?php
// admin/exa_stats.php
// File ini di-include oleh admin/index.php.
// Variabel $koneksi, $alamat_admin, dll., sudah tersedia dari admin/index.php.
// class GameXaAPI juga diasumsikan sudah di-include oleh index.php.

include_once '../koneksi.php'; // Tetap ada jika dibutuhkan oleh header/footer atau fungsi lain
// include_once '../classes/class.exa.php'; // Tidak perlu di sini, karena AJAX akan memanggil file lain

// Perlindungan tambahan, meskipun index.php sudah melakukan pengecekan sesi.
if (!isset($_SESSION['kode_admin'])) {
    echo '<script>alert("Sesi tidak ditemukan, harap login kembali."); window.location.replace("'.$alamat_admin.'keluar.php");</script>';
    exit();
}

// Set tanggal default untuk form
$defaultStartDate = date('Y-m-d', strtotime('-7 days'));
$defaultEndDate = date('Y-m-d');

?>
<div class="container-xxl flex-grow-1 container-p-y">
  <h4 class="py-3 mb-4">
    <span class="text-muted fw-light">Menu Utama /</span> Statistik Transaksi GameXa
  </h4>

  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <h5 class="card-header">Filter Statistik</h5>
        <div class="card-body">
          <form id="filterStatsForm" class="mb-4">
            <div class="row g-3">
              <div class="col-md-5">
                <label for="startDate" class="form-label">Tanggal Mulai:</label>
                <input type="date" class="form-control" id="startDate" name="start_date" value="<?php echo htmlspecialchars($defaultStartDate); ?>" required>
              </div>
              <div class="col-md-5">
                <label for="endDate" class="form-label">Tanggal Berakhir:</label>
                <input type="date" class="form-control" id="endDate" name="end_date" value="<?php echo htmlspecialchars($defaultEndDate); ?>" required>
              </div>
              <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100" id="loadStatsBtn">
                  <span class="tf-icons mdi mdi-chart-bar me-1"></span> Tampilkan Statistik
                </button>
              </div>
            </div>
          </form>

          <div id="loadingMessage" class="alert alert-info text-center" style="display:none;">
            Memuat statistik transaksi...
          </div>
          <div id="errorMessageDisplay" class="alert alert-danger" role="alert" style="display:none;">
            </div>

          <h6 class="mt-4 mb-2">Statistik Berdasarkan Tipe Transaksi</h6>
          <div class="table-responsive text-nowrap">
            <table class="table table-hover" id="transactionTypeStatsTable">
              <thead>
                <tr>
                  <th>Tipe Transaksi</th>
                  <th>Jumlah Transaksi</th>
                  <th>Total Nominal</th>
                  <th>Rata-rata Nominal</th>
                </tr>
              </thead>
              <tbody class="table-border-bottom-0">
                </tbody>
            </table>
          </div>

          <h6 class="mt-5 mb-2">Statistik Harian</h6>
          <div class="table-responsive text-nowrap">
            <table class="table table-hover" id="dailyStatsTable">
              <thead>
                <tr>
                  <th>Tanggal</th>
                  <th>Jumlah Transaksi</th>
                  <th>Total Nominal</th>
                </tr>
              </thead>
              <tbody class="table-border-bottom-0">
                </tbody>
            </table>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<script type="text/javascript" src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function() {
    var transactionTypeStatsTable = null;
    var dailyStatsTable = null;

    const filterStatsForm = $('#filterStatsForm');
    const loadStatsBtn = $('#loadStatsBtn');
    const loadingMessage = $('#loadingMessage');
    const errorMessageDisplay = $('#errorMessageDisplay');
    const startDateInput = $('#startDate');
    const endDateInput = $('#endDate');

    // Fungsi untuk memuat data statistik dari API
    function loadTransactionStats() {
        const startDate = startDateInput.val();
        const endDate = endDateInput.val();

        if (!startDate || !endDate) {
            errorMessageDisplay.text('Mohon pilih tanggal mulai dan tanggal berakhir.').show();
            return;
        }
        if (new Date(startDate) > new Date(endDate)) {
            errorMessageDisplay.text('Tanggal mulai tidak boleh lebih dari tanggal berakhir.').show();
            return;
        }

        loadStatsBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memuat...');
        loadingMessage.show();
        errorMessageDisplay.hide().text('');

        $.ajax({
            url: '<?php echo $alamat_admin; ?>proses/fetch_transaction_stats.php', // Path ke file PHP backend
            method: 'GET',
            dataType: 'json',
            data: {
                start_date: startDate,
                end_date: endDate
            },
            success: function(response) {
                loadingMessage.hide();
                if (response.status === 'success') {
                    const statsData = response.data;
                    
                    // Inisialisasi tabel untuk Statistik Berdasarkan Tipe Transaksi
                    initializeTransactionTypeStatsTable(statsData.stats || []);

                    // Inisialisasi tabel untuk Statistik Harian
                    initializeDailyStatsTable(statsData.daily || []);

                } else {
                    errorMessageDisplay.text(response.message).show();
                    initializeTransactionTypeStatsTable([]);
                    initializeDailyStatsTable([]);
                }
            },
            error: function(xhr, status, error) {
                loadingMessage.hide();
                errorMessageDisplay.text('Terjadi kesalahan jaringan atau server: ' + error + ' - ' + xhr.responseText).show();
                initializeTransactionTypeStatsTable([]);
                initializeDailyStatsTable([]);
                console.error("AJAX Error: ", status, error, xhr.responseText);
            },
            complete: function() {
                loadStatsBtn.prop('disabled', false).html('<span class="tf-icons mdi mdi-chart-bar me-1"></span> Tampilkan Statistik');
            }
        });
    }

    // Fungsi untuk menginisialisasi atau mereload DataTables Statistik Berdasarkan Tipe Transaksi
    function initializeTransactionTypeStatsTable(data) {
        if (transactionTypeStatsTable !== null) {
            transactionTypeStatsTable.destroy();
            $('#transactionTypeStatsTable tbody').empty();
        }

        transactionTypeStatsTable = $('#transactionTypeStatsTable').DataTable({
            data: data,
            columns: [
                { data: 'transaction_type', defaultContent: '-' },
                { data: 'count', defaultContent: '0' },
                { 
                    data: 'total_amount', 
                    defaultContent: '0.00',
                    render: $.fn.dataTable.render.number('.', ',', 2, 'Rp ')
                },
                { 
                    data: 'avg_amount', 
                    defaultContent: '0.00',
                    render: $.fn.dataTable.render.number('.', ',', 2, 'Rp ')
                }
            ],
            "paging": false,
            "searching": false,
            "info": false,
            "ordering": false,
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json"
            }
        });
    }

    // Fungsi untuk menginisialisasi atau mereload DataTables Statistik Harian
    function initializeDailyStatsTable(data) {
        if (dailyStatsTable !== null) {
            dailyStatsTable.destroy();
            $('#dailyStatsTable tbody').empty();
        }

        dailyStatsTable = $('#dailyStatsTable').DataTable({
            data: data,
            columns: [
                { 
                    data: 'date', 
                    defaultContent: '-',
                    render: function(data, type, row) {
                        if (data) {
                            const date = new Date(data); // Asumsi format ISO 8601 dari API
                            return date.toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' });
                        }
                        return data;
                    }
                },
                { data: 'count', defaultContent: '0' },
                { 
                    data: 'total_amount', 
                    defaultContent: '0.00',
                    render: $.fn.dataTable.render.number('.', ',', 2, 'Rp ')
                }
            ],
            "order": [[0, "desc"]], // Urutkan berdasarkan tanggal descending
            "pageLength": 10,
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json"
            }
        });
    }

    // --- Event listener untuk form filter ---
    filterStatsForm.on('submit', function(e) {
        e.preventDefault(); // Mencegah submit form default
        loadTransactionStats();
    });

    // --- Muat data otomatis saat halaman pertama kali dimuat (dengan tanggal default) ---
    loadTransactionStats();
});
</script>