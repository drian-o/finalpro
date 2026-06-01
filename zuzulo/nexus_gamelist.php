<?php
// nexus_gamelist.php
session_start();
include_once '../koneksi.php';

if (!isset($_SESSION['kode_admin'])) {
    echo '
      <script>
        alert("Terjadi kesalahan, harap masuk kembali!");
        window.location.replace("'.$alamat_admin.'keluar.php");
      </script>
    ';
    exit();
}

// Ambil daftar provider dari database untuk dropdown
$providers = [];
$query_providers = mysqli_query($koneksi, "SELECT provider_code, provider_name FROM nexus_provider ORDER BY provider_name ASC");
if ($query_providers) {
    while ($p = mysqli_fetch_assoc($query_providers)) {
        $providers[] = $p;
    }
}
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">Manajemen Game /</span> Nexus Game List
    </h4>

    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="provider-select" class="form-label">Pilih Provider:</label>
                    <select id="provider-select" class="form-select">
                        <option value="">-- Pilih Provider --</option>
                        <?php foreach ($providers as $provider): ?>
                            <option value="<?php echo htmlspecialchars($provider['provider_code']); ?>">
                                <?php echo htmlspecialchars($provider['provider_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 d-flex align-items-end gap-2">
                    <button id="show-games-btn" class="btn btn-primary w-100" type="button">Tampilkan Game</button>
                    <button id="update-games-btn" class="btn btn-success w-100" type="button">Perbarui Game List</button>
                </div>
            </div>
        </div>
    </div>
    
    <div id="game-list-container" class="card" style="display: none;">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0" id="game-list-title">Game List</h5>
            <div id="loading-indicator" class="spinner-border spinner-border-sm" role="status" style="display: none;">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
        <div class="card-body">
            <div id="game-table-container" class="table-responsive">
                <table id="games-table" class="table table-bordered table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <th>ID Game</th>
                            <th>Nama Game</th>
                            <th>Gambar</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="games-table-body">
                        </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="modal fade" id="updateLogModal" tabindex="-1" aria-labelledby="updateLogModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateLogModalLabel">Proses Pembaruan Game</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="background-color: #333; color: #fff; max-height: 70vh; overflow-y: scroll; font-family: monospace;">
                    <pre id="update-log-output"></pre>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editGameModal" tabindex="-1" aria-labelledby="editGameModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editGameModalLabel">Edit Game Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editGameForm">
                        <input type="hidden" id="edit-provider-code">
                        <input type="hidden" id="edit-game-code">
                        <div class="mb-3">
                            <label for="edit-game-name" class="form-label">Nama Game</label>
                            <input type="text" class="form-control" id="edit-game-name" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="edit-game-status" class="form-label">Status</label>
                            <select id="edit-game-status" class="form-select">
                                <option value="open">Open</option>
                                <option value="maintenance">Maintenance</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary" id="save-game-changes">Simpan Perubahan</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const providerSelect = document.getElementById('provider-select');
    const showGamesBtn = document.getElementById('show-games-btn');
    const updateGamesBtn = document.getElementById('update-games-btn');
    const gameListContainer = document.getElementById('game-list-container');
    const gameListTitle = document.getElementById('game-list-title');
    const gamesTableBody = document.getElementById('games-table-body');
    const loadingIndicator = document.getElementById('loading-indicator');
    const editGameModal = new bootstrap.Modal(document.getElementById('editGameModal'));
    const updateLogModal = new bootstrap.Modal(document.getElementById('updateLogModal'));
    const updateLogOutput = document.getElementById('update-log-output');

    let dataTable = null;

    function showGames(providerCode) {
        if (!providerCode) {
            alert('Mohon pilih provider terlebih dahulu.');
            return;
        }

        loadingIndicator.style.display = 'block';
        gameListContainer.style.display = 'none';

        fetch('ajax/ajax_get_games.php?provider_code=' + encodeURIComponent(providerCode))
            .then(response => response.json())
            .then(data => {
                if (dataTable) {
                    dataTable.destroy();
                    gamesTableBody.innerHTML = '';
                }

                if (data.success) {
                    gameListTitle.textContent = `Game List: ${data.providerName}`;
                    data.games.forEach(game => {
                        const row = `
                            <tr>
                                <td>${htmlspecialchars(game.game_code)}</td>
                                <td>${htmlspecialchars(game.game_name)}</td>
                                <td><img src="${htmlspecialchars(game.game_image)}" alt="${htmlspecialchars(game.game_name)}" width="50"></td>
                                <td>${htmlspecialchars(game.game_status)}</td>
                                <td>
                                    <button class="btn btn-sm btn-primary edit-game-btn" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editGameModal"
                                            data-provider-code="${htmlspecialchars(providerCode)}"
                                            data-game-code="${htmlspecialchars(game.game_code)}"
                                            data-game-name="${htmlspecialchars(game.game_name)}"
                                            data-game-status="${htmlspecialchars(game.game_status)}">Edit</button>
                                </td>
                            </tr>
                        `;
                        gamesTableBody.insertAdjacentHTML('beforeend', row);
                    });
                    gameListContainer.style.display = 'block';
                    dataTable = $('#games-table').DataTable();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat memuat data game.');
            })
            .finally(() => {
                loadingIndicator.style.display = 'none';
            });
    }

    function updateGames(providerCode) {
        if (!providerCode) {
            alert('Mohon pilih provider terlebih dahulu.');
            return;
        }
        
        updateLogOutput.textContent = 'Memulai proses pembaruan...';
        updateLogModal.show();
        
        fetch('ajax/ajax_update_gamelist.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams('provider_code=' + encodeURIComponent(providerCode))
        })
        .then(response => {
            const reader = response.body.getReader();
            const decoder = new TextDecoder();
            let result = '';

            function readStream() {
                reader.read().then(({ done, value }) => {
                    if (done) {
                        console.log('Stream complete.');
                        updateLogOutput.textContent += "\nProses selesai.";
                        showGames(providerCode);
                        return;
                    }
                    result += decoder.decode(value, {stream: true});
                    updateLogOutput.innerHTML = result;
                    updateLogOutput.scrollTop = updateLogOutput.scrollHeight;
                    readStream();
                }).catch(error => {
                    console.error('Stream reading error:', error);
                    updateLogOutput.textContent += "\nError: Terjadi kesalahan koneksi saat memproses.";
                    showGames(providerCode);
                });
            }
            readStream();
        })
        .catch(error => {
            console.error('Fetch error:', error);
            updateLogOutput.textContent = 'Terjadi kesalahan saat memulai proses pembaruan.';
            showGames(providerCode);
        });
    }

    showGamesBtn.addEventListener('click', function() {
        showGames(providerSelect.value);
    });

    updateGamesBtn.addEventListener('click', function() {
        updateGames(providerSelect.value);
    });
    
    gamesTableBody.addEventListener('click', function(e) {
        if (e.target.classList.contains('edit-game-btn')) {
            const btn = e.target;
            document.getElementById('edit-provider-code').value = btn.dataset.providerCode;
            document.getElementById('edit-game-code').value = btn.dataset.gameCode;
            document.getElementById('edit-game-name').value = btn.dataset.gameName;
            document.getElementById('edit-game-status').value = btn.dataset.gameStatus;
        }
    });

    document.getElementById('save-game-changes').addEventListener('click', function() {
        const providerCode = document.getElementById('edit-provider-code').value;
        const gameCode = document.getElementById('edit-game-code').value;
        const newStatus = document.getElementById('edit-game-status').value;

        const formData = new FormData();
        formData.append('provider_code', providerCode);
        formData.append('game_code', gameCode);
        formData.append('game_status', newStatus);

        fetch('ajax/ajax_edit_game.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Status game berhasil diubah.');
                editGameModal.hide();
                showGames(providerCode);
            } else {
                alert('Gagal mengubah status game: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menyimpan perubahan.');
        });
    });
    
    function htmlspecialchars(str) {
        if (typeof str !== 'string') return str;
        return str.replace(/&/g, '&amp;')
                  .replace(/</g, '&lt;')
                  .replace(/>/g, '&gt;')
                  .replace(/"/g, '&quot;')
                  .replace(/'/g, '&#039;');
    }
});
</script>