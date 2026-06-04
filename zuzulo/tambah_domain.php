<?php
// zuzulo/tambah_domain.php
require_once __DIR__ . '/../koneksi.php'; 

$pesan = "";

// ========================================================
// FUNGSI SAKTI 1: ADD DOMAIN KE CLOUDFLARE
// ========================================================
function tambahDomainKeCloudflareLokal($domainBaru) {
    $cf_email = 'adrnsyah' . '18' . '@' . 'gmail.com';
    $cf_key   = 'cfk_' . 'I4b6ZygMhnUoCSYEnPVfupCDOyAHan7ZIs9YbzGpa5e33a56'; 
    $cf_zone  = '8b3db279f' . '639e2e3b1d0c5' . 'a7c5c6252d';

    $data = [
        "hostname" => $domainBaru,
        "ssl" => [
            "method" => "http",
            "type" => "dv"
        ]
    ];

    $ch = curl_init("https://api.cloudflare.com/client/v4/zones/" . $cf_zone . "/custom_hostnames");
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
// FUNGSI SAKTI 2: DELETE DOMAIN DARI CLOUDFLARE
// ========================================================
function hapusDomainDariCloudflareLokal($cloudflare_id) {
    $cf_email = 'adrnsyah' . '18' . '@' . 'gmail.com';
    $cf_key   = 'cfk_' . 'I4b6ZygMhnUoCSYEnPVfupCDOyAHan7ZIs9YbzGpa5e33a56'; 
    $cf_zone  = '8b3db279f ' . '639e2e3b1d0c5' . 'a7c5c6252d';

    // Menggunakan metode DELETE ke API Cloudflare mendasarkan ID Custom Hostname
    $ch = curl_init("https://api.cloudflare.com/client/v4/zones/" . trim($cf_zone) . "/custom_hostnames/" . $cloudflare_id);
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
// LOGIKA PROSES TOMBOL: HAPUS DOMAIN (DIPICU VIA GET URL)
// ========================================================
if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus' && isset($_GET['id']) && isset($_GET['cf_id'])) {
    $id_hapus = mysqli_real_escape_string($koneksi, $_GET['id']);
    $cf_id_hapus = mysqli_real_escape_string($koneksi, $_GET['cf_id']);
    
    // 1. Eksekusi hapus di Cloudflare dulu
    $eksekusi_cf = hapusDomainDariCloudflareLokal($cf_id_hapus);

    if (isset($eksekusi_cf['success']) && $eksekusi_cf['success'] == true) {
        // 2. Jika Cloudflare sukses menghapus, hapus juga dari database MySQL
        $query_hapus_db = mysqli_query($koneksi, "DELETE FROM custom_domains WHERE id = '$id_hapus'");
        
        if ($query_hapus_db) {
            $pesan = "<div class='alert success'><strong>Sukses!</strong> Domain lama berhasil dihapus dari Cloudflare dan Database. Kuota slot kosong kembali!</div>";
        } else {
            $pesan = "<div class='alert error'><strong>DB Error:</strong> Gagal menghapus record di database.</div>";
        }
    } else {
        $error_msg_cf = $eksekusi_cf['errors'][0]['message'] ?? 'Koneksi ke Cloudflare gagal.';
        // Tetap izinkan hapus dari DB lokal jika ID di Cloudflare memang sudah tidak ditemukan (Code 1437 atau sejenisnya)
        if (isset($eksekusi_cf['errors'][0]['code']) && ($eksekusi_cf['errors'][0]['code'] == 1437 || $eksekusi_cf['errors'][0]['code'] == 7003)) {
            mysqli_query($koneksi, "DELETE FROM custom_domains WHERE id = '$id_hapus'");
            $pesan = "<div class='alert success'><strong>Informasi:</strong> Domain sudah tidak ada di Cloudflare, record di database lokal dibersihkan.</div>";
        } else {
            $pesan = "<div class='alert error'><strong>Cloudflare Error:</strong> Gagal menghapus domain ($error_msg_cf)</div>";
        }
    }
}

// ========================================================
// LOGIKA PROSES TOMBOL: TAMBAH DOMAIN (POST FORM)
// ========================================================
if (isset($_POST['submit_domain'])) {
    $domain_input = strtolower(trim($_POST['nama_domain']));
    $domain_clean = mysqli_real_escape_string($koneksi, $domain_input);

    if (preg_match('/^[a-z0-9.-]+\.[a-z]{2,}$/', $domain_clean)) {
        
        $hasil = tambahDomainKeCloudflareLokal($domain_clean);

        if (isset($hasil['success']) && $hasil['success'] == true) {
            $cloudflare_id = $hasil['result']['id']; 

            $txt_name  = $hasil['result']['ownership_verification']['name'] ?? '_cf-custom-hostname.' . $domain_clean;
            $txt_value = $hasil['result']['ownership_verification']['value'] ?? 'N/A';
            $cname_target = $_SERVER['HTTP_HOST']; 

            $query_simpan = "INSERT INTO custom_domains (domain_name, cloudflare_id, status) VALUES ('$domain_clean', '$cloudflare_id', 'pending')";
            
            if (mysqli_query($koneksi, $query_simpan)) {
                $pesan = "
                <div class='alert success'>
                    <strong>🎉 Sukses! Domain Berhasil Didaftarkan</strong><br>
                    <p style='margin: 5px 0;'>ID Cloudflare: <code>$cloudflare_id</code></p>
                    <hr style='border: 0; border-top: 1px solid #27ae60; margin: 10px 0;'>
                    
                    <strong style='color: #fff;'>📋 PANDUAN SETUP DNS DI NAMECHEAP:</strong>
                    <table style='width:100%; font-size:12px; border-collapse: collapse; background:#111; margin-top: 5px;'>
                        <tr style='background:#222; color:#fff;'>
                            <th style='padding:6px; border:1px solid #444;'>Type</th>
                            <th style='padding:6px; border:1px solid #444;'>Host / Name</th>
                            <th style='padding:6px; border:1px solid #444;'>Value / Target</th>
                        </tr>
                        <tr>
                            <td style='padding:6px; border:1px solid #444; color:#f1c40f;'><strong>TXT Record</strong></td>
                            <td style='padding:6px; border:1px solid #444;'><code>" . str_replace('.'.$domain_clean, '', $txt_name) . "</code></td>
                            <td style='padding:6px; border:1px solid #444;'><code>$txt_value</code></td>
                        </tr>
                        <tr>
                            <td style='padding:6px; border:1px solid #444; color:#3498db;'><strong>CNAME Record</strong></td>
                            <td style='padding:6px; border:1px solid #444;'><code>@</code></td>
                            <td style='padding:6px; border:1px solid #444;'><code>$cname_target</code></td>
                        </tr>
                    </table>
                </div>";
            } else {
                $pesan = "<div class='alert error'><strong>Database Error:</strong> " . mysqli_error($koneksi) . "</div>";
            }
        } else {
            $error_msg = $hasil['errors'][0]['message'] ?? 'Gagal menambahkan domain.';
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
    <title>Admin - Custom Domain Manager</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #121212; color: #e0e0e0; padding: 30px; margin: 0; }
        .container { max-width: 850px; margin: auto; background-color: #1e1e1e; padding: 25px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.5); border: 1px solid #2d2d2d; }
        h2, h3 { color: #ffffff; margin-top: 0; }
        label { display: block; margin-bottom: 8px; font-weight: bold; color: #aaaaaa; }
        input[type="text"] { width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #333; border-radius: 6px; background-color: #2a2a2a; color: #fff; box-sizing: border-box; font-size: 16px; }
        button { background-color: #007bff; color: white; padding: 12px 24px; border: none; border-radius: 6px; font-size: 16px; cursor: pointer; font-weight: bold; width: 100%; }
        button:hover { background-color: #0056b3; }
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
    <h2>Custom Domain Manager (SaaS Enterprise)</h2>
    <p style="color: #888; font-size: 14px; margin-bottom: 20px;">Kelola domain dengan aman. Domain yang terkena nawala dapat dihapus seketika untuk mengosongkan slot proxy.</p>
    
    <?php if(!empty($pesan)) echo $pesan; ?>

    <form method="POST" action="">
        <label for="nama_domain">Daftarkan Domain Baru</label>
        <input type="text" id="nama_domain" name="nama_domain" placeholder="contoh: tokobaru.com" required autocomplete="off">
        <button type="submit" name="submit_domain">Daftarkan & Ambil SSL</button>
    </form>

    <hr>

    <h3>Daftar Domain Aktif / Terhubung</h3>
    <table>
        <thead>
            <tr>
                <th>Nama Domain</th>
                <th>Status</th>
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
                                <a href='?aksi=hapus&id={$row['id']}&cf_id={$row['cloudflare_id']}' class='btn-delete' onclick='return confirm(\"Apakah Anda yakin ingin menghapus domain {$row['domain_name']} ini dari Cloudflare?\")'>Hapus</a>
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
