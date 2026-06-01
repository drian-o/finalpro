<?php
include_once '../koneksi.php';
include_once '../classes/class.nexusggr.php';
include_once '../classes/connectAPI.php'; // untuk kredensial API

if (!isset($_SESSION['kode_admin'])) {
    echo '
      <script>
        alert("Terjadi kesalahan, harap masuk kembali!");
        window.location.replace("'.$alamat_admin.'keluar.php");
      </script>
    ';
    exit();
}
?>
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">Manajemen Transaksi /</span> Nexus Bet History
    </h4>

    <div class="card mb-4">
        <div class="card-body">
            <form id="history-bet-form" class="row g-3 align-items-center">
                <div class="col-md-5">
                    <label for="start-time" class="form-label">Waktu Mulai:</label>
                    <input type="text" id="start-time" class="form-control" placeholder="YYYY-MM-DD HH:mm:ss" value="<?php echo date('Y-m-d 00:00:00'); ?>">
                </div>
                <div class="col-md-5">
                    <label for="end-time" class="form-label">Waktu Selesai:</label>
                    <input type="text" id="end-time" class="form-control" placeholder="YYYY-MM-DD HH:mm:ss" value="<?php echo date('Y-m-d 23:59:59'); ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100 mt-md-4">
                        <span class="d-none d-sm-inline-block">Tampilkan History Bet</span>
                        <span class="d-sm-none mdi mdi-cloud-download"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">History Bet</h5>
            <div id="loading-indicator" class="spinner-border spinner-border-sm" role="status" style="display: none;">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="bet-history-table" class="table table-bordered table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <th>History ID</th>
                            <th>User Code</th>
                            <th>Provider</th>
                            <th>Game Code</th>
                            <th>Bet Money</th>
                            <th>Win Money</th>
                            <th>Start Balance</th>
                            <th>End Balance</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody id="bet-history-body">
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
$(document).ready(function() {
    let dataTable = null;
    const form = $('#history-bet-form');
    const tableBody = $('#bet-history-body');
    const loadingIndicator = $('#loading-indicator');

    form.on('submit', function(e) {
        e.preventDefault();
        
        const startTime = $('#start-time').val();
        const endTime = $('#end-time').val();

        if (!startTime || !endTime) {
            alert('Mohon lengkapi waktu mulai dan selesai.');
            return;
        }
        
        loadingIndicator.show();
        if (dataTable) {
            dataTable.destroy();
        }
        tableBody.empty();

        $.ajax({
            url: 'ajax/ajax_history_bet.php',
            method: 'POST',
            dataType: 'json',
            data: {
                start_time: startTime,
                end_time: endTime
            },
            success: function(response) {
                if (response.success) {
                    if (response.transactions.length > 0) {
                        response.transactions.forEach(transaction => {
                            const row = `
                                <tr>
                                    <td>${transaction.history_id}</td>
                                    <td>${transaction.user_code}</td>
                                    <td>${transaction.provider_code}</td>
                                    <td>${transaction.game_code}</td>
                                    <td>${transaction.bet_money}</td>
                                    <td>${transaction.win_money}</td>
                                    <td>${transaction.user_start_balance}</td>
                                    <td>${transaction.user_end_balance}</td>
                                    <td>${transaction.created_at}</td>
                                </tr>
                            `;
                            tableBody.append(row);
                        });
                        dataTable = $('#bet-history-table').DataTable();
                    } else {
                        tableBody.append('<tr><td colspan="9" class="text-center">Tidak ada transaksi ditemukan.</td></tr>');
                    }
                } else {
                    alert('Gagal mengambil data: ' + response.message);
                    tableBody.append('<tr><td colspan="9" class="text-center">Gagal memuat data.</td></tr>');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                alert('Terjadi kesalahan jaringan atau server.');
                tableBody.append('<tr><td colspan="9" class="text-center">Terjadi kesalahan jaringan.</td></tr>');
            },
            complete: function() {
                loadingIndicator.hide();
            }
        });
    });
});
</script>