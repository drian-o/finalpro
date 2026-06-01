<?php
ob_start();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'koneksi.php';

$page_game_type = 'cockfight'; 

// Tentukan data provider "virtual" untuk halaman ini, karena tidak ada pilihan provider
$current_provider_slug_initial = 'all_cockfight_games'; // Slug fiktif untuk semua game cockfight
$current_provider_server_initial = 'gamexa_cockfight'; // Server fiktif untuk game cockfight
$current_provider_name_initial = 'Semua Game Sabung Ayam'; // Nama tampilan

// URL search term initial
$search_term_initial = isset($_GET['search']) ? htmlspecialchars(trim($_GET['search']), ENT_QUOTES) : '';

include_once 'header.php';
global $koneksi, $isi_1_popup_teks_belum_login_web;
?>

<style>
/* CSS UNTUK KOTAK PENCARIAN GAME (Diperbarui agar mirip dengan arcade) */
.game-search-box { /* Menggunakan nama kelas yang sama dengan provider-search-box di arcade.php */
    position: relative;
    margin-bottom: 1rem;
    display: flex; /* Ditambahkan untuk flex layout */
    align-items: center;
    gap: 0.5rem; /* Spasi antara input dan button (meskipun di cockfight tidak ada button) */
}
.game-search-box input {
    flex-grow: 1; /* Biarkan input mengambil ruang yang tersedia */
    background-color: #1f2937;
    border: 1px solid #374151;
    color: white;
    padding: 0.75rem 1rem 0.75rem 2.5rem; /* Sesuaikan padding untuk ikon */
    border-radius: 0.5rem;
    width: 100%; /* Pastikan lebar 100% */
    font-size: 1rem;
    outline: none;
}
.game-search-box .search-icon { /* Menambahkan kelas .search-icon untuk Font Awesome */
    position: absolute;
    left: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    font-size: 20px; /* Ukuran ikon */
}


/* CSS UNTUK TAMPILAN GRID GAME LIST */
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
    border-radius: 0.375rem 0.375rem 0 0; /* Menambahkan border-radius agar konsisten dengan arcade */
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

/* Load More Button & Loading Spinner */
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

/* Media queries untuk tampilan mobile/desktop */
@media (max-width: 767px) {
    .game-grid-container {
        grid-template-columns: repeat(3, 1fr);
    }
}
@media (min-width: 768px) and (max-width: 1023px) {
    .game-grid-container {
        grid-template-columns: repeat(4, 1fr);
    }
}
@media (min-width: 1024px) {
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
						<svg width="17" height="17" viewbox="0 0 24 24" class="text-gray-400" fill="currentColor" xmlns="http://www.w3.org/2000/svg" size="17">
							<path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z"></path>
						</svg>
                        <a class="text-xs pl-1 border-b border-transparent hover:lg:border-primary transition-all duration-200 ease-in-out group-last:text-primary text-primary" href="cockfight">Cockfight</a>
					</div>
				</li>
                <li id="breadcrumb-provider-name" class="inline-flex items-end pr-1 group">
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
        
        <h2 class="text-xl font-bold mb-4" id="main-provider-heading">Game Sabung Ayam GameXa:</h2>
        
        <div id="game-search-section-main" class="game-search-box w-full mt-1 mb-3"> <i class="fas fa-search search-icon"></i> <input type="text" id="game-search-input-main" placeholder="Cari Game Sabung Ayam..." value="<?php echo htmlspecialchars($search_term_initial); ?>">
        </div>

        <h2 class="text-xl font-bold mb-4 mt-2" id="games-section-title">Daftar Game Sabung Ayam:</h2>
        <div id="game-list-container" class="game-grid-container">
            <p class="col-span-full text-center py-10">Memuat game...</p>
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

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js" integrity="sha512-..." crossorigin="anonymous"></script>

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

    // Variabel yang tidak dibutuhkan karena tidak ada pilihan provider
    // const providerSearchInput = document.getElementById('providerSearchInput');
    // const providerSearchSection = document.getElementById('provider-search-section');
    // const providerScrollContainer = document.querySelector('.provider-scroll-container');
    // const mainProviderHeading = document.getElementById('main-provider-heading');

    const gameListContainer = document.getElementById('game-list-container');
    const gamesMessage = document.getElementById('games-message');
    const gameSearchInputMain = document.getElementById('game-search-input-main');
    const breadcrumbProviderName = document.getElementById('breadcrumb-provider-name');
    const currentProviderBreadcrumbText = document.querySelector('.current-provider-breadcrumb-text');
    const gamesSectionTitle = document.getElementById('games-section-title');

    const loadMoreContainer = document.getElementById('load-more-container');
    const loadMoreBtn = document.getElementById('loadMoreBtn');
    const loadMoreLoading = document.getElementById('loadMoreLoading');

    // Default values for this page
    let currentProviderSlug = 'all_cockfight_games'; // Fiktif, mewakili semua game sabung ayam
    let currentProviderServer = 'gamexa_cockfight'; // Fiktif, mewakili server GameXa untuk sabung ayam
    let currentProviderNameDisplay = 'Semua Game Sabung Ayam'; 
    const pageGameType = '<?php echo htmlspecialchars($page_game_type); ?>'; 
    let currentSearchTerm = '<?php echo htmlspecialchars($search_term_initial); ?>';
    let isLoading = false;
    let searchDebounceTimer;

    let currentPageOffset = 0;
    const gamesPerPage = 12;

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
        let url;

        // Untuk halaman cockfight, kita selalu memanggil cockfight_gamelist.php
        url = `ajax/cockfight_gamelist.php?game_type=${encodeURIComponent(gameType)}&provider_code=${encodeURIComponent(providerCode)}${searchParam}${paginationParams}&_=${new Date().getTime()}`;
        
        // Pastikan input pencarian terlihat (div parent-nya)
        const gameSearchSectionMain = document.getElementById('game-search-section-main');
        if (gameSearchSectionMain) gameSearchSectionMain.style.display = 'flex'; // Pastikan container-nya visible

        if (gamesSectionTitle) gamesSectionTitle.style.display = 'block';


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
                    if (searchTermQuery) {
                        gamesMessage.textContent = `Menampilkan ${currentTotalGamesDisplayed} dari ${data.totalGamesOverall} game untuk "${searchTermQuery}" dari ${providerName}.`;
                    }
                    
                    currentPageOffset += data.totalGamesLoaded;

                    if (data.hasMore) {
                        loadMoreContainer.style.display = 'block';
                        loadMoreBtn.style.display = 'inline-block';
                    } else {
                        loadMoreContainer.style.display = 'none';
                    }

                    // Update breadcrumb untuk halaman ini
                    if (currentProviderBreadcrumbText) currentProviderBreadcrumbText.textContent = providerName;
                    if (breadcrumbProviderName) breadcrumbProviderName.classList.remove('hidden');
                    if (gamesSectionTitle) gamesSectionTitle.textContent = 'Daftar Game Sabung Ayam:'; // Judul akan selalu ini
                } else {
                    if (!append) {
                        gameListContainer.innerHTML = `<p class="col-span-full text-center py-5 text-red-500">Gagal memuat game: ${data.message || 'Error tidak diketahui.'}</p>`;
                    }
                    gamesMessage.textContent = data.message || 'Error memuat game.';
                    loadMoreContainer.style.display = 'none';
                }
            })
            .catch(error => {
                if (!append) {
                    gameListContainer.innerHTML = `<p class="col-span-full text-center py-5 text-red-500">Terjadi kesalahan jaringan: ${error.message}.</p>`;
                }
                gamesMessage.textContent = `Error jaringan: ${error.message}.`;
                console.error('Error fetching games:', error);
                loadMoreContainer.style.display = 'none';
            })
            .finally(() => {
                isLoading = false;
                hidePageFullLoading();
                loadMoreLoading.style.display = 'none';
            });
    }

    // Karena tidak ada pilihan provider, tidak ada event listener untuk provider-scroll-item
    // dan tombol 'Kembali ke Daftar Provider' juga tidak ada

    // Event listener untuk pencarian game
    if(gameSearchInputMain) {
        gameSearchInputMain.addEventListener('input', function() {
            clearTimeout(searchDebounceTimer);
            const searchTerm = this.value.trim();
            searchDebounceTimer = setTimeout(() => {
                if (searchTerm !== currentSearchTerm) {
                    currentSearchTerm = searchTerm;
                    currentPageOffset = 0; // Reset offset saat pencarian berubah
                    loadGames(currentProviderSlug, currentProviderServer, pageGameType, currentSearchTerm, currentProviderNameDisplay, false); // Muat ulang
                }
            }, 700);
        });
    }

    // Event listener untuk tombol "Load More"
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function() {
            loadGames(currentProviderSlug, currentProviderServer, pageGameType, currentSearchTerm, currentProviderNameDisplay, true); // Append game
        });
    }

    // Event listener untuk klik game
    if(gameListContainer) { 
        gameListContainer.addEventListener('click', handleGameClick);
    }

    function handleGameClick(event) {
        const targetLink = event.target.closest('.play-game-trigger');

        if (targetLink) {
            event.preventDefault();

            if (!isLoggedIn) {
                if (typeof registerPopup === 'function') {
                    registerPopup({ content: notLoggedInMessage });
                } else {
                    alert(notLoggedInMessage);
                }
                return;
            }
            
            showPageFullLoading();

            const gameCode = targetLink.dataset.gameCode;
            const providerCode = targetLink.dataset.provider; 
            const gameType = targetLink.dataset.gameType;
            const server = targetLink.dataset.server;

            let basePlayUrl;
            if (server === 'gamexa_cockfight' || server === 'gamexa') { // Bisa jadi gamexa_cockfight atau hanya gamexa
                basePlayUrl = "playgame/playGame.php"; // Mengarah ke playGame.php yang universal
            } else {
                alert('Sumber game tidak dikenal atau tidak didukung. Tidak dapat meluncurkan permainan.');
                console.error('Unknown or unsupported game source:', server);
                hidePageFullLoading();
                return;
            }
            
            const finalUrl = `${basePlayUrl}?game_uid=${gameCode}&provider_code=${providerCode}&game_type=${gameType}`; 
            
            window.location.href = finalUrl;
        }
    }

    // Penanganan Tombol Back/Forward Browser untuk halaman ini
    window.addEventListener('popstate', function(event) {
        const urlParams = new URLSearchParams(window.location.search);
        const searchTerm = urlParams.get('search') || '';

        // Reset offset
        currentPageOffset = 0; 
        currentSearchTerm = searchTerm;

        // Muat ulang game dengan search term baru dari URL
        loadGames(currentProviderSlug, currentProviderServer, pageGameType, currentSearchTerm, currentProviderNameDisplay, false);
    });

    // Pemanggilan Awal Saat Halaman Dimuat
    const initialUrlParams = new URLSearchParams(window.location.search);
    const initialSearchTerm = initialUrlParams.get('search');

    currentSearchTerm = initialSearchTerm || '';

    // Selalu muat game cockfight dari awal
    loadGames(currentProviderSlug, currentProviderServer, pageGameType, currentSearchTerm, currentProviderNameDisplay, false);

    // Sembunyikan elemen-elemen yang tidak relevan dengan halaman tanpa pilihan provider
    const mainProviderHeading = document.getElementById('main-provider-heading');
    const providerSearchSection = document.getElementById('provider-search-section'); // Ini tidak ada di cockfight.php, tapi untuk jaga-jaga
    const providerScrollContainer = document.querySelector('.provider-scroll-container'); // Ini tidak ada di cockfight.php, tapi untuk jaga-jaga
    const providerHeaderSection = document.getElementById('provider-header-section'); 

    if (mainProviderHeading) mainProviderHeading.style.display = 'none'; // Sembunyikan "Pilih Provider..."
    if (providerSearchSection) providerSearchSection.style.display = 'none'; // Sembunyikan pencarian provider (jika ada)
    if (providerScrollContainer) providerScrollContainer.style.display = 'none'; // Sembunyikan daftar provider (jika ada)
    if (providerHeaderSection) providerHeaderSection.style.display = 'none'; // Sembunyikan tombol "Kembali ke Daftar Provider"
});
</script>

<?php ob_end_flush(); ?>
<?php include_once 'footer.php'; ?>