<?php
include_once 'koneksi.php';
include_once 'header.php';

// 1. KEAMANAN & PENGAMBILAN DATA
// ======================================================================

// Cek jika pengguna sudah login, jika tidak, alihkan ke halaman utama
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo '<script>window.location.replace("' . $alamat_website . 'home");</script>';
    exit();
}

// Ambil data penting dari session
$id_anggota_aktif = $_SESSION['id_anggota'];
$username_aktif = $_SESSION['nama_pengguna_anggota']; // Ambil username dari session

// Gunakan prepared statement untuk mengambil data anggota, ini lebih aman dari SQL Injection
// PASTIKAN turnover_amount SUDAH ADA DI TABEL ANDA
$stmt = $koneksi->prepare("SELECT * FROM anggota WHERE id_anggota = ?");
$stmt->bind_param("i", $id_anggota_aktif);
$stmt->execute();
$result = $stmt->get_result();
$data_anggota = $result->fetch_assoc();
$stmt->close();

// Jika data anggota tidak ditemukan di database (misal: sesi tidak valid), logout pengguna
if (!$data_anggota) {
    echo '<script>window.location.replace("' . $alamat_website . 'logout.php");</script>';
    exit();
}

// Ekstrak data anggota ke variabel yang bersih (menggunakan htmlspecialchars untuk keamanan)
$nama_rekening_anggota = htmlspecialchars($data_anggota['nama_rekening_anggota']);
$email_anggota = htmlspecialchars($data_anggota['email_anggota']);
$telepon_anggota = htmlspecialchars($data_anggota['telepon_anggota']);
$username = htmlspecialchars($data_anggota['nama_pengguna_anggota']);
$inisial = strtoupper(substr($nama_rekening_anggota, 0, 1));
$turnover_amount = floatval($data_anggota['turnover_amount']); // Ambil nilai turnover


// --- PENGAMBILAN DATA REFERRAL BONUS ---
$total_referral_bonus_display = 0;
$stmt_get_referral_bonus = $koneksi->prepare("SELECT SUM(bonus) AS total_bonus FROM tb_refferal WHERE user_refferal = ?");
if ($stmt_get_referral_bonus) {
    $stmt_get_referral_bonus->bind_param("s", $username_aktif);
    $stmt_get_referral_bonus->execute();
    $result_referral_bonus = $stmt_get_referral_bonus->get_result();
    if ($data_referral_bonus = $result_referral_bonus->fetch_assoc()) {
        $total_referral_bonus_display = floatval($data_referral_bonus['total_bonus']);
    }
    $stmt_get_referral_bonus->close();
}
// --- AKHIR PENGAMBILAN DATA REFERRAL BONUS ---


// 2. LOGIKA SISTEM VIP
// ======================================================================

/*
 * Konfigurasi Level VIP.
 * Anda bisa dengan mudah menambah atau mengubah level di sini.
 * 'min_days' adalah syarat minimal hari untuk mencapai level tersebut.
 * 'next_level_days' adalah total hari yang dibutuhkan untuk naik ke level berikutnya.
 * '-1' berarti level maksimal.
*/
$vip_levels = [
    1 => ['name' => 'Pemain Baru', 'min_days' => 0,   'next_level_days' => 30,  'badge_url' => 'https://cdn.databerjalan.com/assets/images/store/2024-07-11T06:15:28.250Z_Pemain_Baru_1.png'],
    2 => ['name' => 'Anggota Setia', 'min_days' => 30,  'next_level_days' => 180, 'badge_url' => 'https://cdn.databerjalan.com/assets/images/store/2024-07-11T06:16:34.908Z_Anggota_Setia_1.png'],
    3 => ['name' => 'Veteran',       'min_days' => 180, 'next_level_days' => 365, 'badge_url' => 'https://cdn.databerjalan.com/assets/images/store/2024-07-11T06:16:53.640Z_Veteran_1.png'],
    4 => ['name' => 'Legenda',       'min_days' => 365, 'next_level_days' => -1,  'badge_url' => 'https://cdn.databerjalan.com/assets/images/store/2024-07-11T06:17:10.597Z_Legenda_1.png']
];

// Hitung lama bergabung pengguna dalam hari
try {
    // Pastikan kolom 'tanggal_bergabung' ada untuk menghindari error
    if (!isset($data_anggota['tanggal_bergabung'])) {
        throw new Exception("Kolom 'tanggal_bergabung' tidak ditemukan di tabel anggota.");
    }
    $tanggal_bergabung = new DateTime($data_anggota['tanggal_bergabung']);
    $sekarang = new DateTime();
    $selisih = $sekarang->diff($tanggal_bergabung);
    $lama_bergabung_hari = $selisih->days;
} catch (Exception $e) {
    // Jika ada error (misal: kolom tidak ada), set ke default agar halaman tidak rusak
    error_log("VIP System Error: " . $e->getMessage());
    $lama_bergabung_hari = 0;
}


// Tentukan level VIP pengguna saat ini
$current_level_data = $vip_levels[1]; // Level default
foreach (array_reverse($vip_levels, true) as $level_id => $data) {
    if ($lama_bergabung_hari >= $data['min_days']) {
        $current_level_data = $data;
        $current_level_id = $level_id;
        break;
    }
}

// Hitung progress untuk progress bar
$progress_value = 0;
$progress_max = 100;
$progress_text = "Anda telah mencapai level tertinggi!";

// Jika bukan level maksimal, hitung progress ke level selanjutnya
if ($current_level_data['next_level_days'] > 0) {
    $days_in_current_tier = $lama_bergabung_hari - $current_level_data['min_days'];
    $days_for_next_tier = $current_level_data['next_level_days'] - $current_level_data['min_days'];
    
    // Pastikan tidak ada pembagian dengan nol
    if ($days_for_next_tier > 0) {
        $progress_value = ($days_in_current_tier / $days_for_next_tier) * 100;
    }

    $hari_menuju_level_berikutnya = $current_level_id < count($vip_levels) ? ($vip_levels[$current_level_id + 1]['min_days'] - $lama_bergabung_hari) : 0;
    $next_level_name = $current_level_id < count($vip_levels) ? $vip_levels[$current_level_id + 1]['name'] : '';
    
    if ($current_level_data['next_level_days'] == -1 || $hari_menuju_level_berikutnya <= 0) {
        $progress_text = "Anda telah mencapai level tertinggi!";
    } else {
        $progress_text = "Naik ke level {$next_level_name} dalam {$hari_menuju_level_berikutnya} hari lagi.";
    }
}


// 3. TAMPILAN HTML
// ======================================================================
?>
<section class="container flex mx-auto lg:pt-3 lg:pb-5 lg:mt-5">
    <div class="w-full lg:w-1/3 px-3 hidden lg:block">
        
        <a class="flex px-3 py-4 bg-background-default lg:bg-background-secondary rounded-xl mt-4 drop-shadow-[0_0_5px_rgba(0,0,0,0.6)] lg:drop-shadow-none group transition-all duration-300 ease-in-out lg:hover:bg-black/30" href="<?php echo $alamat_website . 'vip-details'; ?>">
            <figure class="flex-none flex items-center justify-center w-12 md:w-16 h-12 md:h-16">
                <img alt="VIP Level Badge" width="64" height="64" decoding="async" class="w-full" style="color: transparent;" loading="lazy" src="<?php echo htmlspecialchars($current_level_data['badge_url']); ?>">
            </figure>
            <article class="w-full pl-4">
                <p class="text-sm md:text-base group-hover:text-white"><?php echo htmlspecialchars($current_level_data['name']); ?></p>
                <progress class="w-full h-[5px] primary-progress" value="<?php echo $progress_value; ?>" max="<?php echo $progress_max; ?>"></progress>
                <span class="text-xs md:text-sm group-hover:text-white"><?php echo htmlspecialchars($progress_text); ?></span>
            </article>
            <figure class="pl-2 flex items-center">
                <svg width="26" height="26" viewbox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg" size="26">
                    <path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="var(--base)"></path>
                </svg>
            </figure>
        </a>

        <a id="showVoucherClaimCardBtnDesktop" class="flex px-3 py-4 bg-background-default lg:bg-background-secondary rounded-xl mt-4 drop-shadow-[0_0_5px_rgba(0,0,0,0.6)] lg:drop-shadow-none group transition-all duration-300 ease-in-out lg:hover:bg-black/30 cursor-pointer">
            <figure class="flex-none flex items-center justify-center w-12 md:w-16 h-12 md:h-16">
                <i class="fas fa-ticket-alt text-primary text-3xl"></i> 
            </figure>
            <article class="w-full pl-4">
                <p class="text-sm md:text-base group-hover:text-white">Punya Kode Voucher?</p>
                <p class="text-xs md:text-sm group-hover:text-white text-primary">Klaim hadiah Anda di sini!</p>
            </article>
            <figure class="pl-2 flex items-center">
                <svg width="26" height="26" viewbox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg" size="26">
                    <path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="var(--base)"></path>
                </svg>
            </figure>
        </a>

        <a id="claimReferralBonusBtnDesktop" class="flex px-3 py-4 bg-background-default lg:bg-background-secondary rounded-xl mt-4 drop-shadow-[0_0_5px_rgba(0,0,0,0.6)] lg:drop-shadow-none group transition-all duration-300 ease-in-out lg:hover:bg-black/30 cursor-pointer <?php echo ($total_referral_bonus_display <= 0) ? 'pointer-events-none opacity-50' : ''; ?>">
            <figure class="flex-none flex items-center justify-center w-12 md:w-16 h-12 md:h-16">
                <i class="fas fa-hand-holding-usd text-primary text-3xl"></i>
            </figure>
            <article class="w-full pl-4">
                <p class="text-sm md:text-base group-hover:text-white">Balance Referral</p>
                <p class="text-xs md:text-sm group-hover:text-white text-primary">IDR <?php echo number_format($total_referral_bonus_display, 0, ',', '.'); ?></p>
            </article>
            <figure class="pl-2 flex items-center">
                <svg width="26" height="26" viewbox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg" size="26">
                    <path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="var(--base)"></path>
                </svg>
            </figure>
        </a>

        <div class="flex px-3 py-4 bg-background-default lg:bg-background-secondary rounded-xl mt-4 drop-shadow-[0_0_5px_rgba(0,0,0,0.6)] lg:drop-shadow-none group transition-all duration-300 ease-in-out lg:hover:bg-black/30">
            <figure class="flex-none flex items-center justify-center w-12 md:w-16 h-12 md:h-16">
                <i class="fas fa-sync-alt text-primary text-3xl"></i>
            </figure>
            <article class="w-full pl-4">
                <p class="text-sm md:text-base group-hover:text-white">Turnover (TO)</p>
                <p class="text-xs md:text-sm group-hover:text-white text-primary">IDR <?php echo number_format($turnover_amount, 0, ',', '.'); ?></p>
            </article>
        </div>

        <section class="bg-background-secondary rounded-xl mt-4">
            <div class="w-full lg:px-4 pt-3 flex flex-wrap px-4">
                <article class="w-full flex items-center mb-1 lg:mb-3">
                    <svg width="24" height="24" viewbox="0 0 24 24" fill="var(--primary)" xmlns="http://www.w3.org/2000/svg" size="24"><path fill-rule="evenodd" clip-rule="evenodd" d="M2.879 3.879C2 4.757 2 6.172 2 9v6c0 2.828 0 4.243.879 5.121C3.757 21 5.172 21 8 21h10c.93 0 1.395 0 1.776-.102a3 3 0 0 0 2.122-2.122C22 18.395 22 17.93 22 17h-6a3 3 0 1 1 0-6h6V9c0-2.828 0-4.243-.879-5.121C20.243 3 18.828 3 16 3H8c-2.828 0-4.243 0-5.121.879ZM7 7a1 1 0 0 0 0 2h3a1 1 0 1 0 0-2H7Z" fill="var(--primary)"></path><path d="M17 14h-1" stroke="var(--primary)" stroke-width="2" stroke-linecap="round"></path></svg>
                    <span class="text-xs lg:text-sm text-caption px-2">Saldo Akun</span>
                </article>
                <div class="w-full flex lg:gap-x-5">
                    <div class="w-full flex items-center">
                        <section class="w-full flex items-center h-7">
                            <span class="text-sm lg:text-base font-semibold">IDR&nbsp;<?php echo number_format($_SESSION['saldo_anggota'], 0, ',', '.'); ?></span>
                        </section>
                    </div>
                </div>
            </div>
            <div class="flex gap-x-4 px-4 pb-6 mt-5">
                <a class="w-full text-center justify-center py-2 rounded-lg text-primary border border-primary transition-all duration-200 ease-in-out hover:lg:bg-background-tertiary" href="<?php echo $alamat_website . 'withdraw'; ?>">Withdraw</a>
                <a class="w-full text-center justify-center py-2 rounded-lg bg-primary text-white transition-all duration-200 ease-in-out hover:lg:brightness-90" href="<?php echo $alamat_website . 'deposit'; ?>">Deposit</a>
            </div>
        </section>

        <section class="bg-background-secondary rounded-xl mt-4 overflow-hidden">
            <a class="flex justify-between px-4 py-3 hover:lg:bg-background-tertiary transition-all duration-300 ease-in-out bg-background-tertiary" href="<?php echo $alamat_website . 'my-account'; ?>">
                <div class="flex items-center w-[calc(100%-24px)] pr-1">
                    <svg width="24" height="24" viewbox="0 0 24 24" fill="var(--primary)" xmlns="http://www.w3.org/2000/svg" size="24"><path fill-rule="evenodd" clip-rule="evenodd" d="M13.984 2.542c.087.169.109.386.152.82.082.82.123 1.23.295 1.456a1 1 0 0 0 .929.384c.28-.037.6-.298 1.238-.82.337-.277.506-.415.687-.473a1 1 0 0 1 .702.035c.175.076.33.23.637.538l.894.894c.308.308.462.462.538.637a1 1 0 0 1 .035.702c-.058.181-.196.35-.472.687-.523.639-.784.958-.822 1.239a1 1 0 0 0 .385.928c.225.172.636.213 1.457.295.433.043.65.065.82.152a1 1 0 0 1 .47.521c.071.177.071.395.071.831v1.264c0 .436 0 .654-.07.83a1 1 0 0 1-.472.522c-.169.087-.386.109-.82.152-.82.082-1.23.123-1.456.295a1 1 0 0 0-.384.929c.038.28.299.6.821 1.238.276.337.414.505.472.687a1 1 0 0 1-.035.702c-.076.175-.23.329-.538.637l-.894.893c-.308.309-.462.463-.637.538a1 1 0 0 1-.702.035c-.181-.058-.35-.196-.687-.472-.639-.522-.958-.783-1.238-.82a1 1 0 0 0-.929.384c-.172.225-.213.635-.295 1.456-.043.434-.065.651-.152.82a1 1 0 0 1-.521.472c-.177.07-.395.07-.831.07h-1.264c-.436 0-.654 0-.83-.07a1 1 0 0 1-.522-.472c-.087-.169-.109-.386-.152-.82-.082-.82-.123-1.23-.295-1.456a1 1 0 0 0-.928-.384c-.281.037-.6.298-1.239.82-.337.277-.506.415-.687-.473a1 1 0 0 1-.702-.035c-.175-.076-.33-.23-.637.538l-.894-.894c-.308-.308-.462-.462-.538-.637a1 1 0 0 1-.035-.702c.058-.181.196-.35.472-.687.523-.639.784-.958.821-1.239a1 1 0 0 0-.384-.928c-.225-.172-.636-.213-1.457-.295-.433-.043-.65-.065-.82-.152a1 1 0 0 1-.47-.521C2 13.286 2 13.068 2 12.632v-1.264c0-.436 0-.654.07-.83a1 1 0 0 1 .472-.522c.169-.087.386-.109.82-.152.82-.082 1.231-.123 1.456-.295a1 1 0 0 0 .385-.928c-.038-.281-.3-.6-.822-1.24-.276-.337-.414-.505-.472-.687a1 1 0 0 1 .035-.702c.076-.174.23-.329.538-.637l.894-.893c.308-.308.462-.463.637-.538a1 1 0 0 1 .702.035c.181.058.35.196.687.472.639.522.958.783 1.238.821a1 1 0 0 0 .93-.385c.17-.225.212-.635.294-1.456.043-.433.065-.65.152-.82a1 1 0 0 1 .521-.471c.177-.07.395-.07.831-.07h1.264c.436 0 .654 0 .83.07a1 1 0 0 1 .522.472ZM12 16a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" fill="var(--primary)"></path></svg>
                    <span class="text-sm pl-2 text-primary">Akun Saya</span>
                </div>
                <figure class="w-6 flex items-center"><svg width="22" height="22" viewbox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg" size="22"><path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="var(--base)"></path></svg></figure>
            </a>
            <a class="flex justify-between px-4 py-3 hover:lg:bg-background-tertiary transition-all duration-300 ease-in-out" href="<?php echo $alamat_website . 'change-password'; ?>">
                <div class="flex items-center w-[calc(100%-24px)] pr-1">
                    <svg width="24" height="24" viewbox="0 0 24 24" fill="var(--primary)" xmlns="http://www.w3.org/2000/svg" size="24"><path fill-rule="evenodd" clip-rule="evenodd" d="M13.984 2.542c.087.169.109.386.152.82.082.82.123 1.23.295 1.456a1 1 0 0 0 .929.384c.28-.037.6-.298 1.238-.82.337-.277.506-.415.687-.473a1 1 0 0 1 .702.035c.175.076.33.23.637.538l.894.894c.308.308.462.462.538.637a1 1 0 0 1 .035.702c-.058.181-.196.35-.472.687-.523.639-.784.958-.822 1.239a1 1 0 0 0 .385.928c.225.172.636.213 1.457.295.433.043.65.065.82.152a1 1 0 0 1 .47.521c.071.177.071.395.071.831v1.264c0 .436 0 .654-.07.83a1 1 0 0 1-.472.522c-.169.087-.386.109-.82.152-.82.082-1.23.123-1.456.295a1 1 0 0 0-.384.929c.038.28.299.6.821 1.238.276.337.414.505.472.687a1 1 0 0 1-.035.702c-.076.175-.23.329-.538.637l-.894.893c-.308.309-.462.463-.637.538a1 1 0 0 1-.702.035c-.181-.058-.35-.196-.687-.472-.639-.522-.958-.783-1.238-.82a1 1 0 0 0-.929.384c-.172.225-.213.635-.295 1.456-.043.434-.065.651-.152.82a1 1 0 0 1-.521.472c-.177.07-.395.07-.831.07h-1.264c-.436 0-.654 0-.83-.07a1 1 0 0 1-.522-.472c-.087-.169-.109-.386-.152-.82-.082-.82-.123-1.23-.295-1.456a1 1 0 0 0-.928-.384c-.281.037-.6.298-1.239.82-.337.277-.506.415-.687-.473a1 1 0 0 1-.702-.035c-.175-.076-.33-.23-.637.538l-.894-.894c-.308-.308-.462-.462-.538-.637a1 1 0 0 1-.035-.702c.058-.181.196-.35.472-.687.523-.639.784-.958.821-1.239a1 1 0 0 0-.384-.928c-.225-.172-.636-.213-1.457-.295-.433-.043-.65-.065-.82-.152a1 1 0 0 1-.47-.521C2 13.286 2 13.068 2 12.632v-1.264c0-.436 0-.654.07-.83a1 1 0 0 1 .472-.522c.169-.087.386-.109.82-.152.82-.082 1.231-.123 1.456-.295a1 1 0 0 0 .385-.928c-.038-.281-.3-.6-.822-1.24-.276-.337-.414-.505-.472-.687a1 1 0 0 1 .035-.702c.076-.174.23-.329.538-.637l.894-.893c.308-.308.462-.463.637-.538a1 1 0 0 1 .702.035c.181.058.35.196.687.472.639.522.958.783 1.238.821a1 1 0 0 0 .93-.385c.17-.225.212-.635.294-1.456.043-.433.065-.65.152-.82a1 1 0 0 1 .521-.471c.177-.07.395-.07.831-.07h1.264c.436 0 .654 0 .83.07a1 1 0 0 1 .522.472ZM12 16a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" fill="var(--primary)"></path></svg>
                    <span class="text-sm pl-2">Ganti Password</span>
                </div>
                <figure class="w-6 flex items-center"><svg width="22" height="22" viewbox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg" size="22"><path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="var(--base)"></path></svg></figure>
            </a>
            <a class="flex justify-between px-4 py-3 hover:lg:bg-background-tertiary transition-all duration-300 ease-in-out" href="<?php echo $alamat_website . 'logout'; ?>">
                <div class="flex items-center w-[calc(100%-24px)] pr-1">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="var(--primary)" xmlns="http://www.w3.org/2000/svg" size="24"><path d="m2 12-.78-.625-.5.625.5.625L2 12Zm9 1a1 1 0 1 0 0-2v2ZM5.22 6.375l-4 5 1.56 1.25 4-5-1.56-1.25Zm-4 6.25 4 5 1.56-1.25-4-5-1.56 1.25ZM2 13h9v-2H2v2ZM13.342 20.557l.165-.986-.165.986Zm7.597.19.646.764-.646-.763ZM15.014 3.165l-.165-.986.165.986Zm5.925.088.646-.763-.646.763ZM13.507 4.43l1.671-.278-.329-1.973-1.671.279.329 1.972ZM21 9.083v5.834h2V9.083h-2Zm-5.822 10.766-1.671-.278-.329 1.973 1.671.278.329-1.973ZM11 8.132v-.743H9v.743h2Zm0 8.48v-.546H9v.546h2Zm2.507 2.959c-.824-.138-1.35-.227-1.734-.342-.358-.106-.472-.201-.536-.277l-1.526 1.293c.41.484.932.735 1.491.901.532.159 1.203.269 1.976.398l.329-1.973ZM9 16.61c0 .784-.002 1.464.067 2.015.072.578.234 1.135.644 1.619l1.526-1.293c-.064-.075-.14-.203-.185-.574-.05-.398-.052-.932-.052-1.767H9Zm12-1.694c0 1.675-.002 2.823-.123 3.67-.116.82-.32 1.174-.584 1.398l1.293 1.526c.797-.675 1.123-1.593 1.272-2.642.144-1.021.142-2.338.142-3.952h-2Zm-6.15 6.905c1.59.265 2.89.484 3.92.51 1.06.025 2.018-.146 2.816-.821l-1.293-1.526c-.264.223-.646.367-1.474.347-.856-.021-1.99-.207-3.641-.483l-.329 1.973ZM.328-17.671c1.652-.275 2.785-.462 3.64-.483.829-.02 1.21.124 1.475.347l1.293-1.526c-.797-.675-1.757-.846-2.816-.82-1.03.025-2.33.244-3.92.509l.328 1.973ZM23 9.083c0-1.614.002-2.93-.142-3.952-.15-1.049-.476-1.967-1.273-2.642l-1.292 1.526c.264.224.468.577.584 1.397.12.848.123 1.996.123 3.67h2Zm-9.822-6.626c-.773.128-1.444.238-1.976.397-.559.166-1.081.417-1.491.901l1.526 1.293c.064-.076.178-.17.536-.277.384-.115.91-.204 1.734-.342l-.329-1.972ZM11 7.389c0-.835.002-1.369.052-1.767.046-.37.121-.499.185-.574L9.711 3.755c-.41.484-.572 1.04-.644 1.62C8.998 5.924 9 6.604 9 7.388h2Z" fill="var(--primary)"></path></svg>
                    <span class="text-sm pl-2">Logout</span>
                </div>
                <figure class="w-6 flex items-center"><svg width="22" height="22" viewbox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg" size="22"><path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="var(--base)"></path></svg></figure>
            </a>
        </section>
    </div>

   <div class="w-full lg:w-2/3 px-4 md:px-0 pt-6 pb-28 relative overflow-hidden">
        <div class="absolute z-10 -left-20 -right-20 -top-0 bg-background-tertiary h-56 rounded-bl-full rounded-br-full"></div>
        <div class="relative z-20">
            <section class="flex p-3 bg-background-tertiary rounded-xl">
                <div class="flex-none w-12 md:w-16 h-12 md:h-16 flex items-center justify-center rounded-full bg-background-secondary border border-base">
                    <p class="text-2xl md:text-4xl font-bold"><?php echo $inisial; ?></p>
                </div>
                <section class="w-full pl-4 overflow-hidden">
                    <p class="text-sm md:text-base mb-1 truncate font-semibold"><?php echo $nama_rekening_anggota; ?></p>
                    <p class="flex items-center -ml-1 mb-1 text-xs md:text-sm">
                        <svg width="22" height="22" viewBox="0 0 25 25" fill="var(--primary)" xmlns="http://www.w3.org/2000/svg" size="22"><path fill-rule="evenodd" clip-rule="evenodd" d="M3.975 10.394c-.026.594-.026 1.314-.026 2.22 0 2.21 0 3.313.37 4.182a4.63 4.63 0 0 0 2.449 2.449c.868.37 1.972.37 4.181.37h4c2.21 0 3.314 0 4.182-.37a4.63 4.63 0 0 0 2.45-2.45c.368-.868.368-1.972.368-4.18 0-.907 0-1.627-.025-2.221l-7.74 4.3a2.544 2.544 0 0 1-2.47 0l-7.74-4.3Zm.956-3 8.018 4.455 8.019-4.455a4.63 4.63 0 0 0-1.837-1.41c-.868-.37-1.973-.37-4.182-.37h-4c-2.209 0-3.313 0-4.181.37a4.63 4.63 0 0 0-1.837 1.41Z" fill="var(--primary)"></path></svg>
                        <span class="pl-2 truncate"><?php echo $email_anggota; ?></span>
                    </p>
                    <p class="flex items-center -ml-1 text-xs md:text-sm">
                        <svg width="22" height="22" viewBox="0 0 25 25" fill="var(--primary)" xmlns="http://www.w3.org/2000/svg" size="22"><path d="m19.364 14.034 2.469 2.469a1.2 1.2 0 0 1 0 1.696 7.199 7.199 0 0 1-9.41.669l-.167-.125a28.516 28.516 0 0 1-5.703-5.704l-.126-.167a7.199 7.199 0 0 1 .669-9.41 1.2 1.2 0 0 1 1.697 0l2.469 2.47a1.263 1.263 0 0 1 0 1.786L9.524 9.456a1.034 1.034 0 0 0-.194 1.193 11.887 11.887 0 0 0 5.316 5.316c.398.199.879.121 1.194-.194l1.737-1.738a1.263 1.263 0 0 1 1.787 0Z" fill="var(--primary)"></path></svg>
                        <span class="pl-2 truncate"><?php echo $telepon_anggota; ?></span>
                    </p>
                </section>
            </section>
            
            <a class="flex lg:hidden px-3 py-4 bg-background-default rounded-xl mt-4 drop-shadow-[0_0_5px_rgba(0,0,0,0.6)] group" href="<?php echo $alamat_website . 'vip-details'; ?>">
                <figure class="flex-none flex items-center justify-center w-12 md:w-16 h-12 md:h-16">
                    <img alt="VIP Level Badge" width="64" height="64" decoding="async" class="w-full" style="color: transparent;" loading="lazy" src="<?php echo htmlspecialchars($current_level_data['badge_url']); ?>">
                </figure>
                <article class="w-full pl-4">
                    <p class="text-sm md:text-base group-hover:text-white"><?php echo htmlspecialchars($current_level_data['name']); ?></p>
                    <progress class="w-full h-[5px] primary-progress" value="<?php echo $progress_value; ?>" max="<?php echo $progress_max; ?>"></progress>
                    <span class="text-xs md:text-sm group-hover:text-white"><?php echo htmlspecialchars($progress_text); ?></span>
                </article>
                <figure class="pl-2 flex items-center">
                    <svg width="26" height="26" viewbox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg" size="26"><path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="var(--base)"></path></svg>
                </figure>
            </a>

            <a id="showVoucherClaimCardBtnMobile" class="flex lg:hidden px-3 py-4 bg-background-default rounded-xl mt-4 drop-shadow-[0_0_5px_rgba(0,0,0,0.6)] group cursor-pointer">
                <figure class="flex-none flex items-center justify-center w-12 md:w-16 h-12 md:h-16">
                    <i class="fas fa-ticket-alt text-primary text-3xl"></i>
                </figure>
                <article class="w-full pl-4">
                    <p class="text-sm md:text-base group-hover:text-white">Punya Kode Voucher?</p>
                    <p class="text-xs md:text-sm group-hover:text-white text-primary">Klaim hadiah Anda di sini!</p>
                </article>
                <figure class="pl-2 flex items-center">
                    <svg width="26" height="26" viewbox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg" size="26"><path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="var(--base)"></path></svg>
                </figure>
            </a>
            
            <a id="claimReferralBonusBtnMobile" class="flex lg:hidden px-3 py-4 bg-background-default rounded-xl mt-4 drop-shadow-[0_0_5px_rgba(0,0,0,0.6)] group cursor-pointer <?php echo ($total_referral_bonus_display <= 0) ? 'pointer-events-none opacity-50' : ''; ?>">
                <figure class="flex-none flex items-center justify-center w-12 md:w-16 h-12 md:h-16">
                    <i class="fas fa-hand-holding-usd text-primary text-3xl"></i>
                </figure>
                <article class="w-full pl-4">
                    <p class="text-sm md:text-base group-hover:text-white">Balance Referral</p>
                    <p class="text-xs md:text-sm group-hover:text-white text-primary">IDR <?php echo number_format($total_referral_bonus_display, 0, ',', '.'); ?></p>
                </article>
                <figure class="pl-2 flex items-center">
                    <svg width="26" height="26" viewbox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg" size="26">
                        <path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="var(--base)"></path>
                    </svg>
                </figure>
            </a>

            <div class="flex lg:hidden px-3 py-4 bg-background-default rounded-xl mt-4 drop-shadow-[0_0_5px_rgba(0,0,0,0.6)] group">
                <figure class="flex-none flex items-center justify-center w-12 md:w-16 h-12 md:h-16">
                    <i class="fas fa-sync-alt text-primary text-3xl"></i>
                </figure>
                <article class="w-full pl-4">
                    <p class="text-sm md:text-base group-hover:text-white">Turnover (TO)</p>
                    <p class="text-xs md:text-sm group-hover:text-white text-primary">IDR <?php echo number_format($turnover_amount, 0, ',', '.'); ?></p>
                </article>
            </div>
            
            <div class="bg-background-secondary rounded-xl mt-4 p-4 lg:p-6">
                <h2 class="text-lg font-semibold mb-4 border-b border-gray-700 pb-2">Detail Akun Saya</h2>
                <div class="space-y-4 text-sm">
                    <div>
                        <p class="text-gray-400">Username</p>
                        <p class="font-medium"><?php echo $username; ?></p>
                    </div>
                    <div>
                        <p class="text-gray-400">Email</p>
                        <p class="font-medium"><?php echo $email_anggota; ?></p>
                    </div>
                    <div>
                        <p class="text-gray-400">No. Telepon</p>
                        <p class="font-medium"><?php echo $telepon_anggota; ?></p>
                    </div>
                    <div>
                        <p class="text-gray-400">Level VIP</p>
                        <p class="font-medium text-primary"><?php echo htmlspecialchars($current_level_data['name']); ?></p>
                    </div>
                    <div>
                        <p class="text-gray-400">Bergabung Sejak</p>
                        <p class="font-medium"><?php echo $tanggal_bergabung->format('d F Y'); ?> (<?php echo $lama_bergabung_hari; ?> hari)</p>
                    </div>
                </div>
                 <a href="<?php echo $alamat_website . 'change-password'; ?>" class="mt-6 inline-block w-full text-center bg-primary text-white font-semibold py-2 px-4 rounded-lg hover:brightness-90 transition-all duration-200">
                    Ganti Password
                </a>
                                 <a href="<?php echo $alamat_website . 'rekening'; ?>" class="mt-6 inline-block w-full text-center bg-primary text-white font-semibold py-2 px-4 rounded-lg hover:brightness-90 transition-all duration-200">
                    Tambah Rekening
                </a>
                <a href="<?php echo $alamat_website . 'kyc'; ?>" class="mt-6 inline-block w-full text-center bg-primary text-white font-semibold py-2 px-4 rounded-lg hover:brightness-90 transition-all duration-200">
                    Registrasi KYC
                </a>
            </div>
            
        </div>
    </div>
</section>

<?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) : ?>
    <div id="voucherClaimModal" class="welcome-modal-overlay" style="display: none;">
        <div class="welcome-modal-content">
            <button id="closeVoucherClaimModal" class="welcome-close-button">&times;</button>
            
            <div id="voucherInputContainer">
                <h3>Klaim Voucher Anda</h3>
                <p class="game-subtitle">Masukkan kode voucher di bawah ini:</p>
                
                <div class="voucher-form-group">
                    <input type="text" id="voucherCodeInput" placeholder="Masukkan Kode Voucher" class="voucher-input">
                    <button id="claimVoucherBtn" class="claim-voucher-button">Klaim Voucher</button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<style>
    /* CSS Modal Selamat Datang (Reused for Voucher Modal) */
    .welcome-modal-overlay{display:flex;align-items:center;justify-content:center;position:fixed;top:0;left:0;width:100%;height:100%;background-color:rgba(10,10,10,.8);backdrop-filter:blur(5px);z-index:1001}.welcome-modal-content{font-family:'Poppins',sans-serif;background:#1a1d24;color:#e5e7eb;padding:28px;border-radius:12px;border:1px solid rgba(255,255,255,.1);max-width:420px;width:calc(100% - 32px);box-shadow:0 10px 40px rgba(0,0,0,.5);position:relative;text-align:center;transition:all .3s ease}.welcome-modal-content h3{font-size:1.8rem;font-weight:700;margin:0 0 8px;color:#4ade80}.welcome-user-name{font-size:1.2rem;font-weight:500;color:#fff;margin:0 0 20px;padding-bottom:20px;border-bottom:1px solid rgba(255,255,255,.1)}.welcome-text{font-size:1rem;line-height:1.6;color:#9ca3af;margin-bottom:24px}.cs-button{display:block;width:100%;text-align:center;padding:12px 24px;background-color:#4ade80;color:#1a1d24;text-decoration:none;border-radius:8px;font-weight:700;transition:all .2s ease}.cs-button:hover{background-color:#34d399;transform:translateY(-2px)}.cs-button .fa-headset{margin-right:8px}.welcome-close-button{position:absolute;top:10px;right:10px;background:0 0;border:none;font-size:1.8rem;color:#9ca3af;cursor:pointer;transition:color .2s ease}.welcome-close-button:hover{color:#fff}.back-button{background:0 0;border:none;color:#9ca3af;cursor:pointer;margin-top:20px;font-size:.9rem}

    /* Voucher Input Styles (from pengumuman_web.php, adjusted for column layout) */
    #voucherInputContainer { padding: 0 10px; }
    .game-subtitle { color: #9CA3AF; font-size: 0.9rem; margin-top: -5px; margin-bottom: 25px; } /* Reused class for subtitle */
    .voucher-form-group {
        display: flex;
        flex-direction: column; /* THIS IS THE KEY CHANGE: Stacks items vertically */
        gap: 10px;
        margin-top: 20px;
    }
    .voucher-input {
        flex-grow: 1; /* This will make the input take full width */
        padding: 12px;
        border-radius: 8px;
        border: 1px solid #555;
        background-color: #333;
        color: #eee;
        font-size: 1rem;
        width: 100%; /* Ensure input takes full available width */
        box-sizing: border-box; /* Include padding and border in the element's total width and height */
    }
    .voucher-input::placeholder {
        color: #999;
    }
    .claim-voucher-button {
        width: 100%; /* Make button take full width */
        padding: 12px; /* Adjust padding as needed */
        margin-top: 5px; /* Add some space above the button */
        background-color: #4ADE80;
        color: #1a1d24;
        border: none;
        border-radius: 8px;
        font-weight: 700;
        cursor: pointer;
        transition: all .2s ease;
    }
    .claim-voucher-button:hover {
        background-color: #34d399;
    }
    .claim-voucher-button:disabled {
        background-color: #999;
        cursor: not-allowed;
    }
</style>

<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script> 
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tombol pemicu modal
    const showVoucherClaimCardBtnDesktop = document.getElementById('showVoucherClaimCardBtnDesktop');
    const showVoucherClaimCardBtnMobile = document.getElementById('showVoucherClaimCardBtnMobile');
    
    // Elemen modal dan dalamnya
    const voucherClaimModal = document.getElementById('voucherClaimModal');
    const closeVoucherClaimBtn = document.getElementById('closeVoucherClaimModal');
    const voucherCodeInput = document.getElementById('voucherCodeInput');
    const claimVoucherBtn = document.getElementById('claimVoucherBtn');

    // Tombol dan elemen untuk klaim Referral Bonus
    const claimReferralBonusBtnDesktop = document.getElementById('claimReferralBonusBtnDesktop');
    const claimReferralBonusBtnMobile = document.getElementById('claimReferralBonusBtnMobile');
    const totalReferralBonusDisplay = <?php echo $total_referral_bonus_display; ?>; // Ambil nilai dari PHP

    // Fungsi untuk menampilkan modal
    const showModal = () => {
        if (voucherClaimModal) {
            voucherClaimModal.style.display = 'flex'; // Mengubah display untuk menampilkan modal
        }
    };

    // Fungsi untuk menyembunyikan modal dan mereset form
    const hideVoucherClaimModal = () => {
        if (voucherClaimModal) {
            voucherClaimModal.style.display = 'none';
            voucherCodeInput.value = ''; // Clear input on close
            claimVoucherBtn.disabled = false; // Re-enable button
        }
    };

    // Event listeners untuk tombol pemicu voucher
    if (showVoucherClaimCardBtnDesktop) {
        showVoucherClaimCardBtnDesktop.addEventListener('click', showModal);
    }
    if (showVoucherClaimCardBtnMobile) {
        showVoucherClaimCardBtnMobile.addEventListener('click', showModal);
    }

    // Event listener untuk tombol tutup modal voucher
    if (closeVoucherClaimBtn) {
        closeVoucherClaimBtn.addEventListener('click', hideVoucherClaimModal);
    }

    // Tutup modal voucher jika mengklik di luar konten modal
    window.addEventListener('click', (event) => {
        if (event.target == voucherClaimModal) {
            hideVoucherClaimModal();
        }
    });

    // Event listener untuk tombol klaim voucher
    if (claimVoucherBtn) {
        claimVoucherBtn.addEventListener('click', () => {
            const voucherCode = voucherCodeInput.value.trim();
            if (!voucherCode) {
                swal("Peringatan", "Kode voucher tidak boleh kosong!", "warning");
                return;
            }

            claimVoucherBtn.disabled = true; // Disable button to prevent multiple clicks

            fetch('<?php echo $alamat_website; ?>process_voucher.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'voucher_code=' + encodeURIComponent(voucherCode)
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    swal("Berhasil!", data.message, "success")
                        .then(() => {
                            hideVoucherClaimModal(); // Sembunyikan modal
                            location.reload(); // Refresh halaman untuk update saldo
                        });
                } else {
                    swal("Gagal!", data.message, "error")
                        .then(() => {
                            claimVoucherBtn.disabled = false; // Re-enable button on failure
                        });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                swal("Error", "Terjadi kesalahan saat memproses voucher.", "error")
                    .then(() => {
                        claimVoucherBtn.disabled = false; // Re-enable button on error
                    });
            });
        });
    }

    // --- LOGIKA KLAIM REFERRAL BONUS ---
    const handleClaimReferral = () => {
        if (totalReferralBonusDisplay <= 0) {
            swal("Peringatan", "Tidak ada bonus referral yang bisa diklaim.", "warning");
            return;
        }

        swal({
            title: "Konfirmasi Klaim",
            text: `Anda akan mengklaim bonus referral sebesar IDR ${new Intl.NumberFormat('id-ID').format(totalReferralBonusDisplay)}. Lanjutkan?`,
            icon: "info",
            buttons: ["Batal", "Klaim"],
            dangerMode: false,
        })
        .then((willClaim) => {
            if (willClaim) {
                // Disable tombol untuk mencegah double click
                if (claimReferralBonusBtnDesktop) claimReferralBonusBtnDesktop.classList.add('pointer-events-none', 'opacity-50');
                if (claimReferralBonusBtnMobile) claimReferralBonusBtnMobile.classList.add('pointer-events-none', 'opacity-50');

                fetch('<?php echo $alamat_website; ?>process_claim_referral.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json', // Menggunakan JSON untuk request
                    },
                    body: JSON.stringify({}) // Body kosong atau data tambahan jika diperlukan
                })
                .then(response => {
                    if (!response.ok) { // Check for HTTP errors
                        return response.json().then(errorData => {
                            throw new Error(errorData.message || 'Network response was not ok.');
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.status === 'success') {
                        swal("Berhasil!", data.message, "success")
                            .then(() => {
                                location.reload(); // Reload halaman untuk update tampilan saldo dan bonus
                            });
                    } else {
                        swal("Gagal!", data.message, "error")
                            .then(() => {
                                // Re-enable tombol pada kegagalan
                                if (claimReferralBonusBtnDesktop) claimReferralBonusBtnDesktop.classList.remove('pointer-events-none', 'opacity-50');
                                if (claimReferralBonusBtnMobile) claimReferralBonusBtnMobile.classList.remove('pointer-events-none', 'opacity-50');
                            });
                    }
                })
                .catch(error => {
                    console.error('Error klaim referral:', error);
                    swal("Error", `Terjadi kesalahan saat memproses klaim: ${error.message}`, "error")
                        .then(() => {
                            // Re-enable tombol pada error
                            if (claimReferralBonusBtnDesktop) claimReferralBonusBtnDesktop.classList.remove('pointer-events-none', 'opacity-50');
                            if (claimReferralBonusBtnMobile) claimReferralBonusBtnMobile.classList.remove('pointer-events-none', 'opacity-50');
                        });
                });
            }
        });
    };

    if (claimReferralBonusBtnDesktop) {
        claimReferralBonusBtnDesktop.addEventListener('click', handleClaimReferral);
    }
    if (claimReferralBonusBtnMobile) {
        claimReferralBonusBtnMobile.addEventListener('click', handleClaimReferral);
    }
    // --- AKHIR LOGIKA KLAIM REFERRAL BONUS ---
});
</script>

<?php include_once 'footer.php'; ?>