<?php
if (!isset($_SESSION)) {
    session_start();
}
include_once 'koneksi.php';
$nama_pengguna = isset($_SESSION['nama_pengguna_anggota']) ? $_SESSION['nama_pengguna_anggota'] : '';

// Ambil inisial huruf pertama dari nama pengguna
$inisial = strtoupper(substr($nama_pengguna, 0, 1));
// Cek jika pengguna sudah login
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    // Ambil ID anggota dari session
    $id_anggota = $_SESSION['id_anggota'];

    // Query untuk mendapatkan saldo terbaru dari database
    $query = "SELECT saldo_anggota FROM anggota WHERE id_anggota = ?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, 'i', $id_anggota);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        // Update saldo di session dengan nilai terbaru dari database
        $_SESSION['saldo_anggota'] = $row['saldo_anggota'];
    }
}
$q_amp = mysqli_query($koneksi, "SELECT * FROM pengaturan WHERE nama_pengaturan = 'amp_pengaturan'");
$d_amp = mysqli_fetch_array($q_amp);
$amp_status = $d_amp['isi_1_pengaturan'] ?? 'off';
$amp_script = $d_amp['isi_2_pengaturan'] ?? '';
// Tambahkan logika untuk menentukan tema pengguna (contoh: 'dark' atau 'light')
// Anda bisa mendapatkan ini dari database, session, atau default
$user_theme = 'dark'; // Ubah 'dark' menjadi 'light' jika Anda ingin tema terang sebagai default

// Ambil nilai warna dari database
$theme_color_query = mysqli_query($koneksi, "SELECT * FROM pengaturan WHERE nama_pengaturan IN ('bg_1_web', 'bg_2_web', 'bg_3_web')");

// Ambil data warna
$colors = [];
while ($row = mysqli_fetch_array($theme_color_query)) {
    $colors[$row['nama_pengaturan']] = $row['isi_1_pengaturan'];
}

// Data navigasi
$nav_items = [
    ['title' => 'TOGEL', 'link' => $alamat_website . 'lottery', 'icon' => $alamat_website . 'assets/menu/lottery.png'],
    ['title' => 'SLOT', 'link' => $alamat_website . 'slot', 'icon' => $alamat_website . 'assets/menu/slot.png'],
    ['title' => 'CASINO', 'link' => $alamat_website . 'casino', 'icon' => $alamat_website . 'assets/menu/casino.png'],
    ['title' => 'TABLE', 'link' => $alamat_website . 'table', 'icon' => $alamat_website . 'assets/menu/games.png'],
    ['title' => 'SPORT', 'link' => $alamat_website . 'sports', 'icon' => $alamat_website . 'assets/menu/sports.png'],
    ['title' => 'PROMO', 'link' => $alamat_website . 'promo', 'icon' => $alamat_website . 'assets/menu/promo.png'],
    ['title' => 'ARCADE', 'link' => $alamat_website . 'arcade', 'icon' => $alamat_website . 'assets/menu/arcade.png'],
    ['title' => 'POKER', 'link' => $alamat_website . '#', 'icon' => $alamat_website . 'assets/menu/poker.png'],
    ['title' => 'FISHING', 'link' => $alamat_website . 'fishing', 'icon' => $alamat_website . 'assets/menu/fishing.png'],
    ['title' => 'COCKFIGHT', 'link' => $alamat_website . 'comming_soon', 'icon' => $alamat_website . 'assets/menu/cockfight.png'],
    ['title' => 'CRASH', 'link' => $alamat_website . 'crash', 'icon' => $alamat_website . 'assets/menu/esports.png'],
    ['title' => 'RTP', 'link' => $alamat_website . 'rtp', 'icon' => $alamat_website . 'assets/menu/other.png'],
];
?>

<!DOCTYPE html>
<html lang="id" class="notranslate __className_7df6af" translate="no" data-theme="<?php echo htmlspecialchars($user_theme); ?>" style="--primary: <?php echo htmlspecialchars($colors['bg_1_web']); ?>;">

<head>
    <meta charSet="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <link rel="stylesheet" href="_next/static/css/0a4ae62ed810513b.css" data-precedence="next" />
    <link rel="stylesheet" href="_next/static/css/54fc46000f7e20bc.css" data-precedence="next" />
    <link rel="preload" href="_next/static/chunks/webpack-e30d72a36c0ae6d3.js" as="script" fetchPriority="low" />
    <script src="_next/static/chunks/1179-e1ca092b8d3f3375.js" async=""></script>
    <script src="_next/static/chunks/main-app-12309b691508e534.js" async=""></script>
    <title><?php echo $isi_1_judul_web; ?></title>
    <?php if (!empty($amp_script)): ?>
    <link rel="amphtml" href="<?php echo htmlspecialchars($amp_script); ?>">
    <?php endif; ?>
    <meta name="description" content="<?php echo $isi_1_deskripsi_web; ?>">
    <meta name="application-name" content="<?php echo $isi_1_judul_web; ?>" />
    <link rel="amphtml" href="<?php echo $amp_script; ?>">
    <link rel="author" href="drianproject" />
    <meta name="author" content="<?php echo $isi_1_judul_web; ?>" />
    <meta name="generator" content="<?php echo $alamat_website; ?>" />
    <meta name="keywords" content="<?php echo $isi_1_judul_web; ?> 88,<?php echo $isi_1_judul_web; ?>" />
    <meta name="referrer" content="origin-when-cross-origin" />
    <meta name="color-scheme" content="dark" />
    <meta name="creator" content="<?php echo $isi_1_judul_web; ?>" />
    <meta name="publisher" content="<?php echo $isi_1_judul_web; ?> - Best E-Gaming Provider" />
    <link rel="bookmarks" href="/" />
    <meta name="category" content="games" />
    <meta name="robots" content="index,follow" />
    <meta name="author" content="<?php echo $isi_1_judul_web; ?>" />
    <meta name="geo.region" content="ID" />
    <meta name="geo.placename" content="Indonesia" />
    <meta name="publisher" content="<?php echo $isi_1_judul_web; ?>" />
    <meta name="copyright" content="<?php echo $isi_1_judul_web; ?>" />
    <meta name="categories" content="website" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="<?php echo $isi_1_judul_web; ?> | Cepat Dan Pasti" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
    <meta property="og:title" content="<?php echo $isi_1_judul_web; ?> | Cepat Dan Pasti" />
    <meta property="og:description" content="<?php echo $isi_1_judul_web; ?>: Mengedepankan keinginan pemain untuk dapat kesuksesan yang nyata dan cepat." />
    <meta property="og:site_name" content="<?php echo $isi_1_judul_web; ?>" />
    <meta property="og:image:width" content="800" />
    <meta property="og:image:height" content="600" />
    <meta property="og:image:alt" content="<?php echo $isi_1_judul_web; ?>" />
    <meta property="og:image:width" content="1800" />
    <meta property="og:image:height" content="1600" />
    <meta property="og:image:alt" content="<?php echo $isi_1_judul_web; ?>" />
    <meta property="og:type" content="website" />
    <link rel="shortcut icon" href="<?php echo 'assets/img/' . $isi_1_favicon_web; ?>" />
    <link rel="icon" href="<?php echo 'assets/img/' . $isi_1_favicon_web; ?>" />
    <link rel="apple-touch-icon" href="<?php echo 'assets/img/' . $isi_1_favicon_web; ?>" />
    <link rel="apple-touch-icon-precomposed" href="<?php echo 'assets/img/' . $isi_1_favicon_web; ?>" />
    <meta name="next-size-adjust" />
    <script src="_next/static/chunks/polyfills-c67a75d1b6f99dc8.js" noModule=""></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css">
    
    <style>
        .header-transition {
            transition: top 0.5s ease-out;
        }
        .header-fixed {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: var(--background-default);
            z-index: 97;
            width: 100%;
        }
        .header-hidden {
            top: 0px;
        }
        .overflow-auto {
            overflow: auto
        }
        .overflow-hidden {
            overflow: hidden
        }
        .overflow-x-auto {
            overflow-x: auto
        }
        .overflow-y-hidden {
            overflow-y: hidden
        }
        .overflow-x-scroll {
            overflow-x: scroll
        }
        .overflow-y-scroll {
            overflow-y: scroll
        }
        .overscroll-contain {
            overscroll-behavior: contain
        }
        .scroll-smooth {
            scroll-behavior: smooth
        }
        .sidebar {
            position: fixed;
            top: 0;
            right: 0;
            width: 350px;
            height: 110%;
            background-color: #1b1b1b;
            color: #fff;
            transform: translateX(100%);
            transition: transform 0.3s ease-in-out;
            z-index: 1001;
        }
        .sidebar.active {
            transform: translateX(0);
        }
        .toggle-btn {
            position: flex;
            top: 20px;
            right: 20px;
            z-index: 1001;
        }
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            z-index: 1000;
            display: none;
        }
        
        /* Modal Menu Styling */
        .menu-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            background-color: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease;
        }
        .menu-modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        .menu-modal-content {
            background-color: #1f2937;
            border-radius: 1rem;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            transform: translateY(-20px);
            transition: transform 0.3s ease;
        }
        .menu-modal-overlay.active .menu-modal-content {
            transform: translateY(0);
        }
        .menu-modal-close-btn {
            position: absolute;
            top: 0.75rem;
            right: 0.75rem;
            font-size: 1.5rem;
            color: white;
            cursor: pointer;
            z-index: 10;
            background: rgba(0, 0, 0, 0.5);
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .menu-modal-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            padding: 1.5rem;
        }
        .menu-modal-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 1rem;
            text-decoration: none;
            color: #9CA3AF;
            background-color: #2c3e50;
            border-radius: 0.5rem;
            transition: all 0.2s;
        }
        .menu-modal-item:hover {
            color: white;
            background-color: #374151;
            transform: translateY(-2px);
        }
        .menu-modal-item .icon-menu-png {
            height: 48px;
            width: 48px;
            margin-bottom: 0.5rem;
        }
        .menu-modal-item p {
            font-size: 0.9rem;
            font-weight: 600;
        }

        .header-buttons {
            display: flex;
            gap: 1rem;
        }
        .header-button {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            color: white;
            text-decoration: none;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .header-button-icon {
            background-color: #e74c3c; /* Warna merah */
            color: white;
            width: 45px; /* Sedikit lebih besar untuk ikon */
            height: 45px; /* Sedikit lebih besar untuk ikon */
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s;
        }
        .header-button-icon:hover {
            transform: scale(1.05);
        }
        .header-button-icon i {
            font-size: 28px; /* Ukuran ikon */
        }

        /* Ukuran logo yang lebih besar */
        .logo-img {
            height: 48px; /* Ukuran logo lebih besar */
            width: auto;
        }
        /* Penyesuaian untuk desktop jika perlu */
        @media (min-width: 1024px) {
            .logo-img {
                height: 60px; /* Ukuran logo lebih besar di desktop */
            }
        }
    </style>
</head>

<?php echo $isi_1_script_livechat_web; ?>
<body>
    <div id="sidebar-overlay" class="overlay"></div>
    
    <div id="notification" class="fixed z-[9999] px-4 pt-3 pb-5 top-3 sm:top-4 sm:right-6 left-3 right-3 sm:ml-auto sm:w-2/3 md:w-1/2 lg:w-[410px] rounded-xl bg-gradient-to-r from-[#710000] to-background-secondary to-50%" style="display:none;">
        <button class="h-6 w-6 ml-auto absolute right-3 top-2 z-50" onclick="document.getElementById('notification').style.display='none';">
            <svg width="100%" height="100%" viewBox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg">
                <path d="M18 6L6 18M6 6l12 12" stroke="var(--base)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
        </button>
        <div class="flex items-center">
            <figure class="flex-none h-12 w-12">
                <svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" viewBox="0 0 60 60" fill="none">
                    <path stroke="#FF3B30" stroke-linecap="round" stroke-width="5" d="M30 52.5a22.5 22.5 0 1 0-15.91-6.59M22.5 22.5l15 15M37.5 22.5l-15 15"></path>
                </svg>
            </figure>
            <article class="pl-3">
                <p class="font-medium">Cancelled</p>
                <p class="text-xs mt-1" id="notification-text">
                    </p>
            </article>
        </div>
    </div>
    <script>
        function registerPopup(options) {
            const notification = document.getElementById('notification');
            const notificationText = document.getElementById('notification-text');
            
            if (options && options.content) {
                notificationText.textContent = options.content;
                notification.style.display = 'block';
            }
        }
    </script>
    <script>
        function updateSaldo() {
            fetch('update_saldo')
            .then(response => response.text())
            .catch(error => {
                console.error('Terjadi kesalahan:', error);
            });
        }
        
        window.addEventListener('load', updateSaldo);
    </script>

    <header id="header" class="fixed z-[97] left-0 right-0 bg-background-default transition-all duration-500 ease-out top-0">
        <section class="container mx-auto px-3 py-4 flex flex-col lg:flex-row items-center justify-between">
            <div class="flex items-center w-full lg:w-auto justify-between lg:justify-start">
                <a href="<?php echo $alamat_website . 'home'; ?>" class="flex items-center">
                    <img alt="Logo Sigmabet29" fetchpriority="high" loading="eager" width="0" height="0" decoding="async" data-nimg="1" class="logo-img" style="color: transparent;" src="<?php echo 'assets/img/' . $isi_1_logo_web; ?>">
                </a>
                <div class="header-buttons lg:hidden">
                    <button id="openMenuModalBtn" class="header-button" aria-label="Open Menu">
                        <div class="header-button-icon">
                            <i class="mdi mdi-menu"></i>
                        </div>
                        <span>MENU</span>
                    </button>
                    <button id="openSidebarBtn" class="header-button toggle-btn" aria-label="Sidebar Toggle Button">
                        <div class="header-button-icon">
                            <i class="mdi mdi-account-circle"></i>
                        </div>
                        <span>AKUN</span>
                    </button>
                </div>
            </div>

            <nav class="hidden lg:grid grid-cols-6 gap-x-2 gap-y-1 mt-4 w-full">
                <?php foreach ($nav_items as $item): ?>
                    <a class="flex flex-col items-center justify-center p-2 lg:px-1 lg:py-1 border-b-2 border-transparent hover:border-primary transition-all duration-300 ease-in-out text-white hover:text-primary text-[10px] lg:text-sm font-semibold" href="<?php echo $item['link']; ?>">
                        <img src="<?php echo $item['icon']; ?>" alt="<?php echo $item['title']; ?>" class="h-6 w-6 lg:h-7 lg:w-7">
                        <p class="mt-1"><?php echo $item['title']; ?></p>
                    </a>
                <?php endforeach; ?>
            </nav>
        </section>
    </header>
    
    <div id="main-content" class="content-padding">
        </div>

    <div id="menuModal" class="menu-modal-overlay">
        <div class="menu-modal-content">
            <button id="closeMenuModalBtn" class="menu-modal-close-btn">&times;</button>
            <div class="menu-modal-grid">
                <?php foreach ($nav_items as $item): ?>
                    <a class="menu-modal-item" href="<?php echo $item['link']; ?>">
                        <img src="<?php echo $item['icon']; ?>" alt="<?php echo $item['title']; ?>" class="icon-menu-png">
                        <p><?php echo $item['title']; ?></p>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <?php include 'side_navbar.php'; ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const openMenuModalBtn = document.getElementById('openMenuModalBtn');
            const closeMenuModalBtn = document.getElementById('closeMenuModalBtn');
            const menuModal = document.getElementById('menuModal');
            const body = document.body;

            const openSidebarBtn = document.getElementById('openSidebarBtn');
            const sidebar = document.querySelector('.sidebar');
            const sidebarOverlay = document.getElementById('sidebar-overlay');
            const closeSidebarBtn = document.getElementById('close-sidebar-btn'); // Pastikan ID ini ada di side_navbar.php

            openMenuModalBtn.addEventListener('click', function() {
                menuModal.classList.add('active');
                body.style.overflow = 'hidden';
            });

            closeMenuModalBtn.addEventListener('click', function() {
                menuModal.classList.remove('active');
                body.style.overflow = 'auto';
            });
            
            menuModal.addEventListener('click', function(event) {
                if (event.target === menuModal) {
                    menuModal.classList.remove('active');
                    body.style.overflow = 'auto';
                }
            });

            // Sidebar logic
            openSidebarBtn.addEventListener('click', function() {
                sidebar.classList.add('active');
                sidebarOverlay.style.display = 'block';
                body.style.overflow = 'hidden';
            });

            sidebarOverlay.addEventListener('click', function() {
                sidebar.classList.remove('active');
                sidebarOverlay.style.display = 'none';
                body.style.overflow = 'auto';
            });

            // Pastikan tombol close sidebar ada dan berfungsi
            if (closeSidebarBtn) {
                closeSidebarBtn.addEventListener('click', function() {
                    sidebar.classList.remove('active');
                    sidebarOverlay.style.display = 'none';
                    body.style.overflow = 'auto';
                });
            }
        });
    </script>
</body>
</html>
