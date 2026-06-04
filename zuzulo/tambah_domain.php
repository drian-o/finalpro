<?php
// zuzulo/tambah_domain.php
require_once __DIR__ . '/../koneksi.php'; // Ini tetap dipakai HANYA untuk mengambil koneksi database ($koneksi)

$pesan = "";

// ========================================================
// FUNGSI SAKTI ADD DOMAIN KE CLOUDFLARE (DITARUH LANGSUNG DISINI)
// ========================================================
function tambahDomainKeCloudflareLokal($domainBaru) {
    // Mengambil credentials dari Environment Variables Coolify
    $cf_email = getenv('CF_EMAIL');
    $cf_key   = getenv('CF_GLOBAL_KEY');
    $cf_zone  = getenv('CF_ZONE_ID');

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

    if ($err) {
        return ['success' => false, 'error' => 'cURL Error: ' . $err];
    } else {
        return json_decode($response, true);
    }
}
// ========================================================

// Proses ketika tombol "Daftarkan Domain" diklik
if (isset($_POST['submit_domain'])) {
    $domain_input = strtolower(trim($_POST['nama_domain']));
    
    // Bersihkan inputan untuk mencegah SQL Injection
    $domain_clean = mysqli_real_escape_string($koneksi, $domain_input);

    // Validasi dasar format domain (ex: domain.com)
    if (preg_match('/^[a-z0-9.-]+\.[a-z]{2,}$/', $domain_clean)) {
        
        // MEMANGGIL FUNGSI LOKAL DI ATAS
        $hasil = tambahDomainKeCloudflareLokal($domain_clean);

        if (isset($hasil['success']) && $hasil['success'] == true) {
            $cloudflare_id = $hasil['result']['id']; // Ambil ID Custom Hostname dari CF

            // Simpan ke database MySQL dengan status 'pending'
            $query_simpan = "INSERT INTO custom_domains (domain_name, cloudflare_id, status) VALUES ('$domain_clean', '$cloudflare_id', 'pending')";
            
            if (mysqli_query($koneksi, $query_simpan)) {
                $pesan = "<div class='alert success'>
                            <strong>Sukses!</strong> Domain <u>$domain_clean</u> berhasil didaftarkan.<br>
                            <small>ID CF: $cloudflare_id</small><br>
                            <em>Instruksi: Minta user arahkan CNAME ke domain utama Anda atau ganti Nameserver.</em>
                          </div>";
            } else {
                $pesan = "<div class='alert error'><strong>Database Error:</strong> " . mysqli_error($koneksi) . "</div>";
            }
        } else {
            // Jika ditolak Cloudflare
            $error_msg = $hasil['errors'][0]['message'] ?? 'Terjadi kesalahan pada internal Cloudflare / Env variabel belum terbaca.';
            $pesan = "<div class='alert error'><strong>Cloudflare Error:</strong> $error_msg</div>";
        }
    } else {
        $pesan = "<div class='alert warning'><strong>Input Salah:</strong> Format nama domain tidak valid! (Contoh yang benar: toko-budi.com)</div>";
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
        .container { max-width: 800px; margin: auto; background-color: #1e1e1e; padding: 25px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.5); border: 1px solid #2d2d2d; }
        h2, h3 { color: #ffffff; margin-top: 0; }
        label { display: block; margin-bottom: 8px; font-weight: bold; color: #aaaaaa; }
        input[type="text"] { width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #333; border-radius: 6px; background-color: #2a2a2a; color: #fff; box-sizing: border-box; font-size: 16px; }
        input[type="text"]:focus { border-color: #007bff; outline: none; }
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
        hr { border: 0; height: 1px; background: #2d2d2d; margin: 25px 0; }
    </style>
</head>
<body>

<div class="container">
    <h2>Custom Domain Manager (SaaS Enterprise)</h2>
    <p style="color: #888; font-size: 14px; margin-bottom: 20px;">Daftarkan domain external milik user agar otomatis terhubung ke sistem reverse proxy platform Anda.</p>
    
    <?php if(!empty($pesan)) echo $pesan; ?>

    <form method="POST" action="">
        <label for="nama_domain">Masukkan Nama Domain Baru</label>
        <input type="text" id="nama_domain" name="nama_domain" placeholder="contoh: tokobaru.com atau sub.domainuser.net" required autocomplete="off">
        <button type="submit" name="submit_domain">Daftarkan & Ambil SSL</button>
    </form>

    <hr>

    <h3>Daftar Domain Terhubung</h3>
    <table>
        <thead>
            <tr>
                <th>Nama Domain</th>
                <th>Status SSL / Host</th>
                <th>Waktu Registrasi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Ambil data domain dari database
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
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='3' style='text-align: center; color: #666;'>Belum ada domain yang didaftarkan.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

</body>
</html>
