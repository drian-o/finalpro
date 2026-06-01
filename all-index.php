<?php
// Pastikan variabel yang dibutuhkan di file-file ini sudah didefinisikan sebelumnya,
// misalnya $koneksi, $alamat_website, $_SESSION, dll.
?>

<div id="popular-games-content" class="tab-content" style="display: none;">
    <div class="w-full px-3 mt-3 lg:mt-5 order-6">
        <div class="mt-3 -mx-[5px] lg:-mx-2 pb-2 whitespace-nowrap overflow-x-scroll overflow-y-hidden lg:overflow-x-hidden opacity-scroll">
            <?php
            $query_game_populer = "SELECT * FROM gamepopuler ORDER BY id ASC LIMIT 6";
            $result_game_populer = mysqli_query($koneksi, $query_game_populer);
    
            if ($result_game_populer && mysqli_num_rows($result_game_populer) > 0) {
                while ($row_game = mysqli_fetch_assoc($result_game_populer)) {
                    $nama_game = $row_game['game_name'];
                    $gambar_game = $row_game['banner'];
                    $provider_game = $row_game['provideragent'];
                    $kode_game = $row_game['game_code'];
                    $is_hot = isset($row_game['is_hot']) ? $row_game['is_hot'] : true;
                    $game_type = 'slot';
            ?>
            <div class="w-1/4 md:w-1/5 lg:w-1/6 px-[5px] lg:px-2 inline-block">
                <div class="game-card">
                    <div class="game-card-image-container">
                        <img alt="<?php echo htmlspecialchars($nama_game); ?>-<?php echo htmlspecialchars($provider_game); ?>" fetchPriority="high" width="300" height="300" decoding="async" data-nimg="1" class="game-card-image" src="<?php echo htmlspecialchars($gambar_game); ?>" />
                        <?php if ($is_hot): ?>
                        <div class="hot-badge-icon">
                            <i class="mdi mdi-fire text-white text-base animate-pulse-fast"></i>
                        </div>
                        <?php endif; ?>
                        <div class="game-card-overlay">
                            <?php
                            if (isset($_SESSION['id_anggota'])) {
                            ?>
                                <a href="playgame/Gameplay.php?game_uid=<?php echo htmlspecialchars($kode_game); ?>&provider_code=<?php echo htmlspecialchars($provider_game); ?>&game_type=<?php echo htmlspecialchars($game_type); ?>" class="play-game-popular-trigger play-button">Main Game</a>
                            <?php
                            } else {
                            ?>
                                <a href="javascript:registerPopup({ content:'<?php echo htmlspecialchars($isi_1_popup_teks_belum_login_web, ENT_QUOTES, 'UTF-8'); ?>' });" class="play-game-popular-trigger play-button">Main Game</a>
                            <?php
                            }
                            ?>
                        </div>
                    </div>
                    <p class="game-card-title"><?php echo htmlspecialchars($nama_game); ?></p>
                </div>
            </div>
            <?php
                }
            } else {
                if (!$result_game_populer) {
                    // error_log("Query game populer gagal: " . mysqli_error($koneksi));
                }
            }
            ?>
        </div>
    </div>
</div>

<div id="recomended-games-content" class="tab-content" style="display: none;">
    <div class="w-full px-3 mt-3 lg:mt-5 order-6">
        <div class="mt-3 -mx-[5px] lg:-mx-2 pb-2 whitespace-nowrap overflow-x-scroll overflow-y-hidden lg:overflow-x-hidden opacity-scroll">
            <?php
            $query_game_rekomendasi = "SELECT * FROM gamerekomended ORDER BY RAND() LIMIT 6";
            $result_game_rekomendasi = mysqli_query($koneksi, $query_game_rekomendasi);
    
            if ($result_game_rekomendasi && mysqli_num_rows($result_game_rekomendasi) > 0) {
                while ($row_game = mysqli_fetch_assoc($result_game_rekomendasi)) {
                    $nama_game = $row_game['game_name'];
                    $gambar_game = $row_game['banner'];
                    $provider_game = $row_game['provideragent'];
                    $kode_game = $row_game['game_code'];
                    $is_top = isset($row_game['is_top']) ? $row_game['is_top'] : true;
                    $game_type = 'slot';
            ?>
            <div class="w-1/4 md:w-1/5 lg:w-1/6 px-[5px] lg:px-2 inline-block">
                <div class="game-card">
                    <div class="game-card-image-container">
                        <img alt="<?php echo htmlspecialchars($nama_game); ?>" fetchPriority="high" width="300" height="300" decoding="async" data-nimg="1" class="game-card-image" src="<?php echo htmlspecialchars($gambar_game); ?>" />
                        <?php if ($is_top): ?>
                        <div class="rekomendasi-badge-icon">
                            <i class="mdi mdi-star-shooting text-white text-base animate-pulse-fast"></i>
                        </div>
                        <?php endif; ?>
                        <div class="game-card-overlay">
                            <?php
                            if (isset($_SESSION['id_anggota'])) {
                            ?>
                                <a href="playgame/Gameplay.php?game_uid=<?php echo htmlspecialchars($kode_game); ?>&provider_code=<?php echo htmlspecialchars($provider_game); ?>&game_type=<?php echo htmlspecialchars($game_type); ?>" class="play-game-rekomendasi-trigger play-button">Main Game</a>
                            <?php
                            } else {
                            ?>
                                <a href="javascript:registerPopup({ content:'<?php echo htmlspecialchars($isi_1_popup_teks_belum_login_web, ENT_QUOTES, 'UTF-8'); ?>' });" class="play-game-rekomendasi-trigger play-button">Main Game</a>
                            <?php
                            }
                            ?>
                        </div>
                    </div>
                    <p class="game-card-title"><?php echo htmlspecialchars($nama_game); ?></p>
                </div>
            </div>
            <?php
                }
            } else {
                if (!$result_game_rekomendasi) {
                    // error_log("Query game rekomendasi gagal: " . mysqli_error($koneksi));
                }
            }
            ?>
        </div>
    </div>
</div>

<div id="live-casino-content" class="tab-content" style="display: none;">
    <div class="w-full px-3 mt-3 lg:mt-5 order-3">
        <div class="mt-3 -mx-[5px] lg:-mx-2 pb-2 whitespace-nowrap overflow-x-scroll overflow-y-hidden lg:overflow-x-hidden opacity-scroll flex">
            <?php
            $casino_games = [
                [
                    'provider_name' => 'Pragmatic Play',
                    'provider_image' => 'https://cdn-proxy.globalcontentcloud.com/common/dark/casino/pragmaticplay.png',
                    'provider_type' => 'casino?provider=pragmatiplay_live&server=gamexa'
                ],
                [
                    'provider_name' => 'Evolution Gaming',
                    'provider_image' => 'https://cdn-proxy.globalcontentcloud.com/common/dark/casino/evolution.png',
                    'provider_type' => 'casino?provider=evolution&server=gamexa'
                ],
                [
                    'provider_name' => 'Microgaming Live',
                    'provider_image' => 'https://cdn-proxy.globalcontentcloud.com/common/dark/casino/mg_live_grand.png',
                    'provider_type' => 'casino'
                ],
                [
                    'provider_name' => 'Playtech',
                    'provider_image' => 'https://cdn-proxy.globalcontentcloud.com/common/dark/casino/playtech_casino.png',
                    'provider_type' => 'casino'
                ],
                [
                    'provider_name' => 'SA Gaming',
                    'provider_image' => 'https://cdn-proxy.globalcontentcloud.com/common/dark/casino/sagaming.png',
                    'provider_type' => 'casino'
                ],
                [
                    'provider_name' => 'Oriental Game',
                    'provider_image' => 'https://cdn-proxy.globalcontentcloud.com/common/dark/casino/og.png',
                    'provider_type' => 'casino'
                ],
                [
                    'provider_name' => 'World Entertainment',
                    'provider_image' => 'https://cdn-proxy.globalcontentcloud.com/common/dark/casino/ebetlive.png',
                    'provider_type' => 'casino'
                ],
                [
                    'provider_name' => 'Vivo Gaming',
                    'provider_image' => 'https://cdn-proxy.globalcontentcloud.com/common/dark/casino/vivo-gaming.png',
                    'provider_type' => 'casino'
                ],
                [
                    'provider_name' => 'Ezugi',
                    'provider_image' => 'https://cdn-proxy.globalcontentcloud.com/common/dark/casino/ezugi-gaming.png',
                    'provider_type' => 'casino?provider=ezugi&server=gamexa'
                ],
                [
                    'provider_name' => 'LuckyStreak',
                    'provider_image' => 'https://cdn-proxy.globalcontentcloud.com/common/dark/casino/luckystreak.png',
                    'provider_type' => 'casino'
                ]
            ];
            
            $display_games = array_slice($casino_games, 0, 4);
            
            foreach ($display_games as $game) : ?>
                <div class="px-2 mt-4 w-1/4 flex-shrink-0">
                    <a class="casino-card" href="<?php echo htmlspecialchars($alamat_website . $game['provider_type']); ?>">
                        <div class="casino-image-container">
                            <img alt="<?php echo htmlspecialchars($game['provider_name']); ?>" loading="lazy" src="<?php echo htmlspecialchars($game['provider_image']); ?>" class="casino-background-image" />
                            <div class="live-badge">
                                <i class="mdi mdi-record text-red-500 animate-pulse-slow"></i>
                                <span>LIVE</span>
                            </div>
                            <div class="casino-foreground-image">
                                <img alt="<?php echo htmlspecialchars($game['provider_name']); ?>" loading="lazy" src="<?php echo htmlspecialchars($game['provider_image']); ?>" />
                            </div>
                        </div>
                        <div class="casino-details-bg">
                            <p class="casino-title"><?php echo htmlspecialchars($game['provider_name']); ?></p>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<style>
    /* Gabungkan semua CSS dari game-popular.php, game-recomended.php, dan index_casino.php di sini */
    @keyframes pulse-slow {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
    @keyframes pulse-slow-icon {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.1); }
    }
    .casino-card {
        display: block;
        background-color: #282c34;
        border-radius: 0.5rem;
        overflow: hidden;
        position: relative;
        box-shadow: 0 4px 6px rgba(0,0,0,0.2);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        text-align: center;
        text-decoration: none;
    }
    .casino-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 12px rgba(0,0,0,0.4);
    }
    .casino-image-container {
        position: relative;
        width: 100%;
        padding-top: 200%;
        overflow: hidden;
    }
    .casino-background-image {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease, filter 0.3s ease;
        filter: blur(5px) brightness(0.6);
    }
    .casino-card:hover .casino-background-image {
        transform: scale(1.1);
    }
    .live-badge {
        position: absolute;
        top: 0.5rem;
        left: 0.5rem;
        z-index: 20;
        background-color: rgba(0,0,0,0.7);
        color: white;
        padding: 0.2rem 0.6rem;
        border-radius: 9999px;
        display: flex;
        align-items: center;
        gap: 4px;
        font-size: 0.7rem;
        font-weight: bold;
        text-transform: uppercase;
    }
    .live-badge .mdi {
        font-size: 0.8rem;
    }
    .animate-pulse-slow {
        animation: pulse-slow 2s infinite;
    }
    .casino-foreground-image {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 70%;
        height: auto;
        z-index: 15;
        transition: transform 0.3s ease;
    }
    .casino-foreground-image img {
        width: 100%;
        height: auto;
        object-fit: contain;
    }
    .casino-details-bg {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background-color: rgba(0,0,0,0.7);
        padding: 0.75rem 0.5rem;
        transition: background-color 0.3s ease;
    }
    .casino-card:hover .casino-details-bg {
        background-color: rgba(0,0,0,0.9);
    }
    .casino-title {
        color: white;
        font-size: 0.85rem;
        font-weight: 600;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    @keyframes spinPageLoaderPopular {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    @keyframes pulse-fast {
        0%, 100% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.2);
        }
    }
    .game-card {
        position: relative;
        overflow: hidden;
        border-radius: 0.75rem;
        background-color: #374151;
        box-shadow: 0 4px 8px rgba(0,0,0,0.4);
        transition: transform 0.3s ease;
    }
    .game-card:hover {
        transform: scale(1.05);
    }
    .game-card-image-container {
        position: relative;
        width: 100%;
        padding-top: 130%;
        overflow: hidden;
    }
    .game-card-image {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease, filter 0.5s ease;
    }
    .game-card:hover .game-card-image {
        transform: scale(1.1);
        filter: blur(3px);
    }
    .hot-badge-icon,
    .rekomendasi-badge-icon {
        position: absolute;
        top: 0.5rem;
        left: 0.5rem;
        z-index: 20;
        width: 28px;
        height: 28px;
        border-radius: 9999px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .hot-badge-icon { background-color: #ee0000; }
    .rekomendasi-badge-icon { background-color: #3B82F6; }
    .animate-pulse-fast {
        animation: pulse-fast 1.5s infinite;
    }
    .game-card-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
        z-index: 10;
    }
    .game-card:hover .game-card-overlay {
        opacity: 1;
    }
    .play-button {
        background-color: var(--primary);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 9999px;
        font-size: 0.8rem;
        font-weight: bold;
        text-decoration: none;
        transition: background-color 0.3s ease;
    }
    .play-button:hover {
        background-color: #eab308;
    }
    .game-card-title {
        font-size: 0.8rem;
        font-weight: 500;
        text-align: center;
        padding: 0.5rem;
        color: white;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    @media (min-width: 1024px) {
        .lg\:w-1\/6 {
            width: 16.666667%;
        }
    }
    @media (max-width: 639px) {
        .w-1\/4 {
            width: calc(100% / 4);
        }
        .px-\[5px\] {
            padding-left: 5px;
            padding-right: 5px;
        }
    }
</style>

<div id="popularGameLoadingIndicator" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.65); z-index: 99999; flex-direction: column; justify-content: center; align-items: center;">
    <div style="border: 8px solid #4A5568; border-top: 8px solid #FCD34D; border-radius: 50%; width: 60px; height: 60px; animation: spinPageLoaderPopular 1s linear infinite;"></div>
    <p style="color: white; margin-top: 15px; font-size: 1.1em;">Memuat Permainan...</p>
</div>

<div id="rekomendasiGameLoadingIndicator" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.65); z-index: 99999; flex-direction: column; justify-content: center; align-items: center;">
    <div style="border: 8px solid #4A5568; border-top: 8px solid #FCD34D; border-radius: 50%; width: 60px; height: 60px; animation: spinPageLoaderRekomendasi 1s linear infinite;"></div>
    <p style="color: white; margin-top: 15px; font-size: 1.1em;">Memuat Permainan...</p>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const popularGameLinks = document.querySelectorAll('.play-game-popular-trigger');
    const rekomendasiGameLinks = document.querySelectorAll('.play-game-rekomendasi-trigger');
    const popularLoadingIndicator = document.getElementById('popularGameLoadingIndicator');
    const rekomendasiLoadingIndicator = document.getElementById('rekomendasiGameLoadingIndicator');

    function setupPlayLinks(links, loadingIndicator) {
        if (links.length > 0) {
            links.forEach(link => {
                link.addEventListener('click', function(event) {
                    const isLoggedIn = <?php echo isset($_SESSION['id_anggota']) ? 'true' : 'false'; ?>;
                    if (!isLoggedIn) {
                        return;
                    }
                    event.preventDefault();
                    loadingIndicator.style.display = 'flex';
                    const gameUrl = this.href;
                    setTimeout(() => {
                        window.location.href = gameUrl;
                    }, 500);
                });
            });
        }
    }

    setupPlayLinks(popularGameLinks, popularLoadingIndicator);
    setupPlayLinks(rekomendasiGameLinks, rekomendasiLoadingIndicator);
});
</script>