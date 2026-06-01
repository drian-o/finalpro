<?php
// Pastikan $koneksi tersedia secara global.
// Variabel $alamat_website juga diasumsikan tersedia.

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) :

    $bonus_balance_anggota = 0.00; // Default value
    $saldo_anggota_display = $_SESSION['saldo_anggota'] ?? 0.00;
    $nama_pengguna_display = htmlspecialchars($_SESSION['nama_pengguna_anggota'] ?? 'User');

    // Fetch current bonus_balance from DB for display
    if (isset($_SESSION['id_anggota']) && isset($koneksi)) {
        $id_anggota_session = $_SESSION['id_anggota'];
        // Prepare statement to prevent SQL injection
        $stmt_get_bonus_display = $koneksi->prepare("SELECT bonus_balance FROM anggota WHERE id_anggota = ?");
        if ($stmt_get_bonus_display) {
            $stmt_get_bonus_display->bind_param("i", $id_anggota_session);
            $stmt_get_bonus_display->execute();
            $result_bonus_display = $stmt_get_bonus_display->get_result();
            if ($data_bonus_user = $result_bonus_display->fetch_assoc()) {
                $bonus_balance_anggota = (float)$data_bonus_user['bonus_balance'];
            }
            $stmt_get_bonus_display->close();
        }
    }
?>
    <section class="container mx-auto pb-5 lg:pb-5 flex flex-wrap">
        <div class="w-full px-3 mt-3 order-2">
            <div class="bg-background-secondary h-full lg:h-auto rounded-xl pt-4 lg:py-4 overflow-hidden relative flex flex-wrap">
                <div class="w-7/12 lg:w-[45%] lg:px-8 items-center lg:pt-3 flex flex-wrap px-4">
                    <article class="w-full flex items-center mb-1 lg:mb-3">
                        <svg width="24" height="24" viewbox="0 0 24 24" fill="var(--primary)" xmlns="http://www.w3.org/2000/svg" size="24">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M2.879 3.879C2 4.757 2 6.172 2 9v6c0 2.828 0 4.243.879 5.121C3.757 21 5.172 21 8 21h10c.93 0 1.395 0 1.776-.102a3 3 0 0 0 2.122-2.122C22 18.395 22 17.93 22 17h-6a3 3 0 1 1 0-6h6V9c0-2.828 0-4.243-.879-5.121C20.243 3 18.828 3 16 3H8c-2.828 0-4.243 0-5.121.879ZM7 7a1 1 0 0 0 0 2h3a1 1 0 1 0 0-2H7Z" fill="var(--primary)"></path>
                            <path d="M17 14h-1" stroke="var(--primary)" stroke-width="2" stroke-linecap="round"></path>
                        </svg>
                        <span class="text-xs lg:text-sm text-caption px-2">Account Balance</span>
                        <button>
                            <svg width="20" height="20" viewbox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="12" cy="12" r="3.5" stroke="var(--caption)"></circle>
                                <path d="M20.188 10.934c.388.472.582.707.582 1.066 0 .359-.194.594-.582 1.066C18.768 14.79 15.636 18 12 18c-3.636 0-6.768-3.21-8.188-4.934-.388-.472-.582-.707-.582-1.066 0-.359.194.594.582-1.066C5.232 9.21 8.364 6 12 6c3.636 0 6.768 3.21 8.188 4.934Z" stroke="var(--caption)"></path>
                            </svg>
                        </button>
                    </article>
                    <div class="w-full flex lg:gap-x-5">
                        <div class="lg:w-2/3 flex items-center">
                            <section class="w-full flex items-center h-7">
                                <span id="accountBalanceDisplay" class="text-sm lg:text-xl font-semibold">IDR&nbsp;<?php echo number_format($saldo_anggota_display, 0, ',', '.'); ?></span>
                                <button id="refreshBalanceBtn" class="rounded-full bg-background-default cursor-pointer w-7 h-7 ml-2 items-center justify-center flex">
                                    <svg width="20" height="20" viewbox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="m10 19-.707-.707-.707.707.707.707L10 19Zm3.293-4.707-4 4 1.414 1.414 4-4-1.414-1.414Zm-4 5.414 4 4 1.414-1.414-4-4-1.414 1.414Z" fill="var(--caption)"></path>
                                        <path d="M5.938 15.5A7 7 0 1 1 12 19" stroke="var(--caption)" stroke-width="2" stroke-linecap="round"></path>
                                    </svg>
                                </button>
                            </section>
                        </div>
                    </div>
                </div>
                <div class="w-5/12 lg:w-[20%] lg:pt-3 px-4 lg:px-8 border-l border-separator lg:order-last">
                    <div class="w-full flex items-center mb-2">
                        <img alt="VIP Icon" loading="lazy" width="0" height="0" decoding="async" data-nimg="1" class="w-6 lg:w-8 h-auto" src="assets/img/pemainbaru1.png" style="color: transparent;">
                        <a class="flex items-center ml-2 lg:ml-4 lg:font-medium">
                            <span class="text-xs lg:text-sm lg:mr-3 text-caption border-b border-transparent hover:lg:border-primary transition-all duration-300 ease-out"><?php echo $nama_pengguna_display; ?></span>
                            <svg width="18" height="18" viewbox="0 0 24 24" fill="var(--caption)" xmlns="http://www.w3.org/2000/svg" size="18">
                                <path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="var(--caption)"></path>
                            </svg>
                        </a>
                    </div>
                    <p class="font-semibold mt-1 lg:mt-4">Pemain Baru</p>
                </div>
                <section class="flex flex-wrap w-full lg:w-[35%] py-3 lg:pt-3 lg:pb-0 px-4 lg:px-8 mt-4 mb-2 lg:my-0 border-l border-transparent lg:border-l-separator border-t border-t-separator lg:border-t-transparent">
                    <article class="w-full flex flex-wrap justify-between lg:items-center">
                        <div class="flex items-center lg:mb-3">
                            <svg width="24" height="24" viewbox="0 0 24 24" fill="var(--primary)" xmlns="http://www.w3.org/2000/svg" size="24">
                                <path d="M5 12H4v8a2 2 0 0 0 2 2h5V12H5Zm13 0h-5v10h5a2 2 0 0 0 2-2v-8h-2Zm.791-5c.147-.486.217-.992.209-1.5C19 3.57 17.43 2 15.5 2c-1.622 0-2.705 1.482-3.404 3.085C11.407 3.57 10.269 2 8.5 2 6.57 2 5 3.57 5 5.5c0 .596.079 1.089.209 1.5H2v4h9V9h2v2h9V7h-3.209ZM7 5.5C7 4.673 7.673 4 8.5 4c.888 0 1.714 1.525 2.198 3H8c-.374 0-1 0-1-1.5ZM15.5 4c.827 0 1.5.673 1.5 1.5C17 7 16.374 7 16 7h-2.477c.51-1.576 1.251-3 1.977-3Z" fill="var(--primary)"></path>
                            </svg>
                            <p class="text-xs lg:text-sm text-caption pl-2">Bonus Balance</p>
                        </div>
                        <div class="flex w-full">
                            <div class="w-2/3 lg:w-[70%] flex flex-wrap">
                                <p class="flex items-center text-sm lg:text-xl mt-1 lg:mt-0 w-full" id="bonusBalanceDisplay">IDR&nbsp;<?php echo number_format($bonus_balance_anggota, 0, ',', '.'); ?></p>
                            </div>
                            <div class="w-1/3 lg:w-[30%] flex items-center justify-end">
                                <button id="claimBonusBtn" class="px-5 py-1 lg:py-2 text-sm lg:text-base justify-center font-semibold rounded-lg w-full h-8 lg:h-auto transition-all duration-200 ease-in-out <?php if ($bonus_balance_anggota > 0) echo 'border border-primary text-primary hover:lg:brightness-[0.9]'; else echo 'border border-gray-400 text-gray-400 opacity-50 cursor-not-allowed'; ?>" <?php if ($bonus_balance_anggota <= 0) echo 'disabled'; ?>>Claim</button>
                            </div>
                        </div>
                    </article>
                </section>

                <section id="claimBonusModal" class="fixed z-[9999] flex items-center justify-center overflow-hidden transition duration-300 ease-in-out w-0">
                    <div class="bg-background-secondary rounded-lg shadow-xl w-11/12 max-w-md mx-auto">
                        <div class="flex justify-between items-center px-4 lg:px-7 pt-5 mb-3">
                            <h3 class="text-lg font-semibold text-text-default">Confirm Claim</h3>
                            <button id="modalTopCloseButton" class="text-gray-400 hover:text-gray-600">
                                <svg width="24" height="24" viewbox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg" size="24">
                                    <path d="M18 6 6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </button>
                        </div>
                        <div class="max-h-[calc(100vh-200px)] lg:max-w-2xl px-4 lg:px-7 pb-3 overflow-auto">
                            <p class="mt-4 font-light text-center text-text-default" id="claimModalText">
                                Claim <span class="font-medium">-</span> bonus balance to main balance?
                            </p>
                        </div>
                        <div class="flex justify-center px-4 lg:px-20 pt-4 pb-8 gap-3">
                            <button id="confirmClaimBtn" class="bg-primary justify-center text-sm text-white w-24 py-2 rounded-lg transition-all duration-200 ease-in-out hover:lg:brightness-90 flex items-center justify-center">Yes</button>
                            <button id="cancelClaimBtn" class="text-sm justify-center w-24 py-2 rounded-lg border border-primary text-primary transition-all duration-200 ease-in-out hover:lg:brightness-75">No</button>
                        </div>
                    </div>
                </section>

            </div>
        </div>
    </section>
<?php else : ?>
    <section class="container mx-auto pb-3 lg:pb-20 flex flex-wrap">
        <?php // Konten untuk pengguna yang belum login, jika ada ?>
    </section>
<?php endif; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const baseUrl = '<?php echo rtrim($alamat_website, '/'); ?>';

        // --- AJAX untuk Refresh Saldo ---
        const refreshButton = document.getElementById('refreshBalanceBtn');
        const balanceDisplay = document.getElementById('accountBalanceDisplay');

        if (refreshButton && balanceDisplay) {
            const originalButtonIconHTML = refreshButton.innerHTML;
            const loadingIconSVG_Refresh = `
                <svg class="animate-spin h-5 w-5 text-[var(--caption)]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>`;

            refreshButton.addEventListener('click', function() {
                refreshButton.innerHTML = loadingIconSVG_Refresh;
                refreshButton.disabled = true;

                fetch(`${baseUrl}/update_saldo.php`, {
                    method: 'GET',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => {
                            throw new Error(text || 'Gagal menghubungi server. Status: ' + response.status);
                        });
                    }
                    return response.text();
                })
                .then(data => {
                    if (data.trim().startsWith("IDR")) {
                        balanceDisplay.innerHTML = data.trim();
                    } else {
                        console.error('Pesan dari server (update_saldo.php):', data);
                        alert('Gagal memperbarui saldo: ' + data.trim());
                    }
                })
                .catch(error => {
                    console.error('Error saat melakukan fetch saldo:', error);
                    alert('Terjadi kesalahan: ' + error.message);
                })
                .finally(() => {
                    refreshButton.innerHTML = originalButtonIconHTML;
                    refreshButton.disabled = false;
                });
            });
        }

        // --- AJAX untuk Claim Bonus ---
        const claimBonusBtn = document.getElementById('claimBonusBtn');
        const bonusBalanceDisplay = document.getElementById('bonusBalanceDisplay');

        const claimBonusModal = document.getElementById('claimBonusModal');
        const claimModalText = document.getElementById('claimModalText');
        const confirmClaimBtn = document.getElementById('confirmClaimBtn');
        const cancelClaimBtn = document.getElementById('cancelClaimBtn');
        const modalTopCloseButton = document.getElementById('modalTopCloseButton');

        let amountToClaim = 0;
        const originalClaimButtonHTML = claimBonusBtn ? claimBonusBtn.textContent : 'Claim';

        const loadingSpinnerSVG = (colorClass = 'text-primary') => `
            <svg class="animate-spin h-5 w-5 ${colorClass}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>`;

        function openClaimModal(bonusAmount) {
            amountToClaim = bonusAmount;
            if (claimModalText) {
                claimModalText.innerHTML = `Claim <span class="font-medium">IDR ${parseFloat(bonusAmount).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 })}</span> bonus balance to main balance?`;
            }
            if (claimBonusModal) {
                claimBonusModal.classList.remove('w-0');
                claimBonusModal.classList.add('w-screen', 'h-screen', 'inset-0', 'bg-black', 'bg-opacity-50');
            }
        }

        function closeClaimModal() {
            if (claimBonusModal) {
                claimBonusModal.classList.add('w-0');
                claimBonusModal.classList.remove('w-screen', 'h-screen', 'inset-0', 'bg-black', 'bg-opacity-50');
            }
        }

        if (claimBonusBtn && bonusBalanceDisplay) {
            const initialBonusText = bonusBalanceDisplay.innerText.replace('IDR', '').replace(/\./g, '').trim();
            let initialBonusAmount = parseFloat(initialBonusText.replace(',', '.'));
            if (isNaN(initialBonusAmount)) initialBonusAmount = 0;

            if (initialBonusAmount <= 0) {
                 claimBonusBtn.disabled = true;
                 claimBonusBtn.classList.remove('border-primary', 'text-primary', 'hover:lg:brightness-[0.9]');
                 claimBonusBtn.classList.add('border-gray-400', 'text-gray-400', 'opacity-50', 'cursor-not-allowed');
            } else {
                 claimBonusBtn.disabled = false;
                 claimBonusBtn.classList.add('border-primary', 'text-primary', 'hover:lg:brightness-[0.9]');
                 claimBonusBtn.classList.remove('border-gray-400', 'text-gray-400', 'opacity-50', 'cursor-not-allowed');
            }

            claimBonusBtn.addEventListener('click', function() {
                const bonusText = bonusBalanceDisplay.innerText.replace('IDR', '').replace(/\./g, '').trim();
                const bonusAmount = parseFloat(bonusText.replace(',', '.'));

                if (!isNaN(bonusAmount) && bonusAmount > 0) {
                    openClaimModal(bonusAmount);
                } else {
                    alert('No bonus balance to claim or invalid amount.');
                }
            });
        }

        if (confirmClaimBtn) {
            confirmClaimBtn.addEventListener('click', function() {
                if (amountToClaim <= 0) {
                    alert('Invalid bonus amount to claim.');
                    closeClaimModal();
                    return;
                }

                if(claimBonusBtn) {
                    claimBonusBtn.innerHTML = loadingSpinnerSVG('text-primary');
                    claimBonusBtn.disabled = true;
                }
                confirmClaimBtn.innerHTML = loadingSpinnerSVG('text-white');
                confirmClaimBtn.disabled = true;
                if(cancelClaimBtn) cancelClaimBtn.disabled = true;

                const formData = new FormData();
                formData.append('jumlah_bonus_diklaim', amountToClaim);
                formData.append('keterangan', 'Klaim bonus balance');

                fetch(`${baseUrl}/proses_claim_slider_bonus.php`, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) {
                        if (balanceDisplay && data.new_main_balance !== undefined) {
                            balanceDisplay.innerHTML = `IDR&nbsp;${parseFloat(data.new_main_balance).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 })}`;
                        }
                        if (bonusBalanceDisplay && data.new_bonus_balance !== undefined) {
                            bonusBalanceDisplay.innerHTML = `IDR&nbsp;${parseFloat(data.new_bonus_balance).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 })}`;
                        }

                        if (claimBonusBtn) {
                            claimBonusBtn.innerHTML = originalClaimButtonHTML;
                            if (data.new_bonus_balance <= 0) {
                                claimBonusBtn.disabled = true;
                                claimBonusBtn.classList.remove('border-primary', 'text-primary', 'hover:lg:brightness-[0.9]');
                                claimBonusBtn.classList.add('border-gray-400', 'text-gray-400', 'opacity-50', 'cursor-not-allowed');
                            } else {
                               claimBonusBtn.disabled = false;
                               claimBonusBtn.classList.add('border-primary', 'text-primary', 'hover:lg:brightness-[0.9]');
                               claimBonusBtn.classList.remove('border-gray-400', 'text-gray-400', 'opacity-50', 'cursor-not-allowed');
                            }
                        }
                        closeClaimModal();
                    } else {
                         if (claimBonusBtn) {
                            claimBonusBtn.innerHTML = originalClaimButtonHTML;
                            const currentBonusText = bonusBalanceDisplay.innerText.replace('IDR', '').replace(/\./g, '').trim();
                            if (parseFloat(currentBonusText.replace(',', '.')) > 0) {
                                claimBonusBtn.disabled = false;
                            } else {
                                claimBonusBtn.disabled = true;
                            }
                        }
                    }
                })
                .catch(error => {
                    console.error('Error claiming bonus:', error);
                    alert('An error occurred: ' + error.message);
                    if (claimBonusBtn) {
                        claimBonusBtn.innerHTML = originalClaimButtonHTML;
                        claimBonusBtn.disabled = false;
                    }
                })
                .finally(() => {
                    confirmClaimBtn.innerHTML = 'Yes';
                    confirmClaimBtn.disabled = false;
                    if(cancelClaimBtn) cancelClaimBtn.disabled = false;
                });
            });
        }

        if (cancelClaimBtn) {
            cancelClaimBtn.addEventListener('click', closeClaimModal);
        }
        if (modalTopCloseButton) {
            modalTopCloseButton.addEventListener('click', closeClaimModal);
        }
    });
</script>