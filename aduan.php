<?php
include_once 'koneksi.php';
include_once 'header.php';

// Pastikan hanya member yang sudah login yang bisa mengakses halaman ini
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo '<script>alert("Silakan login untuk mengakses halaman ini."); window.location.replace("' . $alamat_website . 'auth-login");</script>';
    exit();
}

$message = '';
$message_type = '';
$id_anggota = $_SESSION['id_anggota'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form
    $username = $_SESSION['nama_pengguna_anggota'];
    $judul_aduan = htmlspecialchars($_POST['judul_aduan']);
    $isi_aduan = htmlspecialchars($_POST['isi_aduan']);
    $no_whatsapp = htmlspecialchars($_POST['no_whatsapp']);
    $email = htmlspecialchars($_POST['email']);
    $file_screenshot_path = null;

    // Proses unggah file
    if (isset($_FILES['file_screenshot']) && $_FILES['file_screenshot']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_path = $_FILES['file_screenshot']['tmp_name'];
        $file_name = uniqid() . '-' . basename($_FILES['file_screenshot']['name']);
        $dest_path = 'uploads/complaints/' . $file_name;

        // Cek ekstensi file yang diizinkan
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
        if (in_array($_FILES['file_screenshot']['type'], $allowed_types)) {
            if (move_uploaded_file($file_tmp_path, $dest_path)) {
                $file_screenshot_path = $dest_path;
            } else {
                $message = 'Gagal mengunggah file screenshot. Mohon coba lagi.';
                $message_type = 'error';
            }
        } else {
            $message = 'Jenis file tidak diizinkan. Mohon unggah JPG, PNG, GIF, atau PDF.';
            $message_type = 'error';
        }
    }

    // Validasi data dan insert ke database jika tidak ada error upload
    if ($message_type === '') {
        if (empty($judul_aduan) || empty($isi_aduan)) {
            $message = 'Judul dan isi aduan tidak boleh kosong.';
            $message_type = 'error';
        } else {
            // Gunakan Prepared Statement untuk mencegah SQL Injection
            $query = "INSERT INTO aduan_member (id_anggota, username, no_whatsapp, email, judul_aduan, isi_aduan, file_screenshot) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($koneksi, $query);

            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "issssss", $id_anggota, $username, $no_whatsapp, $email, $judul_aduan, $isi_aduan, $file_screenshot_path);
                if (mysqli_stmt_execute($stmt)) {
                    $message = 'Aduan Anda berhasil dikirim! Tim kami akan segera memprosesnya.';
                    $message_type = 'success';
                } else {
                    $message = 'Gagal mengirim aduan. Mohon coba lagi.';
                    $message_type = 'error';
                    error_log("Failed to insert aduan: " . mysqli_error($koneksi));
                }
                mysqli_stmt_close($stmt);
            } else {
                $message = 'Terjadi kesalahan pada sistem. Mohon hubungi LiveChat.';
                $message_type = 'error';
                error_log("Prepared statement failed: " . mysqli_error($koneksi));
            }
        }
    }
}
?>

<section class="container mx-auto pt-3 pb-10 lg:pb-12 px-3">
    <nav class="flex mb-1 lg:mb-2">
        <ol class="flex items-center pb-1 overflow-x-scroll whitespace-nowrap opacity-scroll">
            <li class="inline-flex items-end pr-1">
                <a class="text-xs border-b border-transparent hover:lg:border-primary transition-all duration-200 ease-in-out undefined" href="<?php echo $alamat_website . 'home'; ?>">Home</a>
            </li>
            <li class="inline-flex items-end pr-1 group">
                <div class="flex items-center">
                    <svg width="17" height="17" viewbox="0 0 24 24" fill="var(--base)" xmlns="http://www.w3.org/2000/svg" size="17">
                        <path d="m15 12 .354-.354.353.354-.353.354L15 12ZM9.354 5.646l6 6-.708.708-6-6 .708-.708Zm6 6.708-6 6-.708-.708 6-6 .708.708Z" fill="var(--base)"></path>
                    </svg><a class="text-xs pl-1 border-b border-transparent hover:lg:border-primary transition-all duration-200 ease-in-out group-last:text-primary undefined" href="<?php echo $alamat_website . 'aduan'; ?>">Pengaduan Member</a>
                </div>
            </li>
        </ol>
    </nav>
    
    <h3 class="md:text-lg font-medium w-full text-white mb-4">Formulir Pengaduan</h3>
    
    <?php if ($message): ?>
    <div class="mt-4 p-4 rounded-md <?php echo ($message_type == 'success') ? 'bg-green-500/20 text-green-300' : 'bg-red-500/20 text-red-300'; ?>">
        <p><?php echo $message; ?></p>
    </div>
    <?php endif; ?>

    <div class="flex flex-wrap -mx-2 lg:-mx-3 mt-4">
        <div class="w-full px-[6px] lg:px-3 mb-3 lg:mb-6">
            <div class="bg-background-tertiary p-5 lg:p-8 rounded-lg">
                <form action="aduan" method="POST" enctype="multipart/form-data">
                    <div class="mb-4">
                        <label for="no_whatsapp" class="block text-gray-400 text-sm font-bold mb-2">Nomor WhatsApp</label>
                        <input type="text" id="no_whatsapp" name="no_whatsapp"
                               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline bg-gray-900 border-gray-700 text-white">
                    </div>
                    <div class="mb-4">
                        <label for="email" class="block text-gray-400 text-sm font-bold mb-2">Alamat Email</label>
                        <input type="email" id="email" name="email"
                               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline bg-gray-900 border-gray-700 text-white">
                    </div>
                    <div class="mb-4">
                        <label for="judul_aduan" class="block text-gray-400 text-sm font-bold mb-2">Judul Aduan</label>
                        <input type="text" id="judul_aduan" name="judul_aduan" required
                               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline bg-gray-900 border-gray-700 text-white">
                    </div>
                    <div class="mb-4">
                        <label for="isi_aduan" class="block text-gray-400 text-sm font-bold mb-2">Isi Aduan</label>
                        <textarea id="isi_aduan" name="isi_aduan" rows="6" required
                                  class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline bg-gray-900 border-gray-700 text-white"></textarea>
                    </div>
                    <div class="mb-4">
                        <label for="file_screenshot" class="block text-gray-400 text-sm font-bold mb-2">Upload Screenshot (Opsional)</label>
                        <input type="file" id="file_screenshot" name="file_screenshot" accept="image/*,.pdf"
                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-white hover:file:bg-yellow-600">
                    </div>
                    <div class="flex items-center justify-between">
                        <button type="submit"
                                class="bg-primary hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-300">
                            Kirim Aduan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="mt-8">
        <h3 class="md:text-lg font-medium w-full text-white mb-4">Riwayat Aduan Anda</h3>
        <div class="bg-background-tertiary p-5 lg:p-8 rounded-lg overflow-x-auto">
            <?php
            // Query untuk mengambil semua aduan dari user yang sedang login
            $query_riwayat = "SELECT id_aduan, judul_aduan, tanggal_aduan, status, isi_aduan, file_screenshot FROM aduan_member WHERE id_anggota = ? ORDER BY tanggal_aduan DESC";
            $stmt_riwayat = mysqli_prepare($koneksi, $query_riwayat);
            mysqli_stmt_bind_param($stmt_riwayat, "i", $id_anggota);
            mysqli_stmt_execute($stmt_riwayat);
            $result_riwayat = mysqli_stmt_get_result($stmt_riwayat);

            if (mysqli_num_rows($result_riwayat) > 0) {
            ?>
                <table class="min-w-full divide-y divide-gray-700">
                    <thead class="bg-gray-800">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">No. Tiket</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Judul</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider hidden sm:table-cell">Tanggal</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Detail</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        <?php while ($row = mysqli_fetch_assoc($result_riwayat)) { ?>
                            <tr>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-300"><?php echo htmlspecialchars($row['id_aduan']); ?></td>
                                <td class="px-4 py-4 max-w-[200px] truncate text-sm text-white"><?php echo htmlspecialchars($row['judul_aduan']); ?></td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-400 hidden sm:table-cell"><?php echo date('d-m-Y H:i', strtotime($row['tanggal_aduan'])); ?></td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm">
                                    <?php
                                        $status = htmlspecialchars($row['status']);
                                        $color_class = '';
                                        if ($status == 'resolved') {
                                            $color_class = 'bg-green-500/20 text-green-300';
                                        } elseif ($status == 'in_progress') {
                                            $color_class = 'bg-yellow-500/20 text-yellow-300';
                                        } else {
                                            $color_class = 'bg-red-500/20 text-red-300';
                                        }
                                    ?>
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $color_class; ?>">
                                        <?php echo ucfirst($status); ?>
                                    </span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-center">
                                    <button class="text-primary hover:text-yellow-600 font-medium detail-button"
                                            data-id="<?php echo htmlspecialchars($row['id_aduan']); ?>"
                                            data-judul="<?php echo htmlspecialchars($row['judul_aduan']); ?>"
                                            data-isi="<?php echo htmlspecialchars($row['isi_aduan']); ?>"
                                            data-tanggal="<?php echo date('d M Y H:i', strtotime($row['tanggal_aduan'])); ?>"
                                            data-status="<?php echo ucfirst(htmlspecialchars($row['status'])); ?>"
                                            data-screenshot="<?php echo htmlspecialchars($row['file_screenshot']); ?>">
                                        Lihat
                                    </button>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php } else { ?>
                <p class="text-center text-gray-400 py-8">Anda belum membuat aduan.</p>
            <?php } ?>
        </div>
    </div>
</section>

<div id="aduanDetailModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background-color: rgba(0, 0, 0, 0.7); display: none;">
    <div class="bg-background-tertiary rounded-lg shadow-lg w-full max-w-xl mx-auto p-6">
        <div class="flex justify-between items-center pb-3 border-b border-gray-700 mb-4">
            <h4 class="text-lg font-bold text-white">Detail Aduan</h4>
            <button id="closeModal" class="text-gray-400 hover:text-white">&times;</button>
        </div>
        <div class="text-sm">
            <div class="mb-2">
                <span class="font-bold text-gray-400">Judul:</span>
                <p id="modal-judul" class="text-white"></p>
            </div>
            <div class="mb-2">
                <span class="font-bold text-gray-400">Status:</span>
                <span id="modal-status" class="px-2 py-1 rounded-full text-xs font-semibold"></span>
            </div>
            <div class="mb-2">
                <span class="font-bold text-gray-400">Tanggal:</span>
                <p id="modal-tanggal" class="text-gray-300"></p>
            </div>
            <div class="mb-4">
                <span class="font-bold text-gray-400">Isi:</span>
                <p id="modal-isi" class="text-gray-300 mt-1 whitespace-pre-wrap"></p>
            </div>
            <div id="modal-screenshot-container" style="display: none;">
                <span class="font-bold text-gray-400 block mb-2">Screenshot:</span>
                <a href="#" target="_blank" id="modal-screenshot-link" class="block w-full h-auto">
                    <img id="modal-screenshot" src="" alt="Screenshot Aduan" class="w-full h-auto rounded-md shadow-md">
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('aduanDetailModal');
    const closeBtn = document.getElementById('closeModal');
    const detailButtons = document.querySelectorAll('.detail-button');

    detailButtons.forEach(button => {
        button.addEventListener('click', function() {
            const judul = this.getAttribute('data-judul');
            const isi = this.getAttribute('data-isi');
            const tanggal = this.getAttribute('data-tanggal');
            const status = this.getAttribute('data-status');
            const screenshot = this.getAttribute('data-screenshot');

            document.getElementById('modal-judul').textContent = judul;
            document.getElementById('modal-isi').textContent = isi;
            document.getElementById('modal-tanggal').textContent = tanggal;

            const modalStatus = document.getElementById('modal-status');
            modalStatus.textContent = status;
            modalStatus.className = 'px-2 py-1 rounded-full text-xs font-semibold';
            if (status.toLowerCase() === 'resolved') {
                modalStatus.classList.add('bg-green-500/20', 'text-green-300');
            } else if (status.toLowerCase() === 'in_progress') {
                modalStatus.classList.add('bg-yellow-500/20', 'text-yellow-300');
            } else {
                modalStatus.classList.add('bg-red-500/20', 'text-red-300');
            }
            
            const screenshotContainer = document.getElementById('modal-screenshot-container');
            if (screenshot && screenshot !== 'null') {
                const full_url = '<?php echo $alamat_website; ?>' + screenshot;
                document.getElementById('modal-screenshot-link').href = full_url;
                document.getElementById('modal-screenshot').src = full_url;
                screenshotContainer.style.display = 'block';
            } else {
                screenshotContainer.style.display = 'none';
            }

            modal.style.display = 'flex';
        });
    });

    closeBtn.addEventListener('click', function() {
        modal.style.display = 'none';
    });

    window.addEventListener('click', function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    });
});
</script>

<?php include_once 'footer.php'; ?>