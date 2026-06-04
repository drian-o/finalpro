<?php
// zuzulo/tambah_domain.php
require_once __DIR__ . '/../koneksi.php'; 

$pesan = "";

// ========================================================
// FUNGSI SAKTI 1: TAMBAH ZONE/SITE BARU KE CLOUDFLARE (METODE NS)
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
// FUNGSI SAKTI 2: HAPUS ZONE/SITE DARI CLOUDFLARE
// ========================================================
function hapusSiteDariCloudflareLokal($zone_id) {
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
// LOGIKA PROSES TOMBOL: HAPUS SITE DOMAIN
// ========================================================
if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus' && isset($_GET['id']) && isset($_GET['cf_id'])) {
    $id_hapus = mysqli_real_escape_string($koneksi, $_GET['id']);
    $zone_id_hapus = mysqli_real_escape_string($koneksi, $_GET['cf_id']);
    
    $eksekusi_cf = hapusSiteDariCloudflareLokal($zone_id_hapus);

    if (isset($eksekusi_cf['success']) && $eksekusi_cf['success'] == true) {
        mysqli_query($koneksi, "DELETE FROM custom_domains WHERE id = '$id_hapus'");
        $pesan = "<div class='alert success'><strong>Sukses!</strong> Domain lama berhasil dihapus dari Cloudflare dan Database. Slot akun kosong kembali!</div>";
    } else {
        $error_msg_cf = $eksekusi_cf['errors'][0]['message'] ?? 'Koneksi ke Cloudflare gagal.';
        if (isset($eksekusi_cf['errors'][0]['code']) && ($eksekusi_cf['errors'][0]['code'] == 1006 || $eksekusi_cf['errors'][0]['code'] == 7003)) {
            mysqli_query($koneksi, "DELETE FROM custom_domains WHERE id = '$id_hapus'");
            $pesan = "<div class='alert success'><strong>Informasi:</strong> Domain sudah bersih, record database lokal dihapus.</div>";
        } else {
            $pesan = "<div class='alert error'><strong>Cloudflare Error:</strong> Gagal menghapus ($error_msg_cf)</div>";
        }
    }
}

// ========================================================
// LOGIKA PROSES TOMBOL: TAMBAH SITE DOMAIN (POST FORM)
// ========================================================
if (isset($_POST['submit_domain'])) {
    $domain_input = strtolower(trim($_POST['nama_domain']));
    $domain_clean = mysqli_real_escape_string($koneksi, $domain_input);

    if (preg_match('/^[a-z0-9.-]+\.[a-z]{2,}$/', $domain_clean)) {
        
        $hasil = tambahSiteBaruCloudflareLokal($domain_clean);

        if (isset($hasil['success']) && $hasil['success'] == true) {
            $zone_id = $hasil['result']['id']; 
            $ns1 = $hasil['result']['name_servers'][0] ?? 'ns1.cloudflare.com';
            $ns2 = $hasil['result']['name_servers'][1] ?? 'ns2.cloudflare.com';

            // --------------------------------------------------------
            // PENTING: PROSES OTOMATISASI ARTI (MEMBUAT A RECORD KE VPS)
            // --------------------------------------------------------
            $cf_email = 'adrnsyah' . '18' . '@' . 'gmail.com';
            $cf_key   = 'cfk_' . 'I4b6ZygMhnUoCSYEnPVfupCDOyAHan7ZIs9YbzGpa5e33a56'; 

            // PENTING: Ganti string di bawah ini dengan IP VPS Coolify kamu!
            $ip_server_kamu = '137.184.155.151'; 

            // Payload untuk mengarahkan root domain (@) ke IP VPS
            $dns_data = [
                "type" => "A",
                "name" => "@",
                "content" => $ip_server_kamu,
                "ttl" => 1, 
                "proxied" => true // Wajib true agar dapet SSL/HTTPS otomatis dari Cloudflare
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
            // --------------------------------------------------------

            // Simpan ke database MySQL
            $query_simpan = "INSERT INTO custom_domains (domain_name, cloudflare_id, status) VALUES ('$domain_clean', '$zone_id', 'pending')";
            
            if (mysqli_query($koneksi, $query_simpan)) {
                $pesan = "
                <div class='alert success'>
                    <strong>🎉 Sukses! Domain Berhasil Didaftarkan ke Sistem</strong><br>
                    <p style='margin: 5px 0;'>ID Zone: <code>$zone_id</code></p>
                    <hr style='border: 0; border-top: 1px solid #27ae60; margin: 10px 0;'>
                    
                    <strong style='color: #fff;'>🛠️ INSTRUKSI PINDAH NAMESERVER (NS) USER:</strong>
                    <p style='font-size: 13px; margin: 5px 0 10px 0;'>Silakan masukkan kedua nilai berikut ke pengaturan **Custom DNS** di Namecheap Anda:</p>
                    
                    <div style='background: #111; padding: 12px; border-radius: 5px; border: 1px solid #444; font-family: monospace;'>
                        1. <strong style='color: #f1c40f;'>$ns1</strong><br>
                        2. <strong style='color: #f1c40f;'>$ns2</strong>
                    </div>
                    <small style='color: #aaa; display:block; margin-top:10px;'>*Sistem telah otomatis membuatkan rute DNS ke server. Website akan langsung aktif begitu propagasi NS di Namecheap selesai.</small>
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Custom Domain Manager (NS Mode)</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #121212; color: #e0e0e0; padding: 30px; margin: 0; }
        .container { max-width: 850px; margin: auto; background-color: #1e1e1e; padding: 25px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.5); border: 1px solid #2d2d2d; }
        h2, h3 { color: #ffffff; margin-top: 0; }
        label { display: block; margin-bottom: 8px; font-weight: bold; color: #aaaaaa; }
        input[type="text"] { width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #333; border-radius: 6px; background-color: #2a2a2a; color: #fff; box-sizing: border-box; font-size: 16px; }
        button { background-color: #27ae60; color: white; padding: 12px 24px; border: none; border-radius: 6px; font-size: 16px; cursor: pointer; font-weight: bold; width: 100%; }
        button:hover { background-color: #219150; }
        .alert { padding: 15px; border-radius: 6px; margin-bottom: 20px; font-size: 14px; line-height: 1.5; }
        .success { background-color: #1b4d3e; color: #2ecc71; border: 1px solid #27ae60; }
        .error { background-color: #4c1d1d; color: #e74c3c; border: 1px solid #c0392b; }
        .warning { background-color: #4d3a1b; color: #f1c40f; border: 1px solid #d35400; }
        table { width: 100%; border-collapse: collapse; margin-top: 25px; background-color: #1a1a1a; }
        th, td { padding: 12px 15px; border: 1px solid #2d2d2d; text-align: left; }
        th { background-color: #252525; color: #ffffff; font-weight: 600; }
        tr:nth-child(even) { background-color: #202020; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; display: inline-block; }
        .badge-active { background-color: #27ae60; color: #fff; }
        .badge-pending { background-color: #d35400; color: #fff; }
        .btn-delete { background-color: #c0392b; color: white; padding: 5px 10px; text-decoration: none; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .btn-delete:hover { background-color: #e74c3c; }
        hr { border: 0; height: 1px; background: #2d2d2d; margin: 25px 0; }
        code { background: #000; padding: 2px 6px; border-radius: 4px; color: #f1c40f; font-family: monospace; }
    </style>
</head>
<body>

<div class="container">
    <h2>SaaS Domain Manager (Nameserver Mode)</h2>
    <p style="color: #888; font-size: 14px; margin-bottom: 20px;">User cukup mengganti pengaturan Nameservers di panel registrar mereka (Namecheap/Niagahoster) ke Custom Nameserver platform Anda.</p>
    
    <?php if(!empty($pesan)) echo $pesan; ?>

    <form method="POST" action="">
        <label for="nama_domain">Input Domain Sampel</label>
        <input type="text" id="nama_domain" name="nama_domain" placeholder="contoh: domainsampel.com" required autocomplete="off">
        <button type="submit" name="submit_domain">Generate Custom Nameserver</button>
    </form>

    <hr>

    <h3>Daftar Manajemen Domain User</h3>
    <table>
        <thead>
            <tr>
                <th>Nama Domain</th>
                <th>Status Sistem</th>
                <th>Waktu Registrasi</th>
                <th style="text-align: center;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $query_tampil = mysqli_query($koneksi, "SELECT * FROM custom_domains ORDER BY id DESC");
            if (mysqli_num_rows($query_tampil) > 0) {
                while ($row = mysqli_fetch_assoc($query_tampil)) {
                    $status_badge = ($row['status'] === 'active') 
                        ? "<span class='badge badge-active'>ACTIVE</span>" 
                        : "<span class='badge badge-pending'>PENDING</span>";
                    
                    echo "<tr>
                            <td><strong>" . htmlspecialchars($row['domain_name']) . "</strong></td>
                            <td>$status_badge</td>
                            <td><small>" . $row['created_at'] . "</small></td>
                            <td style='text-align: center;'>
                                <a href='?aksi=hapus&id={$row['id']}&cf_id={$row['cloudflare_id']}' class='btn-delete' onclick='return confirm(\"Apakah Anda yakin ingin menghapus total domain {$row['domain_name']} ini?\")'>Hapus</a>
                            </td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='4' style='text-align: center; color: #666;'>Belum ada domain yang didaftarkan.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

</body>
</html>
