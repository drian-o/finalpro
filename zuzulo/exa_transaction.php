<?php
// admin/exa_transaction.php
// File ini di-include oleh admin/index.php.
// Variabel $koneksi, $alamat_admin, dll., sudah tersedia dari admin/index.php.
// class GameXaAPI juga diasumsikan sudah di-include oleh index.php.

include_once '../koneksi.php'; // Tetap ada jika dibutuhkan oleh header/footer atau fungsi lain
include_once '../classes/class.exa.php'; // Tambahkan include ini secara eksplisit

// Perlindungan tambahan, meskipun index.php sudah melakukan pengecekan sesi.
if (!isset($_SESSION['kode_admin'])) {
    echo '<script>alert("Sesi tidak ditemukan, harap login kembali."); window.location.replace("'.$alamat_admin.'keluar.php");</script>';
    exit();
}

$transactions = []; // Variabel untuk menyimpan data transaksi
$errorMessage = ''; // Variabel untuk pesan error API
$users_list = []; // Variabel untuk menyimpan daftar pengguna

// --- Ambil daftar pengguna dari database lokal ---
$query_users = "SELECT nama_pengguna_anggota FROM anggota ORDER BY nama_pengguna_anggota ASC";
$result_users = mysqli_query($koneksi, $query_users);
if ($result_users) {
    while ($row_user = mysqli_fetch_assoc($result_users)) {
        $users_list[] = $row_user['nama_pengguna_anggota'];
    }
} else {
    $errorMessage = 'Gagal mengambil daftar pengguna dari database: ' . mysqli_error($koneksi);
    error_log("Error fetching users list: " . $errorMessage);
}

// Logika pengambilan data transaksi akan dipindahkan ke JavaScript
// Karena kita akan memuat data berdasarkan pilihan dropdown.

?>
<div class="container-xxl flex-grow-1 container-p-y">
  <h4 class="py-3 mb-4">
    <span class="text-muted fw-light">Menu Utama /</span> Daftar Transaksi GameXa
  </h4>

  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <h5 class="card-header">Data Transaksi</h5>
        <div class="card-body">
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="select_user_filter" class="form-label">Filter berdasarkan Pengguna:</label>
              <select class="form-select" id="select_user_filter">
                <option value="">-- Semua Pengguna --</option>
                <?php foreach ($users_list as $user_name): ?>
                  <option value="<?php echo htmlspecialchars($user_name); ?>">
                    <?php echo htmlspecialchars($user_name); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6 d-flex align-items-end justify-content-end">
              <button id="showAllTransactionsBtn" class="btn btn-secondary waves-effect waves-light">
                <span class="tf-icons mdi mdi-format-list-bulleted me-1"></span> Tampilkan Semua Transaksi
              </button>
            </div>
          </div>

          <div id="loadingMessage" class="alert alert-info text-center" style="display:block;">
            Memuat data transaksi...
          </div>
          <div id="errorMessageDisplay" class="alert alert-danger" role="alert" style="display:none;">
            <?php echo htmlspecialchars($errorMessage); ?>
          </div>

          <div class="table-responsive text-nowrap">
            <table class="table table-hover" id="transactionsTable">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Tipe</th>
                  <th>Jumlah</th>
                  <th>Mata Uang</th>
                  <th>S. Sebelum</th>
                  <th>S. Sesudah</th>
                  <th>Username</th>
                  <th>Nama Lengkap</th>
                  <th>Status</th>
                  <th>Tanggal Dibuat</th>
                  <th>TRX ID</th>
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
    var transactionsTable = null; // Variabel global untuk instance DataTables
    const selectUserFilter = $('#select_user_filter');
    const showAllTransactionsBtn = $('#showAllTransactionsBtn');
    const loadingMessage = $('#loadingMessage');
    const errorMessageDisplay = $('#errorMessageDisplay');

    // Fungsi untuk memuat data transaksi dari API
    function loadTransactions(usernameFilter = null) {
        loadingMessage.show();
        errorMessageDisplay.hide().text(''); // Sembunyikan dan kosongkan pesan error

        // Parameter untuk permintaan API
        let requestData = {
            page: 1,
            limit: 500, // Sesuaikan limit sesuai kebutuhan
            startDate: '<?php echo date('Y-m-d', strtotime('-30 days')); ?>', // Default 30 hari terakhir
            endDate: '<?php echo date('Y-m-d'); ?>' // Hari ini
        };

        if (usernameFilter) {
            requestData.search = usernameFilter; // Gunakan parameter search untuk filter username
        }

        $.ajax({
            url: '<?php echo $alamat_admin; ?>proses/fetch_transactions.php', // Path ke file PHP backend
            method: 'GET',
            dataType: 'json',
            data: requestData,
            success: function(response) {
                loadingMessage.hide();
                if (response.status === 'success') {
                    if (response.data.length > 0) {
                        initializeDataTable(response.data);
                    } else {
                        initializeDataTable([]); // Inisialisasi dengan data kosong
                        $('#transactionsTable tbody').html('<tr><td colspan="11" class="text-center">Tidak ada data transaksi yang ditemukan untuk filter ini.</td></tr>');
                    }
                } else {
                    errorMessageDisplay.text(response.message).show();
                    initializeDataTable([]); // Inisialisasi tabel kosong saat error
                }
            },
            error: function(xhr, status, error) {
                loadingMessage.hide();
                errorMessageDisplay.text('Terjadi kesalahan jaringan atau server: ' + error + ' - ' + xhr.responseText).show();
                initializeDataTable([]); // Inisialisasi tabel kosong saat error
                console.error("AJAX Error: ", status, error, xhr.responseText);
            }
        });
    }

    // Fungsi untuk menginisialisasi atau mereload DataTables
    function initializeDataTable(data) {
        // Hancurkan instance DataTables yang sudah ada jika ada
        if (transactionsTable !== null) {
            transactionsTable.destroy();
            $('#transactionsTable tbody').empty(); // Kosongkan tbody secara manual
        }

        transactionsTable = $('#transactionsTable').DataTable({
            data: data,
            columns: [
                { data: 'id', defaultContent: '-' },
                {
                    data: 'transaction_type',
                    defaultContent: '-',
                    render: function(data, type, row) {
                        let badgeClass = 'secondary';
                        if (data === 'deposit') badgeClass = 'success';
                        else if (data === 'withdrawal') badgeClass = 'warning';
                        else if (data === 'bet') badgeClass = 'danger';
                        else if (data === 'win') badgeClass = 'primary';
                        return `<span class="badge bg-label-${badgeClass}">${data}</span>`;
                    }
                },
                {
                    data: 'amount',
                    defaultContent: '0.00',
                    render: $.fn.dataTable.render.number('.', ',', 2, '') // Format angka
                },
                { data: 'currency', defaultContent: '-' },
                {
                    data: 'balance_before',
                    defaultContent: '0.00',
                    render: $.fn.dataTable.render.number('.', ',', 2, '') // Format angka
                },
                {
                    data: 'balance_after',
                    defaultContent: '0.00',
                    render: $.fn.dataTable.render.number('.', ',', 2, '') // Format angka
                },
                { data: 'username', defaultContent: '-' },
                { data: 'full_name', defaultContent: '-' },
                {
                    data: 'status',
                    defaultContent: '-',
                    render: function(data, type, row) {
                        let badgeClass = 'secondary';
                        if (data === 'completed') badgeClass = 'success';
                        else if (data === 'pending') badgeClass = 'warning';
                        else if (data === 'failed') badgeClass = 'danger';
                        return `<span class="badge bg-label-${badgeClass}">${data}</span>`;
                    }
                },
                {
                    data: 'created_at',
                    defaultContent: '-',
                    render: function(data, type, row) {
                        if (data) {
                            // Konversi format ISO 8601 ke format yang mudah dibaca
                            const date = new Date(data);
                            return date.toLocaleString('id-ID', {
                                year: 'numeric', month: '2-digit', day: '2-digit',
                                hour: '2-digit', minute: '2-digit', second: '2-digit'
                            });
                        }
                        return data;
                    }
                },
                { data: 'transaction_id', defaultContent: '-' }
            ],
            "order": [[9, "desc"]], // Urutkan berdasarkan kolom 'Tanggal Dibuat' (indeks 9) secara descending
            "pageLength": 25, // Default menampilkan 25 baris per halaman
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json" // Bahasa Indonesia
            }
        });
    }

    // --- Event listener untuk dropdown filter pengguna ---
    selectUserFilter.on('change', function() {
        const selectedUsername = $(this).val(); // Mengambil nilai username yang dipilih
        loadTransactions(selectedUsername); // Memuat ulang transaksi dengan filter username
    });

    // --- Event listener untuk tombol "Tampilkan Semua Transaksi" ---
    showAllTransactionsBtn.on('click', function() {
        selectUserFilter.val(''); // Reset dropdown ke opsi "Semua Pengguna"
        loadTransactions(); // Memuat ulang semua transaksi (tanpa filter)
    });

    // --- Muat data otomatis saat halaman pertama kali dimuat ---
    loadTransactions(); // Memanggil fungsi untuk pertama kali memuat data (tanpa filter awal)
});
</script>