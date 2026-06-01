<?php
// 1. Mulai Sesi (dengan pemeriksaan yang lebih kuat)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 2. Sertakan File Header dan Koneksi
include_once 'header.php';
include_once 'koneksi.php';

// --- LOGIKA PHP (Sama seperti sebelumnya) ---

// 3. Periksa apakah pengguna sudah login
if (!isset($_SESSION['nama_pengguna_anggota']) || empty($_SESSION['nama_pengguna_anggota'])) {
    ob_start();
    ?>
    <div class='min-h-screen bg-gray-900 flex flex-col justify-center py-6 sm:py-12'>
        <div class='relative py-3 sm:max-w-xl sm:mx-auto w-full px-4 sm:px-0'>
            <div class='relative px-4 py-10 bg-gray-800 shadow-2xl sm:rounded-3xl sm:p-10 md:p-12 border border-gray-700'>
                <div class='max-w-md mx-auto text-center'>
                    <div class='p-4 bg-red-800 border border-red-700 text-red-100 rounded-lg flex items-center justify-center space-x-3'>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-8 h-8">
                            <path fill-rule="evenodd" d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.753-2.5-2.598-4.5L9.4 3.003zM12 8.25a.75.75 0 01.75.75v3.75a.75.75 0 01-1.5 0V9a.75.75 0 01.75-.75zm0 8.25a.75.75 0 100-1.5.75.75 0 000 1.5z" clip-rule="evenodd" />
                        </svg>
                        <p>Akses ditolak. Anda harus login terlebih dahulu.
                        <?php if (isset($alamat_website)): ?>
                            <a href='<?php echo htmlspecialchars(rtrim($alamat_website, '/')) . "/auth-login"; ?>' class='font-semibold text-indigo-400 hover:text-indigo-300'>Login di sini</a>.
                        <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    $error_output = ob_get_clean();
    echo $error_output;
    include_once 'footer.php';
    exit;
}
$nama_anggota = $_SESSION['nama_pengguna_anggota'];

// 4. Validasi variabel $koneksi dan $alamat_website
if (!isset($koneksi) || !$koneksi instanceof mysqli) {
    ob_start();
    ?>
    <div class='min-h-screen bg-gray-900 flex flex-col justify-center py-6 sm:py-12'>
        <div class='relative py-3 sm:max-w-xl sm:mx-auto w-full px-4 sm:px-0'>
            <div class='relative px-4 py-10 bg-gray-800 shadow-2xl sm:rounded-3xl sm:p-10 md:p-12 border border-gray-700'>
                <div class='max-w-md mx-auto text-center'>
                     <div class='p-4 bg-red-800 border border-red-700 text-red-100 rounded-lg flex items-center justify-center space-x-3'>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-8 h-8">
                            <path fill-rule="evenodd" d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.753-2.5-2.598-4.5L9.4 3.003zM12 8.25a.75.75 0 01.75.75v3.75a.75.75 0 01-1.5 0V9a.75.75 0 01.75-.75zm0 8.25a.75.75 0 100-1.5.75.75 0 000 1.5z" clip-rule="evenodd" />
                        </svg>
                        <p>Koneksi ke database gagal. Hubungi administrator.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    $error_output = ob_get_clean();
    echo $error_output;
    include_once 'footer.php';
    exit;
}

if (!isset($alamat_website)) {
    $alamat_website = './'; // Fallback
}
$form_action_url = htmlspecialchars(rtrim($alamat_website, '/')) . '/process_kyc';

// 5. Operasi Database (Error handling disesuaikan untuk tema gelap)
$stmt = $koneksi->prepare("SELECT email_anggota, telepon_anggota FROM anggota WHERE nama_pengguna_anggota = ?");

$db_error_message_wrapper_start = "<div class='min-h-screen bg-gray-900 flex flex-col justify-center py-6 sm:py-12'><div class='relative py-3 sm:max-w-xl sm:mx-auto w-full px-4 sm:px-0'><div class='relative px-4 py-10 bg-gray-800 shadow-xl sm:rounded-3xl sm:p-10 md:p-12 border border-gray-700'><div class='max-w-md mx-auto text-center'><div class='p-4 bg-yellow-700 border border-yellow-600 text-yellow-100 rounded-lg flex items-center justify-center space-x-3'>";
$db_error_icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-8 h-8"><path fill-rule="evenodd" d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.753-2.5-2.598-4.5L9.4 3.003zM12 8.25a.75.75 0 01.75.75v3.75a.75.75 0 01-1.5 0V9a.75.75 0 01.75-.75zm0 8.25a.75.75 0 100-1.5.75.75 0 000 1.5z" clip-rule="evenodd" /></svg>';
$db_error_message_wrapper_end = "</div></div></div></div></div>";
$db_error_message_content = "<p>Terjadi kesalahan dalam memproses data. Coba lagi nanti.</p>";

if (false === $stmt) {
    error_log("MySQLi prepare failed: (" . $koneksi->errno . ") " . $koneksi->error);
    echo $db_error_message_wrapper_start . $db_error_icon . $db_error_message_content . $db_error_message_wrapper_end;
    if (isset($koneksi)) $koneksi->close();
    include_once 'footer.php';
    exit;
}

$stmt->bind_param("s", $nama_anggota);

if (!$stmt->execute()) {
    error_log("MySQLi execute failed: (" . $stmt->errno . ") " . $stmt->error);
    echo $db_error_message_wrapper_start . $db_error_icon . $db_error_message_content . $db_error_message_wrapper_end;
    $stmt->close();
    if (isset($koneksi)) $koneksi->close();
    include_once 'footer.php';
    exit;
}

$result = $stmt->get_result();
$data_found = ($result && $result->num_rows > 0);
if ($data_found) {
    $data = $result->fetch_assoc();
}

// --- OUTPUT HTML (Tema Gelap) ---
?>

<section class="container mx-auto py-3 px-3">
    <div class="flex pb-3 overflow-x-scroll">
        <a aria-label="KYC-tab-button" aria-labelledby="KYC-tab-button" class="text-sm border-b uppercase flex-none px-1 pb-2 mx-2 cursor-pointer transition-all duration-300 ease-in-out hover:lg:border-primary hover:lg:text-inverse font-semibold border-primary" href="kyc">KYC</a>
    </div>

    <div class="w-full lg:w-2/3 lg:mx-auto px-2 mt-6">
        <div class="relative py-3 sm:max-w-xl sm:mx-auto w-full px-4 sm:px-0">
            <div class="relative px-4 py-10 bg-gray-800 shadow-2xl sm:rounded-3xl sm:p-8 md:p-10 lg:p-12 border border-gray-700">
                <div class="max-w-md mx-auto">
                    <?php if ($data_found): ?>
                    <form method="POST" action="<?php echo $form_action_url; ?>" enctype="multipart/form-data" class="space-y-6">
                        <div>
                            <h2 class="text-3xl sm:text-4xl font-extrabold text-center text-white mb-2">Verifikasi Akun (KYC)</h2>
                            <p class="text-center text-sm text-gray-400 mb-8">Lengkapi data berikut untuk menyelesaikan proses verifikasi akun Anda.</p>
                        </div>

                        <div class="mb-5">
                            <label for="email" class="flex items-center text-sm font-semibold text-gray-200 mb-1">
                                <i class="mdi mdi-email-outline w-5 h-5 mr-2 text-gray-400"></i>
                                Email Anda
                            </label>
                            <input type="email" id="email" name="email_anggota" value="<?php echo htmlspecialchars($data['email_anggota']); ?>" readonly
                                class="mt-1 block w-full px-4 py-3 border border-gray-600 rounded-lg shadow-sm bg-gray-700 text-gray-400 cursor-not-allowed
                                        focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-150">
                        </div>

                        <div class="mb-5">
                            <label for="phone" class="flex items-center text-sm font-semibold text-gray-200 mb-1">
                                <i class="mdi mdi-whatsapp w-5 h-5 mr-2 text-gray-400"></i>
                                Nomor Whatsapp Anda
                            </label>
                            <input type="tel" id="phone" name="telepon_anggota" value="<?php echo htmlspecialchars($data['telepon_anggota']); ?>" readonly
                                class="mt-1 block w-full px-4 py-3 border border-gray-600 rounded-lg shadow-sm bg-gray-700 text-gray-400 cursor-not-allowed
                                        focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-150">
                        </div>

                        <div class="mb-6">
                            <label for="identity_document_input_trigger" class="flex items-center text-sm font-semibold text-gray-200 mb-1">
                                <i class="mdi mdi-file-upload-outline w-5 h-5 mr-2 text-gray-400"></i>
                                Unggah Dokumen Identitas Asli Anda <span class="text-red-500 ml-1">*</span>
                            </label>
                            <div class="mt-2 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-600 border-dashed rounded-lg hover:border-indigo-500 transition duration-150 ease-in-out">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-500" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="flex text-sm text-gray-400 justify-center">
                                        <label for="identity_document_input_trigger" class="relative cursor-pointer bg-gray-800 rounded-md font-medium text-indigo-400 hover:text-indigo-300 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-offset-gray-800 focus-within:ring-indigo-500">
                                            <span>Unggah file</span>
                                            <input id="identity_document_input_trigger" name="identity_document" type="file" class="sr-only" accept=".jpg, .jpeg, .png, .pdf" required>
                                        </label>
                                        <p class="pl-1">atau tarik dan lepas</p>
                                    </div>
                                    <p class="text-xs text-gray-500" id="file_chosen_text">JPG, JPEG, PNG, PDF (Maks: 2MB)</p>
                                </div>
                            </div>
                        </div>

                        <div class="pt-2">
                            <button type="submit"
                                    class="w-full group flex items-center justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-base font-medium text-white
                                        bg-indigo-600 hover:bg-indigo-700
                                        focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-indigo-500 active:bg-indigo-800 transition duration-150 ease-in-out">
                                <i class="mdi mdi-check-decagram-outline w-5 h-5 mr-2 -ml-1"></i>
                                Verifikasi Sekarang
                            </button>
                        </div>
                    </form>
                    <?php else: ?>
                    <div class='text-center p-4 bg-red-800 border border-red-700 text-red-100 rounded-lg flex items-center justify-center space-x-3'>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-8 h-8">
                            <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zM12.75 9a.75.75 0 00-1.5 0v2.25H9a.75.75 0 000 1.5h2.25V15a.75.75 0 001.5 0v-2.25H15a.75.75 0 000-1.5h-2.25V9z" clip-rule="evenodd" />
                        </svg>
                        <p>Data anggota tidak ditemukan untuk pengguna: <strong class="font-semibold"><?php echo htmlspecialchars($nama_anggota); ?></strong>.
                        Pastikan profil Anda sudah lengkap atau hubungi dukungan.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('identity_document_input_trigger');
    const fileChosenText = document.getElementById('file_chosen_text');

    if (fileInput && fileChosenText) {
        fileInput.addEventListener('change', function() {
            if (this.files && this.files.length > 0) {
                let fileName = this.files[0].name;
                const maxLength = 30; 
                if (fileName.length > maxLength) {
                    fileName = fileName.substring(0, maxLength - 3) + "...";
                }
                fileChosenText.textContent = fileName;
            } else {
                fileChosenText.textContent = 'JPG, JPEG, PNG, PDF (Maks: 2MB)';
            }
        });
    }
});
</script>

<?php
// --- Pembersihan PHP (Sama seperti sebelumnya) ---
if (isset($stmt)) {
    $stmt->close();
}
if (isset($koneksi)) {
    $koneksi->close();
}

include_once 'footer.php';
?>