<?php
// Set header respons HTTP yang sesuai. 403 Forbidden adalah pilihan yang umum.
http_response_code(403);

// Mendapatkan informasi yang mungkin relevan untuk dicatat (opsional)
$ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Tidak diketahui';
$request_uri = $_SERVER['REQUEST_URI'] ?? 'Tidak diketahui';
$timestamp = date("Y-m-d H:i:s T");

// Mencatat upaya ini (opsional, tetapi sangat disarankan)
// Anda bisa mengganti ini dengan mekanisme logging yang lebih canggih
$log_message = "{$timestamp} - Akses Mencurigakan Ditolak - IP: {$ip_address} - URI: {$request_uri}\n";
 file_put_contents('security_violations.log', $log_message, FILE_APPEND);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AKSES DITOLAK - PERINGATAN KEAMANAN</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap');

        body {
            font-family: 'Open Sans', sans-serif;
            background-color: #f0f2f5; /* Latar belakang abu-abu muda netral */
            color: #333;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            line-height: 1.6;
        }

        .container {
            background-color: #ffffff; /* Konten dengan latar putih */
            padding: 30px 40px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            max-width: 700px;
            width: 90%;
            text-align: left;
            border-top: 5px solid #d9534f; /* Aksen merah untuk peringatan */
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
        }

        .header h1 {
            font-size: 26px;
            font-weight: 700;
            color: #d9534f; /* Warna merah untuk judul peringatan */
            margin: 0 0 10px 0;
        }

        .header p {
            font-size: 16px;
            color: #555;
            margin: 0;
        }

        .content h2 {
            font-size: 20px;
            font-weight: 600;
            color: #2c3e50; /* Biru tua untuk sub-judul */
            margin-top: 25px;
            margin-bottom: 10px;
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 5px;
        }

        .content p, .content ul {
            font-size: 15px;
            color: #444;
            margin-bottom: 15px;
        }

        .content ul {
            padding-left: 20px;
        }

        .content li {
            margin-bottom: 8px;
        }

        .warning-box {
            background-color: #f8d7da; /* Latar belakang merah muda untuk kotak peringatan hukum */
            border: 1px solid #f5c6cb;
            color: #721c24; /* Teks merah tua */
            padding: 15px;
            border-radius: 4px;
            margin-top: 20px;
        }
        
        .warning-box strong {
            font-weight: 700;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 13px;
            color: #777;
        }

        .footer a {
            color: #007bff; /* Link biru standar */
            text-decoration: none;
        }

        .footer a:hover {
            text-decoration: underline;
        }

        @media (max-width: 600px) {
            .container {
                padding: 20px 25px;
            }
            .header h1 {
                font-size: 22px;
            }
            .content h2 {
                font-size: 18px;
            }
            .content p, .content ul {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>AKSES DITOLAK</h1>
            <p>Sistem Keamanan Situs Telah Mendeteksi Aktivitas Mencurigakan</p>
        </div>

        <div class="content">
            <p>Permintaan Anda ke situs ini telah diblokir karena terindikasi adanya upaya yang dapat mengganggu atau merusak sistem kami. Kami mencatat semua upaya akses yang tidak sah.</p>
            
            <p>Informasi terkait upaya ini, termasuk alamat IP (<strong><?php echo htmlspecialchars($ip_address); ?></strong>) dan detail permintaan, telah dicatat oleh sistem kami untuk keperluan investigasi lebih lanjut.</p>

            <h2>PERINGATAN HUKUM</h2>
            <p>Perlu diketahui bahwa setiap upaya untuk mengakses, meretas, mengubah, atau merusak sistem elektronik tanpa hak adalah pelanggaran hukum dan dapat dikenakan sanksi pidana berdasarkan peraturan perundang-undangan yang berlaku di Republik Indonesia.</p>

            <div class="warning-box">
                <strong>Dasar Hukum Relevan (Undang-Undang No. 11 Tahun 2008 sebagaimana telah diubah oleh Undang-Undang No. 19 Tahun 2016 tentang Informasi dan Transaksi Elektronik - UU ITE):</strong>
                <ul>
                    <li><strong>Pasal 30 ayat (1):</strong> "Setiap Orang dengan sengaja dan tanpa hak atau melawan hukum mengakses Komputer dan/atau Sistem Elektronik milik Orang lain dengan cara apa pun."</li>
                    <li><strong>Pasal 30 ayat (3):</strong> "Setiap Orang dengan sengaja dan tanpa hak atau melawan hukum mengakses Komputer dan/atau Sistem Elektronik dengan cara apa pun dengan melanggar, menerobos, melampaui, atau menjebol sistem pengamanan."</li>
                    <li><strong>Pasal 46 ayat (1):</strong> "Setiap Orang yang memenuhi unsur sebagaimana dimaksud dalam Pasal 30 ayat (1) dipidana dengan pidana penjara paling lama 6 (enam) tahun dan/atau denda paling banyak Rp600.000.000,00 (enam ratus juta rupiah)."</li>
                    <li><strong>Pasal 46 ayat (3):</strong> "Setiap Orang yang memenuhi unsur sebagaimana dimaksud dalam Pasal 30 ayat (3) dipidana dengan pidana penjara paling lama 8 (delapan) tahun dan/atau denda paling banyak Rp800.000.000,00 (delapan ratus juta rupiah)."</li>
                </ul>
                <p>Upaya lebih lanjut yang bersifat ilegal akan kami tindak lanjuti melalui jalur hukum bekerja sama dengan pihak yang berwenang.</p>
            </div>

            <h2>Bagi Pengguna Sah</h2>
            <p>Jika Anda adalah pengguna sah dan merasa pemblokiran ini adalah sebuah kekeliruan, mohon untuk tidak mengulangi tindakan yang sama. Pastikan input atau parameter yang Anda gunakan tidak mengandung karakter atau pola yang mencurigakan. Anda dapat mencoba kembali atau menghubungi administrator situs jika masalah berlanjut.</p>
        </div>

        <div class="footer">
            <p>&copy; <?php echo date("Y"); ?> <?php echo htmlspecialchars($_SERVER['SERVER_NAME'] ?? 'Nama Situs Anda'); ?> - Sistem Keamanan Aktif</p>
            <p><a href="/">Kembali ke Halaman Utama</a></p>
        </div>
    </div>
</body>
</html>