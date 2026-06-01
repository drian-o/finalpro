<?php
include_once 'header.php';
$saldo_anggota = isset($_SESSION['saldo_anggota']) ? $_SESSION['saldo_anggota'] : 0;
$isLoggedIn = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
?>

<style>
    .bg-gradient-custom {
        background-image: linear-gradient(135deg, #1A237E, #FF5722);
    }
    .btn-action {
        background-color: #FF5722;
        color: #FFFFFF;
        font-weight: bold;
        padding: 0.75rem 1.5rem;
        border-radius: 9999px;
        transition: background-color 0.3s ease;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    }
    .btn-action:hover {
        background-color: #E64A19;
    }
    .btn-outline-action {
        color: #FF5722;
        font-weight: bold;
        padding: 0.75rem 1.5rem;
        border-radius: 9999px;
        border: 2px solid #FF5722;
        transition: background-color 0.3s ease, color 0.3s ease;
    }
    .btn-outline-action:hover {
        background-color: #FF5722;
        color: #FFFFFF;
    }
    .animate-wiggle {
        animation: wiggle 2s infinite ease-in-out;
    }
    @keyframes wiggle {
        0%, 100% {
            transform: rotate(-3deg);
        }
        50% {
            transform: rotate(3deg);
        }
    }
</style>

<section class="container mx-auto pt-3 pb-10 lg:pb-12 px-3">
    <nav class="flex mb-1 lg:mb-2">
        <ol class="flex items-center pb-1 overflow-x-scroll whitespace-nowrap opacity-scroll">
            <li class="inline-flex items-end pr-1">
                <a class="text-xs border-b border-transparent hover:lg:border-primary transition-all duration-200 ease-in-out undefined" href="<?php echo $alamat_website . 'home'; ?>">Home</a>
            </li>
            <li class="inline-flex items-end pr-1 group">
                <div class="flex items-center">
                    <svg width="17" height="17" viewbox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg" size="17">
                        <path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="var(--base)"></path>
                    </svg><a class="text-xs pl-1 border-b border-transparent hover:lg:border-primary transition-all duration-200 ease-in-out group-last:text-primary undefined" href="<?php echo $alamat_website . 'coming_soon'; ?>">Coming Soon</a>
                </div>
            </li>
        </ol>
    </nav>
    
    <div class="flex flex-col items-center justify-center text-center py-16 lg:py-24 bg-background-tertiary rounded-lg shadow-lg">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-28 w-28 text-primary mb-6 animate-wiggle" fill="#FFC107" viewBox="0 0 24 24" stroke="#FF5722" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <h3 class="md:text-3xl text-2xl font-bold w-full mb-4 text-white">Segera Hadir!</h3>
        <p class="text-sm md:text-base text-gray-300 max-w-xl mx-auto px-4 mb-8">
            Halaman ini sedang dalam tahap pengembangan. Nantikan update selanjutnya untuk fitur-fitur menarik yang akan datang. Mohon kesabarannya ya!
        </p>

        <a href="<?php echo $alamat_website . 'home'; ?>" class="btn-action mb-4">Kembali ke Home</a>

        <?php if (!$isLoggedIn): ?>
            <div class="flex gap-4">
                <a href="<?php echo $alamat_website . 'auth-login'; ?>" class="btn-outline-action">Login</a>
                <a href="<?php echo $alamat_website . 'auth-register'; ?>" class="btn-action">Daftar</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include_once 'footer.php'; ?>