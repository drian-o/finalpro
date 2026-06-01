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
<section class="container flex mx-auto lg:pt-3 lg:pb-5 lg:mt-5">
    <div class="w-full lg:w-1/3 px-3 hidden lg:block">
        <a class="px-3 py-4 bg-background-default lg:bg-background-secondary rounded-xl mt-4 drop-shadow-[0_0_5px_rgba(0,0,0,0.6)] lg:drop-shadow-none group transition-all duration-300 ease-in-out lg:hover:bg-black/30" href="#">
            <figure class="flex flex-none items-center justify-center w-12 md:w-16 h-12 md:h-16">
                <img alt="VIP Level Badge" width="0" height="0" decoding="async" data-nimg="1" class="w-full" style="color: transparent;" loading="lazy" src="https://cdn.databerjalan.com/assets/images/store/2024-07-11T06:15:28.250Z_Pemain_Baru_1.png">
            </figure>
            <article class="w-full pl-4">
                <p class="text-sm md:text-base group-hover:text-white">Pemain Baru</p>
                <progress class="w-full h-[5px] primary-progress" value="0" max="100"></progress>
                <span class="text-xs md:text-sm group-hover:text-white">Increase your level and get rewards</span>
            </article>
            <figure class="pl-2">
                <svg width="26" height="26" viewbox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg" size="26">
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
        <section class="bg-background-secondary rounded-xl mt-4 overflow-hidden">
            <a class="justify-between px-4 py-3 hover:lg:bg-background-tertiary transition-all duration-300 ease-in-out" href="<?php echo $alamat_website . 'my-account'; ?>">
                <div class="flex items-center w-[calc(100%-24px)] pr-1">
                    <svg width="24" height="24" viewbox="0 0 24 24" fill="var(--primary)" xmlns="http://www.w3.org/2000/svg" size="24">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M2.002 10h19.996c-.012-2.175-.108-3.353-.877-4.121C20.243 5 18.828 5 16 5H8c-2.828 0-4.243 0-5.121.879-.769.768-.865 1.946-.877 4.121ZM22 12H2v2c0 2.828 0 4.243.879 5.121C3.757 20 5.172 20 8 20h8c2.828 0 4.243 0 5.121-.879C22 18.243 22 16.828 22 14v-2ZM7 15a1 1 0 1 0 0 2h.01a1 1 0 1 0 0-2H7Z" fill="var(--primary)"></path>
                    </svg>
                    <span class="text-sm pl-2 undefined text-primary">My Account</span>
                </div>
                <figure class="w-6"><svg width="22" height="22" viewbox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg" size="22">
                        <path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="var(--base)"></path>
                    </svg>
                </figure>
            </a>
        </section>
        <section class="bg-background-secondary rounded-xl mt-4 overflow-hidden">
            <a class="justify-between px-4 py-3 hover:lg:bg-background-tertiary transition-all duration-300 ease-in-out" href="<?php echo $alamat_website . 'change-pasword'; ?>">
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
            <!--$-->
            <!--/$-->
    </div>
    </a>
    <div class="w-full lg:w-2/3 lg:px-5">
        <div class="px-2 my-2 lg:hidden">
            <a href="<?php echo $alamat_website . 'home'; ?>">
                <figure class="h-auto w-6 rotate-180">
                    <svg width="100%" height="100%" viewbox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg">
                        <path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="var(--base)"></path>
                    </svg>
                </figure><span class="text-sm">Back</span>
            </a>
        </div>
        <div class="grid lg:flex lg:gap-x-6 grid-cols-2 lg:mb-3">
            <a aria-label="My Profile-tab-button" aria-labelledby="My Profile-tab-button" class="text-sm border-b uppercase flex-none px-1 pb-2 justify-center cursor-pointer transition-all duration-300 ease-in-out hover:lg:border-primary hover:lg:text-inverse font-semibold border-primary " href="">My Profile</a>
            <a aria-label="Change Password-tab-button" aria-labelledby="Change Password-tab-button" class="text-sm border-b uppercase flex-none px-1 pb-2 justify-center cursor-pointer transition-all duration-300 ease-in-out hover:lg:border-primary hover:lg:text-inverse font-semibold border-transparent text-caption " href="<?php echo $alamat_website . 'change-pasword'; ?>">Change Password</a>
        </div>
        <section class="pt-5 pb-3 lg:pb-10 px-4 bg-background-tertiary lg:rounded-xl min-h-[82vh] lg:min-h-min sm:relative sm:z-10">
<div class="lg:w-1/2 lg:mx-auto">
<div class="w-16 lg:w-20 h-16 lg:h-20 mx-auto flex justify-center items-center rounded-full bg-background-secondary border border-base">
<p class="text-4xl font-bold text-center leading-[48px]"><?php echo $inisial; ?></p></div>
<form class="mt-8">
<div class="relative mt-4 lg:mt-5 rounded-xl group border border-separator focus-within:border-primary focus-within:ring-1"><div class="absolute left-0 -top-4 lg:-top-[14px] mx-2 z-20 bg-transparent bg-background-tertiary">
<label class="text-[10px] lg:text-xs opacity-100  bg-background-tertiary ">Username</label></div>
<div class="relative">
<input readonly="" label="Username" disabled="" class="p-3 text-sm lg:text-base w-full rounded-lg border bg-transparent border-transparent focus:outline-none" type="text" value="<?php echo $nama_pengguna_anggota; ?>">
</div>
</div>
<div class="relative mt-4 lg:mt-5 rounded-xl group border border-separator focus-within:border-primary focus-within:ring-1"><div class="absolute left-0 -top-4 lg:-top-[14px] mx-2 z-20 bg-transparent ">
<label class="text-[10px] lg:text-xs opacity-100  bg-background-tertiary ">Nama Lengkap</label></div>
<div class="relative">
<input label="Nama Lengkap" class="p-3 text-sm lg:text-base w-full rounded-lg border bg-background-default rounded-xl border-transparent focus:outline-none" type="text" value="<?php echo $nama_rekening_anggota; ?>"></div></div><div class="relative mt-4 lg:mt-5 rounded-xl group border border-separator focus-within:border-primary focus-within:ring-1"><div class="absolute left-0 -top-4 lg:-top-[14px] mx-2 z-20 bg-transparent bg-background-tertiary"><label class="text-[10px] lg:text-xs opacity-100  bg-background-tertiary ">Alamat Email</label></div>
<div class="relative"><input readonly="" label="Alamat Email" disabled="" class="p-3 text-sm lg:text-base w-full rounded-lg border bg-transparent border-transparent focus:outline-none" type="email" value="<?php echo $email_anggota; ?>"></div></div><div class="flex -mx-1">
<div class="w-4/12 lg:w-5/12 px-1"><div class="relative mt-4 lg:mt-5 rounded-xl group border border-separator focus-within:border-primary focus-within:ring-1"><div class="absolute left-0 -top-4 lg:-top-[14px] mx-2 z-20 bg-transparent bg-background-tertiary"><label class="text-[10px] lg:text-xs opacity-100  bg-background-tertiary ">Kode Negara</label></div><div class="relative">
<input readonly="" label="Kode Negara" disabled="" class="p-3 text-sm lg:text-base w-full rounded-lg border bg-transparent border-transparent focus:outline-none" value="+62">
<span class="absolute px-2 flex items-center rounded-md opacity-70 cursor-pointer right-[1px] top-[1px] bottom-[1px]">
<svg width="24" height="24" viewBox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg" size="24">
<path d="m11.808 14.77-3.715-4.458A.8.8 0 0 1 8.708 9h6.584a.8.8 0 0 1 .614 1.312l-3.714 4.458a.25.25 0 0 1-.384 0Z" fill="var(--base)"></path></svg></span></div></div></div><div class="w-8/12 lg:w-9/12 px-1"><div class="relative mt-4 lg:mt-5 rounded-xl group border border-separator focus-within:border-primary focus-within:ring-1"><div class="absolute left-0 -top-4 lg:-top-[14px] mx-2 z-20 bg-transparent bg-background-tertiary"></div><div class="relative"><input readonly="" label="" disabled="" class="p-3 text-sm lg:text-base w-full rounded-lg border bg-transparent border-transparent focus:outline-none" type="number" value="6282374156088"></div></div></div></div><div class="flex justify-center my-5"><button type="submit" aria-label="Save profile button" aria-labelledby="Save profile button" class="bg-primary text-white lg:hover:brightness-95 rounded-xl text-sm lg:text-base font-semibold w-3/4 lg:w-1/2 justify-center py-3">Simpan</button></div></form></div>
</section>