<?php
// admin/voucher/index.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Pastikan hanya admin yang bisa mengakses halaman ini
if (!isset($_SESSION['kode_admin'])) {
    header("Location: ../../keluar.php"); // Sesuaikan path ke halaman logout admin Anda
    exit();
}

include_once __DIR__ . '/../../koneksi.php'; // Sesuaikan path ke koneksi.php
// include_once __DIR__ . '/../../includes/header.php'; // Asumsi Anda punya header admin
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Voucher & Klaim - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.12.0/dist/sweetalert2.min.css">

    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.0.1/css/buttons.dataTables.min.css">


    <style>
        body { font-family: sans-serif; margin: 20px; background-color: #f4f7f6; }
        .container-xxl { max-width: 1200px; margin: auto; padding: 20px; background-color: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .card { border: 1px solid #e0e0e0; border-radius: 8px; margin-bottom: 20px; }
        .card-header { background-color: #f9f9f9; padding: 15px 20px; border-bottom: 1px solid #e0e0e0; font-size: 1.2rem; font-weight: bold; }
        .card-body { padding: 20px; }
        .form-floating-outline { margin-bottom: 15px; }
        .form-control { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        .btn { padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-primary { background-color: #007bff; color: white; }
        .btn-success { background-color: #28a745; color: white; }
        .btn-danger { background-color: #dc3545; color: white; }
        .btn-warning { background-color: #ffc107; color: #333; }
        .btn-sm { padding: 5px 10px; font-size: 0.875rem; }
        table.dataTable { width: 100% !important; margin-top: 20px; border-collapse: collapse; }
        table.dataTable thead th { background-color: #f2f2f2; padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        table.dataTable tbody td { padding: 10px; border-bottom: 1px solid #eee; }
        table.dataTable tbody tr:nth-child(even) { background-color: #f9f9f9; }
        .dataTables_wrapper .dataTables_paginate .paginate_button { padding: 5px 10px; margin: 0 2px; border: 1px solid #ccc; border-radius: 3px; cursor: pointer; }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover { background-color: #007bff; color: white; border-color: #007bff; }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover { background-color: #e9ecef; }
        .dataTables_wrapper .dataTables_filter input { margin-left: 0.5em; padding: 5px; border-radius: 4px; border: 1px solid #ccc; }
        .dataTables_wrapper .dataTables_length select { margin: 0 0.5em; padding: 5px; border-radius: 4px; border: 1px solid #ccc; }
        /* SweetAlert2 custom styles */
        .swal2-container { z-index: 99999 !important; }
    </style>
</head>
<body>

<?php // include_once __DIR__ . '/../../includes/header.php'; // Letakkan header Anda di sini ?>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row gy-4 mb-4">
        <div class="col-md-12">
            <div class="fw-bold fs-4 text-center text-md-start">Manajemen Voucher & Klaim</div>
        </div>
    </div>

    <div class="card mb-4">
        <h5 class="card-header">Tambah/Edit Voucher</h5>
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
                <button type="submit" class="btn btn-primary me-2"><i class="fas fa-save me-1"></i> Simpan Voucher</button>
                <button type="button" id="resetForm" class="btn btn-warning"><i class="fas fa-undo me-1"></i> Reset Form</button>
            </div>
        </form>
    </div>

    <div class="card mb-4">
        <h5 class="card-header">Daftar Voucher</h5>
        <div class="card-body">
            <table id="vouchersTable" class="display responsive nowrap">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Kode Voucher</th>
                        <th>Jumlah</th>
                        <th>Aktif</th>
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

    <div class="card">
        <h5 class="card-header">Klaim Voucher</h5>
        <div class="card-body">
            <table id="claimedVouchersTable" class="display responsive nowrap">
                <thead>
                    <tr>
                        <th>ID Klaim</th>
                        <th>Voucher Code</th>
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

<?php // include_once __DIR__ . '/../../includes/footer.php'; // Letakkan footer Anda di sini ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.12.0/dist/sweetalert2.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/2.0.1/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.html5.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.print.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.colVis.min.js"></script>


<script>
$(document).ready(function() {
    // Inisialisasi DataTables
    const vouchersTable = $('#vouchersTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "get_vouchers.php", // Endpoint untuk mengambil data voucher
            "type": "POST"
        },
        "columns": [
            { "data": "id" },
            { "data": "voucher_code" },
            { "data": "amount", render: $.fn.dataTable.render.number('.', ',', 2, 'Rp ') },
            { "data": "is_active", render: function(data, type, row) {
                return data == 1 ? '<span style="color: green;">Aktif</span>' : '<span style="color: red;">Tidak Aktif</span>';
            }},
            { "data": "created_at" },
            { "data": "updated_at" },
            { "data": null, "defaultContent": '<button class="btn btn-warning btn-sm edit-btn me-2"><i class="fas fa-edit"></i> Edit</button><button class="btn btn-danger btn-sm delete-btn"><i class="fas fa-trash-alt"></i> Delete</button>' }
        ],
        "order": [[0, "desc"]] // Urutkan berdasarkan ID terbaru
    });

    const claimedVouchersTable = $('#claimedVouchersTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "get_claimed_vouchers.php", // Endpoint untuk mengambil data klaim voucher
            "type": "POST"
        },
        "columns": [
            { "data": "claim_id" },
            { "data": "voucher_code" },
            { "data": "username_anggota" },
            { "data": "amount", render: $.fn.dataTable.render.number('.', ',', 2, 'Rp ') },
            { "data": "claimed_at" }
        ],
        "order": [[0, "desc"]]
    });

    // Reset Form
    $('#resetForm').on('click', function() {
        $('#voucherForm')[0].reset();
        $('#voucherId').val(''); // Clear hidden ID for new entry
        $('#voucherForm button[type="submit"]').text('Simpan Voucher').removeClass('btn-success').addClass('btn-primary');
    });

    // Handle Form Submission (Add/Edit Voucher)
    $('#voucherForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize();
        const voucherId = $('#voucherId').val();
        const action = voucherId ? 'edit' : 'add'; // Determine action based on hidden ID

        Swal.fire({
            title: 'Konfirmasi',
            text: `Anda yakin ingin ${action === 'add' ? 'menambah' : 'mengubah'} voucher ini?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Lanjutkan!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'process_voucher_admin.php',
                    type: 'POST',
                    data: formData + '&action=' + action, // Pass action to PHP
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire('Berhasil!', response.message, 'success');
                            $('#voucherForm')[0].reset(); // Reset form
                            $('#voucherId').val(''); // Clear hidden ID
                            $('#voucherForm button[type="submit"]').text('Simpan Voucher').removeClass('btn-success').addClass('btn-primary'); // Reset button text
                            vouchersTable.ajax.reload(); // Reload table data
                        } else {
                            Swal.fire('Gagal!', response.message, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX error:", status, error);
                        Swal.fire('Error!', 'Terjadi kesalahan komunikasi dengan server.', 'error');
                    }
                });
            }
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
        $('#voucherForm button[type="submit"]').text('Update Voucher').removeClass('btn-primary').addClass('btn-success');
    });

    // Handle Delete Button Click
    $('#vouchersTable tbody').on('click', '.delete-btn', function() {
        const data = vouchersTable.row($(this).parents('tr')).data();
        const voucherId = data.id;

        Swal.fire({
            title: 'Konfirmasi Hapus',
            text: `Anda yakin ingin menghapus voucher "${data.voucher_code}" ini? Tindakan ini tidak dapat dibatalkan.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'process_voucher_admin.php',
                    type: 'POST',
                    data: { action: 'delete', id: voucherId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire('Dihapus!', response.message, 'success');
                            vouchersTable.ajax.reload(); // Reload table data
                            claimedVouchersTable.ajax.reload(); // Reload claimed vouchers too
                            $('#resetForm').click(); // Reset form just in case it was in edit mode
                        } else {
                            Swal.fire('Gagal!', response.message, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX error:", status, error);
                        Swal.fire('Error!', 'Terjadi kesalahan komunikasi dengan server.', 'error');
                    }
                });
            }
        });
    });
});
</script>

</body>
</html>