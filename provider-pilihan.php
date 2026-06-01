<?php
// Pastikan koneksi ke database sudah ada
// include_once 'koneksi.php';

// Semua konten provider-pilihan.php ditempatkan di sini
?>
<div class="w-full px-3 mt-3 lg:mt-5 order-2">
    <div class="flex justify-between items-center mb-4">
        <p class="md:text-lg font-medium text-white">Provider Pilihan</p>
        <button id="toggle-providers-button" class="bg-gray-800 px-3 py-2 rounded-lg flex items-center hover:bg-gray-700 transition-all duration-300">
            <span id="toggle-text" class="text-xs pr-1 text-primary">Tampilkan Semua Provider</span>
            <svg id="toggle-icon" width="18" height="18" viewBox="0 0 24 24" fill="var(--primary)" size="18" style="transition: transform 0.3s ease;">
                <path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="var(--primary)"></path>
            </svg>
        </button>
    </div>

    <div id="provider-container-wrapper" class="relative overflow-hidden" style="max-height: 120px; transition: max-height 0.5s ease-in-out;">
        <div id="provider-grid-container" class="flex flex-wrap -mx-[5px] lg:-mx-2 pb-2">
            <?php
            // Mengambil semua provider yang aktif dari database
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
        </div>
    </div>
</div>

<style>
/* CSS Tambahan & Pembaruan */
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none; /* IE and Edge */
    scrollbar-width: none; /* Firefox */
}
@keyframes pulse-fast {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.2); }
}
.provider-card-new {
    position: relative;
    width: 100%;
    padding-top: 100%;
    border-radius: 0.75rem;
    overflow: hidden;
    background-color: #213B53;
    border: 1px solid #324F6C;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}
.provider-card-new:hover {
    transform: translateY(-5px) scale(1.05);
    border-color: var(--primary);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.4);
}
.provider-bg-image-blur {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-size: cover;
    background-position: center;
    filter: blur(15px) brightness(0.6);
    z-index: 1;
    transition: filter 0.3s ease;
}
.provider-card-new:hover .provider-bg-image-blur {
    filter: blur(5px) brightness(0.8);
}
.provider-icon-wrapper {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 2;
}
.provider-icon {
    max-width: 60%;
    max-height: 60%;
    object-fit: contain;
    transition: transform 0.3s ease;
}
.provider-card-new:hover .provider-icon {
    transform: scale(1.1);
}
.provider-title {
    font-size: 0.8rem;
    font-weight: 500;
    color: #E0E0E0;
    margin-top: 0.5rem;
}
.provider-badge {
    position: absolute;
    top: 0.5rem;
    left: 0.5rem;
    padding: 0.2rem 0.5rem;
    border-radius: 9999px;
    font-size: 0.7rem;
    font-weight: bold;
    color: white;
    z-index: 3;
    display: flex;
    align-items: center;
}
.provider-badge i {
    font-size: 0.8rem;
    margin-right: 0.2rem;
}
.popular-badge {
    background-color: #ef4444; /* Merah untuk populer */
}
.new-badge {
    background-color: #3b82f6; /* Biru untuk baru */
}
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleButton = document.getElementById('toggle-providers-button');
        const toggleText = document.getElementById('toggle-text');
        const toggleIcon = document.getElementById('toggle-icon');
        const providerContainerWrapper = document.getElementById('provider-container-wrapper');
        const providerGridContainer = document.getElementById('provider-grid-container');

        toggleButton.addEventListener('click', function() {
            const isExpanded = providerContainerWrapper.style.maxHeight !== '120px';

            if (isExpanded) {
                // Sembunyikan provider
                providerContainerWrapper.style.maxHeight = '120px';
                toggleText.textContent = 'Tampilkan Semua Provider';
                toggleIcon.style.transform = 'rotate(0deg)';
            } else {
                // Tampilkan semua provider dengan animasi
                const scrollHeight = providerGridContainer.scrollHeight;
                providerContainerWrapper.style.maxHeight = scrollHeight + 'px';

                toggleText.textContent = 'Tutup Semua Provider';
                toggleIcon.style.transform = 'rotate(180deg)';
            }
        });
    });
</script>