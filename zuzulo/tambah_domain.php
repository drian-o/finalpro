<?php
// zuzulo/tambah_domain.php
require_once __DIR__ . '/../koneksi.php'; 

$pesan = "";

// =========================================================================
// FUNGSI SAKTI: OTOMATIS DAFTARKAN SEMUA DOMAIN KE COOLIFY VIA API (HTTPS RESMI)
// =========================================================================
function sinkronisasiDomainKeCoolifyLokal() {
    global $koneksi;

    $api_key = "3|HIDG5O5obDUSuAWiuoDPFSpABtbF4yhALvo3C9Nb14c5fa2b";
    
    // 🔥 PERBAIKAN MUTLAK 1: Menggunakan UUID Aplikasi asli dari URL browser lu!
    $application_uuid = "sfpho7xg4jjpep1xpnaf8y8o";
    
    // Semua daftar domain platform wajib HTTPS murni
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

    // Tembak API Coolify port 8000 luar VPS
    $url = "http://137.184.155.151:8000/api/v1/applications/" . $application_uuid;
    $data_payload = json_encode(array("fqdn" => $string_domains));

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_payload);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    // Matikan verifikasi SSL agar cURL lokal mau tembus tanpa hambatan sertifikat
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $api_key
    ]);

    curl_exec($ch);
    close_curl($ch);
}

// Helper untuk menutup cURL dengan aman
function close_curl($ch) {
    if (is_resource($ch) || (is_object($ch) && $ch instanceof CurlHandle)) {
        curl_close($ch);
    }
}

// ========================================================
// FUNGSI SAKTI 1: TAMBAH ZONE BARU KE CLOUDFLARE (METODE NS)
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
    close_curl($ch);

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
    close_curl($ch);

    return $err ? ['success' => false, 'error' => $err] : json_decode($response, true);
}

// ========================================================
// FUNGSI SAKTI 3: CEK STATUS DOMAIN DI CLOUDFLARE
// ========================================================
function cekStatusZoneCloudflare($zone_id) {
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
    close_curl($ch);

    if ($err) return 'pending';
    $res_data = json_decode($response, true);
    
    return $res_data['result']['status'] ?? 'pending';
}

// LOGIKA PROSES TOMBOL: HAPUS SITE DOMAIN
if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus' && isset($_GET['id']) && isset($_GET['cf_id'])) {
    $id_hapus = mysqli_real_escape_string($koneksi, $_GET['id']);
    $zone_id_hapus = mysqli_real_escape_string($koneksi, $_GET['cf_id']);
    
    $eksekusi_cf = deleteSiteDariCloudflareLokal($zone_id_hapus);

    if (isset($eksekusi_cf['success']) && $eksekusi_cf['success'] == true) {
        mysqli_query($koneksi, "DELETE FROM custom_domains WHERE id = '$id_hapus'");
        sinkronisasiDomainKeCoolifyLokal();
        $pesan = "<div class='alert success'><strong>Sukses!</strong> Domain lama berhasil dihapus.</div>";
    } else {
        mysqli_query($koneksi, "DELETE FROM custom_domains WHERE id = '$id_hapus'");
        sinkronisasiDomainKeCoolifyLokal();
        $pesan = "<div class='alert success'><strong>Informasi:</strong> Domain dibersihkan dari database lokal.</div>";
    }
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

            // 1. Buat A record di Cloudflare dengan Proxy ON (Awan Oranye) untuk SSL otomatis
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
            close_curl($ch_dns);

            // 2. Set SSL Cloudflare ke "full" agar jabat tangan HTTPS murni ke https://* Coolify lolos
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
            close_curl($ch_ssl);

            // 3. Simpan data domain ke database MySQL
            $query_simpan = "INSERT INTO custom_domains (domain_name, cloudflare_id, status) VALUES ('$domain_clean', '$zone_id', 'pending')";
            
            if (mysqli_query($koneksi, $query_simpan)) {
                
                // 🔥 SAKTI UTAMA: Panggil otomatisasi pengisian kolom FQDN di Coolify
                sinkronisasiDomainKeCoolifyLokal();

                $pesan = "
                <div class='alert success'>
                    <strong>🎉 Sukses! Domain Berhasil Didaftarkan ke Sistem</strong><br>
                    <p style='margin: 5px 0;'>ID Zone: <code>$zone_id</code></p>
                    <hr style='border: 0; border-top: 1px solid #27ae60; margin: 10px 0;'>
                    <strong style='color: #fff;'>🛠️ INSTRUKSI PINDAH NAMESERVER (NS) USER:</strong>
                    <div style='background: #111; padding: 12px; border-radius: 5px; border: 1px solid #444; font-family: monospace; margin-top: 5px;'>
                        1. <strong style='color: #f1c40f;'>$ns1</strong><br>
                        2. <strong style='color: #f1c40f;'>$ns2</strong>
                    </div>
                </div>";
            } else {
                $pesan = "<div class='alert error'><strong>Database Error:</strong> " . mysqli_error($koneksi) . "</div>";
            }
        } else {
            $error_msg = $hasil['errors'][0]['message'] ?? 'Gagal menambahkan domain ke Cloudflare.';
            $pesan = "<div class='alert error'><strong>Cloudflare Error:</strong> $error_msg</div>";
        }
    } else {
        $pesan = "<div class='alert warning'><strong>Input Salah:</strong> Format nama domain tidak valid!</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin - Custom Domain Manager</title>
    <style>
        body { font-family: sans-serif; background-color: #121212; color: #e0e0e0; padding: 30px; }
        .container { max-width: 850px; margin: auto; background-color: #1e1e1e; padding: 25px; border-radius: 10px; border: 1px solid #2d2d2d; }
        input[type="text"] { width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #333; background-color: #2a2a2a; color: #fff; box-sizing: border-box; }
        button { background-color: #27ae60; color: white; padding: 12px 24px; border: none; cursor: pointer; width: 100%; font-weight: bold; }
        .alert { padding: 15px; border-radius: 6px; margin-bottom: 20px; }
        .success { background-color: #1b4d3e; color: #2ecc71; border: 1px solid #27ae60; }
        .error { background-color: #4c1d1d; color: #e74c3c; border: 1px solid #c0392b; }
        table { width: 100%; border-collapse: collapse; margin-top: 25px; }
        th, td { padding: 12px; border: 1px solid #2d2d2d; text-align: left; }
        th { background-color: #252525; }
        .btn-delete { background-color: #c0392b; color: white; padding: 5px 10px; text-decoration: none; border-radius: 4px; font-size: 12px; }
    </style>
</head>
<body>
<div class="container">
    <h2>SaaS Domain Manager (Full Auto API Mode)</h2>
    <?php if(!empty($pesan)) echo $pesan; ?>
    <form method="POST" action="">
        <label>Input Domain Baru User</label>
        <input type="text" name="nama_domain" placeholder="contoh: domainsampel.com" required autocomplete="off">
        <button type="submit" name="submit_domain">Daftarkan & Generate NS</button>
    </form>
    <table>
        <thead>
            <tr>
                <th>Nama Domain</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $query_tampil = mysqli_query($koneksi, "SELECT * FROM custom_domains ORDER BY id DESC");
            while ($row = mysqli_fetch_assoc($query_tampil)) {
                echo "<tr>
                        <td><strong>" . htmlspecialchars($row['domain_name'] ?? '', ENT_QUOTES, 'UTF-8') . "</strong></td>
                        <td>" . htmlspecialchars($row['status'] ?? '', ENT_QUOTES, 'UTF-8') . "</td>
                        <td><a href='?aksi=hapus&id={$row['id']}&cf_id={$row['cloudflare_id']}' class='btn-delete' onclick='return confirm(\"Hapus?\")'>Hapus</a></td>
                      </tr>";
            }
            ?>
        </tbody>
    </table>
</div>
</body>
</html>
