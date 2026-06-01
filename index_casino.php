<div class="w-full px-3 mt-3 lg:mt-5 order-3">
    <div class="flex justify-between items-center mb-4 lg:mb-3">
        <div class="casino-title-wrapper flex-grow flex justify-center items-center">
             <p class="md:text-lg font-medium text-white text-center casino-title-container">
                <i class="mdi mdi-dice-5 text-primary text-xl mr-2 animate-pulse-slow-icon"></i>
                Live Casino
            </p>
        </div>
    </div>

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
        
        // Menampilkan 4 provider pertama
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

<style>
    @keyframes pulse-slow {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
    @keyframes pulse-slow-icon {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.1); }
    }

    .casino-title-container {
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
    .casino-title-container .mdi {
        animation: pulse-slow-icon 2s infinite;
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
        padding-top: 200%; /* 3:1 Aspect Ratio (Vertical) */
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
    /* Responsive width adjustments */
    .flex-shrink-0 {
        flex-shrink: 0;
    }
    .w-1\/4 {
        width: calc(100% / 4);
    }
    .px-2 {
        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }
    .mt-4 {
        margin-top: 1rem;
    }
</style>