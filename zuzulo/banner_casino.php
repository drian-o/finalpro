<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include_once '../koneksi.php'; // Sesuaikan path jika perlu

// Pastikan admin sudah login
if (!isset($_SESSION['kode_admin'])) {
    echo '
      <script>
        alert("Sesi Anda telah berakhir atau tidak valid. Harap masuk kembali!");
        window.location.replace("'.$alamat_admin.'keluar.php");
      </script>
    ';
    exit();
}

// Path ke direktori upload banner (relatif dari root website)
$banner_upload_dir_relative_to_root = "uploads/livecasino_banners/";
// Path untuk menampilkan gambar dari file PHP ini
$banner_display_path = "../uploads/livecasino_banners/";


// Ambil daftar provider unik
$providers = [];
$sql_providers = "SELECT DISTINCT provider FROM gamelist_livecasino ORDER BY provider ASC";
$result_providers = mysqli_query($koneksi, $sql_providers);
if ($result_providers) {
    while ($row = mysqli_fetch_assoc($result_providers)) {
        $providers[] = $row['provider'];
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-T">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Game Live Casino</title>
    <!-- Tambahkan link ke Bootstrap CSS dan jQuery jika belum ada -->
    <!-- Contoh: -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .banner-img {
            max-width: 150px;
            max-height: 100px;
            object-fit: cover;
        }
        .table th, .table td {
            vertical-align: middle;
        }
    </style>
</head>
<body>

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">Manajemen Game /</span> Live Casino
    </h4>

    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-md-4">
                    <label for="providerFilter" class="form-label">Pilih Provider:</label>
                    <select id="providerFilter" class="form-select">
                        <option value="">Semua Provider</option>
                        <?php foreach ($providers as $provider) : ?>
                            <option value="<?php echo htmlspecialchars($provider); ?>"><?php echo htmlspecialchars($provider); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Nama Game</th>
                            <th>Banner</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="gameTableBody">
                        <!-- Data game akan dimuat di sini oleh AJAX -->
                        <tr><td colspan="3" class="text-center">Pilih provider untuk menampilkan game.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Banner -->
<div class="modal fade" id="editBannerModal" tabindex="-1" aria-labelledby="editBannerModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editBannerModalLabel">Edit Banner Game</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="editBannerForm" enctype="multipart/form-data">
          <input type="hidden" id="gameId" name="game_id">
          <div class="mb-3">
            <label for="bannerFile" class="form-label">Pilih File Banner Baru (JPG, PNG, GIF, WEBP):</label>
            <input class="form-control" type="file" id="bannerFile" name="banner_file" accept="image/jpeg,image/png,image/gif,image/webp" required>
          </div>
          <div class="mb-3">
            <img id="currentBannerPreview" src="#" alt="Current Banner" style="max-width: 100%; max-height: 200px; display: none;">
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" id="saveBannerButton">Simpan Perubahan</button>
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
    const bannerDisplayPath = '<?php echo $banner_display_path; ?>';

    // Fungsi untuk memuat game berdasarkan provider
    function loadGames(provider) {
        $('#gameTableBody').html('<tr><td colspan="3" class="text-center">Memuat data...</td></tr>');
        $.ajax({
            url: 'ajax_get_livecasino_games.php', // Path ke skrip AJAX
            type: 'GET',
            data: { provider: provider },
            success: function(response) {
                $('#gameTableBody').html(response);
            },
            error: function() {
                $('#gameTableBody').html('<tr><td colspan="3" class="text-center text-danger">Gagal memuat data.</td></tr>');
            }
        });
    }

    // Event listener untuk perubahan dropdown provider
    $('#providerFilter').change(function() {
        var selectedProvider = $(this).val();
        if (selectedProvider) {
            loadGames(selectedProvider);
        } else {
            $('#gameTableBody').html('<tr><td colspan="3" class="text-center">Pilih provider untuk menampilkan game.</td></tr>');
        }
    });

    // Event listener untuk tombol Edit Banner (menggunakan event delegation)
    $(document).on('click', '.edit-banner-btn', function() {
        var gameId = $(this).data('id');
        var currentGameName = $(this).data('gamename');
        var currentBannerFile = $(this).data('bannerfile'); // Nama file banner saat ini

        $('#gameId').val(gameId);
        $('#editBannerModalLabel').text('Edit Banner untuk: ' + currentGameName);
        
        if (currentBannerFile && currentBannerFile !== 'null' && currentBannerFile !== '') {
            $('#currentBannerPreview').attr('src', bannerDisplayPath + currentBannerFile + '?' + new Date().getTime()).show(); // Tambah cache buster
        } else {
            $('#currentBannerPreview').attr('src', '#').hide();
        }
        
        $('#bannerFile').val(''); // Reset file input
        var editModal = new bootstrap.Modal(document.getElementById('editBannerModal'));
        editModal.show();
    });

    // Event listener untuk tombol Simpan Perubahan di modal
    $('#saveBannerButton').click(function() {
        var form = $('#editBannerForm')[0];
        var formData = new FormData(form);
        var gameId = $('#gameId').val();

        if (!$('#bannerFile').val()) {
            alert('Silakan pilih file banner terlebih dahulu.');
            return;
        }

        $(this).prop('disabled', true).text('Menyimpan...');

        $.ajax({
            url: 'ajax_update_livecasino_banner.php', // Path ke skrip AJAX update
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('Banner berhasil diperbarui!');
                    // Perbarui gambar di tabel secara dinamis
                    var newBannerUrl = bannerDisplayPath + response.newBannerFilename + '?' + new Date().getTime();
                    $('img[data-gameid="' + gameId + '"]').attr('src', newBannerUrl);
                    // Perbarui data-bannerfile pada tombol edit juga
                    $('.edit-banner-btn[data-id="' + gameId + '"]').data('bannerfile', response.newBannerFilename);
                    
                    var editModal = bootstrap.Modal.getInstance(document.getElementById('editBannerModal'));
                    editModal.hide();
                    // Opsional: Muat ulang daftar game jika diperlukan
                    // loadGames($('#providerFilter').val()); 
                } else {
                    alert('Gagal memperbarui banner: ' + response.message);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Terjadi kesalahan AJAX: ' + textStatus + ' - ' + errorThrown);
                console.error("AJAX Error:", jqXHR.responseText);
            },
            complete: function() {
                $('#saveBannerButton').prop('disabled', false).text('Simpan Perubahan');
            }
        });
    });

    // Preview gambar yang dipilih di modal
    $("#bannerFile").change(function() {
        const file = this.files[0];
        if (file) {
            let reader = new FileReader();
            reader.onload = function(event) {
                $("#currentBannerPreview").attr("src", event.target.result).show();
            };
            reader.readAsDataURL(file);
        } else {
             $("#currentBannerPreview").hide();
        }
    });
});
</script>

</body>
</html>
