<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once '../koneksi.php';

if (!isset($alamat_admin)) {
    $alamat_admin = '/admin/';
}

if (!isset($_SESSION['kode_admin'])) {
    echo '
      <script>
        alert("Sesi Anda telah berakhir atau tidak valid. Harap masuk kembali!");
        window.location.replace("'.$alamat_admin.'keluar.php");
      </script>
    ';
    exit();
}

$db_connected = false;
$db_connection_var = null;

if (isset($koneksi) && $koneksi instanceof mysqli) {
    $db_connected = true;
    $db_connection_var = $koneksi;
} elseif (isset($koneksi_manual) && $koneksi_manual instanceof mysqli) {
    $db_connected = true;
    $db_connection_var = $koneksi_manual;
}

$providers_from_db = [];
$message_provider_load = '';

if ($db_connected) {
    $sql_get_providers = "SELECT providerid, providername FROM tb_providerbaru ORDER BY providername ASC";
    $result_providers = $db_connection_var->query($sql_get_providers);
    if ($result_providers && $result_providers->num_rows > 0) {
        while ($row = $result_providers->fetch_assoc()) {
            $providers_from_db[] = $row;
        }
    } else {
        $message_provider_load = '<div class="alert alert-warning">Tidak ada provider ditemukan di database <code>tb_providerbaru</code>.</div>';
    }
} else {
    $message_provider_load = '<div class="alert alert-danger">Kesalahan Koneksi Database. Tidak dapat memuat daftar provider.</div>';
}

$base_url_path_js = '';
$base_upload_folder_name_js = 'uploads';
?>

<div class="container-xxl flex-grow-1 container-p-y">
  <h4 class="py-3 mb-4">
    <span class="text-muted fw-light">Manajemen Game /</span> Daftar Game Provider
  </h4>

    <?php
    if (!empty($message_provider_load) && empty($providers_from_db)) {
        echo $message_provider_load;
    }
    ?>

  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Pilih Filter Game</h5>
        </div>
        <div class="card-body">
            <?php if ($db_connected && !empty($providers_from_db)): ?>
            <form id="providerForm">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label for="provider_select" class="form-label">Provider:</label>
                        <select class="form-select" id="provider_select" name="provider_code">
                            <option value="">-- Pilih Provider --</option>
                            <?php foreach ($providers_from_db as $provider): ?>
                                <option value="<?= htmlspecialchars($provider['providerid'] ?? '') ?>">
                                    <?= htmlspecialchars($provider['providername'] ?? '') ?> (<?= htmlspecialchars($provider['providerid'] ?? '') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="sort_column" class="form-label">Urutkan Berdasarkan:</label>
                        <select class="form-select" id="sort_column" name="sort_column">
                            <option value="game_name">Nama Game</option>
                            <option value="id">ID Game</option>
                            <option value="game_code">Kode Game</option>
                            <option value="status">Status</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                         <label for="sort_order" class="form-label">Urutan:</label>
                        <select class="form-select" id="sort_order" name="sort_order">
                            <option value="ASC">A-Z (Naik)</option>
                            <option value="DESC">Z-A (Turun)</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="button" id="showGamesButton" class="btn btn-primary w-100">Tampilkan Game</button>
                    </div>
                </div>
            </form>
            <?php elseif (!$db_connected): ?>
                 <div class="alert alert-danger">Tidak dapat menampilkan pilihan provider karena masalah koneksi database.</div>
            <?php else:
                echo '<div class="alert alert-info">Tidak ada data provider yang ditemukan di sistem.</div>';
            endif; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="row mt-4">
    <div class="col-md-12">
        <div id="gameListContainer">
            <div class="card"><div class="card-body"><p class="text-muted text-center">Silakan pilih provider dan klik "Tampilkan Game" untuk melihat daftar game.</p></div></div>
        </div>
    </div>
  </div>

</div>
<div class="modal fade" id="editGameModal" tabindex="-1" aria-labelledby="editGameModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editGameModalLabel">Edit Game</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="editGameMessage" class="mb-3"></div>
        <form id="editGameForm" enctype="multipart/form-data">
          <input type="hidden" name="game_id" id="edit_game_id">
          <input type="hidden" name="existing_banner_path" id="edit_existing_banner_path">

          <div class="mb-3">
            <label for="edit_game_code" class="form-label">Game Code:</label>
            <input type="text" readonly class="form-control-plaintext" id="edit_game_code">
          </div>
          <div class="mb-3">
            <label for="edit_provider" class="form-label">Provider:</label>
            <input type="text" readonly class="form-control-plaintext" id="edit_provider">
          </div>
          <hr>
          <div class="mb-3">
            <label for="edit_game_name" class="form-label">Nama Game:</label>
            <input type="text" class="form-control" id="edit_game_name" name="game_name" required>
          </div>
          <div class="mb-3">
            <label for="edit_banner_upload" class="form-label">Upload Banner Baru (Kosongkan jika tidak diubah):</label>
            <input type="file" class="form-control" id="edit_banner_upload" name="banner_upload">
            <small class="form-text text-muted">Maks 5MB (JPG, JPEG, PNG, GIF, WEBP).</small>
            <div id="currentBannerPreview" class="mt-2" style="max-height: 150px; overflow: hidden;"></div>
          </div>
          <div class="mb-3">
            <label for="edit_status" class="form-label">Status:</label>
            <select class="form-select" id="edit_status" name="status">
              <option value="1">Aktif (1)</option>
              <option value="0">Tidak Aktif (0)</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="edit_sort" class="form-label">Urutan (Sort):</label>
            <input type="number" class="form-control" id="edit_sort" name="sort" required>
          </div>
          <div class="mb-3">
            <label for="edit_frbavailable" class="form-label">FRB Available:</label>
            <select class="form-select" id="edit_frbavailable" name="frbavailable">
              <option value="1">Yes (1)</option>
              <option value="0">No (0)</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="edit_provideragent" class="form-label">Provider Agent:</label>
            <input type="text" class="form-control" id="edit_provideragent" name="provideragent">
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        <button type="button" class="btn btn-primary" id="saveGameChangesButton">Simpan Perubahan</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const showGamesButton = document.getElementById('showGamesButton');
    const gameListContainer = document.getElementById('gameListContainer');
    const providerSelect = document.getElementById('provider_select');
    const sortColumnSelect = document.getElementById('sort_column');
    const sortOrderSelect = document.getElementById('sort_order');
    
    const editGameModalElement = document.getElementById('editGameModal');
    const editGameModalInstance = new bootstrap.Modal(editGameModalElement);
    const editGameForm = document.getElementById('editGameForm');
    const saveGameChangesButton = document.getElementById('saveGameChangesButton');
    const editGameMessageContainer = document.getElementById('editGameMessage');
    const currentBannerPreviewContainer = document.getElementById('currentBannerPreview');

    const BASE_WEB_PATH = '<?php echo rtrim($base_url_path_js, '/') . '/'; ?>';
    const UPLOADS_FOLDER_NAME = '<?php echo trim($base_upload_folder_name_js, '/'); ?>';


    function fetchAndDisplayGames() {
        const providerCode = providerSelect.value;
        const sortBy = sortColumnSelect.value;
        const sortOrder = sortOrderSelect.value;

        if (!providerCode) {
            gameListContainer.innerHTML = '<div class="card"><div class="card-body"><div class="alert alert-warning mb-0">Silakan pilih provider terlebih dahulu.</div></div></div>';
            return;
        }
        gameListContainer.innerHTML = '<div class="card"><div class="card-body"><p class="text-center">Memuat data game...</p></div></div>'; 

        const formData = new FormData();
        formData.append('provider_code', providerCode);
        formData.append('sort_by', sortBy);
        formData.append('sort_order', sortOrder);

        fetch('ajax_get_games.php', { method: 'POST', body: formData })
        .then(response => {
            if (!response.ok) throw new Error('Network response error: ' + response.status + ' ' + response.statusText);
            return response.text();
        })
        .then(data => {
            gameListContainer.innerHTML = data;
            attachTableActionListeners(); 
        })
        .catch(error => {
            console.error('Error fetching games:', error);
            gameListContainer.innerHTML = `<div class="card"><div class="card-body"><div class="alert alert-danger mb-0">Gagal memuat game: ${error.message}</div></div></div>`;
        });
    }

    if (showGamesButton) {
        showGamesButton.addEventListener('click', fetchAndDisplayGames);
    }

    function attachTableActionListeners() {
        const editButtons = gameListContainer.querySelectorAll('.edit-game-btn');
        editButtons.forEach(button => {
            const newButton = button.cloneNode(true);
            button.parentNode.replaceChild(newButton, button);
            
            newButton.addEventListener('click', function() {
                const gameId = this.dataset.gameId;
                loadGameDetailsIntoModal(gameId);
            });
        });

        const sortableHeaders = gameListContainer.querySelectorAll('.sortable-header');
        sortableHeaders.forEach(header => {
            const newHeader = header.cloneNode(true);
            if(header.parentNode) header.parentNode.replaceChild(newHeader, header);
            
            newHeader.addEventListener('click', function() {
                const column = this.dataset.sortcol;
                let newSortOrder = (sortColumnSelect.value === column && sortOrderSelect.value === 'ASC') ? 'DESC' : 'ASC';
                if (sortColumnSelect.value !== column) newSortOrder = 'ASC';
                
                sortColumnSelect.value = column;
                sortOrderSelect.value = newSortOrder;
                fetchAndDisplayGames();
            });
        });

        const copyNameIcons = gameListContainer.querySelectorAll('.copy-game-name-icon');
        copyNameIcons.forEach(icon => {
            const newIcon = icon.cloneNode(true);
            icon.parentNode.replaceChild(newIcon, icon);

            newIcon.addEventListener('click', function(event) {
                event.stopPropagation();
                const gameNameToCopy = this.dataset.gameName;
                
                if (gameNameToCopy) {
                    navigator.clipboard.writeText(gameNameToCopy)
                        .then(() => {
                            const originalIconClass = this.className;
                            const originalTitle = this.title;
                            this.className = "fas fa-check-circle";
                            this.style.color = "green";
                            this.title = "Disalin!";

                            setTimeout(() => {
                                this.className = originalIconClass;
                                this.style.color = "#007bff";
                                this.title = originalTitle;
                            }, 1500);
                        })
                        .catch(err => {
                            console.error('Gagal menyalin nama game: ', err);
                            alert('Gagal menyalin. Mungkin browser Anda tidak mendukung fitur ini atau tidak ada izin.');
                        });
                }
            });
        });
    }

    function loadGameDetailsIntoModal(gameId) {
        editGameMessageContainer.innerHTML = ''; 
        currentBannerPreviewContainer.innerHTML = '';
        editGameForm.reset(); 
        document.getElementById('edit_banner_upload').value = '';

        document.getElementById('edit_game_id').value = gameId;
        document.getElementById('editGameModalLabel').textContent = 'Memuat Detail Game...';

        fetch(`ajax_get_game_details.php?game_id=${gameId}`)
        .then(response => {
            if (!response.ok) throw new Error('Gagal mengambil detail game: ' + response.status + ' ' + response.statusText);
            return response.json();
        })
        .then(data => {
            if (data.status === 'success' && data.game) {
                const game = data.game;
                document.getElementById('editGameModalLabel').textContent = 'Edit Game: ' + (game.game_name || 'Tanpa Nama');
                document.getElementById('edit_game_id').value = game.id;
                document.getElementById('edit_game_code').value = game.game_code || '';
                document.getElementById('edit_provider').value = game.provider || '';
                document.getElementById('edit_game_name').value = game.game_name || '';
                document.getElementById('edit_status').value = game.status || '1';
                document.getElementById('edit_sort').value = game.sort || '0';
                document.getElementById('edit_frbavailable').value = String(game.frbavailable || '0');
                document.getElementById('edit_provideragent').value = game.provideragent || '';
                document.getElementById('edit_existing_banner_path').value = game.banner || '';

                if (game.banner) {
                    let bannerSrc = '';
                    if (game.banner.startsWith('http://') || game.banner.startsWith('https://')) {
                        bannerSrc = game.banner;
                    } else {
                        let cleanBasePath = BASE_WEB_PATH.endsWith('/') ? BASE_WEB_PATH : BASE_WEB_PATH + '/';
                        let cleanBannerPath = game.banner.startsWith('/') ? game.banner.substring(1) : game.banner;
                        bannerSrc = cleanBasePath + cleanBannerPath;
                    }
                    currentBannerPreviewContainer.innerHTML = `<p class="mb-1 small text-muted">Banner Saat Ini:</p><img src="${bannerSrc}" alt="Banner" class="img-thumbnail" style="max-width: 100%; height: auto; max-height:120px;">`;
                }
                editGameModalInstance.show();
            } else {
                alert('Gagal memuat data game: ' + (data.message || 'Format data tidak sesuai.'));
                 document.getElementById('editGameModalLabel').textContent = 'Edit Game';
            }
        })
        .catch(error => {
            console.error('Error loading game details:', error);
            alert('Error memuat detail: ' + error.message);
            document.getElementById('editGameModalLabel').textContent = 'Edit Game';
        });
    }

    if (saveGameChangesButton) {
        saveGameChangesButton.addEventListener('click', function() {
            editGameMessageContainer.innerHTML = '<div class="alert alert-info p-2">Menyimpan perubahan...</div>';
            const formData = new FormData(editGameForm);

            fetch('ajax_update_game.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(errData => {
                        throw new Error('Network response error: ' + response.status + ' ' + response.statusText + (errData.message ? ' - Server: ' + errData.message : ''));
                    }).catch(() => {
                        throw new Error('Network response error: ' + response.status + ' ' + response.statusText);
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    editGameMessageContainer.innerHTML = `<div class="alert alert-success p-2">${data.message || 'Game berhasil diperbarui!'}</div>`;
                    fetchAndDisplayGames(); 
                    
                    if(data.new_banner_path){
                        let bannerSrc = '';
                         if (data.new_banner_path.startsWith('http://') || data.new_banner_path.startsWith('https://')) {
                            bannerSrc = data.new_banner_path;
                        } else {
                            let cleanBasePath = BASE_WEB_PATH.endsWith('/') ? BASE_WEB_PATH : BASE_WEB_PATH + '/';
                            let cleanBannerPath = data.new_banner_path.startsWith('/') ? data.new_banner_path.substring(1) : data.new_banner_path;
                            bannerSrc = cleanBasePath + cleanBannerPath;
                        }
                        currentBannerPreviewContainer.innerHTML = `<p class="mb-1 small text-muted">Banner Saat Ini:</p><img src="${bannerSrc}" alt="Banner" class="img-thumbnail" style="max-width: 100%; height: auto; max-height:120px;">`;
                        document.getElementById('edit_existing_banner_path').value = data.new_banner_path;
                    }
                    document.getElementById('edit_banner_upload').value = '';

                    setTimeout(() => {
                        if (data.status === 'success') {
                        }
                    }, 1500);
                } else {
                    let errorMsg = data.message || 'Gagal memperbarui game.';
                    if (data.errors && typeof data.errors === 'object') {
                        errorMsg += '<ul class="mb-0 ps-3">';
                        for (const key in data.errors) {
                            errorMsg += `<li>${data.errors[key]}</li>`;
                        }
                        errorMsg += '</ul>';
                    } else if (data.errors && typeof data.errors === 'string') {
                         errorMsg += '<br>' + data.errors;
                    }
                    editGameMessageContainer.innerHTML = `<div class="alert alert-danger p-2">${errorMsg}</div>`;
                }
            })
            .catch(error => {
                console.error('Error updating game:', error);
                editGameMessageContainer.innerHTML = `<div class="alert alert-danger p-2">Error: ${error.message}</div>`;
            });
        });
    }

    if(editGameModalElement){
        editGameModalElement.addEventListener('hidden.bs.modal', function () {
            editGameMessageContainer.innerHTML = '';
            currentBannerPreviewContainer.innerHTML = '';
            editGameForm.reset();
            document.getElementById('edit_banner_upload').value = '';
            document.getElementById('editGameModalLabel').textContent = 'Edit Game';
        });
    }

});
</script>

<?php
if ($db_connected && isset($db_connection_var) && $db_connection_var instanceof mysqli) {
   $db_connection_var->close();
}
?>