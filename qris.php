<?php
ob_start();

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once 'koneksi.php';

$alamat_website = (isset($alamat_website) ? rtrim($alamat_website, '/') . '/' : '/');
$isi_1_link_livechat_web = (isset($isi_1_link_livechat_web) ? $isi_1_link_livechat_web : '#livechat');

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && isset($_SESSION['id_anggota']) && isset($_SESSION['nama_pengguna_anggota'])) {
    $id_anggota = $_SESSION['id_anggota'];
    $username = $_SESSION['nama_pengguna_anggota'];
} else {
    if (ob_get_level() > 0) {
        ob_end_clean();
    }
    header('Location: ' . $alamat_website . 'auth-login');
    exit();
}

$query_anggota_check = "SELECT id_anggota FROM anggota WHERE id_anggota = ?";
$stmt_anggota_check = $koneksi->prepare($query_anggota_check);
if ($stmt_anggota_check) {
    $stmt_anggota_check->bind_param("i", $id_anggota);
    $stmt_anggota_check->execute();
    $result_anggota_check = $stmt_anggota_check->get_result();
    if ($result_anggota_check->num_rows == 0) {
        session_destroy();
        if (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Location: ' . $alamat_website . 'auth-login');
        exit();
    }
    $stmt_anggota_check->close();
} else {
    error_log("Gagal prepare statement untuk cek anggota di qris.php: " . $koneksi->error);
    if (ob_get_level() > 0) {
        ob_end_clean();
    }
    echo "Terjadi kesalahan pada sistem. Silakan coba lagi nanti.";
    if (isset($koneksi) && $koneksi instanceof mysqli) {
        $koneksi->close();
    }
    exit();
}

$status_deposit_pending = null;
$jumlah_deposit_pending = null;
$trx_id_pending = null;
$qris_image_url_pending = null;
$waktu_mulai_pending = null;

$q_deposit_pending_check = "SELECT status_deposit, jumlah_deposit, transaction_id, qris_image, waktu_mulai FROM deposit WHERE id_anggota_deposit = ? AND status_deposit = 'diproses' AND asal_deposit LIKE 'QRIS%' AND transaction_id IS NOT NULL ORDER BY tanggal_deposit DESC LIMIT 1";
$stmt_deposit_pending_check = $koneksi->prepare($q_deposit_pending_check);
if ($stmt_deposit_pending_check) {
    $stmt_deposit_pending_check->bind_param("i", $id_anggota);
    $stmt_deposit_pending_check->execute();
    $result_deposit_pending_check = $stmt_deposit_pending_check->get_result();
    $data_deposit_pending_db = $result_deposit_pending_check->fetch_assoc();
    $stmt_deposit_pending_check->close();

    if ($data_deposit_pending_db) {
        $status_deposit_pending = $data_deposit_pending_db['status_deposit'];
        $jumlah_deposit_pending = $data_deposit_pending_db['jumlah_deposit'];
        $trx_id_pending = $data_deposit_pending_db['transaction_id'];
        $qris_image_url_pending = $data_deposit_pending_db['qris_image'];
        $waktu_mulai_pending = $data_deposit_pending_db['waktu_mulai'];
    }
} else {
    error_log("Gagal prepare statement untuk cek deposit pending di qris.php: " . $koneksi->error);
}

include_once 'header.php';
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .swal2-popup.minimalist {
        background: #333;
        color: #ddd;
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
    }
    .swal2-title.minimalist-title {
        color: #fff;
    }
    .swal2-html-container.minimalist-text {
        color: #ccc;
    }
    .swal2-actions .swal2-confirm {
        background-color: #f87171 !important;
        color: #fff !important;
        border: none !important;
    }
    .swal2-actions .swal2-confirm:hover {
        background-color: #ef4444 !important;
    }
    #newQrisSection, #depositFormContainer {
        animation: fadeIn 0.5s ease-in-out;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    /* Tambahan style untuk tombol baru */
    .btn-deposit-action {
        width: 100%;
        justify-content: center;
        rounded-xl: true;
        py-3: true;
        mt-3: true;
        transition-all: true;
        duration-200: true;
        ease-in-out: true;
        hover:lg:brightness-[0.9]: true;
        disabled:opacity-50: true;
    }
</style>

<section class="lg:px-4 lg:pb-0" style="padding-bottom: 80px;">
    <div class="w-full lg:w-2/3 lg:px-3 pb-24 lg:pb-0">
        <div class="grid grid-cols-2 px-3 lg:gap-x-5 lg:px-4 lg:mb-6 mt-4 lg:mt-0">
            <a aria-label="Deposit-tab-button" aria-labelledby="Deposit-tab-button" class="text-sm border-b uppercase flex-none px-1 pb-2 justify-center cursor-pointer transition-all duration-300 ease-in-out hover:lg:border-primary hover:lg:text-inverse font-semibold border-primary " href="<?php echo $alamat_website . 'qris'; ?>">Deposit</a>
            <a aria-label="Withdraw-tab-button" aria-labelledby="Withdraw-tab-button" class="text-sm border-b uppercase flex-none px-1 pb-2 justify-center cursor-pointer transition-all duration-300 ease-in-out hover:lg:border-primary hover:lg:text-inverse font-semibold border-transparent text-caption " href="<?php echo $alamat_website . 'withdraw'; ?>">Withdraw</a>
        </div>
        <div class="px-4 lg:px-5 mt-4">
            <p class="text-sm text-center font-semibold mb-2 text-gray-700 dark:text-gray-200">Metode Pembayaran: QRIS</p>
            <section class="flex gap-x-3 mt-2">
                <a href="<?php echo htmlspecialchars($alamat_website . 'deposit'); ?>" class="flex flex-col items-center rounded-lg w-full p-3 relative border transition-all duration-200 ease-in-out hover:bg-background-default cursor-pointer border-separator">
                    <figure class="flex flex-none justify-center w-6 lg:w-8 h-6 lg:h-8 mx-auto"><img alt="Bank Assets" loading="lazy" width="0" height="0" decoding="async" data-nimg="1" class="w-full h-full" src="<?php echo htmlspecialchars($alamat_website); ?>assets/img/BANK-LOGO.webp" style="color: transparent;"></figure>
                    <p class="text-xs text-center mt-2 truncate text-inverse">Bank</p>
                </a>
                <div class="rounded-lg w-full p-3 relative border transition-all duration-200 ease-in-out lg:flex lg:items-center hover:lg:bg-background-default cursor-pointer border-primary bg-background-default">
                    <figure class="flex flex-none justify-center w-6 lg:w-8 h-6 lg:h-8 mx-auto lg:mr-3 lg:ml-0"><img alt="QRIS Icon" loading="lazy" width="0" height="0" decoding="async" data-nimg="1" class="w-full h-full lg:mx-auto" src="<?php echo htmlspecialchars($alamat_website); ?>assets/img/E_WALLET.webp" style="color: transparent;"></figure>
                    <p class="text-xs text-center mt-2 truncate text-inverse flex-1 lg:flex-none lg:mt-0 lg:text-base">QRIS</p>
                    <section class="absolute bottom-0 right-0 bg-primary w-5 h-5 flex items-center justify-center rounded-br-md rounded-tl-md"><img alt="Selected Icon" loading="lazy" width="0" height="0" decoding="async" data-nimg="1" class="h-3 w-auto p-[2px]" src="<?php echo htmlspecialchars($alamat_website); ?>assets/img/done.webp" style="color: transparent;"></section>
                </div>
            </section>
        </div>

        <section class="container flex mx-auto lg:pt-3 lg:pb-5 lg:mt-5 justify-center">
            <div class="w-full lg:w-2/3 xl:w-1/2 lg:px-3 pb-24 lg:pb-0">

                <section id="newQrisSection" class="mt-3 px-3 lg:px-4 pb-6 <?php echo ($status_deposit_pending == 'diproses') ? '' : 'hidden'; ?>">
                    <div class="lg:bg-background-secondary lg:px-5 lg:pt-6 lg:pb-16 lg:mb-4 rounded-xl">
                        <div class="h-12 lg:h-16 w-12 lg:w-16 mx-auto my-4">
                            <img src="assets/img/bankdeposit.png" alt="Deposit Progres" class="w-full h-full object-contain" />
                        </div>
                        <p class="text-xs text-center mt-3 opacity-70">Deposit</p>
                        <p class="text-sm font-semibold text-center mt-3" id="qrisTitle">QRIS Pembayaran</p>
                        
                        <div class="px-3 mt-5">
                            <div class="relative">
                                <div class="flex justify-between items-center mt-4 pb-3 border-b border-disable">
                                    <p class="text-xs opacity-70 w-1/2">Amount</p>
                                    <article class="flex items-center justify-end w-1/2">
                                        <p class="text-xs truncate" id="newQrisAmountDisplay">IDR&nbsp;0</p>
                                        <div class="pl-1 cursor-pointer">
                                            <svg width="24" height="24" viewbox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M15.5 3h-6a4 4 0 0 0-4 4v8" stroke="var(--primary)" stroke-width="2"></path>
                                                <path d="M9.5 11.5c0-1.196.001-2.01.071-2.628.068-.598.188-.889.342-1.09a2 2 0 0 1 .37-.369c.2-.154.491-.274 1.09-.342C11.99 7.001 12.803 7 14 7c1.196 0 2.01.001 2.628.071.598.068.889.188 1.09.342.138.107.262.23.369.37.154.2.274.491.342 1.09.07.618.071 1.431.071 2.627v4c0 1.196-.002 2.01-.071 2.628-.068.598-.188.889-.342 1.09-.107.138-.23.262-.37.369-.2.154-.491.274-.342-1.09-.07-.618-.071-1.431-.071-2.627v-4Z" stroke="var(--primary)" stroke-width="2"></path>
                                            </svg>
                                        </div>
                                    </article>
                                </div>
                                <div class="flex justify-between items-center mt-4 pb-3 border-b border-disable">
                                    <p class="text-xs opacity-70 w-1/2">Bank</p>
                                    <article class="flex items-center justify-end w-1/2">
                                        <p class="text-xs truncate" id="newQrisBankDisplay">QRIS</p>
                                    </article>
                                </div>
                                <div class="flex justify-between items-center mt-4 pb-3 border-b border-disable">
                                    <p class="text-xs opacity-70 w-1/2">Transaction ID</p>
                                    <article class="flex items-center justify-end w-1/2">
                                        <p class="text-xs truncate" id="newQrisTrxIdDisplay">TRXID...</p>
                                    </article>
                                </div>
                                <div class="flex justify-between items-center mt-4 pb-3 border-b border-disable">
                                    <p class="text-xs opacity-70 w-1/2">Sender</p>
                                    <article class="flex items-center justify-end w-1/2">
                                        <p class="text-xs truncate" id="newQrisSenderDisplay">QRIS</p>
                                    </article>
                                </div>
                                <div class="flex justify-between items-center mt-4 pb-3 border-b border-disable">
                                    <p class="text-xs opacity-70 w-1/2">Status</p>
                                    <article class="flex items-center justify-end w-1/2">
                                        <p class="text-xs truncate text-danger" id="newQrisStatusText2">Menunggu Pembayaran</p>
                                    </article>
                                </div>
                                <p class="text-xs text-center font-semibold text-danger my-2" id="newQrisStatusText">Menunggu Pembayaran...</p>
                                
                                <div id="newQrisImageContainer" class="w-full max-w-[280px] sm:max-w-[300px] aspect-square mx-auto my-4 flex items-center justify-center bg-white p-2 rounded-md border-4 border-gray-300">
                                    <img id="newQrisImage" src="" alt="QRIS Code" class="hidden object-contain max-w-full max-h-full">
                                </div>
                                
                                <p class="text-xs text-caption text-center mt-2" id="newQrisInfoText">
                                    Sisa waktu pembayaran: <span id="countdown-timer" class="font-semibold text-primary"></span>
                                </p>

                                <p class="text-xs text-red-500 text-center" id="newQrisErrorMessage"></p>
                            </div>
                            
                            <div class="w-full mx-auto grid grid-cols-1 gap-3 mt-4">
                                <button id="newQrisCekStatusBtn" class="bg-primary text-black btn-deposit-action">
                                    Cek Status Pembayaran
                                </button>
                                <button id="newQrisBuatBaruBtn" class="bg-gray-700 text-white btn-deposit-action">
                                    Batalkan & Buat Baru
                                </button>
                            </div>
                        </div>
                    </div>
                </section>

                <div id="depositFormContainer" class="px-4 <?php echo ($status_deposit_pending == 'diproses') ? 'hidden' : ''; ?>">
                    <p class="text-sm my-4 text-center text-gray-700 dark:text-gray-300">Masukkan jumlah deposit untuk membuat QRIS:</p>
                    <div class="grid grid-cols-3 gap-3 mb-4">
                        <?php
                        $nominal_buttons = [25000, 50000, 75000, 100000, 150000, 200000];
                        foreach ($nominal_buttons as $nominal) {
                            echo '<button type="button" class="btn-nominal w-full justify-center rounded-md py-2 text-sm font-semibold bg-primary text-black transition-all duration-200 ease-in-out hover:lg:brightness-[0.9]" data-amount="' . $nominal . '">Rp '. number_format($nominal, 0, ',', '.') . '</button>';
                        }
                        ?>
                    </div>

                    <form id="deposit-form" method="post" action="javascript:void(0);">
                        <div class="relative mt-4 rounded-xl border bg-background-default border-caption focus-within:border-primary">
                            <div class="relative flex items-center top-10 pt-3 px-3"><label class="text-xs opacity-70 bg-background-default">Jumlah Deposit (IDR)</label></div>
                            <div class="relative"><input id="amount-input" name="jumlah_deposit" inputmode="numeric" class="px-3 pt-2 pb-3 text-sm w-full rounded-lg border bg-transparent border-transparent focus:outline-none" placeholder="Minimum IDR 20.000" type="text" required></div>
                        </div>
                        <p class="text-red-500 text-sm mt-2 h-4" id="amount-error"></p>
                        <button type="submit" id="submit-deposit" class="w-full justify-center rounded-xl py-3 mt-5 bg-primary text-black transition-all duration-200 ease-in-out hover:lg:brightness-[0.9] disabled:opacity-50">
                            <span id="submit-deposit-text">Proses Deposit</span>
                            <svg id="submit-deposit-spinner" class="animate-spin h-5 w-5 text-black mx-auto hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </button>
                    </form>
                </div>

            </div>
        </section>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const depositFormContainer = document.getElementById('depositFormContainer');
    const newQrisSection = document.getElementById('newQrisSection');

    const depositForm = document.getElementById('deposit-form');
    const amountInput = document.getElementById('amount-input');
    const submitButton = document.getElementById('submit-deposit');
    const amountError = document.getElementById('amount-error');
    const submitDepositText = document.getElementById('submit-deposit-text');
    const submitDepositSpinner = document.getElementById('submit-deposit-spinner');
    const nominalButtons = document.querySelectorAll('.btn-nominal');

    const newQrisAmountDisplay = document.getElementById('newQrisAmountDisplay');
    const newQrisTrxIdDisplay = document.getElementById('newQrisTrxIdDisplay');
    const newQrisImage = document.getElementById('newQrisImage');
    const newQrisStatusText = document.getElementById('newQrisStatusText');
    const newQrisStatusText2 = document.getElementById('newQrisStatusText2');
    const newQrisErrorMessage = document.getElementById('newQrisErrorMessage');
    const newQrisCekStatusBtn = document.getElementById('newQrisCekStatusBtn');
    const newQrisBuatBaruBtn = document.getElementById('newQrisBuatBaruBtn');
    const qrisTitle = document.getElementById('qrisTitle');
    const newQrisInfoText = document.getElementById('newQrisInfoText'); // Elemen untuk info teks
    const homeRedirectUrl = '<?php echo rtrim($alamat_website, '/') . '/home'; ?>';

    let currentTrxId = null;
    let pollingInterval = null;
    let pollingAttempts = 0;
    const POLLING_INTERVAL = 5000;
    const MAX_POLLING_ATTEMPTS = 180;
    const MINIMUM_DEPOSIT = 1000;
    const TOTAL_QRIS_DURATION = 300; // 5 menit dalam detik

    // === Fungsi untuk hitungan mundur ===
    let countdownInterval = null;
    function startCountdown(durationInSeconds) {
        let timer = durationInSeconds;
        const countdownDisplay = document.getElementById('countdown-timer');

        if (countdownInterval) clearInterval(countdownInterval);

        countdownInterval = setInterval(function () {
            let minutes = parseInt(timer / 60, 10);
            let seconds = parseInt(timer % 60, 10);

            minutes = minutes < 10 ? "0" + minutes : minutes;
            seconds = seconds < 10 ? "0" + seconds : seconds;

            if (countdownDisplay) {
                 countdownDisplay.textContent = minutes + ":" + seconds;
            }

            if (--timer < 0) {
                clearInterval(countdownInterval);
                if (newQrisInfoText) newQrisInfoText.textContent = "Waktu pembayaran telah kedaluwarsa.";
                if (newQrisCekStatusBtn) newQrisCekStatusBtn.style.display = 'none';
                if (newQrisBuatBaruBtn) newQrisBuatBaruBtn.textContent = 'Buat Transaksi Baru';
            }
        }, 1000);
    }
    // === Akhir fungsi hitungan mundur ===

    const status_deposit_pending = <?php echo json_encode($status_deposit_pending); ?>;
    const jumlah_deposit_pending = <?php echo json_encode($jumlah_deposit_pending); ?>;
    const trx_id_pending = <?php echo json_encode($trx_id_pending); ?>;
    const qris_image_url_pending = <?php echo json_encode($qris_image_url_pending); ?>;
    const waktu_mulai_pending = '<?php echo $waktu_mulai_pending ?? ''; ?>';

    if (status_deposit_pending === 'diproses') {
        displayNewQris(jumlah_deposit_pending, trx_id_pending, qris_image_url_pending);
        qrisTitle.textContent = 'QRIS Pembayaran';
    }

    nominalButtons.forEach(button => {
        button.addEventListener('click', function() {
            const amount = this.dataset.amount;
            amountInput.value = amount.toLocaleString('id-ID');
            amountInput.dispatchEvent(new Event('input'));
        });
    });

    amountInput.addEventListener('input', function() {
        let value = this.value.replace(/\./g, '');
        if (value) {
            this.value = parseInt(value, 10).toLocaleString('id-ID');
        } else {
            this.value = '';
        }
        const amount = parseInt(value, 10);
        if (isNaN(amount) || amount < MINIMUM_DEPOSIT) {
            amountError.textContent = 'Minimum deposit adalah IDR ' + MINIMUM_DEPOSIT.toLocaleString('id-ID');
        } else {
            amountError.textContent = '';
        }
    });

    depositForm.addEventListener('submit', function(e) {
        e.preventDefault();
        amountError.textContent = '';
        const amountValue = amountInput.value.replace(/\./g, '');
        const amount = parseInt(amountValue, 10);

        if (isNaN(amount) || amount < MINIMUM_DEPOSIT) {
            Swal.fire({
                icon: 'error',
                title: 'Input Salah!',
                text: 'Minimum deposit adalah IDR ' + MINIMUM_DEPOSIT.toLocaleString('id-ID') + '. Silakan coba lagi.',
                customClass: { popup: 'minimalist', title: 'minimalist-title', htmlContainer: 'minimalist-text' }
            });
            amountError.textContent = 'Minimum deposit adalah IDR ' + MINIMUM_DEPOSIT.toLocaleString('id-ID');
            return;
        }

        if (submitButton.disabled) return;
        submitDepositText.classList.add('hidden');
        submitDepositSpinner.classList.remove('hidden');
        submitButton.disabled = true;

        const formData = new FormData();
        formData.append('jumlah_deposit', amount);

        fetch('qris_generate.php', { method: 'POST', body: formData })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(errData => { throw new Error(errData.message || 'Server Error: ' + response.status); });
                }
                return response.json();
            })
            .then(data => {
                if (data.success && data.qris_image_url) {
                    displayNewQris(data.amount, data.trx_id, data.qris_image_url);
                    qrisTitle.textContent = 'QRIS Pembayaran';
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: data.message || 'Gagal buat QRIS. Data tidak lengkap.',
                        customClass: { popup: 'minimalist', title: 'minimalist-title', htmlContainer: 'minimalist-text' }
                    });
                    amountError.textContent = data.message || 'Gagal buat QRIS. Data tidak lengkap.';
                }
            })
            .catch(error => {
                console.error('Error generating QRIS:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan Jaringan!',
                    text: 'Terjadi kesalahan saat membuat QRIS: ' + error.message,
                    customClass: { popup: 'minimalist', title: 'minimalist-title', htmlContainer: 'minimalist-text' }
                });
                amountError.textContent = 'Kesalahan: ' + error.message;
            })
            .finally(() => {
                submitDepositText.classList.remove('hidden');
                submitDepositSpinner.classList.add('hidden');
                submitButton.disabled = false;
            });
    });

    function displayNewQris(amount, trxId, imageUrl) {
        if(depositFormContainer) depositFormContainer.classList.add('hidden');
        if(newQrisSection) newQrisSection.classList.remove('hidden');
        
        newQrisAmountDisplay.textContent = 'IDR ' + parseInt(amount).toLocaleString('id-ID');
        newQrisTrxIdDisplay.textContent = trxId;
        newQrisImage.src = imageUrl;
        newQrisImage.classList.remove('hidden');
        
        newQrisStatusText.textContent = 'Menunggu Pembayaran...';
        newQrisStatusText2.textContent = 'Menunggu Pembayaran';
        newQrisErrorMessage.textContent = '';
        
        currentTrxId = trxId;
        startPollingStatus(trxId);
        startCountdown(TOTAL_QRIS_DURATION); // Mulai hitungan mundur 5 menit
    }

    function startPollingStatus(trxId) {
        if (pollingInterval) clearInterval(pollingInterval);
        pollingAttempts = 0;

        pollingInterval = setInterval(function() {
            pollingAttempts++;
            if (pollingAttempts > MAX_POLLING_ATTEMPTS) {
                clearInterval(pollingInterval);
                newQrisStatusText.textContent = 'Waktu pengecekan habis.';
                newQrisStatusText2.textContent = 'Waktu pengecekan habis.';
                newQrisErrorMessage.textContent = 'QRIS mungkin sudah kedaluwarsa, silakan buat transaksi baru.';
                return;
            }

            fetch('qris_status.php?trx_id=' + trxId)
                .then(response => response.json())
                .then(data => {
                    const status = data.payment_status;
                    if (status === 'SUCCESS' || status === 'PAID') {
                        newQrisStatusText.textContent = 'Pembayaran Berhasil!';
                        newQrisStatusText2.textContent = 'Pembayaran Berhasil!';
                        clearInterval(pollingInterval);
                        clearInterval(countdownInterval);
                        newQrisErrorMessage.textContent = 'Anda akan dialihkan ke Beranda...';
                        setTimeout(function() { window.location.href = homeRedirectUrl; }, 2000);
                    } else if (status === 'FAILED' || status === 'EXPIRED') {
                        newQrisStatusText.textContent = 'Pembayaran ' + status.charAt(0).toUpperCase() + status.slice(1).toLowerCase() + '.';
                        newQrisStatusText2.textContent = 'Pembayaran ' + status.charAt(0).toUpperCase() + status.slice(1).toLowerCase() + '.';
                        clearInterval(pollingInterval);
                        clearInterval(countdownInterval);
                    }
                })
                .catch(error => {
                    console.error('Error checking status (polling):', error);
                });
        }, POLLING_INTERVAL);
    }

    if(newQrisCekStatusBtn) {
        newQrisCekStatusBtn.addEventListener('click', function() {
            if (currentTrxId) {
                newQrisErrorMessage.textContent = 'Mengecek status...';
                fetch('qris_status.php?trx_id=' + currentTrxId)
                    .then(response => response.json())
                    .then(data => {
                        const status = data.payment_status;
                        if (status === 'SUCCESS' || status === 'PAID') {
                            newQrisStatusText.textContent = 'Pembayaran Berhasil!';
                            newQrisStatusText2.textContent = 'Pembayaran Berhasil!';
                            newQrisErrorMessage.textContent = 'Anda akan dialihkan ke Beranda...';
                            clearInterval(pollingInterval);
                            clearInterval(countdownInterval);
                            setTimeout(function() { window.location.href = homeRedirectUrl; }, 2000);
                        } else if (status === 'FAILED' || status === 'EXPIRED') {
                            newQrisStatusText.textContent = 'Pembayaran ' + status.charAt(0).toUpperCase() + status.slice(1).toLowerCase() + '.';
                            newQrisStatusText2.textContent = 'Pembayaran ' + status.charAt(0).toUpperCase() + status.slice(1).toLowerCase() + '.';
                            clearInterval(pollingInterval);
                            clearInterval(countdownInterval);
                        } else {
                            newQrisErrorMessage.textContent = 'Status masih ' + status + '. Jika sudah membayar, silakan klik tombol lagi.';
                        }
                    })
                    .catch(error => {
                        newQrisErrorMessage.textContent = 'Gagal cek status: ' + error.message;
                    });
            }
        });
    }

    if(newQrisBuatBaruBtn) {
        newQrisBuatBaruBtn.addEventListener('click', function() {
            if (pollingInterval) clearInterval(pollingInterval);
            if (countdownInterval) clearInterval(countdownInterval);
            if (newQrisSection) newQrisSection.classList.add('hidden');
            if (depositFormContainer) depositFormContainer.classList.remove('hidden');
            amountInput.value = '';
            amountInput.focus();
        });
    }

    // Cek transaksi pending saat halaman dimuat
    const status_deposit_pending_php = <?php echo json_encode($status_deposit_pending); ?>;
    const jumlah_deposit_pending_php = <?php echo json_encode($jumlah_deposit_pending); ?>;
    const trx_id_pending_php = <?php echo json_encode($trx_id_pending); ?>;
    const qris_image_url_pending_php = <?php echo json_encode($qris_image_url_pending); ?>;
    const waktu_mulai_pending_php = '<?php echo $waktu_mulai_pending ?? ''; ?>';

    if (status_deposit_pending_php === 'diproses') {
        displayNewQris(jumlah_deposit_pending_php, trx_id_pending_php, qris_image_url_pending_php);
        qrisTitle.textContent = 'QRIS Pembayaran';

        // Hitung sisa waktu berdasarkan waktu mulai dari database
        if (waktu_mulai_pending_php) {
            const startTime = new Date(waktu_mulai_pending_php.replace(/-/g, "/")).getTime();
            const now = new Date().getTime();
            const elapsedSeconds = Math.floor((now - startTime) / 1000);
            const remainingSeconds = TOTAL_QRIS_DURATION - elapsedSeconds;
            if (remainingSeconds > 0) {
                startCountdown(remainingSeconds);
            } else {
                if (newQrisInfoText) newQrisInfoText.textContent = "Waktu pembayaran telah kedaluwarsa.";
                if (newQrisCekStatusBtn) newQrisCekStatusBtn.style.display = 'none';
                if (newQrisBuatBaruBtn) newQrisBuatBaruBtn.textContent = 'Buat Transaksi Baru';
            }
        }
    }
});
</script>

<?php
if (isset($koneksi) && $koneksi instanceof mysqli && $koneksi->thread_id) {
    $koneksi->close();
}
if (ob_get_level() > 0) {
    ob_end_flush();
}
include_once 'bottom_navbar.php';
?>