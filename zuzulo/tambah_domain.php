<?php
// zuzulo/tambah_domain.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($alamat_admin)) {
    // Fallback jika $alamat_admin tidak terdefinisi
    $current_dir_url_path = dirname($_SERVER['SCRIPT_NAME']);
    $alamat_admin = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . $current_dir_url_path . '/';
}

if (!isset($_SESSION['kode_admin'])) {
    echo '<script>alert("Terjadi kesalahan, harap masuk kembali!"); window.location.replace("'.rtrim($alamat_admin, '/').'/keluar.php");</script>';
    exit();
}

// 🔥 REVISI 1: require_once koneksi.php ditiadakan di sini karena sudah otomatis di-load mutlak oleh index.php induk admin lu

$pesan = "Catat NameServer Otomatis Akan Terhapus ketika Halaman di Refresh";

// =========================================================================
// BACKEND API & LOGIKA CLOUDFLARE / COOLIFY (MURNI 100% TIDAK DISENTUH)
// =========================================================================
function sinkronisasiDomainKeCoolifyLokal() {
    global $koneksi;
    $api_key = "3|HIDG5O5obDUSuAWiuoDPFSpABtbF4yhALvo3C9Nb14c5fa2b";
    $application_uuid = "sfpho7xg4jjpep1xpnaf8y8o";
    $domain_utama = "https://exampleproject.my.id";
    $list_domain = [$domain_utama];

    $query_domains = mysqli_query($koneksi, "SELECT domain_name FROM custom_domains");
    while ($row = mysqli_fetch_array($query_domains)) {
        if (!empty($row['domain_name'])) {
            $list_domain[] = "https://" . trim($row['domain_name']);
        }
    }
    $string_domains = implode(",", $list_domain);

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
    curl_exec($ch);
    curl_close($ch); 

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

function tambahSiteBaruCloudflareLokal($domainBaru) {
    $cf_email = 'adrnsyah' . '18' . '@' . 'gmail.com';
    $cf_key   = 'cfk_' . 'I4b6ZygMhnUoCSYEnPVfupCDOyAHan7ZIs9YbzGpa5e33a56'; 
    $data = ["name" => $domainBaru, "jump_start" => true];

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
    curl_close($ch);
    return json_decode($response, true);
}

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
    curl_exec($ch);
    curl_close($ch);
}

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
    $res_data = json_decode($response, true);
    curl_close($ch);
    return $res_data['result']['status'] ?? 'pending'; 
}

// EKSEKUSI HAPUS
if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus' && isset($_GET['id']) && isset($_GET['cf_id'])) {
    $id_hapus = mysqli_real_escape_string($koneksi, $_GET['id']);
    $zone_id_hapus = mysqli_real_escape_string($koneksi, $_GET['cf_id']);
    deleteSiteDariCloudflareLokal($zone_id_hapus);
    mysqli_query($koneksi, "DELETE FROM custom_domains WHERE id = '$id_hapus'");
    sinkronisasiDomainKeCoolifyLokal();
    $pesan = "<div class='alert alert-success alert-dismissible' role='alert'>Domain berhasil dihapus!<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
}

// EKSEKUSI TAMBAH
if (isset($_POST['submit_domain'])) {
    $domain_input = strtolower(trim($_POST['nama_domain']));
    $domain_clean = mysqli_real_escape_string($koneksi, $domain_input);

    if (preg_match('/^[a-z0-9.-]+\.[a-z]{2,}$/', $domain_clean)) {
        $hasil = tambahSiteBaruCloudflareLokal($domain_clean);

        if (isset($hasil['success']) && $hasil['success'] == true) {
            $zone_id = $hasil['result']['id']; 
            $ns1 = $hasil['result']['name_servers'][0] ?? 'ns1.cloudflare.com';
            $ns2 = $hasil['result']['name_servers'][1] ?? 'ns2.cloudflare.com';
            $ip_server_kamu = '137.184.155.151'; 

            // 🔥 REVISI 2: Suntik variabel kredensial di sini agar cURL di bawah terbaca tanpa memicu Warning teks putih
            $cf_email = 'adrnsyah' . '18' . '@' . 'gmail.com';
            $cf_key   = 'cfk_' . 'I4b6ZygMhnUoCSYEnPVfupCDOyAHan7ZIs9YbzGpa5e33a56'; 

            // A Record
            $dns_data = ["type" => "A", "name" => "@", "content" => $ip_server_kamu, "ttl" => 1, "proxied" => true];
            $ch_dns = curl_init("https://api.cloudflare.com/client/v4/zones/" . $zone_id . "/dns_records");
            curl_setopt($ch_dns, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch_dns, CURLOPT_POST, true);
            curl_setopt($ch_dns, CURLOPT_POSTFIELDS, json_encode($dns_data));
            curl_setopt($ch_dns, CURLOPT_HTTPHEADER, ['X-Auth-Email: '.$cf_email, 'X-Auth-Key: '.$cf_key, 'Content-Type: application/json']);
            curl_exec($ch_dns); curl_close($ch_dns);

            // SSL Full
            $ssl_payload = ["id" => "ssl", "value" => "full"];
            $ch_ssl = curl_init("https://api.cloudflare.com/client/v4/zones/" . $zone_id . "/settings/ssl");
            curl_setopt($ch_ssl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch_ssl, CURLOPT_CUSTOMREQUEST, "PATCH");
            curl_setopt($ch_ssl, CURLOPT_POSTFIELDS, json_encode($ssl_payload));
            curl_setopt($ch_ssl, CURLOPT_HTTPHEADER, ['X-Auth-Email: '.$cf_email, 'X-Auth-Key: '.$cf_key, 'Content-Type: application/json']);
            curl_exec($ch_ssl); curl_close($ch_ssl);

            $query_simpan = "INSERT INTO custom_domains (domain_name, cloudflare_id, status) VALUES ('$domain_clean', '$zone_id', 'pending')";
            if (mysqli_query($koneksi, $query_simpan)) {
                sinkronisasiDomainKeCoolifyLokal();
                $pesan = "<div class='alert alert-success alert-dismissible'><strong>Domain Terdaftar!</strong><br>NS 1: $ns1<br>NS 2: $ns2<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
            }
        } else {
            $error_msg = $hasil['errors'][0]['message'] ?? 'Cloudflare Error.';
            $pesan = "<div class='alert alert-danger'>$error_msg</div>";
        }
    }
}
?>

<h4 class="fw-bold py-3 mb-4">Manajemen Domain & Anti-Nawala</h4>

<?php if(!empty($pesan)) echo $pesan; ?>

<div class="card mb-4" style="background-color: #2b2c40; color: #cbcbd6;">
    <h5 class="card-header border-bottom border-secondary text-white fw-bold">Formulir Domain</h5>
    <div class="card-body pt-4">
        <small class="text-muted mb-3 d-block">Buat rute domain baru atau hubungkan ke proxy sistem.</small>
        <form method="POST" action="">
            <div class="row">
                <div class="col-md-10 mb-3">
                    <label class="form-label text-white fw-semibold">Nama Domain / Alamat Web</label>
                    <input type="text" name="nama_domain" class="form-control text-white bg-transparent border-secondary" placeholder="Contoh: harapanjp.my.id" required autocomplete="off" style="border: 1px solid #555 !important; padding: 10px;">
                    <div class="form-text text-muted mt-1"><i class="bx bx-info-circle"></i> Jangan masukkan karakter <code>http://</code> atau <code>https://</code>.</div>
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-3">
                <button type="submit" name="submit_domain" class="btn text-white fw-bold" style="background-color: #696cff;"><i class="bx bx-save me-1"></i> SIMPAN DOMAIN</button>
                <button type="reset" class="btn fw-bold" style="background-color: #ffab00; color: #fff;">RESET FORM</button>
            </div>
        </form>
    </div>
</div>

<div class="card" style="background-color: #2b2c40; color: #cbcbd6;">
    <h5 class="card-header border-bottom border-secondary text-white fw-bold">Daftar Custom Domain</h5>
    <div class="table-responsive text-nowrap">
        <table class="table mb-0">
            <thead>
                <tr style="border-bottom: 1px solid #444;">
                    <th style="width: 70px; color: #a3a4cc;" class="text-center">#</th>
                    <th style="color: #a3a4cc;">NAMA DOMAIN</th>
                    <th style="color: #a3a4cc;">STATUS AKTIF</th>
                    <th style="width: 150px; color: #a3a4cc;" class="text-center">AKSI</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                <?php
                $query_tampil = mysqli_query($koneksi, "SELECT * FROM custom_domains ORDER BY id DESC");
                $no = 1;
                if (mysqli_num_rows($query_tampil) > 0) {
                    while ($row = mysqli_fetch_assoc($query_tampil)) {
                        $status_sekarang = cekStatusZoneCloudflareLokal($row['cloudflare_id']);
                        
                        if ($status_sekarang !== $row['status']) {
                            $id_update = $row['id'];
                            mysqli_query($koneksi, "UPDATE custom_domains SET status = '$status_sekarang' WHERE id = '$id_update'");
                        }

                        $badge_class = ($status_sekarang === 'active') ? 'bg-label-success' : 'bg-label-warning';
                        ?>
                        <tr style="border-bottom: 1px solid #3c3d56;">
                            <td class="text-center fw-semibold"><?= $no++; ?></td>
                            <td><span class="fw-bold text-white"><?= htmlspecialchars($row['domain_name'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                            <td><span class="badge <?= $badge_class; ?> fw-bold"><?= strtoupper($status_sekarang); ?></span></td>
                            <td class="text-center">
                                <a href="?halaman=tambah_domain&aksi=hapus&id=<?= $row['id']; ?>&cf_id=<?= $row['cloudflare_id']; ?>" class="btn btn-sm text-white fw-bold" style="background-color: #ff3e1d;" onclick="return confirm('Hapus domain ini?')">
                                    <i class="bx bx-trash me-1"></i> HAPUS
                                </a>
                            </td>
                        </tr>
                        <?php 
                    } 
                } else {
                    echo "<tr><td colspan='4' class='text-center py-4 text-muted'>Belum ada custom domain yang terdaftar.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
