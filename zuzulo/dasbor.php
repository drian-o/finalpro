<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include_once '../koneksi.php';
include_once '../classes/class.exa.php';
include_once '../classes/class.nexusggr.php';
include_once '../classes/connectAPI.php';

// Pastikan session admin ada, jika tidak, alihkan ke halaman keluar
if (!isset($_SESSION['kode_admin'])) {
    echo '
        <script>
            alert("Terjadi kesalahan, harap masuk kembali!");
            window.location.replace("' . $alamat_admin . 'keluar.php");
        </script>
    ';
    exit();
}

// ======================================================================
// AMBIL DATA DARI DATABASE
// ======================================================================

// Query untuk mendapatkan data jumlah deposit
$deposit = mysqli_query($koneksi, "SELECT SUM(jumlah_deposit) AS total_jumlah_deposit FROM deposit");
$data_deposit = mysqli_fetch_assoc($deposit);
$total_jumlah_deposit = $data_deposit['total_jumlah_deposit'] ?? 0;

// Query untuk mendapatkan data jumlah withdraw
$withdraw = mysqli_query($koneksi, "SELECT SUM(jumlah_withdraw) AS total_jumlah_withdraw FROM withdraw");
$data_withdraw = mysqli_fetch_assoc($withdraw);
$total_jumlah_withdraw = $data_withdraw['total_jumlah_withdraw'] ?? 0;

// Query untuk mendapatkan jumlah anggota
$anggota = mysqli_query($koneksi, "SELECT * FROM anggota");
$jumlah_anggota = mysqli_num_rows($anggota);

// Query untuk mendapatkan jumlah promosi
$promosi = mysqli_query($koneksi, "SELECT * FROM promosi");
$jumlah_promosi = mysqli_num_rows($promosi);

// --- Menghitung Net Revenue ---
$total_net_revenue = $total_jumlah_deposit - $total_jumlah_withdraw;
$formatted_net_revenue = number_format($total_net_revenue, 0, ',', '.');
$net_revenue_class = ($total_net_revenue >= 0) ? 'text-success' : 'text-danger';

// Simpan jumlah saat ini ke dalam session
$_SESSION['lastDepositCount'] = $total_jumlah_deposit;
$_SESSION['lastWithdrawCount'] = $total_jumlah_withdraw;
$_SESSION['lastAnggotaCount'] = $jumlah_anggota;
$_SESSION['lastPromosiCount'] = $jumlah_promosi;
$_SESSION['lastStaffCount'] = 0;

// ======================================================================
// AMBIL DATA DARI API EXA & NEXUS
// ======================================================================

// --- Ambil Saldo Agent dan Pemain dari EXA API ---
$formatted_balance_exa = 'Gagal memuat saldo.';
$formatted_player_balance_exa = 'Gagal memuat saldo.';
try {
    $exaAPI = new GameXaAPI();
    
    // Ambil saldo Agent
    $responseDataExa = $exaAPI->getCurrentAgentInfo();
    if (isset($responseDataExa['success']) && $responseDataExa['success'] === true && isset($responseDataExa['data']['agent']['balance'])) {
        $balance = $responseDataExa['data']['agent']['balance'];
        $formatted_balance_exa = number_format($balance, 0, ',', '.');
    } else {
        $apiMessage = $responseDataExa['message'] ?? 'Respon API Exa tidak valid.';
        $formatted_balance_exa = 'Permintaan API Exa gagal. (' . $apiMessage . ')';
    }
    
    // Ambil saldo Pemain
    $exaPlayers = $exaAPI->getPlayers();
    $total_player_balance_exa = 0;
    if ($exaPlayers['success'] && isset($exaPlayers['data']['players'])) {
        foreach ($exaPlayers['data']['players'] as $player) {
            $total_player_balance_exa += (float)$player['balance'];
        }
        $formatted_player_balance_exa = number_format($total_player_balance_exa, 0, ',', '.');
    } else {
        $formatted_player_balance_exa = 'Gagal memuat saldo pemain.';
    }
} catch (Exception $e) {
    $formatted_balance_exa = 'Error saat memanggil API Exa.';
    $formatted_player_balance_exa = 'Error saat memuat saldo pemain.';
}

// --- Ambil Saldo Agent dan Pemain dari NEXUS API ---
$formatted_balance_nexus = 'Gagal memuat saldo.';
$formatted_player_balance_nexus = 'Gagal memuat saldo.';
try {
    $nexusAPI = new API($user_agent, $signature);
    
    // Ambil saldo Agent
    $responseDataNexus = $nexusAPI->money_info();
    if (isset($responseDataNexus['status']) && $responseDataNexus['status'] == 1 && isset($responseDataNexus['agent']['balance'])) {
        $balance = $responseDataNexus['agent']['balance'];
        $formatted_balance_nexus = number_format($balance, 0, ',', '.');
    } else {
        $apiMessage = $responseDataNexus['msg'] ?? 'Respon API Nexus tidak valid.';
        $formatted_balance_nexus = 'Permintaan API Nexus gagal. (' . $apiMessage . ')';
    }
    
    // Ambil saldo Pemain
    $nexusPlayers = $nexusAPI->money_info_all();
    $total_player_balance_nexus = 0;
    if (isset($nexusPlayers['status']) && $nexusPlayers['status'] == 1 && isset($nexusPlayers['user_list'])) {
        foreach ($nexusPlayers['user_list'] as $user) {
            $total_player_balance_nexus += (float)$user['balance'];
        }
        $formatted_player_balance_nexus = number_format($total_player_balance_nexus, 0, ',', '.');
    } else {
        $formatted_player_balance_nexus = 'Gagal memuat saldo pemain.';
    }
} catch (Exception $e) {
    $formatted_balance_nexus = 'Error saat memanggil API Nexus.';
    $formatted_player_balance_nexus = 'Error saat memuat saldo pemain.';
}

// ======================================================================
// AMBIL DATA TRANSAKSI DIPROSES UNTUK TABEL & KARTU
// ======================================================================

// Query untuk total jumlah deposit yang diproses
$deposit_diproses_count = mysqli_query($koneksi, "SELECT COUNT(*) AS total_diproses FROM deposit WHERE status_deposit = 'diproses'");
$data_deposit_diproses_count = mysqli_fetch_assoc($deposit_diproses_count);
$total_deposit_diproses = $data_deposit_diproses_count['total_diproses'] ?? 0;

// Query untuk total jumlah withdraw yang diproses
$withdraw_diproses_count = mysqli_query($koneksi, "SELECT COUNT(*) AS total_diproses FROM withdraw WHERE status_withdraw = 'diproses'");
$data_withdraw_diproses_count = mysqli_fetch_assoc($withdraw_diproses_count);
$total_withdraw_diproses = $data_withdraw_diproses_count['total_diproses'] ?? 0;

// Query untuk menampilkan detail deposit yang diproses
$query_deposit_diproses_data = mysqli_query($koneksi, "SELECT * FROM deposit WHERE status_deposit = 'diproses' ORDER BY tanggal_deposit DESC");

// Query untuk mendapatkan data queued withdraw
$query_withdraw_diproses = mysqli_query($koneksi, "SELECT * FROM withdraw WHERE status_withdraw = 'diproses' ORDER BY tanggal_withdraw DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <style>
        .card-stat {
            transition: transform 0.2s;
            cursor: pointer;
        }
        .card-stat:hover {
            transform: translateY(-5px);
        }
        .status-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-weight: bold;
            display: inline-block;
        }
        .status-badge.pending {
            background-color: #fbd38d;
            color: #9c5c16;
        }
        .status-badge.in_progress {
            background-color: #f7e3af;
            color: #a08433;
        }
        .status-badge.resolved {
            background-color: #9ae6b4;
            color: #276749;
        }
        .status-badge.diproses {
            background-color: #ffc107;
            color: #000000;
        }
        .status-badge.dibatalkan {
            background-color: #dc3545;
            color: #ffffff;
        }
        .status-badge.disetujui {
            background-color: #198754;
            color: #ffffff;
        }
    </style>
</head>
<body>
    <audio id="notificationSound" preload="auto"></audio>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Kode JavaScript untuk notifikasi dan pembaruan otomatis telah dihapus.
        // Data akan diperbarui setiap kali halaman di-refresh secara manual.
        
        // Fungsi jam_sekarang tetap berjalan secara otomatis
        function updateClock() {
            var now = new Date();
            var hours = now.getHours();
            var minutes = now.getMinutes();
            var seconds = now.getSeconds();
            minutes = minutes < 10 ? '0' + minutes : minutes;
            seconds = seconds < 10 ? '0' + seconds : seconds;
            document.getElementById('jam_sekarang').textContent = 'Jam ' + hours + ':' + minutes + ':' + seconds;
        }
        setInterval(updateClock, 1000);
        updateClock(); // Panggil saat pertama kali dimuat
    </script>
    
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="fw-bold fs-4 text-center text-md-start">Dasbor</div>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <span class="d-block d-md-inline-block"><?php echo ucapan() . ', '; ?></span>
                <span class="d-block d-md-inline-block"><?php echo tanggalIndonesia(date('Y-m-d'), true) . ', '; ?></span>
                <span id="jam_sekarang" class="d-block d-md-inline-block"></span>
            </div>
        </div>
        
        <div class="row g-4 mb-4">
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="card card-stat h-100">
                    <div class="card-body pb-0">
                        <h5 class="mb-0">EXA Balance</h5>
                    </div>
                    <div class="card-body pt-0">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar avatar-lg flex-shrink-0 me-3">
                                <div class="avatar-initial bg-label-success rounded">
                                    <i class="mdi mdi-chart-bar mdi-36px"></i>
                                </div>
                            </div>
                            <div class="row g-0 flex-grow-1">
                                <div class="col-6">
                                    <span class="text-uppercase text-muted">Agent</span>
                                    <h5 class="mb-0 text-white"><?php echo 'Rp.' . $formatted_balance_exa; ?></h5>
                                </div>
                                <div class="col-6">
                                    <span class="text-uppercase text-muted">Player</span>
                                    <h5 class="mb-0 text-white"><?php echo 'Rp.' . $formatted_player_balance_exa; ?></h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="card card-stat h-100">
                    <div class="card-body pb-0">
                        <h5 class="mb-0">Nexus Balance</h5>
                    </div>
                    <div class="card-body pt-0">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar avatar-lg flex-shrink-0 me-3">
                                <div class="avatar-initial bg-label-info rounded">
                                    <i class="mdi mdi-hexagon-slice-5 mdi-36px"></i>
                                </div>
                            </div>
                            <div class="row g-0 flex-grow-1">
                                <div class="col-6">
                                    <span class="text-uppercase text-muted">Agent</span>
                                    <h5 class="mb-0 text-white"><?php echo 'Rp.' . $formatted_balance_nexus; ?></h5>
                                </div>
                                <div class="col-6">
                                    <span class="text-uppercase text-muted">Player</span>
                                    <h5 class="mb-0 text-white"><?php echo 'Rp.' . $formatted_player_balance_nexus; ?></h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row g-4 mb-4">
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="card card-stat h-100">
                    <div class="card-body pb-0">
                        <h5 class="mb-0">Deposit</h5>
                    </div>
                    <div class="card-body pt-0">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar avatar-lg flex-shrink-0 me-3">
                                <div class="avatar-initial bg-label-success rounded">
                                    <i class="mdi mdi-cash-plus mdi-36px"></i>
                                </div>
                            </div>
                            <div class="row g-0 flex-grow-1">
                                <div class="col-6">
                                    <span class="text-uppercase text-muted">Total</span>
                                    <h5 class="mb-0 text-white"><?php echo 'Rp.' . formatAngkaSingkat($total_jumlah_deposit); ?></h5>
                                </div>
                                <div class="col-6">
                                    <span class="text-uppercase text-muted">Queued</span>
                                    <h5 class="mb-0 text-white"><?php echo $total_deposit_diproses; ?></h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="card card-stat h-100">
                    <div class="card-body pb-0">
                        <h5 class="mb-0">Withdraw</h5>
                    </div>
                    <div class="card-body pt-0">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar avatar-lg flex-shrink-0 me-3">
                                <div class="avatar-initial bg-label-danger rounded">
                                    <i class="mdi mdi-cash-minus mdi-36px"></i>
                                </div>
                            </div>
                            <div class="row g-0 flex-grow-1">
                                <div class="col-6">
                                    <span class="text-uppercase text-muted">Total</span>
                                    <h5 class="mb-0 text-white"><?php echo 'Rp.' . formatAngkaSingkat($total_jumlah_withdraw); ?></h5>
                                </div>
                                <div class="col-6">
                                    <span class="text-uppercase text-muted">Queued</span>
                                    <h5 class="mb-0 text-white"><?php echo $total_withdraw_diproses; ?></h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="card card-stat h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar avatar-lg flex-shrink-0 me-3">
                                <div class="avatar-initial bg-label-warning rounded">
                                    <i class="mdi mdi-wallet-bifold mdi-36px"></i>
                                </div>
                            </div>
                            <div class="d-flex flex-column">
                                <span class="text-uppercase text-muted">Net Revenue</span>
                                <h4 class="mb-0 <?php echo $net_revenue_class; ?>"><?php echo 'Rp.' . $formatted_net_revenue; ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row g-4 mb-4">
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="card card-stat h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar avatar-lg flex-shrink-0 me-3">
                                <div class="avatar-initial bg-label-info rounded">
                                    <i class="mdi mdi-account-multiple mdi-36px"></i>
                                </div>
                            </div>
                            <div class="d-flex flex-column">
                                <span class="text-uppercase text-muted">Total Anggota</span>
                                <h4 class="mb-0 text-white"><?php echo formatAngkaSingkat($jumlah_anggota); ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="card card-stat h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar avatar-lg flex-shrink-0 me-3">
                                <div class="avatar-initial bg-label-secondary rounded">
                                    <i class="mdi mdi-star-box mdi-36px"></i>
                                </div>
                            </div>
                            <div class="d-flex flex-column">
                                <span class="text-uppercase text-muted">Total Promosi</span>
                                <h4 class="mb-0 text-white"><?php echo formatAngkaSingkat($jumlah_promosi); ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Antrean Transaksi</h5>
                        <small class="text-muted">Daftar deposit dan withdraw yang perlu diproses</small>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive text-nowrap">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th class="text-center">No.</th>
                                        <th class="text-center">Tipe</th>
                                        <th class="text-center">Kode</th>
                                        <th class="text-center">Username</th>
                                        <th class="text-center">Jumlah</th>
                                        <th class="text-center">Tanggal</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $combined_transactions = [];
                                    
                                    // Fetch all queued deposits
                                    while ($row = mysqli_fetch_assoc($query_deposit_diproses_data)) {
                                        $row['type'] = 'Deposit';
                                        $combined_transactions[] = $row;
                                    }
                                    
                                    // Fetch all queued withdrawals
                                    while ($row = mysqli_fetch_assoc($query_withdraw_diproses)) {
                                        $row['type'] = 'Withdraw';
                                        $combined_transactions[] = $row;
                                    }
                                    
                                    // Sort by date (newest first)
                                    usort($combined_transactions, function($a, $b) {
                                        $date_a = new DateTime($a['tanggal_deposit'] ?? $a['tanggal_withdraw']);
                                        $date_b = new DateTime($b['tanggal_deposit'] ?? $b['tanggal_withdraw']);
                                        return $date_b <=> $date_a;
                                    });
                                    
                                    $nomor = 1;
                                    if (empty($combined_transactions)) {
                                        echo '<tr><td colspan="8" class="text-center text-muted py-4">Tidak ada transaksi yang diantrekan saat ini.</td></tr>';
                                    } else {
                                        foreach ($combined_transactions as $data_transaction) {
                                            $type = $data_transaction['type'];
                                            if ($type === 'Deposit') {
                                                $kode = $data_transaction['kode_deposit'];
                                                $id_anggota = $data_transaction['id_anggota_deposit'];
                                                $jumlah = $data_transaction['jumlah_deposit'];
                                                $tanggal = $data_transaction['tanggal_deposit'];
                                                $status = $data_transaction['status_deposit'];
                                                $link_aksi = $alamat_admin . 'deposit';
                                            } else { // Withdraw
                                                $kode = $data_transaction['kode_withdraw'];
                                                $id_anggota = $data_transaction['id_anggota_withdraw'];
                                                $jumlah = $data_transaction['jumlah_withdraw'];
                                                $tanggal = $data_transaction['tanggal_withdraw'];
                                                $status = $data_transaction['status_withdraw'];
                                                $link_aksi = $alamat_admin . 'withdraw';
                                            }
                                            
                                            // Ambil nama pengguna
                                            $anggota_query = mysqli_query($koneksi, "SELECT nama_pengguna_anggota FROM anggota WHERE id_anggota = '$id_anggota'");
                                            $nama_pengguna = mysqli_num_rows($anggota_query) > 0 ? mysqli_fetch_assoc($anggota_query)['nama_pengguna_anggota'] : 'Anggota Dihapus';
                                            
                                            // Status badge
                                            $badge_class = '';
                                            $display_status = '';
                                            switch ($status) {
                                                case 'diproses':
                                                    $badge_class = 'status-badge diproses';
                                                    $display_status = 'Diproses';
                                                    break;
                                                case 'dibatalkan':
                                                    $badge_class = 'status-badge dibatalkan';
                                                    $display_status = 'Dibatalkan';
                                                    break;
                                                case 'disetujui':
                                                    $badge_class = 'status-badge disetujui';
                                                    $display_status = 'Disetujui';
                                                    break;
                                                default:
                                                    $badge_class = 'status-badge';
                                                    $display_status = ucfirst($status);
                                                    break;
                                            }
                                            ?>
                                            <tr>
                                                <td class="text-center"><?php echo $nomor++; ?></td>
                                                <td class="text-center"><?php echo $type; ?></td>
                                                <td class="text-center"><?php echo $kode; ?></td>
                                                <td class="text-center"><?php echo $nama_pengguna; ?></td>
                                                <td class="text-center">Rp.<?php echo number_format($jumlah, 0, ',', '.'); ?></td>
                                                <td class="text-center"><?php echo jamTanggalIndonesia($tanggal); ?></td>
                                                <td class="text-center">
                                                    <span class="<?php echo $badge_class; ?>">
                                                        <?php echo $display_status; ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <a href="<?php echo $link_aksi; ?>" class="btn btn-sm btn-primary waves-effect waves-light" aria-label="Ubah">
                                                        <span class="tf-icons mdi mdi-cog me-1"></span>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>