<?php
include_once 'header.php';

// Memeriksa apakah parameter 'id' ada di URL
if (isset($_GET['id'])) {
    $id_promosi = intval($_GET['id']); // Amankan input dari pengguna

    // Query untuk mengambil data promo berdasarkan ID
    $query = "SELECT * FROM promosi WHERE id_promosi = $id_promosi";
    $result = mysqli_query($koneksi, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $data_promosi = mysqli_fetch_assoc($result);
        $judul_promosi = htmlspecialchars($data_promosi['judul_promosi']);
        $deskripsi_promosi = htmlspecialchars($data_promosi['deskripsi_promosi']);
        $gambar_promosi = htmlspecialchars($data_promosi['gambar_promosi']);
?>
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
            <li class="inline-flex items-end pr-1 group">
                <div class="flex items-center">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg" size="17">
                        <path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="var(--base)"></path>
                    </svg>
                    <a class="text-xs pl-1 border-b border-transparent hover:lg:border-primary transition-all duration-200 ease-in-out group-last:text-primary undefined"><?php echo $judul_promosi; ?></a>
                </div>
            </li>
        </ol>
    </nav>
    <div class="flex flex-wrap lg:-mx-3">
        <figure class="w-full lg:w-8/12 lg:px-3 lg:order-1">
            <img alt="<?php echo htmlspecialchars($judul_promosi); ?>" loading="lazy" width="0" height="0" decoding="async" data-nimg="1" class="rounded-lg w-full mb-4" src="<?php echo htmlspecialchars($alamat_website . 'assets/img/' . $gambar_promosi); ?>" style="color: transparent;">
        </figure>
        <p class="w-full lg:w-8/12 lg:order-3 lg:px-3 text-lg font-semibold"><?php echo htmlspecialchars($judul_promosi); ?></p>
        <div class="w-full lg:w-8/12 lg:order-3 lg:px-3 mt-3 flex justify-end">
            <button class="border border-caption flex rounded-full px-2 py-1 lg:hover:bg-white/20 transition-all duration-200 ease-in-out">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M16 7a3 3 0 100-6 3 3 0 000 6zM4 14a3 3 0 100-6 3 3 0 000 6zM16 21a3 3 0 100-6 3 3 0 000 6zM6.59 12.51l6.83 3.98M13.41 5.51L6.59 9.49" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
                <span class="text-xs pl-1">Share</span>
            </button>
        </div>
        <div class="w-full lg:w-8/12 lg:order-4 lg:px-3 mt-5 mb-20">
            <article class="admin-custom break-words">
                <p><br></p>
                <div>
                    <strong><?php echo htmlspecialchars_decode($deskripsi_promosi); ?></strong>
                </div>
            </article>
        </div>
    </div>
</section>
<?php
    } else {
        echo "<p>Promotion not found.</p>";
    }
} else {
    echo "<p>Invalid promotion ID.</p>";
}

include_once 'footer.php';
?>
