<?php
// proses_tambah_semua_bonus_mingguan.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/../koneksi.php'; 

$logFileDir = __DIR__ . '/../logs/'; 
$logFilePath = $logFileDir . 'proses_tambah_semua_bonus_mingguan.log';

if (!is_dir($logFileDir)) {
    @mkdir($logFileDir, 0775, true);
}

function log_tambah_semua_bonus_mingguan($message) {
    global $logFilePath;
    $timestamp = date("Y-m-d H:i:s");
    file_put_contents($logFilePath, "[{$timestamp}] " . $message . PHP_EOL, FILE_APPEND | LOCK_EX);
}

$redirect_page = isset($alamat_admin) ? rtrim($alamat_admin, '/') . '/claim_bonus' : 'claim_bonus';

if (!isset($_SESSION['kode_admin'])) {
    $_SESSION['pesan_tambah_bonus_semua'] = ['teks' => 'Akses ditolak. Anda bukan admin atau sesi telah berakhir.', 'tipe' => 'danger'];
    log_tambah_semua_bonus_mingguan("Akses ditolak: Sesi admin tidak ditemukan. IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'N/A'));
    header('Location: ' . $redirect_page);
    exit();
}
$admin_kode = $_SESSION['kode_admin'];

$persentase_bonus = 0.03;
$stmt_get_bonus_rate = $koneksi->prepare("SELECT isi_2_pengaturan FROM pengaturan WHERE nama_pengaturan = 'bonus_mingguan'");
if ($stmt_get_bonus_rate) {
    $stmt_get_bonus_rate->execute();
    $result_bonus_rate = $stmt_get_bonus_rate->get_result();
    if ($data_bonus_rate = $result_bonus_rate->fetch_assoc()) {
        $persentase_bonus = floatval($data_bonus_rate['isi_2_pengaturan']);
    }
    $stmt_get_bonus_rate->close();
}
$persentase_bonus_display = round($persentase_bonus * 100);

$target_action = isset($_GET['target']) ? $_GET['target'] : null;
$confirmation = isset($_GET['confirm']) ? $_GET['confirm'] : null;

if ($target_action !== 'alluser' || $confirmation !== 'true') {
    $_SESSION['pesan_tambah_bonus_semua'] = ['teks' => 'Aksi tidak valid atau konfirmasi salah untuk proses semua bonus.', 'tipe' => 'danger'];
    log_tambah_semua_bonus_mingguan("Aksi tidak valid atau konfirmasi salah. Target: {$target_action}, Confirm: {$confirmation}");
    header('Location: ' . $redirect_page);
    exit();
}

log_tambah_semua_bonus_mingguan("Admin {$admin_kode} memulai proses 'Tambah Semua Bonus Mingguan' via Pretty URL dengan bonus {$persentase_bonus_display}%.");

$berhasil_ditambahkan_count = 0;
$sudah_ditambahkan_sebelumnya_count = 0;
$tidak_ada_bonus_count = 0;
$error_count = 0;
$total_anggota_diproses_loop = 0;
$jumlah_minggu_dicek_untuk_proses = 4;

if (!function_exists('getWeekDates')) {
    function getWeekDates($weeks_ago = 0) {
        $today = new DateTime("now", new DateTimeZone('Asia/Jakarta'));
        if ($weeks_ago > 0) { $today->modify("-{$weeks_ago} weeks"); }
        $day_of_week = $today->format('N'); 
        $start_date = clone $today;
        $start_date->modify('-' . ($day_of_week - 1) . ' days')->setTime(0, 0, 0);
        $end_date = clone $start_date;
        $end_date->modify('+6 days')->setTime(23, 59, 59);
        return ['start_date' => $start_date->format('Y-m-d H:i:s'), 'end_date' => $end_date->format('Y-m-d H:i:s'), 'week_number' => (int)$start_date->format('W'), 'year' => (int)$start_date->format('Y')];
    }
}

$sql_all_anggota = "SELECT id_anggota, nama_pengguna_anggota FROM anggota";
$query_all_anggota = mysqli_query($koneksi, $sql_all_anggota);

if (!$query_all_anggota) {
    $_SESSION['pesan_tambah_bonus_semua'] = ['teks' => 'Gagal mengambil daftar anggota dari database.', 'tipe' => 'danger'];
    log_tambah_semua_bonus_mingguan("CRITICAL: Gagal mengambil daftar anggota. Error: " . mysqli_error($koneksi));
    if (isset($koneksi) && $koneksi instanceof mysqli && $koneksi->thread_id) { $koneksi->close(); }
    header('Location: ' . $redirect_page);
    exit();
}

while ($anggota = mysqli_fetch_assoc($query_all_anggota)) {
    $id_anggota_current = $anggota['id_anggota'];
    $nama_pengguna_current = $anggota['nama_pengguna_anggota'];
    $total_anggota_diproses_loop++;

    for ($w = 0; $w < $jumlah_minggu_dicek_untuk_proses; $w++) {
        $minggu_data = getWeekDates($w);
        $start_date_minggu_current = $minggu_data['start_date'];
        $end_date_minggu_current = $minggu_data['end_date'];
        $minggu_ke_current = $minggu_data['week_number'];
        $tahun_current = $minggu_data['year'];

        $total_deposit_mingguan_current = 0;
        $sql_deposit_c = "SELECT SUM(CAST(REPLACE(jumlah_deposit, ',', '') AS DECIMAL(15,2))) AS total_depo FROM deposit WHERE id_anggota_deposit = ? AND status_deposit = 'disetujui' AND tanggal_deposit BETWEEN ? AND ?";
        $stmt_deposit_c = $koneksi->prepare($sql_deposit_c);
        if ($stmt_deposit_c) {
            $stmt_deposit_c->bind_param("iss", $id_anggota_current, $start_date_minggu_current, $end_date_minggu_current);
            $stmt_deposit_c->execute();
            $result_depo_c = $stmt_deposit_c->get_result();
            if ($data_depo_c = $result_depo_c->fetch_assoc()) {
                $total_deposit_mingguan_current = (float)($data_depo_c['total_depo'] ?? 0);
            }
            $stmt_deposit_c->close();
        } else {
            log_tambah_semua_bonus_mingguan("Error prepare hitung deposit untuk {$nama_pengguna_current}, Minggu {$minggu_ke_current}-{$tahun_current}: " . $koneksi->error);
            $error_count++; continue; 
        }

        $potensi_bonus_mingguan_current = round($total_deposit_mingguan_current * $persentase_bonus, 2);

        if ($potensi_bonus_mingguan_current <= 0.009) {
            $tidak_ada_bonus_count++;
            continue; 
        }

        $koneksi->begin_transaction();
        try {
            $stmt_check_c = $koneksi->prepare("SELECT id_claim FROM claim_bonus WHERE id_anggota_claim = ? AND periode_tahun = ? AND periode_minggu_ke = ?");
            if (!$stmt_check_c) throw new Exception("DB Error (prepare cek duplikat claim) untuk {$nama_pengguna_current}: " . $koneksi->error);
            $stmt_check_c->bind_param("iii", $id_anggota_current, $tahun_current, $minggu_ke_current);
            $stmt_check_c->execute();
            $stmt_check_c->store_result();
            $sudah_diklaim_periode_ini = ($stmt_check_c->num_rows > 0);
            $stmt_check_c->close();

            if ($sudah_diklaim_periode_ini) {
                log_tambah_semua_bonus_mingguan("INFO: Bonus untuk {$nama_pengguna_current} (Minggu {$minggu_ke_current}-{$tahun_current}) sudah ditambahkan.");
                $sudah_ditambahkan_sebelumnya_count++;
                $koneksi->commit();
                continue; 
            }

            $sql_update_bb_c = "UPDATE anggota SET bonus_balance = bonus_balance + ? WHERE id_anggota = ?";
            $stmt_update_bb_c = $koneksi->prepare($sql_update_bb_c);
            if (!$stmt_update_bb_c) throw new Exception("DB Error (prepare update bonus_balance) untuk {$nama_pengguna_current}: " . $koneksi->error);
            $stmt_update_bb_c->bind_param("di", $potensi_bonus_mingguan_current, $id_anggota_current);
            if (!$stmt_update_bb_c->execute()) throw new Exception("DB Error (execute update bonus_balance) untuk {$nama_pengguna_current}: " . $stmt_update_bb_c->error);
            $stmt_update_bb_c->close();

            $keterangan_claim_c = "Bonus mingguan {$persentase_bonus_display}% (Minggu {$minggu_ke_current}-{$tahun_current} dari total depo Rp ".number_format($total_deposit_mingguan_current,0,',','.').") ditambahkan oleh sistem 'Tambah Semua'. Admin: {$admin_kode}";
            $tanggal_proses_c = date('Y-m-d H:i:s');
            
            $sql_log_claim_c = "INSERT INTO claim_bonus 
                                  (id_anggota_claim, nama_pengguna_claim, jumlah_bonus_diklaim, 
                                   tanggal_claim, keterangan, periode_tahun, periode_minggu_ke) 
                               VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt_log_c = $koneksi->prepare($sql_log_claim_c);
            if (!$stmt_log_c) throw new Exception("DB Error (prepare insert log claim) untuk {$nama_pengguna_current}: " . $koneksi->error);
            
            $stmt_log_c->bind_param("isdssii", 
                $id_anggota_current, 
                $nama_pengguna_current, 
                $potensi_bonus_mingguan_current, 
                $tanggal_proses_c, 
                $keterangan_claim_c, 
                $tahun_current, 
                $minggu_ke_current
            );
            if (!$stmt_log_c->execute()) throw new Exception("DB Error (execute insert log claim) untuk {$nama_pengguna_current}: " . $stmt_log_c->error);
            $stmt_log_c->close();

            $koneksi->commit();
            log_tambah_semua_bonus_mingguan("SUKSES: Bonus mingguan {$potensi_bonus_mingguan_current} untuk {$nama_pengguna_current} (Minggu {$minggu_ke_current}-{$tahun_current}) ditambahkan.");
            $berhasil_ditambahkan_count++;

        } catch (Exception $e_user_week) {
            if (isset($koneksi) && $koneksi->ping()) { $koneksi->rollback(); }
            log_tambah_semua_bonus_mingguan("GAGAL proses tambah bonus untuk {$nama_pengguna_current} (Minggu {$minggu_ke_current}-{$tahun_current}): " . $e_user_week->getMessage());
            $error_count++;
        }
    } 
}
mysqli_free_result($query_all_anggota);

$pesan_akhir = "Proses 'Tambah Semua Bonus Mingguan' selesai. ";
$pesan_akhir .= "Total anggota dicek: {$total_anggota_diproses_loop}. ";
$pesan_akhir .= "Penambahan bonus berhasil: {$berhasil_ditambahkan_count} entri. ";
if ($sudah_ditambahkan_sebelumnya_count > 0) $pesan_akhir .= "Sudah pernah ditambahkan: {$sudah_ditambahkan_sebelumnya_count} entri. ";
if ($tidak_ada_bonus_count > 0) $pesan_akhir .= "Tidak ada potensi bonus: {$tidak_ada_bonus_count} kasus. ";
if ($error_count > 0) $pesan_akhir .= "Gagal/Error: {$error_count} entri. ";

$tipe_pesan = ($error_count > 0 || $berhasil_ditambahkan_count == 0 ? 'warning' : 'success');
if ($error_count > 0) {
    $tipe_pesan = 'danger';
}

$_SESSION['pesan_tambah_bonus_semua'] = ['teks' => $pesan_akhir, 'tipe' => $tipe_pesan];
log_tambah_semua_bonus_mingguan($pesan_akhir);

if (isset($koneksi) && $koneksi instanceof mysqli && $koneksi->thread_id) {
    $koneksi->close();
}
header('Location: ' . $redirect_page);
exit();
?>