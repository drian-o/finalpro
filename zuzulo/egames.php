<?php
  if (session_status() == PHP_SESSION_NONE) {
      session_start();
  }
  include_once '../koneksi.php';

  if (!isset($_SESSION['kode_admin'])) {
    $redirect_url = isset($alamat_admin) ? $alamat_admin . 'keluar.php' : 'keluar.php';
    echo '
      <script>
        alert("Sesi Anda telah berakhir atau tidak valid. Harap masuk kembali!");
        window.location.replace("'.$redirect_url.'");
      </script>
    ';
    exit();
  }

  $game_tables_map = [
    'gamelist_action' => 'Action Games',
    'gamelist_external' => 'External Games',
    'gamelist_fishinghunter' => 'Fishing & Hunter',
    'gamelist_future' => 'Future Games',
    'gamelist_ot' => 'OT Games',
    'gamelist_pokercard' => 'Poker & Card',
    'gamelist_sportsbook' => 'Sportsbook',
    'gamelist_lotterykeno' => 'Lottery & Keno',
    'gamelist_livecasino' => 'Live Casino'
  ];
?>
<div class="container-xxl flex-grow-1 container-p-y">
  <h4 class="py-3 mb-4">
    <span class="text-muted fw-light">Manajemen Game /</span> Egames Lists (Sortable)
  </h4>

  <div class="card mb-4">
    <div class="card-header"><h5 class="mb-0">Filter Game dari Sumber</h5></div>
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-md-5">
                <label for="gameTableSelector" class="form-label">Pilih Jenis Game Sumber:</label>
                <select id="gameTableSelector" class="form-select">
                    <option value="">-- Pilih Tabel Game --</option>
                    <?php foreach ($game_tables_map as $table_name => $display_name): ?>
                    <option value="<?php echo htmlspecialchars($table_name); ?>"><?php echo htmlspecialchars($display_name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-auto">
                <button type="button" id="showGamesBtn" class="btn btn-primary">Tampilkan Game</button>
            </div>
        </div>
    </div>
  </div>
  
  <div id="messageArea" class="mb-3"></div>

  <div class="card">
    <div class="card-header"><h5 class="card-title mb-0" id="gameListTitle">Daftar Game</h5></div>
    <div class="card-body">
        <div id="gameListContainer" class="table-responsive text-nowrap">
            <p class="text-muted text-center">Silakan pilih jenis game dan klik "Tampilkan Game" untuk melihat daftar.</p>
        </div>
    </div>
  </div>
</div>

<div class="modal fade" id="addGameModal" tabindex="-1" aria-labelledby="addGameModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addGameModalLabel">Tambahkan Game ke Daftar Egames</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="modalMessageArea" class="mb-3"></div>
        <form id="addGameForm" enctype="multipart/form-data">
          <input type="hidden" id="modal_original_id" name="original_id">
          <input type="hidden" id="modal_game_code" name="game_code">
          <input type="hidden" id="modal_game_name" name="game_name">
          <input type="hidden" id="modal_provider" name="provider">
          <input type="hidden" id="modal_sort" name="sort">
          <input type="hidden" id="modal_lang" name="lang">
          <input type="hidden" id="modal_status" name="status">
          <input type="hidden" id="modal_frbavailable" name="frbavailable">
          <input type="hidden" id="modal_provideragent" name="provideragent">
          <input type="hidden" id="modal_game_vendor" name="game_vendor">
          <input type="hidden" id="modal_original_banner" name="original_banner">

          <div class="mb-3 row"><label class="col-sm-4 col-form-label">Game Code:</label><div class="col-sm-8"><p class="form-control-plaintext" id="confirm_game_code"></p></div></div>
          <div class="mb-3 row"><label class="col-sm-4 col-form-label">Nama Game:</label><div class="col-sm-8"><p class="form-control-plaintext" id="confirm_game_name"></p></div></div>
          <div class="mb-3 row"><label class="col-sm-4 col-form-label">Provider:</label><div class="col-sm-8"><p class="form-control-plaintext" id="confirm_provider"></p></div></div>
          
          <hr/><p class="text-muted small"><em>Kolom di bawah ini dapat Anda ubah sebelum ditambahkan:</em></p>

          <div class="mb-3 row">
            <label for="editable_jenis_game" class="col-sm-4 col-form-label">Jenis Game (Target):</label>
            <div class="col-sm-8">
              <input type="text" class="form-control" id="editable_jenis_game" name="jenis_game">
              <small class="form-text text-muted">Contoh: action, slot, fishing. (Default dari sumber)</small>
            </div>
          </div>

          <div class="mb-3 row">
            <label for="editable_banner_file_upload" class="col-sm-4 col-form-label">Upload Banner Baru (Opsional):</label>
            <div class="col-sm-8">
              <input type="file" class="form-control" id="editable_banner_file_upload" name="banner_file_upload" accept="image/jpeg,image/png,image/gif,image/webp">
              <div id="editable_banner_preview_container" class="mt-2" style="max-height: 100px; overflow: hidden; border: 1px dashed #ccc; text-align:center; padding: 5px;">
                <small class="text-muted">Preview banner (Max 5MB).</small>
              </div>
              <small class="form-text text-muted">Jika tidak upload baru, banner asli akan digunakan: <span id="original_banner_info">Tidak ada</span></small>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" id="confirmAddGameBtn">Ya, Tambahkan</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const gameTableSelector = document.getElementById('gameTableSelector');
    const showGamesBtn = document.getElementById('showGamesBtn');
    const gameListContainer = document.getElementById('gameListContainer');
    const gameListTitle = document.getElementById('gameListTitle');
    const messageArea = document.getElementById('messageArea');

    const addGameModalElement = document.getElementById('addGameModal');
    const addGameModalInstance = new bootstrap.Modal(addGameModalElement);
    const modalMessageArea = document.getElementById('modalMessageArea');
    const confirmAddGameBtn = document.getElementById('confirmAddGameBtn');
    
    const editableJenisGameInput = document.getElementById('editable_jenis_game');
    const editableBannerFileUploadInput = document.getElementById('editable_banner_file_upload');
    const editableBannerPreviewContainer = document.getElementById('editable_banner_preview_container');
    const originalBannerInfoSpan = document.getElementById('original_banner_info');

    const gameTablesMap = <?php echo json_encode($game_tables_map); ?>;

    let currentSortBy = 'game_name'; // Default sort column
    let currentSortOrder = 'ASC';   // Default sort order

    function displayMessage(container, message, type = 'info') {
        container.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
                                ${message}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                              </div>`;
    }

    function htmlspecialchars(str) {
        if (typeof str !== 'string') return '';
        return str.replace(/&/g, '&amp;')
                  .replace(/</g, '&lt;')
                  .replace(/>/g, '&gt;')
                  .replace(/"/g, '&quot;')
                  .replace(/'/g, '&#039;');
    }
    
    function createSortableHeader(label, columnKey, currentSort, currentOrder) {
        let sortIcon = '';
        if (columnKey === currentSort) {
            sortIcon = currentOrder === 'ASC' ? ' <i class="bx bx-sort-up"></i>' : ' <i class="bx bx-sort-down"></i>';
        }
        return `<th scope="col" class="sortable-header" style="cursor:pointer;" data-sortcol="${columnKey}">${label}${sortIcon}</th>`;
    }

    function fetchAndDisplayGames(sortBy = currentSortBy, sortOrder = currentSortOrder) {
        currentSortBy = sortBy;
        currentSortOrder = sortOrder;

        const selectedTableValue = gameTableSelector.value;
        messageArea.innerHTML = ''; 

        if (!selectedTableValue) {
            displayMessage(messageArea, 'Silakan pilih jenis game sumber terlebih dahulu.', 'warning');
            gameListContainer.innerHTML = '<p class="text-muted text-center">Pilihan jenis game belum ditentukan.</p>';
            gameListTitle.textContent = 'Daftar Game';
            return;
        }

        gameListContainer.innerHTML = '<p class="text-center py-5"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memuat data game...</p>';
        const selectedTableDisplayName = gameTablesMap[selectedTableValue] || selectedTableValue;
        gameListTitle.textContent = `Daftar Game dari: ${selectedTableDisplayName}`;

        const formData = new FormData();
        formData.append('action', 'fetch_games');
        formData.append('table_name', selectedTableValue);
        formData.append('sort_by', sortBy);
        formData.append('sort_order', sortOrder);

        fetch('ajax_egames_handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => { throw new Error('Network response error: ' + response.status + '. ' + text) });
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 'success') {
                if (data.games && data.games.length > 0) {
                    let tableHtml = `<table class="table table-hover table-striped"><thead><tr>`;
                    tableHtml += createSortableHeader('Nama Game', 'game_name', currentSortBy, currentSortOrder);
                    tableHtml += createSortableHeader('Provider', 'provider', currentSortBy, currentSortOrder);
                    tableHtml += createSortableHeader('Game Code', 'game_code', currentSortBy, currentSortOrder);
                    tableHtml += `<th scope="col">Jenis Game (Target)</th>`; // Kolom baru
                    tableHtml += createSortableHeader('Vendor', 'game_vendor', currentSortBy, currentSortOrder);
                    tableHtml += createSortableHeader('Status', 'status', currentSortBy, currentSortOrder);
                    tableHtml += `<th scope="col">Aksi</th></tr></thead><tbody>`;

                    data.games.forEach(game => {
                        const gameDataString = htmlspecialchars(JSON.stringify(game));
                        tableHtml += `<tr>
                            <td>${game.game_name || 'N/A'}</td>
                            <td>${game.provider || 'N/A'}</td>
                            <td>${game.game_code || 'N/A'}</td>
                            <td><span class="badge bg-label-primary">${htmlspecialchars(data.jenis_game_value) || 'N/A'}</span></td>
                            <td>${game.game_vendor || 'N/A'}</td>
                            <td>${game.status == '1' ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Nonaktif</span>'}</td>
                            <td>
                                <button class="btn btn-sm btn-info add-to-egames-btn"
                                        data-game='${gameDataString}'
                                        data-jenis_game_value="${htmlspecialchars(data.jenis_game_value || '')}">
                                    <i class="bx bx-plus"></i> Tambah ke Egames
                                </button>
                            </td>
                        </tr>`;
                    });
                    tableHtml += `</tbody></table>`;
                    gameListContainer.innerHTML = tableHtml;
                    attachTableActionListeners();
                } else {
                    gameListContainer.innerHTML = '<p class="text-center text-muted">Tidak ada game baru yang bisa ditambahkan dari tabel sumber ini (kemungkinan semua sudah ada di Egames).</p>';
                }
            } else {
                displayMessage(messageArea, data.message || 'Gagal memuat data game dari server.', 'danger');
                gameListContainer.innerHTML = '<p class="text-center text-danger">Gagal memuat data. Coba lagi.</p>';
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            displayMessage(messageArea, 'Terjadi kesalahan teknis saat mengambil data: ' + error.message, 'danger');
            gameListContainer.innerHTML = `<p class="text-center text-danger">Error: ${error.message}.</p>`;
        });
    }
    
    if(editableBannerFileUploadInput) {
         editableBannerFileUploadInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!allowedTypes.includes(file.type)) {
                    editableBannerPreviewContainer.innerHTML = '<small class="text-danger">Tipe file tidak didukung.</small>';
                    this.value = ''; 
                    return;
                }
                if (file.size > 5 * 1024 * 1024) { 
                     editableBannerPreviewContainer.innerHTML = '<small class="text-danger">Ukuran file terlalu besar (Max 5MB).</small>';
                    this.value = ''; 
                    return;
                }
                const reader = new FileReader();
                reader.onload = function(e) {
                    editableBannerPreviewContainer.innerHTML = `<img src="${e.target.result}" alt="Banner Preview" style="max-height: 90px; max-width: 100%; object-fit: contain;">`;
                }
                reader.readAsDataURL(file);
            } else {
                editableBannerPreviewContainer.innerHTML = '<small class="text-muted">Preview banner (Max 5MB).</small>';
            }
        });
    }

    function attachTableActionListeners() {
        const addButtons = gameListContainer.querySelectorAll('.add-to-egames-btn');
        addButtons.forEach(button => {
            const newButton = button.cloneNode(true);
            button.parentNode.replaceChild(newButton, button);
            newButton.addEventListener('click', function() {
                try {
                    const gameData = JSON.parse(this.dataset.game);
                    const jenisGameValue = this.dataset.jenis_game_value;
                    
                    document.getElementById('addGameForm').reset(); 
                    modalMessageArea.innerHTML = ''; 
                    editableBannerPreviewContainer.innerHTML = '<small class="text-muted">Preview banner (Max 5MB).</small>';

                    document.getElementById('confirm_game_code').textContent = gameData.game_code || 'N/A';
                    document.getElementById('confirm_game_name').textContent = gameData.game_name || 'N/A';
                    document.getElementById('confirm_provider').textContent = gameData.provider || 'N/A';

                    document.getElementById('modal_original_id').value = gameData.id || '';
                    document.getElementById('modal_game_code').value = gameData.game_code || '';
                    document.getElementById('modal_game_name').value = gameData.game_name || '';
                    document.getElementById('modal_provider').value = gameData.provider || '';
                    document.getElementById('modal_sort').value = gameData.sort || '0';
                    document.getElementById('modal_lang').value = gameData.lang || 'id';
                    document.getElementById('modal_status').value = gameData.status || '0';
                    document.getElementById('modal_frbavailable').value = gameData.frbavailable || '0';
                    document.getElementById('modal_provideragent').value = gameData.provideragent || '';
                    document.getElementById('modal_game_vendor').value = gameData.game_vendor || '';
                    // Set nilai banner asli ke input tersembunyi
                    document.getElementById('modal_original_banner').value = gameData.banner || '';
                    
                    editableJenisGameInput.value = jenisGameValue || '';
                    
                    if (gameData.banner && gameData.banner.trim() !== '' && gameData.banner.toLowerCase() !== 'null') {
                        originalBannerInfoSpan.innerHTML = `<a href="${htmlspecialchars(gameData.banner)}" target="_blank">Lihat Banner Asli</a>`;
                    } else {
                        originalBannerInfoSpan.textContent = 'Tidak ada';
                    }
                    
                    addGameModalInstance.show();
                } catch (e) {
                    console.error("Error parsing game data:", e, this.dataset.game);
                    displayMessage(messageArea, 'Gagal memproses data game. Data tidak valid.', 'danger');
                }
            });
        });

        const sortableHeaders = gameListContainer.querySelectorAll('.sortable-header');
        sortableHeaders.forEach(header => {
            const newHeader = header.cloneNode(true);
            if(header.parentNode) header.parentNode.replaceChild(newHeader, header);

            newHeader.addEventListener('click', function() {
                const columnToSort = this.dataset.sortcol;
                let newSortOrder;
                if (columnToSort === currentSortBy) {
                    newSortOrder = (currentSortOrder === 'ASC') ? 'DESC' : 'ASC';
                } else {
                    newSortOrder = 'ASC';
                }
                fetchAndDisplayGames(columnToSort, newSortOrder);
            });
        });
    }
    
    if (confirmAddGameBtn) {
        confirmAddGameBtn.addEventListener('click', function() {
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menambahkan...';
            modalMessageArea.innerHTML = '';

            const addForm = document.getElementById('addGameForm');
            const formData = new FormData(addForm); 
            formData.append('action', 'add_to_egames');
            
            fetch('ajax_egames_handler.php', {
                method: 'POST',
                body: formData 
            })
            .then(response => {
                 if (!response.ok) { 
                    return response.json().then(errData => { 
                        throw { status: response.status, data: errData };
                    }).catch(() => { 
                        throw { status: response.status, data: { message: 'Error: ' + response.statusText } };
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    displayMessage(modalMessageArea, data.message || 'Game berhasil ditambahkan!', 'success');
                    // Refresh tabel di belakang setelah berhasil menambahkan
                    setTimeout(() => {
                        addGameModalInstance.hide();
                        fetchAndDisplayGames(currentSortBy, currentSortOrder);
                    }, 1500); // Tunggu 1.5 detik agar user bisa baca pesan sukses
                } else {
                    displayMessage(modalMessageArea, data.message || 'Gagal menambahkan game.', 'danger');
                }
            })
            .catch(error => {
                console.error('Add game error:', error);
                const errorMsg = (error.data && error.data.message) ? error.data.message : (error.message || 'Terjadi kesalahan teknis.');
                displayMessage(modalMessageArea, errorMsg , 'danger');
            })
            .finally(() => {
                confirmAddGameBtn.disabled = false;
                confirmAddGameBtn.innerHTML = 'Ya, Tambahkan';
            });
        });
    }
    
    if (addGameModalElement) {
        addGameModalElement.addEventListener('hidden.bs.modal', function () {
            modalMessageArea.innerHTML = '';
            document.getElementById('addGameForm').reset();
            editableBannerPreviewContainer.innerHTML = '<small class="text-muted">Preview banner (Max 5MB).</small>';
            originalBannerInfoSpan.textContent = 'Tidak ada';
        });
    }

    if (showGamesBtn) {
        showGamesBtn.addEventListener('click', () => fetchAndDisplayGames());
    }
});
</script>