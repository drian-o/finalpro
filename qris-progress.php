<?php
include_once 'header.php'; 

// Memeriksa flag di sesi
if (empty($_SESSION['valid_navigation'])) {
    // Jika flag tidak ada, arahkan ke halaman error dengan JavaScript
    echo '
    <script>
        window.location.replace("home");
    </script>
    ';
    exit();
}

// Menghapus flag setelah validasi
unset($_SESSION['valid_navigation']);
?>
<div id="notification" class="fixed z-[9999] px-4 pt-3 pb-5 top-3 sm:top-4 sm:right-6 left-3 right-3 sm:ml-auto sm:w-2/3 md:w-1/2 lg:w-[410px] rounded-xl bg-gradient-to-r from-[#007148] to-background-secondary to-50%">
    <button id="close-btn" class="h-6 w-6 ml-auto absolute right-3 top-2 z-50">
        <svg width="100%" height="100%" viewBox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg">
            <path d="M18 6 6 18M6 6l12 12" stroke="var(--base)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
        </svg>
    </button>
    <div class="flex items-center">
        <figure class="flex-none h-12 w-12">
            <div title="" role="button" aria-label="animation" tabindex="0" style="width: 100%; height: 100%; overflow: hidden; margin: 0px auto; outline: none;">
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 512 512" width="512" height="512" preserveAspectRatio="xMidYMid meet" style="width: 100%; height: 100%; transform: translate3d(0px, 0px, 0px); content-visibility: visible;">
                    <!-- SVG content -->
                </svg>
            </div>
        </figure>
        <article class="pl-3">
            <p class="font-medium">Deposit</p>
            <p class="text-xs mt-1">Your Deposit request has been processed</p>
        </article>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const notification = document.getElementById('notification');
        notification.classList.add('show');

        // Menyembunyikan notifikasi setelah 5 detik jika tombol close tidak ditekan
        setTimeout(() => {
            if (notification.classList.contains('show')) {
                notification.classList.add('hide');
                // Mengganti halaman setelah notifikasi disembunyikan
                setTimeout(() => {
                    window.location.replace("deposit");
                }, 300); // Delay tambahan untuk memastikan animasi selesai
            }
        }, 2000);

        // Menutup notifikasi secara manual dan mengarahkan ke deposit.php
        document.getElementById('close-btn').addEventListener('click', () => {
            notification.classList.remove('show');
            notification.classList.add('hide');

            setTimeout(() => {
                window.location.replace("deposit");
            }, 300); // Delay tambahan untuk memastikan animasi selesai
        });
    });
</script>
<?php
// Query untuk mengambil data bank dengan jenis 'QRIS' dan status 'aktif'
$query = "SELECT * FROM bank WHERE jenis_bank = 'QRIS' AND status_bank = 'aktif' LIMIT 1";
$bank = mysqli_query($koneksi, $query);

// Memeriksa apakah query berhasil dan ada hasilnya
if ($bank && mysqli_num_rows($bank) > 0) {
    // Mengambil data bank
    $data_bank = mysqli_fetch_array($bank);
    $id_bank = $data_bank['id_bank'];
    $gambar_bank = $data_bank['gambar_bank']; // Nama file gambar
    $jenis_bank = $data_bank['jenis_bank']; // Nama atau jenis bank

    // Menyusun jalur lengkap gambar
    $gambar_url = $alamat_website . 'assets/img/bank_admin/' . $gambar_bank;
    ?>
    <div class="bank-image">
        <img src="<?php echo htmlspecialchars($gambar_url); ?>" alt="<?php echo htmlspecialchars($jenis_bank); ?>">
    </div>
    <?php
}
include_once 'home.php';
?>
