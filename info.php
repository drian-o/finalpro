<?php
// --- FILE INCLUSIONS AND INITIALIZATION ---
include_once 'koneksi.php';
include_once 'header.php';

// Set top padding for the main content area to prevent overlap with the fixed header.
$main_content_top_padding = '10px';

// Asumsi $isi_1_teks_berjalan_web, $isi_2_teks_berjalan_web, $isi_3_teks_berjalan_web
// didefinisikan di koneksi.php atau file konfigurasi lain yang disertakan
// sebelum index.php.
?>
<!DOCTYPE html>
<html lang="en" class="notranslate __className_7df6af" translate="no" data-theme="light" style="--primary: <?php echo htmlspecialchars($colors['bg_1_web']); ?>;">

<head>
    <meta name="google-site-verification" content="1N79qdZhpfWCQXVf64xBWlGMrWHP7EVkRB745MaR0BU" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;700&family=Inter:wght@400;700&display=swap" rel="stylesheet">

</head>
    <?php
    include_once 'pengumuman_web.php';
    include_once 'footer.php';
    ?>


    <script async custom-element="amp-iframe" src="https://cdn.ampproject.org/v0/amp-iframe-0.1.js"></script>
    <script src="_next/static/chunks/webpack-e30d72a36c0ae6d3.js" async=""></script>
</body>
</html>