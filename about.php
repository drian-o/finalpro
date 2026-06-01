<?php include_once 'header.php'; ?>
<style>
/* CSS Tambahan untuk Styling yang lebih baik */
.about-card {
    background-color: #1f2937;
    border: 1px solid #374151;
    border-radius: 0.75rem;
    box-shadow: 0 4px 6px rgba(0,0,0,0.2);
}
.about-card-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #374151;
}
.about-card-icon {
    font-size: 2.5rem;
    color: #FCD34D;
}
.about-card-title {
    font-size: 1.5rem;
    font-weight: bold;
    color: #FFFFFF;
}
.about-list {
    list-style: none;
    padding-left: 0;
}
.about-list li {
    position: relative;
    padding-left: 1.5rem;
    margin-bottom: 0.5rem;
    color: #9CA3AF;
}
.about-list li::before {
    content: '\2713'; /* Checkmark symbol */
    position: absolute;
    left: 0;
    color: #4ade80;
    font-weight: bold;
}
.about-list-title {
    font-weight: bold;
    color: #FCD34D;
    margin-right: 0.5rem;
}
</style>

<section class="container mx-auto py-3 px-3">
    <div class="flex pb-3 overflow-x-scroll">
        <a aria-label="About Us-tab-button" aria-labelledby="About Us-tab-button" class="text-sm border-b uppercase flex-none px-1 pb-2 mx-2 cursor-pointer transition-all duration-300 ease-in-out hover:lg:border-primary hover:lg:text-inverse font-semibold border-primary" href="about">About Us</a>
    </div>
    
    <div class="w-full lg:w-2/3 lg:mx-auto px-2 mt-6">
        
        <div class="text-center mb-8">
            <h1 class="text-3xl lg:text-4xl font-extrabold text-white">Tentang <span class="text-primary">Kami</span></h1>
            <p class="text-md lg:text-lg text-gray-400 mt-2">Pahami lebih dalam tentang komitmen dan layanan kami.</p>
        </div>

        <div class="about-card p-6 mb-8">
            <div class="about-card-header">
                <i class="mdi mdi-information-outline about-card-icon"></i>
                <h2 class="about-card-title">Selamat Datang</h2>
            </div>
            <p class="mt-4 text-gray-300">
                Situs taruhan online terkemuka di Asia, yang menyediakan beragam produk permainan terbaik. Kami menawarkan pengalaman judi online terbaik dengan berbagai variasi permainan kasino &amp; sportsbook dengan odds paling kompetitif. Kami menawarkan rata-rata 8.000 permainan olahraga yang berbeda setiap bulan dan berbagai kompetisi di seluruh dunia. Total lebih dari 88 permainan kasino dari variasi bakarat, slot, roulette, dan permainan kasino lainnya dapat dimainkan di situs kami.
            </p>
        </div>

        <div class="about-card p-6 mb-8">
            <div class="about-card-header">
                <i class="mdi mdi-lock-outline about-card-icon"></i>
                <h2 class="about-card-title">Keamanan & Integritas</h2>
            </div>
            <p class="mt-4 text-gray-300">
                Integritas produk kami adalah poros fundamental dari pengalaman taruhan online. Kami selalu mengutamakan keamanan tercanggih dan memperbarui semua permainan serta proses kami secara berkala, demi memastikan pengalaman online Anda 100% aman dan adil. Kami tidak akan pernah membagikan ataupun menjual data Anda ke pihak ketiga.
            </p>
            <div class="mt-4">
                <ul class="about-list">
                    <li><span class="about-list-title">Data Terjamin:</span> Seluruh data Anda kami jamin kerahasiaannya. Data Anda tersimpan di server kami yang sudah dilengkapi dengan standar keamanan tinggi.</li>
                    <li><span class="about-list-title">Transaksi Aman:</span> Setiap transaksi yang dilakukan di website kami terjamin dan aman. Anda juga dapat melakukan deposit dan withdraw kapan pun.</li>
                </ul>
            </div>
        </div>

        <div class="about-card p-6 mb-8">
            <div class="about-card-header">
                <i class="mdi mdi-headset about-card-icon"></i>
                <h2 class="about-card-title">Layanan & Komitmen</h2>
            </div>
            <p class="mt-4 text-gray-300">
                Didukung layanan pelanggan 24 jam, yang tersedia 7 hari seminggu. Staf kami yang ramah dan profesional akan memastikan bahwa semua masalah Anda ditangani dengan cepat dan efisien. Kami memprioritaskan sistem pembayaran yang aman dan menjaga kerahasiaan informasi pribadi. Kami mengikuti kebijakan Kenali Pelanggan Anda (KYC) dan Anti-Pencucian Uang (AML) untuk memastikan ketaatan berstandar tertinggi pada hukum dan peraturan. Misi utama kami adalah memberikan pengalaman taruhan online terbaik bagi pemain yang bertanggung jawab.
            </p>
            <p class="mt-4 text-gray-300">
                Silakan hubungi kami melalui Livechat dan Whatsapp dengan saran dan komentar Anda. Kami memiliki beberapa metode pembayaran yang mudah dan aman, demi kenyamanan Anda.
            </p>
        </div>
        
    </div>
</section>

<?php include_once 'footer.php'; ?>