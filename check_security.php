<?php
// check_security.php

/**
 * Fungsi untuk memeriksa apakah sebuah nilai cocok dengan salah satu pola berbahaya.
 *
 * @param mixed $value Nilai yang akan diperiksa (bisa string atau array).
 * @param array $patterns Array berisi pola regex berbahaya.
 * @return bool True jika mencurigakan, false jika tidak.
 */
function is_value_suspicious($value, $patterns) {
    if (is_array($value)) {
        foreach ($value as $item) {
            // Panggil rekursif jika item adalah array (untuk input array multidimensi)
            if (is_value_suspicious($item, $patterns)) {
                return true;
            }
        }
    } elseif (is_string($value)) {
        foreach ($patterns as $pattern_name => $pattern_regex) {
            if (preg_match($pattern_regex, $value)) {
                // Opsional: Log detail temuan untuk analisis lebih lanjut
                // error_log("Peringatan Keamanan: Pola '$pattern_name' cocok pada input. IP: {$_SERVER['REMOTE_ADDR']}, URI: {$_SERVER['REQUEST_URI']}, Input: " . substr($value, 0, 100));
                return true;
            }
        }
    }
    return false;
}

// Daftar pola regex yang dianggap mencurigakan
// Terinspirasi dari pola di .htaccess Anda [cite: 5]
$suspicious_patterns = [
    // SQL Injection
    'SQL_SIMPLE_COMMENTS'        => "/(\-\-|\#|\/\*)/i", // Lebih efektif menangkap --, #, /*
    'SQL_ALWAYS_TRUE_BASIC'      => "/((\%27)|\'|\s)\s*(OR|AND)\s+(TRUE|[0-9a-zA-Z_]+\s*=\s*[\'\"]?[0-9a-zA-Z_]+[\'\"]?)/i", // Menangkap ' OR 1=1, name='name', dll.
    'SQL_UNION_SELECT'           => "/\b(UNION\s+(ALL\s+)?SELECT)\b/i",
    'SQL_COMMON_KEYWORDS'        => "/\b(INSERT\s+INTO|UPDATE\s+.*\s+SET|DELETE\s+FROM|DROP\s+(TABLE|DATABASE)|CONCAT|LOAD_FILE|OUTFILE|XP_CMDSHELL)\b/i", // SELECT .. FROM dihilangkan karena bisa terlalu banyak false positive. Jika butuh, tambahkan sendiri dengan hati-hati.
    'SQL_HEX_ENCODED'            => "/0x[0-9a-fA-F]{4,}/i", // Mencari string hex panjang

    // Cross-Site Scripting (XSS) - Pola Dasar
    'XSS_SCRIPT_TAGS'            => "/<\s*script\b[^>]*>(.*?)<\/\s*script\s*>/is",
    'XSS_EVENT_HANDLERS'         => "/\bon\w+\s*=\s*([\"\'])?(?(1)[^\\1]*?|[^\s>]+)(?(1)\\1|)/i",
    'XSS_JAVASCRIPT_PROTOCOL'    => "/javascript\s*:/i",
    'XSS_IMG_SRC_MALICIOUS'      => "/<img\s+[^>]*src\s*=\s*[\'\"]?\s*javascript:/is", // <img src="javascript:...">
    'XSS_IFRAME_SRC_MALICIOUS'   => "/<iframe\s+[^>]*src\s*=\s*[\'\"]?\s*javascript:/is" // <iframe src="javascript:...">
];

// Array yang akan diperiksa (GET dan POST adalah yang paling umum)
$inputs_to_check = [
    'GET'  => $_GET,
    'POST' => $_POST,
    // 'COOKIE' => $_COOKIE, // Hati-hati dengan false positive jika diaktifkan
];

$suspicion_detected = false;

foreach ($inputs_to_check as $method_name => $input_array) {
    if (is_array($input_array)) {
        // Kita periksa salinan array agar tidak mengganggu array aslinya jika ada pemrosesan lain
        $array_to_scan = $input_array;
        array_walk_recursive($array_to_scan, function($value, $key) use ($suspicious_patterns, &$suspicion_detected) {
            if ($suspicion_detected) return; // Hentikan jika sudah terdeteksi

            // Periksa nilai (value)
            if (is_value_suspicious($value, $suspicious_patterns)) {
                $suspicion_detected = true;
                return;
            }
            // Opsional: Periksa juga kunci (key) jika diperlukan, meskipun jarang
            // if (is_value_suspicious($key, $suspicious_patterns)) {
            //     $suspicion_detected = true;
            //     return;
            // }
        });

        if ($suspicion_detected) {
            break; // Hentikan pemeriksaan jika sudah terdeteksi di salah satu metode (GET/POST)
        }
    }
}

if ($suspicion_detected) {
    // Pastikan tidak ada output yang sudah terkirim agar header() berfungsi
    if (!headers_sent()) {
        // Bersihkan output buffer jika ada yang aktif
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        // Arahkan ke halaman error
        header('Location: error_param.php');
        exit; // Penting: Hentikan eksekusi skrip lebih lanjut
    } else {
        // Jika header sudah terkirim, redirect tidak bisa dilakukan.
        // Ini situasi yang tidak ideal, catat sebagai error kritis.
        error_log("Peringatan Keamanan Kritis: Input mencurigakan terdeteksi NAMUN header sudah terkirim. Tidak dapat mengarahkan ke error_param.php. IP: {$_SERVER['REMOTE_ADDR']}, URI: {$_SERVER['REQUEST_URI']}");
        // Anda bisa memilih untuk menghentikan skrip dengan pesan sederhana,
        // meskipun ini kurang elegan jika halaman sudah setengah terender.
        // die("Permintaan Anda tidak dapat diproses karena alasan keamanan.");
    }
}

// Jika skrip sampai di sini, berarti tidak ada input mencurigakan yang terdeteksi oleh filter ini.
?>