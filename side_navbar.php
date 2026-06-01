<?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) : ?>
    <main id="main-component" class="min-h-screen transition-all duration-500 ease-out lg:pb-0 relative" style="padding-top: 100px;">
        <template data-dgst="NEXT_DYNAMIC_NO_SSR_CODE"></template>
        <div class="fixed z-[1000] transition duration-300 ease-in-out overscroll-contain -right-3/4 w-0"></div>
        <aside id="sidebar" class="sidebar">
            <div class="flex justify-between items-center px-4 mt-4 h-[64px]">
                <div class="flex">
                    <div class="flex-none w-10 md:w-12 h-10 md:h-12 flex items-center justify-center rounded-full bg-background-secondary border border-base">
                        <p class="text-xl md:text-3xl font-bold"><?php echo $inisial; ?></p>
                    </div>
                    <div class="px-3 flex">
                        <div class="flex items-center">
                            <div>
                                <p class="text-xs truncate"><?php echo htmlspecialchars($nama_pengguna); ?></p>
                                <span class="text-sm font-medium flex items-center h-6">IDR <?php echo number_format($_SESSION['saldo_anggota'], 0, ',', '.'); ?>
                                    <button class="ml-3 w-6 h-6 items-center justify-center rotate-270 rounded-full bg-background-secondary flex" onclick="window.location.reload();">
                                        <svg width="18" height="18" viewbox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="m10 19-.707-.707-.707.707.707.707L10 19Zm3.293-4.707-4 4 1.414 1.414 4-4-1.414-1.414Z" fill="var(--caption)"></path>
                                            <path d="M5.938 15.5A7 7 0 1 1 12 19" stroke="var(--caption)" stroke-width="2" stroke-linecap="round"></path>
                                        </svg>
                                    </button>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <button class="close-btn" aria-label="Close Sidebar">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" size="24">
                        <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                </button>
            </div>

            <div class="px-3 py-1">
                <a href="<?php echo $alamat_website . 'deposit'; ?>" class="w-full justify-between border border-base py-2 px-2 pl-3 rounded-full flex items-center bg-primary text-white">
                    <figure class="flex items-center">
                        <svg width="24" height="24" viewbox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg" size="24">
                            <g clip-path="url(#a)">
                                <path d="M13.942 18.995c-.341.341-.925.1-.925-.383V16.39a.541.541 0 0 0-.541-.541h-.375c-.3 0-.542.242-.542.541v2.224a.541.541 0 0 1-.924.382.541.541 0 0 0-.765 0l-.265.265a.541.541 0 0 0 0 .766l2.3 2.3a.541.541 0 0 0 .766 0l2.3-2.3a.541.541 0 0 0 0-.765l-.264-.266a.54.54 0 0 0-.765 0Z" fill="white"></path>
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M12.288 16.577a7.288 7.288 0 1 0 0-14.577 7.288 7.288 0 1 0 0 14.577Zm2.186-9.475h-2.186a.73.73 0 0 0 0 1.457c1.205 0 2.186.981 2.186 2.187 0 .949-.611 1.75-1.457 2.052v1.592h-1.458v-1.458h-1.457v-1.457h2.186a.73.73 0 0 0 0-1.458 2.189 2.189 0 0 1-2.186-2.186c0-.95.611-1.751 1.457-2.053V4.187h1.458v1.458h1.457v1.457Z" fill="white"></path>
                                <path d="M7.186 22.407H5.73A.729.729 0 0 1 5 21.68V20.22a.729.729 0 0 1 1.458 0c0 .402.326.729.728.729a.729.729 0 0 1 0 1.457ZM18.849 22.407H17.39a.729.729 0 0 1 0-1.457.729.729 0 0 0 .729-.729.729.729 0 0 1 1.457 0v1.458a.729.729 0 0 1-.728.729Z" fill="white"></path>
                            </g>
                            <defs>
                                <clippath id="a">
                                    <path fill="#fff" transform="translate(5 2)" d="M0 0h14.577v20.485H0z"></path>
                                </clippath>
                            </defs>
                        </svg>
                    </figure>
                    <span class="font-semibold">INSTANT DEPOSIT</span>
                    <svg width="22" height="22" viewbox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg" size="22">
                        <path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="white"></path>
                    </svg>
                </a>
            </div>

            <div id="account-navigation-options-sidebar" class="mt-3">
                <a class="justify-between px-4 py-3 hover:lg:bg-background-tertiary transition-all duration-300 ease-in-out" href="<?php echo $alamat_website . 'my-account'; ?>">
                    <div class="flex items-center w-[calc(100%-24px)] pr-1">
                        <svg width="24" height="24" viewbox="0 0 24 24" fill="var(--primary)" xmlns="http://www.w3.org/2000/svg" size="24">
                            <path d="M19.651 20.431c.553-.115.883-.694.608-1.187-.606-1.088-1.56-2.043-2.78-2.772-1.572-.938-3.498-1.446-5.479-1.446-1.981 0-3.907.508-5.479 1.446-1.22.729-2.174 1.684-2.78 2.772-.275.493.055 1.072.607 1.187a37.503 37.503 0 0015.303 0z" fill="var(--primary)"></path>
                            <circle cx="12" cy="8.026" r="5" fill="var(--primary)"></circle>
                        </svg>
                        <span class="text-sm pl-2 undefined undefined">My Account</span>
                    </div>
                    <figure class="w-6">
                        <svg width="22" height="22" viewbox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg" size="22">
                            <path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="var(--base)"></path>
                        </svg>
                    </figure>
                </a>
                <a class="justify-between px-4 py-3 hover:lg:bg-background-tertiary transition-all duration-300 ease-in-out" href="<?php echo $alamat_website . 'withdraw'; ?>">
                    <div class="flex items-center w-[calc(100%-24px)] pr-1">
                        <svg width="24" height="24" viewbox="0 0 24 24" fill="var(--primary)" xmlns="http://www.w3.org/2000/svg" size="24">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M7 2a5 5 0 0 0-5 5v10a5 5 0 0 0 5 5h10a5 5 0 0 0 5-5V7a5 5 0 0 0-5-5H7Zm.386 8.322 4-3.111.614-.478.614.478 4 3.11-1.228 1.58L13 10.044V15a1 1 0 1 1-2 0v-4.955L8.614 11.9l-1.228-1.578Z" fill="var(--primary)"></path>
                        </svg>
                        <span class="text-sm pl-2 undefined undefined">Withdraw</span>
                    </div>
                    <figure class="w-6">
                        <svg width="22" height="22" viewbox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg" size="22">
                            <path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="var(--base)"></path>
                        </svg>
                    </figure>
                </a>
                <a class="justify-between px-4 py-3 hover:lg:bg-background-tertiary transition-all duration-300 ease-in-out" href="<?php echo $alamat_website . 'history-deposit'; ?>">
                    <div class="flex items-center w-[calc(100%-24px)] pr-1">
                        <svg width="24" height="24" viewbox="0 0 24 24" fill="var(--primary)" xmlns="http://www.w3.org/2000/svg" size="24">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M12 1v5.56c0 .466 0 .92.05 1.294.057.421.195.902.594 1.302.4.4.88.537 1.302.594.374.05.828.05 1.294.05h5.56c-.011-.453-.047-.752-.163-1.03-.167-.405-.485-.723-1.12-1.359L16.588 2.29c-.636-.636-.954-.954-1.358-1.122-.278-.115-.578-.15-1.031-.162-.466-.011-.921-.01-1.38-.01H12Zm2.2.005V6.5c0 .55.002.851.03 1.06l.002.008.008.002c.209.028.51.03 1.06.03h5.495c-.011-.453-.047-.752-.163-1.03-.167-.405-.485-.723-1.12-1.359L16.588 2.29c-.636-.636-.954-.954-1.358-1.122-.278-.115-.578-.15-1.031-.162ZM7.6 13.1A1.1 1.1 0 0 1 8.7 12h6.6a1.1 1.1 0 0 1 0 2.2H8.7a1.1 1.1 0 0 1-1.1-1.1Zm1.1 3.3a1.1 1.1 0 0 0 0 2.2h4.4a1.1 1.1 0 0 0 0-2.2H8.7Z" fill="var(--primary)"></path>
                        </svg>
                        <span class="text-sm pl-2 undefined undefined">History</span>
                    </div>
                    <figure class="w-6">
                        <svg width="22" height="22" viewbox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg" size="22">
                            <path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="var(--base)"></path>
                        </svg>
                    </figure>
                </a>
                <a class="justify-between px-4 py-3 flex cursor-pointer hover:lg:bg-background-tertiary transition-all duration-300 ease-in-out" href="<?php echo $alamat_website . 'logout'; ?>">
                    <div class="flex items-center w-[calc(100%-24px)] pr-1">
                        <svg width="24" height="24" viewbox="0 0 24 24" fill="var(--primary)" xmlns="http://www.w3.org/2000/svg" size="24">
                            <path d="m2 12-.78-.625-.5.625.5.625L2 12Zm9 1a1 1 0 1 0 0-2v2ZM5.22 6.375l-4 5 1.56 1.25 4-5-1.56-1.25Zm-4 6.25 4 5 1.56-1.25-4-5-1.56 1.25ZM2 13h9v-2H2v2ZM13.342 20.557l.165-.986-.165.986Zm7.597.19.646.764-.646-.763ZM15.014 3.165l-.165-.986.165.986Zm5.925.088.646-.763-.646.763ZM13.507 4.43l1.671-.278-.329-1.973-1.671.279.329 1.972ZM21 9.083v5.834h2V9.083h-2Zm-5.822 10.766-1.671-.278-.329 1.973 1.671.278.329-1.973ZM11 8.132v-.743H9v.743h2Zm0 8.48v-.546H9v.546h2Zm2.507 2.959c-.824-.138-1.35-.227-1.734-.342-.358-.106-.472-.201-.536-.277l-1.526 1.293c.41.484.932.735 1.491.901.532.159 1.203.269 1.976.398l.329-1.973ZM9 16.61c0 .784-.002 1.464.067 2.015.072.578.234 1.135.644 1.619l1.526-1.293c-.064-.075-.14-.203-.185-.574-.05-.398-.052-.932-.052-1.767H9Zm12-1.694c0 1.675-.002 2.823-.123 3.67-.116.82-.32 1.174-.584 1.398l1.293 1.526c.797-.675 1.123-1.593 1.272-2.642.144-1.021.142-2.338.142-3.952h-2Zm-6.15 6.905c1.59.265 2.89.484 3.92.51 1.06.025 2.018-.146 2.816-.821l-1.293-1.526c-.264.223-.646.367-1.474.347-.856-.021-1.99-.207-3.641-.483l-.329 1.973Zm.328-17.671c1.652-.275 2.785-.462 3.64-.483.829-.02 1.21.124 1.475.347l1.293-1.526c-.797-.675-1.757-.846-2.816-.82-1.03.025-2.33.244-3.92.509l.328 1.973ZM23 9.083c0-1.614.002-2.93-.142-3.952-.15-1.049-.476-1.967-1.273-2.642l-1.292 1.526c.264.224.468.577.584 1.397.12.848.123 1.996.123 3.67h2Zm-9.822-6.626c-.773.128-1.444.238-1.976.397-.559.166-1.081.417-1.491.901l1.526 1.293c.064-.076.178-.17.536-.277.384-.115.91-.204 1.734-.342l-.329-1.972ZM11 7.389c0-.835.002-1.369.052-1.767.046-.37.121-.499.185-.574L9.711 3.755c-.41.484-.572 1.04-.644 1.62C8.998 5.924 9 6.604 9 7.388h2Z" fill="var(--primary)"></path>
                        </svg>
                        <span class="text-sm pl-2 undefined undefined">Logout</span>
                    </div>
                </a>
            </div>

            <div id="sidebar-navigation-options" class="mt-3 landscape:h-[calc(100%-120px)] landscape:overflow-auto">
                <a class="justify-between px-4 py-3 hover:lg:bg-background-tertiary transition-all duration-300 ease-in-out" href="<?php echo $alamat_website . 'home'; ?>">
                    <div class="flex items-center w-[calc(100%-24px)] pr-1">
                        <svg width="24" height="24" viewbox="0 0 24 24" fill="var(--primary)" xmlns="http://www.w3.org/2000/svg" size="24">
                            <path d="M21.462 13.303c-.348.348-.81.54-1.302.54h-.302v6.004a2.157 2.157 0 0 1-2.155 2.155h-3.778v-5.294a.984.984 0 0 0-.984-.983H11.06a.984.984 0 0 0-.984.983v5.294H6.297a2.158 2.158 0 0 1-2.155-2.155v-6.005h-.325c-.02 0-.038 0-.057-.002a1.843 1.843 0 0 1-1.225-3.137l.008-.009 8.155-8.155A1.83 1.83 0 0 1 12 2c.492 0 .954.193 1.302.54l8.16 8.16a1.844 1.844 0 0 1 0 2.604Z" fill="var(--primary)"></path>
                        </svg>
                        <span class="text-sm pl-2 undefined text-primary">Beranda</span>
                    </div>
                    <figure class="w-6">
                        <svg width="22" height="22" viewbox="0 0 24 24" fill="var(--primary)" xmlns="http://www.w3.org/2000/svg" size="22">
                            <path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="var(--primary)"></path>
                        </svg>
                    </figure>
                </a>
                <a class="justify-between px-4 py-3 hover:lg:bg-background-tertiary transition-all duration-300 ease-in-out" href="<?php echo $alamat_website . 'promo'; ?>">
                    <div class="flex items-center w-[calc(100%-24px)] pr-1">
                        <svg width="24" height="24" viewbox="0 0 25 25" fill="var(--base)" xmlns="http://www.w3.org/2000/svg" size="24">
                            <g fill="var(--base)">
                                <path d="M17.011 14.523a.718.718 0 0 0-.716.716c0 .393.323.715.716.715a.718.718 0 0 0 .716-.715.718.718 0 0 0-.716-.716Zm0 0a.718.718 0 0 0-.716.716c0 .393.323.715.716.715a.718.718 0 0 0 .716-.715.718.718 0 0 0-.716-.716Zm0 0a.718.718 0 0 0-.716.716c0 .393.323.715.716.715a.718.718 0 0 0 .716-.715.718.718 0 0 0-.716-.716Zm0 0a.718.718 0 0 0-.716.716c0 .393.323.715.716.715a.718.718 0 0 0 .716-.715.718.718 0 0 0-.716-.716Zm0 0a.718.718 0 0 0-.716.716c0 .393.323.715.716.715a.718.718 0 0 0 .716-.715.718.718 0 0 0-.716-.716Zm0 0a.718.718 0 0 0-.716.716c0 .393.323.715.716.715a.718.718 0 0 0 .716-.715.718.718 0 0 0-.716-.716Zm0 0a.718.718 0 0 0-.716.716c0 .393.323.715.716.715a.718.718 0 0 0 .716-.715.718.718 0 0 0-.716-.716Zm0 0a.718.718 0 0 0-.716.716c0 .393.323.715.716.715a.718.718 0 0 0 .716-.715.718.718 0 0 0-.716-.716Zm0 0a.718.718 0 0 0-.716.716c0 .393.323.715.716.715a.718.718 0 0 0 .716-.715.718.718 0 0 0-.716-.716Zm0 0a.718.718 0 0 0-.716.716c0 .393.323.715.716.715a.718.718 0 0 0 .716-.715.718.718 0 0 0-.716-.716ZM22.023 4.5H8.42a.723.723 0 0 0-.507.209l-.924.927-.925-.927a.722.722 0 0 0-.507-.209h-3.58c-.789 0-1.432.643-1.432 1.432v12.886c0 .79.643 1.432 1.432 1.432h3.58c.19 0 .372-.076.507-.209l.925-.927.924.926c.135.134.317.21.507.21h13.603c.79 0 1.431-.642 1.431-1.432V5.932c0-.789-.64-1.432-1.431-1.432ZM7.704 17.386H6.273v-1.431h1.432v1.431Zm0-2.863H6.274V13.09h1.432v1.432Zm0-2.864H6.274v-1.432h1.432v1.432Zm0-2.864H6.274V7.364h1.432v1.431Zm5.012-1.431a2.15 2.15 0 0 1 2.148 2.147 2.15 2.15 0 0 1-2.148 2.148 2.15 2.15 0 0 1-2.148-2.148 2.15 2.15 0 0 1 2.148-2.147Zm-1.432 10.022a.716.716 0 0 1-.55-1.174l7.16-8.59a.717.717 0 0 1 1.099.918l-7.16 8.59a.715.715 0 0 1-.549.256Zm5.727 0a2.15 2.15 0 0 1-2.147-2.147 2.15 2.15 0 0 1 2.147-2.148 2.15 2.15 0 0 1 2.148 2.148 2.15 2.15 0 0 1-2.148 2.147Zm0-2.863a.718.718 0 0 0-.716.716c0 .393.323.715.716.715a.718.718 0 0 0 .716-.715.718.718 0 0 0-.716-.716Zm-4.295-4.296a.716.716 0 1 0 0-1.432.716.716 0 0 0 0 1.432Zm4.295 4.296a.718.718 0 0 0-.716.716c0 .393.323.715.716.715a.718.718 0 0 0 .716-.715.718.718 0 0 0-.716-.716Zm0 0a.718.718 0 0 0-.716.716c0 .393.323.715.716.715a.718.718 0 0 0 .716-.715.718.718 0 0 0-.716-.716Zm0 0a.718.718 0 0 0-.716.716c0 .393.323.715.716.715a.718.718 0 0 0 .716-.715.718.718 0 0 0-.716-.716Zm0 0a.718.718 0 0 0-.716.716c0 .393.323.715.716.715a.718.718 0 0 0 .716-.715.718.718 0 0 0-.716-.716Zm0 0a.718.718 0 0 0-.716.716c0 .393.323.715.716.715a.718.718 0 0 0 .716-.715.718.718 0 0 0-.716-.716Zm0 0a.718.718 0 0 0-.716.716c0 .393.323.715.716.715a.718.718 0 0 0 .716-.715.718.718 0 0 0-.716-.716Zm0 0a.718.718 0 0 0-.716.716c0 .393.323.715.716.715a.718.718 0 0 0 .716-.715.718.718 0 0 0-.716-.716Zm0 0a.718.718 0 0 0-.716.716c0 .393.323.715.716.715a.718.718 0 0 0 .716-.715.718.718 0 0 0-.716-.716Z"></path>
                            </g>
                        </svg><span class="text-sm pl-2 undefined false">Promosi</span>
                    </div>
                    <figure class="w-6"><svg width="22" height="22" viewbox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg" size="22">
                        <path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="var(--base)"></path>
                    </svg>
                    </figure>
                </a>
                <a class="justify-between px-4 py-3 hover:lg:bg-background-tertiary transition-all duration-300 ease-in-out" href="<?php echo $alamat_website . 'about'; ?>">
                    <div class="flex items-center w-[calc(100%-24px)] pr-1">
                        <svg width="24" height="24" viewbox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg" size="24">
                            <path d="M16 3v3M8 3v3" stroke="var(--base)" stroke-width="2" stroke-linecap="round"></path>
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M14 4h-4v2a2 2 0 1 1-4 0V4.076c-.975.096-1.631.313-2.121.803C3 5.757 3 7.172 3 10v5c0 2.828 0 4.243.879 5.121C4.757 21 6.172 21 9 21h6c2.828 0 4.243 0 5.121-.879C21 19.243 21 17.828 21 15v-5c0-2.828 0-4.243-.879-5.121-.49-.49-1.146-.707-2.121-.803V6a2 2 0 1 1-4 0V4Zm-7 8a1 1 0 0 1 1-1h8a1 1 0 1 1 0 2H8a1 1 0 0 1-1-1Zm1 3a1 1 0 1 0 0 2h8a1 1 0 1 0 0-2H8Z" fill="var(--base)"></path>
                        </svg>
                        <span class="text-sm pl-2 undefined false">Tentang Kami</span>
                    </div>
                    <figure class="w-6">
                        <svg width="22" height="22" viewbox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg" size="22">
                            <path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="var(--base)"></path>
                        </svg>
                    </figure>
                </a>
                <a class="justify-between px-4 py-3 hover:lg:bg-background-tertiary transition-all duration-300 ease-in-out" href="<?php echo $alamat_website . 'referal'; ?>">
                        <div class="flex items-center w-[calc(100%-24px)] pr-1">
                            <svg width="24" height="24" viewbox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg" size="24">
                                <circle cx="10" cy="8" r="5" fill="var(--base)"></circle>
                                <path d="M19 10v6M22 13h-6" stroke="var(--base)" stroke-width="2" stroke-linecap="round"></path>
                                <path d="M17.142 20.383c.462-.105.739-.585.534-1.012-.552-1.15-1.459-2.162-2.634-2.924C13.595 15.508 11.823 15 10 15s-3.595.508-5.042 1.447c-1.175.762-2.082 1.773-2.634 2.924-.205.427.072.907.534 1.012a32.333 32.333 0 0 0 14.284 0Z" fill="var(--base)"></path>
                            </svg>
                            <span class="text-sm pl-2 undefined false">Referral</span>
                        </div>
                        <figure class="w-6">
                            <svg width="22" height="22" viewbox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg" size="22">
                                <path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="var(--base)"></path>
                            </svg>
                        </figure>
                    </a>
                <a class="justify-between px-4 py-3 hover:lg:bg-background-tertiary transition-all duration-300 ease-in-out" href="<?php echo $isi_1_link_livechat_web; ?>" target="blank">
                    <div class="flex items-center w-[calc(100%-24px)] pr-1">
                        <svg width="24" height="24" viewbox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="16" y="12" width="4" height="7" rx="2" fill="var(--base)" stroke="var(--base)" stroke-width="2" stroke-linejoin="round"></rect>
                            <rect x="4" y="12" width="4" height="7" rx="2" fill="var(--base)" stroke="var(--base)" stroke-width="2" stroke-linejoin="round"></rect>
                            <path d="M4 13v3M20 13v3M20 13c0-2.387-.843-4.676-2.343-6.364C16.157 4.948 14.122 4 12 4s-4.157.948-5.657 2.636C4.843 8.324 4 10.613 4 13" stroke="var(--base)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                        <span class="text-sm pl-2 undefined false">Pusat Bantuan</span>
                    </div>
                    <figure class="w-6">
                        <svg width="22" height="22" viewbox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg" size="22">
                            <path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="var(--base)"></path>
                        </svg>
                    </figure>
                </a>
                <a class="justify-between px-4 py-3 hover:lg:bg-background-tertiary transition-all duration-300 ease-in-out" href="<?php echo $isi_1_link_livechat_web; ?>" target="blank">
                    <div class="flex items-center w-[calc(100%-24px)] pr-1">
                        <svg xmlns="http://www.w3.org/2000/svg" xml:space="preserve" width="24" height="24" viewbox="0 0 512 512" size="24" fill="var(--base)">
                            <path fill="var(--base)" d="M120.606 169h270.788v220.663c0 13.109-10.628 23.737-23.721 23.737H340.55v67.203c0 17.066-13.612 30.897-30.415 30.897-16.846 0-30.438-13.831-30.438-30.897V413.4h-47.371v67.203c0 17.066-13.639 30.897-30.441 30.897-16.799 0-30.437-13.831-30.437-30.897V413.4h-27.099c-13.096 0-23.744-10.628-23.744-23.737V169zm-53.065-1.801c-16.974 0-30.723 13.963-30.723 31.2v121.937c0 17.217 13.749 31.204 30.723 31.204 16.977 0 30.723-13.987 30.723-31.204V198.399c0-17.237-13.746-31.2-30.723-31.2zm323.854-20.435H120.606c3.342-38.578 28.367-71.776 64.392-90.998l-25.746-37.804c-3.472-5.098-2.162-12.054 2.946-15.525C167.3-1.034 174.242.286 177.731 5.38l28.061 41.232c15.558-5.38 32.446-8.469 50.208-8.469 17.783 0 34.672 3.089 50.229 8.476L334.29 5.395c3.446-5.108 10.41-6.428 15.512-2.957 5.108 3.471 6.418 10.427 2.946 15.525l-25.725 37.804c36.024 19.21 61.032 52.408 64.372 90.997zm-177.53-52.419c0-8.273-6.699-14.983-14.969-14.983-8.291 0-14.99 6.71-14.99 14.983 0 8.269 6.721 14.976 14.99 14.976s14.969-6.707 14.969-14.976zm116.127 0c0-8.273-6.722-14.983-14.99-14.983-8.291 0-14.97 6.71-14.97 14.983 0 8.269 6.679 14.976 14.97 14.976 8.269 0 14.99-6.707 14.99-14.976zm114.488 72.811c-16.956 0-30.744 13.984-30.744 31.222v121.98c0 17.238 13.788 31.226 30.744 31.226 16.978 0 30.701-13.987 30.701-31.226v-121.98c.001-17.238-13.723-31.222-30.701-31.222z"></path>
                        </svg>
                        <span class="text-sm pl-2 undefined false">Aplikasi Android
                            <span class="text-xs px-2 ml-2 rounded-sm bg-success">New</span>
                        </span>
                    </div>
                    <figure class="w-6">
                        <svg width="22" height="22" viewbox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg" size="22">
                            <path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="var(--base)"></path>
                        </svg>
                    </figure>
                </a>
            </div>
            <p class="text-center text-sm text-caption absolute landscape:relative landscape:bottom-0 bottom-6 left-0 right-0">
                Version
                3.4.0.2
            </p>
        </aside>
    <?php else : ?>
        <main id="main-component" class="min-h-screen transition-all duration-500 ease-out lg:pb-0 relative" style="padding-top: 100px;">
            <template data-dgst="NEXT_DYNAMIC_NO_SSR_CODE"></template>
            <div class="fixed z-[1000] transition duration-300 ease-in-out overscroll-contain -right-3/4 w-0"></div>
            <aside id="sidebar" class="sidebar">
                <div class="flex justify-between items-center px-4 mt-4 h-[64px]">
                    <div class="flex items-center">
                        <a href="<?php echo $alamat_website . 'home'; ?>">
                            <img alt="Logo Brand" loading="lazy" width="0" height="0" decoding="async" data-nimg="1" class="h-6 lg:h-8 w-auto" style="color: transparent;" src="<?php echo 'assets/img/' . $isi_1_logo_web; ?>">
                        </a>
                    </div>
                    <button class="close-btn" aria-label="Close Sidebar">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" size="24">
                            <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </button>
                </div>
                <div class="p-3">
                    <form id="loginDesktop" class="flex flex-col" method="post" action="process_login">
                        <input id="usernameCompact" name="nama_pengguna_anggota" class="bg-background-secondary w-full rounded-md py-2 px-3 my-2 border border-background-secondary focus:outline-none focus:border-primary focus:ring-primary" placeholder="Username" autocomplete="username" type="text" value="">
                        <div class="relative flex items-center w-full">
                            <input id="passwordCompact" name="kata_sandi_anggota" class="bg-background-secondary w-full rounded-md py-2 px-3 my-2 border border-background-secondary focus:outline-none focus:border-primary focus:ring-primary" placeholder="Password" autocomplete="password" type="password" value="">
                            <span class="absolute right-2 cursor-pointer bg-background-secondary px-2">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" size="20">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M15.92 12.799a4 4 0 0 0-4.719-4.719l.923.923a3 3 0 0 1 2.873 2.873l.923.923Zm-6.527-2.285a3 3 0 0 0 4.093 4.093l.726.726a4 4 0 0 1-5.545-5.545l.726.726Z" fill="#fff"></path>
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="m16.154 17.275-.735-.734c-1.064.579-2.22.959-3.419.959-1.672 0-3.262-.74-4.633-1.726-1.367-.984-2.474-2.182-3.17-3.026-.423-.515-.467-.604-.467-.748 0-.143.044-.233.468-.748.67-.812 1.72-1.953 3.018-2.915L6.5 7.623C5.17 8.63 4.104 9.793 3.426 10.616l-.059.072c-.33.399-.637.77-.637 1.312s.307.913.637 1.312l.059.072c.725.88 1.894 2.149 3.357 3.201C8.243 17.635 10.036 18.5 12 18.5c1.51 0 2.92-.511 4.154-1.225ZM9.19 6.07c.88-.35 1.824-.569 2.81-.569 1.964 0 3.758.865 5.217 1.915 1.463 1.052 2.632 2.321 3.357 3.201l.059.072c.33.399.637.77.637 1.312s-.307.913-.637 1.312l-.059.072a19.988 19.988 0 0 1-1.983 2.086l-.708-.708a18.943 18.943 0 0 0 1.92-2.014c.424-.515.467-.604.467-.748 0-.143-.043-.233-.468-.748-.695-.844-1.802-2.042-3.17-3.026C15.263 7.24 13.673 6.5 12 6.5c-.694 0-1.375.128-2.031.348l-.78-.78Z" fill="#fff"></path>
                                    <path d="m5 2 16 16" stroke="#fff"></path>
                                </svg>
                            </span>
                        </div>
                        <button aria-label="Button Login Header Form" aria-labelledby="Button Login Header Form" type="submit" class="bg-primary px-2 py-1 text-sm justify-center rounded-lg my-2 h-full min-w-[56px] min-h-[34px] text-white hover:lg:brightness-90 transition-all duration-300 ease-in-out">Login</button>
                        <a class="bg-white text-primary px-2 py-1 text-sm rounded-lg my-2 h-full min-h-[34px] hover:lg:brightness-90 transition-all duration-300 ease-in-out text-center" href="<?php echo $alamat_website . 'auth-register'; ?>">Daftar</a>
                    </form>
                </div>

                <div id="sidebar-navigation-options-unlogged" class="mt-3 landscape:h-[calc(100%-120px)] landscape:overflow-auto">
                    <a class="justify-between px-4 py-3 hover:lg:bg-background-tertiary transition-all duration-300 ease-in-out" href="<?php echo $alamat_website . 'home'; ?>">
                        <div class="flex items-center w-[calc(100%-24px)] pr-1">
                            <svg width="24" height="24" viewbox="0 0 24 24" fill="var(--primary)" xmlns="http://www.w3.org/2000/svg" size="24">
                                <path d="M21.462 13.303c-.348.348-.81.54-1.302.54h-.302v6.004a2.157 2.157 0 0 1-2.155 2.155h-3.778v-5.294a.984.984 0 0 0-.984-.983H11.06a.984.984 0 0 0-.984.983v5.294H6.297a2.158 2.158 0 0 1-2.155-2.155v-6.005h-.325c-.02 0-.038 0-.057-.002a1.843 1.843 0 0 1-1.225-3.137l.008-.009 8.155-8.155A1.83 1.83 0 0 1 12 2c.492 0 .954.193 1.302.54l8.16 8.16a1.844 1.844 0 0 1 0 2.604Z" fill="var(--primary)"></path>
                            </svg>
                            <span class="text-sm pl-2 undefined text-primary">Beranda</span>
                        </div>
                        <figure class="w-6">
                            <svg width="22" height="22" viewbox="0 0 24 24" fill="var(--primary)" xmlns="http://www.w3.org/2000/svg" size="22">
                                <path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="var(--primary)"></path>
                            </svg>
                        </figure>
                    </a>
                    </div>
                <p class="text-center text-sm text-caption absolute landscape:relative landscape:bottom-0 bottom-6 left-0 right-0">
                    Version 3.4.0.2
                </p>
            </aside>
    <?php endif; ?>

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
    </style>

    <div id="globalLoadingIndicator">
        <div class="spinner"></div>
        <p id="loadingMessage">Memproses...</p>
    </div>

    <script>
        const sidebar = document.querySelector('.sidebar');
        const toggleBtn = document.querySelector('.toggle-btn');
        const closeBtn = document.querySelector('.close-btn');
		const overlay = document.getElementById('sidebar-overlay');

        function openSidebar() {
            sidebar.classList.add('active');
            overlay.style.display = 'block';
        }

        function closeSidebar() {
            sidebar.classList.remove('active');
            overlay.style.display = 'none';
        }

        toggleBtn.addEventListener('click', openSidebar);
        closeBtn.addEventListener('click', closeSidebar);

        overlay.addEventListener('click', closeSidebar);

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeSidebar();
            }
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const toggleButton = document.getElementById("menu-toggle");
            const navigationOptions = document.getElementById("account-navigation-options");

            if(toggleButton && navigationOptions) {
                toggleButton.addEventListener("click", function() {
                    if (navigationOptions.style.maxHeight === "0px" || navigationOptions.style.maxHeight === "") {
                        navigationOptions.style.maxHeight = "400px";
                    } else {
                        navigationOptions.style.maxHeight = "0px";
                    }
                });
            }
        });
    </script>
    <script>
        function setActiveMenu() {
            const currentPath = window.location.pathname;
            const menuItems = document.querySelectorAll('.navigation a');

            menuItems.forEach(item => {
                const href = item.getAttribute('href');
                const pElement = item.querySelector('p');

                if (currentPath.includes(href.split('/').pop())) {
                    pElement.classList.remove('false');
                    pElement.classList.add('text-primary');
                } else {
                    pElement.classList.remove('text-primary');
                    pElement.classList.add('false');
                }
            });
        }

        document.addEventListener('DOMContentLoaded', setActiveMenu);
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const header = document.getElementById('header');
            let lastScrollTop = 0;
            let isThrottled = false;
            const throttleDelay = 50;

            function handleScroll() {
                if (isThrottled) return;
                isThrottled = true;
                setTimeout(() => {
                    let scrollTop = window.pageYOffset || document.documentElement.scrollTop;

                    if (scrollTop > lastScrollTop) {
                        header.classList.add('header-hidden');
                    } else {
                        header.classList.remove('header-hidden');
                    }
                    lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;

                    isThrottled = false;
                }, throttleDelay);
            }

            window.addEventListener('scroll', handleScroll);
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const logoutButton = document.querySelector('.bg-background-secondary');
            const logoutConfirm = document.querySelector('.fixed');
            const yesButton = logoutConfirm.querySelector('button.border');
            const noButton = logoutConfirm.querySelector('button.bg-primary');

            if (logoutButton && logoutConfirm) {
                logoutButton.addEventListener('click', function() {
                    logoutConfirm.style.maxHeight = '500px';
                });
            }

            if (yesButton) {
                yesButton.addEventListener('click', function() {
                    window.location.href = 'logout.php';
                });
            }

            if (noButton) {
                noButton.addEventListener('click', function() {
                    logoutConfirm.style.maxHeight = '0';
                });
            }
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('loginDesktop');
            const loginSubmitBtn = loginForm.querySelector('button[type="submit"]');
            const usernameInput = document.getElementById('usernameCompact');
            const passwordInput = document.getElementById('passwordCompact');

            const globalLoadingIndicator = document.getElementById('globalLoadingIndicator');
            const loadingMessageElement = document.getElementById('loadingMessage');
            const loadingSpinnerElement = globalLoadingIndicator.querySelector('.spinner');

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
                loadingMessageElement.textContent = message;
                loadingSpinnerElement.classList.remove('animate-spin');
                if (success) {
                    loadingSpinnerElement.classList.add('success');
                } else {
                    loadingSpinnerElement.style.borderColor = '#e74c3c';
                    loadingSpinnerElement.style.borderTopColor = '#e74c3c';
                }
            }

            loginForm.addEventListener('submit', function(e) {
                e.preventDefault();

                if (!usernameInput.value.trim() || !passwordInput.value.trim()) {
                    alert('Username dan password tidak boleh kosong.');
                    return;
                }

                loginSubmitBtn.disabled = true;
                showGlobalLoading('Memproses...');

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
                        updateLoadingState(true, 'Login Berhasil!');
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
                    console.error('Error during login AJAX:', error);
                    updateLoadingState(false, 'Terjadi kesalahan jaringan atau server. Silakan coba lagi.');
                    setTimeout(() => {
                        hideGlobalLoading();
                    }, 2000);
                })
                .finally(() => {
                    loginSubmitBtn.disabled = false;
                });
            });
        });
    </script>
    <?php
        $nomor_floating = 1;
        $floating = mysqli_query($koneksi, "SELECT * FROM floating");
        while ($data_floating = mysqli_fetch_array($floating)) {
            $id_floating = $data_floating['id_floating'];
            $nama_floating = $data_floating['nama_floating'];
            $link_floating = $data_floating['link_floating'];
            $gambar_floating = $data_floating['gambar_floating'];

            $href = empty($link_floating) ? $alamat_website . 'rtp' : $link_floating;
            $alt_text = htmlspecialchars($nama_floating);
            $src = $alamat_website . 'assets/img/' . htmlspecialchars($gambar_floating);
            $bottom_position = 80 * $nomor_floating . 'px';

        ?>
        <div style="bottom: <?php echo $bottom_position; ?>; left: 5px; opacity: 0.98; position: fixed; z-index: 9999;">
            <a href="<?php echo $href; ?>" rel="noopener" target="_blank">
                <img alt="<?php echo $alt_text; ?>" class="wabutton" src="<?php echo $src; ?>" width="60" height="60">
            </a>
        </div>
        <?php
            $nomor_floating++;
        }
    ?>