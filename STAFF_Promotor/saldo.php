<?php
include_once '../koneksi.php';
include_once __DIR__ . '/../classes/class.nexusggr.php';
include_once __DIR__ . '/../classes/class.exa.php';
include_once __DIR__ . '/../classes/connectAPI.php';

if (!isset($_SESSION['kode_staff'])) {
    echo '
      <script>
        alert("Terjadi kesalahan, harap masuk kembali!");
        window.location.replace("'.$alamat_staff.'keluar.php");
      </script>
    ';
    exit;
}
?>

<div class="container-xxl flex-grow-1 container-p-y">
  <div class="row gy-4 mb-4">
    <div class="col-md-6">
      <div class="fw-bold fs-4 text-center text-md-start">Anggota</div>
    </div>
    <div class="col-md-6 text-center text-md-end">
        <button id="calibrateAllBtn" class="btn btn-warning waves-effect waves-light me-2">
            <span class="tf-icons mdi mdi-sync me-1"></span> Update Semua Saldo
        </button>
    </div>
  </div>

  <div class="card table-responsive p-3">
    <table class="table" id="example">
      <thead>
        <tr>
          <th scope="col" class="text-center">#</th>
          <th scope="col" class="text-center">Nama Pengguna</th>
          <th scope="col" class="text-center">Bank</th>
          <th scope="col" class="text-center">Nama Rekening</th>
          <th scope="col" class="text-center">Nomor Rekening</th>
          <th scope="col" class="text-center">Saldo</th>
          <th scope="col" class="text-center">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $nomor_anggota = 1;
        $anggota_query = mysqli_query($koneksi, "SELECT * FROM anggota ORDER BY id_anggota DESC");

        if (mysqli_num_rows($anggota_query) > 0) {
            while ($data_anggota = mysqli_fetch_array($anggota_query)) {
                $id_anggota = $data_anggota['id_anggota'];
                $nama_pengguna_anggota = $data_anggota['nama_pengguna_anggota'];
                $bank_anggota = $data_anggota['bank_anggota'];
                $nama_rekening_anggota = $data_anggota['nama_rekening_anggota'];
                $nomor_rekening_anggota = $data_anggota['nomor_rekening_anggota'];
                $saldo_anggota = $data_anggota['saldo_anggota'];
                $id_sigma = $data_anggota['id_sigma'];
                $id_nexus = $data_anggota['id_nexus'];
        ?>
            <tr id="row-anggota-<?php echo htmlspecialchars($id_anggota); ?>">
              <th scope="row" class="text-center"><?php echo $nomor_anggota++; ?></th>
              <td class="text-center"><?php echo htmlspecialchars($nama_pengguna_anggota); ?></td>
              <td class="text-center"><?php echo htmlspecialchars($bank_anggota); ?></td>
              <td class="text-center"><?php echo htmlspecialchars($nama_rekening_anggota); ?></td>
              <td class="text-center"><?php echo htmlspecialchars($nomor_rekening_anggota); ?></td>
              <td class="text-center saldo-display" data-id-anggota="<?php echo htmlspecialchars($id_anggota); ?>"><?php echo 'Rp.' . number_format($saldo_anggota, 0, ',', '.'); ?></td>
              <td class="text-center">
                <a href="<?php echo htmlspecialchars($alamat_staff . 'ubah_saldo/' . $id_anggota); ?>" class="btn btn-sm btn-primary waves-effect waves-light me-1">
                  <span class="tf-icons mdi mdi-pencil me-1"></span>
                  Ubah
                </a>
                <button class="btn btn-sm btn-info waves-effect waves-light btn-calibrate-single me-1" data-id-anggota="<?php echo htmlspecialchars($id_anggota); ?>">
                  <span class="tf-icons mdi mdi-cash-sync me-1"></span>
                  Calibrate
                </button>
                <button class="btn btn-sm btn-success waves-effect waves-light btn-view-balance"
                        data-id-anggota="<?php echo htmlspecialchars($id_anggota); ?>"
                        data-id-sigma="<?php echo htmlspecialchars($id_sigma); ?>"
                        data-id-nexus="<?php echo htmlspecialchars($id_nexus); ?>">
                  <span class="tf-icons mdi mdi-eye me-1"></span>
                  Lihat Saldo API
                </button>
              </td>
            </tr>
        <?php
            }
        } else {
        ?>
            <tr>
              <td class="text-center" colspan="7">Tidak Ada Data</td>
            </tr>
        <?php
        }
        ?>
      </tbody>
    </table>
  </div>
  
  <div class="row gy-4 mt-4">
    <div class="col-md-12">
      <div class="fw-bold fs-4 text-center text-md-start">Riwayat Transaksi staff</div>
    </div>
  </div>
  <div class="card table-responsive p-3 mt-3">
    <table class="table" id="riwayat_staff">
      <thead>
        <tr>
          <th scope="col" class="text-center">#</th>
          <th scope="col" class="text-center">Nama Anggota</th>
          <th scope="col" class="text-center">Tipe Transaksi</th>
          <th scope="col" class="text-center">Kode Transaksi</th>
          <th scope="col" class="text-center">Jumlah</th>
          <th scope="col" class="text-center">Tanggal</th>
          <th scope="col" class="text-center">Status</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $nomor_transaksi = 1;
        $data_transaksi_gabungan = [];

        // Query untuk mendapatkan data deposit manual oleh staff
        $deposit_query = mysqli_query($koneksi, "SELECT 'Deposit' AS tipe, d.kode_deposit AS kode, d.jumlah_deposit AS jumlah, d.tanggal_deposit AS tanggal, d.status_deposit AS status, a.nama_pengguna_anggota FROM deposit d JOIN anggota a ON d.id_anggota_deposit = a.id_anggota WHERE d.kode_deposit LIKE 'ADM-%'");

        while ($data_deposit = mysqli_fetch_array($deposit_query)) {
            $data_transaksi_gabungan[] = $data_deposit;
        }

        // Query untuk mendapatkan data withdraw manual oleh staff
        $withdraw_query = mysqli_query($koneksi, "SELECT 'Withdraw' AS tipe, w.kode_withdraw AS kode, w.jumlah_withdraw AS jumlah, w.tanggal_withdraw AS tanggal, w.status_withdraw AS status, a.nama_pengguna_anggota FROM withdraw w JOIN anggota a ON w.id_anggota_withdraw = a.id_anggota WHERE w.kode_withdraw LIKE 'ADM-%'");

        while ($data_withdraw = mysqli_fetch_array($withdraw_query)) {
            $data_transaksi_gabungan[] = $data_withdraw;
        }

        // Mengurutkan data berdasarkan tanggal secara descending (terbaru)
        usort($data_transaksi_gabungan, function($a, $b) {
            return strtotime($b['tanggal']) - strtotime($a['tanggal']);
        });

        foreach ($data_transaksi_gabungan as $data) {
            $nama_anggota = $data['nama_pengguna_anggota'];
            $tipe = $data['tipe'];
            $kode = $data['kode'];
            $jumlah = $data['jumlah'];
            $tanggal = $data['tanggal'];
            $status = $data['status'];
        ?>
        <tr>
          <th scope="row" class="text-center"><?php echo $nomor_transaksi++; ?></th>
          <td class="text-center"><?php echo $nama_anggota; ?></td>
          <td class="text-center"><?php echo $tipe; ?></td>
          <td class="text-center"><?php echo $kode; ?></td>
          <td class="text-center"><?php echo 'Rp.' . number_format($jumlah, 0, ',', '.'); ?></td>
          <td class="text-center"><?php echo $tanggal; ?></td>
          <td class="text-center"><?php echo $status; ?></td>
        </tr>
        <?php
        }
        ?>
      </tbody>
    </table>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

<script>
$(document).ready(function() {
    // Inisialisasi DataTables untuk tabel anggota
    $('#example').DataTable({
        "paging": true,
        "ordering": true,
        "info": true
    });

    // Inisialisasi DataTables untuk tabel riwayat staff
    $('#riwayat_staff').DataTable({
        "paging": true,
        "ordering": true,
        "info": true,
        "order": [[5, "desc"]] // Urutkan berdasarkan kolom tanggal (indeks 5) secara descending
    });

    // Fungsi untuk menampilkan notifikasi SweetAlert
    function showNotification(title, text, icon) {
        swal(title, text, icon);
    }

    // --- Tombol Update Semua Saldo ---
    $('#calibrateAllBtn').on('click', function() {
        var $btn = $(this);
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...');

        showNotification('Memulai Kalibrasi', 'Memperbarui saldo semua anggota, ini mungkin memakan waktu...', 'info');

        $.ajax({
            url: '<?php echo $alamat_staff; ?>proses/process_calibrate_all.php',
            method: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    showNotification('Berhasil!', response.message, 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    showNotification('Gagal!', response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                showNotification('Error Jaringan', 'Terjadi kesalahan komunikasi dengan server: ' + error, 'error');
                console.error("AJAX Error: ", status, error, xhr.responseText);
            },
            complete: function() {
                $btn.prop('disabled', false).html('<span class="tf-icons mdi mdi-sync me-1"></span> Update Semua Saldo');
            }
        });
    });

    // --- Tombol Kalibrasi Saldo Individual dengan Event Delegation ---
    // Gunakan 'body' sebagai elemen induk yang statis
    $('body').on('click', '.btn-calibrate-single', function() {
        var id_anggota = $(this).data('id-anggota');
        var $btn = $(this);
        var originalButtonHtml = $btn.html();

        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');

        showNotification('Mengkalibrasi Saldo', 'Memperbarui saldo untuk anggota ini...', 'info');

        $.ajax({
            url: '<?php echo $alamat_staff; ?>proses/process_calibrate_single.php',
            method: 'POST',
            dataType: 'json',
            data: { id_anggota: id_anggota },
            success: function(response) {
                if (response.status === 'success') {
                    // Perbarui teks saldo di baris tabel yang relevan
                    $('td.saldo-display[data-id-anggota="' + response.id_anggota + '"]').text(response.formatted_new_balance);
                    showNotification('Berhasil!', response.message, 'success');
                } else {
                    showNotification('Gagal!', response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                showNotification('Error Jaringan', 'Terjadi kesalahan komunikasi dengan server: ' + error, 'error');
                console.error("AJAX Error: ", status, error, xhr.responseText);
            },
            complete: function() {
                $btn.prop('disabled', false).html(originalButtonHtml);
            }
        });
    });

    // --- Tombol Lihat Saldo API dengan Event Delegation ---
    $('body').on('click', '.btn-view-balance', function() {
        var id_sigma = $(this).data('id-sigma');
        var id_nexus = $(this).data('id-nexus');
        var $btn = $(this);
        var originalButtonHtml = $btn.html();
        
        if (!id_sigma && !id_nexus) {
            showNotification('Gagal!', 'ID Sigma atau ID Nexus tidak tersedia.', 'error');
            return;
        }

        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');

        showNotification('Memuat Saldo', 'Mengambil data saldo dari API...', 'info');

        // Panggilan untuk API Nexus
        var promiseNexus = $.ajax({
            url: '<?php echo $alamat_staff; ?>proses/get_balance.php',
            method: 'POST',
            dataType: 'json',
            data: { id_api: 'nexus', id_user: id_nexus }
        });

        // Panggilan untuk API Exa
        var promiseExa = $.ajax({
            url: '<?php echo $alamat_staff; ?>proses/get_balance.php',
            method: 'POST',
            dataType: 'json',
            data: { id_api: 'exa', id_user: id_sigma }
        });

        // Handle hasil dari kedua panggilan API
        $.when(promiseNexus, promiseExa)
            .done(function(responseNexus, responseExa) {
                var message = '';
                var nexusBalance = 'Tidak Ditemukan';
                var exaBalance = 'Tidak Ditemukan';

                // Proses respons dari Nexus
                if (responseNexus[0].status === 1) {
                    nexusBalance = 'Rp.' + (responseNexus[0].user.balance).toLocaleString('id-ID');
                } else {
                    message += 'Nexus: ' + (responseNexus[0].msg || 'Gagal mengambil saldo') + '<br>';
                }

                // Proses respons dari Exa
                if (responseExa[0].success) {
                    exaBalance = 'Rp.' + (responseExa[0].data.balance).toLocaleString('id-ID');
                } else {
                    message += 'Exa: ' + (responseExa[0].message || 'Gagal mengambil saldo') + '<br>';
                }

                message += '<br><b>Saldo Nexus: ' + nexusBalance + '</b><br>';
                message += '<b>Saldo Exa: ' + exaBalance + '</b>';

                swal({
                    title: 'Saldo Anggota',
                    content: {
                        element: "div",
                        attributes: {
                            innerHTML: message
                        },
                    },
                    icon: "success",
                });
            })
            .fail(function(xhr, status, error) {
                showNotification('Error', 'Terjadi kesalahan saat memuat saldo: ' + error, 'error');
                console.error("AJAX Error: ", status, error, xhr.responseText);
            })
            .always(function() {
                // Kembalikan tombol ke kondisi semula
                $btn.prop('disabled', false).html(originalButtonHtml);
            });
    });
});
</script>