<?php include_once 'header.php'; ?>
<section class="lg:bg-background-tertiary min-h-content">
	<div class="container mx-auto md:py-4 lg:py-8 lg:px-3">
		<div class="bg-background-default rounded-2xl p-4 lg:px-10 mx-auto md:w-4/5 lg:w-3/5">
			<section class="flex justify-center rounded-full bg-separator w-full lg:w-3/5 mx-auto overflow-hidden lg:mt-3 mb-6">
				<a class="w-1/2 justify-center opacity-70" href="<?php echo $alamat_website . 'auth-register'; ?>">Register</a>
				<a class="w-1/2 justify-center bg-inverse rounded-full text-primary font-semibold py-2" href="<?php echo $alamat_website . 'auth-login'; ?>">Login</a>
			</section>

			<form id="loginForm">
				<div class="relative mt-4 lg:mt-5 rounded-xl group border lg:bg-background-default border-caption focus-within:border-primary focus-within:ring-1">
					<div class="relative flex items-center top-0 pt-3 px-3 ">
						<svg width="20" height="20" viewbox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M19.727 20.447c-.455-1.276-1.46-2.403-2.857-3.207C15.473 16.436 13.761 16 12 16c-1.761 0-3.473.436-4.87 1.24-1.397.804-2.402 1.931-2.857 3.207" stroke="var(--primary)" stroke-width="2" stroke-linecap="round"></path>
							<circle cx="12" cy="8" r="4" stroke="var(--primary)" stroke-width="2" stroke-linecap="round"></circle>
						</svg>
						<label class="text-xs opacity-70 pl-2 bg-background-default rounded-full ">Username</label>
					</div>
					<div class="relative">
						<input id="username" placeholder="Enter your username" label="Username" name="nama_pengguna_anggota" class="px-3 pt-2 pb-3 focus:border-transparent focus:ring-0 text-sm lg:text-base w-full rounded-lg border bg-transparent border-transparent focus:outline-none" type="text" value="" required>
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
						<input id="password" placeholder="Enter your password" name="kata_sandi_anggota" label="Password" class="px-3 pt-2 pb-3 focus:border-transparent focus:ring-0 text-sm lg:text-base w-full rounded-lg border bg-transparent border-transparent focus:outline-none" type="password" value="" required>
                        <span class="absolute px-2 flex items-center rounded-md opacity-70 cursor-pointer right-[1px] top-[1px] bottom-[1px]"><span>
                            <svg width="20" height="20" viewbox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" size="20">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M15.92 12.799a4 4 0 0 0-4.719-4.719l.923.923a3 3 0 0 1 2.873 2.873l.923.923Zm-6.527-2.285a3 3 0 0 0 4.093 4.093l.726.726a4 4 0 0 1-5.545-5.545l.726.726Z" fill="#fff"></path>
                                <path fill-rule="evenodd" clip-rule="evenodd" d="m16.154 17.275-.735-.734c-1.064.579-2.22.959-3.419.959-1.672 0-3.262-.74-4.633-1.726-1.367-.984-2.474-2.182-3.17-3.026-.423-.515-.467-.604-.467-.748 0-.143.044-.233.468-.748.67-.812 1.72-1.953 3.018-2.915L6.5 7.623C5.17 8.63 4.104 9.793 3.426 10.616l-.059.072c-.33.399-.637.77-.637 1.312s.307.913.637.1312l.059.072c.725.88 1.894 2.149 3.357 3.201C8.243 17.635 10.036 18.5 12 18.5c1.51 0 2.92-.511 4.154-1.225ZM9.19 6.07c.88-.35 1.824-.569 2.81-.569 1.964 0 3.758.865 5.217 1.915 1.463 1.052 2.632 2.321 3.357 3.201l.059.072c.33.399.637.77.637 1.312s-.307.913-.637.1312l-.059.072a19.988 19.988 0 0 1-1.983 2.086l-.708-.708a18.943 18.943 0 0 0 1.92-2.014c.424-.515.467-.604.467-.748 0-.143-.043-.233-.468-.748-.695-.844-1.802-2.042-3.17-3.026C15.263 7.24 13.673 6.5 12 6.5c-.694 0-1.375.128-2.031.348l-.78-.78Z" fill="#fff"></path>
                                <path d="m5 2 16 16" stroke="#fff"></path>
                            </svg>
                        </span>
					</div>
				</div>
				<div class="flex justify-center mt-6 mb-4">
					<button type="submit" id="loginSubmitBtn" aria-label="Button Login Form" aria-labelledby="Button Login Form" class="bg-primary lg:hover:brightness-95 rounded-xl text-sm lg:text-base font-semibold w-full lg:w-1/2 min-h-[44px] justify-center py-3 text-white">Login</button>
				</div>
				<p class="mt-10 mb-8 text-sm text-center">
					Don't have an account? <a class="text-primary inline-block" href="<?php echo $alamat_website . 'auth-register'; ?>">Register</a>
				</p>
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
    /* CSS for Global Loading Indicator */
    #globalLoadingIndicator {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.65);
        z-index: 99999;
        display: none; /* Hidden by default */
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center; /* Untuk teks di tengah */
    }
    #globalLoadingIndicator .spinner {
        border: 8px solid #4A5568; /* Light grey */
        border-top: 8px solid #FCD34D; /* Yellow/Orange */
        border-radius: 50%;
        width: 60px;
        height: 60px;
        animation: spinGlobalLoader 1s linear infinite;
        margin-bottom: 15px; /* Jarak antara spinner dan teks */
    }
    #globalLoadingIndicator p {
        color: white;
        font-size: 1.1em;
    }
    #globalLoadingIndicator.success .spinner {
        animation: none; /* Stop animation on success */
        border-top-color: #2ecc71; /* Green color for success */
        border-left-color: #2ecc71; /* Make it a full circle if needed */
        border-right-color: #2ecc71;
        border-bottom-color: #2ecc71;
    }
    @keyframes spinGlobalLoader {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    /* Style untuk pesan error inline */
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
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const loginSubmitBtn = document.getElementById('loginSubmitBtn');
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');
    const formResponseMessage = document.getElementById('formResponseMessage'); // Elemen untuk pesan form

    // Global Loading Indicator elements
    const globalLoadingIndicator = document.getElementById('globalLoadingIndicator');
    const loadingMessageElement = document.getElementById('loadingMessage');
    const loadingSpinnerElement = globalLoadingIndicator.querySelector('.spinner');

    // Functions for Global Loading Indicator
    function showGlobalLoading(message = 'Memproses...', resetSpinner = true) {
        loadingMessageElement.textContent = message;
        globalLoadingIndicator.style.display = 'flex';
        if (resetSpinner) {
            loadingSpinnerElement.classList.remove('success'); 
            loadingSpinnerElement.style.borderColor = '#4A5568'; // Reset border color
            loadingSpinnerElement.style.borderTopColor = '#FCD34D'; // Reset border-top color
            loadingSpinnerElement.classList.add('animate-spin'); // Ensure spinner animation
        }
    }

    function hideGlobalLoading() {
        globalLoadingIndicator.style.display = 'none';
        loadingSpinnerElement.classList.remove('animate-spin'); 
    }
    
    // Function to update loading state for success/error
    function updateLoadingState(success, message) {
        if (success) {
            loadingMessageElement.textContent = message;
            loadingSpinnerElement.classList.remove('animate-spin'); // Stop animation
            loadingSpinnerElement.classList.add('success'); // Change color/appearance for success
        } else {
            loadingMessageElement.textContent = message;
            loadingSpinnerElement.classList.remove('animate-spin'); // Stop current animation for reset
            loadingSpinnerElement.style.borderColor = '#e74c3c'; // Red for error
            loadingSpinnerElement.style.borderTopColor = '#e74c3c'; 
            // Optionally, you might want to show a small 'X' inside the circle or similar for error state
        }
    }

    // Fungsi untuk menampilkan pesan langsung di bawah form (jika diperlukan)
    function showFormMessage(message, type) {
        if (!formResponseMessage) return;
        formResponseMessage.textContent = message;
        formResponseMessage.className = 'form-message ' + type;
        formResponseMessage.style.display = 'block';
    }

    function hideFormMessage() {
        if (formResponseMessage) formResponseMessage.style.display = 'none';
    }

    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();

        hideFormMessage();

        if (!usernameInput.value.trim() || !passwordInput.value.trim()) {
            showFormMessage('Username dan password tidak boleh kosong.', 'error');
            return;
        }

        loginSubmitBtn.disabled = true; // Nonaktifkan tombol login
        showGlobalLoading('Memproses...'); // Tampilkan loading overlay

        const formData = new FormData(loginForm);

        fetch('<?php echo $alamat_website . 'process_login'; ?>', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateLoadingState(true, 'Login Berhasil!'); // Update teks dan state spinner untuk sukses
                showFormMessage(data.message, 'success'); // Opsional: tampilkan pesan di bawah form juga
                
                // Redirect setelah delay
                setTimeout(() => {
                    hideGlobalLoading(); // Sembunyikan loading sebelum redirect penuh
                    window.location.href = data.redirect;
                }, 1500); 
            } else {
                updateLoadingState(false, data.message); // Update teks dan state spinner untuk error
                showFormMessage(data.message, 'error');
                
                // Sembunyikan loading overlay setelah beberapa saat untuk user melihat pesan error
                setTimeout(() => {
                    hideGlobalLoading();
                }, 2000); 
            }
        })
        .catch(error => {
            console.error('Error during login AJAX:', error);
            updateLoadingState(false, 'Terjadi kesalahan jaringan atau server. Silakan coba lagi.'); // Update teks dan state spinner untuk error
            showFormMessage('Terjadi kesalahan jaringan atau server. Silakan coba lagi.', 'error');
            
            setTimeout(() => {
                hideGlobalLoading();
            }, 2000);
        })
        .finally(() => {
            loginSubmitBtn.disabled = false; // Aktifkan kembali tombol login
        });
    });
});
</script>