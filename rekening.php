<?php
include_once 'koneksi.php';
include_once 'header.php';
// Cek jika pengguna sudah login
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    // Ambil ID anggota dan username dari session
    $id_anggota = $_SESSION['id_anggota'];
    $username = $_SESSION['nama_pengguna_anggota']; // Pastikan username disimpan dalam session
} else {
    // Jika tidak login, alihkan atau tampilkan pesan error
    echo '
        <script>
          window.location.replace("' . $alamat_website . 'home");
        </script>
      ';
    exit();
}
// Memastikan session id_anggota terdefinisi
if (isset($_SESSION['id_anggota'])) {
    $id_anggota_aktif = $_SESSION['id_anggota'];

    // Query untuk mendapatkan data anggota
    $query_anggota = mysqli_query($koneksi, "SELECT * FROM anggota WHERE id_anggota = '$id_anggota_aktif'");
    $data_anggota = mysqli_fetch_assoc($query_anggota);

    if ($data_anggota) {
        $bank_anggota_aktif = $data_anggota['bank_anggota'];
        $nomor_rekening_anggota_aktif = $data_anggota['nomor_rekening_anggota'];
        $nama_rekening_anggota = $data_anggota['nama_rekening_anggota'];
        $nama_pengguna_anggota = $data_anggota['nama_pengguna_anggota'];
        $email_anggota = $data_anggota['email_anggota'];
        
        // Fungsi untuk menyensor dan memformat nomor rekening
        function sensorNomorRekening($nomorRekening)
        {
            // Cek apakah panjang nomor rekening minimal 3 digit
            if (strlen($nomorRekening) >= 3) {
                // Potong bagian awal nomor rekening untuk menyensor 3 angka terakhir
                $numLength = strlen($nomorRekening) - 3;
                $visiblePart = substr($nomorRekening, 0, $numLength);
                $hiddenPart = 'XXX';

                // Tambahkan tanda hubung setiap 3 digit pada bagian yang terlihat
                $formattedVisiblePart = chunk_split($visiblePart, 3, '-');

                // Gabungkan bagian yang terlihat dan yang tersembunyi
                return rtrim($formattedVisiblePart, '-') . '-' . $hiddenPart;
            }
            // Jika nomor rekening kurang dari 3 digit, kembalikan sebagaimana adanya
            return $nomorRekening;
        }

        // Sensor dan format nomor rekening anggota aktif
        $nomor_rekening_anggota_aktif_sensored = sensorNomorRekening($nomor_rekening_anggota_aktif);
    }
}
?>
<div class="w-full lg:w-2/3 lg:px-3 pb-24 lg:pb-0">
<section class="px-4 mt-4 lg:mt-0 lg:mx-auto">
<div class="mb-4 lg:w-3/5 lg:mx-auto">
<h3 class="font-semibold"><?php echo $bank_anggota_aktif; ?></h3>
<div class="bg-background-secondary rounded-xl p-3 flex items-center justify-between mt-3 cursor-pointer transition-all duration-300 ease-in-out hover:lg:bg-background-tertiary"><div class="flex items-center">
<figure class="flex flex-none w-11 h-11 items-center justify-center rounded-full bg-white"><img alt="Logo Bank OVO" loading="lazy" width="0" height="0" decoding="async" data-nimg="1" class="w-full px-1" src="https://cdn.databerjalan.com/cdn-cgi/image/width=auto,quality=75,fit=contain,format=auto//assets/images/static/v3/icon/transaction/BANK.webp" style="color: transparent;">
</figure><article class="pl-3"><p class="text-sm font-semibold truncate"><?php echo $bank_anggota_aktif; ?></p><p class="text-xs mt-[2px]"><?php echo $nomor_rekening_anggota_aktif; ?></p>
</article>
</div>
<div class="flex-none"><svg width="25" height="25" viewBox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg" size="25">
<path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="var(--base)"></path></svg>
</div>
</div>
</div>
<a class="w-2/3 lg:w-2/4 py-3 mt-5 mx-auto text-xs justify-center font-semibold rounded-full bg-primary text-white hover:lg:brightness-90 transition-all duration-300 ease-in-out" href="https://direct.lc.chat/18723807/">+ Tambah Rekening</a>
<div class="lg:mt-5 lg:mb-10">
<p class="text-xs text-center mt-3 lg:mb-5">Hubungi CS untuk mengubah nama rekening Anda</p>

<figure class="flex items-center">
<svg width="24" height="24" viewBox="0 0 24 24" fill="var(--primary)" xmlns="http://www.w3.org/2000/svg" size="24">
<g fill="var(--primary)">
<path d="m21.696 20.72-4.971-4.973-1.001 1 4.978 4.966.994-.994ZM2.298 3.355 7.27 8.327l.993-.993-4.971-4.972-.994.993ZM19.704 22.711l-4.972-4.972-.99.99c-2.929-1.626-6.836-5.486-8.461-8.413l.998-.998-4.972-4.971-.018.017C.044 5.61-.304 7.504.423 9.078 2.342 13.225 7.675 20.321 15.04 23.65c1.589.717 3.433.293 4.628-.903l.036-.036ZM8.346 2.673l.995.994c3.016-3.016 8.016-3.016 11.032 0s3.016 7.97 0 10.985l.994.994c3.564-3.563 3.564-9.41 0-12.973-3.564-3.564-9.457-3.564-13.02 0Z"></path>
<path d="m12.628 8.484-.297.149a2.962 2.962 0 0 0-1.646 2.664V12h4.219v-1.406h-2.649c.15-.299.393-.547.705-.703l.297-.149a2.962 2.962 0 0 0 1.647-2.664c0-1.163-.947-2.11-2.11-2.11-1.163 0-2.11.947-2.11 2.11v.703h1.407v-.703a.704.704 0 0 1 1.406 0c0 .6-.333 1.138-.87 1.406ZM16.31 4.969v4.219h2.812V12h1.407V4.969h-1.407V7.78h-1.406V4.97H16.31Z">
</path></g></svg>
<button id="contact-cs-button" class="bg-background-tertiary justify-between rounded-full w-full lg:w-1/2 lg:mx-auto mt-5 py-2 px-3 transition-all duration-300 ease-in-out lg:hover:bg-white/30">
    <span class="text-xs pl-2">Hubungi CS</span>
    <svg width="24" height="24" viewBox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg" size="24">
        <path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="var(--base)"></path>
    </svg>
</button>

<div id="contact-us-popup" class="hidden fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50">
    <section class="bg-background-secondary rounded-lg max-h-[70vh] lg:max-h-[30vh] overflow-auto p-4">
        <div class="flex justify-between items-center mb-3">
            <p class="flex items-center">
                <span class="text-sm font-semibold pr-2">Hubungi Kami</span>
                <span class="text-xs bg-primary px-2 py-[1px] rounded-full text-white">Layanan 24/7</span>
            </p>
            <button id="close-popup" class="ml-auto">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg">
                    <path d="M18 6 6 18M6 6l12 12" stroke="var(--base)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
            </button>
        </div>
        <div class="overflow-auto">
            <button aria-label="livechat contact button" class="flex items-center w-full border-b border-separator pb-3 mt-3 cursor-pointer">
                <figure class="mr-3 flex-none w-7 h-7">
                    <img alt="Contact-livechat" loading="lazy" class="h-full w-auto" src="https://cdn.databerjalan.com/cdn-cgi/image/width=auto,quality=75,fit=contain,format=auto//assets/images/social/circle/livechat-new.webp" style="color: transparent;">
                </figure>
                <p class="text-sm">Livechat</p>
            </button>
            <a target="_blank" rel="nofollow" class="flex items-center border-b border-separator pb-3 mt-3 cursor-pointer" href="https://wa.me/6281229143783">
                <figure class="mr-3 flex-none w-7 h-7">
                    <img alt="Contact-Whatsapp" loading="lazy" class="h-full w-auto" src="https://cdn.databerjalan.com/cdn-cgi/image/width=auto,quality=75,fit=contain,format=auto//assets/images/social/circle/whatsapp.png" style="color: transparent;">
                </figure>
                <p class="text-sm">Whatsapp</p>
            </a>
            <a target="_blank" rel="nofollow" class="flex items-center border-b border-separator pb-3 mt-3 cursor-pointer" href="https://t.me/+hR77EgZT8jdjZGY1">
                <figure class="mr-3 flex-none w-7 h-7">
                    <img alt="Contact-Telegram" loading="lazy" class="h-full w-auto" src="https://cdn.databerjalan.com/cdn-cgi/image/width=auto,quality=75,fit=contain,format=auto//assets/images/social/circle/telegram.png" style="color: transparent;">
                </figure>
                <p class="text-sm">Telegram</p>
            </a>
       </div>
</section>
</div>
</div>
</section>
</div>

<script>
// Mendapatkan elemen tombol dan popup
const contactButton = document.getElementById('contact-cs-button');
const contactPopup = document.getElementById('contact-us-popup');
const closeButton = document.getElementById('close-popup');

// Fungsi untuk membuka popup
function openPopup() {
    contactPopup.classList.remove('hidden');
}

// Fungsi untuk menutup popup
function closePopup() {
    contactPopup.classList.add('hidden');
}

// Event listener untuk membuka popup saat tombol diklik
contactButton.addEventListener('click', openPopup);

// Event listener untuk menutup popup saat tombol close diklik
closeButton.addEventListener('click', closePopup);

// Jika ingin menutup popup dengan mengklik di luar popup
window.addEventListener('click', (event) => {
    if (event.target === contactPopup) {
        closePopup();
    }
});

</script>
<?php include_once 'footer.php'; ?>