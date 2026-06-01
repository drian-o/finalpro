<div class="w-full px-3 mt-3 lg:mt-5 order-6">
	<div class="flex justify-between items-center mb-4 lg:mb-3">
        <div class="flex-grow flex justify-center items-center">
            <p class="md:text-lg font-medium text-white text-center game-rekomendasi-title-container">
                <i class="mdi mdi-star text-primary text-xl mr-2 animate-pulse-slow-icon"></i>
                Game Rekomendasi
            </p>
        </div>
	</div>
	<div class="mt-3 -mx-[5px] lg:-mx-2 pb-2 whitespace-nowrap overflow-x-scroll overflow-y-hidden lg:overflow-x-hidden opacity-scroll">
		<?php
		// Query untuk mengambil daftar game rekomendasi dari database
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
            // Optional: Handle the case where no popular games are found
			if (!$result_game_rekomendasi) {
                // error_log("Query game rekomendasi gagal: " . mysqli_error($koneksi));
            }
		}
		?>
	</div>
</div>

<style>
    @keyframes spinPageLoaderRekomendasi {
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
    @keyframes pulse-slow-icon {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.1); }
    }

    .game-rekomendasi-title-container {
        display: flex;
        align-items: center;
        justify-content: center;
        width: max-content;
        margin: 0 auto;
        padding: 0.2rem 1rem;
        border-radius: 9999px;
        background-color: #374151;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    .game-rekomendasi-title-container .mdi {
        animation: pulse-slow-icon 2s infinite;
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
        padding-top: 130%; /* Set a fixed aspect ratio for a uniform look */
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
    .rekomendasi-badge-icon {
        position: absolute;
        top: 0.5rem;
        left: 0.5rem;
        z-index: 20;
        background-color: #3B82F6; /* Warna biru */
        width: 28px;
        height: 28px;
        border-radius: 9999px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
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
    /* Responsive styles for smaller cards on larger screens */
    @media (min-width: 1024px) {
        .lg\:w-1\/6 {
            width: 16.666667%; /* 6 items per row */
        }
    }
    /* Penyesuaian untuk tampilan 4.5 kartu */
    @media (max-width: 639px) { /* Mobile */
        .w-1\/4 {
            width: calc(100% / 4);
        }
        .px-\[5px\] {
            padding-left: 5px;
            padding-right: 5px;
        }
        .w-full > div {
            flex: 0 0 auto;
        }
    }
</style>
<div id="rekomendasiGameLoadingIndicator" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.65); z-index: 99999; flex-direction: column; justify-content: center; align-items: center;">
    <div style="border: 8px solid #4A5568; border-top: 8px solid #FCD34D; border-radius: 50%; width: 60px; height: 60px; animation: spinPageLoaderRekomendasi 1s linear infinite;"></div>
    <p style="color: white; margin-top: 15px; font-size: 1.1em;">Memuat Permainan...</p>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const rekomendasiGameLinks = document.querySelectorAll('.play-game-rekomendasi-trigger');
    const loadingIndicator = document.getElementById('rekomendasiGameLoadingIndicator');

    if (rekomendasiGameLinks.length > 0) {
        rekomendasiGameLinks.forEach(link => {
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
                }, 500); // Jeda 500ms
            });
        });
    }
});
</script>