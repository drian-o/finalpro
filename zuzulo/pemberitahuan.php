<?php
// Pastikan session kode_admin terdefinisi
if (!isset($_SESSION['kode_admin'])) {
    echo '
    <script>
        alert("Terjadi kesalahan, harap masuk kembali!");
        window.location.replace("' . $alamat_admin . 'keluar.php");
    </script>
    ';
    exit;
}

include_once '../koneksi.php';

// Query untuk mengambil semua data pemberitahuan
$query_pemberitahuan = "SELECT * FROM pemberitahuan ORDER BY waktu_dibuat DESC";
$result_pemberitahuan = $koneksi->query($query_pemberitahuan);

// Mengecek apakah ada pemberitahuan yang ditemukan


    // Verifikasi apakah permintaan POST telah dikirim
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_pemberitahuan'])) {
    include_once '../koneksi.php'; // Sesuaikan dengan lokasi koneksi.php

    // Pastikan session kode_admin terdefinisi
    if (!isset($_SESSION['kode_admin'])) {
        echo '
        <script>
            alert("Terjadi kesalahan, harap masuk kembali!");
            window.location.replace("' . $alamat_admin . 'keluar.php");
        </script>
        ';
        exit;
    }

    // Ambil id_pemberitahuan dari permintaan POST
    $id_pemberitahuan = $_POST['id_pemberitahuan'];

    // Query untuk menghapus pemberitahuan berdasarkan id_pemberitahuan
    $query_delete = "DELETE FROM pemberitahuan WHERE id_pemberitahuan = ?";

    // Mengecek koneksi database
    if ($koneksi) {
        // Prepare statement untuk query delete pemberitahuan
        $stmt_delete = $koneksi->prepare($query_delete);
        $stmt_delete->bind_param("i", $id_pemberitahuan);

         // Eksekusi query delete
         if ($stmt_delete->execute()) {
            echo '
            <script>
                alert("Pemberitahuan berhasil dihapus.");
                window.location.href = "pemberitahuan"; // Redirect ke halaman pemberitahuan setelah berhasil
            </script>
            ';
            exit;
        } else {
            echo '
            <script>
                alert("Gagal menghapus pemberitahuan: ' . $stmt_delete->error . '");
                window.history.back();
            </script>
            ';
            exit;
        }

        $stmt_delete->close();
    } else {
        echo '
        <script>
            alert("Koneksi database tidak tersedia.");
            window.history.back();
        </script>
        ';
        exit;
    }
}

// Proses kirim pemberitahuan jika metode adalah POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $username = $_POST['nama_pengguna'] ?? '';
    $message1 = $_POST['teks_pemberitahuan_1'] ?? '';
    $message2 = $_POST['teks_pemberitahuan_2'] ?? '';
    $message3 = $_POST['teks_pemberitahuan_3'] ?? '';
    $description = $_POST['keterangan'] ?? '';

    // Validasi data yang diperlukan
    if (empty($username) || empty($message1) || empty($message2) || empty($message3)) {
        echo '
        <script>
            alert("Semua kolom harus diisi.");
            window.history.back();
        </script>
        ';
        exit;
    }

    // Query untuk mencari nama_pengguna_anggota berdasarkan username
    $query_anggota = "SELECT nama_pengguna_anggota FROM anggota WHERE nama_pengguna_anggota = ?";
    
    // Mengecek koneksi database
    if ($koneksi) {
        // Prepare statement untuk mencari anggota
        $stmt_anggota = $koneksi->prepare($query_anggota);
        $stmt_anggota->bind_param("s", $username);
        $stmt_anggota->execute();
        $result_anggota = $stmt_anggota->get_result();

        // Jika ditemukan anggota dengan username yang cocok
        if ($result_anggota->num_rows > 0) {
            $row = $result_anggota->fetch_assoc();
            $nama_pengguna_anggota = $row['nama_pengguna_anggota'];

            // Query untuk menyimpan data ke dalam tabel pemberitahuan
            $query_insert = "INSERT INTO pemberitahuan (nama_pengguna_pemberitahuan, teks_pemberitahuan_1, teks_pemberitahuan_2, teks_pemberitahuan_3, keterangan, status_baca, waktu_dibuat)
                            VALUES (?, ?, ?, ?, ?, 0, NOW())";

            // Prepare statement untuk query insert pemberitahuan
            $stmt_insert = $koneksi->prepare($query_insert);
            $stmt_insert->bind_param("sssss", $nama_pengguna_anggota, $message1, $message2, $message3, $description);

            // Eksekusi query insert
            if ($stmt_insert->execute()) {
                // Query untuk menghitung jumlah pemberitahuan yang belum dibaca
                $query_unread_count = "SELECT COUNT(*) as unread_count FROM pemberitahuan WHERE nama_pengguna_pemberitahuan = ? AND status_baca = 0";
                $stmt_unread_count = $koneksi->prepare($query_unread_count);
                $stmt_unread_count->bind_param("s", $nama_pengguna_anggota);
                $stmt_unread_count->execute();
                $result_unread_count = $stmt_unread_count->get_result();

                if ($row = $result_unread_count->fetch_assoc()) {
                    $unread_count = $row['unread_count'];
                } else {
                    $unread_count = 0; // Jika tidak ada pemberitahuan yang belum dibaca
                }

                $stmt_unread_count->close();

                echo '
                <script>
                    alert("Pemberitahuan berhasil dikirim.");
                    window.location.replace("pemberitahuan");
                </script>
                ';
                exit;
            } else {
                echo '
                <script>
                    alert("Gagal mengirim pemberitahuan: ' . $stmt_insert->error . '");
                    window.location.replace("pemberitahuan");
                </script>
                ';
                exit;
            }

            $stmt_insert->close();
        } else {
            echo '
            <script>
                alert("Username tidak ditemukan.");
                window.location.replace("pemberitahuan");
            </script>
            ';
            exit;
        }

        $stmt_anggota->close();
    } else {
        echo '
        <script>
            alert("Koneksi database tidak tersedia.");
            window.location.replace("pemberitahuan");
        </script>
        ';
        exit;
    }
}
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row gy-4 mb-4">
        <div class="col-md-6">
            <div class="fw-bold fs-4 text-center text-md-start">Daftar Pemberitahuan</div>
        </div>
        <div class="col-md-6">
            <div class="text-center text-md-end">
                <button id="btnKirimPemberitahuan" class="btn btn-sm btn-primary waves-effect waves-light">
                    <span class="tf-icons mdi mdi-plus me-1"></span>
                    Kirim Pemberitahuan
                </button>
            </div>
        </div>
    </div>

     <!-- Modal Kirim Pemberitahuan -->
     <div class="modal fade" id="modalKirimPemberitahuan" tabindex="-1" aria-labelledby="modalKirimPemberitahuanLabel" aria-hidden="true">
     <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalKirimPemberitahuanLabel">Kirim Pemberitahuan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Form Pemberitahuan -->
                    <form method="post">
                        <div class="mb-3">
                            <label for="nama_pengguna" class="form-label">Nama Pengguna</label>
                            <input type="text" class="form-control" id="nama_pengguna" name="nama_pengguna" required>
                        </div>
                        <div class="mb-3">
                            <label for="teks_pemberitahuan_1" class="form-label">Teks Notifikasi</label>
                            <input type="text" class="form-control" id="teks_pemberitahuan_1" name="teks_pemberitahuan_1" rows="3" required>
                        </div>
                        <div class="mb-3">
                            <label for="teks_pemberitahuan_2" class="form-label">Status</label>
                            <input type="text" class="form-control" id="teks_pemberitahuan_2" name="teks_pemberitahuan_2" rows="3" required>
                        </div>
                        <div class="mb-3">
                            <label for="teks_pemberitahuan_3" class="form-label">Waktu</label>
                            <input type="text" class="form-control" id="teks_pemberitahuan_3" name="teks_pemberitahuan_3" rows="3" required>
                        </div>
                        <div class="mb-3">
                            <label for="keterangan" class="form-label">Keterangan</label>
                            <textarea class="form-control" id="keterangan" name="keterangan" required></textarea>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary waves-effect waves-light">Kirim Pemberitahuan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="card table-responsive p-3">
        <table class="table text-center" id="example">
            <thead>
                <tr>
                    <th scope="col" class="text-center">#</th>
                    <th scope="col" class="text-center">Nama Pengguna</th>
                    <th scope="col" class="text-center">Teks Pemberitahuan 1</th>
                    <th scope="col" class="text-center">Teks Pemberitahuan 2</th>
                    <th scope="col" class="text-center">Teks Pemberitahuan 3</th>
                    <th scope="col" class="text-center">Keterangan</th>
                    <th scope="col" class="text-center">Status Baca</th>
                    <th scope="col" class="text-center">Waktu Dibuat</th>
                    <th scope="col" class="text-center">Aksi</th> <!-- Kolom Aksi -->
                </tr>
            </thead>
            <tbody>
    <?php
    // Nomor urut counter
    $counter = 1;

    // Output data dari setiap baris
    while ($row = $result_pemberitahuan->fetch_assoc()) {
        // Pisahkan tanggal dan jam
        $waktu_dibuat = $row['waktu_dibuat'];
        $tanggal = date('Y-m-d', strtotime($waktu_dibuat));
        $jam = date('H:i:s', strtotime($waktu_dibuat));

        // Tentukan kelas CSS berdasarkan status baca
        $status_class = ($row['status_baca'] == 0) ? 'text-warning' : 'text-success';

        echo '<tr>';
        echo '<td class="text-center">' . $counter++ . '</td>';
        echo '<td class="text-center">' . htmlspecialchars($row['nama_pengguna_pemberitahuan']) . '</td>';
        echo '<td class="text-center" style="word-wrap: break-word; max-width: 200px;">' . htmlspecialchars($row['teks_pemberitahuan_1']) . '</td>';
        echo '<td class="text-center" style="word-wrap: break-word; max-width: 200px;">' . htmlspecialchars($row['teks_pemberitahuan_2']) . '</td>';
        echo '<td class="text-center" style="word-wrap: break-word; max-width: 200px;">' . htmlspecialchars($row['teks_pemberitahuan_3']) . '</td>';
        echo '<td class="text-center" style="word-wrap: break-word; max-width: 200px;">' . htmlspecialchars($row['keterangan']) . '</td>';
        echo '<td class="text-center ' . $status_class . '">' . ($row['status_baca'] == 0 ? 'Belum Dibaca' : 'Sudah Dibaca') . '</td>';
        echo '<td class="text-center" >';
        echo '<div>' . $tanggal . '</div>';
        echo '<div>' . $jam . '</div>';
        echo '</td>';
        echo '<td class="text-center">';
        echo '<form method="post">';
        echo '<input type="hidden" name="id_pemberitahuan" value="' . $row['id_pemberitahuan'] . '">';
        echo '<button type="submit" class="btn btn-danger btn-sm" onclick="return confirm(\'Apakah Anda yakin ingin menghapus pemberitahuan ini?\')">Hapus</button>';
        echo '</form>';
        echo '</td>';
        echo '</tr>';
    }
    ?>
            </tbody>
        </table>
    </div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<script>
$(document).ready(function() {
    $('#btnKirimPemberitahuan').click(function() {
        $('#modalKirimPemberitahuan').modal('show');
    });
});
</script>

