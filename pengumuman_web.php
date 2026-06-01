<?php
// File: pengumuman_web.php (Versi Final Lengkap dengan Semua Fitur)

// Memastikan session telah dimulai, penting untuk semua logika di bawah.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include koneksi.php and chaos.php for database and API interaction
include_once __DIR__ . '/koneksi.php'; // Adjust path if necessary
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&family=Inter:wght@400;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>


<div id="fakeWithdrawNotification" class="notification-container">
    <div class="notification-icon">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
    </div>
    <div class="notification-text">
        <p>User <strong id="wd-username"></strong> baru saja melakukan penarikan!</p>
        <p>Sejumlah <span id="wd-amount"></span></p>
    </div>
    <button class="notification-close-btn" id="closeWithdrawNotification" aria-label="Tutup Notifikasi">
        &times;
    </button>
</div>


<?php
// === LOGIKA UTAMA: MEMISAHKAN TAMPILAN BERDASARKAN STATUS LOGIN ===

// ==================================================
// BAGIAN JIKA PENGGUNA SUDAH LOGIN
// ==================================================
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) :

    // [FITUR 2] Tampilkan modal selamat datang HANYA JIKA belum pernah ditampilkan di sesi ini
    if (!isset($_SESSION['welcome_modal_shown'])) :
?>
    <div id="loggedInWelcomeModal" class="welcome-modal-overlay">
        <div class="welcome-modal-content">
            <button id="closeWelcomeModal" class="welcome-close-button">&times;</button>

            <div id="welcomeContent">
                <h3>Selamat Datang Kembali!</h3>
                <p class="welcome-user-name">
                    <?php
                        echo isset($_SESSION['nama_pengguna_anggota']) ? htmlspecialchars($_SESSION['nama_pengguna_anggota']) : "Member";
                    ?>
                </p>
                <p class="welcome-text">
                    Ada kendala atau butuh bantuan? Hubungi Customer Service kami.
                </p>
                <a href="<?php echo isset($isi_1_link_livechat_web) ? htmlspecialchars($isi_1_link_livechat_web) : '#'; ?>" target="_blank" class="cs-button">
                    <i class="fas fa-headset"></i> Hubungi CS Kami
                </a>

                <button id="showVoucherInputBtn" class="voucher-button">
                    <i class="fas fa-ticket-alt"></i> Punya Kode Voucher? Masukkan di sini!
                </button>
            </div>

            <div id="voucherInputContainer" style="display: none;">
                <h3>Klaim Voucher Anda</h3>
                <p class="game-subtitle">Masukkan kode voucher di bawah ini:</p>

                <div class="voucher-form-group">
                    <input type="text" id="voucherCodeInput" placeholder="Masukkan Kode Voucher" class="voucher-input">
                    <button id="claimVoucherBtn" class="claim-voucher-button">Klaim Voucher</button>
                </div>

                <button id="backToWelcomeFromVoucherBtn" class="back-button">&larr; Kembali</button>
            </div>
        </div>
    </div>
<?php
    // Tandai bahwa modal sudah ditampilkan agar tidak muncul lagi di refresh berikutnya
    $_SESSION['welcome_modal_shown'] = true;
    endif;

// ==================================================
// BAGIAN JIKA PENGGUNA BELUM LOGIN (TAMU)
// ==================================================
else :
?>
    <div id="announcementModal">
        <div class="modal-content">
             <button id="closeAnnouncementModal" class="close-button" aria-label="Tutup Pengumuman">&times;</button>
            <h2><i class="fas fa-bullhorn"></i><span>CUBY138</span></h2>
            <p class="modal-intro">Selamat datang di situs kami! Nikmati promosi menarik dan jaminan pembayaran tercepat untuk semua kemenangan Anda.</p>
            <div class="announcement-details">
                <p><i class="fas fa-check-circle icon-perk"></i> Bonus New Member Terbesar!</p>
                <p><i class="fas fa-check-circle icon-perk"></i> Event & Turnamen Setiap Hari!</p>
                <p class="payment-guarantee">Jaminan Kemenangan Berapapun <strong>PASTI DIBAYAR!</strong></p>
            </div>
            <div class="login-register-buttons">
                <a href="auth-register" class="register-button"><i class="fas fa-user-plus"></i> Daftar Sekarang</a>
                <a href="auth-login" class="login-button"><i class="fas fa-sign-in-alt"></i> Login</a>
            </div>
            <p id="countdown-timer" class="countdown-text"></p>
        </div>
    </div>
<?php
endif; // Akhir dari if/else 'loggedin'
?>


<style>
    /* CSS Notifikasi Withdraw */
    .notification-container{position:fixed;top:80px;right:-400px;display:flex;align-items:center;gap:12px;background-color:#252a34;color:#e5e7eb;font-family:'Inter',sans-serif;padding:12px 16px;border-radius:8px;border-left:4px solid #4ade80;box-shadow:0 5px 20px rgba(0,0,0,.4);z-index:9997;transition:all .5s cubic-bezier(.25,.8,.25,1);opacity:0;visibility:hidden}.notification-container.show{right:20px;opacity:1;visibility:visible}.notification-icon{color:#4ade80}.notification-icon svg{width:32px;height:32px}.notification-text p{margin:0;font-size:.875rem;line-height:1.4}.notification-text p:first-child{color:#9ca3af}.notification-text strong{font-weight:700;color:#fff}.notification-text span{font-weight:700;color:#4ade80}
    /* Gaya untuk tombol close */
    .notification-close-btn {
        background: none;
        border: none;
        color: #9ca3af; /* Warna X */
        font-size: 1.5rem;
        cursor: pointer;
        position: absolute; /* Posisikan relatif terhadap container */
        top: 5px; /* Sesuaikan posisi vertikal */
        right: 5px; /* Sesuaikan posisi horizontal */
        line-height: 1; /* Pastikan X sejajar */
        padding: 0 5px; /* Sedikit padding untuk area klik */
        transition: color 0.2s ease;
    }
    .notification-close-btn:hover {
        color: #fff; /* Warna X saat hover */
    }

    /* CSS Modal Selamat Datang */
    .welcome-modal-overlay{display:flex;align-items:center;justify-content:center;position:fixed;top:0;left:0;width:100%;height:100%;background-color:rgba(10,10,10,.8);backdrop-filter:blur(5px);z-index:1001}.welcome-modal-content{font-family:'Poppins',sans-serif;background:#1a1d24;color:#e5e7eb;padding:28px;border-radius:12px;border:1px solid rgba(255,255,255,.1);max-width:420px;width:calc(100% - 32px);box-shadow:0 10px 40px rgba(0,0,0,.5);position:relative;text-align:center;transition:all .3s ease}.welcome-modal-content h3{font-size:1.8rem;font-weight:700;margin:0 0 8px;color:#4ade80}.welcome-user-name{font-size:1.2rem;font-weight:500;color:#fff;margin:0 0 20px;padding-bottom:20px;border-bottom:1px solid rgba(255,255,255,.1)}.welcome-text{font-size:1rem;line-height:1.6;color:#9ca3af;margin-bottom:24px}.cs-button{display:block;width:100%;text-align:center;padding:12px 24px;background-color:#4ade80;color:#1a1d24;text-decoration:none;border-radius:8px;font-weight:700;transition:all .2s ease}.cs-button:hover{background-color:#34d399;transform:translateY(-2px)}.cs-button .fa-headset{margin-right:8px}.welcome-close-button{position:absolute;top:10px;right:10px;background:0 0;border:none;font-size:1.8rem;color:#9ca3af;cursor:pointer;transition:color .2s ease}.welcome-close-button:hover{color:#fff}.back-button{background:0 0;border:none;color:#9ca3af;cursor:pointer;margin-top:20px;font-size:.9rem}

    /* Voucher Button and Input Styles */
    .voucher-button {
        display: block;
        width: 100%;
        margin-top: 15px;
        padding: 12px;
        background-color: transparent;
        border: 2px solid #FBBF24;
        color: #FBBF24;
        font-family: 'Poppins', sans-serif;
        font-weight: 700;
        border-radius: 8px;
        cursor: pointer;
        transition: all .2s ease;
    }
    .voucher-button:hover {
        background-color: #FBBF24;
        color: #1a1d24;
    }
    .voucher-button .fa-ticket-alt {
        margin-right: 8px;
    }

    #voucherInputContainer {
        padding: 0 10px;
    }
    .voucher-form-group {
        display: flex; /* Keep flex for horizontal layout if desired for input + button */
        flex-direction: column; /* THIS IS THE KEY CHANGE: Stacks items vertically */
        gap: 10px;
        margin-top: 20px;
    }
    .voucher-input {
        flex-grow: 1; /* This will make the input take full width */
        padding: 12px;
        border-radius: 8px;
        border: 1px solid #555;
        background-color: #333;
        color: #eee;
        font-size: 1rem;
        width: 100%; /* Ensure input takes full available width */
        box-sizing: border-box; /* Include padding and border in the element's total width and height */
    }
    .voucher-input::placeholder {
        color: #999;
    }
    .claim-voucher-button {
        width: 100%; /* Make button take full width */
        padding: 12px; /* Adjust padding as needed */
        margin-top: 5px; /* Add some space above the button */
        background-color: #4ADE80;
        color: #1a1d24;
        border: none;
        border-radius: 8px;
        font-weight: 700;
        cursor: pointer;
        transition: all .2s ease;
    }
    .claim-voucher-button:hover {
        background-color: #34d399;
    }
    .claim-voucher-button:disabled {
        background-color: #999;
        cursor: not-allowed;
    }


    /* CSS Modal Pengumuman (Tamu) */
    :root{--dark-bg:#1A1D24;--dark-secondary:#252A34;--green-accent:#4ADE80;--green-hover:#34D399;--text-main:#E5E7EB;--text-secondary:#9CA3AF;--font-body:'Inter','Segoe UI',Tahoma,sans-serif;--font-heading:'Poppins','Segoe UI',Tahoma,sans-serif}#announcementModal{display:flex;align-items:center;justify-content:center;position:fixed;top:0;left:0;width:100%;height:100%;background-color:rgba(10,10,10,.8);-webkit-backdrop-filter:blur(5px);backdrop-filter:blur(5px);z-index:1000;opacity:0;visibility:hidden;transition:opacity .3s ease,visibility .3s ease}#announcementModal.show-modal{opacity:1;visibility:visible}#announcementModal.show-modal .modal-content{transform:scale(1);opacity:1}.modal-content{font-family:var(--font-body);background:linear-gradient(145deg,var(--dark-secondary),var(--dark-bg));padding:32px;border-radius:16px;border:1px solid rgba(255,255,255,.1);border-top:4px solid var(--green-accent);max-width:500px;width:calc(100% - 32px);box-shadow:0 10px 40px rgba(0,0,0,.5),0 0 20px rgba(74,222,128,.1);position:relative;text-align:center;color:var(--text-main);transform:scale(.95);opacity:0;transition:transform .3s ease,opacity .3s ease}.modal-content h2{font-family:var(--font-heading);font-size:2rem;font-weight:700;margin-top:0;margin-bottom:16px;color:#fff;display:flex;align-items:center;justify-content:center;gap:12px}.modal-content h2 .fa-bullhorn{color:var(--green-accent)}.modal-intro{font-size:1.1rem;line-height:1.6;color:var(--text-main);margin-bottom:24px;font-weight:400}.announcement-details{text-align:left;margin:0 auto 30px;max-width:fit-content}.announcement-details p{font-size:1rem;color:var(--text-secondary);margin-bottom:12px;display:flex;align-items:center;gap:10px}.icon-perk{color:var(--green-accent)}.payment-guarantee{font-size:1.1rem!important;font-weight:500;color:#fff!important;background-color:rgba(74,222,128,.1);padding:10px 15px;border-radius:8px;border-left:3px solid var(--green-accent);margin-top:20px}.payment-guarantee strong{color:var(--green-accent);font-weight:700}.login-register-buttons{display:flex;flex-direction:column;gap:12px}.login-button,.register-button{width:100%;padding:14px 28px;border:2px solid transparent;border-radius:8px;font-family:var(--font-heading);font-weight:700;font-size:1rem;text-transform:uppercase;letter-spacing:.05em;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;gap:10px;transition:all .2s ease-in-out}.register-button{background-color:var(--green-accent);color:var(--dark-bg)}.register-button:hover{background-color:var(--green-hover);transform:translateY(-2px);box-shadow:0 4px 15px rgba(74,222,128,.2)}.login-button{background-color:transparent;border-color:var(--green-accent);color:var(--green-accent)}.login-button:hover{background-color:rgba(74,222,128,.1);color:#fff;border-color:var(--green-hover)}.close-button{position:absolute;top:15px;right:15px;background:0 0;border:none;font-size:2rem;color:var(--text-secondary);cursor:pointer;transition:all .2s ease}.close-button:hover{color:#fff;transform:rotate(90deg)}.countdown-text{margin-top:24px;font-size:.875rem;color:var(--text-secondary);opacity:.8}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {

    // --- Script Notifikasi Withdraw Palsu ---
    const notificationEl = document.getElementById('fakeWithdrawNotification');
    if (notificationEl) {
        const usernameEl = document.getElementById('wd-username');
        const amountEl = document.getElementById('wd-amount');
        const closeWithdrawNotificationBtn = document.getElementById('closeWithdrawNotification'); // Ambil tombol close
        let startX = 0;
        let currentX = 0;
        let isSwiping = false;

        const prefixes = ['Juragan', 'Sultan', 'Raja', 'Bos', 'Master', 'Agen', 'Kapten'];
        const baseNames = ['Budi', 'Dewi', 'Putra', 'Rini', 'Agus', 'Eka', 'Sari', 'Adi', 'Maya', 'Rizky', 'Dika', 'Cindy', 'Joko', 'Wati', 'Ade'];
        const suffixWords = ['Gacor', 'Jitu', 'Hoki', 'JP', 'Maxwin', 'Slot', '77', '88', '99'];

        function getRandomItem(arr) { return arr[Math.floor(Math.random() * arr.length)]; }
        function getRandomNumber(min, max) { return Math.floor(Math.random() * (max - min + 1)) + min; }

        function generateRandomUsername() {
            let name = getRandomItem(baseNames);
            if (Math.random() < 0.25) { name = getRandomItem(prefixes) + name; }
            if (Math.random() < 0.50) { name += getRandomItem(suffixWords); }
            if (Math.random() < 0.70) { name += getRandomNumber(10, 999); }
            return name.toLowerCase();
        }

        // Fungsi baru untuk menyensor username
        function censorUsername(username) {
            if (username.length <= 4) { // Jika nama terlalu pendek, tampilkan sebagian atau semua
                return username.substring(0, 1) + "***";
            }
            const firstChars = 2; // Jumlah karakter di awal yang tidak disensor
            const lastChars = 2;  // Jumlah karakter di akhir yang tidak disensor
            const censoredLength = username.length - firstChars - lastChars;

            if (censoredLength <= 0) { // Pastikan tidak menyensor terlalu banyak atau jadi negatif
                return username.substring(0, 1) + "*****"; // Contoh: ve****
            }

            const censoredPart = '*'.repeat(Math.max(1, censoredLength)); // Minimal 1 bintang
            return username.substring(0, firstChars) + censoredPart + username.substring(username.length - lastChars);
        }

        function formatCurrency(amount) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount); }

        function hideNotification() {
            notificationEl.classList.remove('show');
            notificationEl.style.transform = 'translateX(0px)'; // Reset transform saat disembunyikan
        }

        function showRandomNotification() {
            const rawUsername = generateRandomUsername();
            const censoredUsername = censorUsername(rawUsername); // Panggil fungsi sensor
            const randomAmount = Math.round(getRandomNumber(50000, 5000000) / 10000) * 10000;
            usernameEl.textContent = censoredUsername; // Gunakan username yang sudah disensor
            amountEl.textContent = formatCurrency(randomAmount);
            notificationEl.classList.add('show');
            // Atur agar otomatis hilang setelah 5 detik jika tidak diinteraksi
            setTimeout(() => { hideNotification(); }, 5000);
        }

        function notificationLoop() {
            const randomInterval = getRandomNumber(20000, 25000); // Interval antar notifikasi
            showRandomNotification();
            setTimeout(notificationLoop, randomInterval);
        }

        // Mulai loop notifikasi setelah beberapa detik awal
        setTimeout(notificationLoop, getRandomNumber(10000, 13000));

        // --- Event Listeners untuk Swipe ---
        notificationEl.addEventListener('touchstart', (e) => {
            startX = e.touches[0].clientX;
            isSwiping = true;
            notificationEl.style.transition = 'none'; // Nonaktifkan transisi CSS saat menyapu
        }, { passive: true }); // Menggunakan passive: true untuk peningkatan kinerja scrolling

        notificationEl.addEventListener('touchmove', (e) => {
            if (!isSwiping) return;
            currentX = e.touches[0].clientX;
            const diffX = currentX - startX;
            if (diffX > 0) { // Hanya geser ke kanan
                notificationEl.style.transform = `translateX(${diffX}px)`;
            }
        });

        notificationEl.addEventListener('touchend', () => {
            if (!isSwiping) return;
            isSwiping = false;
            notificationEl.style.transition = ''; // Aktifkan kembali transisi CSS
            const diffX = currentX - startX;
            if (diffX > 100) { // Jika geser lebih dari 100px ke kanan
                hideNotification();
            } else {
                notificationEl.style.transform = 'translateX(0px)'; // Kembali ke posisi semula
            }
            startX = 0;
            currentX = 0;
        });

        // --- Event Listener untuk Tombol Close ---
        if (closeWithdrawNotificationBtn) {
            closeWithdrawNotificationBtn.addEventListener('click', hideNotification);
        }
    }


    // --- Script untuk Modal Selamat Datang (Login) & Voucher Input ---
    const welcomeModal = document.getElementById('loggedInWelcomeModal');
    if (welcomeModal) {
        const closeWelcomeBtn = document.getElementById('closeWelcomeModal');
        const hideWelcomeModal = () => { welcomeModal.style.display = 'none'; };
        welcomeModal.style.display = 'flex';
        if (closeWelcomeBtn) { closeWelcomeBtn.addEventListener('click', hideWelcomeModal); }
        window.addEventListener('click', (event) => { if (event.target == welcomeModal) { hideWelcomeModal(); } });

        const showVoucherInputBtn = document.getElementById('showVoucherInputBtn');
        const backToWelcomeFromVoucherBtn = document.getElementById('backToWelcomeFromVoucherBtn');
        const welcomeContent = document.getElementById('welcomeContent');
        const voucherInputContainer = document.getElementById('voucherInputContainer');
        const voucherCodeInput = document.getElementById('voucherCodeInput');
        const claimVoucherBtn = document.getElementById('claimVoucherBtn');

        showVoucherInputBtn.addEventListener('click', () => {
            welcomeContent.style.display = 'none';
            voucherInputContainer.style.display = 'block';
        });
        backToWelcomeFromVoucherBtn.addEventListener('click', () => {
            voucherInputContainer.style.display = 'none';
            welcomeContent.style.display = 'block';
        });

        claimVoucherBtn.addEventListener('click', () => {
            const voucherCode = voucherCodeInput.value.trim();
            if (!voucherCode) {
                swal("Peringatan", "Kode voucher tidak boleh kosong!", "warning");
                return;
            }

            claimVoucherBtn.disabled = true; // Disable button to prevent multiple clicks

            // Using Fetch API to send data to a new PHP endpoint for voucher processing
            fetch('process_voucher.php', { // You'll create this file
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'voucher_code=' + encodeURIComponent(voucherCode)
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    swal("Berhasil!", data.message, "success")
                        .then(() => {
                            // Optionally, refresh the page or update balance display
                            location.reload();
                        });
                } else {
                    swal("Gagal!", data.message, "error");
                }
            })
            .catch(error => {
                console.error('Error:', error);
                swal("Error", "Terjadi kesalahan saat memproses voucher.", "error");
            })
            .finally(() => {
                claimVoucherBtn.disabled = false; // Re-enable button
            });
        });
    }

    // --- Script untuk Modal Pengumuman (Tamu) ---
    const announcementModal = document.getElementById('announcementModal');
    if (announcementModal) {
        const closeAnnouncementModalButton = document.getElementById('closeAnnouncementModal');
        const countdownElement = document.getElementById('countdown-timer');
        let countdown = 5;
        let countdownInterval;
        function showModal() {
            announcementModal.classList.add('show-modal');
            countdownInterval = setInterval(() => {
                if (countdownElement) { countdownElement.textContent = `(Menutup otomatis dalam ${countdown} detik...)`; }
                countdown--;
                if (countdown < 0) { clearInterval(countdownInterval); hideModal(); }
            }, 1000);
        }
        function hideModal() { announcementModal.classList.remove('show-modal'); }
        setTimeout(showModal, 500);
        if (closeAnnouncementModalButton) { closeAnnouncementModalButton.addEventListener('click', () => { clearInterval(countdownInterval); hideModal(); }); }
        window.addEventListener('click', (event) => { if (event.target == announcementModal) { clearInterval(countdownInterval); hideModal(); } });
    }
});
</script>