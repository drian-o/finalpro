<?php

// URL API yang akan diakses
$url = 'https://busanslotid.in/office/game-oc/game/getNodeInfoList?l=id&l=id&parentId=24792061';

// --- Proses mengambil data dengan cURL ---
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// Opsi tambahan untuk cURL
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36');

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo '<p style="text-align:center; color: red;">Terjadi kesalahan saat mengambil data dari API.</p>';
    exit;
}
curl_close($ch);

// --- Menguraikan Data JSON yang diambil ---
$data_results = [];
$json_data = json_decode($response, true);

// Periksa apakah respons JSON valid dan memiliki data yang dibutuhkan
if ($json_data && isset($json_data['success']) && $json_data['success'] == 1 && !empty($json_data['result'])) {
    foreach ($json_data['result'] as $item) {
        if (isset($item['lotteryNodeFetchOutDto'])) {
            $lottery_info = $item['lotteryNodeFetchOutDto'];
            
            $winning_number = $lottery_info['attachInfo']['winningNumber'] ?? 'N/A';

            $data_results[] = [
                'gameId' => $item['gameId'] ?? 'N/A',
                'gameName' => $lottery_info['gameName'] ?? 'N/A',
                'winningNumber' => $winning_number,
                'stopTime' => $lottery_info['lotteryStopTime'] ?? 'N/A',
                'url' => $lottery_info['attachInfo']['url'] ?? '',
                'countDown' => $lottery_info['countDown'] ?? 0,
            ];
        }
    }
} else {
    echo '<p style="text-align:center;">Tidak ada data result yang tersedia.</p>';
}

?>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;700;900&display=swap');
    .widget-container {
        font-family: 'Roboto', sans-serif;
        color: #e0e0e0;
        padding: 10px 0;
        overflow: hidden;
    }
    .horizontal-scroll-container {
        display: flex;
        gap: 10px;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        padding: 0 10px 10px;
        scroll-snap-type: x mandatory;
    }
    .result-card-wrapper {
        flex: 0 0 80%;
        scroll-snap-align: center;
        padding: 5px;
    }
    .result-card {
        background: transparent;
        border: 1px solid rgba(255, 255, 255, 0.15);
        border-radius: 16px;
        padding: 20px;
        text-align: center;
        position: relative;
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.5);
        cursor: pointer;
        
        min-width: 330px; 
        max-width: 400px;
        height: 180px; 
        
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center; 
    }
    .result-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.7);
    }
    .game-name {
        font-size: 1.2rem;
        font-weight: bold;
        color: #ffcc00;
        margin-bottom: 5px;
    }
    .period-text {
        font-size: 0.9rem;
        color: #fff;
        margin-bottom: 5px;
        text-decoration: none;
    }
    .result-number {
        font-size: 1.8rem;
        font-weight: 900;
        letter-spacing: 2px;
        background: linear-gradient(90deg, #00f260, #0575e6);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        text-shadow: 0 2px 5px rgba(0, 0, 0, 0.4);
        margin: 10px 0;
        
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        text-align: center;
    }
    .next-draw-text {
        font-size: 0.9rem;
        color: #b0b0b0;
        margin-top: 5px;
    }

    @media (min-width: 640px) {
        .result-card-wrapper {
            flex: 0 0 calc(50% - 10px); 
        }
    }
    @media (min-width: 1024px) {
        .result-card-wrapper {
            flex: 0 0 calc(33.333% - 12px); 
        }
    }
</style>

<div class="widget-container">
    <div id="result-carousel-container" class="horizontal-scroll-container">
        <?php if (!empty($data_results)) : ?>
            <?php foreach ($data_results as $result) : ?>
                <div class="result-card-wrapper">
                    <div class="result-card">
                        <div class="game-name"><?php echo htmlspecialchars($result['gameName']); ?></div>
                        <a href="<?php echo htmlspecialchars($result['url']); ?>" target="_blank" class="period-text">
                            Periode: <?php echo htmlspecialchars($result['gameId']); ?>
                        </a>
                        <div class="result-number" data-value="<?php echo htmlspecialchars($result['winningNumber']); ?>">
                            <?php echo htmlspecialchars($result['winningNumber']); ?>
                        </div>
                        <div class="next-draw-text">
                            Draw selanjutnya: <?php echo htmlspecialchars(substr($result['stopTime'], 11, 5)); ?> WIB
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <p style="text-align:center;">Tidak ada data result yang tersedia.</p>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('result-carousel-container');
    const items = container.querySelectorAll('.result-card-wrapper');
    const autoScrollInterval = 3000;
    const numberChangeInterval = 1000; 

    function initializeNumberRotation() {
        document.querySelectorAll('.result-number').forEach(element => {
            const dataValue = element.getAttribute('data-value');
            const numbers = dataValue.split(',');

            if (numbers.length > 1) {
                element.style.fontSize = '1.4rem';
                
                let currentIndex = 0;
                setInterval(() => {
                    element.textContent = numbers[currentIndex];
                    currentIndex = (currentIndex + 1) % numbers.length;
                }, numberChangeInterval);
            } else {
                element.textContent = dataValue;
                element.style.fontSize = '1.8rem';
            }
        });
    }

    if (items.length > 0) {
        let currentIndex = 0;
        function scrollToNextItem() {
            if (window.innerWidth < 640) {
                currentIndex = (currentIndex + 1) % items.length;
                items[currentIndex].scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest',
                    inline: 'center'
                });
            }
        }
        let autoScroll = setInterval(scrollToNextItem, autoScrollInterval);
        
        initializeNumberRotation();

        container.addEventListener('touchstart', () => clearInterval(autoScroll));
        container.addEventListener('touchend', () => autoScroll = setInterval(scrollToNextItem, autoScrollInterval));
        container.addEventListener('mouseover', () => clearInterval(autoScroll));
        container.addEventListener('mouseout', () => autoScroll = setInterval(scrollToNextItem, autoScrollInterval));
    }
});
</script>