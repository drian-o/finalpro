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
    // Mengecek status deposit terakhir anggota
    $status_deposit = null;
    $tanggal_deposit_terakhir = null;
    $tujuan_deposit = null;

    // Query untuk mendapatkan status deposit dan tanggal deposit terakhir
    $query_deposit = mysqli_query($koneksi, "SELECT status_deposit,tanggal_deposit FROM deposit WHERE id_anggota_deposit = '$id_anggota_aktif' ORDER BY tanggal_deposit DESC LIMIT 1");
    $data_deposit = mysqli_fetch_assoc($query_deposit);

    if ($data_deposit) {
        $status_deposit = $data_deposit['status_deposit'];
        $tanggal_deposit_terakhir = $data_deposit['tanggal_deposit'];
    }
    // Mengecek jumlah_deposit terakhir yang masih berstatus diproses
    $jumlah_deposit_terakhir = null;
    $query_jumlah = mysqli_query($koneksi, "SELECT jumlah_deposit FROM deposit WHERE id_anggota_deposit = '$id_anggota_aktif' AND status_deposit = 'diproses' ORDER BY tanggal_deposit DESC LIMIT 1");
    $data_jumlah = mysqli_fetch_assoc($query_jumlah);

    if ($data_jumlah) {
        $jumlah_deposit_terakhir = $data_jumlah['jumlah_deposit'];
        // Mengubah jumlah_deposit_terakhir menjadi format rupiah
        $jumlah_deposit_terakhir = number_format($jumlah_deposit_terakhir, 0, ',', '.');
    }
    
    $data_metode = null;
    $query_metode = mysqli_query($koneksi, "SELECT tujuan_deposit FROM deposit WHERE id_anggota_deposit = '$id_anggota_aktif' AND status_deposit = 'diproses' ORDER BY tanggal_deposit DESC LIMIT 1");
    $data_metode = mysqli_fetch_assoc($query_metode);

    if ($data_metode) {
        $metode_deposit = $data_metode['tujuan_deposit'];
    }
}
?>
<section class="container flex mx-auto lg:pt-3 lg:pb-5 lg:mt-5">
    <div class="w-full lg:w-1/3 px-3 hidden lg:block">
        <a class="px-3 py-4 bg-background-default lg:bg-background-secondary rounded-xl mt-4 drop-shadow-[0_0_5px_rgba(0,0,0,0.6)] lg:drop-shadow-none group transition-all duration-300 ease-in-out lg:hover:bg-black/30" href="#">
            <figure class="flex flex-none items-center justify-center w-12 md:w-16 h-12 md:h-16">
                <img alt="VIP Level Badge" width="0" height="0" decoding="async" data-nimg="1" class="w-full" style="color: transparent;" loading="lazy" src="assets/img/pemainbaru1.png">
            </figure>
            <article class="w-full pl-4">
                <p class="text-sm md:text-base group-hover:text-white">Pemain Baru</p>
                <progress class="w-full h-[5px] primary-progress" value="0" max="100"></progress>
                <span class="text-xs md:text-sm group-hover:text-white">Increase your level and get rewards</span>
            </article>
            <figure class="pl-2"><svg width="26" height="26" viewbox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg" size="26">
                    <path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="var(--base)"></path>
                </svg>
            </figure>
        </a>
        <section class="bg-background-secondary rounded-xl mt-4">
            <div class="w-full lg:px-4 pt-3 flex flex-wrap px-4">
                <article class="w-full flex items-center mb-1 lg:mb-3">
                    <svg width="24" height="24" viewbox="0 0 24 24" fill="var(--primary)" xmlns="http://www.w3.org/2000/svg" size="24">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M2.879 3.879C2 4.757 2 6.172 2 9v6c0 2.828 0 4.243.879 5.121C3.757 21 5.172 21 8 21h10c.93 0 1.395 0 1.776-.102a3 3 0 0 0 2.122-2.122C22 18.395 22 17.93 22 17h-6a3 3 0 1 1 0-6h6V9c0-2.828 0-4.243-.879-5.121C20.243 3 18.828 3 16 3H8c-2.828 0-4.243 0-5.121.879ZM7 7a1 1 0 0 0 0 2h3a1 1 0 1 0 0-2H7Z" fill="var(--primary)"></path>
                        <path d="M17 14h-1" stroke="var(--primary)" stroke-width="2" stroke-linecap="round"></path>
                    </svg>
                    <span class="text-xs lg:text-sm text-caption px-2">Account Balance</span>
                    <button>
                        <svg width="20" height="20" viewbox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="12" cy="12" r="3.5" stroke="var(--caption)"></circle>
                            <path d="M20.188 10.934c.388.472.582.707.582 1.066 0 .359-.194.594-.582 1.066C18.768 14.79 15.636 18 12 18c-3.636 0-6.768-3.21-8.188-4.934-.388-.472-.582-.707-.582-1.066 0-.359.194-.594.582-1.066C5.232 9.21 8.364 6 12 6c3.636 0 6.768 3.21 8.188 4.934Z" stroke="var(--caption)"></path>
                        </svg>
                    </button>
                </article>
                <div class="w-full flex lg:gap-x-5">
                    <div class="w-full flex items-center">
                        <section class="w-full flex items-center h-7">
                            <span class="text-sm lg:text-base font-semibold">IDR&nbsp;<?php echo number_format($_SESSION['saldo_anggota'], 0, ',', '.'); ?></span>
                            <button class="rounded-full bg-background-default cursor-pointer rotate-270 w-7 h-7 ml-2 items-center justify-center flex">
                                <svg width="20" height="20" viewbox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="m10 19-.707-.707-.707.707.707.707L10 19Zm3.293-4.707-4 4 1.414 1.414 4-4-1.414-1.414Zm-4 5.414 4 4 1.414-1.414-4-4-1.414 1.414Z" fill="var(--caption)"></path>
                                    <path d="M5.938 15.5A7 7 0 1 1 12 19" stroke="var(--caption)" stroke-width="2" stroke-linecap="round"></path>
                                </svg>
                            </button>
                        </section>
                    </div>
                </div>
            </div>
            <div class="flex gap-x-4 px-4 pb-6 mt-5">
                <a class="w-full justify-center py-2 rounded-lg text-primary border border-primary transition-all duration-200 ease-in-out hover:lg:bg-background-tertiary" href="<?php echo $alamat_website . 'withdraw'; ?>">Withdraw</a>
                <a class="w-full justify-center py-2 rounded-lg bg-primary text-white transition-all duration-200 ease-in-out hover:lg:brightness-90" href="<?php echo $alamat_website . 'deposit'; ?>">Deposit</a>
            </div>
        </section>
        <section class="bg-background-secondary rounded-xl mt-4 overflow-hidden"><a class="justify-between px-4 py-3 hover:lg:bg-background-tertiary transition-all duration-300 ease-in-out" href="<?php echo $alamat_website . 'my-account'; ?>">
                <div class="flex items-center w-[calc(100%-24px)] pr-1">
                    <svg width="24" height="24" viewbox="0 0 24 24" fill="var(--primary)" xmlns="http://www.w3.org/2000/svg" size="24">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M2.002 10h19.996c-.012-2.175-.108-3.353-.877-4.121C20.243 5 18.828 5 16 5H8c-2.828 0-4.243 0-5.121.879-.769.768-.865 1.946-.877 4.121ZM22 12H2v2c0 2.828 0 4.243.879 5.121C3.757 20 5.172 20 8 20h8c2.828 0 4.243 0 5.121-.879C22 18.243 22 16.828 22 14v-2ZM7 15a1 1 0 1 0 0 2h.01a1 1 0 1 0 0-2H7Z" fill="var(--primary)"></path>
                    </svg>
                    <span class="text-sm pl-2 undefined text-primary">My Account</span>
                </div>
                <figure class="w-6"><svg width="22" height="22" viewbox="0 0 24 24" fill="var(--primary)" xmlns="http://www.w3.org/2000/svg" size="22">
                        <path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="var(--primary)"></path>
                    </svg>
                </figure>
                <figure class="w-6"><svg width="22" height="22" viewbox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg" size="22">
                        <path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="var(--base)"></path>
                    </svg>
                </figure>
            </a>
        </section>
        <section class="bg-background-secondary rounded-xl mt-4 overflow-hidden"><a class="justify-between px-4 py-3 hover:lg:bg-background-tertiary transition-all duration-300 ease-in-out" href="<?php echo $alamat_website . 'change-pasword'; ?>">
                <div class="flex items-center w-[calc(100%-24px)] pr-1">
                    <svg width="24" height="24" viewbox="0 0 24 24" fill="var(--primary)" xmlns="http://www.w3.org/2000/svg" size="24">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M13.984 2.542c.087.169.109.386.152.82.082.82.123 1.23.295 1.456a1 1 0 0 0 .929.384c.28-.037.6-.298 1.238-.82.337-.277.506-.415.687-.473a1 1 0 0 1 .702.035c.175.076.33.23.637.538l.894.894c.308.308.462.462.538.637a1 1 0 0 1 .035.702c-.058.181-.196.35-.472.687-.523.639-.784.958-.822 1.239a1 1 0 0 0 .385.928c.225.172.636.213 1.457.295.433.043.65.065.82.152a1 1 0 0 1 .47.521c.071.177.071.395.071.831v1.264c0 .436 0 .654-.07.83a1 1 0 0 1-.472.522c-.169.087-.386.109-.82.152-.82.082-1.23.123-1.456.295a1 1 0 0 0-.384.929c.038.28.299.6.821 1.238.276.337.414.505.472.687a1 1 0 0 1-.035.702c-.076.175-.23.329-.538.637l-.894.893c-.308.309-.462.463-.637.538a1 1 0 0 1-.702.035c-.181-.058-.35-.196-.687-.472-.639-.522-.958-.783-1.238-.82a1 1 0 0 0-.929.384c-.172.225-.213.635-.295 1.456-.043.434-.065.651-.152.82a1 1 0 0 1-.521.472c-.177.07-.395.07-.831.07h-1.264c-.436 0-.654 0-.83-.07a1 1 0 0 1-.522-.472c-.087-.169-.109-.386-.152-.82-.082-.82-.123-1.23-.295-1.456a1 1 0 0 0-.928-.384c-.281.037-.6.298-1.239.82-.337.277-.506.415-.687.473a1 1 0 0 1-.702-.035c-.175-.076-.33-.23-.637-.538l-.894-.894c-.308-.308-.462-.462-.538-.637a1 1 0 0 1-.035-.702c.058-.181.196-.35.472-.687.523-.639.784-.958.821-1.239a1 1 0 0 0-.384-.928c-.225-.172-.636-.213-1.457-.295-.433-.043-.65-.065-.82-.152a1 1 0 0 1-.47-.521C2 13.286 2 13.068 2 12.632v-1.264c0-.436 0-.654.07-.83a1 1 0 0 1 .472-.522c.169-.087.386-.109.82-.152.82-.082 1.231-.123 1.456-.295a1 1 0 0 0 .385-.928c-.038-.281-.3-.6-.822-1.24-.276-.337-.414-.505-.472-.687a1 1 0 0 1 .035-.702c.076-.174.23-.329.538-.637l.894-.893c.308-.308.462-.463.637-.538a1 1 0 0 1 .702-.035c.181.058.35.196.687.472.639.522.958.783 1.238.821a1 1 0 0 0 .93-.385c.17-.225.212-.635.294-1.456.043-.433.065-.65.152-.82a1 1 0 0 1 .521-.471c.177-.07.395-.07.831-.07h1.264c.436 0 .654 0 .83.07a1 1 0 0 1 .522.472ZM12 16a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" fill="var(--primary)"></path>
                    </svg>
                    <span class="text-sm pl-2 undefined false">Account Settings</span>
                </div>
                <figure class="w-6"><svg width="22" height="22" viewbox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg" size="22">
                        <path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="var(--base)"></path>
                    </svg>
                </figure>
            </a>
        </section>
        <a href="<?php echo $alamat_website . 'logout'; ?>" class="block">
            <div class="bg-background-secondary rounded-xl mt-4 overflow-hidden">
                <section class="justify-between px-4 py-3 flex cursor-pointer hover:lg:bg-background-tertiary transition-all duration-300 ease-in-out " href="<?php echo $alamat_website . 'logout'; ?>">
                    <div class="flex items-center w-[calc(100%-24px)] pr-1">
                        <svg width="24" height="24" viewbox="0 0 24 24" fill="var(--primary)" xmlns="http://www.w3.org/2000/svg" size="24">
                            <path d="m2 12-.78-.625-.5.625.5.625L2 12Zm9 1a1 1 0 1 0 0-2v2ZM5.22 6.375l-4 5 1.56 1.25 4-5-1.56-1.25Zm-4 6.25 4 5 1.56-1.25-4-5-1.56 1.25ZM2 13h9v-2H2v2ZM13.342 20.557l.165-.986-.165.986Zm7.597.19.646.764-.646-.763ZM15.014 3.165l-.165-.986.165.986Zm5.925.088.646-.763-.646.763ZM13.507 4.43l1.671-.278-.329-1.973-1.671.279.329 1.972ZM21 9.083v5.834h2V9.083h-2Zm-5.822 10.766-1.671-.278-.329 1.973 1.671.278.329-1.973ZM11 8.132v-.743H9v.743h2Zm0 8.48v-.546H9v.546h2Zm2.507 2.959c-.824-.138-1.35-.227-1.734-.342-.358-.106-.472-.201-.536-.277l-1.526 1.293c.41.484.932.735 1.491.901.532.159 1.203.269 1.976.398l.329-1.973ZM9 16.61c0 .784-.002 1.464.067 2.015.072.578.234 1.135.644 1.619l1.526-1.293c-.064-.075-.14-.203-.185-.574-.05-.398-.052-.932-.052-1.767H9Zm12-1.694c0 1.675-.002 2.823-.123 3.67-.116.82-.32 1.174-.584 1.398l1.293 1.526c.797-.675 1.123-1.593 1.272-2.642.144-1.021.142-2.338.142-3.952h-2Zm-6.15 6.905c1.59.265 2.89.484 3.92.51 1.06.025 2.018-.146 2.816-.821l-1.293-1.526c-.264.223-.646.367-1.474.347-.856-.021-1.99-.207-3.641-.483l-.329 1.973Zm.328-17.671c1.652-.275 2.785-.462 3.64-.483.829-.02 1.21.124 1.475.347l1.293-1.526c-.797-.675-1.757-.846-2.816-.82-1.03.025-2.33.244-3.92.509l.328 1.973ZM23 9.083c0-1.614.002-2.93-.142-3.952-.15-1.049-.476-1.967-1.273-2.642l-1.292 1.526c.264.224.468.577.584 1.397.12.848.123 1.996.123 3.67h2Zm-9.822-6.626c-.773.128-1.444.238-1.976.397-.559.166-1.081.417-1.491.901l1.526 1.293c.064-.076.178-.17.536-.277.384-.115.91-.204 1.734-.342l-.329-1.972ZM11 7.389c0-.835.002-1.369.052-1.767.046-.37.121-.499.185-.574L9.711 3.755c-.41.484-.572 1.04-.644 1.62C8.998 5.924 9 6.604 9 7.388h2Z" fill="var(--primary)"></path>
                        </svg>
                        <span class="text-sm pl-2 undefined undefined">Logout</span>
                    </div>
                    <figure class="w-6"><svg width="22" height="22" viewbox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg" size="22">
                            <path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="var(--base)"></path>
                        </svg>
                    </figure>
                </section>
            </div>
            </div>
    </a>
    <div class="w-full lg:w-2/3 lg:px-3 pb-24 lg:pb-0">
        <div class="grid grid-cols-2 px-3 lg:gap-x-5 lg:px-4 lg:mb-6 mt-4 lg:mt-0">
            <a aria-label="Deposit-tab-button" aria-labelledby="Deposit-tab-button" class="text-sm border-b uppercase flex-none px-1 pb-2 justify-center cursor-pointer transition-all duration-300 ease-in-out hover:lg:border-primary hover:lg:text-inverse font-semibold border-primary " href="<?php echo $alamat_website . 'deposit'; ?>">Deposit</a>
            <a aria-label="Withdraw-tab-button" aria-labelledby="Withdraw-tab-button" class="text-sm border-b uppercase flex-none px-1 pb-2 justify-center cursor-pointer transition-all duration-300 ease-in-out hover:lg:border-primary hover:lg:text-inverse font-semibold border-transparent text-caption " href="<?php echo $alamat_website . 'withdraw'; ?>">Withdraw</a>
        </div>
        <?php if (isset($status_deposit) && $status_deposit == 'diproses') : ?>
            <section class="mt-3 px-3 lg:px-4 pb-6">
                <div class="lg:bg-background-secondary lg:px-5 lg:pt-6 lg:pb-16 lg:mb-4 rounded-xl">
                    <div class="h-12 lg:h-16 w-12 lg:w-16 mx-auto my-4">
                        <img src="assets/img/bankdeposit.png" alt="Deposit Progres" class="w-full h-full object-contain" />
                    </div>
                    <p class="text-xs text-center mt-3 opacity-70">Deposit</p>
                    <p class="text-sm font-semibold text-center mt-3">In Progress</p>
                    <div class="px-3 mt-5">
                        <div class="relative">
                            <div class="flex justify-between items-center mt-4 pb-3 border-b border-disable">
                                <p class="text-xs opacity-70 w-1/2">Amount</p>
                                <article class="flex items-center justify-end w-1/2">
                                    <p class="text-xs truncate">IDR&nbsp;<?php echo $jumlah_deposit_terakhir; ?></p>
                                    <div class="pl-1 cursor-pointer">
                                        <svg width="24" height="24" viewbox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M15.5 3h-6a4 4 0 0 0-4 4v8" stroke="var(--primary)" stroke-width="2"></path>
                                            <path d="M9.5 11.5c0-1.196.001-2.01.071-2.628.068-.598.188-.889.342-1.09a2 2 0 0 1 .37-.369c.2-.154.491-.274 1.09-.342C11.99 7.001 12.803 7 14 7c1.196 0 2.01.001 2.628.071.598.068.889.188 1.09.342.138.107.262.23.369.37.154.2.274.491.342 1.09.07.618.071 1.431.071 2.627v4c0 1.196-.002 2.01-.071 2.628-.068.598-.188.889-.342 1.09-.107.138-.23.262-.37.369-.2.154-.491.274-1.09.342-.618.07-1.431.071-2.627.071-1.196 0-2.01-.002-2.628-.071-.598-.068-.889-.188-1.09-.342a1.998 1.998 0 0 1-.369-.37c-.154-.2-.274-.491-.342-1.09-.07-.618-.071-1.431-.071-2.627v-4Z" stroke="var(--primary)" stroke-width="2"></path>
                                        </svg>
                                    </div>
                                </article>
                            </div>
                            <div class="flex justify-between items-center mt-4 pb-3 border-b border-disable">
                                <p class="text-xs opacity-70 w-1/2">Bank</p>
                                <article class="flex items-center justify-end w-1/2">
                                    <p class="text-xs truncate"><?php echo $bank_anggota_aktif; ?></p>
                                </article>
                            </div>
                            <div class="flex justify-between items-center mt-4 pb-3 border-b border-disable">
                                <p class="text-xs opacity-70 w-1/2">Account Number</p>
                                <article class="flex items-center justify-end w-1/2">
                                    <p class="text-xs truncate"><?php echo htmlspecialchars(rtrim($nomor_rekening_anggota_aktif, '-')); ?></p>
                                    <div class="pl-1 cursor-pointer">
                                        <svg width="24" height="24" viewbox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M15.5 3h-6a4 4 0 0 0-4 4v8" stroke="var(--primary)" stroke-width="2"></path>
                                            <path d="M9.5 11.5c0-1.196.001-2.01.071-2.628.068-.598.188-.889.342-1.09a2 2 0 0 1 .37-.369c.2-.154.491-.274 1.09-.342C11.99 7.001 12.803 7 14 7c1.196 0 2.01.001 2.628.071.598.068.889.188 1.09.342.138.107.262.23.369.37.154.2.274.491.342 1.09.07.618.071 1.431.071 2.627v4c0 1.196-.002 2.01-.071 2.628-.068.598-.188.889-.342 1.09-.107.138-.23.262-.37.369-.2.154-.491.274-1.09.342-.618.07-1.431.071-2.627.071-1.196 0-2.01-.002-2.628-.071-.598-.068-.889-.188-1.09-.342a1.998 1.998 0 0 1-.369-.37c-.154-.2-.274-.491-.342-1.09-.07-.618-.071-1.431-.071-2.627v-4Z" stroke="var(--primary)" stroke-width="2"></path>
                                        </svg>
                                    </div>
                                </article>
                            </div>
                            <div class="flex justify-between items-center mt-4 pb-3 border-b border-disable">
                                <p class="text-xs opacity-70 w-1/2">Sender</p>
                                <article class="flex items-center justify-end w-1/2">
                                    <p class="text-xs truncate"><?php echo $nama_rekening_anggota; ?></p>
                                </article>
                            </div>
                            <div class="flex justify-between items-center mt-4 pb-3 border-b border-disable">
                                <p class="text-xs opacity-70 w-1/2">Request Date</p>
                                <article class="flex items-center justify-end w-1/2">
                                    <p class="text-xs truncate"><?php echo $tanggal_deposit_terakhir; ?></p>
                                </article>
                            </div>
                            <div class="flex justify-between items-center mt-4 pb-3 border-b border-disable">
                                <p class="text-xs opacity-70 w-1/2">Status</p>
                                <article class="flex items-center justify-end w-1/2">
                                    <p class="text-xs truncate text-danger">In Progress</p>
                                </article>
                            </div>
                        </div>
                        <p class="text-xs text-center mt-3 lg:mb-5">
                            You have pending transactions, please contact Customer Service
                        </p>
                        <a href="<?php echo $isi_1_link_livechat_web; ?>"  target="blank" aria-label="You have pending transactions, please contact Customer Service contact button" class="bg-background-tertiary justify-between rounded-full w-full lg:w-1/2 lg:mx-auto mt-5 py-2 px-3 transition-all duration-300 ease-in-out lg:hover:bg-white/30">
                            <figure class="flex items-center">
                                <svg width="24" height="24" viewbox="0 0 24 24" fill="var(--primary)" xmlns="http://www.w3.org/2000/svg" size="24">
                                    <g fill="var(--primary)">
                                        <path d="m21.696 20.72-4.971-4.973-1.001 1 4.978 4.966.994-.994ZM2.298 3.355 7.27 8.327l.993-.993-4.971-4.972-.994.993ZM19.704 22.711l-4.972-4.972-.99.99c-2.929-1.626-6.836-5.486-8.461-8.413l.998-.998-4.972-4.971-.018.017C.044 5.61-.304 7.504.423 9.078 2.342 13.225 7.675 20.321 15.04 23.65c1.589.717 3.433.293 4.628-.903l.036-.036ZM8.346 2.673l.995.994c3.016-3.016 8.016-3.016 11.032 0s3.016 7.97 0 10.985l.994.994c3.564-3.563 3.564-9.41 0-12.973-3.564-3.564-9.457-3.564-13.02 0Z"></path>
                                        <path d="m12.628 8.484-.297.149a2.962 2.962 0 0 0-1.646 2.664V12h4.219v-1.406h-2.649c.15-.299.393-.547.705-.703l.297-.149a2.962 2.962 0 0 0 1.647-2.664c0-1.163-.947-2.11-2.11-2.11-1.163 0-2.11.947-2.11 2.11v.703h1.407v-.703a.704.704 0 0 1 1.406 0c0 .6-.333 1.138-.87 1.406ZM16.31 4.969v4.219h2.812V12h1.407V4.969h-1.407V7.78h-1.406V4.97H16.31Z"></path>
                                    </g>
                                </svg>
                                <span class="text-xs pl-2">Contact CS</span>
                            </figure>
                            <svg width="24" height="24" viewbox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg" size="24">
                                <path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="var(--base)"></path>
                            </svg>
                        </a>
                        <div id="contact-us-popup" class="relative">
                        </div>
                    </div>
                </div>
            </section>
            <div class="border-t-8 border-background-secondary lg:border-background-default px-4 mt-4 lg:hidden">
                <a class="bg-primary font-medium mt-8 justify-center py-3 rounded-xl" href="home">Back to Home</a>
                <a class="border border-primary text-primary font-medium mt-4 justify-center py-3 rounded-xl" href="history-deposit">History Deposit</a>
            </div>
    </div>
    </div>
<?php else : ?>

    <div class="flex justify-between items-center px-4 my-4">
        <p class="text-sm">Choose Deposit Method</p>
        <div class="relative">
            <button class="flex items-center">
                <svg width="24" height="24" viewbox="0 0 24 24" fill="var(--primary)" xmlns="http://www.w3.org/2000/svg" size="24">
                    <path d="M12 6a3.939 3.939 0 0 0-3.934 3.934h2C10.066 8.867 10.934 8 12 8c1.066 0 1.934.867 1.934 1.934 0 .598-.481 1.032-1.216 1.626-.24.188-.47.388-.691.599-.998.997-1.027 2.056-1.027 2.174V15h2l-.001-.633c0-.016.033-.386.44-.793.15-.15.34-.3.536-.458.779-.631 1.958-1.584 1.958-3.182A3.937 3.937 0 0 0 12 6Zm-1 10h2v2h-2v-2Z" fill="var(--primary)"></path>
                    <path d="M12 2C6.486 2 2 6.486 2 12s4.486 10 10 10 10-4.486 10-10S17.514 2 12 2Zm0 18c-4.411 0-8-3.589-8-8s3.589-8 8-8 8 3.589 8 8-3.589 8-8 8Z" fill="var(--primary)"></path>
                </svg>
            </button>
        </div>
    </div>
<div class="px-4">
    <div class="flex">
        <a href="qris" class="w-full items-center rounded-lg px-3 py-4 lg:px-5 relative overflow-hidden transition-all duration-200 ease-in-out bg-gradient-to-tl from-primary to-[#3431C2] flex" aria-label="Deposit via VA or QRIS">
            <figure class="w-10 h-auto ">
                <svg width="100%" height="100%" viewbox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg">
                    <g fill="var(--base)">
                        <path d="M16.733 23.798c-.433-.102-.88-.166-1.297-.312-1.768-.616-2.963-1.82-3.595-3.582-.022-.062-.047-.122-.077-.202-.069.04-.125.071-.178.107-.466.31-.929.627-1.398.933-.366.239-.746.252-1.116.013-1.999-1.291-3.919-2.686-5.635-4.343-.754-.728-1.448-1.508-1.954-2.439a3.845 3.845 0 0 1-.482-1.885c.009-2.735.003-5.47.005-8.205 0-.134.007-.27.034-.4.082-.388.426-.703.819-.717 1.827-.059 3.562-.552 5.283-1.109A41.054 41.054 0 0 0 9.146.948c.328-.125.629-.122.954.003a35.002 35.002 0 0 0 4.347 1.382c.915.222 1.838.396 2.784.423.624.019.99.395.991 1.026.003 1.65.002 3.302.002 4.953v3.594c.571.214 1.134.361 1.637.623 1.819.948 2.88 2.457 3.105 4.5a5.705 5.705 0 0 1-5.053 6.307c-.05.006-.1.025-.15.038h-1.03Zm-.434-11.471V4.626c-.46-.077-.904-.139-1.343-.226-1.762-.346-3.464-.895-5.15-1.5a.599.599 0 0 0-.378 0c-1.482.534-2.976 1.028-4.516 1.366-.654.145-1.315.251-1.99.378V4.9c0 2.406-.008 4.812.008 7.219.002.294.065.626.214.873.306.508.647 1.009 1.048 1.444 1.565 1.698 3.424 3.043 5.33 4.324.05.034.17.015.228-.024a80.76 80.76 0 0 0 1.688-1.183.386.386 0 0 0 .134-.232c.304-1.936 1.296-3.4 3.026-4.325.528-.28 1.117-.443 1.701-.67Zm-2.85 5.73c-.007 2.108 1.691 3.81 3.815 3.823a3.817 3.817 0 0 0 3.827-3.787c.014-2.12-1.683-3.85-3.781-3.856-2.15-.007-3.853 1.679-3.86 3.82Z"></path>
                        <path d="M10.578 15.18H8.666v-.94H6.761v-1.921h.244c1.009 0 2.018.001 3.026-.002.381 0 .612-.273.52-.603-.06-.22-.226-.34-.487-.347-.396-.01-.794.01-1.186-.03-1.168-.115-2.027-1.028-2.128-2.23-.09-1.073.658-2.127 1.729-2.435l.183-.054v-.977h1.91v.932h1.894v1.923h-.235c-.986 0-1.973 0-2.958.002-.097 0-.198 0-.289.025a.473.473 0 0 0 .14.922c.306.01.612.003.92.004 1.246.007 2.257.864 2.422 2.053.165 1.194-.546 2.284-1.732 2.639-.131.04-.16.097-.158.217.006.268.002.535.002.822ZM14.93 18.913l.855-1.708 1.158.575 1.445-2.165 1.59 1.06-.292.442c-.518.776-1.035 1.553-1.554 2.328-.35.523-.833.652-1.396.372l-1.806-.904Z"></path>
                    </g>
                </svg>
            </figure>
            <div class="flex flex-wrap items-center w-full pl-3">
                <p class="w-full text-sm text-left uppercase font-medium">Deposit via VA or QRIS</p>
                <p class="text-xs text-left w-full">Faster and automatic deposit</p>
            </div>
            <figure class="w-7">
                <svg width="100%" height="100%" viewbox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg">
                    <path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="var(--base)"></path>
                </svg>
            </figure>
        </a>
    </div>
</div>

    <section class="lg:px-4 lg:pb-0" style="padding-bottom: 80px;">
        <div class="lg:bg-background-secondary lg:py-1 lg:pb-3 lg:rounded-t-xl lg:mt-4">
            <div class="px-4 lg:px-5 mt-2">
                <section class="flex gap-x-3 mt-2">
                    <div class="rounded-lg w-full p-3 relative border transition-all duration-200 ease-in-out lg:flex flex-col items-center hover:lg:bg-background-default cursor-pointer border-separator false">
                        <figure class="flex flex-none justify-center w-6 lg:w-8 h-6 lg:h-8 mx-auto lg:mr-3 lg:ml-0">
                            <img alt="Bank Assets" loading="lazy" width="0" height="0" decoding="async" data-nimg="1" class="w-full h-full lg:mx-auto" src="assets/img/BANK-LOGO.webp" style="color: transparent;">
                        </figure>
                        <p class="text-xs text-center mt-2 truncate text-inverse">Bank</p>
                        <section class="absolute bottom-0 right-0 bg-primary w-5 h-5 flex items-center justify-center rounded-br-md rounded-tl-md">
                            <img alt="BANK" loading="lazy" width="0" height="0" decoding="async" data-nimg="1" class="h-3 w-auto p-[2px]" src="assets/img/done.webp" style="color: transparent;">
                        </section>
                    </div>
                    <a href="qris" class="rounded-lg w-full p-3 relative border transition-all duration-200 ease-in-out lg:flex flex-col items-center hover:lg:bg-background-default cursor-pointer border-separator false">
    <figure class="flex flex-none justify-center w-6 lg:w-8 h-6 lg:h-8 mx-auto lg:mr-3 lg:ml-0">
        <img alt="E-Wallet Assets" loading="lazy" width="0" height="0" decoding="async" data-nimg="1" class="w-full h-full lg:mx-auto" src="assets/img/E_WALLET.webp" style="color: transparent;">
    </figure>
    <p class="text-xs text-center mt-2 truncate text-caption">QRIS</p>
</a>
            </section>
            </div>
            <div class="px-4 lg:px-5 mt-2">
                <section class="relative">
                    <form class="relative" method="post" action="<?php echo $alamat_website . 'proses_deposit'; ?>">
                        <div class="px-3 py-2 my-4 rounded-xl cursor-pointer border border-caption lg:bg-background-default">
                            <label class="text-xs opacity-70">Select Account</label>
                            <div class="flex items-center justify-between overflow-hidden my-0">
                                <div class="group pb-2 pt-1 lg:pt-2 cursor-pointer lg:hover:bg-background-default lg:hover:rounded-lg transition-all duration-300 ease-out undefined last:border-transparent">
                                    <div class="flex items-center w-full gap-x-4">
                                        <figure class="flex flex-none w-11 h-11 items-center justify-center rounded-full bg-white">
                                            <img alt="Logo Bank BCA" fetchpriority="high" loading="eager" width="0" height="0" decoding="async" data-nimg="1" class="w-full px-1" src="assets/img/bankdeposit.png" style="color: transparent;">
                                        </figure>
                                        <article class="flex-auto w-full lg:w-[calc(100%-44px)]">
                                            <p class="text-sm truncate"><?php echo $nama_rekening_anggota; ?></p>
                                            <p class="text-xs mt-[5px]">
                                                <span class="text-xs pr-1"><?php echo $bank_anggota_aktif; ?></span>-
                                                <span class="text-xs pl-1 pr-3"><?php echo htmlspecialchars(rtrim($nomor_rekening_anggota_aktif_sensored, '-')); ?></span>
                                            </p>
                                        </article>
                                    </div>
                                </div>
                                <div class="flex-none">
                                    <input type="hidden" name="asal_deposit" value="<?php echo $bank_anggota_aktif; ?> - <?php echo $nama_rekening_anggota; ?> - <?php echo $nomor_rekening_anggota_aktif; ?>">
                                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16">
                                        <path d="m11.808 14.77-3.715-4.458A.8.8 0 0 1 8.708 9h6.584a.8.8 0 0 1 .614 1.312l-3.714 4.458a.25.25 0 0 1-.384 0Z" fill="var(--base)"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div class="md:hidden fixed z-[1000] transition duration-300 ease-in-out w-0"></div>
                        <section class="fixed md:absolute z-[9999] md:z-[999] left-0 right-0 bottom-0 md:bottom-[unset] md:mt-2 lg:mt-1 overflow-hidden bg-background-secondary rounded-tl-3xl rounded-tr-3xl md:rounded-xl transition-all duration-300 ease-out max-h-0">
                            <div class="flex md:hidden justify-between px-4 pt-5">
                                <p class="lg:hidden font-medium">Select Account</p>
                                <button class="ml-auto"><svg width="24" height="24" viewbox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg" size="24">
                                        <path d="M18 6 6 18M6 6l12 12" stroke="var(--base)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                </button>
                            </div>
                            <div class="max-h-[70vh] md:max-h-[20vh] lg:max-h-60 px-4 pt-3 pb-6 overflow-auto">
                                <div class="group py-3 lg:pt-2 cursor-pointer lg:hover:bg-background-default lg:hover:rounded-lg transition-all duration-300 ease-out border-b border-separator last:border-transparent">
                                    <div class="flex items-center w-full gap-x-4">
                                        <figure class="flex flex-none w-11 h-11 items-center justify-center rounded-full bg-white">
                                            <img alt="Logo Bank " fetchpriority="high" loading="eager" width="0" height="0" decoding="async" data-nimg="1" class="w-full px-1" src="assets/img/BANK-LOGO.webp" style="color: transparent;">
                                        </figure>
                                        <article class="flex-auto w-full lg:w-[calc(100%-44px)]" name="asal_deposit">
                                            <p class="text-sm truncate"><?php echo $nama_rekening_anggota; ?></p>
                                            <p class="text-xs mt-[5px]">
                                                <span class="text-xs pr-1"><?php echo $bank_anggota_aktif; ?></span>-<span class="text-xs pl-1 pr-3"><?php echo htmlspecialchars(rtrim($nomor_rekening_anggota_aktif_sensored, '-')); ?></span>
                                            </p>
                                        </article>
                                    </div>
                                </div>
                        </section>
                </section>
            </div>
            <div class="px-4 lg:px-5 mt-2 border-b-4 border-background-secondary lg:border-background-default pb-2">
                <section class="relative">
                    <div class="px-3 py-2 my-4 rounded-xl cursor-pointer border border-caption lg:bg-background-default" onclick="toggleBankList()">
                        <label class="text-xs opacity-70">Select Transfer Destination</label>
                        <div class="flex items-center justify-between overflow-hidden my-0">
                            <div class="group pb-2 pt-1 lg:pt-2 cursor-pointer lg:hover:bg-background-default lg:hover:rounded-lg transition-all duration-300 ease-out">
                                <div class="flex items-center w-full gap-x-4">
                                    <figure class="flex flex-none w-11 h-11 items-center justify-center rounded-full bg-white">
                                        <img alt="Logo Bank" fetchpriority="high" loading="eager" width="0" height="0" decoding="async" data-nimg="1" class="w-full px-1" src="assets/img/bankdeposit.png" style="color: transparent;">
                                    </figure>
                                    <article class="flex-auto w-full lg:w-[calc(100%-44px)]">
                                        <p class="text-sm truncate" id="selectedBank">Pilih Bank Tujuan</p>
                                        <p class="text-xs mt-[5px]">
                                            <span class="text-xs pr-1"></span><span class="text-xs pl-1 pr-3"></span>
                                            <span class="text-[10px] rounded-full px-2 inline-block relative bg-success">
                                                <span class="relative z-30 text-white"></span>
                                                <span class="group-hover:animate-ping absolute z-20 inline-flex top-0 bottom-0 left-1 right-1 rounded-full bg-success opacity-90"></span>
                                            </span>
                                        </p>
                                    </article>
                                </div>
                            </div>
                            <div class="flex-none">
                                <svg width="25" height="25" viewbox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg" size="25">
                                    <path d="m11.808 14.77-3.715-4.458A.8.8 0 0 1 8.708 9h6.584a.8.8 0 0 1 .614 1.312l-3.714 4.458a.25.25 0 0 1-.384 0Z" fill="var(--base)"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <section id="bankList" class="hidden fixed md:absolute z-[9999] md:z-[999] left-0 right-0 bottom-0 md:bottom-[unset] md:mt-2 lg:mt-1 overflow-hidden bg-background-secondary rounded-tl-3xl rounded-tr-3xl md:rounded-xl transition-all duration-300 ease-out max-h-0">
                        <div class="flex md:hidden justify-between px-4 pt-5">
                            <p class="lg:hidden font-medium">Transfer Destination</p>
                            <button class="ml-auto" onclick="toggleBankList()">
                                <svg width="24" height="24" viewbox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg" size="24">
                                    <path d="M18 6 6 18M6 6l12 12" stroke="var(--base)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </button>
                        </div>
                        <div class="max-h-[70vh] md:max-h-[20vh] lg:max-h-60 px-4 pt-3 pb-6 overflow-auto">
                            <?php
                            $bank = mysqli_query($koneksi, "SELECT * FROM bank WHERE status_bank = 'aktif'");
                            while ($data_bank = mysqli_fetch_array($bank)) {
                                $id_bank = $data_bank['id_bank'];
                                $gambar_bank = $data_bank['gambar_bank'];
                                $jenis_bank = $data_bank['jenis_bank'];
                                $atas_nama_bank = $data_bank['atas_nama_bank'];
                                $nomor_rekening_bank = $data_bank['nomor_rekening_bank'];
                            ?>
                                <div class="group py-3 lg:pt-2 cursor-pointer lg:hover:bg-background-default lg:hover:rounded-lg transition-all duration-300 ease-out border-b border-separator last:border-transparent" onclick="selectBank('<?php echo strtoupper($jenis_bank); ?>', '<?php echo strtoupper($atas_nama_bank); ?>', '<?php echo $nomor_rekening_bank; ?>')">
                                    <div class="flex items-center w-full gap-x-4">
                                        <figure class="flex flex-none w-11 h-11 items-center justify-center rounded-full bg-white">
                                            <img alt="Logo Bank <?php echo $jenis_bank; ?>" id="<?php echo 'bank' . $id_bank; ?>" fetchpriority="high" loading="eager" width="0" height="0" decoding="async" data-nimg="1" class="w-full px-1" src="<?php echo $alamat_website . 'assets/img/bank_admin/' . $gambar_bank; ?>" style="color: transparent;">
                                        </figure>
                                        <article class="flex-auto w-full lg:w-[calc(100%-44px)]">
                                            <p class="text-sm truncate"><?php echo strtoupper($atas_nama_bank); ?></p>
                                            <p class="text-xs mt-[5px]">
                                                <span class="text-xs pr-1"><?php echo $jenis_bank; ?></span>-<span class="text-xs pl-1 pr-3"><?php echo $nomor_rekening_bank; ?> </span>
                                                <span class="text-[10px] rounded-full px-2 inline-block relative bg-success">
                                                    <span class="relative z-30 text-white">ONLINE</span>
                                                    <span class="group-hover:animate-ping absolute z-20 inline-flex top-0 bottom-0 left-1 right-1 rounded-full bg-success opacity-90"></span>
                                                </span>
                                            </p>
                                        </article>
                                    </div>
                                </div>
                            <?php
                            }
                            ?>
                        </div>
                    </section>
            </div>
            <input type="hidden" id="selectedBankField" name="tujuan_deposit">

            <script>
                function toggleBankList() {
                    const bankList = document.getElementById('bankList');
                    if (bankList.classList.contains('hidden')) {
                        bankList.classList.remove('hidden');
                        bankList.style.maxHeight = '70vh'; // Adjust the height as needed
                    } else {
                        bankList.classList.add('hidden');
                        bankList.style.maxHeight = '0';
                    }
                }

                function selectBank(jenisBank, atasNamaBank, nomorRekening) {
                    const selectedBank = document.getElementById('selectedBank');
                    selectedBank.innerHTML = `${atasNamaBank}<br>${jenisBank} - ${nomorRekening} <span class="text-[10px] rounded-full px-2 inline-block relative bg-success"><span class="relative z-30 text-white">ONLINE</span><span class="group-hover:animate-ping absolute z-20 inline-flex top-0 bottom-0 left-1 right-1 rounded-full bg-success opacity-90"></span></span>`;
                    toggleBankList();

                    const bankField = document.getElementById('selectedBankField');
                    if (bankField) {
                        bankField.value = `${jenisBank} - ${atasNamaBank} - ${nomorRekening}`;
                    }
                }
            </script>

            <div id="deposit-nominal-section" class="px-4 lg:px-5 mt-4 border-b-8 border-background-secondary lg:border-background-default pb-5">
                <p class="text-sm">Type Deposit Amount</p>
                <div class="relative">
                    <section class="mt-3 relative">
                        <div class="relative mt-4 lg:mt-5 rounded-xl group border lg:bg-background-default border-caption focus-within:border-primary focus-within:ring-1">
                            <div class="relative flex items-center top-0 pt-3 px-3 ">
                                <label class="text-xs opacity-70 bg-background-default rounded-full"> type the amount here</label>
                            </div>
                            <div class="relative">
                                <input id="amount-input" inputmode="numeric" name="jumlah_deposit" maxlength="16" class="px-3 pt-2 pb-3 focus:border-transparent focus:ring-0 text-sm lg:text-base w-full rounded-lg border bg-transparent border-transparent focus:outline-none" placeholder="Minimum deposit IDR 50.000" type="text" value="" required>
                            </div>
                        </div>
                        <div class="flex justify-between mt-2">
                            <p class="text-error text-sm" id="amount-error"></p>
                        </div>
                    </section>
                </div>
                <section id="add-promo-button" class="border border-separator flex justify-between rounded-xl p-3 mt-2 w-full">
                    <div class="flex items-center">
                        <figure class="flex flex-none items-center">
                            <img alt="Deposit promo take icon" width="0" height="0" decoding="async" data-nimg="1" class="w-5 lg:w-7 h-auto" src="data:image/webp;base64,UklGRo4FAABXRUJQVlA4WAoAAAAQAAAAXAAAWwAAQUxQSC8CAAANkFXbet5GCoKqCCoG0SCoGTRBYBtBw0AxgnYQJEaQFMEkDMSgDgMXwSz/6n6f7ltfIoKB20ZSuo24oSzL7B+MarPVm7cmxqYzhO39MLuT9+FGuBKeVyJUZd2xsVkN4U6D6UiaDMBUQCYCNA2wSRBauQDs3HqK7ZgQ9yU+VtXH21KCbvC67/+ynsK75iytLcIvJ++R4PedqLbkHBjzivex72EVxWzY5d5ZwzpR/DC7Hr19wfKrN+jLzpX/nA48vM2p9YdaeBpg8XDxr0rrjGOmNqfcvn9P+rMFMfVgxToK6Xyf9LaDmM4bjcSP2ASgfXRxp4sF8rcdwkaZRYASqsaXusuNLK07DaYBdhI032ygfEsQGFlHRkAhXReZ4KjQ2ZE6N2JP51X5EHsagkd9dHv5NRNqVZIJqAJRk2ldM6vLsK7aEdZSH21L6is+K6t7N/UB0gZWutniJyiiPqqnwrMJrCzNyZrV6fcz/ROxbo31hOJMhYSU2Y6mzUXaz++ArvlAfABKxOGpmaNbDZpFmNTY6B3yov8bFPshqgigh+KGeVyHtDP6DftIr4a1LX6hX5b286QvtHqhlc6RKE5CZg2SIviFxrmR1K9POE8TGHQeAsGLu67R8dJKJo2NmgXd0gyhJG6PZdYHNlHance79+sqneglro/1FDFqfK1UtrYKgoR7vWEn+/4w9fDDcBiHxSg8xmAyApfTsDkFmx/BLaeEdG2hKLN/femt6b6uakkNAFZQOCA4AwAAUBQAnQEqXQBcAD4xFIlDIiEhF4h9RCADBLQAY5bmf27skLM8k/Jj8u+sc3d8CfjTmH/HH+u+533m/6T+Ae4DzAP0U6QH8j9AH6o/7f/ge8l/jv8B/APcB6AH8t/rvpAexF6AH7C+kj/3P8R8C37S/uN7Nn/o6wDqn+AH0ABOfWbbJ+NpGsqXPMx+2MULPShndV+uRdM4It6fVziwfVZGmxCcRATPkfSkBLAAAP7/k4qn/nipAB++ujp6xJyvujbmYm7jT/d2FDmRxg8Q6VmtkmPZ5bSB/Zv/kKWbazlj9oHHXAZTd3pznFdvS4Th47u0x982j1GdL/FsBzQ5dMqWgOKxChjXXUPmYJf5bn7RI///0vi58OdnJrDgO5uL+VmHjq6RqRL8IzRejCq+O2Gr3XQ71Ytk04i43fRMhqvYmZ9o+G33sRm/Db7zroWYASvq86/unbAsnvyvV80rIc0yF8dIyMDo+cn9xCzf1C0BzwGU83YL11fkXHTvIaCmdOo5LxGtN5x9RPwgEY4AlBSk0CSu3co4LXfySxHlGloi/+wcRJm7BVsPMMPcIrWzkaFKGQJjrld9slHs38aEwGeCHc1apKTpgDhe/EfeOSyqPqTrhe/EXwgRrALyA1R+A+uaTxP328LXhkvFf+TifTJ9ot7ieXsaUBbt5d4rL1YbF8MdFa/Az4Rs3RwHY/0FNtXDJDD3CK1s5GhShkAPD+u/2//6/GhMBnghhJFwT7M2Q4XvxHwCEN8RdRvETZYKSxT0NsUkOXI3Dv3d7aF+1PYs82koRUxOiKcZ1npy4RsC7qxPBmFOlgCPmAbDAXu9Ky4sbGTPAQr8za+uGa7t+2o0EWkWKsWNScIQ0N155IrK7msAAAEt/5WLemU3BR0rI06Qi4GCjj+Ni0BraG5MN3+oq7dFUC10X/dXAKpizp9RB+PTjOroTHnreUArV2MGj0YHiFurMK/ZDMYFVlfZ1pynBVbvex6j8r4NhxwCUIEA4L+bd4f4bP9NDttoUXoQSTO3pWB5+IecfDUrKLvLV/5WN2OsvGgChHUR2L7QhiDb96quG3JzQDBg90WwD/+8D856mY8yONnqAAA=" style="color: transparent;">
                        </figure>
                        <div class="pl-2 lg:pl-3">
                            <span class="text-xs block text-left">Add Promo</span>
                        </div>
                    </div>
                    <figure class="w-5 h-5 flex-none">
                        <svg width="100%" height="100%" viewbox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg">
                            <path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="var(--base)"></path>
                        </svg>
                    </figure>
                </section>

                <section id="promo-modal" class="fixed z-[9999] top-0 bottom-0 left-0 right-0 overflow-hidden transition duration-500 ease-in-out h-[calc(100vh-122px)] lg:h-[90vh] md:w-4/5 lg:w-[450px] md:m-auto bg-background-tertiary lg:rounded-lg mt-auto hidden">
                    <button id="close-modal-button" type="button" class="w-full bg-background-secondary p-3 md:p-5 lg:p-3">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg" size="24">
                            <path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="var(--base)"></path>
                        </svg>
                        <span class="pl-2">Back</span>
                    </button>
                    <div class="h-[90%] lg:h-[93%] px-3 md:px-5 lg:px-3 overflow-auto">
                        <?php
                        $bonus = mysqli_query($koneksi, "SELECT * FROM bonus");
                        if (mysqli_num_rows($bonus) > 0) {
                            while ($data_bonus = mysqli_fetch_array($bonus)) {
                                $id_bonus = $data_bonus['id_bonus'];
                                $judul_bonus = $data_bonus['judul_bonus'];
                                $tanggal_berakhir = $data_bonus['tanggal_berakhir'] ?? 'N/A'; // Menambahkan baris ini
                        ?>
                                <div class="flex flex-wrap items-center md:justify-between bg-background-default hover:bg-background-secondary py-3 md:py-5 rounded-lg mt-3 cursor-pointer transition duration-500 ease-in-out" required>
                                    <div class="flex w-full mb-1 -mt-1">
                                        <span class="text-[10px] font-medium rounded-e-full bg-success px-2 mr-auto">
                                            <span class="animate-ping absolute z-20 inline-flex h-3 w-8 rounded-e-full bg-success opacity-90"></span>
                                            New
                                        </span>
                                        <span class="text-[10px] font-medium rounded-s-full bg-separator px-2 ml-auto">Remaining claims 3X</span>
                                    </div>
                                    <figure class="w-9/12 md:w-8/12 lg:w-4/5 flex items-center pl-3">
                                        <img alt="<?php echo htmlspecialchars($judul_bonus); ?> promo image" loading="lazy" width="30" height="30" decoding="async" data-nimg="1" class="h-auto w-8 lg:w-10" src="data:image/webp;base64,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">
                                        <article class="px-3 lg:px-4 lg:w-[calc(100%-40px)]">
                                            <p class="text-xs lg:text-sm font-semibold max-h-4 lg:max-h-5 overflow-hidden"><?php echo htmlspecialchars($judul_bonus); ?></p>
                                            <p class="text-[10px] lg:text-xs lg:mt-1 opacity-80">Valid</p>
                                        </article>
                                    </figure>
                                    <div class="w-3/12 md:w-2/12 lg:w-1/5 pr-3">
                                        <button class="claim-button text-xs py-1 lg:py-2 w-full justify-center rounded-full bg-primary hover:brightness-90 transition duration-300 ease-in-out" data-id="<?php echo $id_bonus; ?>" data-title="<?php echo htmlspecialchars($judul_bonus); ?>" data-expiry="<?php echo htmlspecialchars($tanggal_berakhir); ?>">Claim</button>
                                    </div>
                                </div>
                            <?php
                            }
                        } else {
                            ?>
                            <p class="text-center">Tidak ada data bonus</p>
                        <?php
                        }
                        ?>
                    </div>
            </div>
            <input type="hidden" id="selectedBonusField" name="bonus_deposit">
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    document.querySelectorAll('.claim-button').forEach(button => {
                        button.addEventListener('click', function() {
                            const bonusTitle = this.getAttribute('data-title');

                            document.getElementById('selectedBonusField').value = bonusTitle;

                        });
                    });
                });
            </script>
            <script>
                // Kode ini tidak digunakan dan bisa dihapus.
                // Logika utamanya sudah di-handle oleh event listener di atas.
                document.querySelectorAll('.claim-button').forEach(function(button) {
                    button.addEventListener('click', function(event) {
                        event.preventDefault();
                        var idBonus = this.getAttribute('data-id');
                        var title = this.getAttribute('data-title');
                        var expiry = this.getAttribute('data-expiry');
                        console.log('ID Bonus:', idBonus);
                        console.log('Title:', title);
                        console.log('Expiry:', expiry);
                    });
                });
            </script>
            <script>
                // Kode ini tidak digunakan dan bisa dihapus.
                document.getElementById('close-modal-button').addEventListener('click', function(event) {
                    event.preventDefault();
                });
            </script>
            <div class="px-4 lg:px-5 mt-5 lg:pb-4">
                <div class="relative mt-4 lg:mt-5 rounded-xl group border lg:bg-background-default border-caption focus-within:border-primary focus-within:ring-1">
                    <div class="relative flex items-center top-0 pt-3 px-3 ">
                        <label class="text-xs opacity-70 bg-background-default rounded-full ">Add note</label>
                    </div>
                    <div class="relative">
                        <input label="Add note" placeholder="(Optional)" class="px-3 pt-2 pb-3 focus:border-transparent focus:ring-0 text-sm lg:text-base w-full rounded-lg border bg-transparent border-transparent focus:outline-none" type="text" value="" name="kode_deposit">
                    </div>
                </div>
            </div>
        </div>
    </section>
    <div id="deposit-summary" class="fixed rounded-t-3xl px-4 lg:px-5 pt-6 lg:pt-5 pb-8 lg:pb-5 lg:mx-4 lg:mt-2 lg:mb-20 lg:rounded-none lg:rounded-b-lg shadow-[0px_4px_10px_6px_black] lg:shadow-none lg:relative left-0 right-0 bottom-0 z-[999] bg-background-tertiary lg:bg-background-secondary">
        <div class="flex">
            <p class="text-sm font-medium mr-2">Transfer Amount</p>
            <button>
                <figure class="w-5 h-5 rotate-270 transition-all duration-300 ease-in-out"><svg width="100%" height="100%" viewBox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg">
                        <path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="var(--base)"></path>
                    </svg>
                </figure>
            </button>
        </div>
        <div class="max-h-[0px] transition-all duration-300 ease-in-out overflow-hidden overflow-y-scroll">
            <div class="flex justify-between mt-3">
                <p class="text-sm opacity-50 font-light">Fee deduction</p>
                <p id="fee-deduction" class="text-sm font-medium">IDR&nbsp;</p>
            </div>
            <div class="flex justify-between mt-3">
                <p class="text-sm opacity-50 font-light">Estimated balance received</p>
                <p id="estimated-balance" class="text-sm font-medium">IDR&nbsp;</p>
            </div>
        </div>
        <div class="flex mt-5 lg:mt-3 items-center">
            <p id="total-amount" class="w-7/12 text-xl font-semibold">IDR&nbsp;</p>
            <button type="submit" name="submitdeposit" value="Kirim" class="w-5/12 justify-center rounded-xl py-3 lg:mx-auto bg-primary text-white transition-all duration-200 ease-in-out hover:lg:brightness-[0.9]">Transfer</button>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const amountInput = document.getElementById('amount-input');
            const feeDeduction = document.getElementById('fee-deduction');
            const estimatedBalance = document.getElementById('estimated-balance');
            const totalAmount = document.getElementById('total-amount');

            function formatRupiah(angka) {
                let numberString = angka.replace(/[^,\d]/g, '').toString();
                let split = numberString.split(',');
                let sisa = split[0].length % 3;
                let rupiah = split[0].substr(0, sisa);
                let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

                if (ribuan) {
                    let separator = sisa ? '.' : '';
                    rupiah += separator + ribuan.join('.');
                }

                rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
                return 'IDR ' + rupiah;
            }

            function updateAmounts() {
                const amount = amountInput.value;
                const formattedAmount = formatRupiah(amount);
                feeDeduction.textContent = formattedAmount;
                estimatedBalance.textContent = formattedAmount;
                totalAmount.textContent = formattedAmount;
            }

            amountInput.addEventListener('input', updateAmounts);

            updateAmounts();
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const addPromoButton = document.getElementById('add-promo-button');
            const promoModal = document.getElementById('promo-modal');
            const closeModalButton = document.getElementById('close-modal-button');

            addPromoButton.addEventListener('click', () => {
                promoModal.classList.remove('hidden');
            });

            closeModalButton.addEventListener('click', () => {
                promoModal.classList.add('hidden');
            });

            document.querySelectorAll('.claim-button').forEach(button => {
                button.addEventListener('click', (event) => {
                    const bonusTitle = event.target.getAttribute('data-title');

                    addPromoButton.innerHTML = `
                                    <div class="flex items-center">
                                        <figure class="flex flex-none items-center">
                                            <img alt="Deposit promo take icon" width="0" height="0" decoding="async" data-nimg="1" class="w-5 lg:w-7 h-auto" src="data:image/webp;base64,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">
                                        </figure>
                                        <div class="pl-2 lg:pl-3">
                                            <span class="text-xs block text-left" name="bonus_deposit">${bonusTitle}</span>
                                        </div>
                                    </div>
                                    <figure class="w-5 h-5 flex-none">
                                        <svg width="100%" height="100%" viewbox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg">
                                            <path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="var(--base)"></path>
                                        </svg>
                                    </figure>
                                `;

                    promoModal.classList.add('hidden');
                });
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const amountInput = document.getElementById('amount-input');
            amountInput.addEventListener('input', function() {
                // Menghapus karakter non-angka
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        });
    </script>
<?php endif; ?>