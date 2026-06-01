<?php
// ajax_get_all_score.php

// Mencegah error jika parameter 'type' tidak ada
$type = isset($_GET['type']) ? $_GET['type'] : '';

if ($type == 'live-score') {
    // --- Logika untuk widget LIVE SCORE ---
    ?>
    <style>
        .slider-container {
            position: relative;
            overflow: hidden;
            margin-top: 20px;
            padding: 0 15px;
        }
        .slider-track {
            display: flex;
            transition: transform 0.3s ease-in-out;
        }
        .widget-card {
            flex: 0 0 auto;
            width: 100%;
            max-width: 300px;
            background-color: #282c34;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
            text-align: center;
            margin-right: 15px;
        }
        .widget-card:last-child {
            margin-right: 0;
        }
        .widget-card .title {
            font-size: 1.2rem;
            font-weight: bold;
            color: #ff9900;
            margin-bottom: 10px;
        }
        .widget-card .widget-content {
            min-height: 250px;
        }
        .nav-button {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background-color: rgba(40, 44, 52, 0.7);
            color: white;
            border: none;
            padding: 10px;
            cursor: pointer;
            z-index: 10;
            border-radius: 50%;
            font-size: 1.5rem;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .nav-button.prev {
            left: 0;
        }
        .nav-button.next {
            right: 0;
        }
        .nav-button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
    </style>

    <div class="slider-container">
        <button class="nav-button prev">&lt;</button>
        <div class="slider-track">
            
            <div class="widget-card">
                <p class="title">Live Score Football</p>
                <div class="widget-content">
                    <div data-widget-type="entityScores" data-entity-type="league" data-entity-id="10" data-lang="id-id" data-widget-id="1d88c4af-d691-4e2c-a4d0-8f11afe4e363" data-limit-height-display="350" data-theme="dark"></div>
                    <div id="powered-by">Powered by<a id="powered-by-link" href="https://www.365scores.com" target="_blank">365Scores.com</a></div>
                </div>
            </div>

            <div class="widget-card">
                <p class="title">Live Score Basketball</p>
                <div class="widget-content">
                    <div data-widget-type="entityScores" data-entity-type="league" data-entity-id="103" data-lang="id-id" data-widget-id="77f26f17-0c2c-4286-a665-1465614ce556" data-limit-height-display="350" data-theme="dark"></div>
                    <div id="powered-by">Powered by<a id="powered-by-link" href="https://www.365scores.com" target="_blank">365Scores.com</a></div>
                </div>
            </div>

            <div class="widget-card">
                <p class="title">Live Score Volleyball</p>
                <div class="widget-content">
                    <div data-widget-type="entityScores" data-entity-type="league" data-entity-id="6171" data-lang="id-id" data-widget-id="b6016234-86dd-4381-babe-ddeefdc9798d" data-limit-height-display="350" data-theme="dark"></div>
                    <div id="powered-by">Powered by<a id="powered-by-link" href="https://www.365scores.com" target="_blank">365Scores.com</a></div>
                </div>
            </div>

            <div class="widget-card">
                <p class="title">Live Score Tenis</p>
                <div class="widget-content">
                    <div data-widget-type="entityScores" data-entity-type="league" data-entity-id="215" data-lang="id-id" data-widget-id="8b631bfa-d9d9-4729-88f8-983cf1c29935" data-limit-height-display="350" data-theme="dark"></div>
                    <div id="powered-by">Powered by<a id="powered-by-link" href="https://www.365scores.com" target="_blank">365Scores.com</a></div>
                </div>
            </div>

        </div>
        <button class="nav-button next">&gt;</button>
    </div>

    <script src="https://widgets.365scores.com/main.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sliderContainer = document.querySelector('.slider-container');
            if (sliderContainer) {
                const sliderTrack = sliderContainer.querySelector('.slider-track');
                const cards = sliderContainer.querySelectorAll('.widget-card');
                const prevButton = sliderContainer.querySelector('.nav-button.prev');
                const nextButton = sliderContainer.querySelector('.nav-button.next');
                let currentIndex = 0;
                const totalCards = cards.length;

                function updateSlider() {
                    if (cards.length === 0) return;
                    const cardWidth = cards[0].offsetWidth + 15; // Lebar kartu + margin-right
                    const offset = -currentIndex * cardWidth;
                    sliderTrack.style.transform = `translateX(${offset}px)`;

                    // Atur status tombol
                    prevButton.disabled = currentIndex === 0;
                    nextButton.disabled = currentIndex === totalCards - 1;
                }

                prevButton.addEventListener('click', () => {
                    if (currentIndex > 0) {
                        currentIndex--;
                        updateSlider();
                    }
                });

                nextButton.addEventListener('click', () => {
                    if (currentIndex < totalCards - 1) {
                        currentIndex++;
                        updateSlider();
                    }
                });

                window.addEventListener('resize', updateSlider);
                updateSlider(); // Panggil pertama kali untuk inisialisasi
            }
        });
    </script>
    <?php
} else if ($type == 'live-result') {
    // --- Logika untuk widget LIVE RESULT ---
    $url = 'https://widgets.livesgp.day/result.php?show_id=89,111,77,72';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $rawHtml = curl_exec($ch);

    if (curl_errno($ch)) {
        echo '<p style="text-align:center; color: red;">Terjadi kesalahan saat mengambil data widget.</p>';
        exit;
    }
    curl_close($ch);

    $data_results = [];
    if (!empty($rawHtml)) {
        $dom = new DOMDocument();
        @$dom->loadHTML($rawHtml);
        $xpath = new DOMXPath($dom);
        $rows = $xpath->query('//table/tbody/tr');
        if ($rows->length > 1) {
            for ($i = 1; $i < $rows->length; $i++) {
                $row = $rows->item($i);
                $cells = $xpath->query('.//td', $row);
                
                if ($cells->length >= 3) {
                    $data_results[] = [
                        'pasaran' => trim($cells->item(0)->textContent),
                        'tanggal' => trim($cells->item(1)->textContent),
                        'hasil' => trim($cells->item(2)->textContent)
                    ];
                }
            }
        }
    }
    ?>
    <h2 class="widget-title">Live Result - Tampilan Kustom</h2>
    <div class="horizontal-scroll-container">
        <?php if (!empty($data_results)) : ?>
            <?php foreach ($data_results as $result) : ?>
                <div class="result-card">
                    <div class="pasaran"><?php echo htmlspecialchars($result['pasaran']); ?></div>
                    <div class="tanggal"><?php echo htmlspecialchars($result['tanggal']); ?></div>
                    <div class="hasil"><?php echo htmlspecialchars($result['hasil']); ?></div>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <p style="text-align:center;">Tidak ada data result yang tersedia.</p>
        <?php endif; ?>
    </div>
    <?php
} else {
    // Jika tidak ada parameter yang valid, kembalikan pesan kosong atau error
    echo '<p style="text-align:center;">Pilih widget untuk ditampilkan.</p>';
}
?>