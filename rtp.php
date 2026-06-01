<?php
ob_start();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'koneksi.php';

$page_game_type = 'slot';

$nexus_providers = [];
$query_nexus_providers = mysqli_query($koneksi, "SELECT provider_code, provider_name, provider_type, provider_status, provider_image, urutan FROM nexus_provider WHERE provider_type = '{$page_game_type}' ORDER BY (urutan = 0) ASC, urutan ASC, provider_name ASC");

if ($query_nexus_providers) {
    while ($p = mysqli_fetch_assoc($query_nexus_providers)) {
        $p['server'] = 'nexus';
        $nexus_providers[] = $p;
    }
} else {
    error_log("Error fetching providers from nexus_provider: " . mysqli_error($koneksi));
}

$combined_slot_providers = $nexus_providers;

$current_provider_slug_initial = isset($_GET['provider']) ? mysqli_real_escape_string($koneksi, $_GET['provider']) : '';
$current_provider_server_initial = isset($_GET['server']) ? mysqli_real_escape_string($koneksi, $_GET['server']) : '';
$current_provider_name_initial = '';

if (!empty($current_provider_slug_initial) && !empty($current_provider_server_initial)) {
    if ($current_provider_slug_initial === 'featured' && $current_provider_server_initial === 'nexus_featured') {
        $current_provider_name_initial = 'Rekomendasi';
    } else {
        foreach ($combined_slot_providers as $provider) {
            if ($provider['provider_code'] === $current_provider_slug_initial && $provider['server'] === $current_provider_server_initial) {
                $current_provider_name_initial = $provider['provider_name'];
                break;
            }
        }
    }

    if (empty($current_provider_name_initial)) {
        $current_provider_slug_initial = '';
        $current_provider_server_initial = '';
    }
}

include_once 'header.php';

global $koneksi, $isi_1_popup_teks_belum_login_web;
$search_term_initial = isset($_GET['search']) ? htmlspecialchars(trim($_GET['search']), ENT_QUOTES) : '';
?>

<style>
/* Base Styles */
.provider-scroll-container {
    display: flex;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    padding-bottom: 1rem;
    gap: 0.5rem;
    background-color: #1f2937;
    border-radius: 0.5rem;
    padding: 0.5rem;
}

.provider-scroll-item {
    flex: 0 0 calc(33.333% - 0.333rem);
    display: flex;
    flex-direction: column;
    align-items: center;
    text-decoration: none;
    color: white;
    background-color: #374151;
    border-radius: 0.375rem;
    overflow: hidden;
    transition: transform 0.2s ease;
    padding-bottom: 0.5rem;
    position: relative;
}
.provider-scroll-item:hover {
    transform: scale(1.03);
}

.provider-scroll-image {
    width: 100%;
    padding-top: 100%;
    position: relative;
    overflow: hidden;
    border-radius: 0.375rem 0.375rem 0 0;
}
.provider-scroll-image img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.provider-scroll-name {
    font-size: 0.75rem;
    font-weight: 500;
    padding: 0.5rem;
    text-align: center;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    width: 100%;
}

/* --- Start: CSS untuk Provider Non-aktif --- */
.provider-scroll-item.maintenance {
    cursor: not-allowed;
    pointer-events: none;
}

.provider-scroll-item.maintenance .provider-scroll-image img {
    filter: grayscale(100%);
    opacity: 0.6;
}

.provider-scroll-item.maintenance .maintenance-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    color: white;
    font-weight: bold;
    font-size: 0.8rem;
    padding: 0.5rem;
}

.provider-scroll-item.maintenance .provider-scroll-name {
    color: #9ca3af;
}
/* --- End: CSS untuk Provider Non-aktif --- */


/* Provider Search Box and Toggle Button */
.provider-search-box,
.game-search-box-style {
    position: relative;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.provider-search-box input,
.game-search-box-style input {
    flex-grow: 1;
    background-color: #1f2937;
    border: 1px solid #374151;
    color: white;
    padding: 0.75rem 1rem 0.75rem 2.5rem;
    border-radius: 0.5rem;
    width: 100%;
    font-size: 1rem;
    outline: none;
}
.provider-search-box .search-icon,
.game-search-box-style .search-icon {
    position: absolute;
    left: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    font-size: 20px;
}

.view-toggle-button {
    background-color: #FCD34D;
    color: #1f2937;
    border: none;
    border-radius: 0.5rem;
    padding: 0.75rem 0.75rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease;
    flex-shrink: 0;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.4);
}
.view-toggle-button:hover {
    background-color: #EAB308;
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.5);
}
.view-toggle-button .fas {
    color: #1f2937;
    font-size: 22px;
}


/* List View Specific Styles */
.provider-list-view {
    flex-direction: column;
    overflow-x: hidden;
    padding-bottom: 0.5rem;
    gap: 0.25rem;
}

.provider-list-view .provider-scroll-item {
    flex: 0 0 auto;
    flex-direction: row;
    padding: 0.5rem;
    border-radius: 0.375rem;
    align-items: center;
    justify-content: flex-start;
    padding-bottom: 0;
}

.provider-list-view .provider-scroll-image {
    width: 50px;
    min-width: 50px;
    height: 50px;
    padding-top: 0;
    border-radius: 0.25rem;
}
.provider-list-view .provider-scroll-image img {
    object-fit: contain;
}

.provider-list-view .provider-scroll-name {
    text-align: left;
    margin-left: 0.75rem;
    flex-grow: 1;
    white-space: normal;
    overflow: hidden;
    text-overflow: ellipsis;
    font-size: 0.875rem;
    padding: 0;
}


/* Game Grid Styles */
.game-grid-container {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.5rem;
    padding: 0.5rem;
    background-color: #1f2937;
    border-radius: 0.5rem;
}
.game-grid-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-decoration: none;
    color: white;
    background-color: #374151;
    border-radius: 0.375rem;
    overflow: hidden;
    transition: transform 0.2s ease;
    padding-bottom: 0.5rem;
}
.game-grid-item:hover {
    transform: scale(1.03);
}
.game-grid-figure {
    position: relative;
    width: 100%;
    padding-top: 100%;
    overflow: hidden;
}
.game-grid-figure img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 0.375rem 0.375rem 0 0;
}
.game-grid-name {
    font-size: 0.75rem;
    font-weight: 500;
    padding: 0.5rem;
    text-align: center;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    width: 100%;
}

#load-more-container {
    text-align: center;
    margin-top: 1rem;
    padding-bottom: 2rem;
}

#loadMoreBtn {
    background-color: #28a745;
    color: white;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 0.375rem;
    cursor: pointer;
    font-size: 1rem;
    transition: background-color 0.3s ease;
}

#loadMoreBtn:hover {
    background-color: #218838;
}

#loadMoreLoading {
    display: none;
    align-items: center;
    justify-content: center;
    margin-top: 1rem;
    color: white;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.spinner {
    border: 4px solid rgba(255, 255, 255, 0.3);
    border-top: 4px solid #FCD34D;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    animation: spin 1s linear infinite;
    margin-right: 0.5rem;
}

/* Modal Styling */
.rtp-modal-overlay {
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
.rtp-modal-overlay.active {
    opacity: 1;
    visibility: visible;
}
.rtp-modal-content {
    background-color: #1f2937;
    border-radius: 1rem;
    width: 90%;
    max-width: 400px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transform: translateY(-20px);
    transition: transform 0.3s ease;
}
.rtp-modal-overlay.active .rtp-modal-content {
    transform: translateY(0);
}
.rtp-modal-close {
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
.rtp-modal-body {
    padding: 1.5rem;
    text-align: center;
}
.rtp-modal-title {
    font-size: 1.25rem;
    font-weight: bold;
    color: #FCD34D;
    margin-bottom: 1rem;
}
.rtp-modal-info p {
    font-size: 0.9rem;
    margin: 0.5rem 0;
    text-align: left;
}
.rtp-modal-info h5 {
    font-weight: bold;
    color: #E5E7EB;
    text-align: left;
    margin-top: 1rem;
}
.rtp-modal-info .rtp-percentage-text {
    font-size: 1.5rem;
    font-weight: bold;
    color: #FCD34D;
}
.rtp-modal-pola-container {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-top: 1rem;
}
.rtp-modal-pola-item {
    background-color: #374151;
    padding: 0.5rem;
    border-radius: 0.5rem;
    font-size: 0.9rem;
    color: #ddd;
    text-align: left;
}
.rtp-modal-pola-item .spin-type-text {
    font-weight: bold;
    color: #FCD34D;
}
.rtp-modal-play-btn {
    background-color: #28a745;
    color: white;
    font-weight: bold;
    padding: 0.75rem 1.5rem;
    border-radius: 0.5rem;
    margin-top: 2rem;
    text-decoration: none;
    display: inline-block;
    transition: background-color 0.2s ease;
}
.rtp-modal-play-btn:hover {
    background-color: #218838;
}
.rtp-progress-bar-container {
    height: 10px;
    background-color: #4B5563;
    border-radius: 9999px;
    overflow: hidden;
    margin-top: 0.5rem;
}
.rtp-progress-bar {
    height: 100%;
    transition: width 0.5s ease-in-out;
    border-radius: 9999px;
}
.rtp-progress-bar.high { background-color: #2ecc71; } /* Green */
.rtp-progress-bar.medium { background-color: #f1c40f; } /* Yellow */
.rtp-progress-bar.low { background-color: #e74c3c; } /* Red */
.live-badge {
    background-color: #E74C3C;
    color: white;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: bold;
    margin-left: 8px;
    animation: pulse 1s infinite;
}
@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}
.rtp-disclaimer {
    font-size: 0.75rem;
    color: #6B7280;
    margin-top: 1.5rem;
    text-align: center;
}
.rtp-icon {
    width: 1.2rem;
    height: 1.2rem;
    margin-right: 0.5rem;
}

/* Responsive Media Queries */
@media (max-width: 767px) {
    /* Grid view (default) */
    .provider-scroll-item {
        flex: 0 0 calc(33.333% - 0.333rem);
    }
    .game-grid-container {
        grid-template-columns: repeat(3, 1fr);
    }
}
@media (min-width: 768px) and (max-width: 1023px) {
    /* Grid view */
    .provider-scroll-item {
        flex: 0 0 calc(25% - 0.375rem);
    }
    .game-grid-container {
        grid-template-columns: repeat(4, 1fr);
    }
}
@media (min-width: 1024px) {
    /* Grid view */
    .provider-scroll-item {
        flex: 0 0 calc(20% - 0.4rem);
    }
    .game-grid-container {
        grid-template-columns: repeat(5, 1fr);
    }
}
</style>

<section class="relative bg-[#000134] min-h-screen text-white">
	<div class="container mx-auto p-3 lg:pb-8 relative z-10">
		<nav class="flex mb-1 lg:mb-2">
			<ol class="flex items-center pb-1 overflow-x-scroll whitespace-nowrap opacity-scroll">
				<li class="inline-flex items-end pr-1">
					<a class="text-xs border-b border-transparent hover:lg:border-primary transition-all duration-200 ease-in-out text-gray-300 hover:text-primary" href="<?php echo htmlspecialchars($alamat_website . 'home'); ?>">Home</a>
				</li>
				<li class="inline-flex items-end pr-1 group">
					<div class="flex items-center">
						<svg width="17" height="17" viewbox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg" size="17">
							<path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z"></path>
						</svg>
                        <a class="text-xs pl-1 border-b border-transparent hover:lg:border-primary transition-all duration-200 ease-in-out group-last:text-primary text-primary" href="rtp">RTP Live</a>
					</div>
				</li>
                <li id="breadcrumb-provider-name" class="inline-flex items-end pr-1 group <?php echo empty($current_provider_slug_initial) ? 'hidden' : ''; ?>">
                    <div class="flex items-center">
                        <svg width="17" height="17" viewbox="0 0 24 24" class="text-gray-400" fill="currentColor" xmlns="http://www.w3.org/2000/svg" size="17">
                            <path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z"></path>
                        </svg>
                        <span class="text-xs pl-1 border-b border-transparent group-last:text-primary current-provider-breadcrumb-text">
                            <?php echo htmlspecialchars($current_provider_name_initial ?? ''); ?>
                        </span>
                    </div>
                </li>
			</ol>
		</nav>

        <h2 class="text-xl font-bold mb-4" id="main-provider-heading">Pilih Provider RTP</h2>
        
        <div class="provider-search-box" id="provider-search-section">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="providerSearchInput" placeholder="Cari Provider...">
            <button id="toggleProviderView" class="view-toggle-button" aria-label="Toggle view">
                <i id="gridIcon" class="fas fa-th"></i> <i id="listIcon" class="fas fa-list hidden"></i> </button>
        </div>

        <div class="provider-scroll-container">
            <a href="#" class="provider-scroll-item play-provider-link"
               data-provider-code="featured"
               data-provider-name="Rekomendasi"
               data-server="nexus_featured"> <figure class="provider-scroll-image">
                    <img alt="Rekomendasi" loading="lazy" src="assets/img/recommendation-icon.png">
                </figure>
                <p class="provider-scroll-name">Rekomendasi</p>
            </a>
            <?php
            if (empty($combined_slot_providers)) {
                echo '<div class="w-full text-center py-10"><p class="text-gray-400">Tidak ada provider slot yang tersedia saat ini di database. Pastikan Anda sudah menjalankan update provider.</p></div>';
            } else {
                foreach ($combined_slot_providers as $provider) {
                    $nama_provider = $provider['provider_name'];
                    $kode_provider = $provider['provider_code'];
                    $provider_server = $provider['server'];
                    $provider_status = $provider['provider_status'];
                    $gambar_provider_path_from_db = $provider['provider_image'];

                    $is_open = ($provider_status === 'open');
                    
                    $gambar_src = '';
                    if (!empty($gambar_provider_path_from_db)) {
                        $gambar_src = htmlspecialchars($gambar_provider_path_from_db);
                    } else {
                        $gambar_src = 'assets/img/default-provider-no-image.jpg';
                    }

                    if ($is_open) : ?>
                        <a href="#" class="provider-scroll-item play-provider-link"
                           data-provider-code="<?php echo htmlspecialchars($kode_provider); ?>"
                           data-provider-name="<?php echo htmlspecialchars($nama_provider); ?>"
                           data-server="<?php echo htmlspecialchars($provider_server); ?>">
                            <figure class="provider-scroll-image">
                                <img alt="<?php echo $nama_provider; ?>" loading="lazy" src="<?php echo $gambar_src; ?>">
                            </figure>
                            <p class="provider-scroll-name"><?php echo $nama_provider; ?></p>
                        </a>
                    <?php else : ?>
                        <div class="provider-scroll-item maintenance">
                            <figure class="provider-scroll-image">
                                <img alt="<?php echo $nama_provider; ?>" loading="lazy" src="<?php echo $gambar_src; ?>">
                                <div class="maintenance-overlay">
                                    <p>Dalam Perbaikan</p>
                                </div>
                            </figure>
                            <p class="provider-scroll-name"><?php echo $nama_provider; ?></p>
                        </div>
                    <?php endif;
                }
            }
            ?>
        </div>

        <div id="provider-header-section" class="flex justify-between items-center lg:pl-3 mb-4" style="display: none;">
            <p class="text-xl font-semibold provider-name-display"></p>
            <a href="rtp" class="btn btn-sm bg-primary text-white py-1 px-3 rounded-md hover:brightness-90 transition-all duration-200 ease-in-out">
                < Kembali ke Daftar Provider
            </a>
        </div>

        <div id="game-search-section-main" class="flex-col items-center w-full mt-1 mb-3" style="display: none;">
            <div id="game-search-input-container" class="game-search-box-style w-full"> <i class="fas fa-search search-icon"></i>
                <input type="text" id="game-search-input-main" placeholder="Cari Game..." value="<?php echo htmlspecialchars($search_term_initial); ?>">
            </div>
        </div>

        <h2 class="text-xl font-bold mb-4 mt-2" id="games-section-title">Game RTP:</h2>
        <div id="game-list-container" class="game-grid-container">
            <p class="col-span-full text-center py-10">Pilih provider untuk melihat game.</p>
        </div>

        <div class="w-full">
            <div class="flex justify-center my-5 lg:mt-8">
                <span id="games-message" class="text-[10px] text-center font-medium bg-background-secondary px-3 py-2 rounded-lg"></span>
            </div>
        </div>

        <div id="load-more-container" style="display: none;">
            <button id="loadMoreBtn">Muat Lebih Banyak</button>
            <div id="loadMoreLoading" class="hidden">
                <div class="spinner"></div>
                Memuat...
            </div>
        </div>
	</div>
</section>

<div id="rtpModal" class="rtp-modal-overlay">
    <div class="rtp-modal-content">
        <span class="rtp-modal-close" id="closeRtpModal">&times;</span>
        <div class="rtp-modal-body">
            <h4 id="modalGameName" class="rtp-modal-title"></h4>
            
            <div class="rtp-modal-info">
                <h5>RTP Live:</h5>
                <p>
                    <span id="modalRtpPercentage" class="rtp-percentage-text"></span>
                </p>
                <div class="rtp-progress-bar-container">
                    <div id="modalRtpProgressBar" class="rtp-progress-bar"></div>
                </div>

                <h5>Jam Gacor:</h5>
                <p>
                    <span id="modalGacorTime" class="font-bold text-lg"></span>
                    <span id="modalLiveBadge" class="live-badge hidden">LIVE</span>
                </p>
                
                <h5>Pola Main:</h5>
                <div id="modalPolaMain" class="rtp-modal-pola-container"></div>

                <p class="rtp-disclaimer">Disclaimer: Data ini hanyalah prediksi berdasarkan algoritma. Hasil permainan dapat bervariasi. Bermainlah dengan bijak!</p>
            </div>

            <a href="#" id="modalPlayButton" class="rtp-modal-play-btn play-game-trigger">MAIN SEKARANG</a>
        </div>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/scripts/mdi.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const isLoggedIn = <?php echo isset($_SESSION['id_anggota']) ? 'true' : 'false'; ?>;
    const notLoggedInMessage = '<?php echo htmlspecialchars($isi_1_popup_teks_belum_login_web ?? 'Silakan login untuk bermain.', ENT_QUOTES, 'UTF-8'); ?>';

    const pageLoadingIndicator = document.createElement('div');
    pageLoadingIndicator.id = 'pageFullLoadingIndicator';
    pageLoadingIndicator.style.cssText = 'display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.65); z-index: 99999; flex-direction: column; justify-content: center; align-items: center;';
    pageLoadingIndicator.innerHTML = `
        <div style="border: 8px solid #4A5568; border-top: 8px solid #FCD34D; border-radius: 50%; width: 60px; height: 60px; animation: spinPageLoaderFull 1s linear infinite;"></div>
        <p style="color: white; margin-top: 15px; font-size: 1.1em;">Memuat Permainan...</p>
    `;
    document.body.appendChild(pageLoadingIndicator);

    const keyframesFull = `@keyframes spinPageLoaderFull { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }`;
    const styleSheetFull = document.createElement("style");
    styleSheetFull.type = "text/css";
    styleSheetFull.innerText = keyframesFull;
    document.head.appendChild(styleSheetFull);

    function showPageFullLoading() { pageLoadingIndicator.style.display = 'flex'; }
    function hidePageFullLoading() { pageLoadingIndicator.style.display = 'none'; }

    const providerSearchInput = document.getElementById('providerSearchInput');
    const providerSearchSection = document.getElementById('provider-search-section');
    const providerScrollContainer = document.querySelector('.provider-scroll-container');
    const gameListContainer = document.getElementById('game-list-container');
    const gamesMessage = document.getElementById('games-message');
    const gameSearchInputMain = document.getElementById('game-search-input-main');
    const providerHeaderSection = document.getElementById('provider-header-section');
    const breadcrumbProviderName = document.getElementById('breadcrumb-provider-name');
    const currentProviderBreadcrumbText = document.querySelector('.current-provider-breadcrumb-text');
    const mainProviderHeading = document.getElementById('main-provider-heading');
    const gamesSectionTitle = document.getElementById('games-section-title');

    const loadMoreContainer = document.getElementById('load-more-container');
    const loadMoreBtn = document.getElementById('loadMoreBtn');
    const loadMoreLoading = document.getElementById('loadMoreLoading');

    const toggleProviderViewBtn = document.getElementById('toggleProviderView');
    const gridIcon = document.getElementById('gridIcon');
    const listIcon = document.getElementById('listIcon');

    const gameSearchSectionMain = document.getElementById('game-search-section-main');
    const gameSearchInputContainer = document.getElementById('game-search-input-container');

    const rtpModal = document.getElementById('rtpModal');
    const closeRtpModalBtn = document.getElementById('closeRtpModal');
    const modalGameName = document.getElementById('modalGameName');
    const modalRtpPercentage = document.getElementById('modalRtpPercentage');
    const modalRtpProgressBar = document.getElementById('modalRtpProgressBar');
    const modalGacorTime = document.getElementById('modalGacorTime');
    const modalLiveBadge = document.getElementById('modalLiveBadge');
    const modalPolaMain = document.getElementById('modalPolaMain');
    const modalPlayButton = document.getElementById('modalPlayButton');

    let currentProviderSlug = '<?php echo htmlspecialchars($current_provider_slug_initial); ?>';
    let currentProviderServer = '<?php echo htmlspecialchars($current_provider_server_initial); ?>';
    let currentProviderNameDisplay = '<?php echo htmlspecialchars($current_provider_name_initial ?? ''); ?>';
    const pageGameType = '<?php echo htmlspecialchars($page_game_type); ?>';
    let currentSearchTerm = '<?php echo htmlspecialchars($search_term_initial); ?>';
    let isLoading = false;
    let searchDebounceTimer;

    let currentPageOffset = 0;
    const gamesPerPage = 20;

    let isListView = localStorage.getItem('providerView') === 'list';

    function setProviderView(isList) {
        if (isList) {
            providerScrollContainer.classList.add('provider-list-view');
            if (gridIcon) gridIcon.classList.add('hidden');
            if (listIcon) listIcon.classList.remove('hidden');
        } else {
            providerScrollContainer.classList.remove('provider-list-view');
            if (gridIcon) gridIcon.classList.remove('hidden');
            if (listIcon) listIcon.classList.add('hidden');
        }
        localStorage.setItem('providerView', isList ? 'list' : 'grid');
    }

    setProviderView(isListView);

    if (toggleProviderViewBtn) {
        toggleProviderViewBtn.addEventListener('click', function() {
            isListView = !isListView;
            setProviderView(isListView);
        });
    }

    function checkGacorTime(gacorTime) {
        const now = new Date();
        const currentHour = now.getHours();
        const currentMinute = now.getMinutes();

        const [start, end] = gacorTime.split(' - ');
        const [startHour, startMinute] = start.split(':').map(Number);
        const [endHour, endMinute] = end.split(':').map(Number);

        const isGacor = (currentHour > startHour || (currentHour === startHour && currentMinute >= startMinute)) &&
                        (currentHour < endHour || (currentHour === endHour && currentMinute <= endMinute));
        
        return isGacor;
    }

    function loadGames(providerCode, providerServer, gameType, searchTermQuery = '', providerName = '', append = false) {
        if (isLoading) return;
        isLoading = true;

        if (!append) {
            gameListContainer.innerHTML = '<p class="col-span-full text-center py-10 flex items-center justify-center"><span class="animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2"></span> Memuat game...</p>';
            gamesMessage.textContent = 'Memuat game...';
            showPageFullLoading();
            loadMoreContainer.style.display = 'none';
        } else {
            loadMoreBtn.style.display = 'none';
            loadMoreLoading.style.display = 'flex';
        }

        const searchParam = searchTermQuery ? `&search=${encodeURIComponent(searchTermQuery)}` : '';
        const paginationParams = `&limit=${gamesPerPage}&offset=${currentPageOffset}`;
        const url = `ajax/rtp_gamelist.php?provider_code=${encodeURIComponent(providerCode)}&server=${encodeURIComponent(providerServer)}&game_type=${encodeURIComponent(gameType)}${searchParam}${paginationParams}&_=${new Date().getTime()}`;

        fetch(url)
            .then(response => {
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status} dari ${url}`);
                return response.json();
            })
            .then(data => {
                if (!append) {
                    gameListContainer.innerHTML = '';
                }

                if (data.success) {
                    if (data.gamesHtml) {
                        gameListContainer.insertAdjacentHTML('beforeend', data.gamesHtml);
                    } else if (currentPageOffset === 0) {
                        gameListContainer.innerHTML = `<p class="col-span-full text-center py-5 text-gray-400">${data.message || 'Tidak ada game yang tersedia.'}</p>`;
                    }

                    const currentTotalGamesDisplayed = gameListContainer.children.length;
                    gamesMessage.textContent = `Menampilkan ${currentTotalGamesDisplayed} dari ${data.totalGamesOverall} game untuk ${providerName}.`;
                    
                    currentPageOffset += data.totalGamesLoaded;

                    if (data.hasMore) {
                        loadMoreContainer.style.display = 'block';
                        loadMoreBtn.style.display = 'inline-block';
                    } else {
                        loadMoreContainer.style.display = 'none';
                    }

                    if (providerCode !== 'featured') {
                        if (providerHeaderSection) {
                            providerHeaderSection.style.display = 'flex';
                            providerHeaderSection.querySelector('.provider-name-display').textContent = providerName;
                        }
                        if (currentProviderBreadcrumbText) currentProviderBreadcrumbText.textContent = providerName;
                        if (breadcrumbProviderName) breadcrumbProviderName.classList.remove('hidden');
                        if (gamesSectionTitle) gamesSectionTitle.textContent = providerName;
                        
                        if (providerScrollContainer) providerScrollContainer.style.display = 'none';
                        if (mainProviderHeading) mainProviderHeading.style.display = 'none';
                        if (providerSearchSection) providerSearchSection.style.display = 'none';
                        if (toggleProviderViewBtn) toggleProviderViewBtn.style.display = 'none';

                        if (gameSearchSectionMain) gameSearchSectionMain.style.display = 'flex';
                        
                        if (gameSearchInputMain) {
                            gameSearchInputMain.placeholder = `Cari di ${providerName}...`;
                        }
                    } else {
                        if (providerHeaderSection) providerHeaderSection.style.display = 'none';
                        if (breadcrumbProviderName) breadcrumbProviderName.classList.add('hidden');
                        if (gamesSectionTitle) gamesSectionTitle.textContent = 'Game RTP:';

                        if (providerScrollContainer) providerScrollContainer.style.display = 'flex';
                        if (mainProviderHeading) mainProviderHeading.style.display = 'block';
                        if (providerSearchSection) providerSearchSection.style.display = 'flex';
                        if (toggleProviderViewBtn) toggleProviderViewBtn.style.display = 'flex';
                        setProviderView(isListView);

                        if (gameSearchSectionMain) gameSearchSectionMain.style.display = 'none';
                    }
                } else {
                    if (!append) {
                        gameListContainer.innerHTML = `<p class="col-span-full text-center py-5 text-red-500">Gagal memuat game: ${data.message || 'Error tidak diketahui.'}</p>`;
                    }
                    gamesMessage.textContent = data.message || 'Error memuat game.';
                    loadMoreContainer.style.display = 'none';
                    if (providerCode !== 'featured' && !append) {
                        loadGames('featured', 'nexus_featured', pageGameType, '', 'Rekomendasi', false);
                    }
                }
            })
            .catch(error => {
                if (!append) {
                    gameListContainer.innerHTML = `<p class="col-span-full text-center py-5 text-red-500">Terjadi kesalahan jaringan: ${error.message}.</p>`;
                }
                gamesMessage.textContent = `Error jaringan: ${error.message}.`;
                console.error('Error fetching games:', error);
                loadMoreContainer.style.display = 'none';
                if (providerCode !== 'featured' && !append) {
                    loadGames('featured', 'nexus_featured', pageGameType, '', 'Rekomendasi', false);
                }
            })
            .finally(() => {
                isLoading = false;
                hidePageFullLoading();
                loadMoreLoading.style.display = 'none';
            });
    }

    if (providerScrollContainer) {
        providerScrollContainer.addEventListener('click', function(event) {
            const targetLink = event.target.closest('.play-provider-link');
            if (targetLink) {
                event.preventDefault();

                const providerCode = targetLink.dataset.providerCode;
                const providerServer = targetLink.dataset.server;
                const providerName = targetLink.dataset.providerName;
                
                document.querySelectorAll('.provider-scroll-item').forEach(item => item.classList.remove('active'));
                targetLink.classList.add('active');

                currentProviderSlug = providerCode;
                currentProviderServer = providerServer;
                currentProviderNameDisplay = providerName;
                currentSearchTerm = '';
                currentPageOffset = 0;

                const newUrl = `rtp?provider=${encodeURIComponent(providerCode)}&server=${encodeURIComponent(providerServer)}`;
                history.pushState({ provider: providerCode, server: providerServer, name: providerName }, '', newUrl);

                loadGames(currentProviderSlug, currentProviderServer, pageGameType, currentSearchTerm, currentProviderNameDisplay, false);

                if (providerHeaderSection) {
                    providerHeaderSection.style.display = 'flex';
                    providerHeaderSection.querySelector('.provider-name-display').textContent = providerName;
                }
                if (gameSearchInputMain) {
                    gameSearchInputMain.placeholder = `Cari game di ${providerName}...`;
                    gameSearchInputMain.value = '';
                }

                if (mainProviderHeading) mainProviderHeading.style.display = 'none';
                if (providerScrollContainer) providerScrollContainer.style.display = 'none';
                if (providerSearchSection) providerSearchSection.style.display = 'none';

                if (gameSearchSectionMain) gameSearchSectionMain.style.display = 'flex';
                if (gamesSectionTitle) {
                    gamesSectionTitle.style.display = 'block';
                    gamesSectionTitle.textContent = providerName;
                }

                window.scrollTo({
                    top: gameSearchInputMain ? gameSearchInputMain.offsetTop - 20 : gameListContainer.offsetTop - 20,
                    behavior: 'smooth'
                });
            }
        });
    }

    const backToProviderListLink = document.querySelector('#provider-header-section a[href="rtp"]');
    if (backToProviderListLink) {
        backToProviderListLink.addEventListener('click', function(event) {
            event.preventDefault();

            currentProviderSlug = '';
            currentProviderServer = '';
            currentProviderNameDisplay = '';
            currentSearchTerm = '';
            currentPageOffset = 0;

            history.pushState({}, '', 'rtp');

            if (providerScrollContainer) providerScrollContainer.style.display = 'flex';
            if (mainProviderHeading) mainProviderHeading.style.display = 'block';
            if (providerSearchSection) providerSearchSection.style.display = 'flex';
            if (gamesSectionTitle) gamesSectionTitle.textContent = 'Game RTP:';
            if (toggleProviderViewBtn) toggleProviderViewBtn.style.display = 'flex';
            setProviderView(isListView);

            if (providerHeaderSection) providerHeaderSection.style.display = 'none';
            if (gameSearchSectionMain) gameSearchSectionMain.style.display = 'none';
            if (gameSearchInputMain) {
                gameSearchInputMain.value = '';
            }
            if (breadcrumbProviderName) breadcrumbProviderName.classList.add('hidden');

            loadGames('featured', 'nexus_featured', pageGameType, '', 'Rekomendasi', false);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    if (providerSearchInput) {
        providerSearchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const currentProviderItems = providerScrollContainer.querySelectorAll('.provider-scroll-item');

            currentProviderItems.forEach(item => {
                const providerName = item.dataset.providerName ? item.dataset.providerName.toLowerCase() : '';
                if (providerName.includes(searchTerm)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }

    if(gameSearchInputMain) {
        gameSearchInputMain.addEventListener('input', function() {
            clearTimeout(searchDebounceTimer);
            const searchTerm = this.value.trim();
            searchDebounceTimer = setTimeout(() => {
                if (searchTerm !== currentSearchTerm) {
                    currentSearchTerm = searchTerm;
                    currentPageOffset = 0;
                    loadGames(currentProviderSlug, currentProviderServer, pageGameType, currentSearchTerm, currentProviderNameDisplay, false);
                }
            }, 700);
        });
    }

    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function() {
            loadGames(currentProviderSlug, currentProviderServer, pageGameType, currentSearchTerm, currentProviderNameDisplay, true);
        });
    }

    // Fungsi ini akan dipanggil saat tombol "Main Sekarang" di modal diklik
    function handlePlayClick(event) {
        const targetButton = event.target.closest('.play-game-trigger');
        if (targetButton) {
            event.preventDefault();
            
            if (!isLoggedIn) {
                closeModal();
                if (typeof registerPopup === 'function') {
                    registerPopup({ content: notLoggedInMessage });
                } else {
                    alert(notLoggedInMessage);
                }
                return;
            }

            showPageFullLoading();
            const gameCode = targetButton.dataset.gameCode;
            const providerCode = targetButton.dataset.provider;
            const gameType = targetButton.dataset.gameType;
            const server = targetButton.dataset.server;
            let basePlayUrl;
            if (server === 'nexus') {
                basePlayUrl = "playgame/Gameplay.php";
            } else {
                alert('Sumber game tidak dikenal.');
                hidePageFullLoading();
                return;
            }
            const finalUrl = `${basePlayUrl}?game_uid=${gameCode}&provider_code=${providerCode}&game_type=${gameType}`;
            window.location.href = finalUrl;
        }
    }

    if(gameListContainer) {
        gameListContainer.addEventListener('click', function(event) {
            const targetLink = event.target.closest('.rtp-game-card-link');
            if (targetLink) {
                event.preventDefault();
                const gameCard = targetLink;
                const gameName = gameCard.querySelector('.game-grid-name').textContent;
                const rtp = gameCard.dataset.rtp;
                const gacorTime = gameCard.dataset.gacorTime;
                const polaMain = JSON.parse(gameCard.dataset.polaMain);

                modalGameName.textContent = gameName;
                modalRtpPercentage.textContent = rtp + '%';
                
                modalRtpProgressBar.style.width = rtp + '%';
                modalRtpProgressBar.className = 'rtp-progress-bar';
                if (rtp >= 85) {
                    modalRtpProgressBar.classList.add('high');
                } else if (rtp >= 75) {
                    modalRtpProgressBar.classList.add('medium');
                } else {
                    modalRtpProgressBar.classList.add('low');
                }

                modalGacorTime.textContent = gacorTime;
                if (checkGacorTime(gacorTime)) {
                    modalLiveBadge.classList.remove('hidden');
                } else {
                    modalLiveBadge.classList.add('hidden');
                }
                
                modalPolaMain.innerHTML = '';
                polaMain.forEach(pola => {
                    const polaItem = document.createElement('div');
                    polaItem.className = 'rtp-modal-pola-item';
                    const iconSpin = pola.type === 'XX' ? 'mdi-speedometer' : 'mdi-rocket';
                    const iconMode = pola.mode.toLowerCase() === 'auto' ? 'mdi-autorenew' : 'mdi-hand-pointing-right';
                    polaItem.innerHTML = `
                        <div class="flex items-center">
                            <span class="inline-flex items-center me-2">
                                <i class="mdi ${iconSpin} rtp-icon"></i>
                                <span class="spin-type-text">${pola.type}</span>
                            </span>
                            <span class="inline-flex items-center">
                                <i class="mdi ${iconMode} rtp-icon"></i>
                                <span class="spin-type-text">${pola.mode}</span>
                            </span>
                        </div>
                        <span class="mt-1">${pola.spins} Spins</span>
                    `;
                    modalPolaMain.appendChild(polaItem);
                });

                modalPlayButton.dataset.gameCode = gameCard.dataset.gameCode;
                modalPlayButton.dataset.provider = gameCard.dataset.provider;
                modalPlayButton.dataset.gameType = gameCard.dataset.gameType;
                modalPlayButton.dataset.server = gameCard.dataset.server;

                rtpModal.classList.add('active');
            }
        });
    }

    function closeModal() {
        rtpModal.classList.remove('active');
    }

    closeRtpModalBtn.addEventListener('click', closeModal);
    rtpModal.addEventListener('click', function(event) {
        if (event.target === rtpModal) {
            closeModal();
        }
    });

    if (modalPlayButton) {
        modalPlayButton.addEventListener('click', handlePlayClick);
    }

    window.addEventListener('popstate', function(event) {
        const urlParams = new URLSearchParams(window.location.search);
        const provider = urlParams.get('provider');
        const server = urlParams.get('server');
        const searchTerm = urlParams.get('search') || '';

        currentPageOffset = 0;

        if (provider && server) {
            if (server !== 'nexus' && server !== 'nexus_featured') {
                history.replaceState({}, '', 'rtp');
                loadGames('featured', 'nexus_featured', pageGameType, '', 'Rekomendasi', false);
                return;
            }
            
            currentProviderSlug = provider;
            currentProviderServer = server;
            currentSearchTerm = searchTerm;
            const providerElement = document.querySelector(`.provider-scroll-item[data-provider-code="${provider}"][data-server="${server}"]`);
            currentProviderNameDisplay = providerElement ? providerElement.dataset.providerName : '';
            if (currentProviderNameDisplay === '' && provider === 'featured' && server === 'nexus_featured') {
                currentProviderNameDisplay = 'Rekomendasi';
            }

            document.querySelectorAll('.provider-scroll-item').forEach(item => item.classList.remove('active'));
            if (providerElement) {
                providerElement.classList.add('active');
            }

            if (providerScrollContainer) providerScrollContainer.style.display = 'none';
            if (mainProviderHeading) mainProviderHeading.style.display = 'none';
            if (providerSearchSection) providerSearchSection.style.display = 'none';
            if (toggleProviderViewBtn) toggleProviderViewBtn.style.display = 'none';
            
            if (providerHeaderSection) {
                providerHeaderSection.style.display = 'flex';
                providerHeaderSection.querySelector('.provider-name-display').textContent = currentProviderNameDisplay;
            }
            if (gameSearchSectionMain) gameSearchSectionMain.style.display = 'flex';
            
            if (gameSearchInputMain) {
                gameSearchInputMain.placeholder = `Cari di ${currentProviderNameDisplay}...`;
                gameSearchInputMain.value = currentSearchTerm;
            }
            if (currentProviderBreadcrumbText) currentProviderBreadcrumbText.textContent = currentProviderNameDisplay;
            if (breadcrumbProviderName) breadcrumbProviderName.classList.remove('hidden');
            if (gamesSectionTitle) gamesSectionTitle.textContent = currentProviderNameDisplay;
            
            loadGames(currentProviderSlug, currentProviderServer, pageGameType, currentSearchTerm, currentProviderNameDisplay, false);
        } else {
            currentProviderSlug = '';
            currentProviderServer = '';
            currentProviderNameDisplay = '';
            currentSearchTerm = '';
            if (providerScrollContainer) providerScrollContainer.style.display = 'flex';
            if (mainProviderHeading) mainProviderHeading.style.display = 'block';
            if (providerSearchSection) providerSearchSection.style.display = 'flex';
            if (gamesSectionTitle) gamesSectionTitle.textContent = 'Game RTP:';
            if (toggleProviderViewBtn) toggleProviderViewBtn.style.display = 'flex';
            setProviderView(isListView);

            if (providerHeaderSection) providerHeaderSection.style.display = 'none';
            if (gameSearchSectionMain) gameSearchSectionMain.style.display = 'none';
            if (gameSearchInputMain) {
                gameSearchInputMain.value = '';
            }
            if (breadcrumbProviderName) breadcrumbProviderName.classList.add('hidden');
            
            loadGames('featured', 'nexus_featured', pageGameType, '', 'Rekomendasi', false);
        }
    });

    const initialUrlParams = new URLSearchParams(window.location.search);
    const initialProviderSlug = initialUrlParams.get('provider');
    const initialProviderServer = initialUrlParams.get('server');
    const initialSearchTerm = initialUrlParams.get('search');

    if (initialProviderSlug && initialProviderServer) {
        if (initialProviderServer !== 'nexus' && initialProviderServer !== 'nexus_featured') {
            history.replaceState({}, '', 'rtp');
            loadGames('featured', 'nexus_featured', pageGameType, '', 'Rekomendasi', false);
            return;
        }

        currentProviderSlug = initialProviderSlug;
        currentProviderServer = initialProviderServer;
        currentSearchTerm = initialSearchTerm || '';

        const providerElement = document.querySelector(`.provider-scroll-item[data-provider-code="${initialProviderSlug}"][data-server="${initialProviderServer}"]`);
        currentProviderNameDisplay = providerElement ? providerElement.dataset.providerName : '';
        if (currentProviderNameDisplay === '' && initialProviderSlug === 'featured' && initialProviderServer === 'nexus_featured') {
            currentProviderNameDisplay = 'Rekomendasi';
        }
        
        if (providerElement) {
            providerElement.classList.add('active');
        }

        loadGames(currentProviderSlug, currentProviderServer, pageGameType, currentSearchTerm, currentProviderNameDisplay, false);

        if (providerScrollContainer) providerScrollContainer.style.display = 'none';
        if (mainProviderHeading) mainProviderHeading.style.display = 'none';
        if (providerSearchSection) providerSearchSection.style.display = 'none';
        if (toggleProviderViewBtn) toggleProviderViewBtn.style.display = 'none';

        if (providerHeaderSection) {
            providerHeaderSection.style.display = 'flex';
            providerHeaderSection.querySelector('.provider-name-display').textContent = currentProviderNameDisplay;
        }
        if (gameSearchSectionMain) gameSearchSectionMain.style.display = 'flex';
        
        if (gameSearchInputMain) {
            gameSearchInputMain.placeholder = `Cari game di ${currentProviderNameDisplay}...`;
            gameSearchInputMain.value = currentSearchTerm;
        }
        if (currentProviderBreadcrumbText) currentProviderBreadcrumbText.textContent = currentProviderNameDisplay;
        if (breadcrumbProviderName) breadcrumbProviderName.classList.remove('hidden');
        if (gamesSectionTitle) {
            gamesSectionTitle.style.display = 'block';
            gamesSectionTitle.textContent = currentProviderNameDisplay;
        }
    } else {
        loadGames('featured', 'nexus_featured', pageGameType, '', 'Rekomendasi', false);
    }
});
</script>

<?php ob_end_flush(); ?>
<?php include_once 'footer.php'; ?>