<?php
// Pastikan $alamat_website sudah didefinisikan sebelum file ini di-include
// Jika tidak, Anda mungkin perlu menambahkan logic untuk mendefinisikannya di sini.
// Contoh: $alamat_website = "http://localhost/your_site_path/";

$nomor_floating = 1;
// Pastikan $koneksi tersedia di scope ini (karena di-include dari header.php)
if (isset($koneksi)) {
    $floating = mysqli_query($koneksi, "SELECT * FROM floating");
    while ($data_floating = mysqli_fetch_array($floating)) {
        $id_floating = $data_floating['id_floating'];
        $nama_floating = $data_floating['nama_floating'];
        $link_floating = $data_floating['link_floating'];
        $gambar_floating = $data_floating['gambar_floating'];

        // Menentukan URL tujuan
        $href = empty($link_floating) ? $alamat_website . 'rtp' : $link_floating;
        $alt_text = htmlspecialchars($nama_floating);
        $src = htmlspecialchars($alamat_website . 'assets/img/' . $gambar_floating);
        $bottom_position = 80 * $nomor_floating . 'px';

        // Output HTML
        ?>
        <div style="bottom: <?php echo $bottom_position; ?>; left: 5px; opacity: 0.98; position: fixed; z-index: 9999;">
            <a href="<?php echo $href; ?>" rel="noopener" target="_blank">
                <img alt="<?php echo $alt_text; ?>" class="wabutton" src="<?php echo $src; ?>" width="60" height="60">
            </a>
        </div>
        <?php
        $nomor_floating++;
    }
}
?>