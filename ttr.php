<?php

// URL yang ingin Anda akses
$url = 'https://busanslotid.in/office/game-oc/game/getNodeInfoList?l=id&l=id&parentId=24792061';

// Inisialisasi sesi cURL
$ch = curl_init();

// Menetapkan opsi cURL
// CURLOPT_URL: Menentukan URL yang akan diakses
curl_setopt($ch, CURLOPT_URL, $url);

// CURLOPT_RETURNTRANSFER: Mengatur cURL agar mengembalikan hasil transfer sebagai string
// daripada langsung menampilkannya di layar. Ini sangat penting untuk memproses respons.
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Tambahan: Atur timeout untuk mencegah skrip berjalan terlalu lama
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

// Tambahan: (Opsional) Jika server membutuhkan user agent
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36');

// Jalankan sesi cURL
$response = curl_exec($ch);

// Periksa apakah ada kesalahan dalam cURL
if (curl_errno($ch)) {
    $error_msg = curl_error($ch);
    echo "<h2>Error cURL:</h2>";
    echo "<p>$error_msg</p>";
} else {
    // Jika tidak ada error, tampilkan responsnya
    echo "<h2>Respons dari URL:</h2>";
    // Respons biasanya berupa JSON, jadi Anda bisa memformatnya agar lebih mudah dibaca
    echo "<pre>";
    echo htmlspecialchars($response);
    echo "</pre>";

    // Jika Anda ingin memproses data JSON
    $data = json_decode($response, true);
    if ($data !== null) {
        // Data berhasil di-decode, Anda bisa menggunakannya
        echo "<h2>Data JSON (terdekorasi):</h2>";
        echo "<pre>";
        print_r($data);
        echo "</pre>";
    } else {
        echo "<h2>Data JSON tidak valid atau kosong.</h2>";
    }
}

// Tutup sesi cURL
curl_close($ch);

?>