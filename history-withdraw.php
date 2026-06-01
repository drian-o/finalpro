<?php

include_once 'koneksi.php';
include_once 'header.php';
?>
<section class="container flex mx-auto lg:pt-3 lg:pb-5 lg:mt-5">
    <div class="w-full lg:w-1/3 px-3 hidden lg:block">
        <a class="px-3 py-4 bg-background-default lg:bg-background-secondary rounded-xl mt-4 drop-shadow-[0_0_5px_rgba(0,0,0,0.6)] lg:drop-shadow-none group transition-all duration-300 ease-in-out lg:hover:bg-black/30" href="#">
            <figure class="flex flex-none items-center justify-center w-12 md:w-16 h-12 md:h-16">
                <img alt="VIP Level Badge" width="0" height="0" decoding="async" data-nimg="1" class="w-full" style="color: transparent;" loading="lazy" src="https://cdn.databerjalan.com/assets/images/store/2024-07-11T06:15:28.250Z_Pemain_Baru_1.png">
            </figure>
            <article class="w-full pl-4">
                <p class="text-sm md:text-base group-hover:text-white">Pemain Baru</p>
                <progress class="w-full h-[5px] primary-progress" value="0" max="100"></progress>
                <span class="text-xs md:text-sm group-hover:text-white">Increase your level and get rewards</span>
            </article>
            <figure class="pl-2"><svg width="26" height="26" viewbox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg" size="26">
                    <path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="var(--base)"></path>
                </svg>
            </figure>
        </a>
        <section class="bg-background-secondary rounded-xl mt-4">
            <div class="w-full lg:px-4 pt-3 flex flex-wrap px-4">
                <article class="w-full flex items-center mb-1 lg:mb-3">
                    <svg width="24" height="24" viewbox="0 0 24 24" fill="var(--primary)" xmlns="http://www.w3.org/2000/svg" size="24">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M2.879 3.879C2 4.757 2 6.172 2 9v6c0 2.828 0 4.243.879 5.121C3.757 21 5.172 21 8 21h10c.93 0 1.395 0 1.776-.102a3 3 0 0 0 2.122-2.122C22 18.395 22 17.93 22 17h-6a3 3 0 1 1 0-6h6V9c0-2.828 0-4.243-.879-5.121C20.243 3 18.828 3 16 3H8c-2.828 0-4.243 0-5.121.879ZM7 7a1 1 0 0 0 0 2h3a1 1 0 1 0 0-2H7Z" fill="var(--primary)"></path>
                        <path d="M17 14h-1" stroke="var(--primary)" stroke-width="2" stroke-linecap="round"></path>
                    </svg>
                    <span class="text-xs lg:text-sm text-caption px-2">Account Balance</span>
                    <button>
                        <svg width="20" height="20" viewbox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="12" cy="12" r="3.5" stroke="var(--caption)"></circle>
                            <path d="M20.188 10.934c.388.472.582.707.582 1.066 0 .359-.194.594-.582 1.066C18.768 14.79 15.636 18 12 18c-3.636 0-6.768-3.21-8.188-4.934-.388-.472-.582-.707-.582-1.066 0-.359.194-.594.582-1.066C5.232 9.21 8.364 6 12 6c3.636 0 6.768 3.21 8.188 4.934Z" stroke="var(--caption)"></path>
                        </svg>
                    </button>
                </article>
                <div class="w-full flex lg:gap-x-5">
                    <div class="w-full flex items-center">
                        <section class="w-full flex items-center h-7">
                            <span class="text-sm lg:text-base font-semibold">IDR&nbsp;<?php echo number_format($_SESSION['saldo_anggota'], 0, ',', '.'); ?></span>
                            <button class="rounded-full bg-background-default cursor-pointer rotate-270 w-7 h-7 ml-2 items-center justify-center flex">
                                <svg width="20" height="20" viewbox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="m10 19-.707-.707-.707.707.707.707L10 19Zm3.293-4.707-4 4 1.414 1.414 4-4-1.414-1.414Zm-4 5.414 4 4 1.414-1.414-4-4-1.414 1.414Z" fill="var(--caption)"></path>
                                    <path d="M5.938 15.5A7 7 0 1 1 12 19" stroke="var(--caption)" stroke-width="2" stroke-linecap="round"></path>
                                </svg>
                            </button>
                        </section>
                    </div>
                </div>
            </div>
            <div class="flex gap-x-4 px-4 pb-6 mt-5">
                <a class="w-full justify-center py-2 rounded-lg text-primary border border-primary transition-all duration-200 ease-in-out hover:lg:bg-background-tertiary" href="<?php echo $alamat_website . 'withdraw'; ?>">Withdraw</a>
                <a class="w-full justify-center py-2 rounded-lg bg-primary text-white transition-all duration-200 ease-in-out hover:lg:brightness-90" href="<?php echo $alamat_website . 'deposit'; ?>">Deposit</a>
            </div>
        </section>
        <section class="bg-background-secondary rounded-xl mt-4 overflow-hidden">
            <a class="justify-between px-4 py-3 hover:lg:bg-background-tertiary transition-all duration-300 ease-in-out" href="<?php echo $alamat_website . 'my-account'; ?>">
                <div class="flex items-center w-[calc(100%-24px)] pr-1">
                    <svg width="24" height="24" viewbox="0 0 24 24" fill="var(--primary)" xmlns="http://www.w3.org/2000/svg" size="24">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M2.002 10h19.996c-.012-2.175-.108-3.353-.877-4.121C20.243 5 18.828 5 16 5H8c-2.828 0-4.243 0-5.121.879-.769.768-.865 1.946-.877 4.121ZM22 12H2v2c0 2.828 0 4.243.879 5.121C3.757 20 5.172 20 8 20h8c2.828 0 4.243 0 5.121-.879C22 18.243 22 16.828 22 14v-2ZM7 15a1 1 0 1 0 0 2h.01a1 1 0 1 0 0-2H7Z" fill="var(--primary)"></path>
                    </svg>
                    <span class="text-sm pl-2 undefined text-primary">My Account</span>
                </div>
                <figure class="w-6"><svg width="22" height="22" viewbox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg" size="22">
                        <path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="var(--base)"></path>
                    </svg>
                </figure>
            </a>
        </section>
        <section class="bg-background-secondary rounded-xl mt-4 overflow-hidden">
            <a class="justify-between px-4 py-3 hover:lg:bg-background-tertiary transition-all duration-300 ease-in-out" href="<?php echo $alamat_website . 'change-pasword'; ?>">
                <div class="flex items-center w-[calc(100%-24px)] pr-1">
                    <svg width="24" height="24" viewbox="0 0 24 24" fill="var(--primary)" xmlns="http://www.w3.org/2000/svg" size="24">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M13.984 2.542c.087.169.109.386.152.82.082.82.123 1.23.295 1.456a1 1 0 0 0 .929.384c.28-.037.6-.298 1.238-.82.337-.277.506-.415.687-.473a1 1 0 0 1 .702.035c.175.076.33.23.637.538l.894.894c.308.308.462.462.538.637a1 1 0 0 1 .035.702c-.058.181-.196.35-.472.687-.523.639-.784.958-.822 1.239a1 1 0 0 0 .385.928c.225.172.636.213 1.457.295.433.043.65.065.82.152a1 1 0 0 1 .47.521c.071.177.071.395.071.831v1.264c0 .436 0 .654-.07.83a1 1 0 0 1-.472.522c-.169.087-.386.109-.82.152-.82.082-1.23.123-1.456.295a1 1 0 0 0-.384.929c.038.28.299.6.821 1.238.276.337.414.505.472.687a1 1 0 0 1-.035.702c-.076.175-.23.329-.538.637l-.894.893c-.308.309-.462.463-.637.538a1 1 0 0 1-.702.035c-.181-.058-.35-.196-.687-.472-.639-.522-.958-.783-1.238-.82a1 1 0 0 0-.929.384c-.172.225-.213.635-.295 1.456-.043.434-.065.651-.152.82a1 1 0 0 1-.521.472c-.177.07-.395.07-.831.07h-1.264c-.436 0-.654 0-.83-.07a1 1 0 0 1-.522-.472c-.087-.169-.109-.386-.152-.82-.082-.82-.123-1.23-.295-1.456a1 1 0 0 0-.928-.384c-.281.037-.6.298-1.239.82-.337.277-.506.415-.687.473a1 1 0 0 1-.702-.035c-.175-.076-.33-.23-.637-.538l-.894-.894c-.308-.308-.462-.462-.538-.637a1 1 0 0 1-.035-.702c.058-.181.196-.35.472-.687.523-.639.784-.958.821-1.239a1 1 0 0 0-.384-.928c-.225-.172-.636-.213-1.457-.295-.433-.043-.65-.065-.82-.152a1 1 0 0 1-.47-.521C2 13.286 2 13.068 2 12.632v-1.264c0-.436 0-.654.07-.83a1 1 0 0 1 .472-.522c.169-.087.386-.109.82-.152.82-.082 1.231-.123 1.456-.295a1 1 0 0 0 .385-.928c-.038-.281-.3-.6-.822-1.24-.276-.337-.414-.505-.472-.687a1 1 0 0 1 .035-.702c.076-.174.23-.329.538-.637l.894-.893c.308-.308.462-.463.637-.538a1 1 0 0 1 .702-.035c.181.058.35.196.687.472.639.522.958.783 1.238.821a1 1 0 0 0 .93-.385c.17-.225.212-.635.294-1.456.043-.433.065-.65.152-.82a1 1 0 0 1 .521-.471c.177-.07.395-.07.831-.07h1.264c.436 0 .654 0 .83.07a1 1 0 0 1 .522.472ZM12 16a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" fill="var(--primary)"></path>
                    </svg>
                    <span class="text-sm pl-2 undefined false">Account Settings</span>
                </div>
                <figure class="w-6"><svg width="22" height="22" viewbox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg" size="22">
                        <path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="var(--base)"></path>
                    </svg>
                </figure>
            </a>
        </section>
        <a href="<?php echo $alamat_website . 'logout'; ?>" class="block">
            <div class="bg-background-secondary rounded-xl mt-4 overflow-hidden">
                <section class="justify-between px-4 py-3 flex cursor-pointer hover:lg:bg-background-tertiary transition-all duration-300 ease-in-out " href="<?php echo $alamat_website . 'logout'; ?>">
                    <div class="flex items-center w-[calc(100%-24px)] pr-1">
                        <svg width="24" height="24" viewbox="0 0 24 24" fill="var(--primary)" xmlns="http://www.w3.org/2000/svg" size="24">
                            <path d="m2 12-.78-.625-.5.625.5.625L2 12Zm9 1a1 1 0 1 0 0-2v2ZM5.22 6.375l-4 5 1.56 1.25 4-5-1.56-1.25Zm-4 6.25 4 5 1.56-1.25-4-5-1.56 1.25ZM2 13h9v-2H2v2ZM13.342 20.557l.165-.986-.165.986Zm7.597.19.646.764-.646-.763ZM15.014 3.165l-.165-.986.165.986Zm5.925.088.646-.763-.646.763ZM13.507 4.43l1.671-.278-.329-1.973-1.671.279.329 1.972ZM21 9.083v5.834h2V9.083h-2Zm-5.822 10.766-1.671-.278-.329 1.973 1.671.278.329-1.973ZM11 8.132v-.743H9v.743h2Zm0 8.48v-.546H9v.546h2Zm2.507 2.959c-.824-.138-1.35-.227-1.734-.342-.358-.106-.472-.201-.536-.277l-1.526 1.293c.41.484.932.735 1.491.901.532.159 1.203.269 1.976.398l.329-1.973ZM9 16.61c0 .784-.002 1.464.067 2.015.072.578.234 1.135.644 1.619l1.526-1.293c-.064-.075-.14-.203-.185-.574-.05-.398-.052-.932-.052-1.767H9Zm12-1.694c0 1.675-.002 2.823-.123 3.67-.116.82-.32 1.174-.584 1.398l1.293 1.526c.797-.675 1.123-1.593 1.272-2.642.144-1.021.142-2.338.142-3.952h-2Zm-6.15 6.905c1.59.265 2.89.484 3.92.51 1.06.025 2.018-.146 2.816-.821l-1.293-1.526c-.264.223-.646.367-1.474.347-.856-.021-1.99-.207-3.641-.483l-.329 1.973Zm.328-17.671c1.652-.275 2.785-.462 3.64-.483.829-.02 1.21.124 1.475.347l1.293-1.526c-.797-.675-1.757-.846-2.816-.82-1.03.025-2.33.244-3.92.509l.328 1.973ZM23 9.083c0-1.614.002-2.93-.142-3.952-.15-1.049-.476-1.967-1.273-2.642l-1.292 1.526c.264.224.468.577.584 1.397.12.848.123 1.996.123 3.67h2Zm-9.822-6.626c-.773.128-1.444.238-1.976.397-.559.166-1.081.417-1.491.901l1.526 1.293c.064-.076.178-.17.536-.277.384-.115.91-.204 1.734-.342l-.329-1.972ZM11 7.389c0-.835.002-1.369.052-1.767.046-.37.121-.499.185-.574L9.711 3.755c-.41.484-.572 1.04-.644 1.62C8.998 5.924 9 6.604 9 7.388h2Z" fill="var(--primary)"></path>
                        </svg>
                        <span class="text-sm pl-2 undefined undefined">Logout</span>
                    </div>
                    <figure class="w-6"><svg width="22" height="22" viewbox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg" size="22">
                            <path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="var(--base)"></path>
                        </svg>
                    </figure>
                </section>
            </div>
            <!--$-->
            <!--/$-->
    </div>
    </a>


    <div class="w-full lg:w-2/3 lg:px-3 pb-20 md:pb-52">
		<div class="grid grid-cols-4 lg:mb-6 mt-4 lg:mt-0 pb-2 lg:pb-0 lg:gap-x-3 px-3 overflow-x-scroll">
<a aria-label="Transaksi-tab-button" aria-labelledby="Transaksi-tab-button" class="text-sm border-b uppercase flex-none px-1 pb-2 justify-center cursor-pointer transition-all duration-300 ease-in-out hover:lg:border-primary hover:lg:text-inverse font-semibold border-primary " href="/account/history?tab=transaction">Transaksi</a>
<a aria-label="Taruhan-tab-button" aria-labelledby="Taruhan-tab-button" class="text-sm border-b uppercase flex-none px-1 pb-2 justify-center cursor-pointer transition-all duration-300 ease-in-out hover:lg:border-primary hover:lg:text-inverse font-semibold border-transparent text-caption " href="/account/history?tab=game">Taruhan</a>
<a aria-label="Bonus-tab-button" aria-labelledby="Bonus-tab-button" class="text-sm border-b uppercase flex-none px-1 pb-2 justify-center cursor-pointer transition-all duration-300 ease-in-out hover:lg:border-primary hover:lg:text-inverse font-semibold border-transparent text-caption " href="/account/history?tab=bonus">Bonus</a>
<a aria-label="Lainnya-tab-button" aria-labelledby="Lainnya-tab-button" class="text-sm border-b uppercase flex-none px-1 pb-2 justify-center cursor-pointer transition-all duration-300 ease-in-out hover:lg:border-primary hover:lg:text-inverse font-semibold border-transparent text-caption " href="/account/history?tab=other">Lainnya</a>
</div>
        <!--$-->
        <section class="mt-3 lg:px-4">
			<div class="lg:bg-background-secondary rounded-xl lg:py-4">
				<div class="px-4 pb-4 border-b border-separator">
					<div class="lg:flex w-full">
						<div class="w-full lg:pl-2 lg:order-last">
<div class="form-group">
    <p class="text-xs lg:text-sm w-full mb-2">Periode</p>
    <div class="flex items-center w-full">
        <div class="border border-separator px-3 py-2 w-1/2 lg:w-full lg:h-11 justify-between rounded-lg">
            <input 
                class="bg-transparent text-sm w-full focus:outline-none border-none" 
                placeholder="Tanggal Mulai" 
                min="2024-09-16" 
                type="date">
        </div>
        <span class="text-sm px-2">-</span>
        <div class="border border-separator px-3 py-2 w-1/2 lg:w-full justify-between rounded-lg">
            <input 
                class="bg-transparent text-sm w-full focus:outline-none border-none" 
                placeholder="Tanggal Selesai" 
                type="date">
        </div>
    </div>
</div>

                        
                            <div class="w-full lg:pr-2">
                                <p class="text-xs lg:text-sm w-full mb-2 mt-3 lg:mt-0">Transaction Type</p>
                                <div class="relative">
                                    <div id="dropdown" class="border border-separator px-3 py-2 flex items-center w-full lg:h-11 justify-between rounded-lg cursor-pointer">
                                        <span class="text-sm">-- Select --</span>
                                        <figure class="h-auto w-5 rotate-90">
                                            <svg width="100%" height="100%" viewbox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg">
                                                <path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="var(--base)"></path>
                                            </svg>
                                        </figure>
                                    </div>
                                    <div id="dropdown-menu" class="absolute z-[9999] left-0 right-0 top-12 overflow-hidden bg-background-secondary rounded-xl transition-all duration-300 ease-in-out max-h-0">
                                        <div>
                                            <a href="history-deposit" class="text-sm border-b border-inverse py-3 block cursor-pointer hover:bg-background-default transition-all duration-300 ease-in-out">Deposit</a>
                                            <a href="history-withdraw" class="text-sm py-3 block cursor-pointer hover:bg-background-default transition-all duration-300 ease-in-out">Withdraw</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="px-4 mt-4">
                   <div class="flex w-full -mx-1">
<button aria-label="Today instant date filter" aria-labelledby="Today instant date filter" class="px-3 py-[6px] mx-1 rounded-full text-xs bg-primary font-semibold text-white">Hari ini</button>
<button aria-label="Yesterday instant date filter" aria-labelledby="Yesterday instant date filter" class="px-3 py-[6px] mx-1 rounded-full text-xs border border-separator text-separator transition-all duration-300 ease-in-out lg:hover:bg-primary lg:hover:border-primary">Kemarin</button>
<button aria-label="Last 30 days instant date filter" aria-labelledby="Last 30 days instant date filter" class="px-3 py-[6px] mx-1 rounded-full text-xs border border-separator text-separator transition-all duration-300 ease-in-out lg:hover:bg-primary lg:hover:border-primary">30 Hari Terakhir</button>
</div>
<div class="px-4 my-4" id="listContainer">
    
                        <?php
                        $withdraw = mysqli_query($koneksi, "SELECT * FROM withdraw WHERE id_anggota_withdraw = '$id_anggota'");
                        if (mysqli_num_rows($withdraw) >= 1) {
                            while ($data_withdraw = mysqli_fetch_array($withdraw)) {
                                $id_withdraw = $data_withdraw['id_withdraw'];
                                $kode_withdraw = $data_withdraw['kode_withdraw'];
                                $tujuan_withdraw = $data_withdraw['tujuan_withdraw'];
                                $jumlah_withdraw = $data_withdraw['jumlah_withdraw'];
                                $tanggal_withdraw = $data_withdraw['tanggal_withdraw'];
                                $status_withdraw = $data_withdraw['status_withdraw'];
                                $jumlah_withdraw_pendek = number_format($jumlah_withdraw / 1000, 2, '.', ',');
                                $jumlah_withdraw_panjang = number_format($jumlah_withdraw, 2, '.', ',');
                                $tanggal_withdraw_timestamp = strtotime($tanggal_withdraw);
                                $tanggal_withdraw_fix = date('d-M-Y H:i:s', $tanggal_withdraw_timestamp);
                                if ($status_withdraw == "diproses") {
                                    $status_withdraw_fix = "cf8300";
                                } else if ($status_withdraw == "dibatalkan") {
                                    $status_withdraw_fix = "c20000";
                                } else {
                                    $status_withdraw_fix = "1c9401";
                                }
                        ?>
                                <div class="mb-3">
                                    <div class="bg-background-tertiary flex items-center rounded-lg px-3 py-4 w-full justify-between">
                                        <div class="w-2/12 sm:w-1/12">
                                            <img alt="Bank Assets" loading="lazy" width="0" height="0" decoding="async" data-nimg="1" class="w-4/5 lg:w-full h-full mx-auto" src="assets/img/bank-logo-wd1.png" style="color: transparent;">
                                        </div>
                                        <div class="w-5/12 sm:w-6/12 pl-2 text-left">
                                            <p class="text-sm font-medium">Withdraw</p>
                                            <span class="text-xs"><?php echo $kode_withdraw; ?></span>
                                            <div class="text-xs mt-1"><?php echo $tanggal_withdraw_fix; ?></div> <!-- Menambahkan kode deposit di bawah tanggal -->
                                        </div>
                                        <div class="w-5/12 pr-1 text-right">
                                            <p class="text-sm font-medium">IDR&nbsp;<?php echo '' . number_format($jumlah_withdraw); ?></p>
                                            <span class="text-xs font-semibold text-success"><label style="color: #<?php echo $status_withdraw_fix; ?>;"><?php echo ucwords($status_withdraw); ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php
                            }
                        } else {
                            ?>
                            <img alt="Empty List" loading="lazy" width="0" height="0" decoding="async" data-nimg="1" class="w-20 mx-auto mt-16 lg:mt-16" src="https://cdn.databerjalan.com/cdn-cgi/image/width=auto,quality=75,fit=contain,format=auto//assets/images/static/v3/icon/empty-list.webp" style="color: transparent;" id="emptyListImage">
</div>
                            <p class="text-center mt-3">Anda belum memiliki riwayat transaksi</p>
<p class="text-xs lg:text-sm text-center mt-3 opacity-70"></p>
                        <?php
                        }
                        ?>
                   </div>
					</div>
</section>
				</div>
				
				</section>
                <script>
                    document.getElementById('dropdown').addEventListener('click', function() {
                        var menu = document.getElementById('dropdown-menu');
                        menu.style.maxHeight = menu.style.maxHeight === '0px' ? '100px' : '0px'; // Toggle dropdown visibility
                    });

                    document.querySelectorAll('#dropdown-menu p').forEach(function(option) {
                        option.addEventListener('click', function() {
                            var selectedText = this.textContent;
                            document.querySelector('#dropdown span').textContent = selectedText;
                            document.getElementById('dropdown-menu').style.maxHeight = '0px'; // Hide dropdown after selection
                            console.log('Selected value:', this.getAttribute('data-value')); // You can handle the selected value here
                        });
                    });
                </script>
                <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Mengambil referensi ke elemen
        const listContainer = document.getElementById('listContainer');
        const emptyListImage = document.getElementById('emptyListImage');

        // Cek apakah daftar kosong
        const isListEmpty = true; // Ganti dengan logika Anda untuk memeriksa apakah daftar kosong

        if (isListEmpty) {
            // Jika daftar kosong, tampilkan gambar
            emptyListImage.style.display = 'block';
        } else {
            // Jika tidak kosong, sembunyikan gambar
            emptyListImage.style.display = 'none';
        }
    });
</script>
<script>
// Mendapatkan elemen input tanggal
const startDateInput = document.querySelector('input[type="date"]:nth-of-type(1)');
const endDateInput = document.querySelector('input[type="date"]:nth-of-type(2)');

// Fungsi untuk mengupdate nilai maksimum tanggal akhir berdasarkan tanggal awal
function updateEndDate() {
    const startDate = new Date(startDateInput.value);
    
    // Jika tanggal awal tidak valid, set tanggal akhir menjadi tidak ada
    if (isNaN(startDate)) {
        endDateInput.value = '';
        endDateInput.min = '';
        return;
    }

    // Set nilai minimum untuk tanggal akhir
    endDateInput.min = startDate.toISOString().split('T')[0];
    
    // Jika tanggal akhir saat ini kurang dari tanggal minimum, set ke tanggal minimum
    if (endDateInput.value < endDateInput.min) {
        endDateInput.value = endDateInput.min;
    }
}

// Event listener untuk memperbarui tanggal akhir ketika tanggal awal berubah
startDateInput.addEventListener('change', updateEndDate);
</script>

                <style>
                    #dropdown-menu {
                        padding: 0;
                    }

                    #dropdown-menu div {
                        padding: 0;
                    }

                    #dropdown-menu a {
                        padding: 10px 15px;
                        display: block;
                    }

                    #dropdown-menu a:hover {
                        background-color: #333846;
                    }
                </style>
                
                 <?php include_once 'footer.php'; ?>