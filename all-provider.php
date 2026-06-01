<?php
// Konten ini akan di-include di index.php
// Pastikan variabel $koneksi dan $alamat_website sudah didefinisikan sebelumnya.

$query_all_providers = "SELECT provider_name, provider_type, provider_image FROM srg_provider WHERE provider_status = 'active' ORDER BY urutan ASC";
$result_all_providers = mysqli_query($koneksi, $query_all_providers);

if ($result_all_providers && mysqli_num_rows($result_all_providers) > 0) {
    $count = 0;
    while ($row_provider = mysqli_fetch_assoc($result_all_providers)) {
        $nama_provider = $row_provider['provider_name'];
        $gambar_provider = $row_provider['provider_image'];
        $provider_type = $row_provider['provider_type'];

        // Logika untuk badge (contoh)
        $is_popular = ($count < 3);
        $is_new = ($count === 4);
?>
<a href="<?php echo htmlspecialchars($alamat_website . $provider_type); ?>" class="inline-block flex-none w-[calc(100%/4.5)] px-[5px] lg:w-1/6 lg:px-2">
    <div class="provider-card-new">
        <div class="provider-bg-image-blur" style="background-image: url('<?php echo htmlspecialchars($gambar_provider); ?>');"></div>
        <div class="provider-icon-wrapper">
            <img alt="<?php echo htmlspecialchars($nama_provider); ?>" src="<?php echo htmlspecialchars($gambar_provider); ?>" class="provider-icon">
        </div>
        <?php if ($is_popular) : ?>
            <div class="provider-badge popular-badge">
                <i class="mdi mdi-fire text-white animate-pulse-fast"></i>
                <span>Populer</span>
            </div>
        <?php endif; ?>
        <?php if ($is_new) : ?>
            <div class="provider-badge new-badge">
                <i class="mdi mdi-star text-white animate-pulse-fast"></i>
                <span>Baru</span>
            </div>
        <?php endif; ?>
    </div>
    <p class="provider-title mt-1 text-center truncate"><?php echo htmlspecialchars($nama_provider); ?></p>
</a>
<?php
        $count++;
    }
}
?>
