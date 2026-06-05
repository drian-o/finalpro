<?php 
// -------------------------------------------------------------------------
// 1. KONEKSI & AMANKAN ROUTER URL PHP NATIVE (MULTI-METHODE)
// -------------------------------------------------------------------------
include_once 'koneksi.php'; 

// Metode A: Ambil dari clean path URL
$request = $_SERVER['REQUEST_URI'];
$request = explode('?', $request)[0];            // Pisahkan query string (?id=xx) jika ada
$request = preg_replace('#/+#', '/', $request); // Hancurkan double slash akibat proxy
$request = trim($request, '/');                  // Bersihkan slash depan & belakang

// Metode B: Cadangan jika sistem lu mendadak dipanggil pake query ?page=
if (isset($_GET['page']) && !empty($_GET['page'])) {
    $request = trim($_GET['page']);
}

// -------------------------------------------------------------------------
// 2. LOGIKA MAPPING ROUTING MENURUT DAFTAR MENU GAMBAR (FIX TYPO & STUCK)
// -------------------------------------------------------------------------
if (!empty($request)) {
    switch ($request) {
        case 'togel':
            if (file_exists('lottery.php')) { include_once 'lottery.php'; } 
            else { echo "File lottery.php tidak ditemukan!"; }
            exit;

        case 'slot':
            if (file_exists('slot.php')) { include_once 'slot.php'; } 
            else { echo "File slot.php tidak ditemukan!"; }
            exit;

        case 'casino':
            if (file_exists('casino.php')) { include_once 'casino.php'; } 
            else { echo "File casino.php tidak ditemukan!"; }
            exit;

        case 'table':
            if (file_exists('table.php')) { include_once 'table.php'; } 
            else { echo "File table.php tidak ditemukan!"; }
            exit;

        case 'sport':
        case 'sports':
            if (file_exists('sports.php')) { include_once 'sports.php'; } 
            else { echo "File sports.php tidak ditemukan!"; }
            exit;

        case 'promo':
            if (file_exists('promo.php')) { include_once 'promo.php'; } 
            else { echo "File promo.php tidak ditemukan!"; }
            exit;

        case 'arcade':
            if (file_exists('arcade.php')) { include_once 'arcade.php'; } 
            else { echo "File arcade.php tidak ditemukan!"; }
            exit;

        case 'poker':
            // 🔥 FIX TYPO: Sekarang mengarah murni ke poker.php, bukan casino.php lagi!
            if (file_exists('poker.php')) { include_once 'poker.php'; } 
            else { echo "File poker.php tidak ditemukan!"; }
            exit;

        case 'fishing':
            if (file_exists('fishing.php')) { include_once 'fishing.php'; } 
            else { echo "File fishing.php tidak ditemukan!"; }
            exit;

        case 'cockfight':
            if (file_exists('cockfight.php')) { include_once 'cockfight.php'; } 
            else { echo "File cockfight.php tidak ditemukan!"; }
            exit;

        case 'crash':
            if (file_exists('crash.php')) { include_once 'crash.php'; } 
            else { echo "File crash.php tidak ditemukan!"; }
            exit;

        case 'rtp':
            if (file_exists('rtp.php')) { include_once 'rtp.php'; } 
            else { echo "File rtp.php tidak ditemukan!"; }
            exit;

        case 'auth-register':
        case 'register':
            if (file_exists('auth-register.php')) { include_once 'auth-register.php'; } 
            else { echo "File auth-register.php tidak ditemukan!"; }
            exit;
            
        case 'auth-login':
        case 'login':
            if (file_exists('auth-login.php')) { include_once 'auth-login.php'; } 
            else { echo "File auth-login.php tidak ditemukan!"; }
            exit;
    }
}

// -------------------------------------------------------------------------
// 3. DEFAULT TAMPILAN: JIKA TIDAK ADA MENU YANG DIPILIH (HALAMAN BERANDA)
// -------------------------------------------------------------------------
include_once 'header.php'; 
include_once 'carousel_slider.php';
?>
<style>
    .rfm-marquee-container {
        position: relative;
        overflow: hidden;
        white-space: nowrap;
        width: 110%;
    }

    .rfm-marquee {
        display: inline-block;
        white-space: nowrap;
        animation: marquee 20s linear infinite;
    }

    @keyframes marquee {
        0% { transform: translateX(10%); }
        100% { transform: translateX(-50%); }
    }
</style>
</head>
<body>
    <div class="w-full px-3 mt-3 order-1">
        <div class="bg-background-secondary rounded-full py-1 flex items-center">
            <div class="block relative bg-background-secondary rounded-full z-10 pl-3 pr-2">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M3.158 13.93a3.752 3.752 0 0 1 0-3.86 1.5 1.5 0 0 1 .993-.7l1.693-.339a.45.45 0 0 0 .258-.153L8.17 6.395c1.182-1.42 1.774-2.129 2.301-1.938C11 4.648 11 5.572 11 7.42v9.162c0 1.847 0 2.77-.528 2.962-.527.19-1.119-.519-2.301-1.938L6.1 15.122a.45.45 0 0 0-.257-.153l-1.693-.339a1.5 1.5 0 0 1-.993-.7Z" stroke="var(--primary)"></path>
                    <path d="M15.536 8.464a5 5 0 0 1 .027 7.044M19.657 6.343a8 8 0 0 1 .044 11.27" stroke="var(--primary)" stroke-linecap="round"></path>
                </svg>
            </div>
            <div class="w-[calc(100%-58px)] overflow-hidden inline-block">
                <div class="text-sm z-[9] inline-block">
                    <div class="rfm-marquee-container">
                        <div class="rfm-marquee">
                            <?php echo $isi_1_teks_berjalan_web; ?> | <?php echo $isi_2_teks_berjalan_web; ?> | <?php echo $isi_3_teks_berjalan_web; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="w-full px-3 mt-3 order-3 overflow-hidden">
        <div class="flex justify-between items-center mb-4 lg:mb-3">
            <p class="md:text-lg font-medium">Lottery Results</p>
            <button id="openPopupButton" class="bg-background-tertiary px-3 py-2 rounded-lg flex items-center hover:lg:brightness-[0.9] transition-all duration-200 ease-in-out justify-between">
                <span class="text-xs pr-1 text-primary">Lottery Market Schedule</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 16 16" fill="var(--primary)" size="18">
                    <path fill="var(--primary)" d="M1.75 5.625c0-1.179 0-1.768.366-2.134.366-.366.955-.366 2.134-.366h7.5c1.178 0 1.768 0 2.134.366.366.366.366.955.366 2.134 0 .295 0 .442-.091.533-.092.092-.24.092-.534.092H2.375c-.295 0-.442 0-.533-.092-.092-.091-.092-.238-.092-.533Z"></path>
                    <path fill="var(--primary)" fill-rule="evenodd" d="M2.116 13.384c-.366-.366-.366-.956-.366-2.134V8.125c0-.295 0-.442.092-.533.091-.092.238-.092.533-.092h11.25c.295 0 .442 0 .534.092.091.091.091.238.091.533v3.125c0 1.178 0 1.768-.366 2.134-.366.366-.956.366-2.134.366h-7.5c-1.179 0-1.768 0-2.134-.366ZM5.5 10a.625.625 0 1 0 0 1.25h5a.625.625 0 1 0 0-1.25h-5Z" clip-rule="evenodd"></path>
                    <path stroke="var(--primary)" stroke-linecap="round" stroke-width="1.25" d="M4.875 1.875V3.75M11.125 1.875V3.75"></path>
                </svg>
            </button>
            <div id="popupOverlay" class="fixed z-[999] bg-black/60 top-0 bottom-0 left-0 right-0 w-0 invisible"></div>
            <section id="popupSection" class="fixed z-[9999] flex md:items-center left-0 right-0 bottom-0 md:top-0 md:bottom-0 transition-all duration-300 ease-in-out overflow-hidden -z-10 opacity-0 invisible max-h-full md:max-h-screen">
                <div class="w-full md:w-2/3 lg:w-[450px] md:mx-auto bg-background-secondary p-3 pb-12 md:px-4 md:pb-6 rounded-t-3xl md:rounded-lg overflow-y-scroll">
                    <div class="flex justify-between items-center py-2">
                        <figure class="flex items-center">
                            <img alt="Jadwal Pasaran Togel Icon" loading="lazy" width="0" height="0" decoding="async" data-nimg="1" class="rounded-xl w-8 mx-auto" style="color:transparent" src="https://cdn.databerjalan.com/cdn-cgi/image/width=auto,quality=75,fit=contain,format=auto//assets/images/static/v3/lottery/icons/market-popup-desktop.webp" />
                            <span class="text-sm md:text-base font-semibold pl-2">Market Schedule</span>
                        </figure>
                        <button id="closePopupButton">
                            <svg width="25" height="25" viewBox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg" size="25">
                                <path d="M18 6 6 18M6 6l12 12" stroke="var(--base)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="max-h-[68vh] overflow-auto">
                        <table class="table-auto w-full">
                            <thead>
                                <tr>
                                    <th class="py-2 px-1 text-xs lg:text-sm text-left font-semibold w-2/6">Market</th>
                                    <th class="py-2 px-1 text-xs lg:text-sm text-left font-semibold w-2/6">Schedule</th>
                                    <th class="py-2 px-1 text-xs lg:text-sm font-semibold w-1/6">Closing Time</th>
                                    <th class="py-2 px-1 text-xs lg:text-sm font-semibold w-1/6">Opening Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="even:bg-background-default">
                                    <td class="py-2 px-1 text-xs lg:text-sm underline"><a target="_blank" rel="nofollow" href="http://www.hkpools1.com/">HONGKONG</a></td>
                                    <td class="py-2 px-1 text-xs lg:text-sm">Everyday</td>
                                    <td class="py-2 px-1 text-xs lg:text-sm text-center">22:30</td>
                                    <td class="py-2 px-1 text-xs lg:text-sm text-center">23:59</td>
                                </tr>
                                <tr class="even:bg-background-default">
                                    <td class="py-2 px-1 text-xs lg:text-sm underline"><a target="_blank" rel="nofollow" href="https://online.singaporepools.com/en/lottery">SINGAPORE</a></td>
                                    <td class="py-2 px-1 text-xs lg:text-sm">Closed Tuesday and Friday (conditional)</td>
                                    <td class="py-2 px-1 text-xs lg:text-sm text-center">17:25</td>
                                    <td class="py-2 px-1 text-xs lg:text-sm text-center">21:00</td>
                                </tr>
                                <tr class="even:bg-background-default">
                                    <td class="py-2 px-1 text-xs lg:text-sm underline"><a target="_blank" rel="nofollow" href="http://livedrawsydney.co/">SYDNEY</a></td>
                                    <td class="py-2 px-1 text-xs lg:text-sm">Everyday</td>
                                    <td class="py-2 px-1 text-xs lg:text-sm text-center">13:30</td>
                                    <td class="py-2 px-1 text-xs lg:text-sm text-center">14:30</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>

        <div class="relative group">
            <button class="hidden lg:flex justify-center items-center absolute -right-12 top-1/3 w-8 h-8 rounded-full text-2xl text-caption group-hover:-right-3 bg-white/70 hover:bg-white transition-all duration-300 ease-in-out">&gt;</button>
            <div class="-mx-1 lg:mx-0 overflow-x-scroll whitespace-nowrap pb-3 scroll-smooth opacity-scroll">
                <a class="inline-block justify-center md:w-[calc(100%/4-8px)] lg:w-[calc(100%/5-16px)] px-2 lg:px-3 py-3 lg:py-4 mx-2 rounded-lg bg-gradient-to-b from-primary to-background-secondary" href="/togel">
                    <div class="flex items-center justify-center">
                        <div>
                            <p class="font-bold text-[10px] md:text-xs lg:text-sm uppercase truncate w-28 lg:w-32 text-center text-white">HONGKONG</p>
                            <p class="my-1 py-1 text-3xl w-24 lg:w-auto mx-auto font-extrabold text-center bg-background-default border rounded-md border-primary" id="hk-number"></p>
                            <p class="text-[9px] lg:text-xs text-center" id="hk-date"></p>
                        </div>
                    </div>
                </a>
                <a class="inline-block justify-center md:w-[calc(100%/4-8px)] lg:w-[calc(100%/5-16px)] px-2 lg:px-3 py-3 lg:py-4 mx-2 rounded-lg bg-gradient-to-b from-primary to-background-secondary" href="/togel">
                    <div class="flex items-center justify-center">
                        <div>
                            <p class="font-bold text-[10px] md:text-xs lg:text-sm uppercase truncate w-28 lg:w-32 text-center text-white">SINGAPORE</p>
                            <p class="my-1 py-1 text-3xl w-24 lg:w-auto mx-auto font-extrabold text-center bg-background-default border rounded-md border-primary" id="sg-number"></p>
                            <p class="text-[9px] lg:text-xs text-center" id="sg-date"></p>
                        </div>
                    </div>
                </a>
                <a class="inline-block justify-center md:w-[calc(100%/4-8px)] lg:w-[calc(100%/5-16px)] px-2 lg:px-3 py-3 lg:py-4 mx-2 rounded-lg bg-gradient-to-b from-primary to-background-secondary" href="/togel">
                    <div class="flex items-center justify-center">
                        <div>
                            <p class="font-bold text-[10px] md:text-xs lg:text-sm uppercase truncate w-28 lg:w-32 text-center text-white">SYDNEY</p>
                            <p class="my-1 py-1 text-3xl w-24 lg:w-auto mx-auto font-extrabold text-center bg-background-default border rounded-md border-primary" id="sy-number"></p>
                            <p class="text-[9px] lg:text-xs text-center" id="sy-date"></p>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <section class="w-full md:mx-auto px-3 mt-4 lg:mt-8 mb-3 lg:mb-4 order-4">
            <div class="relative w-full h-[18.5vw] sm:h-20 md:h-[11vh] lg:h-48">
                <figure class="h-full relative z-20">
                    <img alt="Jackpot counter image" loading="lazy" width="0" height="0" decoding="async" data-nimg="1" class="w-auto h-full" src="https://cdn.databerjalan.com/cdn-cgi/image/width=auto,quality=75,fit=contain,format=auto//assets/images/static/v3/jackpot/main-icon.webp" style="color: transparent;">
                </figure>
                <div class="h-[18.5vw] sm:h-20 md:h-[11vh] lg:h-48 absolute top-0 bottom-0 right-0 w-10/12 z-10 overflow-hidden rounded-xl lg:rounded-3xl bg-gradient-to-r from-[#8d4a11] via-[#efe172] to-[#8d4a11]">
                    <div class="absolute top-1 bottom-1 left-1 right-1 lg:left-2 lg:right-2 lg:top-2 lg:bottom-2 flex items-center bg-background-secondary rounded-xl lg:rounded-3xl overflow-hidden">
                        <div class="absolute right-3 lg:right-12 w-[calc(100vw-50vw)] sm:w-[calc(100%-7rem)] lg:w-[calc(100%-192px-15%)] h-8 md:h-16 lg:h-28 rounded-lg lg:rounded-2xl bg-gradient-to-r from-[#8D4A11] via-[#EFE484] to-[#8D4A11]">
                            <div class="absolute top-[1px] bottom-[1px] left-[1px] right-[1px] lg:left-1 lg:right-1 lg:top-1 lg:bottom-1 bg-background-default rounded-lg lg:rounded-2xl flex items-center justify-center">
                                <p id="jackpot-counter" class="text-[calc(1vw+1vh+1vmin)] md:text-[calc(1.25vw+1.25vh+1.25vmin)] lg:text-[calc(36px+12*((100vw-300px)/(1300)))] leading-8 lg:leading-normal font-bold text-center text-transparent bg-clip-text bg-gradient-to-t from-[#C27E1E] to-[#EFE172]">IDR 10.762.327.720</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <?php include 'game-popular.php'; ?>
        <?php include 'game-recomended.php'; ?>
    </div>

    <div class="w-full px-3 mt-1 lg:mt-4 order-last">
        <div class="flex justify-between items-center">
            <p class="md:text-lg font-medium">Promo</p>
            <a class="text-primary text-sm md:text-base border-b border-transparent hover:lg:border-primary" href="/promo">
                Show All
                <svg width="20" height="20" viewBox="0 0 24 24" fill="var(--primary)" xmlns="http://www.w3.org/2000/svg" size="20">
                    <path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="var(--primary)"></path>
                </svg>
            </a>
        </div>

        <?php
        $promosi = mysqli_query($koneksi, "SELECT * FROM promosi");
        $promosi_data = [];
        while ($data_promosi = mysqli_fetch_array($promosi)) {
            if ($data_promosi['gambar_promosi'] && $data_promosi['judul_promosi']) {
                $promosi_data[] = $data_promosi;
            }
        }
        ?>

        <ul class="flex space-x-2 lg:space-x-3 mt-3">
            <?php foreach ($promosi_data as $index => $data_promosi) : ?>
                <?php if ($index >= 3) break; ?>
                <li aria-label="slide item <?php echo $index; ?>" role="listitem" class="w-2 lg:w-3 h-2 lg:h-3 ml-2 lg:ml-3 rounded-full inline-block border-[0.5px] border-base" style="background-color: var(--secondaryBackground)"></li>
            <?php endforeach; ?>
        </ul>

        <div class="mt-3 -mx-[5px] lg:-mx-2 pb-2 whitespace-nowrap overflow-x-scroll overflow-y-hidden lg:overflow-x-hidden opacity-scroll">
            <?php foreach ($promosi_data as $index => $data_promosi) : ?>
                <?php if ($index >= 3) break; ?>
                <div class="w-[80%] sm:w-2/3 md:w-[45%] lg:w-1/3 inline-block px-2 mt-4">
                    <a class="block bg-background-tertiary px-3 lg:px-4 py-2 lg:pt-4 lg:pb-2 rounded-md relative" href="/promo">
                        <figure class="mb-2">
                            <img alt="<?php echo htmlspecialchars($data_promosi['judul_promosi'], ENT_QUOTES, 'UTF-8'); ?>" loading="lazy" width="0" height="0" decoding="async" data-nimg="1" class="rounded-md w-full min-h-[96px] lg:min-h-[150px] max-h-24 md:max-h-32 object-cover object-center" src="<?php echo htmlspecialchars($alamat_website . 'assets/img/' . $data_promosi['gambar_promosi'], ENT_QUOTES, 'UTF-8'); ?>" />
                            <span class=" absolute z-10 left-0 top-2 text-[10px] font-medium px-3 py-[1px] rounded-e-full bg-success">
                                <span class="animate-ping absolute z-20 inline-flex h-4 w-8 rounded-e-full bg-success opacity-90"></span>
                                <span class="relative z-30 text-white">New</span>
                            </span>
                        </figure>
                        <article class="py-0 opacity-100 relative">
                            <span class="font-semibold text-primary px-2 relative text-[9px] lg:text-[10px] lg:relative top-0">
                                <span class="bg-primary opacity-20 absolute top-0 bottom-0 left-0 right-0 rounded-full"></span>
                                Ongoing Promo
                            </span>
                            <p class="h-8 lg:h-10 flex flex-col items-start justify-center mt-2">
                                <span class="text-xs lg:text-sm max-h-8 lg:max-h-10 overflow-hidden whitespace-normal"><?php echo htmlspecialchars($data_promosi['judul_promosi'], ENT_QUOTES, 'UTF-8'); ?></span>
                            </p>
                        </article>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <?php include_once 'footer.php'; ?>

    <script>
        function formatDate(date) {
            const options = { weekday: 'short', day: '2-digit', month: '2-digit', year: '2-digit' };
            return date.toLocaleDateString('en-GB', options).replace(/,/g, '');
        }
        function addDays(date, days) {
            let result = new Date(date);
            result.setDate(result.getDate() + days);
            return result;
        }
        function generateRandomNumber() {
            return Math.floor(1000 + Math.random() * 9000);
        }
        function updateNumbersAndDates() {
            const today = new Date().toLocaleDateString('en-GB');
            const storedDate = localStorage.getItem('lastDate');

            if (storedDate !== today) {
                localStorage.setItem('lastDate', today);
                localStorage.setItem('hkNumber', generateRandomNumber());
                localStorage.setItem('sgNumber', generateRandomNumber());
                localStorage.setItem('syNumber', generateRandomNumber());
            }

            document.getElementById('hk-number').innerText = localStorage.getItem('hkNumber');
            document.getElementById('sg-number').innerText = localStorage.getItem('sgNumber');
            document.getElementById('sy-number').innerText = localStorage.getItem('syNumber');

            document.getElementById('hk-date').innerText = formatDate(addDays(new Date(), 0));
            document.getElementById('sg-date').innerText = formatDate(addDays(new Date(), 1));
            document.getElementById('sy-date').innerText = formatDate(addDays(new Date(), 1));
        }
        updateNumbersAndDates();
    </script>
    <script>
        document.getElementById('openPopupButton').addEventListener('click', function() {
            const overlay = document.getElementById('popupOverlay');
            const section = document.getElementById('popupSection');
            overlay.classList.remove('invisible', 'w-0');
            overlay.classList.add('w-full', 'visible'); 
            section.classList.remove('opacity-0', 'invisible', '-z-10', 'max-h-0');
            section.classList.add('opacity-100', 'z-[9999]', 'max-h-screen'); 
        });

        document.getElementById('closePopupButton').addEventListener('click', function() {
            const overlay = document.getElementById('popupOverlay');
            const section = document.getElementById('popupSection');
            section.classList.add('opacity-0', 'invisible', '-z-10', 'max-h-0');
            section.classList.remove('opacity-100', 'z-[9999]', 'max-h-screen');
            overlay.classList.add('invisible', 'w-0');
            overlay.classList.remove('w-full', 'visible');
        });
    </script>
    <script>
        function getRandomIncrement() { return Math.floor(Math.random() * 1000) + 1; }
        function formatNumber(num) { return 'IDR ' + num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "."); }
        let jackpotValue = 10762327720;
        function updateJackpotCounter() {
            jackpotValue += getRandomIncrement();
            document.getElementById('jackpot-counter').textContent = formatNumber(jackpotValue);
        }
        setInterval(updateJackpotCounter, 1000);
    </script>    
</body>
</html>
