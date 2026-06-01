<?php
include_once 'header.php'; 

// Memeriksa flag di sesi
if (empty($_SESSION['valid_navigation'])) {
    // Jika flag tidak ada, arahkan ke halaman error dengan JavaScript
    echo '
    <script>
        window.location.replace("home");
    </script>
    ';
    exit();
}

// Menghapus flag setelah validasi
unset($_SESSION['valid_navigation']);
?>
<div id="notification" class="fixed z-[9999] px-4 pt-3 pb-5 top-3 sm:top-4 sm:right-6 left-3 right-3 sm:ml-auto sm:w-2/3 md:w-1/2 lg:w-[410px] rounded-xl bg-gradient-to-r from-[#007148] to-background-secondary to-50%">
    <button id="close-btn" class="h-6 w-6 ml-auto absolute right-3 top-2 z-50">
        <svg width="100%" height="100%" viewBox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg">
            <path d="M18 6 6 18M6 6l12 12" stroke="var(--base)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
        </svg>
    </button>
    <div class="flex items-center">
        <figure class="flex-none h-12 w-12">
				<div title="" role="button" aria-label="animation" tabindex="0" style="width: 100%; height: 100%; overflow: hidden; margin: 0px auto; outline: none;">
					<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 512 512" width="512" height="512" preserveAspectRatio="xMidYMid meet" style="width: 100%; height: 100%; transform: translate3d(0px, 0px, 0px); content-visibility: visible;">
						<defs>
							<clipPath id="__lottie_element_13">
								<rect width="512" height="512" x="0" y="0"></rect>
							</clipPath>
							<clipPath id="__lottie_element_15">
								<path d="M0,0 L512,0 L512,512 L0,512z"></path>
							</clipPath>
							<clipPath id="__lottie_element_22">
								<path d="M0,0 L512,0 L512,512 L0,512z"></path>
							</clipPath>
							<clipPath id="__lottie_element_29">
								<path d="M0,0 L512,0 L512,512 L0,512z"></path>
							</clipPath>
							<clipPath id="__lottie_element_36">
								<path d="M0,0 L512,0 L512,512 L0,512z"></path>
							</clipPath>
							<clipPath id="__lottie_element_43">
								<path d="M0,0 L512,0 L512,512 L0,512z"></path>
							</clipPath>
							<clipPath id="__lottie_element_50">
								<path d="M0,0 L512,0 L512,512 L0,512z"></path>
							</clipPath>
							<clipPath id="__lottie_element_57">
								<path d="M0,0 L512,0 L512,512 L0,512z"></path>
							</clipPath>
							<clipPath id="__lottie_element_64">
								<path d="M0,0 L512,0 L512,512 L0,512z"></path>
							</clipPath>
							<clipPath id="__lottie_element_71">
								<path d="M0,0 L512,0 L512,512 L0,512z"></path>
							</clipPath>
							<clipPath id="__lottie_element_78">
								<path d="M0,0 L512,0 L512,512 L0,512z"></path>
							</clipPath>
							<clipPath id="__lottie_element_85">
								<path d="M0,0 L512,0 L512,512 L0,512z"></path>
							</clipPath>
						</defs>
						<g clip-path="url(#__lottie_element_13)">
							<g transform="matrix(0.800000011920929,0,0,0.800000011920929,256,256)" opacity="1" style="display: block;">
								<g opacity="1" transform="matrix(1,0,0,1,5.992000102996826,3.490000009536743)">
									<path fill="rgb(84,190,64)" fill-opacity="1" d=" M0,-200.5124969482422 C110.66284942626953,-200.5124969482422 200.5124969482422,-110.66284942626953 200.5124969482422,0 C200.5124969482422,110.66284942626953 110.66284942626953,200.5124969482422 0,200.5124969482422 C-110.66284942626953,200.5124969482422 -200.5124969482422,110.66284942626953 -200.5124969482422,0 C-200.5124969482422,-110.66284942626953 -110.66284942626953,-200.5124969482422 0,-200.5124969482422z"></path>
								</g>
							</g>
							<g transform="matrix(0.695900022983551,0,0,0.695900022983551,236.88800048828125,240.25799560546875)" opacity="1" style="display: block;">
								<g opacity="1" transform="matrix(1,0,0,1,-7,11)">
									<path stroke-linecap="round" stroke-linejoin="miter" fill-opacity="0" stroke-miterlimit="4" stroke="rgb(255,255,255)" stroke-opacity="1" stroke-width="35" d=" M-76.4260025024414,37.999000549316406 C-76.4260025024414,37.999000549316406 12.055999755859375,114.0739974975586 12.055999755859375,114.0739974975586 C12.055999755859375,114.0739974975586 169.99099731445312,-68.63500213623047 169.99099731445312,-68.63500213623047"></path>
								</g>
							</g>
							<g clip-path="url(#__lottie_element_85)" transform="matrix(0.13650371134281158,-0.139422208070755,0.139422208070755,0.13650371134281158,346.0849609375,113.95313262939453)" opacity="1" style="display: block;">
								<g transform="matrix(1,0,0,1,256,256)" opacity="0.3" style="display: block;">
									<g opacity="1" transform="matrix(1,0,0,1,0,0)">
										<path stroke-linecap="round" stroke-linejoin="miter" fill-opacity="0" stroke-miterlimit="4" stroke="rgb(84,190,64)" stroke-opacity="1" stroke-width="70" d="M0 0"></path>
									</g>
								</g>
							</g>
							<g clip-path="url(#__lottie_element_78)" transform="matrix(-0.13846343755722046,-0.1374761462211609,0.1374761462211609,-0.13846343755722046,105.11674499511719,182.35052490234375)" opacity="1" style="display: block;">
								<g transform="matrix(1,0,0,1,256,256)" opacity="0.3" style="display: block;">
									<g opacity="1" transform="matrix(1,0,0,1,0,0)">
										<path stroke-linecap="round" stroke-linejoin="miter" fill-opacity="0" stroke-miterlimit="4" stroke="rgb(84,190,64)" stroke-opacity="1" stroke-width="70" d="M0 0"></path>
									</g>
								</g>
							</g>
							<g clip-path="url(#__lottie_element_71)" transform="matrix(-0.13650371134281158,0.139422208070755,0.139422208070755,0.13650371134281158,104.78787231445312,331.9609375)" opacity="1" style="display: block;">
								<g transform="matrix(1,0,0,1,256,256)" opacity="0.3" style="display: block;">
									<g opacity="1" transform="matrix(1,0,0,1,0,0)">
										<path stroke-linecap="round" stroke-linejoin="miter" fill-opacity="0" stroke-miterlimit="4" stroke="rgb(84,190,64)" stroke-opacity="1" stroke-width="70" d="M0 0"></path>
									</g>
								</g>
							</g>
							<g clip-path="url(#__lottie_element_64)" transform="matrix(0.13846343755722046,0.1374761462211609,0.1374761462211609,-0.13846343755722046,340.2244567871094,406.7827453613281)" opacity="1" style="display: block;">
								<g transform="matrix(1,0,0,1,256,256)" opacity="0.3" style="display: block;">
									<g opacity="1" transform="matrix(1,0,0,1,0,0)">
										<path stroke-linecap="round" stroke-linejoin="miter" fill-opacity="0" stroke-miterlimit="4" stroke="rgb(84,190,64)" stroke-opacity="1" stroke-width="70" d="M0 0">

										</path>
									</g>
								</g>
							</g>
							<g clip-path="url(#__lottie_element_57)" transform="matrix(0.2094983607530594,-0.21473342180252075,0.21473342180252075,0.2094983607530594,288.2906799316406,152.30117797851562)" opacity="1" style="display: block;">
								<g transform="matrix(1,0,0,1,256,256)" opacity="1" style="display: block;">
									<g opacity="0.29051567756888974" transform="matrix(1,0,0,1,174.07814025878906,-5.414000034332275)">
										<path fill="rgb(255,255,255)" fill-opacity="1" d=" M0,-23.14451026916504 C12.773455619812012,-23.14451026916504 23.14451026916504,-12.773455619812012 23.14451026916504,0 C23.14451026916504,12.773455619812012 12.773455619812012,23.14451026916504 0,23.14451026916504 C-12.773455619812012,23.14451026916504 -23.14451026916504,12.773455619812012 -23.14451026916504,0 C-23.14451026916504,-12.773455619812012 -12.773455619812012,-23.14451026916504 0,-23.14451026916504z"></path>
									</g>
								</g>
							</g>
							<g clip-path="url(#__lottie_element_50)" transform="matrix(0.06023769825696945,-0.19071292877197266,0.19071292877197266,0.06023769825696945,295.16961669921875,184.31365966796875)" opacity="1" style="display: block;">
								<g transform="matrix(1,0,0,1,256,256)" opacity="1" style="display: block;">
									<g opacity="0.29051567756888974" transform="matrix(1,0,0,1,174.07814025878906,-5.414000034332275)">
										<path fill="rgb(255,255,255)" fill-opacity="1" d=" M0,-23.14451026916504 C12.773455619812012,-23.14451026916504 23.14451026916504,-12.773455619812012 23.14451026916504,0 C23.14451026916504,12.773455619812012 12.773455619812012,23.14451026916504 0,23.14451026916504 C-12.773455619812012,23.14451026916504 -23.14451026916504,12.773455619812012 -23.14451026916504,0 C-23.14451026916504,-12.773455619812012 -12.773455619812012,-23.14451026916504 0,-23.14451026916504z"></path>
									</g>
								</g>
							</g>
							<g clip-path="url(#__lottie_element_43)" transform="matrix(0.19010640680789948,-0.06212533265352249,0.06212533265352249,0.19010640680789948,336.0636901855469,156.94483947753906)" opacity="1" style="display: block;">
								<g transform="matrix(1,0,0,1,256,256)" opacity="1" style="display: block;">
									<g opacity="0.29051567756888974" transform="matrix(1,0,0,1,174.07814025878906,-5.414000034332275)">
										<path fill="rgb(255,255,255)" fill-opacity="1" d=" M0,-23.14451026916504 C12.773455619812012,-23.14451026916504 23.14451026916504,-12.773455619812012 23.14451026916504,0 C23.14451026916504,12.773455619812012 12.773455619812012,23.14451026916504 0,23.14451026916504 C-12.773455619812012,23.14451026916504 -23.14451026916504,12.773455619812012 -23.14451026916504,0 C-23.14451026916504,-12.773455619812012 -12.773455619812012,-23.14451026916504 0,-23.14451026916504z"></path>
									</g>
								</g>
							</g>
							<g clip-path="url(#__lottie_element_36)" transform="matrix(0.15000000596046448,0,0,0.15000000596046448,431.510009765625,220.39199829101562)" opacity="1" style="display: block;">
								<g transform="matrix(1,0,0,1,256,256)" opacity="0.3" style="display: block;">
									<g opacity="1" transform="matrix(1,0,0,1,0,0)">
										<path stroke-linecap="round" stroke-linejoin="miter" fill-opacity="0" stroke-miterlimit="4" stroke="rgb(84,190,64)" stroke-opacity="1" stroke-width="70" d="M0 0">

										</path>
									</g>
								</g>
							</g>
							<g clip-path="url(#__lottie_element_29)" transform="matrix(0,-0.15000000596046448,0.15000000596046448,0,227.9219970703125,82.71499633789062)" opacity="1" style="display: block;">
								<g transform="matrix(1,0,0,1,256,256)" opacity="0.3" style="display: block;">
									<g opacity="1" transform="matrix(1,0,0,1,0,0)">
										<path stroke-linecap="round" stroke-linejoin="miter" fill-opacity="0" stroke-miterlimit="4" stroke="rgb(84,190,64)" stroke-opacity="1" stroke-width="70" d="M0 0"></path>
									</g>
								</g>
							</g>
							<g clip-path="url(#__lottie_element_22)" transform="matrix(-0.15000000596046448,0,0,-0.15000000596046448,90.04100036621094,291.67498779296875)" opacity="1" style="display: block;">
								<g transform="matrix(1,0,0,1,256,256)" opacity="0.3" style="display: block;">
									<g opacity="1" transform="matrix(1,0,0,1,0,0)">
										<path stroke-linecap="round" stroke-linejoin="miter" fill-opacity="0" stroke-miterlimit="4" stroke="rgb(84,190,64)" stroke-opacity="1" stroke-width="70" d="M0 0"></path>
									</g>
								</g>
							</g>
							<g clip-path="url(#__lottie_element_15)" transform="matrix(0,0.15000000596046448,-0.15000000596046448,0,301.7340087890625,432.7090148925781)" opacity="1" style="display: block;">
								<g transform="matrix(1,0,0,1,256,256)" opacity="0.3" style="display: block;">
									<g opacity="1" transform="matrix(1,0,0,1,0,0)">
										<path stroke-linecap="round" stroke-linejoin="miter" fill-opacity="0" stroke-miterlimit="4" stroke="rgb(84,190,64)" stroke-opacity="1" stroke-width="70" d="M0 0"></path>
									</g>
								</g>
							</g>
						</g>
					</svg>
				</div>
			</figure>
			<article class="pl-3">
				<p class="font-medium">Withdraw</p>
				<p class="text-xs mt-1">Your Withdraw request has been processed</p>
			</article>
		</div>
	</div>
	<script>
        document.addEventListener('DOMContentLoaded', () => {
    const notification = document.getElementById('notification');
    const closeButton = document.getElementById('close-btn');

    // Tampilkan notifikasi
    notification.classList.add('show');

    // Menyembunyikan notifikasi setelah 2 detik jika tombol close tidak ditekan
    setTimeout(() => {
        if (notification.classList.contains('show')) {
            notification.classList.remove('show');
            notification.classList.add('hide');
            // Mengganti halaman setelah notifikasi disembunyikan
            setTimeout(() => {
                window.location.replace("<?php echo $alamat_website . 'withdraw'; ?>"); // Ganti dengan URL yang sesuai
            }, 300); // Delay tambahan untuk memastikan animasi selesai
        }
    }, 2000);

    // Menutup notifikasi secara manual dan mengarahkan ke halaman
    if (closeButton) {
        closeButton.addEventListener('click', () => {
            notification.classList.remove('show');
            notification.classList.add('hide');
            setTimeout(() => {
                window.location.replace("<?php echo $alamat_website . 'withdraw'; ?>"); // Ganti dengan URL yang sesuai
            }, 300); // Delay tambahan untuk memastikan animasi selesai
        });
    }
});
    </script>
	<script>
    document.getElementById('close-btn').addEventListener('click', function() {
        document.getElementById('notification').style.display = 'none';
    });
</script>
	<?php
include_once 'home.php'; ?>
