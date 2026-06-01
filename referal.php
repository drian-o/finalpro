<?php
include_once 'header.php';
include_once 'koneksi.php'; // Ensure connection to the database
// Get the member's username from the session
if (!isset($_SESSION)) { session_start(); }
	include_once 'koneksi.php';
	$nama_pengguna = isset($_SESSION['nama_pengguna_anggota']) ? $_SESSION['nama_pengguna_anggota'] : '';
$nama_anggota = $_SESSION['nama_pengguna_anggota'];

// Prepare and execute the query to fetch KYC status
$stmt = $koneksi->prepare("SELECT kyc_status FROM anggota WHERE nama_pengguna_anggota = ?");
$stmt->bind_param("s", $nama_anggota);
$stmt->execute();
$result = $stmt->get_result();

$isKYCVerified = ''; // Default value
$statusMessage = ''; // Initialize status message

// Check if the query returned a result
if ($result && $result->num_rows === 1) {
    $user = $result->fetch_assoc();
    // Set isKYCVerified based on kyc_status
    if ($user['kyc_status'] == 1) {
        $isKYCVerified = true;
        $statusMessage = 'KYC Anda sudah terverifikasi.';
    } elseif ($user['kyc_status'] == 2) {
        $isKYCVerified = false;
        $statusMessage = 'KYC Anda sedang diproses.';
    } else {
        $isKYCVerified = false;
        $statusMessage = 'KYC Anda belum terverifikasi.';
    }
} else {
    $statusMessage = 'Pengguna tidak ditemukan.';
}
?>
<div class="container mx-auto mt-3">
    <div class="px-4 mt-5 flex"><nav class="flex mb-1 lg:mb-2">
        <ol class="flex items-center pb-1 overflow-x-scroll whitespace-nowrap opacity-scroll">
            <li class="inline-flex items-end pr-1">
                <a class="text-xs border-b border-transparent hover:lg:border-primary transition-all duration-200 ease-in-out undefined" href="home">Beranda</a>
                </li>
                <li class="inline-flex items-end pr-1 group">
                    <div class="flex items-center">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg" size="17">
                        <path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="var(--base)"></path>
                        </svg>
                        <a class="text-xs pl-1 border-b border-transparent hover:lg:border-primary transition-all duration-200 ease-in-out group-last:text-primary undefined" href="referal">Undang Teman</a>
                        </div>
                        </li>
                         </ol>
                                    </nav>
                                    </div>
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
		<section class="bg-background-secondary rounded-xl mt-4 overflow-hidden"><a class="justify-between px-4 py-3 hover:lg:bg-background-tertiary transition-all duration-300 ease-in-out" href="<?php echo $alamat_website . 'my-account'; ?>">
				<div class="flex items-center w-[calc(100%-24px)] pr-1">
					<svg width="24" height="24" viewbox="0 0 24 24" fill="var(--primary)" xmlns="http://www.w3.org/2000/svg" size="24">
						<path fill-rule="evenodd" clip-rule="evenodd" d="M2.002 10h19.996c-.012-2.175-.108-3.353-.877-4.121C20.243 5 18.828 5 16 5H8c-2.828 0-4.243 0-5.121.879-.769.768-.865 1.946-.877 4.121ZM22 12H2v2c0 2.828 0 4.243.879 5.121C3.757 20 5.172 20 8 20h8c2.828 0 4.243 0 5.121-.879C22 18.243 22 16.828 22 14v-2ZM7 15a1 1 0 1 0 0 2h.01a1 1 0 1 0 0-2H7Z" fill="var(--primary)"></path>
					</svg>
					<span class="text-sm pl-2 undefined text-primary">My Account</span>
				</div>
				<figure class="w-6"><svg width="22" height="22" viewbox="0 0 24 24" fill="var(--primary)" xmlns="http://www.w3.org/2000/svg" size="22">
						<path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="var(--primary)"></path>
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


<?php if ($isKYCVerified): ?>
    <div class="w-full lg:w-2/3 lg:px-4">
            <div class="lg:hidden sm:absolute sm:left-0 sm:right-0 sm:top-0 sm:h-screen bg-primary"></div>
            <div class="lg:hidden sm:absolute sm:left-0 sm:right-0 sm:top-0 sm:h-screen bg-black/30"></div>
            <div class="lg:mx-0 lg:rounded-xl lg:overflow-hidden px-4 lg:px-0 pt-5 pb-10 lg:pb-0 relative">
                <div class="flex flex-wrap lg:items-center lg:px-3">
                    <div class="absolute top-0 left-0 right-0 -bottom-4 lg:-bottom-32 z-[1] bg-primary"></div>
                    <div class="absolute top-0 left-0 right-0 -bottom-4 lg:-bottom-32 z-[2] bg-black/30"></div>
                    <div class="w-full lg:w-5/12 lg:order-last relative z-10 pt-5 lg:pt-0">
                        <img alt="Referral Banner" loading="lazy" class="h-40 lg:h-36 w-auto mx-auto" src="https://cdn.databerjalan.com/cdn-cgi/image/width=auto,quality=75,fit=contain,format=auto/assets/images/static/v3/banner/referraltop.png">
                    </div>
                    <article class="w-full md:w-10/12 lg:w-7/12 md:mx-auto px-4 lg:px-6 relative z-10">
                        <p class="text-xl lg:text-2xl font-semibold text-center lg:text-left mt-4 text-white">Undang Teman, Dapat Bonus</p>
                        <p class="text-xs text-center lg:text-left lg:text-sm lg:mt-4 mt-2 opacity-80 text-white lg:px-0">Dapatkan keuntungan bonus tiap kali mengundang teman. Makin banyak teman yang diundang, makin banyak juga bonus yang akan didapat!</p>
                        <div class="w-full lg:w-7/12 relative z-10 mt-3 lg:mt-4"></div>
                    </article>
                    <div class="relative z-10 mt-4 lg:mt-8 w-full">
                        <div class="px-4 py-8 lg:w-full relative z-30 lg:py-8 lg:rounded-none mt-8 lg:mt-0 mb-20 lg:mb-0 bg-background-default lg:bg-background-secondary rounded-xl">
                            <article>
                                <p class="text-xs mb-1">Referral URL</p>
<p class="font-semibold" id="referral-url"><?php echo $alamat_website; ?>auth-register?refferal=<?php echo $_SESSION['nama_pengguna_anggota']; ?></p>
<button id="copy-button" onclick="copyToClipboard()" aria-label="Salin link referal">
  [salin link]  📋
</button>
</article>
                        </div>
                    </div>
                </div>
            </div>
        </div>
<?php elseif ($user['kyc_status'] == 2): ?>
    <div class="w-full lg:w-2/3 lg:px-4">
                    <div class="lg:hidden sm:absolute sm:left-0 sm:right-0 sm:top-0 sm:h-screen bg-primary"></div>
                    <div class="lg:hidden sm:absolute sm:left-0 sm:right-0 sm:top-0 sm:h-screen bg-black/30"></div>
                    <div class="lg:mx-0 lg:rounded-xl lg:overflow-hidden px-4 lg:px-0 pt-5 pb-10 lg:pb-0 relative">
                        <div class="flex flex-wrap lg:items-center lg:px-3">
                            <div class="absolute top-0 left-0 right-0 -bottom-4 lg:-bottom-32 z-[1] bg-primary"></div>
                            <div class="absolute top-0 left-0 right-0 -bottom-4 lg:-bottom-32 z-[2] bg-black/30"></div>
                            <div class="w-full lg:w-5/12 lg:order-last relative z-10 pt-5 lg:pt-0">
                                <img alt="Referral Banner" loading="lazy" width="0" height="0" decoding="async" data-nimg="1" class="h-40 lg:h-36 w-auto mx-auto" style="color:transparent" src="https://cdn.databerjalan.com/cdn-cgi/image/width=auto,quality=75,fit=contain,format=auto/assets/images/static/v3/banner/referraltop.png"/>
                            </div>
                            <article class="w-full md:w-10/12 lg:w-7/12 md:mx-auto px-4 lg:px-6 relative z-10">
                                <p class="text-xl lg:text-2xl font-semibold text-center lg:text-left mt-4 text-white">Undang Teman, Dapat Bonus</p>
                                <p class="text-xs text-center lg:text-left lg:text-sm lg:mt-4 mt-2 opacity-80 text-white lg:px-0">Dapatkan keuntungan bonus tiap kali mengundang teman. Makin banyak teman yang diundang, makin banyak juga bonus yang akan didapat!</p>
                                <div class="w-full lg:w-7/12 relative z-10 mt-3 lg:mt-4">
                                    <a class="justify-center lg:justify-start" href="details">
                                        <span class="text-sm transition-all duration-200 ease-in-out border-b border-white text-white">Detail Peraturan</span>
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="#FFFFFF" xmlns="http://www.w3.org/2000/svg" size="22">
                                            <path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="#FFFFFF"></path>
                                        </svg>
                                    </a>
                                </div>
                            </article>
                        </div>
<div class="relative z-10 mt-4 lg:mt-8 w-full ">
<div class="px-4 py-8 lg:w-full relative z-30 lg:py-8 lg:rounded-none mt-8 lg:mt-0 mb-20 lg:mb-0 bg-background-default lg:bg-background-secondary rounded-xl">
<div class="lg:w-3/5 px-4 w-full lg:mx-auto relative">
<figure class="w-12 mx-auto">
<img alt="KYC Verification" loading="lazy" width="0" height="0" decoding="async" data-nimg="1" class="w-full h-auto mx-auto" src="data:image/webp;base64,UklGRnoFAABXRUJQVlA4WAoAAAAwAAAAXwAAXwAASUNDUMgBAAAAAAHIAAAAAAQwAABtbnRyUkdCIFhZWiAH4AABAAEAAAAAAABhY3NwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAQAA9tYAAQAAAADTLQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAlkZXNjAAAA8AAAACRyWFlaAAABFAAAABRnWFlaAAABKAAAABRiWFlaAAABPAAAABR3dHB0AAABUAAAABRyVFJDAAABZAAAAChnVFJDAAABZAAAAChiVFJDAAABZAAAAChjcHJ0AAABjAAAADxtbHVjAAAAAAAAAAEAAAAMZW5VUwAAAAgAAAAcAHMAUgBHAEJYWVogAAAAAAAAb6IAADj1AAADkFhZWiAAAAAAAABimQAAt4UAABjaWFlaIAAAAAAAACSgAAAPhAAAts9YWVogAAAAAAAA9tYAAQAAAADTLXBhcmEAAAAAAAQAAAACZmYAAPKnAAANWQAAE9AAAApbAAAAAAAAAABtbHVjAAAAAAAAAAEAAAAMZW5VUwAAACAAAAAcAEcAbwBvAGcAbABlACAASQBuAGMALgAgADIAMAAxADZBTFBIuwEAAAGQVmtv3kiCYAiCYAiGYAY1gwmDmsGYQcpgl4EhNAwEQRC03xu/r8/8jogJkBvz9CD1qYJYg7oCqHF5WteC/Fg32M7tjXWFra6TN5cJoBrTpQgibXybDNd5VsHtDIdAN4b2tagMFUsZClZiSFjieC7gE2+iDbyB1vAqWsJTNJlob4HvaAOvoGU8mVhvITywGkNyKGWQjnQKZUJSDhk4p5Amh1EW6ShdeA3DhLhgFCYZCF245zoTcrVVpmySfY1n4W9rquywrWiyx3Zfk11Wv8er7FPtDsuyU53/N1U22//nkP2q/cuVZcvN/uSH7Fp7RHhPsu309Ig4dVsfHn+0tqc84x9NN/QR//ncjc7476lbyRY3mm6keNzqdRuPuP2xiUcsfGyhxtLHBtTXeKZTi8WmbFcsn2TPAPyk0oAsTIZhiecZoJ1GA9UTywsmPkk0gJXjhdQpNJA9MTSo6AyG9SbIAV7wBlrHe6NNuBTwCa3iVbSBN9Am3nc0xzOwFIQJKzNkrMJQsRpD+1p0hg5Uz3kxXHM0DL2C2BTBgvoNUIK8rBtsY92L7Vx3sLV1yblM10nhqoKok8ZnlhsBAFZQOCDIAQAA8BAAnQEqYABgAD6NPJhHpSOioS1YjVCgEYliANJuGUAP69vocmt6m3N6p9tT5jPOqfSv0x+SsLe0QC2MmfDcJTq1hIK7y/wZRrZCkwosHt20nu2vIUeFsnUrN6GEfrFnA9JGHFR1WF2gdXazREplP2HDKhxOLKID2mFT0BJ7CBsqf+AJzd85xprOremtFSBAAP78XWjnMMjcRFuZINW/86M/xZxS/DUQ0Z9p/M9i4FrY+iUchzQGz8pjioGyuJKeGGk+kWBiL3/RK6hyEE4xw6YG6i2ojlvFX+n/lF0nxr1E0UlH+/mHHTCAXQv1mBft+4aXP80oSUgcC0EbcgIc8FvSXNh0npXb/Xu/xpAnPf2guqWJvqJb7lg5Rh3OYr5/f1GJ4egbf/488AQ//xxA4kC3C9Kpw8oMUY67r3qAcXkL2A0EIPQG9QrP/jidM1LpinnEp8i5r8INLnRBluqvVc3+m28KMG1mWpwBbRgqZ9xTBsRF6fOmSuvAG5gvaxX8JI13bTLU8SJqJZWU0WVsd/k1alfPEXV8CoNRgEEy1f7/HYLOYccvborU82rukuU9Oz1BLvn0kKERXfh19sQwP8Zx2CemOMAA" style="color: transparent;">
</figure>
<p class="mt-4 text-center font-semibold">Verifikasi dokumen Anda sedang dalam proses</p>
<p class="text-sm opacity-70 mt-2 font-light text-center">Mohon tunggu! Silahkan menunggu kabar dari kami. Layanan referral akan segera Anda nikmati.</p></div>
<div class="mt-4">
<p class="text-sm text-center font-light opacity-70">supported by</p>
<img alt="ASP Image" fetchpriority="low" loading="lazy" width="0" height="0" decoding="async" data-nimg="1" class="w-28 lg:w-32 mx-auto mt-3" src="https://cdn.databerjalan.com/cdn-cgi/image/width=auto,quality=75,fit=contain,format=auto//assets/images/static/v3/footer/play-responsibility/asf.webp" style="color: transparent;">
</div>
</div>
</div>
<?php else: ?>
    <div class="w-full lg:w-2/3 lg:px-4">
            <div class="lg:hidden sm:absolute sm:left-0 sm:right-0 sm:top-0 sm:h-screen bg-primary"></div>
            <div class="lg:hidden sm:absolute sm:left-0 sm:right-0 sm:top-0 sm:h-screen bg-black/30"></div>
            <div class="lg:mx-0 lg:rounded-xl lg:overflow-hidden px-4 lg:px-0 pt-5 pb-10 lg:pb-0 relative">
                <div class="flex flex-wrap lg:items-center lg:px-3">
                    <div class="absolute top-0 left-0 right-0 -bottom-4 lg:-bottom-32 z-[1] bg-primary"></div>
                    <div class="absolute top-0 left-0 right-0 -bottom-4 lg:-bottom-32 z-[2] bg-black/30"></div>
                    <div class="w-full lg:w-5/12 lg:order-last relative z-10 pt-5 lg:pt-0">
                        <img alt="Referral Banner" loading="lazy" class="h-40 lg:h-36 w-auto mx-auto" src="https://cdn.databerjalan.com/cdn-cgi/image/width=auto,quality=75,fit=contain,format=auto/assets/images/static/v3/banner/referraltop.png" style="color: transparent;">
                    </div>
                    <article class="w-full md:w-10/12 lg:w-7/12 md:mx-auto px-4 lg:px-6 relative z-10">
                        <p class="text-xl lg:text-2xl font-semibold text-center lg:text-left mt-4 text-white">Undang Teman, Dapat Bonus</p>
                        <p class="text-xs text-center lg:text-left lg:text-sm lg:mt-4 mt-2 opacity-80 text-white lg:px-0">Dapatkan keuntungan bonus tiap kali mengundang teman. Makin banyak teman yang diundang, makin banyak juga bonus yang akan didapat</p>
                        <div class="w-full lg:w-7/12 relative z-10 mt-3 lg:mt-4">
                            <a class="justify-center lg:justify-start" href="details">
                                <span class="text-sm transition-all duration-200 ease-in-out border-b border-white text-white">Detail Peraturan</span>
                                <svg width="22" height="22" viewbox="0 0 24 24" fill="#FFFFFF" xmlns="http://www.w3.org/2000/svg" size="22">
                                    <path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="#FFFFFF"></path>
                                </svg>
                            </a>
                        </div>
                    </article>
                </div>
                <div class="relative z-10 mt-4 lg:mt-8 w-full">
                    <div class="px-4 py-8 lg:w-full relative z-30 lg:py-8 lg:rounded-none mt-8 lg:mt-0 mb-20 lg:mb-0 bg-background-default lg:bg-background-secondary rounded-xl">
                        <div class="lg:w-3/5 px-4 w-full lg:mx-auto relative">
                            <figure class="w-12 mx-auto">
                                <img alt="KYC Verification" loading="lazy" class="w-full h-auto mx-auto" src="data:image/webp;base64,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" style="color: transparent;">
                            </figure>
                            <p class="mt-4 text-center font-semibold">Verify Your Account</p>
                            <p class="text-sm opacity-70 mt-2 font-light text-center">Verify your email, cellphone number and identity documents</p>
                            <a class="bg-primary justify-center py-2 mt-4 rounded-lg hover:lg:brightness-90 transition-all duration-300 ease-in-out" href="kyc">Verify Now</a>
                        </div>
                        <div class="mt-4">
                            <p class="text-sm text-center font-light opacity-70">supported by</p>
                            <img alt="ASP Image" fetchpriority="low" loading="lazy" class="w-28 lg:w-32 mx-auto mt-3" src="https://cdn.databerjalan.com/cdn-cgi/image/width=auto,quality=75,fit=contain,format=auto//assets/images/static/v3/footer/play-responsibility/asf.webp" style="color: transparent;">
                        </div>
                    </div>
                </div>
<?php endif; ?>
   </div>
    </div>
    </section>
<script>
function copyToClipboard() {
    const referralUrl = document.getElementById('referral-url').innerText;
    navigator.clipboard.writeText(referralUrl).then(() => {
        alert('Link referal telah disalin ke clipboard!');
    }).catch(err => {
        console.error('Gagal menyalin: ', err);
    });
}
</script>
<?php include_once 'footer.php'; ?>