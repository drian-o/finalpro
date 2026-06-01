<?php
include_once 'koneksi.php';
include_once 'classes/class.exa.php';
require_once 'functions_telegram.php';
include_once 'header.php';

$bank_anggota_aktif = '';
$nomor_rekening_anggota_aktif = '';
$nama_rekening_anggota = '';
$nama_pengguna_anggota = '';
$nomor_rekening_anggota_aktif_sensored = '';
$id_anggota_aktif = null;
$id_sigma_anggota = null;

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    $id_anggota = $_SESSION['id_anggota'];
    $username_session = $_SESSION['nama_pengguna_anggota'];
} else {
    echo '<script>window.location.replace("' . $alamat_website . 'home");</script>';
    exit();
}

if (isset($_SESSION['id_anggota'])) {
    $id_anggota_aktif = $_SESSION['id_anggota'];
    
    $query_anggota_data = mysqli_prepare($koneksi, "SELECT bank_anggota, nama_rekening_anggota, nomor_rekening_anggota, nama_pengguna_anggota, id_sigma, turnover_amount FROM anggota WHERE id_anggota = ?");
    if ($query_anggota_data) {
        mysqli_stmt_bind_param($query_anggota_data, 'i', $id_anggota_aktif);
        mysqli_stmt_execute($query_anggota_data);
        mysqli_stmt_bind_result($query_anggota_data, $db_bank_anggota, $db_nama_rekening, $db_nomor_rekening, $db_nama_pengguna, $db_id_sigma, $db_turnover_amount);
        if (mysqli_stmt_fetch($query_anggota_data)) {
            $bank_anggota_aktif = $db_bank_anggota;
            $nomor_rekening_anggota_aktif = $db_nomor_rekening;
            $nama_rekening_anggota = $db_nama_rekening;
            $nama_pengguna_anggota = $db_nama_pengguna;
            $id_sigma_anggota = $db_id_sigma;
            $to_anggota = $db_turnover_amount;

            if (!function_exists('sensorNomorRekening')) {
                function sensorNomorRekening($nomorRekening) {
                    if (strlen($nomorRekening) >= 3) {
                        $numLength = strlen($nomorRekening) - 3;
                        $visiblePart = substr($nomorRekening, 0, $numLength);
                        $hiddenPart = 'XXX';
                        $formattedVisiblePart = '';
                        if (!empty($visiblePart)) {
                             $formattedVisiblePart = chunk_split($visiblePart, 3, '-');
                             $formattedVisiblePart = rtrim($formattedVisiblePart, '-');
                        }
                        return $formattedVisiblePart . (empty($visiblePart) ? '' : '-') . $hiddenPart;
                    }
                    return $nomorRekening;
                }
            }
            $nomor_rekening_anggota_aktif_sensored = sensorNomorRekening($nomor_rekening_anggota_aktif);
        }
        mysqli_stmt_close($query_anggota_data);
    }
}

$tujuan_withdraw = $bank_anggota_aktif . ' - ' . $nomor_rekening_anggota_aktif . ' - ' . $nama_rekening_anggota;

if (isset($_POST['submit'])) {
    $kode_withdraw = "W" . rand(1000000, 9999999);
    $jumlah_withdraw = isset($_POST['ui_amount']) ? str_replace(',', '', $_POST['ui_amount']) : 0;
    $jumlah_withdraw_float = floatval($jumlah_withdraw);
    $tanggal_withdraw = date("Y-m-d H:i:s");
    $withdraw_reference_id = 'withdraw_' . uniqid();

    $saldo_anggota_session = isset($_SESSION['saldo_anggota']) ? floatval($_SESSION['saldo_anggota']) : 0;
    
    if (empty($id_sigma_anggota)) {
        echo '
        <script>
        document.addEventListener("DOMContentLoaded", function() {
            alert("Terjadi kesalahan: ID Sigma (Player ID) anggota tidak ditemukan.");
        });
        </script>
        ';
        error_log("Withdraw Error: id_sigma_anggota is empty for user " . $nama_pengguna_anggota);
        goto end_of_submit_block;
    }
    
    // Validasi Turnover: cek apakah nilai turnover_amount lebih dari 0
    if ($to_anggota > 0) {
        echo '
        <script>
        document.addEventListener("DOMContentLoaded", function() {
            var notification = document.getElementById("notification");
            if (notification) {
                notification.style.display = "block";
                var notificationText = document.getElementById("notification-text");
                if (notificationText) {
                    notificationText.textContent = "Maaf, Anda tidak dapat melakukan penarikan karena masih memiliki Turnover (TO).";
                }
            } else {
                alert("Maaf, Anda tidak dapat melakukan penarikan karena masih memiliki Turnover (TO).");
            }
        });
        </script>
        ';
        goto end_of_submit_block;
    }

    if ($jumlah_withdraw_float < 50000) {
        echo '
        <script>
        document.addEventListener("DOMContentLoaded", function() {
            alert("Jumlah withdraw minimal adalah 50.000");
        });
        </script>
        ';
    } elseif ($saldo_anggota_session < $jumlah_withdraw_float) {
        echo '
        <script>
        document.addEventListener("DOMContentLoaded", function() {
            var notification = document.getElementById("notification");
            if (notification) {
                notification.style.display = "block";
                var notificationText = document.getElementById("notification-text");
                if (notificationText) {
                    notificationText.textContent = "Saldo tidak mencukupi untuk melakukan penarikan.";
                }
            } else {
                alert("Saldo tidak mencukupi untuk melakukan penarikan.");
            }
        });
        </script>
        <div id="notification" class="fixed z-[9999] px-4 pt-3 pb-5 top-3 sm:top-4 sm:right-6 left-3 right-3 sm:ml-auto sm:w-2/3 md:w-1/2 lg:w-[410px] rounded-xl bg-gradient-to-r from-[#710000] to-background-secondary to-50%" style="display:none;">
        <button class="h-6 w-6 ml-auto absolute right-3 top-2 z-50" onclick="document.getElementById(\'notification\').style.display=\'none\';">
        <svg width="100%" height="100%" viewBox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg">
        <path d="M18 6L6 18M6 6l12 12" stroke="var(--base)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
        </svg>
        </button>
        <div class="flex items-center">
        <figure class="flex-none h-12 w-12">
        <svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" viewBox="0 0 60 60" fill="none">
        <path stroke="#FF3B30" stroke-linecap="round" stroke-width="5" d="M30 52.5a22.5 22.5 0 1 0-15.91-6.59M22.5 22.5l15 15M37.5 22.5l-15 15"></path>
        </svg>
        </figure>
        <article class="pl-3">
        <p class="font-medium">Cancelled</p>
        <p class="text-xs mt-1" id="notification-text">
        </p>
        </article>
        </div>
        </div>
        ';
    } else {
        $gameXaAPI = new GameXaAPI();
        $proses_api = $gameXaAPI->withdrawFromPlayer($id_sigma_anggota, $jumlah_withdraw_float, $withdraw_reference_id);
        
        $api_status_success = false;
        if(is_array($proses_api) && isset($proses_api['success'])){
            if($proses_api['success'] === true){
                 $api_status_success = true;
            }
        }

        if (!$api_status_success) {
            $error_message_api = "Cobalah beberapa saat lagi!";
            if(is_array($proses_api) && isset($proses_api['message']) && !empty($proses_api['message'])){
                $error_message_api = $proses_api['message'];
            }
            error_log("GameXaAPI Withdraw Failed for Player ID: " . $id_sigma_anggota . ", Amount: " . $jumlah_withdraw_float . ". Response: " . json_encode($proses_api));
            echo '
            <script>
            document.addEventListener("DOMContentLoaded", function() {
                alert("'.addslashes($error_message_api).'");
            });
            </script>
            ';
        } else {
            $saldo_anggota_fix = $saldo_anggota_session - $jumlah_withdraw_float;
            $query_insert = "INSERT INTO withdraw (id_anggota_withdraw, kode_withdraw, jumlah_withdraw, tanggal_withdraw, tujuan_withdraw, reference_id) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_insert = mysqli_prepare($koneksi, $query_insert);
            mysqli_stmt_bind_param($stmt_insert, 'isdsss', $id_anggota_aktif, $kode_withdraw, $jumlah_withdraw_float, $tanggal_withdraw, $tujuan_withdraw, $withdraw_reference_id);
            
            if (mysqli_stmt_execute($stmt_insert)) {
                $new_withdraw_id = mysqli_insert_id($koneksi);
                mysqli_stmt_close($stmt_insert);
                
                $query_update_saldo = "UPDATE anggota SET saldo_anggota = ? WHERE id_anggota = ?";
                $stmt_update_saldo = mysqli_prepare($koneksi, $query_update_saldo);
                mysqli_stmt_bind_param($stmt_update_saldo, 'di', $saldo_anggota_fix, $id_anggota_aktif);
                
                if (mysqli_stmt_execute($stmt_update_saldo)) {
                    mysqli_stmt_close($stmt_update_saldo);
                    $_SESSION['saldo_anggota'] = $saldo_anggota_fix;
                    $_SESSION['valid_navigation'] = true;

                    $withdrawDataForTelegram = [
                        'id_withdraw' => $new_withdraw_id,
                        'kode_withdraw' => $kode_withdraw,
                        'jumlah_withdraw' => $jumlah_withdraw_float,
                        'tanggal_withdraw' => $tanggal_withdraw,
                        'tujuan_withdraw' => $tujuan_withdraw,
                        'reference_id' => $withdraw_reference_id
                    ];
                    
                    if (!sendNewWithdrawNotificationToTelegram($withdrawDataForTelegram, $nama_pengguna_anggota, $alamat_admin)) {
                        error_log("Gagal mengirim notifikasi Telegram untuk withdraw kode: " . $kode_withdraw . "(User: {$nama_pengguna_anggota})");
                    }

                    echo '<script>
                    window.location.href = "withdraw-progress";
                    </script>';
                    exit();
                } else {
                    error_log("Database Update Saldo Error for Withdraw Kode: " . $kode_withdraw . " - " . mysqli_error($koneksi));
                    echo "Proses Gagal Update Saldo<br>Error : " . mysqli_error($koneksi);
                }
            } else {
                error_log("Database Insert Withdraw Error for Kode: " . $kode_withdraw . " - " . mysqli_error($koneksi));
                echo "Proses Gagal Insert Withdraw<br>Error : " . mysqli_error($koneksi);
            }
        }
    }
    end_of_submit_block:;
}

$status_withdraw = null;
$jumlah_withdraw_terakhir = null;
$tanggal_withdraw_terakhir = null;

if ($id_anggota_aktif !== null) {
    $query_last_withdraw_details = "SELECT status_withdraw, jumlah_withdraw, tanggal_withdraw FROM withdraw WHERE id_anggota_withdraw = ? ORDER BY tanggal_withdraw DESC LIMIT 1";
    if ($stmt_last_withdraw_details = mysqli_prepare($koneksi, $query_last_withdraw_details)) {
        mysqli_stmt_bind_param($stmt_last_withdraw_details, 'i', $id_anggota_aktif);
        mysqli_stmt_execute($stmt_last_withdraw_details);
        mysqli_stmt_bind_result($stmt_last_withdraw_details, $status_withdraw, $jumlah_withdraw_terakhir, $tanggal_withdraw_terakhir);
        mysqli_stmt_fetch($stmt_last_withdraw_details);
        mysqli_stmt_close($stmt_last_withdraw_details);
    }
}
?>

<section class="container flex mx-auto lg:pt-3 lg:pb-5 lg:mt-5">
    <div class="w-full lg:w-1/3 px-3 hidden lg:block">
        <a class="px-3 py-4 bg-background-default lg:bg-background-secondary rounded-xl mt-4 drop-shadow-[0_0_5px_rgba(0,0,0,0.6)] lg:drop-shadow-none group transition-all duration-300 ease-in-out lg:hover:bg-black/30" href="#">
            <figure class="flex flex-none items-center justify-center w-12 md:w-16 h-12 md:h-16">
                <img alt="VIP Level Badge" width="0" height="0" decoding="async" data-nimg="1" class="w-full" style="color: transparent;" loading="lazy" src="assets/img/pemainbaru1.png">
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
                            <span class="text-sm lg:text-base font-semibold">IDR&nbsp;<?php echo number_format(isset($_SESSION['saldo_anggota']) ? $_SESSION['saldo_anggota'] : 0, 0, ',', '.'); ?></span>
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
                <figure class="w-6">
                    <svg width="22" height="22" viewbox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg" size="22">
                        <path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="var(--base)"></path>
                    </svg>
                </figure>
            </a>
        </section>
        <section class="bg-background-secondary rounded-xl mt-4 overflow-hidden">
            <a class="justify-between px-4 py-3 hover:lg:bg-background-tertiary transition-all duration-300 ease-in-out" href="<?php echo $alamat_website . 'change-pasword'; ?>">
                <div class="flex items-center w-[calc(100%-24px)] pr-1">
                    <svg width="24" height="24" viewbox="0 0 24 24" fill="var(--primary)" xmlns="http://www.w3.org/2000/svg" size="24">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M13.984 2.542c.087.169.109.386.152.82.082.82.123 1.23.295 1.456a1 1 0 0 0 .929.384c.28-.037.6-.298 1.238-.82.337-.277.506-.415.687-.473a1 1 0 0 1 .702.035c.175.076.33.23.637.538l.894.894c.308.308.462.462.538.637a1 1 0 0 1 .035.702c-.058.181-.196.35-.472.687-.523.639-.784.958-.822 1.239a1 1 0 0 0 .385.928c.225.172.636.213 1.457.295.433.043.65.065.82.152a1 1 0 0 1 .47.521c.071.177.071.395.071.831v1.264c0 .436 0 .654-.07.83a1 1 0 0 1-.472.522c-.169.087-.386.109-.82.152-.82.082-1.23.123-1.456.295a1 1 0 0 0-.384.929c.038.28.299.6.821 1.238.276.337.414.505.472.687a1 1 0 0 1-.035.702c-.076.175-.23.329-.538.637l-.894.893c-.308.309-.462.463-.637.538a1 1 0 0 1-.702.035c-.181-.058-.35-.196-.687-.472-.639-.522-.958-.783-1.238-.82a1 1 0 0 0-.929.384c-.172.225-.213.635-.295 1.456-.043.434-.065.651-.152.82a1 1 0 0 1-.521.472c-.177.07-.395.07-.831.07h-1.264c-.436 0-.654 0-.83-.07a1 1 0 0 1-.522-.472c-.087-.169-.109-.386-.152-.82-.082-.82-.123-1.23-.295-1.456a1 1 0 0 0-.928-.384c-.281.037-.6.298-1.239.82-.337.277-.506.415-.687-.473a1 1 0 0 1-.702-.035c-.175-.076-.33-.23-.637.538l-.894-.894c-.308-.308-.462-.462-.538-.637a1 1 0 0 1-.035-.702c.058-.181.196-.35.472-.687.523-.639.784-.958.821-1.239a1 1 0 0 0-.384-.928c-.225-.172-.636-.213-1.457-.295-.433-.043-.65-.065-.82-.152a1 1 0 0 1-.47-.521C2 13.286 2 13.068 2 12.632v-1.264c0-.436 0-.654.07-.83a1 1 0 0 1 .472-.522c.169-.087.386-.109.82-.152.82-.082 1.231-.123 1.456-.295a1 1 0 0 0 .385-.928c-.038-.281-.3-.6-.822-1.24-.276-.337-.414-.505-.472-.687a1 1 0 0 1 .035-.702c.076-.174.23-.329.538-.637l.894-.893c.308-.308.462-.463.637-.538a1 1 0 0 1 .702.035c.181.058.35.196.687-.472.639.522.958.783 1.238.821a1 1 0 0 0 .93-.385c.17-.225.212-.635.294-1.456.043-.433.065-.65.152-.82a1 1 0 0 1 .521-.471c.177-.07.395-.07.831-.07h1.264c.436 0 .654 0 .83.07a1 1 0 0 1 .522.472ZM12 16a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" fill="var(--primary)"></path>
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
                            <path d="m2 12-.78-.625-.5.625.5.625L2 12Zm9 1a1 1 0 1 0 0-2v2ZM5.22 6.375l-4 5 1.56 1.25 4-5-1.56-1.25Zm-4 6.25 4 5 1.56-1.25-4-5-1.56 1.25ZM2 13h9v-2H2v2ZM13.342 20.557l.165-.986-.165.986Zm7.597.19.646.764-.646-.763ZM15.014 3.165l-.165-.986.165.986Zm5.925.088.646-.763-.646.763ZM13.507 4.43l1.671-.278-.329-1.973-1.671.279.329 1.972ZM21 9.083v5.834h2V9.083h-2Zm-5.822 10.766-1.671-.278-.329 1.973 1.671.278.329-1.973ZM11 8.132v-.743H9v.743h2Zm0 8.48v-.546H9v.546h2Zm2.507 2.959c-.824-.138-1.35-.227-1.734-.342-.358-.106-.472-.201-.536-.277l-1.526 1.293c.41.484.932.735 1.491.901.532.159 1.203.269 1.976.398l.329-1.973ZM9 16.61c0 .784-.002 1.464.067 2.015.072.578.234 1.135.644 1.619l1.526-1.293c-.064-.075-.14-.203-.185-.574-.05-.398-.052-.932-.052-1.767H9Zm12-1.694c0 1.675-.002 2.823-.123 3.67-.116.82-.32 1.174-.584 1.398l1.293 1.526c.797-.675 1.123-1.593 1.272-2.642.144-1.021.142-2.338.142-3.952h-2Zm-6.15 6.905c1.59.265 2.89.484 3.92.51 1.06.025 2.018-.146 2.816-.821l-1.293-1.526c-.264.223-.646.367-1.474.347-.856-.021-1.99-.207-3.641-.483l-.329 1.973ZM11.177 3.329c1.652-.275 2.785-.462 3.64-.483.829-.02 1.21.124 1.475.347l1.293-1.526c-.797-.675-1.757-.846-2.816-.82-1.03.025-2.33.244-3.92.509l.328 1.973ZM23 9.083c0-1.614.002-2.93-.142-3.952-.15-1.049-.476-1.967-1.273-2.642l-1.292 1.526c.264.224.468.577.584 1.397.12.848.123 1.996.123 3.67h2Zm-9.822-6.626c-.773.128-1.444.238-1.976.397-.559.166-1.081.417-1.491.901l1.526 1.293c.064-.076.178-.17.536-.277.384-.115.91-.204 1.734-.342l-.329-1.972ZM11 7.389c0-.835.002-1.369.052-1.767.046-.37.121-.499.185-.574L9.711 3.755c-.41.484-.572 1.04-.644 1.62C8.998 5.924 9 6.604 9 7.388h2Z" fill="var(--primary)"></path>
                        </svg>
                        <span class="text-sm pl-2 undefined undefined">Logout</span>
                    </div>
                    <figure class="w-6"><svg width="22" height="22" viewbox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg" size="22">
                        <path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="var(--base)"></path>
                    </svg>
                    </figure>
                </section>
            </div>
        </a>
    </div>
    <div class="w-full lg:w-2/3 lg:px-3 pb-24 lg:pb-0">
        <div class="grid grid-cols-2 px-3 lg:gap-x-5 lg:px-4 lg:mb-6 mt-4 lg:mt-0">
            <a aria-label="Deposit-tab-button" aria-labelledby="Deposit-tab-button" class="text-sm border-b uppercase flex-none px-1 pb-2 justify-center cursor-pointer transition-all duration-300 ease-in-out hover:lg:border-primary hover:lg:text-inverse font-semibold border-transparent text-caption " href="<?php echo $alamat_website . 'deposit'; ?>">Deposit</a>
            <a aria-label="Withdraw-tab-button" aria-labelledby="Withdraw-tab-button" class="text-sm border-b uppercase flex-none px-1 pb-2 justify-center cursor-pointer transition-all duration-300 ease-in-out hover:lg:border-primary hover:lg:text-inverse font-semibold border-primary " href="<?php echo $alamat_website . 'withdraw'; ?>">Withdraw</a>
        </div>
        <?php if (isset($status_withdraw) && strtolower($status_withdraw) == 'diproses') : ?>
        <section class="mt-3 px-3 lg:px-4 pb-6">
            <div class="lg:bg-background-secondary lg:px-5 lg:pt-6 lg:pb-16 lg:mb-4 rounded-xl">
                <div class="h-12 lg:h-16 w-12 lg:w-16 mx-auto my-4">
                    <svg width="100%" height="100%" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M2.483 0h19.034v2.483H2.483V0Z" fill="#7D8D9C"></path>
                        <path d="M3.31 2.483h17.38v3.31H3.31v-3.31ZM7.862 24v-1.655h4.966V24H7.862Z" fill="#AABECE"></path>
                        <path d="M7.448 4.138h9.104v10.759H7.448V4.137Z" fill="#A9D39F"></path>
                        <path d="M4.552 3.724h.827v.828h-.827v-.828ZM18.62 3.724h.828v.828h-.827v-.828Z" fill="#364A5B"></path>
                        <path d="M15.31 13.655h-2.482v-.827h1.655v-1.656h.827v2.483ZM8.69 11.172h.827v2.483H8.69v-2.483Z" fill="#78A36C"></path>
                        <path d="M12.828 9.517a.828.828 0 1 1-1.656 0 .828.828 0 0 1 1.656 0Z" fill="#FCF05A"></path>
                        <path d="M8.69 4.138h.827v6.207H8.69V4.138ZM14.483 4.138h.827v6.207h-.827V4.138Z" fill="#78A36C"></path>
                        <path d="M12 7.034c1.6 0 2.896-1.296 2.896-2.896H9.104c0 1.6 1.297 2.896 2.897 2.896Z" fill="#D9E5D6"></path>
                        <path d="M6.207 3.724h11.586v.828H6.207v-.828Z" fill="#364A5B"></path>
                        <path d="M11.586 14.896v-2.068A.828.828 0 0 0 10.76 12h-.414a.828.828 0 0 0-.828.828v2.069H7.448v-5.38l-1.655 1.655v5.38l2.07 5.793h4.965v-4.552l-1.242-2.896Z" fill="#FFD782"></path>
                        <path d="M9.103 14.896h.828v2.07h-.828v-2.07Z" fill="#D39C39"></path>
                    </svg>
                </div>
                <p class="text-xs text-center mt-3 opacity-70">Withdraw</p>
                <p class="text-sm font-semibold text-center mt-3">In Progress</p>
                <div class="px-3 mt-5">
                    <div class="relative">
                        <div class="flex justify-between items-center mt-4 pb-3 border-b border-disable">
                            <p class="text-xs opacity-70 w-1/2">Amount</p>
                            <article class="flex items-center justify-end w-1/2">
                                <p class="text-xs truncate">IDR&nbsp;<?php echo number_format($jumlah_withdraw_terakhir ?: 0, 0, ',', '.'); ?></p>
                                <div class="pl-1 cursor-pointer">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M15.5 3h-6a4 4 0 0 0-4 4v8" stroke="var(--primary)" stroke-width="2"></path>
                                        <path d="M9.5 11.5c0-1.196.001-2.01.071-2.628.068-.598.188-.889.342-1.09a2 2 0 0 1 .37-.369c.2-.154.491-.274 1.09-.342C11.99 7.001 12.803 7 14 7c1.196 0 2.01.001 2.628.071.598.068.889.188 1.09.342.138.107.262.23.369.37.154.2.274.491.342 1.09.07.618.071 1.431.071 2.627v4c0 1.196-.002 2.01-.071 2.628-.068.598-.188.889-.342 1.09-.107.138-.23.262-.37.369-.2.154-.491.274-1.09.342-.618.07-1.431.071-2.627.071-1.196 0-2.01-.002-2.628-.071-.598-.068-.889-.188-1.09-.342a1.998 1.998 0 0 1-.369-.37c-.154-.2-.274-.491-.342-1.09-.07-.618-.071-1.431-.071-2.627v-4Z" stroke="var(--primary)" stroke-width="2"></path>
                                    </svg>
                                </div>
                            </article>
                        </div>
                        <div class="flex justify-between items-center mt-4 pb-3 border-b border-disable">
                            <p class="text-xs opacity-70 w-1/2">Bank</p>
                            <article class="flex items-center justify-end w-1/2">
                                <p class="text-xs truncate"><?php echo htmlspecialchars($bank_anggota_aktif); ?></p>
                            </article>
                        </div>
                        <div class="flex justify-between items-center mt-4 pb-3 border-b border-disable">
                            <p class="text-xs opacity-70 w-1/2">Account Number</p>
                            <article class="flex items-center justify-end w-1/2">
                                <p class="text-xs truncate"><?php echo htmlspecialchars($nomor_rekening_anggota_aktif_sensored); ?></p>
                                <div class="pl-1 cursor-pointer">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M15.5 3h-6a4 4 0 0 0-4 4v8" stroke="var(--primary)" stroke-width="2"></path>
                                        <path d="M9.5 11.5c0-1.196.001-2.01.071-2.628.068-.598.188-.889.342-1.09a2 2 0 0 1 .37-.369c.2-.154.491-.274 1.09-.342C11.99 7.001 12.803 7 14 7c1.196 0 2.01.001 2.628.071.598.068.889.188 1.09.342.138.107.262.23.369.37.154.2.274.491.342 1.09.07.618.071 1.431.071 2.627v4c0 1.196-.002 2.01-.071 2.628-.068.598-.188.889-.342 1.09-.107.138-.23.262-.37.369-.2.154-.491.274-1.09.342-.618.07-1.431.071-2.627.071-1.196 0-2.01-.002-2.628-.071-.598-.068-.889-.188-1.09-.342a1.998 1.998 0 0 1-.369-.37c-.154-.2-.274-.491-.342-1.09-.07-.618-.071-1.431-.071-2.627v-4Z" stroke="var(--primary)" stroke-width="2"></path>
                                    </svg>
                                </div>
                            </article>
                        </div>
                        <div class="flex justify-between items-center mt-4 pb-3 border-b border-disable">
                            <p class="text-xs opacity-70 w-1/2">Receiver</p>
                            <article class="flex items-center justify-end w-1/2">
                                <p class="text-xs truncate"><?php echo htmlspecialchars($nama_rekening_anggota); ?></p>
                            </article>
                        </div>
                        <div class="flex justify-between items-center mt-4 pb-3 border-b border-disable">
                            <p class="text-xs opacity-70 w-1/2">Request Date</p>
                            <article class="flex items-center justify-end w-1/2">
                                <p class="text-xs truncate"><?php echo htmlspecialchars($tanggal_withdraw_terakhir); ?></p>
                            </article>
                        </div>
                        <div class="flex justify-between items-center mt-4 pb-3 border-b border-disable">
                            <p class="text-xs opacity-70 w-1/2">Status</p>
                            <article class="flex items-center justify-end w-1/2">
                                <p class="text-xs truncate text-danger">In Progress</p>
                            </article>
                        </div>
                    </div>
                    <p class="text-xs text-center mt-3 lg:mb-5">
                        You have pending transactions, please contact Customer Service
                    </p>
                    <a href="<?php echo isset($isi_1_link_livechat_web) ? $isi_1_link_livechat_web : '#'; ?>" target="blank" aria-label="You have pending transactions, please contact Customer Service contact button" class="bg-background-tertiary justify-between rounded-full w-full lg:w-1/2 lg:mx-auto mt-5 py-2 px-3 transition-all duration-300 ease-in-out lg:hover:bg-white/30">
                        <figure class="flex items-center">
                            <svg width="24" height="24" viewbox="0 0 24 24" fill="var(--primary)" xmlns="http://www.w3.org/2000/svg" size="24">
                                <g fill="var(--primary)">
                                    <path d="m21.696 20.72-4.971-4.973-1.001 1 4.978 4.966.994-.994ZM2.298 3.355 7.27 8.327l.993-.993-4.971-4.972-.994.993ZM19.704 22.711l-4.972-4.972-.99.99c-2.929-1.626-6.836-5.486-8.461-8.413l.998-.998-4.972-4.971-.018.017C.044 5.61-.304 7.504.423 9.078 2.342 13.225 7.675 20.321 15.04 23.65c1.589.717 3.433.293 4.628-.903l.036-.036ZM8.346 2.673l.995.994c3.016-3.016 8.016-3.016 11.032 0s3.016 7.97 0 10.985l.994.994c3.564-3.563 3.564-9.41 0-12.973-3.564-3.564-9.457-3.564-13.02 0Z"></path>
                                    <path d="m12.628 8.484-.297.149a2.962 2.962 0 0 0-1.646 2.664V12h4.219v-1.406h-2.649c.15-.299.393-.547.705-.703l.297-.149a2.962 2.962 0 0 0 1.647-2.664c0-1.163-.947-2.11-2.11-2.11-1.163 0-2.11.947-2.11 2.11v.703h1.407v-.703a.704.704 0 0 1 1.406 0c0 .6-.333 1.138-.87 1.406ZM16.31 4.969v4.219h2.812V12h1.407V4.969h-1.407V7.78h-1.406V4.97H16.31Z"></path>
                                </g>
                            </svg>
                            <span class="text-xs pl-2">Contact CS</span>
                        </figure>
                        <svg width="24" height="24" viewbox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg" size="24">
                            <path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="var(--base)"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </section>
        <?php else : ?>
                <?php include_once 'info_acc.php'; ?>
        <form class="lg:bg-background-secondary lg:pt-3 lg:pb-1 mt-3 lg:rounded-xl" method="post" action="<?php echo $alamat_website . 'withdraw'; ?>">
            <div class="px-4 lg:px-5 mt-2">
                <p class="text-xs mb-3">Choose Withdraw Destination</p>
                <div class="bg-background-secondary lg:bg-transparent border border-separator px-3 py-4 rounded-xl">
                    <div class="flex flex-auto items-center">
                        <figure class="flex flex-none w-11 h-11 items-center justify-center rounded-full bg-white">
                            <img alt="Bankdeposit" fetchpriority="high" width="0" height="0" decoding="async" data-nimg="1" class="w-full px-1" src="assets/img/bankdeposit.png" style="color: transparent;">
                        </figure>
                        <article class="w-64 lg:w-full pl-4">
                            <p class="text-sm truncate"><?php echo htmlspecialchars($nama_rekening_anggota); ?></p>
                            <p class="text-xs mt-[5px]">
                                <span class="text-xs pr-1"><?php echo htmlspecialchars($bank_anggota_aktif); ?></span>-<span class="text-xs pl-1 pr-2"><?php echo htmlspecialchars($nomor_rekening_anggota_aktif_sensored); ?></span>
                            </p>
                        </article>
                    </div>
                </div>
            </div>
            <div class="px-4 lg:px-5 mt-4">
                <section class="mt-3 relative">
                    <div class="relative mt-4 lg:mt-5 rounded-xl group border lg:bg-background-default border-caption focus-within:border-primary focus-within:ring-1">
                        <div class="relative flex items-center top-0 pt-3 px-3">
                            <label class="text-xs opacity-70 bg-background-default rounded-full">Enter withdraw amount</label>
                        </div>
                        <div class="relative">
                            <input name="ui_amount" inputmode="numeric" maxlength="16" class="px-3 pt-2 pb-3 focus:border-transparent focus:ring-0 text-sm lg:text-base w-full rounded-lg border bg-transparent border-transparent focus:outline-none" label="Enter withdraw amount" placeholder="Minimum withdraw IDR&nbsp;50.000" type="text" value="">
                        </div>
                    </div>
                    <div class="flex justify-between mt-2">
                        <p class="text-error text-sm"></p>
                    </div>
                </section>
                <div class="px-4 lg:px-5">
                    <button type="submit" name="submit" aria-label="Withdraw Form Confirmation" aria-labelledby="Withdraw Form Confirmation" class="w-full lg:w-1/2 justify-center rounded-full py-3 mt-6 mb-10 lg:mx-auto bg-primary transition-all duration-200 ease-in-out hover:lg:brightness-[0.9]">Next</button>
                </div>
            </div>
        </form>
        <?php endif; ?>
    </div>
</section>
<div id="notification" class="fixed z-[9999] px-4 pt-3 pb-5 top-3 sm:top-4 sm:right-6 left-3 right-3 sm:ml-auto sm:w-2/3 md:w-1/2 lg:w-[410px] rounded-xl bg-gradient-to-r from-[#710000] to-background-secondary to-50%" style="display:none;">
    <button class="h-6 w-6 ml-auto absolute right-3 top-2 z-50" onclick="document.getElementById('notification').style.display='none';">
        <svg width="100%" height="100%" viewBox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg">
            <path d="M18 6L6 18M6 6l12 12" stroke="var(--base)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
        </svg>
    </button>
    <div class="flex items-center">
        <figure class="flex-none h-12 w-12">
            <svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" viewBox="0 0 60 60" fill="none">
                <path stroke="#FF3B30" stroke-linecap="round" stroke-width="5" d="M30 52.5a22.5 22.5 0 1 0-15.91-6.59M22.5 22.5l15 15M37.5 22.5l-15 15"></path>
            </svg>
        </figure>
        <article class="pl-3">
            <p class="font-medium">Pemberitahuan</p>
            <p class="text-xs mt-1" id="notification-text">
                </p>
        </article>
    </div>
</div>
<?php include_once 'footer.php'; ?>