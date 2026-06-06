<?php
$id_anggota = $_SESSION['id_anggota'] ?? null;
$username = $_SESSION['nama_pengguna_anggota'] ?? null;
$alamat_website = (isset($alamat_website) ? rtrim($alamat_website, '/') . '/' : '/');
$isi_1_link_livechat_web = (isset($isi_1_link_livechat_web) ? $isi_1_link_livechat_web : '#livechat');
$isi_1_popup_teks_belum_login_web = $isi_1_popup_teks_belum_login_web ?? 'Silakan login untuk bermain.';

ob_start();
include_once 'cek_nama.php';
$rekeningData = $rekeningData ?? ['banks' => [], 'ewallets' => []];
ob_end_clean();

$validBankKeys = array_merge(
    array_column($rekeningData['banks'], 'key'),
    array_column($rekeningData['ewallets'], 'key')
);
$validBankKeysJson = json_encode($validBankKeys);

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

?>

<style>
:root {
    --primary: #1E90FF;
    --separator: #B0B0B0;
    --background-secondary: #172B3F;
    --background-default: #213B53;
    --text-color: #e0e0e0;
    --text-caption: #888;
    --error-color: #dc3545;
    --success-color: #28a745;
}

.bottom-navbar-container {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 99;
}
.bottom-navbar {
    background-color: var(--background-secondary);
    border-top: 2px solid var(--background-default);
    display: flex;
    justify-content: space-around;
    align-items: center;
    padding: 8px 12px;
    box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.3);
    height: 60px;
    position: relative;
}
.nav-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    color: var(--separator);
    width: 20%;
    transition: color 0.3s ease;
}
.nav-item.active-item { color: var(--primary); }
.nav-item-icon {
    width: 24px;
    height: 24px;
    display: flex;
    justify-content: center;
    align-items: center;
    transition: transform 0.3s ease;
}
.nav-item.active-item .nav-item-icon { transform: translateY(-8px) scale(1.1); color: var(--primary); }
.nav-item-text {
    font-size: 10px;
    margin-top: 4px;
    text-align: center;
    font-weight: 500;
    transition: color 0.3s ease, opacity 0.3s ease, transform 0.3s ease;
}
.nav-item.active-item .nav-item-text { color: var(--primary); opacity: 1; transform: translateY(-5px); }
.main-btn-container {
    position: relative;
    width: 20%;
    height: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
}
.main-btn {
    position: absolute;
    top: -20px;
    width: 65px;
    height: 65px;
    background: linear-gradient(to bottom right, var(--primary), #1B1B1B);
    border-radius: 50%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    box-shadow: 0 -4px 10px rgba(0, 0, 0, 0.4);
    border: 4px solid var(--background-secondary);
    cursor: pointer;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.main-btn:active { transform: scale(0.95); box-shadow: 0 -2px 5px rgba(0, 0, 0, 0.4); }
.main-btn-icon { width: 32px; height: 32px; fill: white; transition: transform 0.3s ease; }
.main-btn.active .main-btn-icon { transform: rotate(90deg); }
.main-btn-text { font-size: 10px; color: white; margin-top: 4px; }

.menu-popup-container, .modal-auth-container {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(3px);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
    visibility: hidden;
    opacity: 0;
    transition: opacity 0.3s ease-in-out, visibility 0.3s ease-in-out;
}
.menu-popup-container.show, .modal-auth-container.show { visibility: visible; opacity: 1; }

.modal-auth-content {
    background-color: var(--background-default);
    padding: 20px;
    border-radius: 10px;
    width: 90%;
    max-width: 400px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5);
    color: white;
    transform: scale(0.95);
    opacity: 0;
    transition: opacity 0.3s ease-in-out, transform 0.3s ease-in-out;
    position: relative;
    max-height: 90vh;
    overflow-y: auto;
}
.modal-auth-container.show .modal-auth-content { opacity: 1; transform: scale(1); }
.modal-auth-close {
    position: absolute;
    top: 10px;
    right: 10px;
    background: none;
    border: none;
    color: white;
    font-size: 24px;
    cursor: pointer;
}
.form-group { margin-bottom: 15px; }
.form-group label { display: block; margin-bottom: 5px; font-size: 14px; }
.form-group input,
.form-group select {
    width: 100%;
    padding: 10px;
    background-color: #333;
    border: 1px solid #555;
    border-radius: 5px;
    color: white;
    box-sizing: border-box;
}
.form-group input:focus,
.form-group select:focus { outline: none; border-color: var(--primary); }
.auth-button {
    width: 100%;
    padding: 10px;
    background-color: var(--primary);
    border: none;
    border-radius: 5px;
    color: black;
    font-weight: bold;
    cursor: pointer;
}
.auth-button:disabled { background-color: #555; cursor: not-allowed; }
.auth-switch-text { text-align: center; margin-top: 15px; font-size: 12px; }
.auth-switch-text a { color: var(--primary); text-decoration: none; }

.input-group { position: relative; }
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
.status-icon {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
}
.input-realtime-check { padding-right: 40px; }
.status-message { display: block; min-height: 1.2em; font-size: 12px; margin-top: 5px; }
.text-error { color: var(--error-color); }
.text-success { color: var(--success-color); }
.text-checking { color: orange; }
.quick-bank-btn {
    transition: background-color 0.2s, transform 0.2s;
    background-color: #dc3545;
    color: white;
    border: none;
    padding: 8px;
    border-radius: 5px;
    font-size: 12px;
    cursor: pointer;
}
.quick-bank-btn:hover { background-color: #c82333; transform: scale(1.02); }
.quick-bank-btn.selected { background-color: #28a745; }
    
.menu-content-wrapper {
    position: relative;
    width: 90%;
    max-width: 400px;
    transition: transform 0.3s ease-in-out;
}
.menu-content {
    background-color: var(--background-secondary);
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5);
    width: 100%;
    color: white;
    transform: scale(0.95);
    opacity: 0;
    transition: opacity 0.3s ease-in-out, transform 0.3s ease-in-out;
    display: flex;
    flex-direction: column;
}
.menu-popup-container.show .menu-content { opacity: 1; transform: scale(1); }
.close-btn {
    position: absolute;
    bottom: -50px;
    left: 50%;
    transform: translateX(-50%);
    background: var(--primary);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    font-size: 16px;
    cursor: pointer;
    white-space: nowrap;
}
.close-btn:hover { background-color: #3e8e41; }
.search-bar {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid var(--separator);
    border-radius: 5px;
    background-color: #333;
    color: white;
    transition: border-color 0.3s ease;
}
.search-bar:focus { outline: none; border-color: var(--primary); }
.game-list-menu {
    display: none;
    flex-wrap: nowrap;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    gap: 10px;
    padding: 10px 0;
    margin-top: 15px;
}
.game-list-menu.show-game-list { display: flex; }
.game-list-item {
    flex-shrink: 0;
    width: 100px;
    text-align: center;
    text-decoration: none;
    color: white;
    font-size: 10px;
    transition: transform 0.2s ease;
}
.game-list-item:hover { transform: scale(1.05); }
.game-image-wrapper {
    width: 100px;
    height: 100px;
    border-radius: 5px;
    overflow: hidden;
    margin-bottom: 5px;
}
.game-image-wrapper img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.menu-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
    text-align: center;
}
.menu-grid a {
    padding: 12px;
    background-color: #444;
    border-radius: 5px;
    text-decoration: none;
    color: white;
    transition: background-color 0.2s ease;
}
.menu-grid a:hover { background-color: var(--primary); }
.menu-grid .logout-btn { background-color: #dc3545; }
.menu-grid .logout-btn:hover { background-color: #c82333; }
    
#pageFullLoadingIndicator, #authLoadingIndicator {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.65);
    z-index: 99999;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}
@keyframes spinPageLoaderFull { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
#pageFullLoadingIndicator .spinner, #authLoadingIndicator .spinner {
    border: 8px solid #4A5568;
    border-top: 8px solid #FCD34D;
    border-radius: 50%;
    width: 60px;
    height: 60px;
    animation: spinPageLoaderFull 1s linear infinite;
}
#pageFullLoadingIndicator p, #authLoadingIndicator p {
    color: white;
    margin-top: 15px;
    font-size: 1.1em;
}

</style>

<div id="pageFullLoadingIndicator">
    <div class="spinner"></div>
    <p>Memuat Permainan...</p>
</div>

<div id="authLoadingIndicator">
    <div class="spinner"></div>
    <p id="authLoadingMessage">Memproses...</p>
</div>

<div class="modal-auth-container" id="loginModal">
    <div class="modal-auth-content">
        <button class="modal-auth-close" onclick="closeLoginModal()">&#x2715;</button>
        <h3 style="margin-bottom: 20px; text-align: center;">Login</h3>
        <form id="loginForm">
            <div class="form-group">
                <label for="login-username">Username</label>
                <input type="text" id="login-username" name="nama_pengguna_anggota" required>
            </div>
            <div class="form-group">
                <label for="login-password">Password</label>
                <input type="password" id="login-password" name="kata_sandi_anggota" required>
            </div>
            <p id="login-error-message" style="color: red; font-size: 12px; text-align: center; margin-bottom: 10px;"></p>
            <button type="submit" class="auth-button" id="loginSubmitButton">Login</button>
        </form>
        <p class="auth-switch-text">Belum punya akun? <a href="#" onclick="showRegisterModal(); return false;">Daftar</a></p>
    </div>
</div>

<div class="modal-auth-container" id="registerModal">
    <div class="modal-auth-content">
        <button class="modal-auth-close" onclick="closeRegisterModal()">&#x2715;</button>
        <h3 style="margin-bottom: 20px; text-align: center;">Register</h3>
        <form id="registerForm">
            <div class="input-group form-group">
                <label for="register-username">Username</label>
                <input type="text" id="register-username" name="nama_pengguna_anggota" class="input-realtime-check" placeholder="6-14 Karakter Huruf Atau Angka" required minlength="6" maxlength="14" data-field-name="username">
                <span class="status-icon" id="register_username_icon"></span>
                <span id="register_username_status_message" class="status-message"></span>
            </div>
            
            <div class="input-group form-group">
                <label for="register-password">Password</label>
                <input type="password" id="register-password" name="kata_sandi_anggota" placeholder="6-14 Karakter Huruf, Angka Atau Symbols" required minlength="6" maxlength="14">
                <span class="status-icon" id="register_password_icon" style="right: 10px;">
                     <i class="bi bi-eye-slash" id="togglePasswordRegister" style="cursor: pointer;"></i>
                </span>
                <span id="register_password_status_message" class="status-message"></span>
            </div>
            
            <div class="input-group form-group">
                <label for="register-confirm-password">Konfirmasi Password</label>
                <input type="password" id="register-confirm-password" name="konfirmasi_kata_sandi_anggota" placeholder="Ulangi pasword seperti Di atas" required minlength="6" maxlength="14">
                <span class="status-icon" id="register_confirm_password_icon" style="right: 10px;">
                    <i class="bi bi-eye-slash" id="toggleConfirmPasswordRegister" style="cursor: pointer;"></i>
                </span>
                <span id="register_confirm_password_status_message" class="status-message"></span>
            </div>

            <div class="form-group">
                <label for="register-phone">No. Telepon</label>
                <input type="number" id="register-phone" name="telepon_anggota" class="input-realtime-check" placeholder="Contoh: 8123456789" required data-field-name="telepon">
                <span id="register_telepon_status_message" class="status-message"></span>
            </div>

            <div class="form-group">
                <label for="register-bank">Pilih Bank / e-Wallet</label>
                <select name="bank_anggota_key" id="register-bank" class="w-full" required>
                    <option value="" disabled selected>Pilih Bank atau e-Wallet</option>
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
                <div class="grid grid-cols-4 gap-2 mt-2">
                    <?php foreach ($quick_banks as $qbank): ?>
                        <button type="button" class="quick-bank-btn" data-bank-key="<?= htmlspecialchars($qbank['key']) ?>"><?= htmlspecialchars($qbank['label']) ?></button>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-group">
                <label for="register-account-number">Nomor Rekening / Telepon</label>
                <input type="text" id="register-account-number" name="nomor_rekening_anggota" placeholder="Nomor Rekening" required>
            </div>

            <div class="form-group">
                <label for="register-account-name">Nama Lengkap sesuai Rekening</label>
                <input type="text" id="register-account-name" name="nama_rekening_anggota" placeholder="Masukkan nama sesuai rekening Anda" required>
            </div>
            
            <div class="form-group">
                <label for="register-email">Email (Opsional)</label>
                <input type="email" id="register-email" name="email_anggota" class="input-realtime-check" placeholder="Masukkan alamat email Anda (untuk reset password)" data-field-name="email">
                <span id="register_email_status_message" class="status-message"></span>
            </div>

            <div class="form-group">
                <label for="register-referral">Kode Referensi (Opsional)</label>
                <input type="text" id="register-referral" name="upline" placeholder="Masukkan kode referral jika ada">
            </div>
            
            <p style="margin-top: 15px; font-size: 12px; text-align: center;">
                <input type="checkbox" id="register_terms_checkbox" required class="mr-2">
                Dengan mendaftar, Anda menyetujui <a target="_blank" href="<?php echo htmlspecialchars($alamat_website . 'syarat-dan-ketentuan'); ?>" class="text-error" style="color: red;">Syarat dan Ketentuan</a> kami.
            </p>
            
            <p id="register-error-message" style="color: red; font-size: 12px; text-align: center; margin-top: 10px;"></p>
            <button type="submit" class="auth-button" id="registerSubmitButton">Daftar</button>
        </form>
        <p class="auth-switch-text">Sudah punya akun? <a href="#" onclick="showLoginModal(); return false;">Login</a></p>
    </div>
</div>

<div class="menu-popup-container" id="menuPopup">
    <div class="menu-content-wrapper">
        <div class="menu-content">
            <input type="text" class="search-bar" placeholder="Cari game..." id="searchBar">
            
            <div class="game-list-menu" id="game-list-menu">
                </div>

            <h3 style="margin-top: 15px; font-size: 14px;">Menu Utama</h3>
            <hr style="border-color: #444; margin: 10px 0;">

            <?php if (isset($id_anggota) && $id_anggota !== null): ?>
                <div class="menu-grid">
                    <a href="<?php echo htmlspecialchars($alamat_website . 'rtp.php'); ?>">RTP</a>
                    <a href="<?php echo htmlspecialchars($alamat_website . 'qris.php'); ?>">Deposit</a>
                    <a href="<?php echo htmlspecialchars($alamat_website . 'withdraw.php'); ?>">Withdraw</a>
                    <a href="<?php echo htmlspecialchars($alamat_website . 'promo.php'); ?>">Blog</a>
                    <a href="<?php echo htmlspecialchars($alamat_website . 'aduan.php'); ?>">Aduan</a>
                    <a href="<?php echo htmlspecialchars($alamat_website . 'logout.php'); ?>" class="logout-btn">Logout</a>
                </div>
            <?php else: ?>
                <div class="menu-grid">
                    <a href="<?php echo htmlspecialchars($alamat_website . 'rtp.php'); ?>">RTP</a>
                    <a href="#" onclick="showLoginModal(); return false;">Login</a>
                    <a href="#" onclick="showRegisterModal(); return false;">Register</a>
                    <a href="<?php echo htmlspecialchars($alamat_website . 'promo.php'); ?>">Blog</a>
                </div>
            <?php endif; ?>
        </div>
        <button class="close-btn" id="closeMenuButton">Tutup Menu</button>
    </div>
</div>

<div class="bottom-navbar-container">
    <nav class="bottom-navbar">
        <?php if (isset($id_anggota) && $id_anggota !== null): ?>
            <a class="nav-item" href="<?php echo htmlspecialchars($alamat_website . 'home'); ?>">
                <div class="nav-item-icon">
                    <svg width="100%" height="100%" viewbox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M21.462 13.303c-.348.348-.81.54-1.302.54h-.302v6.004a2.157 2.157 0 0 1-2.155 2.155h-3.778v-5.294a.984.984 0 0 0-.984.983H11.06a.984.984 0 0 0-.984.983v5.294H6.297a2.158 2.158 0 0 1-2.155-2.155v-6.005h-.325c-.02 0-.038 0-.057-.002a1.843 1.843 0 0 1-1.225-3.137l.008-.009 8.155-8.155A1.83 1.83 0 0 1 12 2c.492 0 .954.193 1.302.54l8.16 8.16a1.844 1.844 0 0 1 0 2.604Z"></path></svg>
                </div>
                <p class="nav-item-text">Beranda</p>
            </a>
            <a class="nav-item" href="<?php echo htmlspecialchars($alamat_website . 'history-deposit.php'); ?>">
                <div class="nav-item-icon">
                    <svg width="100%" height="100%" viewbox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 1v5.56c0 .466 0 .92.05 1.294.057.421.195.902.594 1.302.4.4.88.537 1.302.594.374.05.828.05 1.294.05h5.56v6.6c0 3.111 0 4.667-.966 5.634C18.867 23 17.31 23 14.2 23H9.8c-3.111 0-4.667 0-5.633-.966C3.2 21.067 3.2 19.51 3.2 16.4V7.6c0-3.111 0-4.667.967-5.633C5.133 1 6.689 1 9.8 1H12Zm2.2.005V6.5c0 .55.002.851.03 1.06l.002.008.008.002c.209.028.51.03 1.06.03h5.495c-.011-.453-.047-.752-.163-1.03-.167-.405-.485-.723-1.12-1.359L16.588 2.29c-.636-.636-.954-.954-1.358-1.122-.278-.115-.578-.15-1.031-.162ZM7.6 13.1A1.1 1.1 0 0 1 8.7 12h6.6a1.1 1.1 0 0 1 0 2.2H8.7a1.1 1.1 0 0 1-1.1-1.1Zm1.1 3.3a1.1 1.1 0 0 0 0 2.2h4.4a1.1 1.1 0 0 0 0-2.2H8.7Z"></path></svg>
                </div>
                <p class="nav-item-text">History</p>
            </a>
            <div class="main-btn-container">
                <button class="main-btn" id="mainButton">
                    <svg class="main-btn-icon" viewBox="0 0 24 24" fill="white" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#a)"><path d="M13.942 18.995c-.341.341-.925.1-.925-.383V16.39a.541.541 0 0 0-.541-.541h-.375c-.3 0-.542.242-.542.541v2.224a.541.541 0 0 1-.924.382.541.541 0 0 0-.765 0l-.265.265a.541.541 0 0 0 0 .766l2.3 2.3a.541.541 0 0 0 .766 0l2.3-2.3a.541.541 0 0 0 0-.765l-.264-.266a.54.54 0 0 0-.765 0Z" fill="white"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M12.288 16.577a7.288 7.288 0 1 0 0-14.577 7.288 7.288 0 1 0 0 14.577Zm2.186-9.475h-2.186a.73.73 0 0 0 0 1.457c1.205 0 2.186.981 2.186 2.187 0 .949-.611 1.75-1.457 2.052v1.592h-1.458v-1.458h-1.457v-1.457h2.186a.73.73 0 0 0 0-1.458 2.189 2.189 0 0 1-2.186-2.186c0-.95.611-1.751 1.457-2.053V4.187h1.458v1.458h1.457v1.457Z" fill="white"></path><path d="M7.186 22.407H5.73a.729.729 0 0 1 0-1.458.729.729 0 0 0-.728-.728.729.729 0 0 1 0-1.458.729.729 0 0 0-.729-.728.729.729 0 0 1-.728-.729V.593a.593.593 0 0 1 1.187 0v1.458a.729.729 0 0 1 1.457 0c0 .402-.326.729-.728.729a.729.729 0 0 1 0 1.457c.402 0 .729.326.729.728v1.458a.729.729 0 0 1-.728.729Z" fill="white"></path></g><defs><clippath id="a"><path fill="#fff" transform="translate(5 2)" d="M0 0h14.577v20.485H0z"></path></clippath></defs></svg>
                    <p class="main-btn-text">Menu</p>
                </button>
            </div>
            <a class="nav-item" href="<?php echo htmlspecialchars($alamat_website . 'my-account.php'); ?>">
                <div class="nav-item-icon">
                    <svg width="100%" height="100%" viewbox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M19.651 20.431c.553-.115.883-.694.608-1.187-.606-1.088-1.56-2.043-2.78-2.772-1.572-.938-3.498-1.446-5.479-1.446-1.981 0-3.907.508-5.479 1.446-1.22.729-2.174 1.684-2.78 2.772-.275.493.055 1.072.607 1.187a37.503 37.503 0 0015.303 0z"></path><circle cx="12" cy="8.026" r="5"></circle></svg>
                </div>
                <p class="nav-item-text">Akun Saya</p>
            </a>
            <a class="nav-item" href="javascript:void(0)" onclick="window.open('<?php echo htmlspecialchars($isi_1_link_livechat_web); ?>', '_blank', 'noopener noreferrer')">
                <div class="nav-item-icon">
                    <svg width="100%" height="100%" viewBox="0 0 29 29" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="m23.352 6.981-.831.556.831-.556Zm0 12.62-.831-.555.831.556Zm-1.333 1.334.555.832-.555-.832Zm-5.102.813-.005-1a1 1 0 0 0-.995 1h1Zm0 .002.894.447a1 1 0 0 0 .106-.447h-1Zm-1.522 3.044.894.448-.894-.448Zm-1.79 0-.894.448.895-.448Zm-1.521-3.044h-1a1 1 0 0 0 .105.447l.895-.447Zm0-.002h1a1 1 0 0 0-.996-1l-.004 1Zm-5.102-.813-.556.832.556-.832Zm-1.334-1.333-.831.555.831-.555Zm0-12.62.832.555-.832-.556Zm1.334-1.334.555.831-.555-.831Zm15.037 0 .555-.832-.555.832Zm3.148 7.644c0-1.677.001-3.01-.107-4.072-.11-1.08-.34-1.993-.876-2.794L22.52 7.537c.279.418.455.963.55 1.885.095.939.096 2.152.096 3.87h2Zm-.983 6.865c.535-.8.766-1.713.876-2.793.108-1.064.107-2.396.107-4.072h-2c0 1.717-.001 2.93-.097 3.87-.094.921-.27 1.466-.55 1.884l1.664 1.111Zm-1.61 1.61a5.833 5.833 0 0 0 1.61-1.61l-1.663-1.11c-.28.418-.64.777-1.058 1.057l1.111 1.663Zm-5.653.981c1.299-.005 2.37-.03 3.262-.153.907-.125 1.692-.36 2.391-.828l-1.11-1.663c-.366.244-.83.41-1.555.51-.742.102-1.688.129-2.996.134l.008 2Zm.996-.998v-.002h-2v.002h2Zm-1.628 3.492 1.522-3.045-1.789-.894-1.522 3.044 1.789.895Zm-3.578 0c.737 1.474 2.841 1.474 3.578 0l-1.789-.895-1.789.895Zm-1.522-3.045 1.522 3.045 1.79-.895-1.523-3.044-1.789.894Zm-.105-.449v.002h2v-.002h-2Zm-4.658.019c.7.468 1.484.703 2.391.828.891.123 1.963.148 3.262.153l.009-2c-1.308-.005-2.255-.032-2.997-.134-.726-.1-1.189-.266-1.554-.51l-1.111 1.663Zm-1.61-1.61a5.834 5.834 0 0 0 1.61 1.61l1.111-1.663a3.832 3.832 0 0 1-1.057-1.058l-1.663 1.111Zm-.982-6.865c0 1.676-.002 3.008.106 4.072-.11 1.08-.341 1.992-.877 2.793l1.663-1.11c-.28-.419-.456-.964-.55-1.886-.095-.938-.096-2.152-.096-3.87h-2Zm.983-6.866C4.28 7.227 4.05 8.14 3.94 9.22c-.108 1.063-.107 2.395-.107 4.072h2c0-1.718.002-2.931.097-3.87.094-.922.27-1.467.55-1.885L4.817 6.426Zm1.609-1.61a5.833 5.833 0 0 0-1.61 1.61L6.48 7.537c.28-.419.639-.778 1.057-1.058L6.426 4.816Zm6.866-.983c-1.676 0-3.009 0-4.072.107-1.08.11-1.993.341-2.794.876L7.537 6.48c.418-.279.963-.455 1.885-.549.939-.095 2.152-.097 3.87-.097v-2Zm2.417 0h-2.417v2h2.417v-2Zm6.865.983c-.8-.535-1.714-.766-2.794-.876-1.063-.108-2.395-.107-4.072-.107v2c1.718 0 2.932.002 3.87.097.922.094 1.467.27 1.885.55l1.111-1.664Zm1.61 1.61a5.833 5.833 0 0 0-1.61-1.61l-1.11 1.663c.418.28.777.64 1.057 1.058l1.663-1.111Z"></path><circle cx="19.333" cy="13.292" r="1.208" fill="currentColor" stroke="currentColor" stroke-linecap="round"></circle><circle cx="14.5" cy="13.292" r="1.208" fill="currentColor" stroke="currentColor" stroke-linecap="round"></circle><circle cx="9.667" cy="13.292" r="1.208" fill="currentColor" stroke="currentColor" stroke-linecap="round"></circle></svg>
                </div>
                <p class="nav-item-text">Live Chat</p>
            </a>
        <?php else: ?>
            <a class="nav-item" href="<?php echo htmlspecialchars($alamat_website . 'home'); ?>">
                <div class="nav-item-icon">
                    <svg width="100%" height="100%" viewbox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M21.462 13.303c-.348.348-.81.54-1.302.54h-.302v6.004a2.157 2.157 0 0 1-2.155 2.155h-3.778v-5.294a.984.984 0 0 0-.984.983H11.06a.984.984 0 0 0-.984.983v5.294H6.297a2.158 2.158 0 0 1-2.155-2.155v-6.005h-.325c-.02 0-.038 0-.057-.002a1.843 1.843 0 0 1-1.225-3.137l.008-.009 8.155-8.155A1.83 1.83 0 0 1 12 2c.492 0 .954.193 1.302.54l8.16 8.16a1.844 1.844 0 0 1 0 2.604Z"></path></svg>
                </div>
                <p class="nav-item-text">Beranda</p>
            </a>
            <a class="nav-item" href="#" onclick="showRegisterModal(); return false;">
                <div class="nav-item-icon">
                    <svg width="100%" height="100%" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M19.651 20.431c.553-.115.883-.694.608-1.187-.606-1.088-1.56-2.043-2.78-2.772-1.572-.938-3.498-1.446-5.479-1.446-1.981 0-3.907.508-5.479 1.446-1.22.729-2.174 1.684-2.78 2.772-.275.493.055 1.072.607 1.187a37.503 37.503 0 0015.303 0z"></path><circle cx="12" cy="8.026" r="5"></circle></svg>
                </div>
                <p class="nav-item-text">Daftar</p>
            </a>
            <div class="main-btn-container">
                <button class="main-btn" id="mainButton">
                    <svg class="main-btn-icon" viewBox="0 0 24 24" fill="white" xmlns="http://www.w3.org/2000/svg"><path d="M15.621 2.669-3.69-.58c-3.22-.506-4.83-.76-5.88.139C5 3.126 5 4.756 5 8.016V11h5.92l-2.7-3.375 1.56-1.25 4 5 .5.625-.5.625-4 5-1.56-1.25L10.92 13H5v2.983c0 3.26 0 4.89 1.05 5.788 1.05.898 2.66.645 5.881.14l3.69-.58c1.613-.254 2.419-.38 2.899-.942.48-.561.48-1.377.48-3.01V6.62c0-1.632 0-2.449-.48-3.01-.48-.561-1.286-.688-2.899-.941Z"></path></svg>
                    <p class="main-btn-text">Menu</p>
                </button>
            </div>
            <a class="nav-item" href="#" onclick="showLoginModal(); return false;">
                <div class="nav-item-icon">
                    <svg width="100%" height="100%" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M13.791 19.062c-3.238.25-5.918-.215-7.464-1.605C5.166 16.275 5 15.02 5 12.001c0-3.018.167-4.274 1.328-5.456 1.546-1.39 4.225-1.856 7.464-1.605 3.239.25 5.918.715 7.464 2.105 1.162 1.182 1.33 2.437 1.33 5.455 0 3.019-.168 4.275-1.33 5.456-1.546 1.39-4.225 1.855-7.464 1.605Z"></path></svg>
                </div>
                <p class="nav-item-text">Login</p>
            </a>
            <a class="nav-item" href="javascript:void(0)" onclick="window.open('<?php echo htmlspecialchars($isi_1_link_livechat_web); ?>', '_blank', 'noopener noreferrer')">
                <div class="nav-item-icon">
                    <svg width="100%" height="100%" viewBox="0 0 29 29" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="m23.352 6.981-.831.556.831-.556Zm0 12.62-.831-.555.831.556Zm-1.333 1.334.555.832-.555-.832Zm-5.102.813-.005-1a1 1 0 0 0-.995 1h1Zm0 .002.894.447a1 1 0 0 0 .106-.447h-1Zm-1.522 3.044.894.448-.894-.448Zm-1.79 0-.894.448.895-.448Zm-1.521-3.044h-1a1 1 0 0 0 .105.447l.895-.447Zm0-.002h1a1 1 0 0 0-.996-1l-.004 1Zm-5.102-.813-.556.832.556-.832Zm-1.334-1.333-.831.555.831-.555Zm0-12.62.832.555-.832-.556Zm1.334-1.334.555.831-.555-.831Zm15.037 0 .555-.832-.555.832Zm3.148 7.644c0-1.677.001-3.01-.107-4.072-.11-1.08-.34-1.993-.876-2.794L22.52 7.537c.279.418.455.963.55 1.885.095.939.096 2.152.096 3.87h2Zm-.983 6.865c.535-.8.766-1.713.876-2.793.108-1.064.107-2.396.107-4.072h-2c0 1.717-.001 2.93-.097 3.87-.094.921-.27 1.466-.55 1.884l1.664 1.111Zm-1.61 1.61a5.833 5.833 0 0 0 1.61-1.61l-1.663-1.11c-.28.418-.64.777-1.058 1.057l1.111 1.663Zm-5.653.981c1.299-.005 2.37-.03 3.262-.153.907-.125 1.692-.36 2.391-.828l-1.11-1.663c-.366.244-.83.41-1.555.51-.742.102-1.688.129-2.996.134l.008 2Zm.996-.998v-.002h-2v.002h2Zm-1.628 3.492 1.522-3.045-1.789-.894-1.522 3.044 1.789.895Zm-3.578 0c.737 1.474 2.841 1.474 3.578 0l-1.789-.895-1.789.895Zm-1.522-3.045 1.522 3.045 1.79-.895-1.523-3.044-1.789.894Zm-.105-.449v.002h2v-.002h-2Zm-4.658.019c.7.468 1.484.703 2.391.828.891.123 1.963.148 3.262.153l.009-2c-1.308-.005-2.255-.032-2.997-.134-.726-.1-1.189-.266-1.554-.51l-1.111 1.663Zm-1.61-1.61a5.834 5.834 0 0 0 1.61 1.61l1.111-1.663a3.832 3.832 0 0 1-1.057-1.058l-1.663 1.111Zm-.982-6.865c0 1.676-.002 3.008.106 4.072-.11 1.08-.341 1.992-.877 2.793l1.663-1.11c-.28-.419-.456-.964-.55-1.886-.095-.938-.096-2.152-.096-3.87h-2Zm.983-6.866C4.28 7.227 4.05 8.14 3.94 9.22c-.108 1.063-.107 2.395-.107 4.072h2c0-1.718.002-2.931.097-3.87.094-.922.27-1.467.55-1.885L4.817 6.426Zm1.609-1.61a5.833 5.833 0 0 0-1.61 1.61L6.48 7.537c.28-.419.639-.778 1.057-1.058L6.426 4.816Zm6.866-.983c-1.676 0-3.009 0-4.072.107-1.08.11-1.993.341-2.794.876L7.537 6.48c.418-.279.963-.455 1.885-.549.939-.095 2.152-.097 3.87-.097v-2Zm2.417 0h-2.417v2h2.417v-2Zm6.865.983c-.8-.535-1.714-.766-2.794-.876-1.063-.108-2.395-.107-4.072-.107v2c1.718 0 2.932.002 3.87.097.922.094 1.467.27 1.885.55l1.111-1.664Zm1.61 1.61a5.833 5.833 0 0 0-1.61-1.61l-1.11 1.663c.418.28.777.64 1.057 1.058l1.663-1.111Z"></path><circle cx="19.333" cy="13.292" r="1.208" fill="currentColor" stroke="currentColor" stroke-linecap="round"></circle><circle cx="14.5" cy="13.292" r="1.208" fill="currentColor" stroke="currentColor" stroke-linecap="round"></circle><circle cx="9.667" cy="13.292" r="1.208" fill="currentColor" stroke="currentColor" stroke-linecap="round"></circle></svg>
                </div>
                <p class="nav-item-text">Live Chat</p>
            </a>
        <?php endif; ?>
    </nav>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const mainButton = document.getElementById('mainButton');
    const menuPopup = document.getElementById('menuPopup');
    const closeMenuButton = document.getElementById('closeMenuButton');
    const navItems = document.querySelectorAll('.nav-item');
    const searchBar = document.getElementById('searchBar');
    const menuContentWrapper = document.querySelector('.menu-content-wrapper');
    const gameListMenu = document.getElementById('game-list-menu');
    const isLoggedIn = <?php echo isset($id_anggota) ? 'true' : 'false'; ?>;
    const notLoggedInMessage = '<?php echo htmlspecialchars($isi_1_popup_teks_belum_login_web, ENT_QUOTES, 'UTF-8'); ?>';
    
    const loginModal = document.getElementById('loginModal');
    const registerModal = document.getElementById('registerModal');
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    const authLoadingIndicator = document.getElementById('authLoadingIndicator');
    const authLoadingMessage = document.getElementById('authLoadingMessage');
    const loginErrorMessage = document.getElementById('login-error-message');
    const registerErrorMessage = document.getElementById('register-error-message');
    
    const usernameInputRegister = document.getElementById('register-username');
    const passwordInputRegister = document.getElementById('register-password');
    const confirmPasswordInputRegister = document.getElementById('register-confirm-password');
    const togglePasswordRegister = document.getElementById('togglePasswordRegister');
    const toggleConfirmPasswordRegister = document.getElementById('toggleConfirmPasswordRegister');
    const bankSelectRegister = $('#register-bank');
    const quickBankButtons = document.querySelectorAll('.quick-bank-btn');
    const termsCheckboxRegister = document.getElementById('register_terms_checkbox');
    const inputsToCheckRegister = registerModal.querySelectorAll('.input-realtime-check');
    
    let searchDebounceTimer;
    let registerDebounceTimer;
    
    const currentPath = window.location.href.replace(/\/$/, ""); 
    const isHomepage = currentPath === '<?php echo htmlspecialchars($alamat_website); ?>' || currentPath === '<?php echo htmlspecialchars($alamat_website . 'home'); ?>';

    const pageLoadingIndicator = document.getElementById('pageFullLoadingIndicator');
    function showPageFullLoading() { 
        if (pageLoadingIndicator) { 
            pageFullLoadingIndicator.style.display = 'flex'; 
        } 
    }

    function hidePageFullLoading() {
        if (pageLoadingIndicator) {
            pageFullLoadingIndicator.style.display = 'none';
        }
    }
    
    function setActiveNavItem() {
        navItems.forEach(item => {
            item.classList.remove('active-item');
            const itemHref = item.href.replace(/\/$/, "");
            if (itemHref.length > '<?php echo htmlspecialchars($alamat_website); ?>'.length && currentPath.includes(itemHref)) {
                item.classList.add('active-item');
            }
        });
        
        if (isHomepage) {
            const homeItem = document.querySelector('.nav-item[href*="home"]');
            if (homeItem) { 
                homeItem.classList.add('active-item'); 
            }
        }
    }
    setActiveNavItem();

    if (mainButton) {
        mainButton.addEventListener('click', function(event) {
            event.stopPropagation();
            menuPopup.classList.toggle('show');
            mainButton.classList.toggle('active');
        });
    }

    if (closeMenuButton) {
        closeMenuButton.addEventListener('click', function(event) {
            event.stopPropagation();
            menuPopup.classList.remove('show');
            if (mainButton) { 
                mainButton.classList.remove('active'); 
            }
        });
    }
    
    document.addEventListener('click', function(event) {
        if (menuPopup.classList.contains('show')) {
            if (!menuPopup.contains(event.target) && !mainButton.contains(event.target)) {
                menuPopup.classList.remove('show');
                if (mainButton) { 
                    mainButton.classList.remove('active'); 
                }
            }
        }
    });

    if (menuPopup) { 
        menuPopup.addEventListener('click', function(event) { 
            event.stopPropagation(); 
        }); 
    }

    if (searchBar && menuContentWrapper) {
        searchBar.addEventListener('focus', function() { 
            menuContentWrapper.style.transform = 'translateY(-15vh)'; 
        });
        searchBar.addEventListener('blur', function() { 
            menuContentWrapper.style.transform = 'translateY(0)'; 
        });
    }
    
    function loadGames(searchTerm = '') { 
        if (!isLoggedIn) { 
            gameListMenu.innerHTML = `<p style="color:white; text-align:center;">${notLoggedInMessage}</p>`;
            gameListMenu.classList.add('show-game-list');
            return;
        }

        gameListMenu.innerHTML = `<p style="color:white; text-align:center;">Memuat...</p>`;
        
        fetch(`<?php echo htmlspecialchars($alamat_website); ?>ajax/search_games.php?search=${encodeURIComponent(searchTerm)}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                let html = '';
                if (data.status === 'success' && data.data && data.data.length > 0) {
                    data.data.forEach(game => {
                        const imageUrl = game.game_image_local || game.game_image_url_api;
                        html += `
                            <a href="#" class="game-list-item" 
                               data-game-source="${game.game_source}"
                               data-provider-code="${game.provider_code}" 
                               data-game-code="${game.game_code}"
                               data-game-type="${game.game_type}">
                                <div class="game-image-wrapper">
                                    <img src="${imageUrl}" alt="${game.game_name}">
                                </div>
                                <span>${game.game_name}</span>
                            </a>`;
                    });
                } else {
                    html = `<p style="color:white; text-align:center;">Tidak ada game yang ditemukan.</p>`;
                }
                
                gameListMenu.innerHTML = html;
                gameListMenu.classList.add('show-game-list');
            })
            .catch(error => {
                console.error('Error:', error);
                gameListMenu.innerHTML = `<p style="color:red; text-align:center;">Gagal memuat daftar game: ${error.message}</p>`;
                gameListMenu.classList.add('show-game-list');
            });
    }

    function launchGame(gameSource, providerCode, gameCode, gameType) {
        if (!isLoggedIn) {
            alert(notLoggedInMessage);
            return;
        }

        let gameUrl;
        if (gameSource === 'srg') {
            gameUrl = `<?php echo htmlspecialchars($alamat_website); ?>playgame/playGame.php?game_uid=${gameCode}&provider_code=${providerCode}&game_type=${gameType}`;
        } else if (gameSource === 'nexus') {
            gameUrl = `<?php echo htmlspecialchars($alamat_website); ?>playgame/Gameplay.php?game_uid=${gameCode}&provider_code=${providerCode}&game_type=${gameType}`;
        } else {
            alert('Sumber game tidak valid.');
            return;
        }

        showPageFullLoading();
        window.location.href = gameUrl;
    }
    
    searchBar.addEventListener('input', function() {
        clearTimeout(searchDebounceTimer);
        const searchTerm = this.value.trim();
        if (searchTerm.length > 0) { 
            searchDebounceTimer = setTimeout(() => { loadGames(searchTerm); }, 300); 
        } else {
            gameListMenu.innerHTML = '';
            gameListMenu.classList.remove('show-game-list');
        }
    });

    if (gameListMenu) { 
        gameListMenu.addEventListener('click', function(e) {
            e.preventDefault();
            const target = e.target.closest('.game-list-item');
            if (target) {
                const gameSource = target.getAttribute('data-game-source');
                const providerCode = target.getAttribute('data-provider-code');
                const gameCode = target.getAttribute('data-game-code');
                const gameType = target.getAttribute('data-game-type');
                launchGame(gameSource, providerCode, gameCode, gameType);
            }
        });
    }
    
    function disableNavItems(disable) {
        document.querySelectorAll('.bottom-navbar .nav-item').forEach(item => {
            if (item.href.includes('#')) return;
            item.style.pointerEvents = disable ? 'none' : 'auto';
            item.style.opacity = disable ? '0.5' : '1';
        });
    }

    function showAuthLoading(message) {
        authLoadingMessage.textContent = message;
        authLoadingIndicator.style.display = 'flex';
        disableNavItems(true);
    }
    function hideAuthLoading() {
        authLoadingIndicator.style.display = 'none';
        disableNavItems(false);
    }
    
    window.showLoginModal = function() {
        if (menuPopup.classList.contains('show')) { menuPopup.classList.remove('show'); }
        if (registerModal.classList.contains('show')) { closeRegisterModal(); }
        loginModal.classList.add('show');
        disableNavItems(true);
    };

    window.closeLoginModal = function() {
        loginModal.classList.remove('show');
        loginErrorMessage.textContent = '';
        loginForm.reset();
        disableNavItems(false);
    };

    window.showRegisterModal = function() {
        if (menuPopup.classList.contains('show')) { menuPopup.classList.remove('show'); }
        if (loginModal.classList.contains('show')) { closeLoginModal(); }
        registerModal.classList.add('show');
        disableNavItems(true);
    };

    window.closeRegisterModal = function() {
        registerModal.classList.remove('show');
        registerErrorMessage.textContent = '';
        registerForm.reset();
        disableNavItems(false);
    };

    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        loginErrorMessage.textContent = '';
        const form = e.target;
        const formData = new FormData(form);
        showAuthLoading('Login...');
        fetch('<?php echo htmlspecialchars($alamat_website . 'process_login.php'); ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                authLoadingMessage.textContent = data.message;
                setTimeout(() => {
                    hideAuthLoading();
                    closeLoginModal();
                    window.location.href = data.redirect;
                }, 1500);
            } else {
                authLoadingMessage.textContent = data.message;
                setTimeout(() => {
                    hideAuthLoading();
                    loginErrorMessage.textContent = data.message;
                    document.getElementById('loginSubmitButton').disabled = false;
                }, 2000);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            authLoadingMessage.textContent = 'Terjadi kesalahan jaringan.';
            setTimeout(hideAuthLoading, 2000);
            loginErrorMessage.textContent = 'Terjadi kesalahan jaringan.';
        });
    });

    registerForm.addEventListener('submit', function(e) {
        e.preventDefault();
        registerErrorMessage.textContent = '';
        
        const form = e.target;
        const formData = new FormData(form);
        const password = formData.get('kata_sandi_anggota');
        const confirm_password = formData.get('konfirmasi_kata_sandi_anggota');
        
        if (password !== confirm_password) {
            registerErrorMessage.textContent = 'Password dan Konfirmasi Password tidak cocok.';
            return;
        }
        if (!termsCheckboxRegister.checked) {
            registerErrorMessage.textContent = 'Anda harus menyetujui syarat dan ketentuan.';
            return;
        }

        showAuthLoading('Mendaftar...');

        fetch('<?php echo htmlspecialchars($alamat_website . 'process_register.php'); ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                authLoadingMessage.textContent = data.message;
                setTimeout(() => {
                    hideAuthLoading();
                    closeRegisterModal();
                    window.location.href = data.redirect;
                }, 1500);
            } else {
                authLoadingMessage.textContent = data.message;
                setTimeout(() => {
                    hideAuthLoading();
                    registerErrorMessage.textContent = data.message;
                    document.getElementById('registerSubmitButton').disabled = false;
                }, 2000);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            authLoadingMessage.textContent = 'Terjadi kesalahan jaringan.';
            setTimeout(hideAuthLoading, 2000);
            registerErrorMessage.textContent = 'Terjadi kesalahan jaringan.';
        });
    });

    const iconCheckSvg = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="green" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>`;
    const iconCrossSvg = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="red" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293 5.354 4.646z"/></svg>`;
    const iconLoadingSvg = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="orange" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="animate-spin"><path d="M21 12a9 9 0 1 1-6.219-8.56"></path></svg>`;

    function setFieldStatus(fieldId, status, message = '', iconSvg = '') {
        const messageElement = document.getElementById(fieldId + '_status_message');
        const iconElement = document.getElementById(fieldId + '_icon');
        if (messageElement) { 
            messageElement.textContent = message; 
            messageElement.className = 'status-message'; 
        }
        if (iconElement) { 
            iconElement.innerHTML = iconSvg; 
        }

        switch (status) {
            case 'error': case 'exists': case 'invalid_length': case 'invalid_format':
                if (messageElement) messageElement.classList.add('text-error');
                if (iconElement) iconElement.innerHTML = iconCrossSvg; 
                break;
            case 'success': case 'available':
                if (messageElement) messageElement.classList.add('text-success');
                if (iconElement) iconElement.innerHTML = iconCheckSvg; 
                break;
            case 'checking':
                if (messageElement) messageElement.classList.add('text-checking');
                if (iconElement) iconElement.innerHTML = iconLoadingSvg; 
                break;
            default: 
                break;
        }
    }

    inputsToCheckRegister.forEach(inputElement => {
        const fieldName = inputElement.dataset.fieldName;
        inputElement.addEventListener('input', function() {
            const fieldValue = this.value.trim();
            clearTimeout(registerDebounceTimer);
            setFieldStatus('register_' + fieldName, 'reset', '');

            if (fieldName === 'username') {
                if (fieldValue.includes(' ')) {
                    setFieldStatus('register_' + fieldName, 'error', 'Username tidak boleh mengandung spasi.'); 
                    return;
                }
                if (!/^[a-zA-Z0-9]+$/.test(fieldValue) && fieldValue.length > 0) {
                     setFieldStatus('register_' + fieldName, 'error', 'Username hanya boleh huruf dan angka.'); 
                     return;
                }
            }
            if (fieldValue === '' && fieldName !== 'email') return;
            if (fieldValue === '' && fieldName === 'email') { 
                setFieldStatus('register_email', 'neutral', 'Email (opsional)', ''); 
                return; 
            }

            setFieldStatus('register_' + fieldName, 'checking', 'Mengecek...', iconLoadingSvg);
            registerDebounceTimer = setTimeout(() => {
                const formData = new FormData();
                formData.append('field_name', fieldName);
                formData.append('field_value', fieldValue);
                fetch('check_availability.php', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.field === fieldName) {
                        setFieldStatus('register_' + fieldName, data.status, data.message, data.status === 'available' ? iconCheckSvg : iconCrossSvg);
                    }
                })
                .catch(error => setFieldStatus('register_' + fieldName, 'error', 'Error pengecekan.', iconCrossSvg));
            }, 500);
        });
    });

    if(togglePasswordRegister) togglePasswordRegister.addEventListener('click', function() {
        const type = passwordInputRegister.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInputRegister.setAttribute('type', type);
        this.classList.toggle('bi-eye');
        this.classList.toggle('bi-eye-slash');
    });

    if(toggleConfirmPasswordRegister) toggleConfirmPasswordRegister.addEventListener('click', function() {
        const type = confirmPasswordInputRegister.getAttribute('type') === 'password' ? 'text' : 'password';
        confirmPasswordInputRegister.setAttribute('type', type);
        this.classList.toggle('bi-eye');
        this.classList.toggle('bi-eye-slash');
    });
    
    if(quickBankButtons) quickBankButtons.forEach(button => {
        button.addEventListener('click', function() {
            const bankKey = this.getAttribute('data-bank-key');
            bankSelectRegister.val(bankKey).trigger('change');
        });
    });

    function validatePasswordConfirmationRegister() {
        const passVal = passwordInputRegister.value;
        const confirmPassVal = confirmPasswordInputRegister.value;
        const msgElement = document.getElementById('register_confirm_password_status_message');
        if (passVal !== confirmPassVal) {
            msgElement.textContent = 'Password tidak cocok';
            msgElement.classList.add('text-error');
        } else {
            msgElement.textContent = 'Password cocok';
            msgElement.classList.add('text-success');
        }
        if (confirmPassVal === '') {
            msgElement.textContent = '';
            msgElement.classList.remove('text-error', 'text-success');
        }
    }
    if(passwordInputRegister) passwordInputRegister.addEventListener('input', validatePasswordConfirmationRegister);
    if(confirmPasswordInputRegister) confirmPasswordInputRegister.addEventListener('input', validatePasswordConfirmationRegister);
});
</script>
