<div class="w-full px-3 mt-1 lg:mt-4 order-last">
    <div class="flex justify-between items-center">
        <p class="md:text-xl font-bold text-white text-2xl">PROMO</p>
        <?php /*
        <a class="text-primary text-sm md:text-base transition-all duration-300 ease-in-out border-b border-transparent hover:lg:border-primary" href="promo">
            Show All
            <svg width="20" height="20" viewBox="0 0 24 24" fill="var(--primary)" size="20">
                <path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="var(--primary)"></path>
            </svg>
        </a>
        */ ?>
    </div>

    <?php
    $promosi = mysqli_query($koneksi, "SELECT * FROM promosi");
    $promosi_data = [];
    if ($promosi) {
        while ($data_promosi = mysqli_fetch_array($promosi)) {
            if ($data_promosi['gambar_promosi'] && $data_promosi['judul_promosi']) {
                $promosi_data[] = $data_promosi;
            }
        }
    }
    ?>
    
    <style>
        .promo-card {
            display: block;
            position: relative;
            background-color: #1c1c1c; /* Latar belakang lebih gelap */
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5); /* Bayangan lebih dalam */
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            text-decoration: none;
        }

        .promo-card:hover {
            transform: translateY(-8px); /* Efek angkat saat di-hover */
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.7); /* Bayangan lebih dramatis */
        }
        
        .promo-image-container {
            position: relative;
            width: 100%;
            height: 120px; /* Tinggi gambar yang konsisten */
            overflow: hidden;
            border-radius: 8px;
        }

        .promo-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .promo-card:hover .promo-image {
            transform: scale(1.1); /* Efek zoom saat di-hover */
        }
        
        .promo-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background: linear-gradient(45deg, #FF6B6B, #F8CD4F); /* Gradien warna yang menarik */
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: bold;
            z-index: 10;
        }
        
        .promo-details {
            padding: 15px;
            color: #d1d5db;
        }

        .promo-title {
            font-size: 1rem;
            font-weight: 600;
            color: #fff;
            margin-bottom: 5px;
            text-overflow: ellipsis;
            overflow: hidden;
            white-space: nowrap;
        }

        .promo-subtitle {
            font-size: 0.8rem;
            font-weight: 500;
            color: #9ca3af;
        }

    </style>

    <div class="mt-3 -mx-[5px] lg:-mx-2 pb-2 whitespace-nowrap overflow-x-scroll overflow-y-hidden lg:overflow-x-hidden opacity-scroll">
        <?php foreach (array_slice($promosi_data, 0, 3) as $data_promosi) : ?>
            <div class="w-[80%] sm:w-2/3 md:w-[45%] lg:w-1/3 inline-block px-2 mt-4">
                <a class="promo-card" href="promo">
                    <figure class="promo-image-container">
                        <img alt="<?php echo htmlspecialchars($data_promosi['judul_promosi'], ENT_QUOTES, 'UTF-8'); ?>" loading="lazy" decoding="async" class="promo-image" src="<?php echo htmlspecialchars($alamat_website . 'assets/img/' . $data_promosi['gambar_promosi'], ENT_QUOTES, 'UTF-8'); ?>" />
                        <span class="promo-badge">
                            New
                        </span>
                    </figure>
                    <div class="promo-details">
                        <p class="promo-title"><?php echo htmlspecialchars($data_promosi['judul_promosi'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p class="promo-subtitle">Ongoing Promo</p>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>