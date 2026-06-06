<?php
// zuzulo/tambah_domain.php
require_once __DIR__ . '/../koneksi.php'; 

$pesan = "";

// =========================================================================
// FUNGSI SAKTI: OTOMATIS DAFTARKAN SEMUA DOMAIN & LANGSUNG APPLY DEPLOYMENT
// =========================================================================
function sinkronisasiDomainKeCoolifyLokal() {
    global $koneksi;

    $api_key = "3|HIDG5O5obDUSuAWiuoDPFSpABtbF4yhALvo3C9Nb14c5fa2b";
    $application_uuid = "sfpho7xg4jjpep1xpnaf8y8o";
    
    $domain_utama = "https://exampleproject.my.id";
    $list_domain = [$domain_utama];

    // Ambil semua domain user dari database
    $query_domains = mysqli_query($koneksi, "SELECT domain_name FROM custom_domains");
    while ($row = mysqli_fetch_array($query_domains)) {
        if (!empty($row['domain_name'])) {
            $list_domain[] = "https://" . trim($row['domain_name']);
        }
    }

    $string_domains = implode(",", $list_domain);

    // -------------------------------------------------------------------------
    // LANGKAH 1: UPDATE KOLOM DOMAIN (PATCH)
    // -------------------------------------------------------------------------
    $url = "http://137.184.155.151:8000/api/v1/applications/" . $application_uuid;
    $data_payload = json_encode(array("domains" => $string_domains));

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_payload);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $api_key
    ]);

    $response = curl_exec($ch);
    $err = curl_error($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch); 

    if ($err || ($http_code !== 200 && $http_code !== 201)) {
        return; // Mengurangi interupsi alert jika flow sudah lancar
    }

    // -------------------------------------------------------------------------
    // LANGKAH 2: RESTART PROXY (POST) - AKTIF DALAM 2 DETIK
    // -------------------------------------------------------------------------
    $restart_url = "http://137.184.155.151:8000/api/v1/applications/" . $application_uuid . "/restart"; 

    $ch_deploy = curl_init($restart_url);
    curl_setopt($ch_deploy, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch_deploy, CURLOPT_CUSTOMREQUEST, "POST"); 
    curl_setopt($ch_deploy, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch_deploy, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch_deploy, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch_deploy, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $api_key
    ]);

    curl_exec($ch_deploy);
    curl_close($ch_deploy);
}

// ========================================================
// FUNGSI SAKTI 1: TAMBAH ZONE BARU KE CLOUDFLARE
// ========================================================
function tambahSiteBaruCloudflareLokal($domainBaru) {
    $cf_email = 'adrnsyah' . '18' . '@' . 'gmail.com';
    $cf_key   = 'cfk_' . 'I4b6ZygMhnUoCSYEnPVfupCDOyAHan7ZIs9YbzGpa5e33a56'; 

    $data = [
        "name" => $domainBaru,
        "jump_start" => true 
    ];

    $ch = curl_init("https://api.cloudflare.com/client/v4/zones");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'X-Auth-Email: ' . $cf_email,
        'X-Auth-Key: ' . $cf_key,
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    return $err ? ['success' => false, 'error' => $err] : json_decode($response, true);
}

// ========================================================
// FUNGSI SAKTI 2: HAPUS ZONE DARI CLOUDFLARE
// ========================================================
function deleteSiteDariCloudflareLokal($zone_id) {
    $cf_email = 'adrnsyah' . '18' . '@' . 'gmail.com';
    $cf_key   = 'cfk_' . 'I4b6ZygMhnUoCSYEnPVfupCDOyAHan7ZIs9YbzGpa5e33a56'; 

    $ch = curl_init("https://api.cloudflare.com/client/v4/zones/" . $zone_id);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'X-Auth-Email: ' . $cf_email,
        'X-Auth-Key: ' . $cf_key,
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    return $err ? ['success' => false, 'error' => $err] : json_decode($response, true);
}

// ========================================================
// 🔥 REVISI TAMBAHAN: FUNGSI CUKUP STATUS ZONE CLOUDFLARE
// ========================================================
function cekStatusZoneCloudflareLokal($zone_id) {
    $cf_email = 'adrnsyah' . '18' . '@' . 'gmail.com';
    $cf_key   = 'cfk_' . 'I4b6ZygMhnUoCSYEnPVfupCDOyAHan7ZIs9YbzGpa5e33a56'; 

    $ch = curl_init("https://api.cloudflare.com/client/v4/zones/" . $zone_id);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'X-Auth-Email: ' . $cf_email,
        'X-Auth-Key: ' . $cf_key,
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) return 'pending';
    $res_data = json_decode($response, true);
    return $res_data['result']['status'] ?? 'pending'; 
}

// LOGIKA PROSES TOMBOL: HAPUS SITE DOMAIN
if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus' && isset($_GET['id']) && isset($_GET['cf_id'])) {
    $id_hapus = mysqli_real_escape_string($koneksi, $_GET['id']);
    $zone_id_hapus = mysqli_real_escape_string($koneksi, $_GET['cf_id']);
    
    deleteSiteDariCloudflareLokal($zone_id_hapus);
    mysqli_query($koneksi, "DELETE FROM custom_domains WHERE id = '$id_hapus'");
    sinkronisasiDomainKeCoolifyLokal();
    $pesan = "<div class='alert alert-success alert-dismissible fade show' role='alert'><strong>Sukses!</strong> Domain berhasil dihapus dari sistem.<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>";
}

// LOGIKA PROSES TOMBOL: TAMBAH SITE DOMAIN (POST FORM)
if (isset($_POST['submit_domain'])) {
    $domain_input = strtolower(trim($_POST['nama_domain']));
    $domain_clean = mysqli_real_escape_string($koneksi, $domain_input);

    if (preg_match('/^[a-z0-9.-]+\.[a-z]{2,}$/', $domain_clean)) {
        $hasil = tambahSiteBaruCloudflareLokal($domain_clean);

        if (isset($hasil['success']) && $hasil['success'] == true) {
            $zone_id = $hasil['result']['id']; 
            $ns1 = $hasil['result']['name_servers'][0] ?? 'ns1.cloudflare.com';
            $ns2 = $hasil['result']['name_servers'][1] ?? 'ns2.cloudflare.com';

            $cf_email = 'adrnsyah' . '18' . '@' . 'gmail.com';
            $cf_key   = 'cfk_' . 'I4b6ZygMhnUoCSYEnPVfupCDOyAHan7ZIs9YbzGpa5e33a56'; 
            $ip_server_kamu = '137.184.155.151'; 

            // 1. Buat A record di Cloudflare dengan Proxy ON
            $dns_data = [
                "type" => "A",
                "name" => "@",
                "content" => $ip_server_kamu,
                "ttl" => 1, 
                "proxied" => true 
            ];
            $ch_dns = curl_init("https://api.cloudflare.com/client/v4/zones/" . $zone_id . "/dns_records");
            curl_setopt($ch_dns, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch_dns, CURLOPT_POST, true);
            curl_setopt($ch_dns, CURLOPT_POSTFIELDS, json_encode($dns_data));
            curl_setopt($ch_dns, CURLOPT_HTTPHEADER, [
                'X-Auth-Email: ' . $cf_email,
                'X-Auth-Key: ' . $cf_key,
                'Content-Type: application/json'
            ]);
            curl_exec($ch_dns);
            curl_close($ch_dns);

            // 2. Set SSL Cloudflare ke "full"
            $ssl_payload = [
                "id" => "ssl",
                "value" => "full" 
            ];
            $ch_ssl = curl_init("https://api.cloudflare.com/client/v4/zones/" . $zone_id . "/settings/ssl");
            curl_setopt($ch_ssl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch_ssl, CURLOPT_CUSTOMREQUEST, "PATCH");
            curl_setopt($ch_ssl, CURLOPT_POSTFIELDS, json_encode($ssl_payload));
            curl_setopt($ch_ssl, CURLOPT_HTTPHEADER, [
                'X-Auth-Email: ' . $cf_email,
                'X-Auth-Key: ' . $cf_key,
                'Content-Type: application/json'
            ]);
            curl_exec($ch_ssl);
            curl_close($ch_ssl);

            // 3. Simpan data domain ke database MySQL
            $query_simpan = "INSERT INTO custom_domains (domain_name, cloudflare_id, status) VALUES ('$domain_clean', '$zone_id', 'pending')";
            
            if (mysqli_query($koneksi, $query_simpan)) {
                sinkronisasiDomainKeCoolifyLokal();

                $pesan = "
                <div class='alert alert-success alert-dismissible fade show' role='alert'>
                    <strong>🎉 Sukses! Domain Berhasil Didaftarkan ke Sistem</strong><br>
                    <small>ID Zone: <code>$zone_id</code></small>
                    <hr class='my-2'>
                    <strong>🛠️ INSTRUKSI NAMESERVER (NS):</strong><br>
                    <code>1. $ns1</code><br><code>2. $ns2</code>
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                </div>";
            } else {
                $pesan = "<div class='alert alert-danger'><strong>Database Error:</strong> " . mysqli_error($koneksi) . "</div>";
            }
        } else {
            $error_msg = $hasil['errors'][0]['message'] ?? 'Gagal menambahkan domain ke Cloudflare.';
            $pesan = "<div class='alert alert-danger'><strong>Cloudflare Error:</strong> $error_msg</div>";
        }
    } else {
        $pesan = "<div class='alert alert-warning'><strong>Input Salah:</strong> Format nama domain tidak valid!</div>";
    }
}

// Include layouting header sneat bawaan admin panel jika ada
if(file_exists('header.php')) {
    include_once 'header.php';
}
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css">
<style>
    .card-dark-theme {
        background-color: #2b2c40 !important;
        color: #cbcbd6 !important;
        border-radius: 0.5rem;
    }
    .table-dark-theme th {
        color: #a3a4cc !important;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 1px;
    }
    .table-dark-theme td {
        color: #cbcbd6 !important;
        vertical-align: middle;
    }
    .badge-active-custom {
        background-color: rgba(46, 204, 113, 0.15) !important;
        color: #2ecc71 !important;
        padding: 0.5em 0.8em;
        font-weight: 600;
        border-radius: 0.25rem;
    }
    .badge-pending-custom {
        background-color: rgba(241, 196, 15, 0.15) !important;
        color: #f1c40f !important;
        padding: 0.5em 0.8em;
        font-weight: 600;
        border-radius: 0.25rem;
    }
    .btn-primary-custom {
        background-color: #696cff !important;
        border-color: #696cff !important;
        color: #fff !important;
    }
    .btn-primary-custom:hover {
        background-color: #5f61e6 !important;
        border-color: #5f61e6 !important;
    }
    .btn-danger-custom {
        background-color: #ff3e1d !important;
        border-color: #ff3e1d !important;
        color: #fff !important;
    }
</style>

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold py-3 mb-0" style="color: #cbcbd6;">SaaS Domain Manager</h4>
            <button type="button" class="btn btn-primary-custom d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#modalTambahDomain">
                <i class="bi bi-plus-lg me-2"></i> TAMBAH DATA
            </button>
        </div>

        <?php if(!empty($pesan)) echo $pesan; ?>

        <div class="card card-dark-theme border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive text-nowrap">
                    <table class="table table-dark-theme mb-0">
                        <thead style="background-color: rgba(255,255,255,0.03);">
                            <tr>
                                <th style="width: 80px;" class="text-center">#</th>
                                <th>Nama Domain</th>
                                <th>Kategori / Status</th>
                                <th style="width: 150px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            <?php
                            $query_tampil = mysqli_query($koneksi, "SELECT * FROM custom_domains ORDER BY id DESC");
                            $no = 1;
                            
                            if (mysqli_num_rows($query_tampil) > 0) {
                                while ($row = mysqli_fetch_assoc($query_tampil)) {
                                    
                                    // Ambil status aktual dari Cloudflare API secara live
                                    $status_sekarang = cekStatusZoneCloudflareLokal($row['cloudflare_id']);
                                    
                                    // Jika status di database berbeda dengan Cloudflare, auto-update databasenya
                                    if ($status_sekarang !== $row['status']) {
                                        $id_update = $row['id'];
                                        mysqli_query($koneksi, "UPDATE custom_domains SET status = '$status_sekarang' WHERE id = '$id_update'");
                                    }

                                    $badge_class = ($status_sekarang === 'active') ? 'badge-active-custom' : 'badge-pending-custom';
                                    ?>
                                    <tr>
                                        <td class="text-center fw-semibold"><?= $no++; ?></td>
                                        <td>
                                            <span class="fw-bold" style="color: #fff; font-size: 0.95rem;">
                                                <?= htmlspecialchars($row['domain_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="<?= $badge_class; ?>">
                                                <?= strtoupper(htmlspecialchars($status_sekarang, ENT_QUOTES, 'UTF-8')); ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <a href="?aksi=hapus&id=<?= $row['id']; ?>&cf_id=<?= $row['cloudflare_id']; ?>" 
                                               class="btn btn-sm btn-danger-custom" 
                                               onclick="return confirm('Apakah Anda yakin ingin menghapus domain ini?')">
                                                <i class="bi bi-trash3 me-1"></i> HAPUS
                                            </a>
                                        </td>
                                    </tr>
                                    <?php 
                                } 
                            } else {
                                echo "<tr><td colspan='4' class='text-center py-4 opacity-50'>Tidak ada custom domain terdaftar.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer border-0 py-3" style="background-color: rgba(0,0,0,0.1); border-bottom-left-radius: 0.5rem; border-bottom-right-radius: 0.5rem;">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">Showing 1 to <?= ($no - 1); ?> entries</small>
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item disabled"><a class="page-link" href="javascript:void(0);"><i class="bi bi-chevron-double-left"></i></a></li>
                            <li class="page-item active"><a class="page-link bg-primary border-primary" href="javascript:void(0);">1</a></li>
                            <li class="page-item disabled"><a class="page-link" href="javascript:void(0);"><i class="bi bi-chevron-double-right"></i></a></li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>

    </div>
</div>

<div class="modal fade" id="modalTambahDomain" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content card-dark-theme border-0 shadow-lg">
            <div class="modal-header border-bottom border-secondary pb-3">
                <h5 class="modal-title fw-bold text-white" id="modalTambahDomainTitle">Daftarkan Domain Baru User</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body py-4">
                    <div class="row">
                        <div class="col mb-3">
                            <label for="nama_domain" class="form-label mb-2 fw-semibold" style="color: #a3a4cc;">Nama Domain Alamat Web</label>
                            <input type="text" 
                                   id="nama_domain" 
                                   name="nama_domain" 
                                   class="form-control text-white border-secondary bg-transparent py-2.5" 
                                   style="border-radius: 0.375rem;"
                                   placeholder="contoh: domainsampel.com" 
                                   required 
                                   autocomplete="off">
                            <div class="form-text text-muted mt-2">
                                <i class="bi bi-info-circle me-1"></i> Pastikan domain berstatus valid tanpa menyertakan skema <code>http://</code> atau <code>https://</code>.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary pt-3">
                    <button type="button" class="btn btn-outline-secondary text-white" data-bs-dismiss="modal">KEMBALI</button>
                    <button type="submit" name="submit_domain" class="btn btn-primary-custom px-4">DAFTARKAN & GENERATE NS</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php 
// Include layouting footer bawaan admin panel jika ada
if(file_exists('footer.php')) {
    include_once 'footer.php';
}
?>
