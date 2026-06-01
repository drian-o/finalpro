<?php
include_once 'koneksi.php';
include_once 'header.php';

// 1. KEAMANAN & PENGAMBILAN DATA
// ======================================================================

// Cek jika pengguna sudah login, jika tidak, alihkan ke halaman utama
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo '<script>window.location.replace("' . $alamat_website . 'auth-login.php");</script>';
    exit();
}

// Ambil data penting dari session
$id_anggota_aktif = $_SESSION['id_anggota'];

// Gunakan prepared statement untuk mengambil data anggota
$stmt = $koneksi->prepare("SELECT tanggal_bergabung FROM anggota WHERE id_anggota = ?");
$stmt->bind_param("i", $id_anggota_aktif);
$stmt->execute();
$result = $stmt->get_result();
$data_anggota = $result->fetch_assoc();
$stmt->close();

if (!$data_anggota) {
    // Jika data anggota tidak ditemukan, logout untuk keamanan
    echo '<script>window.location.replace("' . $alamat_website . 'logout.php");</script>';
    exit();
}

// 2. LOGIKA & KONFIGURASI SISTEM VIP
// ======================================================================

/*
 * Konfigurasi Pusat untuk Level VIP.
 * Anda bisa menambahkan 'benefits' (keuntungan) untuk setiap level.
*/
$vip_levels = [
    1 => [
        'name' => 'Pemain Baru',
        'min_days' => 0,
        'badge_url' => '/upload/vip/PemainBaru.png',
        'description' => 'Selamat datang! Nikmati bonus pendaftaran dan mulailah petualangan Anda bersama kami.'
    ],
    2 => [
        'name' => 'Anggota Setia',
        'min_days' => 30,
        'badge_url' => '/upload/vip/AnggotaSetia.png',
        'description' => 'Terima kasih atas kesetiaan Anda. Dapatkan akses ke promosi eksklusif dan bonus loyalitas bulanan.'
    ],
    3 => [
        'name' => 'Veteran',
        'min_days' => 180,
        'badge_url' => '/upload/vip/Veteran.png',
        'description' => 'Anda adalah bagian penting dari kami. Nikmati prioritas layanan pelanggan dan bonus cashback yang lebih besar.'
    ],
    4 => [
        'name' => 'Legenda',
        'min_days' => 365,
        'badge_url' => '/upload/vip/Legenda.png',
        'description' => 'Status tertinggi yang bisa dicapai. Dapatkan undangan ke event spesial, hadiah kejutan, dan manajer akun pribadi.'
    ]
];

// Hitung lama bergabung pengguna dalam hari
$lama_bergabung_hari = 0;
if (isset($data_anggota['tanggal_bergabung'])) {
    try {
        $tanggal_bergabung = new DateTime($data_anggota['tanggal_bergabung']);
        $sekarang = new DateTime();
        $selisih = $sekarang->diff($tanggal_bergabung);
        $lama_bergabung_hari = $selisih->days;
    } catch (Exception $e) {
        error_log("VIP Detail Page Error: " . $e->getMessage());
    }
}

// Tentukan level VIP pengguna saat ini
$current_level_id = 1; // Level default
foreach (array_reverse($vip_levels, true) as $level_id => $data) {
    if ($lama_bergabung_hari >= $data['min_days']) {
        $current_level_id = $level_id;
        break;
    }
}

// 3. TAMPILAN HTML
// ======================================================================
?>

<style>
/* Animasi glowing untuk level aktif */
@keyframes glowing {
  0% { box-shadow: 0 0 5px rgba(255, 255, 255, 0.3); }
  50% { box-shadow: 0 0 20px rgba(255, 255, 255, 0.8), 0 0 10px rgba(255, 255, 255, 0.5); }
  100% { box-shadow: 0 0 5px rgba(255, 255, 255, 0.3); }
}

.vip-card-current {
    position: relative;
    border-color: #FCD34D !important;
    background-color: #374151 !important;
    animation: glowing 2s infinite;
}

.progress-bar-container {
    height: 8px;
    background-color: #4B5563;
    border-radius: 9999px;
    overflow: hidden;
}

.progress-bar {
    height: 100%;
    transition: width 0.5s ease-in-out;
    border-radius: 9999px;
}

.progress-color-green { background-color: #10B981; }
.progress-color-yellow { background-color: #FBBF24; }
.progress-color-red { background-color: #EF4444; }

</style>

<section class="container mx-auto py-8 px-4">
    <div class="max-w-4xl mx-auto">
        
        <div class="text-center mb-10">
            <h1 class="text-3xl lg:text-4xl font-extrabold text-white leading-tight">Jejak <span class="text-primary">VIP</span> Anda</h1>
            <p class="text-md lg:text-lg text-gray-400 mt-2">Lihat keuntungan dari setiap level dan perjalanan Anda mencapai puncak.</p>
        </div>

        <div class="space-y-6">
            <?php foreach ($vip_levels as $level_id => $level_data) : 
                
                // Tentukan status setiap level untuk pengguna saat ini
                $is_current_level = ($level_id == $current_level_id);
                $is_unlocked = ($lama_bergabung_hari >= $level_data['min_days']);
                $is_locked = !$is_unlocked;

                // Tentukan gaya CSS berdasarkan status
                $card_class = 'bg-background-secondary border-gray-700'; // Default
                if ($is_current_level) {
                    $card_class = 'vip-card-current border-primary';
                } elseif ($is_unlocked && !$is_current_level) {
                    $card_class = 'bg-background-tertiary border-green-500';
                } elseif ($is_locked) {
                    $card_class = 'bg-gray-800/50 border-dashed border-gray-700 opacity-60';
                }
            ?>
                <div class="relative border-2 <?php echo $card_class; ?> rounded-xl p-6 transition-all duration-300 ease-in-out">
                    
                    <?php if ($is_current_level) : ?>
                        <span class="absolute top-0 right-0 -mt-3 mr-4 bg-primary text-white text-xs font-bold px-3 py-1 rounded-full shadow-md">Level Anda</span>
                    <?php endif; ?>

                    <div class="flex flex-col md:flex-row items-center gap-6">
                        <div class="flex-shrink-0">
                            <img src="<?php echo htmlspecialchars($level_data['badge_url']); ?>" alt="Badge <?php echo htmlspecialchars($level_data['name']); ?>" class="w-24 h-24 md:w-28 md:h-28">
                        </div>

                        <div class="flex-grow text-center md:text-left">
                            <h2 class="text-2xl font-bold text-white"><?php echo htmlspecialchars($level_data['name']); ?></h2>
                            <p class="text-sm font-medium text-gray-400 mt-1">Syarat: Minimal <?php echo $level_data['min_days']; ?> hari bergabung</p>
                            <p class="text-gray-300 mt-3"><?php echo htmlspecialchars($level_data['description']); ?></p>
                        </div>
                        
                        <div class="flex-shrink-0 w-full md:w-auto text-center mt-4 md:mt-0">
                            <?php if ($is_unlocked) : ?>
                                <div class="inline-flex items-center gap-2 bg-green-500/20 text-green-300 font-semibold px-4 py-2 rounded-full">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                    <span>Tercapai</span>
                                </div>
                            <?php else : 
                                $days_needed = $level_data['min_days'] - $lama_bergabung_hari;    
                                $progress_percent = min(100, ($lama_bergabung_hari / $level_data['min_days']) * 100);
                                $progress_color_class = ($progress_percent >= 75) ? 'progress-color-green' : (($progress_percent >= 50) ? 'progress-color-yellow' : 'progress-color-red');
                            ?>
                                <div class="flex flex-col items-center gap-2">
                                    <div class="inline-flex items-center gap-2 bg-gray-600/50 text-gray-300 font-semibold px-4 py-2 rounded-full">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd" /></svg>
                                        <span>Terkunci</span>
                                    </div>
                                    <p class="text-sm font-semibold text-gray-400 mt-2"><?php echo $days_needed; ?> hari lagi</p>
                                    <div class="progress-bar-container w-full md:w-32 mt-1">
                                        <div class="progress-bar <?php echo $progress_color_class; ?>" style="width: <?php echo $progress_percent; ?>%;"></div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-12">
            <a href="<?php echo $alamat_website . 'my-account'; ?>" class="bg-gray-700 hover:bg-gray-600 text-white font-medium py-3 px-8 rounded-full transition-colors duration-300">
                &larr; Kembali ke Akun Saya
            </a>
        </div>

    </div>
</section>

<?php include_once 'footer.php'; ?>