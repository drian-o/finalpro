<?php
// Menonaktifkan caching untuk memastikan kita mendapatkan data terbaru
clearstatcache();

echo '<pre>'; // Menggunakan <pre> agar output lebih mudah dibaca

// --- Validasi Input ---
$trxId = $_GET['trx_id'] ?? null;
if (empty($trxId)) {
    die("GAGAL: Harap berikan trx_id di URL. Contoh: /cek_akses.php?trx_id=ID_TRANSAKSI_ANDA");
}
echo "Mengecek untuk TRX ID: " . htmlspecialchars($trxId) . "\n\n";


// --- Definisi Path ---
$statusDir = __DIR__ . '/status_pembayaran/';
$filePath = $statusDir . basename($trxId); // Menggunakan basename untuk keamanan dasar

echo "Path Direktori Status yang diuji: " . htmlspecialchars($statusDir) . "\n";
echo "Path File Lengkap yang diuji: " . htmlspecialchars($filePath) . "\n\n";


// --- Pengecekan Direktori ---
echo "--- MENGECEK DIREKTORI ---\n";
if (is_dir($statusDir)) {
    echo "is_dir(): BENAR - Path direktori adalah sebuah direktori yang valid.\n";
    
    // Cek izin baca direktori
    if (is_readable($statusDir)) {
        echo "is_readable() direktori: BENAR - PHP memiliki izin untuk MEMBACA isi direktori.\n";
        
        // Tes paling penting: coba scan isi direktori
        echo "\n--- MENCARI FILE DI DALAM DIREKTORI ---\n";
        try {
            $files = scandir($statusDir);
            echo "scandir(): BERHASIL. Berikut adalah file yang BISA DILIHAT oleh PHP di dalam direktori:\n";
            print_r($files);
            
            if (!in_array(basename($trxId), $files)) {
                echo "\n\nKESIMPULAN PENTING: File '" . htmlspecialchars(basename($trxId)) . "' TIDAK ADA dalam daftar file yang bisa dilihat PHP, meskipun mungkin Anda bisa melihatnya di File Manager.\n";
            } else {
                 echo "\n\nKESIMPULAN PENTING: File '" . htmlspecialchars(basename($trxId)) . "' ADA dalam daftar file yang bisa dilihat PHP.\n";
            }
            
        } catch (Exception $e) {
            echo "scandir(): GAGAL dengan error: " . $e->getMessage() . "\n";
        }
        
    } else {
        echo "is_readable() direktori: SALAH - PHP TIDAK memiliki izin untuk MEMBACA isi direktori.\n";
    }
} else {
    echo "is_dir(): SALAH - Path direktori TIDAK valid atau TIDAK dapat diakses.\n";
}


// --- Pengecekan File Langsung ---
echo "\n\n--- MENGECEK FILE SECARA LANGSUNG ---\n";
if (file_exists($filePath)) {
    echo "file_exists(): BENAR - PHP berhasil menemukan file tersebut.\n";
    
    // Cek izin baca file
    if (is_readable($filePath)) {
        echo "is_readable() file: BENAR - PHP memiliki izin untuk MEMBACA file.\n";
        $content = file_get_contents($filePath);
        echo "file_get_contents(): Berhasil membaca isi file:\n---\n" . htmlspecialchars($content) . "\n---\n";
    } else {
        echo "is_readable() file: SALAH - PHP TIDAK memiliki izin untuk MEMBACA file ini meskipun file-nya ada.\n";
    }
} else {
    echo "file_exists(): SALAH - PHP melaporkan bahwa file tersebut TIDAK ADA.\n";
}

// Menampilkan error terakhir yang mungkin tidak terlihat
$lastError = error_get_last();
if ($lastError) {
    echo "\n\n--- PHP LAST ERROR ---\n";
    print_r($lastError);
}

echo '</pre>';
?>