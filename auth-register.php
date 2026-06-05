<?php
// Langkah 1: Muat data bank dari cek_nama.php tanpa output ke halaman
ob_start();
include_once 'cek_nama.php';
$rekeningData = $rekeningData ?? ['banks' => [], 'ewallets' => []];
ob_end_clean();

// Gabungkan semua 'key' bank dan e-wallet yang valid ke dalam satu array
$validBankKeys = array_merge(
    array_column($rekeningData['banks'], 'key'),
    array_column($rekeningData['ewallets'], 'key')
);
$validBankKeysJson = json_encode($validBankKeys);

// Daftar bank untuk tombol cepat
$quick_banks = [
    ["key" => "bca", "label" => "BCA"],
    ["key" => "bri", "label" => "BRI"],
    ["key" => "mandiri", "label" => "Mandiri"],
    ["key" => "bni", "label" => "BNI"],
    ["key" => "dana", "label" => "DANA"],
    ["key" => "linkaja", "label" => "LinkAja"],
    ["key" => "ovo", "label" => "OVO"],
    ["key" => "shopeepay", "label" => "ShopeePay"],
];

// Langkah 2: Sertakan header seperti biasa
include_once 'header.php';
?>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css" rel="stylesheet">
<style>
    .select2-container--default .select2-selection--single { background-color: transparent; border: 1px solid #444; height: 48px; color: #e0e0e0; border-radius: 0.75rem; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { color: #e0e0e0; line-height: 46px; padding-left: 12px !important; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 46px; }
    .select2-dropdown { background-color: #262626; border: 1px solid #444; }
    .select2-container--default .select2-search--dropdown .select2-search__field { background-color: #333; border: 1px solid #444; color: #e0e0e0; }
    .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable { background-color: #2ecc71; color: #111; }
    .select2-container .select2-selection--single .select2-selection__rendered { padding-right: 20px !important; }
    .select2-results__option.select2-results__option--new { color: #2ecc71; font-weight: bold; }
    .input-group-text-custom {
        display: flex;
        align-items: center;
        padding: 0.375rem 0.75rem;
        font-size: 1rem;
        font-weight: 400;
        line-height: 1.5;
        color: #e0e0e0;
        text-align: center;
        white-space: nowrap;
        background-color: transparent;
        border: 1px solid transparent;
        border-radius: 0.75rem;
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }
</style>

<section class="lg:bg-background-tertiary min-h-content pb-16 lg:pb-0">
    <div class="container mx-auto md:py-4 lg:py-8 lg:px-3">
        <div class="bg-background-default rounded-2xl p-4 lg:px-10 mx-auto md:w-4/5 lg:w-3/5">
            <section class="flex justify-center rounded-full bg-separator w-full lg:w-3/5 mx-auto overflow-hidden lg:mt-3 mb-6">
                <a class="w-1/2 justify-center bg-inverse rounded-full text-primary font-semibold py-2 text-center" href="?page=auth-register">Daftar</a>
                <a class="w-1/2 justify-center opacity-70 text-center py-2" href="?page=auth-login">Login</a>
            </section>

            <form id="registrationForm">
                <div class="relative mt-4 lg:mt-5 rounded-xl group border border-separator focus-within:border-primary focus-within:ring-1" id="username_wrapper">
                    <div class="absolute left-0 -top-4 lg:-top-[14px] mx-2 z-20 bg-background-default "><label class="text-[10px] lg:text-xs opacity-70 px-1 bg-background-default rounded-full ">Username</label></div>
                    <div class="relative"><input name="nama_pengguna_anggota" id="user_name" placeholder="6-14 Karakter Huruf Atau Angka" class="input-realtime-check p-3 pr-10 text-sm lg:text-base w-full rounded-lg border bg-transparent border-transparent focus:outline-none" type="text" required minlength="6" maxlength="14" data-field-name="username"><span class="status-icon" id="username_icon"></span></div>
                    <span id="username_status_message" class="status-message text-xs px-3 pt-1"></span>
                </div>
                
                <div class="relative mt-4 lg:mt-5 rounded-xl group border border-separator focus-within:border-primary focus-within:ring-1" id="password_wrapper">
                    <div class="absolute left-0 -top-4 lg:-top-[14px] mx-2 z-20 bg-background-default">
                        <label class="text-[10px] lg:text-xs opacity-70 px-1 bg-background-default rounded-full">Password</label>
                    </div>
                    <div class="relative flex items-center">
                        <input name="kata_sandi_anggota" id="password" placeholder="6-14 Karakter Huruf, Angka Atau Symbols" class="p-3 pr-10 text-sm lg:text-base w-full rounded-lg border bg-transparent border-transparent focus:outline-none" type="password" required minlength="6" maxlength="14">
                        <div class="absolute right-3 cursor-pointer text-gray-400" id="togglePassword">
                            <i class="bi bi-eye-slash"></i>
                        </div>
                    </div>
                    <span id="password_status_message" class="status-message text-xs px-3 pt-1"></span>
                </div>

                <div class="relative mt-4 lg:mt-5 rounded-xl group border border-separator focus-within:border-primary focus-within:ring-1" id="confirm_password_wrapper">
                    <div class="absolute left-0 -top-4 lg:-top-[14px] mx-2 z-20 bg-background-default">
                        <label class="text-[10px] lg:text-xs opacity-70 px-1 bg-background-default rounded-full">Konfirmasi Password</label>
                    </div>
                    <div class="relative flex items-center">
                        <input name="konfirmasi_kata_sandi_anggota" id="confirm_password" placeholder="Ulangi pasword seperti Di atas" class="p-3 pr-10 text-sm lg:text-base w-full rounded-lg border bg-transparent border-transparent focus:outline-none" type="password" required minlength="6" maxlength="14">
                        <div class="absolute right-3 cursor-pointer text-gray-400" id="toggleConfirmPassword">
                            <i class="bi bi-eye-slash"></i>
                        </div>
                    </div>
                    <span id="confirm_password_status_message" class="status-message text-xs px-3 pt-1"></span>
                </div>

                <div class="flex -mx-1">
                    <div class="w-4/12 lg:w-1/4 px-1">
                        <div class="relative mt-4 lg:mt-5 rounded-xl group border border-separator"><div class="absolute left-0 -top-4 lg:-top-[14px] mx-2 z-20 bg-background-default "><label class="text-[10px] lg:text-xs opacity-70 px-1 bg-background-default rounded-full ">Kode Negara</label></div><div class="relative"><input class="p-3 text-sm lg:text-base w-full rounded-lg border bg-transparent border-transparent focus:outline-none" value="+62" readonly></div></div>
                    </div>
                    <div class="w-8/12 lg:w-3/4 px-1">
                        <div class="relative mt-4 lg:mt-5 rounded-xl group border border-separator focus-within:border-primary focus-within:ring-1" id="telepon_wrapper">
                            <div class="absolute left-0 -top-4 lg:-top-[14px] mx-2 z-20 bg-background-default "><label class="text-[10px] lg:text-xs opacity-70 px-1 bg-background-default rounded-full ">No. Telepon</label></div>
                            <div class="relative"><input placeholder="Contoh: 8123456789" name="telepon_anggota" id="telepon_anggota" class="input-realtime-check p-3 pr-10 text-sm lg:text-base w-full rounded-lg border bg-transparent border-transparent focus:outline-none" type="number" required data-field-name="telepon"><span class="status-icon" id="telepon_icon"></span></div>
                            <span id="telepon_status_message" class="status-message text-xs px-3 pt-1"></span>
                        </div>
                    </div>
                </div>

                <div class="relative mt-4 lg:mt-5 rounded-xl group border-separator">
                    <div class="absolute left-0 -top-4 lg:-top-[14px] mx-2 z-20 bg-background-default">
                        <label class="text-[10px] lg:text-xs opacity-70 px-1 bg-background-default rounded-full">Pilih Bank / e-Wallet</label>
                    </div>
                    <select name="bank_anggota_key" id="bank_anggota" class="w-full" required>
                        <option></option>
                        <optgroup label="Bank">
                            <?php foreach ($rekeningData['banks'] as $bank): ?>
                                <option value="<?= htmlspecialchars($bank['key']) ?>"><?= htmlspecialchars($bank['label']) ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                        <optgroup label="E-Wallet">
                            <?php foreach ($rekeningData['ewallets'] as $ewallet): ?>
                                <option value="<?= htmlspecialchars($ewallet['key']) ?>"><?= htmlspecialchars($ewallet['label']) ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                    </select>
                </div>
                <div class="grid grid-cols-4 gap-2 mt-2">
                    <?php foreach ($quick_banks as $qbank): ?>
                        <button type="button" class="quick-bank-btn p-2 text-xs lg:text-sm bg-error rounded-lg text-white" data-bank-key="<?= htmlspecialchars($qbank['key']) ?>"><?= htmlspecialchars($qbank['label']) ?></button>
                    <?php endforeach; ?>
                </div>

                <div class="relative mt-4 lg:mt-5 rounded-xl group border border-separator focus-within:border-primary focus-within:ring-1" id="nomor_rekening_wrapper">
                    <div class="absolute left-0 -top-4 lg:-top-[14px] mx-2 z-20 bg-background-default "><label class="text-[10px] lg:text-xs opacity-70 px-1 bg-background-default rounded-full ">Nomor Rekening / Telepon</label></div>
                    <div class="relative">
                        <input placeholder="Nomor Rekening" name="nomor_rekening_anggota" id="nomor_rekening_anggota" class="p-3 pr-10 text-sm lg:text-base w-full rounded-lg border bg-transparent border-transparent focus:outline-none" type="number" required>
                        <span class="status-icon" id="nomor_rekening_icon"></span>
                    </div>
                    <span id="nomor_rekening_status_message" class="status-message text-xs px-3 pt-1"></span>
                </div>
                <div class="relative mt-4 lg:mt-5 rounded-xl group border border-separator focus-within:border-primary focus-within:ring-1">
                    <div class="absolute left-0 -top-4 lg:-top-[14px] mx-2 z-20 bg-background-default "><label class="text-[10px] lg:text-xs opacity-70 px-1 bg-background-default rounded-full ">Nama Lengkap sesuai Rekening</label></div>
                    <div class="relative"><input placeholder="Masukkan nama sesuai rekening Anda" name="nama_rekening_anggota" id="nama_rekening_anggota" class="p-3 text-sm lg:text-base w-full rounded-lg border bg-transparent border-transparent focus:outline-none" type="text" required></div>
                </div>
                <div class="relative mt-4 lg:mt-5 rounded-xl group border border-separator focus-within:border-primary focus-within:ring-1" id="email_wrapper">
                    <div class="absolute left-0 -top-4 lg:-top-[14px] mx-2 z-20 bg-background-default "><label class="text-[10px] lg:text-xs opacity-70 px-1 bg-background-default rounded-full ">Email (Opsional)</label></div>
                    <div class="relative"><input name="email_anggota" id="email_anggota" placeholder="Masukkan alamat email Anda (untuk reset password)" class="input-realtime-check p-3 pr-10 text-sm lg:text-base w-full rounded-lg border bg-transparent border-transparent focus:outline-none" type="email" data-field-name="email"><span class="status-icon" id="email_icon"></span></div>
                    <span id="email_status_message" class="status-message text-xs px-3 pt-1"></span>
                </div>
                <div class="relative mt-4 lg:mt-5 rounded-xl group border border-separator focus-within:border-primary focus-within:ring-1">
                    <div class="absolute left-0 -top-4 lg:-top-[14px] mx-2 z-20 bg-background-default"><label class="text-[10px] lg:text-xs opacity-70 px-1 bg-background-default rounded-full">Kode Referensi (Opsional)</label></div>
                    <div class="relative"><input name="upline" placeholder="Masukkan kode referral jika ada" class="p-3 text-sm lg:text-base w-full rounded-lg border bg-transparent border-transparent focus:outline-none" type="text" value="<?php echo isset($_GET['refferal']) ? htmlspecialchars(trim($_GET['refferal'])) : ''; ?>" <?php echo isset($_GET['refferal']) && !empty(trim($_GET['refferal'])) ? 'readonly' : ''; ?> ></div>
                </div>

                <p class="mt-6 mb-8 text-xs lg:text-sm lg:text-center">
                    <input type="checkbox" id="terms_checkbox" required class="mr-2">
                    Dengan mendaftar, Anda menyetujui <a target="_blank" class="text-error inline-block">Syarat dan Ketentuan</a> kami.
                </p>
                <div class="flex justify-center my-4">
                    <button type="submit" id="registerSubmitBtn" class="bg-primary lg:hover:brightness-95 text-white rounded-xl text-sm lg:text-base font-semibold w-full lg:w-1/2 justify-center py-3">Daftar</button>
                </div>
            </form>
        </div>
    </div>
</section>

<?php include_once 'footer.php'; ?>

<div id="globalLoadingIndicator">
    <div class="spinner"></div>
    <p id="loadingMessage">Memproses...</p>
</div>

<style>
    #globalLoadingIndicator {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.65);
        z-index: 99999;
        display: none;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
    }
    #globalLoadingIndicator .spinner {
        border: 8px solid #4A5568;
        border-top: 8px solid #FCD34D;
        border-radius: 50%;
        width: 60px;
        height: 60px;
        animation: spinGlobalLoader 1s linear infinite;
        margin-bottom: 15px;
    }
    #globalLoadingIndicator p {
        color: white;
        font-size: 1.1em;
    }
    #globalLoadingIndicator.success .spinner {
        animation: none;
        border-top-color: #2ecc71;
        border-left-color: #2ecc71;
        border-right-color: #2ecc71;
        border-bottom-color: #2ecc71;
    }
    @keyframes spinGlobalLoader {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    .form-message {
        margin-top: 15px;
        padding: 10px;
        border-radius: 5px;
        font-size: 0.9em;
        text-align: center;
    }
    .form-message.success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    .form-message.error {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    .input-wrapper.input-error .border-separator { border-color: red !important; }
    .input-wrapper.input-available .border-separator { border-color: green !important; }
    .input-wrapper.input-checking .border-separator { border-color: orange !important; }
    .status-message { display: block; min-height: 1.2em; }
    .status-message.text-error, .status-message.text-invalid { color: red; }
    .status-message.text-available { color: green; }
    .status-message.text-checking { color: orange; }
    .status-message.text-neutral { color: #888; }
    .status-icon { position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); display: flex; align-items: center; justify-content: center; width: 20px; height: 20px; }
    .pr-10 { padding-right: 2.5rem !important; }
    .quick-bank-btn {
        transition: background-color 0.2s, transform 0.2s;
    }
    .quick-bank-btn:hover {
        background-color: #d11a2a;
        transform: scale(1.02);
    }
</style>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- Inisialisasi & Variabel ---
    $('#bank_anggota').select2({
        placeholder: 'Cari atau ketik nama bank/e-wallet',
        width: '100%',
        tags: true,
        createTag: function(params) {
            var term = $.trim(params.term);
            if (term === '') { return null; }
            return { id: term.toUpperCase(), text: "Gunakan input manual: " + term, newTag: true, className: 'select2-results__option--new' };
        },
        language: {
            noResults: function() { return "Data bank tidak ditemukan."; }
        }
    });

    const form = document.getElementById('registrationForm');
    const inputsToCheck = form.querySelectorAll('.input-realtime-check');
    const usernameInput = document.getElementById('user_name');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const togglePassword = document.getElementById('togglePassword');
    const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
    const bankSelect = $('#bank_anggota');
    const registerSubmitBtn = document.getElementById('registerSubmitBtn');
    const globalLoadingIndicator = document.getElementById('globalLoadingIndicator');
    const loadingMessageElement = document.getElementById('loadingMessage');
    const loadingSpinnerElement = globalLoadingIndicator.querySelector('.spinner');
    const termsCheckbox = document.getElementById('terms_checkbox');
    const quickBankButtons = document.querySelectorAll('.quick-bank-btn');

    // --- Loading Indicator Functions ---
    function showGlobalLoading(message = 'Memproses...', resetSpinner = true) {
        loadingMessageElement.textContent = message;
        globalLoadingIndicator.style.display = 'flex';
        if (resetSpinner) {
            loadingSpinnerElement.classList.remove('success');
            loadingSpinnerElement.style.borderColor = '#4A5568';
            loadingSpinnerElement.style.borderTopColor = '#FCD34D';
            loadingSpinnerElement.classList.add('animate-spin');
        }
    }

    function hideGlobalLoading() {
        globalLoadingIndicator.style.display = 'none';
        loadingSpinnerElement.classList.remove('animate-spin');
    }

    function updateLoadingState(success, message) {
        if (success) {
            loadingMessageElement.textContent = message;
            loadingSpinnerElement.classList.remove('animate-spin');
            loadingSpinnerElement.classList.add('success');
        } else {
            loadingMessageElement.textContent = message;
            loadingSpinnerElement.classList.remove('animate-spin');
            loadingSpinnerElement.style.borderColor = '#e74c3c';
            loadingSpinnerElement.style.borderTopColor = '#e74c3c';
        }
    }

    // --- Utility Icons & Functions ---
    let debounceTimer;
    const iconCheckSvg = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="green" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>`;
    const iconCrossSvg = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="red" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293 5.354 4.646z"/></svg>`;
    const iconLoadingSvg = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="orange" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="animate-spin"><path d="M21 12a9 9 0 1 1-6.219-8.56"></path></svg>`;

    function setFieldStatus(wrapperId, messageId, iconId, status, message = '', iconSvg = '') {
        const wrapperElement = document.getElementById(wrapperId);
        const messageElement = document.getElementById(messageId);
        const iconElement = document.getElementById(iconId);
        if (wrapperElement) wrapperElement.classList.remove('input-error', 'input-available', 'input-checking');
        if (messageElement) { messageElement.textContent = message; messageElement.className = 'status-message text-xs px-3 pt-1'; }
        if (iconElement) iconElement.innerHTML = iconSvg;
        switch (status) {
            case 'error': case 'exists': case 'invalid_length': case 'invalid_format':
                if (wrapperElement) wrapperElement.classList.add('input-error');
                if (messageElement) messageElement.classList.add('text-error');
                if (iconElement) iconElement.innerHTML = iconCrossSvg; break;
            case 'success': case 'available':
                if (wrapperElement) wrapperElement.classList.add('input-available');
                if (messageElement) messageElement.classList.add('text-available');
                if (iconElement) iconElement.innerHTML = iconCheckSvg; break;
            case 'checking':
                if (wrapperElement) wrapperElement.classList.add('input-checking');
                if (messageElement) messageElement.classList.add('text-checking');
                if (iconElement) iconElement.innerHTML = iconLoadingSvg; break;
            case 'neutral':
                 if (messageElement) messageElement.classList.add('text-neutral'); break;
            default: if (iconElement) iconElement.innerHTML = ''; break;
        }
    }
    
    // --- Toggle Password Visibility ---
    togglePassword.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        this.querySelector('i').classList.toggle('bi-eye');
        this.querySelector('i').classList.toggle('bi-eye-slash');
    });

    toggleConfirmPassword.addEventListener('click', function() {
        const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        confirmPasswordInput.setAttribute('type', type);
        this.querySelector('i').classList.toggle('bi-eye');
        this.querySelector('i').classList.toggle('bi-eye-slash');
    });

    // --- Quick Bank Buttons ---
    quickBankButtons.forEach(button => {
        button.addEventListener('click', function() {
            const bankKey = this.getAttribute('data-bank-key');
            bankSelect.val(bankKey).trigger('change');
        });
    });

    // Realtime check untuk username & email
    inputsToCheck.forEach(inputElement => {
        const fieldName = inputElement.dataset.fieldName;
        inputElement.addEventListener('input', function() {
            const fieldValue = this.value.trim();
            clearTimeout(debounceTimer);
            setFieldStatus(fieldName + '_wrapper', fieldName + '_status_message', fieldName + '_icon', 'reset', '');

            if (fieldName === 'username') {
                if (fieldValue.includes(' ')) {
                    setFieldStatus(fieldName + '_wrapper', fieldName + '_status_message', fieldName + '_icon', 'error', 'Username tidak boleh mengandung spasi.');
                    return;
                }
                if (!/^[a-zA-Z0-9]+$/.test(fieldValue) && fieldValue.length > 0) {
                     setFieldStatus(fieldName + '_wrapper', fieldName + '_status_message', fieldName + '_icon', 'error', 'Username hanya boleh huruf dan angka.');
                     return;
                }
            }

            if (fieldValue === '' && fieldName !== 'email') return;
            if (fieldValue === '' && fieldName === 'email') { setFieldStatus('email_wrapper', 'email_status_message', 'email_icon', 'neutral', 'Email (opsional)', ''); return; }

            setFieldStatus(fieldName + '_wrapper', fieldName + '_status_message', fieldName + '_icon', 'checking', 'Mengecek...', iconLoadingSvg);
            debounceTimer = setTimeout(() => {
                const formData = new FormData(); formData.append('field_name', fieldName); formData.append('field_value', fieldValue);
                fetch('check_availability.php', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.field === fieldName) {
                        setFieldStatus(fieldName + '_wrapper', fieldName + '_status_message', fieldName + '_icon', data.status, data.message, data.status === 'available' ? iconCheckSvg : iconCrossSvg);
                    }
                })
                .catch(error => setFieldStatus(fieldName + '_wrapper', fieldName + '_status_message', fieldName + '_icon', 'error', 'Error pengecekan.', iconCrossSvg));
            }, 500);
        });
    });

    // --- Validasi Password Confirmation ---
    function validatePasswordConfirmation() {
        const passVal = passwordInput.value;
        const confirmPassVal = confirmPasswordInput.value;

        if (passVal.length > 0) {
            if (passVal.length < 6 || passVal.length > 14) {
                setFieldStatus('password_wrapper', 'password_status_message', 'password_icon', 'invalid_length', 'Password harus 6-14 karakter.');
            } else {
                setFieldStatus('password_wrapper', 'password_status_message', 'password_icon', 'success', 'Password valid.');
            }
        } else {
            setFieldStatus('password_wrapper', 'password_status_message', 'password_icon', 'reset', '');
        }

        if (confirmPassVal.length > 0) {
            if (passVal === confirmPassVal) {
                if (confirmPassVal.length < 6 || confirmPassVal.length > 14) {
                    setFieldStatus('confirm_password_wrapper', 'confirm_password_status_message', 'confirm_password_icon', 'invalid_length', 'Password harus 6-14 karakter.');
                } else {
                    setFieldStatus('confirm_password_wrapper', 'confirm_password_status_message', 'confirm_password_icon', 'success', 'Password cocok.');
                }
            } else {
                setFieldStatus('confirm_password_wrapper', 'confirm_password_status_message', 'confirm_password_icon', 'error', 'Password tidak cocok.');
            }
        } else {
            setFieldStatus('confirm_password_wrapper', 'confirm_password_status_message', 'confirm_password_icon', 'reset', '');
        }
    }
    if (passwordInput) passwordInput.addEventListener('input', validatePasswordConfirmation);
    if (confirmPasswordInput) confirmPasswordInput.addEventListener('input', validatePasswordConfirmation);

    // --- AJAX FORM SUBMISSION ---
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        // Validasi Checkbox Syarat & Ketentuan
        if (!termsCheckbox.checked) {
            showGlobalLoading('Anda harus menyetujui Syarat dan Ketentuan.', false);
            termsCheckbox.focus();
            setTimeout(hideGlobalLoading, 2000);
            return;
        }

        let isValid = true;
        if (!usernameInput.value.trim() || usernameInput.value.trim().includes(' ')) {
            setFieldStatus('username_wrapper', 'username_status_message', 'username_icon', 'error', 'Username tidak boleh kosong atau mengandung spasi.');
            isValid = false;
        }
        if (passwordInput.value !== confirmPasswordInput.value) {
            setFieldStatus('confirm_password_wrapper', 'confirm_password_status_message', 'confirm_password_icon', 'error', 'Password tidak cocok.');
            isValid = false;
        }
        if (!isValid) {
            showGlobalLoading('Validasi Gagal! Periksa kembali form.');
            setTimeout(hideGlobalLoading, 2000);
            return;
        }

        registerSubmitBtn.disabled = true;
        showGlobalLoading('Memproses Pendaftaran...');

        const formData = new FormData(form);

        // 🔥 REVISI: Mengubah target endpoint AJAX langsung menembak ke file process_register.php murni
        fetch('process_register.php', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateLoadingState(true, 'Pendaftaran Berhasil!');
                form.reset();
                setTimeout(() => {
                    hideGlobalLoading();
                    window.location.href = data.redirect;
                }, 1500);
            } else {
                updateLoadingState(false, data.message);
                setTimeout(() => {
                    hideGlobalLoading();
                }, 2000);
            }
        })
        .catch(error => {
            console.error('Error during registration AJAX:', error);
            updateLoadingState(false, 'Terjadi kesalahan jaringan atau server. Silakan coba lagi.');
            setTimeout(() => {
                hideGlobalLoading();
            }, 2000);
        })
        .finally(() => {
            registerSubmitBtn.disabled = false;
        });
    });
}); // 🔥 CLOSING DOMCONTENTLOADED PINDAH KE SINI BIAR TIDAK BUBAR KODENYA
</script>
