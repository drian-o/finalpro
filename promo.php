<?php
include_once 'header.php';
$saldo_anggota = isset($_SESSION['saldo_anggota']) ? $_SESSION['saldo_anggota'] : 0;
$isLoggedIn = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;

// Query untuk mengambil data promosi
$promosi_query = mysqli_query($koneksi, "SELECT * FROM promosi ORDER BY id_promosi DESC");
if (!$promosi_query) {
    die('Error: ' . mysqli_error($koneksi));
}
?>

<style>
.promo-card {
    background-color: #1f2937;
    border-radius: 0.75rem;
    overflow: hidden;
    position: relative;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.4);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.promo-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.6);
}
.promo-image-container {
    position: relative;
    width: 100%;
    padding-top: 56.25%; /* 16:9 Aspect Ratio */
    overflow: hidden;
}
.promo-image-container img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}
.promo-card:hover .promo-image-container img {
    transform: scale(1.05);
}
.promo-badge {
    position: absolute;
    top: 1rem;
    left: 1rem;
    background-color: #FCD34D;
    color: #1f2937;
    font-size: 0.75rem;
    font-weight: bold;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    z-index: 10;
}
.promo-card-body {
    padding: 1rem;
    color: white;
}
.promo-title {
    font-size: 1rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    min-height: 2.5rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.promo-category {
    font-size: 0.75rem;
    color: #9CA3AF;
}
</style>

<section class="container mx-auto pt-3 pb-10 lg:pb-12 px-3">
    <nav class="flex mb-1 lg:mb-2">
        <ol class="flex items-center pb-1 overflow-x-scroll whitespace-nowrap opacity-scroll">
            <li class="inline-flex items-end pr-1">
                <a class="text-xs border-b border-transparent hover:lg:border-primary transition-all duration-200 ease-in-out undefined" href="<?php echo $alamat_website . 'home'; ?>">Home</a>
            </li>
            <li class="inline-flex items-end pr-1 group">
                <div class="flex items-center">
                    <svg width="17" height="17" viewbox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg" size="17">
                        <path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="var(--base)"></path>
                    </svg><a class="text-xs pl-1 border-b border-transparent hover:lg:border-primary transition-all duration-200 ease-in-out group-last:text-primary undefined" href="<?php echo $alamat_website . 'promo'; ?>">Promo</a>
                </div>
            </li>
        </ol>
    </nav>
    <h3 class="md:text-lg font-medium w-full text-white">Promo dan Bonus Eksklusif</h3>
    <div class="flex flex-wrap -mx-2 lg:-mx-3 mt-4">
        <?php
        while ($data_promosi = mysqli_fetch_array($promosi_query)) {
            $id_promosi = $data_promosi['id_promosi'];
            $gambar_promosi = $data_promosi['gambar_promosi'];
            $judul_promosi = $data_promosi['judul_promosi'];
            $kategori_promosi = $data_promosi['kategori_promosi'];
            $link_kategori_promosi = strtolower(str_replace(' ', '-', $kategori_promosi));
        ?>
            <div class="w-1/2 md:w-1/3 lg:w-1/4 px-[6px] lg:px-3 mb-3 lg:mb-6">
                <a class="promo-card block" href="<?php echo $alamat_website; ?>details-promo?id=<?php echo $id_promosi; ?>">
                    <div class="promo-badge">Ongoing Promo</div>
                    <div class="promo-image-container">
                        <img alt="Promo <?php echo htmlspecialchars($judul_promosi); ?>" loading="lazy" src="<?php echo htmlspecialchars($alamat_website . 'assets/img/' . $gambar_promosi); ?>">
                    </div>
                    <div class="promo-card-body">
                        <h4 class="promo-title"><?php echo htmlspecialchars($judul_promosi); ?></h4>
                        <p class="promo-category"><?php echo htmlspecialchars($kategori_promosi); ?></p>
                    </div>
                </a>
            </div>
        <?php
        }
        ?>
    </div>
</section>

<?php include_once 'footer.php'; ?>