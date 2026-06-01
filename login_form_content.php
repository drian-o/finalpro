<?php
// Pastikan variabel $alamat_website sudah tersedia saat file ini di-include.
// Jika tidak, Anda mungkin perlu menambahkan logic untuk mendefinisikannya di sini
// atau di file induk yang meng-include file ini.
$alamat_website = $alamat_website ?? '/'; // Default ke root jika tidak didefinisikan
?>

<h2 class="text-center text-xl font-bold mb-6">Login</h2>
<form method="post" action="<?php echo htmlspecialchars($alamat_website . 'process_login'); ?>">
    <div class="relative mt-4 lg:mt-5 rounded-xl group border lg:bg-background-default border-caption focus-within:border-primary focus-within:ring-1">
        <div class="relative flex items-center top-0 pt-3 px-3 ">
            <svg width="20" height="20" viewbox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M19.727 20.447c-.455-1.276-1.46-2.403-2.857-3.207C15.473 16.436 13.761 16 12 16c-1.761 0-3.473.436-4.87 1.24-1.397.804-2.402 1.931-2.857 3.207" stroke="var(--primary)" stroke-width="2" stroke-linecap="round"></path>
                <circle cx="12" cy="8" r="4" stroke="var(--primary)" stroke-width="2" stroke-linecap="round"></circle>
            </svg>
            <label class="text-xs opacity-70 pl-2 bg-background-default rounded-full ">Username</label>
        </div>
        <div class="relative">
            <input id="login-username" placeholder="Enter your username" label="Username" name="nama_pengguna_anggota" class="px-3 pt-2 pb-3 focus:border-transparent focus:ring-0 text-sm lg:text-base w-full rounded-lg border bg-transparent border-transparent focus:outline-none" type="text" value="">
        </div>
    </div>
    <div class="relative mt-4 lg:mt-5 rounded-xl group border lg:bg-background-default border-caption focus-within:border-primary focus-within:ring-1">
        <div class="relative flex items-center top-0 pt-3 px-3 ">
            <svg width="20" height="20" viewbox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M4 13c0-1.886 0-2.828.586-3.414C5.172 9 6.114 9 8 9h8c1.886 0 2.828 0 3.414.586C20 10.172 20 11.114 20 13v2c0 2.828 0 4.243-.879 5.121C18.243 21 16.828 21 14 21h-4c-2.828 0-4.243 0-5.121-.879C4 19.243 4 17.828 4 15v-2Z" stroke="var(--primary)" stroke-width="2"></path>
                <path d="M16 8V7a4 4 0 0 0-4-4v0a4 4 0 0 0-4 4v1" stroke="var(--primary)" stroke-width="2" stroke-linecap="round"></path>
                <circle cx="12" cy="15" r="2" fill="var(--primary)"></circle>
            </svg>
            <label class="text-xs opacity-70 pl-2 bg-background-default rounded-full ">Password</label>
        </div>
        <div class="relative">
            <input id="login-password" placeholder="Enter your password" name="kata_sandi_anggota" label="Password" class="px-3 pt-2 pb-3 focus:border-transparent focus:ring-0 text-sm lg:text-base w-full rounded-lg border bg-transparent border-transparent focus:outline-none" type="password" value="">
            <span class="absolute px-2 flex items-center rounded-md opacity-70 cursor-pointer right-[1px] top-[1px] bottom-[1px]" id="loginTogglePasswordVisibility">
                <span>
                    <svg width="20" height="20" viewbox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" size="20">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M15.92 12.799a4 4 0 0 0-4.719-4.719l.923.923a3 3 0 0 1 2.873 2.873l.923.923Zm-6.527-2.285a3 3 0 0 0 4.093 4.093l.726.726a4 4 0 0 1-5.545-5.545l.726.726Z" fill="#fff"></path>
                        <path fill-rule="evenodd" clip-rule="evenodd" d="m16.154 17.275-.735-.734c-1.064.579-2.22.959-3.419.959-1.672 0-3.262-.74-4.633-1.726-1.367-.984-2.474-2.182-3.17-3.026-.423-.515-.467-.604-.467-.748 0-.143.044-.233.468-.748.67-.812 1.72-1.953 3.018-2.915L6.5 7.623C5.17 8.63 4.104 9.793 3.426 10.616l-.059.072c-.33.399-.637.77-.637 1.312s.307.913.637 1.312l.059.072c.725.88 1.894 2.149 3.357 3.201C8.243 17.635 10.036 18.5 12 18.5c1.51 0 2.92-.511 4.154-1.225ZM9.19 6.07c.88-.35 1.824-.569 2.81-.569 1.964 0 3.758.865 5.217 1.915 1.463 1.052 2.632 2.321 3.357 3.201l.059.072c.33.399.637.77.637 1.312s-.307.913-.637 1.312l-.059.072a19.988 19.988 0 0 1-1.983 2.086l-.708-.708a18.943 18.943 0 0 0 1.92-2.014c.424-.515.467-.604.467-.748 0-.143-.043-.233-.468-.748-.695-.844-1.802-2.042-3.17-3.026C15.263 7.24 13.673 6.5 12 6.5c-.694 0-1.375.128-2.031.348l-.78-.78Z" fill="#fff"></path>
                        <path d="m5 2 16 16" stroke="#fff"></path>
                    </svg>
                </span>
            </span>
        </div>
    </div>
    <div class="flex justify-center mt-6 mb-4">
        <button type="submit" aria-label="Button Login Form" aria-labelledby="Button Login Form" class="bg-primary lg:hover:brightness-95 rounded-xl text-sm lg:text-base font-semibold w-full lg:w-1/2 min-h-[44px] justify-center py-3 text-white">Login</button>
    </div>
    <p class="mt-10 mb-8 text-sm text-center">
        Don't have an account? <a class="text-primary inline-block" href="<?php echo htmlspecialchars($alamat_website . 'auth-register'); ?>">Register</a>
    </p>
</form>

<script>
// Fungsi toggle password visibility untuk form login (modal dan halaman)
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('login-password');
    const togglePasswordVisibility = document.getElementById('loginTogglePasswordVisibility');

    if (passwordInput && togglePasswordVisibility) {
        togglePasswordVisibility.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            const iconPath = this.querySelector('path:last-child'); // Path untuk ikon mata tertutup/terbuka
            if (type === 'text') {
                iconPath.setAttribute('d', 'M12 4.5c4.08 0 7.425 3.098 8.825 7.5-.964 2.91-2.923 5.09-5.148 6.486A11.968 11.968 0 0 1 12 19.5c-4.08 0-7.425-3.098-8.825-7.5.964-2.91 2.923-5.09 5.148-6.486A11.968 11.968 0 0 1 12 4.5Z'); // Ikon mata terbuka (contoh)
            } else {
                iconPath.setAttribute('d', 'm5 2 16 16'); // Ikon mata tertutup (contoh: garis silang)
            }
        });
    }
});
</script>