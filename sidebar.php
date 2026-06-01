<aside class="sidebar">
    <div class="flex justify-between items-center px-4 mt-4 h-[64px]">
        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) : ?>
            <div class="flex">
                <div class="flex-none w-10 md:w-12 h-10 md:h-12 flex items-center justify-center rounded-full bg-background-secondary border border-base">
                    <p class="text-xl md:text-3xl font-bold"><?php echo htmlspecialchars($inisial); ?></p>
                </div>
                <div class="px-3 flex">
                    <div class="flex items-center">
                        <div>
                            <p class="text-xs truncate"><?php echo htmlspecialchars($nama_pengguna); ?></p>
                            <span class="text-sm font-medium flex items-center h-6" id="saldo-display">IDR <?php echo htmlspecialchars($saldo_anggota_formatted); ?>
                                <button class="ml-3 w-6 h-6 items-center justify-center rotate-270 rounded-full bg-background-secondary flex" onclick="updateSaldoDisplay();">
                                    <svg width="18" height="18" viewbox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="m10 19-.707-.707-.707.707.707.707L10 19Zm3.293-4.707-4 4 1.414 1.414 4-4-1.414-1.414Zm-4 5.414 4 4 1.414-1.414-4-4-1.414 1.414Z" fill="var(--caption)"></path>
                                        <path d="M5.938 15.5A7 7 0 1 1 12 19" stroke="var(--caption)" stroke-width="2" stroke-linecap="round"></path>
                                    </svg>
                                </button>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        <?php else : ?>
            <div class="flex items-center">
                <a href="<?php echo htmlspecialchars($alamat_website . 'home'); ?>">
                    <img alt="Logo Brand" loading="lazy" width="0" height="0" decoding="async" data-nimg="1" class="h-6 lg:h-8 w-auto" style="color: transparent;" src="<?php echo htmlspecialchars($alamat_website . 'assets/img/' . $isi_1_logo_web); ?>">
                </a>
            </div>
        <?php endif; ?>

        <button class="close-btn" aria-label="Close Sidebar">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" size="24">
                <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
        </button>
    </div>

    <div id="sidebar-navigation-options" class="mt-3 landscape:h-[calc(100%-120px)] landscape:overflow-auto">
        <a class="flex justify-between px-4 py-3 hover:lg:bg-background-tertiary transition-all duration-300 ease-in-out" href="<?php echo htmlspecialchars($alamat_website . 'home'); ?>">
            <div class="flex items-center w-[calc(100%-24px)] pr-1">
                <svg width="24" height="24" viewbox="0 0 24 24" fill="var(--primary)" xmlns="http://www.w3.org/2000/svg" size="24">
                    <path d="M21.462 13.303c-.348.348-.81.54-1.302.54h-.302v6.004a2.157 2.157 0 0 1-2.155 2.155h-3.778v-5.294a.984.984 0 0 0-.984-.983H11.06a.984.984 0 0 0-.984.983v5.294H6.297a2.158 2.158 0 0 1-2.155-2.155v-6.005h-.325c-.02 0-.038 0-.057-.002a1.843 1.843 0 0 1-1.225-3.137l.008-.009 8.155-8.155A1.83 1.83 0 0 1 12 2c.492 0 .954.193 1.302.54l8.16 8.16a1.844 1.844 0 0 1 0 2.604Z" fill="var(--primary)"></path>
                </svg>
                <span class="text-sm pl-2 text-primary">Beranda</span>
            </div>
            <figure class="w-6">
                <svg width="22" height="22" viewbox="0 0 24 24" fill="var(--primary)" xmlns="http://www.w3.org/2000/svg" size="22">
                    <path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="var(--primary)"></path>
                </svg>
            </figure>
        </a>
        <a class="flex justify-between px-4 py-3 hover:lg:bg-background-tertiary transition-all duration-300 ease-in-out" href="<?php echo htmlspecialchars($alamat_website . 'promo'); ?>">
            <div class="flex items-center w-[calc(100%-24px)] pr-1">
                <svg width="24" height="24" viewbox="0 0 25 25" fill="var(--base)" xmlns="http://www.w3.org/2000/svg" size="24">
                    <g fill="var(--base)">
                        <path d="M17.011 14.523a.718.718 0 0 0-.716.716c0 .393.323.715.716.715a.718.718 0 0 0 .716-.715.718.718 0 0 0-.716-.716Zm0 0a.718.718 0 0 0-.716.716c0 .393.323.715.716.715a.718.718 0 0 0 .716-.715.718.718 0 0 0-.716-.716Zm0 0a.718.718 0 0 0-.716.716c0 .393.323.715.716.715a.718.718 0 0 0 .716-.715.718.718 0 0 0-.716-.716Zm0 0a.718.718 0 0 0-.716.716c0 .393.323.715.716.715a.718.718 0 0 0 .716-.715.718.718 0 0 0-.716-.716Zm0 0a.718.718 0 0 0-.716.716c0 .393.323.715.716.715a.718.718 0 0 0 .716-.715.718.718 0 0 0-.716-.716Zm0 0a.718.718 0 0 0-.716.716c0 .393.323.715.716.715a.718.718 0 0 0 .716-.715.718.718 0 0 0-.716-.716Zm0 0a.718.718 0 0 0-.716.716c0 .393.323.715.716.715a.718.718 0 0 0 .716-.715.718.718 0 0 0-.716-.716Zm0 0a.718.718 0 0 0-.716.716c0 .393.323.715.716.715a.718.718 0 0 0 .716-.715.718.718 0 0 0-.716-.716Zm0 0a.718.718 0 0 0-.716.716c0 .393.323.715.716.715a.718.718 0 0 0 .716-.715.718.718 0 0 0-.716-.716Zm0 0a.718.718 0 0 0-.716.716c0 .393.323.715.716.715a.718.718 0 0 0 .716-.715.718.718 0 0 0-.716-.716ZM22.023 4.5H8.42a.723.723 0 0 0-.507.209l-.924.927-.925-.927a.722.722 0 0 0-.507-.209h-3.58c-.789 0-1.432.643-1.432 1.432v12.886c0 .79.643 1.432 1.432 1.432h3.58c.19 0 .372-.076.507-.209l.925-.927.924.926c.135.134.317.21.507.21h13.603c.79 0 1.431-.642 1.431-1.432V5.932c0-.789-.64-1.432-1.431-1.432ZM7.704 17.386H6.273v-1.431h1.432v1.431Zm0-2.863H6.274V13.09h1.432v1.432Zm0-2.864H6.274v-1.432h1.432v1.432Zm0-2.864H6.274V7.364h1.432v1.431Zm5.012-1.431a2.15 2.15 0 0 1 2.148 2.147 2.15 2.15 0 0 1-2.148 2.148 2.15 2.15 0 0 1-2.148-2.148 2.15 2.15 0 0 1 2.148-2.147Zm-1.432 10.022a.716.716 0 0 1-.55-1.174l7.16-8.59a.717.717 0 0 1 1.099.918l-7.16 8.59a.715.715 0 0 1-.549.256Zm5.727 0a2.15 2.15 0 0 1-2.147-2.147 2.15 2.15 0 0 1 2.147-2.148 2.15 2.15 0 0 1 2.148 2.148 2.15 2.15 0 0 1-2.148 2.147Zm0-2.863a.718.718 0 0 0-.716.716c0 .393.323.715.716.715a.718.718 0 0 0 .716-.715.718.718 0 0 0-.716-.716Zm-4.295-4.296a.716.716 0 1 0 0-1.432.716.716 0 0 0 0 1.432Zm4.295 4.296a.718.718 0 0 0-.716.716c0 .393.323.715.716.715a.718.718 0 0 0 .716-.715.718.718 0 0 0-.716-.716Zm0 0a.718.718 0 0 0-.716.716c0 .393.323.715.716.715a.718.718 0 0 0 .716-.715.718.718 0 0 0-.716-.716Zm0 0a.718.718 0 0 0-.716.716c0 .393.323.715.716.715a.718.718 0 0 0 .716-.715.718.718 0 0 0-.716-.716Zm0 0a.718.718 0 0 0-.716.716c0 .393.323.715.716.715a.718.718 0 0 0 .716-.715.718.718 0 0 0-.716-.716Zm0 0a.718.718 0 0 0-.716.716c0 .393.323.715.716.715a.718.718 0 0 0 .716-.715.718.718 0 0 0-.716-.716Zm0 0a.718.718 0 0 0-.716.716c0 .393.323.715.716.715a.718.718 0 0 0 .716-.715.718.718 0 0 0-.716-.716Zm0 0a.718.718 0 0 0-.716.716c0 .393.323.715.716.715a.718.718 0 0 0 .716-.715.718.718 0 0 0-.716-.716Zm0 0a.718.718 0 0 0-.716.716c0 .393.323.715.716.715a.718.718 0 0 0 .716-.715.718.718 0 0 0-.716-.716Zm0 0a.718.718 0 0 0-.716.716c0 .393.323.715.716.715a.718.718 0 0 0 .716-.715.718.718 0 0 0-.716-.716Z"></path>
                        <path d="M6.273 7.364v1.431h.716v1.432h-.716v1.432h.716v1.432h-.716v1.432h.716v1.431h-.716v1.432h.716v1.728l-.925.927a.722.722 0 0 1-.507.209h-3.58a1.433 1.433 0 0 1-1.432-1.432V5.932c0-.789.643-1.432 1.432-1.432h3.58c.19 0 .372.076.507.209l.925.927v1.728h-.716Z"></path>
                    </g>
                </svg>
                <span class="text-sm pl-2">Promosi</span>
            </div>
            <figure class="w-6">
                <svg width="22" height="22" viewbox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg" size="22">
                    <path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="var(--base)"></path>
                </svg>
            </figure>
        </a>
        <a class="flex justify-between px-4 py-3 hover:lg:bg-background-tertiary transition-all duration-300 ease-in-out" href="<?php echo htmlspecialchars($alamat_website . 'about'); ?>">
            <div class="flex items-center w-[calc(100%-24px)] pr-1">
                <svg width="24" height="24" viewbox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg" size="24">
                    <path d="M16 3v3M8 3v3" stroke="var(--base)" stroke-width="2" stroke-linecap="round"></path>
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M14 4h-4v2a2 2 0 1 1-4 0V4.076c-.975.096-1.631.313-2.121.803C3 5.757 3 7.172 3 10v5c0 2.828 0 4.243.879 5.121C4.757 21 6.172 21 9 21h6c2.828 0 4.243 0 5.121-.879C21 19.243 21 17.828 21 15v-5c0-2.828 0-4.243-.879-5.121-.49-.49-1.146-.707-2.121-.803V6a2 2 0 1 1-4 0V4Zm-7 8a1 1 0 0 1 1-1h8a1 1 0 1 1 0 2H8a1 1 0 0 1-1-1Zm1 3a1 1 0 1 0 0 2h8a1 1 0 1 0 0-2H8Z" fill="var(--base)"></path>
                </svg>
                <span class="text-sm pl-2">Tentang Kami</span>
            </div>
            <figure class="w-6">
                <svg width="22" height="22" viewbox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg" size="22">
                    <path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="var(--base)"></path>
                </svg>
            </figure>
        </a>
        <a class="flex justify-between px-4 py-3 hover:lg:bg-background-tertiary transition-all duration-300 ease-in-out" href="<?php echo htmlspecialchars($alamat_website . 'referal'); ?>">
            <div class="flex items-center w-[calc(100%-24px)] pr-1">
                <svg width="24" height="24" viewbox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg" size="24">
                    <circle cx="10" cy="8" r="5" fill="var(--base)"></circle>
                    <path d="M19 10v6M22 13h-6" stroke="var(--base)" stroke-width="2" stroke-linecap="round"></path>
                    <path d="M17.142 20.383c.462-.105.739-.585.534-1.012-.552-1.15-1.459-2.162-2.634-2.924C13.595 15.508 11.823 15 10 15s-3.595.508-5.042 1.447c-1.175.762-2.082 1.773-2.634 2.924-.205.427.072.907.534 1.012a32.333 32.333 0 0 0 14.284 0Z" fill="var(--base)"></path>
                </svg>
                <span class="text-sm pl-2">Referral</span>
            </div>
            <figure class="w-6">
                <svg width="22" height="22" viewbox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg" size="22">
                    <path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="var(--base)"></path>
                </svg>
            </figure>
        </a>
        <a class="flex justify-between px-4 py-3 hover:lg:bg-background-tertiary transition-all duration-300 ease-in-out" href="<?php echo htmlspecialchars($isi_1_link_livechat_web); ?>" target="_blank">
            <div class="flex items-center w-[calc(100%-24px)] pr-1">
                <svg width="24" height="24" viewbox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="16" y="12" width="4" height="7" rx="2" fill="var(--base)" stroke="var(--base)" stroke-width="2" stroke-linejoin="round"></rect>
                    <rect x="4" y="12" width="4" height="7" rx="2" fill="var(--base)" stroke="var(--base)" stroke-width="2" stroke-linejoin="round"></rect>
                    <path d="M4 13v3M20 13v3M20 13c0-2.387-.843-4.676-2.343-6.364C16.157 4.948 14.122 4 12 4s-4.157.948-5.657 2.636C4.843 8.324 4 10.613 4 13" stroke="var(--base)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
                <span class="text-sm pl-2">Pusat Bantuan</span>
            </div>
            <figure class="w-6">
                <svg width="22" height="22" viewbox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg" size="22">
                    <path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="var(--base)"></path>
                </svg>
            </figure>
        </a>
        <a class="flex justify-between px-4 py-3 hover:lg:bg-background-tertiary transition-all duration-300 ease-in-out" href="<?php echo htmlspecialchars($isi_1_link_livechat_web); ?>" target="_blank">
            <div class="flex items-center w-[calc(100%-24px)] pr-1">
                <svg xmlns="http://www.w3.org/2000/svg" xml:space="preserve" width="24" height="24" viewbox="0 0 512 512" size="24" fill="var(--base)">
                    <path fill="var(--base)" d="M120.606 169h270.788v220.663c0 13.109-10.628 23.737-23.721 23.737H340.55v67.203c0 17.066-13.612 30.897-30.415 30.897-16.846 0-30.438-13.831-30.438-30.897V413.4h-47.371v67.203c0 17.066-13.639 30.897-30.441 30.897-16.799 0-30.437-13.831-30.437-30.897V413.4h-27.099c-13.096 0-23.744-10.628-23.744-23.737V169zm-53.065-1.801c-16.974 0-30.723 13.963-30.723 31.2v121.937c0 17.217 13.749 31.204 30.723 31.204 16.977 0 30.723-13.987 30.723-31.204V198.399c0-17.237-13.746-31.2-30.723-31.2zm323.854-20.435H120.606c3.342-38.578 28.367-71.776 64.392-90.998l-25.746-37.804c-3.472-5.098-2.162-12.054 2.946-15.525C167.3-1.034 174.242.286 177.731 5.38l28.061 41.232c15.558-5.38 32.446-8.469 50.208-8.469 17.783 0 34.672 3.089 50.229 8.476L334.29 5.395c3.446-5.108 10.41-6.428 15.512-2.957 5.108 3.471 6.418 10.427 2.946 15.525l-25.725 37.804c36.024 19.21 61.032 52.408 64.372 90.997zm-177.53-52.419c0-8.273-6.699-14.983-14.969-14.983-8.291 0-14.99 6.71-14.99 14.983 0 8.269 6.721 14.976 14.99 14.976s14.969-6.707 14.969-14.976zm116.127 0c0-8.273-6.722-14.983-14.99-14.983-8.291 0-14.97 6.71-14.97 14.983 0 8.269 6.679 14.976 14.97 14.976 8.269 0 14.99-6.707 14.99-14.976zm114.488 72.811c-16.956 0-30.744 13.984-30.744 31.222v121.98c0 17.238 13.788 31.226 30.744 31.226 16.978 0 30.701-13.987 30.701-31.226v-121.98c.001-17.238-13.723-31.222-30.701-31.222z"></path>
                </svg>
                <span class="text-sm pl-2">Aplikasi Android<span class="text-xs px-2 ml-2 rounded-sm bg-success">New</span>
                </span>
            </div>
            <figure class="w-6">
                <svg width="22" height="22" viewbox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg" size="22">
                    <path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="var(--base)"></path>
                </svg>
            </figure>
        </a>
        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) : ?>
            <div class="px-3 py-1">
                <a id="logout-sidebar-btn" class="w-full flex justify-between items-center border border-base py-2 px-2 pl-3 rounded-full cursor-pointer">
                    <span>LOGOUT</span>
                    <svg width="22" height="22" viewbox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg" size="22">
                        <path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="var(--base)"></path>
                    </svg>
                </a>
            </div>
        <?php else : ?>
            <div class="px-3 py-1">
                <a href="<?php echo htmlspecialchars($alamat_website . 'auth-login'); ?>" class="w-full flex justify-between items-center border border-base py-2 px-2 pl-3 rounded-full">
                    <span>Login</span>
                    <svg width="22" height="22" viewbox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg" size="22">
                        <path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="var(--base)"></path>
                    </svg>
                </a>
            </div>
        <?php endif; ?>
    </div>
    <p class="text-center text-sm text-caption absolute landscape:relative landscape:bottom-0 bottom-6 left-0 right-0">
        Version
        3.4.0.2
    </p>
</aside>

<script>
    // Skrip untuk sidebar (tetap di sidebar.php karena hanya terkait sidebar)
    const sidebar = document.querySelector('.sidebar');
    const toggleBtn = document.querySelector('.toggle-btn'); // toggle-btn ada di header.php, perlu dipertimbangkan
    const closeBtn = document.querySelector('.close-btn');

    if (sidebar && toggleBtn && closeBtn) {
        toggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });

        closeBtn.addEventListener('click', function() {
            sidebar.classList.remove('active');
        });

        // Event listener untuk menutup sidebar saat klik di luar
        document.addEventListener('click', function(event) {
            // Pastikan event.target bukan sidebar itu sendiri, bukan tombol toggle, dan bukan tombol close
            if (!sidebar.contains(event.target) && !toggleBtn.contains(event.target) && !closeBtn.contains(event.target)) {
                sidebar.classList.remove('active');
            }
        });

        // Event listener untuk menutup sidebar dengan tombol Escape
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                sidebar.classList.remove('active');
            }
        });
    }
</script>