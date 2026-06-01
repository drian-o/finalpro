<?php
// admin/voucher.php
// Halaman utama manajemen voucher admin (one-page).

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Pastikan hanya admin yang bisa mengakses halaman ini
if (!isset($_SESSION['kode_admin'])) {
    // Sesuaikan path ke halaman logout admin Anda
    echo '<script>alert("Sesi tidak ditemukan, harap login kembali."); window.location.replace("' . $alamat_admin . 'keluar.php");</script>';
    exit();
}

include_once '../koneksi.php'; // Sesuaikan path ke koneksi.php
// Asumsi $alamat_admin tersedia dari koneksi.php atau tempat lain yang di-include
// Jika tidak, Anda perlu mendefinisikannya di sini, misal:
// $alamat_admin = '/bosigmaadmin/'; 
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row gy-4 mb-4">
        <div class="col-md-12">
            <div class="fw-bold fs-4 text-center text-md-start">Manajemen Voucher & Klaim</div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Formulir Voucher</h5>
            <small class="text-muted">Buat voucher baru atau ubah yang sudah ada.</small>
        </div>
        <form id="voucherForm" class="card-body">
            <input type="hidden" id="voucherId" name="id" value="">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="form-floating-outline mb-3">
                        <label for="voucherCode">Kode Voucher</label>
                        <input type="text" id="voucherCode" name="voucher_code" class="form-control" placeholder="Contoh: BONUSNATAL2024" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating-outline mb-3">
                        <label for="amount">Jumlah Saldo (Rp.)</label>
                        <input type="number" id="amount" name="amount" class="form-control" placeholder="Contoh: 100000" required min="1" step="any">
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-floating-outline mb-3">
                        <label for="isActive">Status Aktif</label>
                        <select id="isActive" name="is_active" class="form-control">
                            <option value="1">Aktif</option>
                            <option value="0">Tidak Aktif</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="pt-4 text-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="mdi mdi-content-save me-1"></i> Simpan Voucher
                </button>
                <button type="button" id="resetForm" class="btn btn-warning">
                    <i class="mdi mdi-refresh me-1"></i> Reset Form
                </button>
            </div>
        </form>
    </div>

    <div id="actionConfirmCard" class="card text-white mb-4" style="display: none;">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h5 id="actionConfirmTitle" class="card-title text-white"></h5>
                <p id="actionConfirmMessage" class="card-text"></p>
            </div>
            <div id="actionConfirmButtons" style="display: none;">
                <button id="confirmActionButton" class="btn btn-success me-2">Ya, Lanjutkan!</button>
                <button id="cancelActionButton" class="btn btn-secondary">Batal</button>
            </div>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Daftar Voucher</h5>
            <small class="text-muted">Kelola voucher yang tersedia untuk anggota.</small>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="vouchersTable" class="table table-striped table-hover dt-responsive nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Kode Voucher</th>
                            <th>Jumlah</th>
                            <th>Status</th>
                            <th>Dibuat</th>
                            <th>Diperbarui</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Riwayat Klaim Voucher</h5>
            <small class="text-muted">Lihat voucher yang telah berhasil diklaim oleh anggota.</small>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="claimedVouchersTable" class="table table-striped table-hover dt-responsive nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th>ID Klaim</th>
                            <th>Kode Voucher</th>
                            <th>Username Anggota</th>
                            <th>Jumlah Voucher</th>
                            <th>Waktu Klaim</th>
                        </tr>
                    </thead>
                    <tbody>
                        </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.12.0/dist/sweetalert2.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/2.0.1/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.html5.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.print.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.colVis.min.js"></script>


<script>
$(document).ready(function() {
    // Inisialisasi DataTables untuk daftar voucher
    const vouchersTable = $('#vouchersTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "<?php echo $alamat_admin; ?>proses/get_vouchers.php",
            "type": "POST"
        },
        "columns": [
            { "data": "id" },
            { "data": "voucher_code" },
            { "data": "amount", render: $.fn.dataTable.render.number('.', ',', 0, 'Rp ') },
            { "data": "is_active", render: function(data, type, row) {
                return data == 1 ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-danger">Tidak Aktif</span>';
            }},
            { "data": "created_at" },
            { "data": "updated_at" },
            { "data": null, "defaultContent": '<button class="btn btn-warning btn-sm edit-btn me-2"><i class="mdi mdi-pencil"></i> Edit</button><button class="btn btn-danger btn-sm delete-btn"><i class="mdi mdi-trash-can"></i> Delete</button>' }
        ],
        "order": [[0, "desc"]],
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json"
        }
    });

    // Inisialisasi DataTables untuk riwayat klaim voucher
    const claimedVouchersTable = $('#claimedVouchersTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "<?php echo $alamat_admin; ?>proses/get_claimed_vouchers.php",
            "type": "POST"
        },
        "columns": [
            { "data": "claim_id" },
            { "data": "voucher_code" },
            { "data": "username_anggota" },
            { "data": "amount", render: $.fn.dataTable.render.number('.', ',', 0, 'Rp ') },
            { "data": "claimed_at" }
        ],
        "order": [[0, "desc"]],
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json"
        }
    });
    
    // Variabel untuk menyimpan ID voucher yang akan dihapus
    let voucherIdToDelete = null;

    // Fungsi untuk menampilkan kartu konfirmasi/notifikasi
    function showActionCard(status, title, message, showButtons = false) {
        const card = $('#actionConfirmCard');
        const titleEl = $('#actionConfirmTitle');
        const messageEl = $('#actionConfirmMessage');
        const buttonsEl = $('#actionConfirmButtons');

        card.removeClass('bg-success bg-danger');
        
        if (status === 'success') {
            card.addClass('bg-success');
        } else if (status === 'error') {
            card.addClass('bg-danger');
        } else { // status = danger (for delete confirmation)
            card.addClass('bg-danger');
        }

        titleEl.text(title);
        messageEl.text(message);
        buttonsEl.toggle(showButtons); // Menampilkan atau menyembunyikan tombol
        
        card.slideDown();

        // Menggulir ke kartu
        $('html, body').animate({
            scrollTop: card.offset().top - 20 // Kurangi 20px agar tidak terlalu mepet
        }, 500);

        // Jika bukan kartu konfirmasi (showButtons = false), sembunyikan setelah 5 detik
        if (!showButtons) {
            setTimeout(() => {
                card.slideUp();
            }, 5000);
        }
    }
    
    // Reset Form
    $('#resetForm').on('click', function() {
        $('#voucherForm')[0].reset();
        $('#voucherId').val('');
        $('#voucherCode').prop('disabled', false); // Aktifkan kembali input kode
        $('#voucherForm button[type="submit"]').html('<i class="mdi mdi-content-save me-1"></i> Simpan Voucher').removeClass('btn-success').addClass('btn-primary');
        $('#isActive').val('1');
        $('#actionConfirmCard').slideUp(); // Sembunyikan kartu notifikasi
    });

    // Handle Form Submission (Add/Edit Voucher)
    $('#voucherForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize();
        const voucherId = $('#voucherId').val();
        const action = voucherId ? 'edit' : 'add';

        const title = "Konfirmasi";
        const message = `Anda yakin ingin ${action === 'add' ? 'menambah' : 'mengubah'} voucher ini?`;
        
        // Tampilkan kartu konfirmasi sebelum AJAX
        showActionCard('danger', title, message, true);
        
        // Tentukan aksi ketika tombol "Ya, Lanjutkan!" di klik
        $('#confirmActionButton').off('click').on('click', function() {
            $.ajax({
                url: '<?php echo $alamat_admin; ?>proses/process_voucher_admin.php',
                type: 'POST',
                data: formData + '&action=' + action,
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        showActionCard('success', 'Berhasil!', response.message, false);
                        $('#resetForm').click();
                        vouchersTable.ajax.reload();
                    } else {
                        showActionCard('error', 'Gagal!', response.message, false);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX error:", status, error);
                    showActionCard('error', 'Error!', 'Terjadi kesalahan komunikasi dengan server.', false);
                },
                complete: function() {
                    $('#actionConfirmButtons').hide();
                }
            });
        });
        
        // Tentukan aksi ketika tombol "Batal" di klik
        $('#cancelActionButton').off('click').on('click', function() {
            $('#actionConfirmCard').slideUp();
        });
    });

    // Handle Edit Button Click
    $('#vouchersTable tbody').on('click', '.edit-btn', function() {
        const data = vouchersTable.row($(this).parents('tr')).data();
        $('#voucherId').val(data.id);
        $('#voucherCode').val(data.voucher_code);
        $('#amount').val(data.amount);
        $('#isActive').val(data.is_active);
        
        // Change button text and color to indicate editing mode
        $('#voucherForm button[type="submit"]').html('<i class="mdi mdi-pencil me-1"></i> Update Voucher').removeClass('btn-primary').addClass('btn-success');
        
        // Gulir ke atas formulir
        $('html, body').animate({
            scrollTop: $('#voucherForm').offset().top - 20
        }, 500);
    });

    // Handle Delete Button Click
    $('#vouchersTable tbody').on('click', '.delete-btn', function() {
        const data = vouchersTable.row($(this).parents('tr')).data();
        voucherIdToDelete = data.id;

        const deleteMessage = `Anda yakin ingin menghapus voucher "${data.voucher_code}" ini? Tindakan ini tidak dapat dibatalkan.`;
        showActionCard('danger', 'Konfirmasi Penghapusan', deleteMessage, true);
        
        // Tentukan aksi ketika tombol "Ya, Hapus!" di klik
        $('#confirmActionButton').off('click').on('click', function() {
            if (voucherIdToDelete) {
                $.ajax({
                    url: '<?php echo $alamat_admin; ?>proses/process_voucher_admin.php',
                    type: 'POST',
                    data: { action: 'delete', id: voucherIdToDelete },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            showActionCard('success', 'Dihapus!', response.message, false);
                            vouchersTable.ajax.reload();
                            claimedVouchersTable.ajax.reload();
                            $('#resetForm').click();
                        } else {
                            showActionCard('error', 'Gagal!', response.message, false);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX error:", status, error);
                        showActionCard('error', 'Error!', 'Terjadi kesalahan komunikasi dengan server.', false);
                    },
                    complete: function() {
                        voucherIdToDelete = null;
                        $('#actionConfirmCard').slideUp();
                    }
                });
            }
        });

        // Tentukan aksi ketika tombol "Batal" di klik
        $('#cancelActionButton').off('click').on('click', function() {
            $('#actionConfirmCard').slideUp();
            voucherIdToDelete = null;
        });
    });
});
</script>