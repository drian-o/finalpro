<?php
// zuzulo/cron_cek_status.php
require_once __DIR__ . '/../koneksi.php'; // Menggunakan __DIR__ agar aman saat dijalankan via Cron

// 1. Ambil semua domain yang statusnya masih 'pending'
$query = mysqli_query($koneksi, "SELECT id, domain_name, cloudflare_id FROM custom_domains WHERE status = 'pending' LIMIT 10");

echo "Memulai pengecekan status domain...<br>";

while ($row = mysqli_fetch_assoc($query)) {
    $domain_id = $row['id'];
    $cf_id = $row['cloudflare_id'];
    $domain_name = $row['domain_name'];

    // 2. Tembak API Cloudflare untuk cek status spesifik ID tersebut
    $ch = curl_init("https://api.cloudflare.com/client/v4/zones/" . CF_ZONE_ID . "/custom_hostnames/" . $cf_id);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'X-Auth-Email: ' . CF_EMAIL,
        'X-Auth-Key: ' . CF_GLOBAL_KEY,
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    $resObj = json_decode($response, true);

    if (isset($resObj['success']) && $resObj['success'] == true) {
        $status_sekarang = $resObj['result']['status']; // Biasanya: 'active', 'pending', atau 'moved'
        $ssl_status = $resObj['result']['ssl']['status']; // Biasanya: 'active' atau 'pending_validation'

        echo "Domain $domain_name -> Status: $status_sekarang | SSL: $ssl_status<br>";

        // 3. Jika status host dan SSL sudah 'active', update database
        if ($status_sekarang === 'active' && $ssl_status === 'active') {
            mysqli_query($koneksi, "UPDATE custom_domains SET status = 'active' WHERE id = '$domain_id'");
            echo "--- SUCCESS: $domain_name sekarang AKTIF!<br>";
        }
    } else {
        echo "Gagal mengecek $domain_name: " . ($resObj['errors'][0]['message'] ?? 'Unknown Error') . "<br>";
    }
}

echo "Pengecekan selesai.";
?>
