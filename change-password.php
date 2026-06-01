<?php
include_once 'koneksi.php';
include_once 'header.php';

// Keamanan: Pastikan pengguna sudah login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo '<script>window.location.replace("' . $alamat_website . 'home");</script>';
    exit();
}

// Ambil data anggota untuk ditampilkan di sidebar (logika VIP disalin dari my-account.php)
$id_anggota_aktif = $_SESSION['id_anggota'];
$stmt = $koneksi->prepare("SELECT tanggal_bergabung FROM anggota WHERE id_anggota = ?");
$stmt->bind_param("i", $id_anggota_aktif);
$stmt->execute();
$result = $stmt->get_result();
$data_anggota = $result->fetch_assoc();
$stmt->close();

$vip_levels = [
    1 => ['name' => 'Pemain Baru', 'badge_url' => 'https://cdn.databerjalan.com/assets/images/store/2024-07-11T06:15:28.250Z_Pemain_Baru_1.png', 'min_days' => 0],
    2 => ['name' => 'Anggota Setia', 'badge_url' => 'https://cdn.databerjalan.com/assets/images/store/2024-07-11T06:16:34.908Z_Anggota_Setia_1.png', 'min_days' => 30],
    3 => ['name' => 'Veteran', 'badge_url' => 'https://cdn.databerjalan.com/assets/images/store/2024-07-11T06:16:53.640Z_Veteran_1.png', 'min_days' => 180],
    4 => ['name' => 'Legenda', 'badge_url' => 'https://cdn.databerjalan.com/assets/images/store/2024-07-11T06:17:10.597Z_Legenda_1.png', 'min_days' => 365]
];
$current_level_data = $vip_levels[1];

if ($data_anggota && isset($data_anggota['tanggal_bergabung'])) {
    try {
        $tanggal_bergabung = new DateTime($data_anggota['tanggal_bergabung']);
        $sekarang = new DateTime();
        $lama_bergabung_hari = $sekarang->diff($tanggal_bergabung)->days;
        foreach (array_reverse($vip_levels, true) as $level_id => $data) {
            if ($lama_bergabung_hari >= $data['min_days']) {
                $current_level_data = $data;
                break;
            }
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
    }
}
?>

<section class="container flex mx-auto lg:pt-3 lg:pb-5 lg:mt-5">
    <div class="w-full lg:w-1/3 px-3 hidden lg:block">
        <a class="flex items-center px-3 py-4 bg-background-default lg:bg-background-secondary rounded-xl mt-4 drop-shadow-[0_0_5px_rgba(0,0,0,0.6)] lg:drop-shadow-none" href="<?php echo $alamat_website . 'vip-details'; ?>">
            <figure class="flex flex-none items-center justify-center w-12 md:w-16 h-12 md:h-16">
                <img alt="VIP Level Badge" width="64" height="64" class="w-full" src="<?php echo htmlspecialchars($current_level_data['badge_url']); ?>">
            </figure>
            <article class="w-full pl-4">
                <p class="text-sm md:text-base"><?php echo htmlspecialchars($current_level_data['name']); ?></p>
            </article>
            <figure class="pl-2 flex items-center">
                <svg width="26" height="26" viewbox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg" size="26"><path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="var(--base)"></path></svg>
            </figure>
        </a>
        <section class="bg-background-secondary rounded-xl mt-4">
            <div class="w-full lg:px-4 pt-3 flex flex-wrap px-4">
                <article class="w-full flex items-center mb-1 lg:mb-3">
                    <svg width="24" height="24" viewbox="0 0 24 24" fill="var(--primary)" xmlns="http://www.w3.org/2000/svg" size="24"><path fill-rule="evenodd" clip-rule="evenodd" d="M2.879 3.879C2 4.757 2 6.172 2 9v6c0 2.828 0 4.243.879 5.121C3.757 21 5.172 21 8 21h10c.93 0 1.395 0 1.776-.102a3 3 0 0 0 2.122-2.122C22 18.395 22 17.93 22 17h-6a3 3 0 1 1 0-6h6V9c0-2.828 0-4.243-.879-5.121C20.243 3 18.828 3 16 3H8c-2.828 0-4.243 0-5.121.879ZM7 7a1 1 0 0 0 0 2h3a1 1 0 1 0 0-2H7Z" fill="var(--primary)"></path><path d="M17 14h-1" stroke="var(--primary)" stroke-width="2" stroke-linecap="round"></path></svg>
                    <span class="text-xs lg:text-sm text-caption px-2">Saldo Akun</span>
                </article>
                <div class="w-full flex items-center h-7">
                    <span class="text-sm lg:text-base font-semibold">IDR&nbsp;<?php echo number_format($_SESSION['saldo_anggota'], 0, ',', '.'); ?></span>
                </div>
            </div>
            <div class="flex gap-x-4 px-4 pb-6 mt-5">
                <a class="w-full text-center justify-center py-2 rounded-lg text-primary border border-primary" href="<?php echo $alamat_website . 'withdraw'; ?>">Withdraw</a>
                <a class="w-full text-center justify-center py-2 rounded-lg bg-primary text-white" href="<?php echo $alamat_website . 'deposit'; ?>">Deposit</a>
            </div>
        </section>
        <section class="bg-background-secondary rounded-xl mt-4 overflow-hidden">
            <a class="flex justify-between px-4 py-3 hover:lg:bg-background-tertiary" href="<?php echo $alamat_website . 'my-account'; ?>">
                <div class="flex items-center"><svg width="24" height="24" viewbox="0 0 24 24" fill="var(--primary)" xmlns="http://www.w3.org/2000/svg" size="24"><path fill-rule="evenodd" clip-rule="evenodd" d="M13.984 2.542c...Z" fill="var(--primary)"></path></svg><span class="text-sm pl-2">Akun Saya</span></div>
                <figure class="w-6 flex items-center"><svg width="22" height="22" viewbox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg" size="22"><path d="m15 12...Z" fill="var(--base)"></path></svg></figure>
            </a>
            <a class="flex justify-between px-4 py-3 bg-background-tertiary" href="<?php echo $alamat_website . 'change-password'; ?>">
                <div class="flex items-center"><svg width="24" height="24" viewbox="0 0 24 24" fill="var(--primary)" xmlns="http://www.w3.org/2000/svg" size="24"><path d="M4 13c...v-2Z" stroke="var(--primary)" stroke-width="2"></path><path d="M16 8V...v1" stroke="var(--primary)" stroke-width="2" stroke-linecap="round"></path><circle cx="12" cy="15" r="2" fill="var(--primary)"></circle></svg><span class="text-sm pl-2 text-primary">Ganti Password</span></div>
                <figure class="w-6 flex items-center"><svg width="22" height="22" viewbox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg" size="22"><path d="m15 12...Z" fill="var(--base)"></path></svg></figure>
            </a>
        </section>
        <a href="<?php echo $alamat_website . 'logout.php'; ?>" class="block mt-4">
            <div class="bg-background-secondary rounded-xl overflow-hidden">
                <section class="flex justify-between px-4 py-3 cursor-pointer hover:lg:bg-background-tertiary"><div class="flex items-center"><svg width="24" height="24" viewbox="0 0 24 24" fill="var(--primary)" xmlns="http://www.w3.org/2000/svg" size="24"><path d="m2 12...h2Z" fill="var(--primary)"></path></svg><span class="text-sm pl-2">Logout</span></div></section>
            </div>
        </a>
    </div>

    <div class="w-full lg:w-2/3 lg:px-5">
        <section class="pt-5 pb-3 lg:pb-10 px-4 bg-background-tertiary lg:rounded-xl min-h-[82vh] lg:min-h-min">
            <div class="lg:w-3/4 mx-auto">
                <div class="text-center mb-8">
                    <h1 class="text-2xl font-bold">Ganti Password</h1>
                    <p class="text-gray-400">Untuk keamanan, ganti password Anda secara berkala.</p>
                </div>

                <?php
                if (isset($_SESSION['flash_message'])) {
                    $flash = $_SESSION['flash_message'];
                    unset($_SESSION['flash_message']);
                    $alert_class = $flash['type'] === 'success' ? 'background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb;' : 'background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;';
                    echo '<div style="padding: 1rem; margin-bottom: 1.5rem; border-radius: 0.5rem; ' . $alert_class . '">' . htmlspecialchars($flash['message']) . '</div>';
                }
                ?>

                <form method="POST" action="<?php echo $alamat_website . 'process_change_password.php'; ?>">
                    <div class="space-y-6">
                        <div>
                            <label for="old_password" class="text-sm font-medium text-gray-300">Password Lama</label>
                            <input id="old_password" type="password" name="old_password" placeholder="Masukkan password lama Anda" class="mt-2 p-3 text-sm w-full rounded-lg bg-background-default border border-separator focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none" required>
                        </div>
                        <div>
                            <label for="new_password" class="text-sm font-medium text-gray-300">Password Baru</label>
                            <input id="new_password" type="password" name="new_password" placeholder="6-14 karakter" class="mt-2 p-3 text-sm w-full rounded-lg bg-background-default border border-separator focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none" required minlength="6" maxlength="14">
                        </div>
                        <div>
                            <label for="confirm_new_password" class="text-sm font-medium text-gray-300">Konfirmasi Password Baru</label>
                            <input id="confirm_new_password" type="password" name="confirm_new_password" placeholder="Ulangi password baru Anda" class="mt-2 p-3 text-sm w-full rounded-lg bg-background-default border border-separator focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none" required minlength="6" maxlength="14">
                        </div>
                    </div>
                    <div class="flex justify-center mt-8">
                        <button type="submit" class="bg-primary text-white lg:hover:brightness-95 rounded-xl text-sm lg:text-base font-semibold w-full lg:w-1/2 justify-center py-3">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </section>
    </div>
</section>

<?php include_once 'footer.php'; ?>
