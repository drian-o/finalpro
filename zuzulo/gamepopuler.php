<?php
// File ini adalah 'gamepopuler.php' atau 'insertdatapopuler.php'
// yang di-include oleh admin/index.php.
// JANGAN tambahkan 'include_once '../koneksi.php';' atau 'session_start();' di sini,
// karena sudah dilakukan oleh admin/index.php.

// Variabel $koneksi, $alamat_admin, dll. sudah tersedia dari admin/index.php

// Pastikan admin sudah login (meskipun index.php sudah mengecek, ini lapisan tambahan)
if (!isset($_SESSION['kode_admin'])) {
    // Seharusnya tidak pernah sampai sini jika index.php bekerja dengan benar
    echo '<script>alert("Sesi tidak ditemukan, harap login kembali."); window.location.replace("'.$alamat_admin.'keluar.php");</script>';
    exit();
}

$message = ''; // Untuk menampilkan pesan sukses/error

// --- Handle Delete Game ---
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id_to_delete = intval($_GET['id']);
    if ($id_to_delete > 0) {
        $stmt = $koneksi->prepare("DELETE FROM gamepopuler WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id_to_delete);
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $message = '<div class="alert alert-success">Game berhasil dihapus!</div>';
                } else {
                    $message = '<div class="alert alert-warning">Game tidak ditemukan atau sudah dihapus sebelumnya.</div>';
                }
            } else {
                $message = '<div class="alert alert-danger">Error saat menghapus game: ' . htmlspecialchars($stmt->error) . '</div>';
            }
            $stmt->close();
        } else {
            $message = '<div class="alert alert-danger">Gagal mempersiapkan statement hapus: ' . htmlspecialchars($koneksi->error) . '</div>';
        }
    } else {
        $message = '<div class="alert alert-danger">ID game tidak valid untuk dihapus.</div>';
    }
    echo '<script>if(history.replaceState){history.replaceState(null, null, window.location.href.split("?")[0]);}</script>';
}


// --- Handle Add Game ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_game'])) {
    $game_name = $_POST['game_name_populer'] ?? '';
    $banner = $_POST['banner_populer'] ?? '';
    $provideragent = $_POST['provideragent_populer'] ?? '';
    $game_code = $_POST['game_code_populer'] ?? '';
    $is_hot = isset($_POST['is_hot_populer']) ? 1 : 0;

    if (!empty($game_name) && !empty($banner) && !empty($game_code)) {
        $stmt = $koneksi->prepare("INSERT INTO gamepopuler (game_name, banner, provideragent, game_code, is_hot) VALUES (?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("ssssi", $game_name, $banner, $provideragent, $game_code, $is_hot);
            if ($stmt->execute()) {
                $message = '<div class="alert alert-success">Game berhasil ditambahkan!</div>';
            } else {
                $message = '<div class="alert alert-danger">Error menambahkan game: ' . htmlspecialchars($stmt->error) . '</div>';
            }
            $stmt->close();
        } else {
             $message = '<div class="alert alert-danger">Gagal mempersiapkan statement tambah: ' . htmlspecialchars($koneksi->error) . '</div>';
        }
    } else {
        $message = '<div class="alert alert-danger">Mohon isi semua field yang wajib (Nama Game, Banner, Kode Game).</div>';
    }
}

// --- Handle Update Game ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_game'])) {
    $id_to_edit = intval($_POST['edit_id_populer']);
    $game_name = $_POST['edit_game_name_populer'] ?? '';
    $banner = $_POST['edit_banner_populer'] ?? '';
    $provideragent = $_POST['edit_provideragent_populer'] ?? '';
    $game_code = $_POST['edit_game_code_populer'] ?? '';
    $is_hot = isset($_POST['edit_is_hot_populer']) ? 1 : 0;

    if (!empty($game_name) && !empty($banner) && !empty($game_code) && $id_to_edit > 0) {
        $stmt = $koneksi->prepare("UPDATE gamepopuler SET game_name = ?, banner = ?, provideragent = ?, game_code = ?, is_hot = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("ssssii", $game_name, $banner, $provideragent, $game_code, $is_hot, $id_to_edit);
            if ($stmt->execute()) {
                $message = '<div class="alert alert-success">Game berhasil diperbarui!</div>';
            } else {
                $message = '<div class="alert alert-danger">Error memperbarui game: ' . htmlspecialchars($stmt->error) . '</div>';
            }
            $stmt->close();
        } else {
            $message = '<div class="alert alert-danger">Gagal mempersiapkan statement update: ' . htmlspecialchars($koneksi->error) . '</div>';
        }
    } else {
        $message = '<div class="alert alert-danger">Mohon isi semua field yang wajib untuk diedit.</div>';
    }
}

// Fetch unique providers from nexus_gamelist
$providers_list = [];
$query_providers = "SELECT DISTINCT provider_code FROM nexus_gamelist WHERE provider_code IS NOT NULL AND provider_code != '' ORDER BY provider_code ASC";
$result_providers = mysqli_query($koneksi, $query_providers);
if ($result_providers) {
    while ($row = mysqli_fetch_assoc($result_providers)) {
        $providers_list[] = $row['provider_code'];
    }
}

// Fetch ALL games from nexus_gamelist for the Add Game modal dropdown (to be filtered by JS)
$gamelist_baru_all = [];
$query_gamelist_baru_all = "SELECT game_code, game_name, game_image_local AS banner, provider_code AS provideragent, provider_code AS provider FROM nexus_gamelist ORDER BY provider_code ASC, game_name ASC";
$result_gamelist_baru_all = mysqli_query($koneksi, $query_gamelist_baru_all);
if ($result_gamelist_baru_all) {
    while ($row = mysqli_fetch_assoc($result_gamelist_baru_all)) {
        $gamelist_baru_all[] = [
            'game_code' => $row['game_code'],
            'game_name' => $row['game_name'],
            'banner' => $row['banner'],
            'provideragent' => $row['provideragent'],
            'provider' => $row['provider']
        ];
    }
}

// Fetch games from gamepopuler to display
$popular_games = [];
$query_popular_games = "SELECT id, game_name, banner, provideragent, game_code, is_hot FROM gamepopuler ORDER BY id DESC";
$result_popular_games = mysqli_query($koneksi, $query_popular_games);
if ($result_popular_games) {
    while ($row = mysqli_fetch_assoc($result_popular_games)) {
        $popular_games[] = $row;
    }
} else {
    $message .= '<div class="alert alert-warning">Tidak dapat mengambil daftar game populer: '.mysqli_error($koneksi).'</div>';
}
?>
<div class="container-xxl flex-grow-1 container-p-y">
  <h4 class="py-3 mb-4">
    <span class="text-muted fw-light">Menu Utama /</span> Kelola Game Populer
  </h4>

  <?php if (!empty($message)) echo $message; ?>

  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addGameModal">
            <i class="bx bx-plus me-1"></i> Add Game Populer
          </button>
        </div>
        <div class="card-body">
          <div class="table-responsive text-nowrap">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Banner</th>
                  <th>Nama Game</th>
                  <th>Provider Agent</th>
                  <th>Kode Game</th>
                  <th>Hot</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody class="table-border-bottom-0">
                <?php if (empty($popular_games)): ?>
                  <tr>
                    <td colspan="7" class="text-center">Belum ada game populer.</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($popular_games as $game): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($game['id']); ?></td>
                      <td>
                        <img src="<?php echo htmlspecialchars($game['banner']); ?>" alt="<?php echo htmlspecialchars($game['game_name']); ?>" width="100" onerror="this.style.display='none'; this.onerror=null; const p = document.createElement('span'); p.innerText='No Img'; this.parentNode.appendChild(p);"/>
                      </td>
                      <td><strong><?php echo htmlspecialchars($game['game_name']); ?></strong></td>
                      <td><?php echo htmlspecialchars($game['provideragent'] ?? '-'); ?></td>
                      <td><?php echo htmlspecialchars($game['game_code']); ?></td>
                      <td>
                        <span class="badge bg-label-<?php echo $game['is_hot'] ? 'danger' : 'secondary'; ?>">
                          <?php echo $game['is_hot'] ? 'HOT' : 'NO'; ?>
                        </span>
                      </td>
                      <td>
                        <button type="button" class="btn btn-icon btn-sm btn-outline-primary"
                                data-bs-toggle="modal" data-bs-target="#editGameModal"
                                data-id="<?php echo $game['id']; ?>"
                                data-name="<?php echo htmlspecialchars($game['game_name']); ?>"
                                data-banner="<?php echo htmlspecialchars($game['banner']); ?>"
                                data-provideragent="<?php echo htmlspecialchars($game['provideragent'] ?? ''); ?>"
                                data-code="<?php echo htmlspecialchars($game['game_code']); ?>"
                                data-hot="<?php echo $game['is_hot']; ?>">
                          <i class="bx bx-edit-alt"></i>
                        </button>
                        <a href="gamepopuler/delete/<?php echo $game['id']; ?>" class="btn btn-icon btn-sm btn-outline-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus game ini?');">
                          <i class="bx bx-trash"></i>
                        </a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="addGameModal" tabindex="-1" aria-labelledby="addGameModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" action="<?php echo $alamat_admin; ?>gamepopuler"> <div class="modal-header">
          <h5 class="modal-title" id="addGameModalLabel">Tambah Game Populer Baru</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="select_provider" class="form-label">Pilih Provider <span class="text-danger">*</span></label>
            <select class="form-select" id="select_provider">
              <option value="">-- Pilih Provider --</option>
              <?php foreach ($providers_list as $provider_name): ?>
                <option value="<?php echo htmlspecialchars($provider_name); ?>">
                  <?php echo htmlspecialchars($provider_name); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label for="select_game_from_list" class="form-label">Pilih Game untuk Isi Otomatis (Opsional)</label>
            <select class="form-select" id="select_game_from_list" disabled>
              <option value="">-- Pilih Game --</option>
            </select>
          </div>
          <hr/>
          <div class="mb-3">
            <label for="game_name_populer" class="form-label">Nama Game <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="game_name_populer" name="game_name_populer" required>
          </div>
          <div class="mb-3">
            <label for="banner_populer" class="form-label">URL Banner <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="banner_populer" name="banner_populer" required>
          </div>
          <div class="mb-3">
            <label for="provideragent_populer" class="form-label">Provider Agent</label>
            <input type="text" class="form-control" id="provideragent_populer" name="provideragent_populer">
          </div>
          <div class="mb-3">
            <label for="game_code_populer" class="form-label">Kode Game <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="game_code_populer" name="game_code_populer" required>
          </div>
          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="is_hot_populer" name="is_hot_populer" value="1" checked>
            <label class="form-check-label" for="is_hot_populer">
              Tandai sebagai HOT
            </label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
          <button type="submit" name="add_game" class="btn btn-primary">Tambah Game</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="editGameModal" tabindex="-1" aria-labelledby="editGameModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" action="<?php echo $alamat_admin; ?>gamepopuler"> <input type="hidden" name="edit_id_populer" id="edit_id_populer">
        <div class="modal-header">
          <h5 class="modal-title" id="editGameModalLabel">Edit Game Populer</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="edit_game_name_populer" class="form-label">Nama Game <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="edit_game_name_populer" name="edit_game_name_populer" required>
          </div>
          <div class="mb-3">
            <label for="edit_banner_populer" class="form-label">URL Banner <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="edit_banner_populer" name="edit_banner_populer" required>
          </div>
          <div class="mb-3">
            <label for="edit_provideragent_populer" class="form-label">Provider Agent</label>
            <input type="text" class="form-control" id="edit_provideragent_populer" name="edit_provideragent_populer">
          </div>
          <div class="mb-3">
            <label for="edit_game_code_populer" class="form-label">Kode Game <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="edit_game_code_populer" name="edit_game_code_populer" required>
          </div>
          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="edit_is_hot_populer" name="edit_is_hot_populer" value="1">
            <label class="form-check-label" for="edit_is_hot_populer">
              Tandai sebagai HOT
            </label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
          <button type="submit" name="edit_game" class="btn btn-primary">Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Pastikan variabel ini didefinisikan dengan benar jika belum ada di skrip utama admin/index.php
// const alamat_admin_js = "<?php echo $alamat_admin; ?>"; // jika diperlukan di JS ini

// Make all games from srg_gamelist available to JavaScript
// IMPORTANT: PHP's json_encode expects an array of objects for easier JS handling
const allGamesFromGamelist = <?php echo json_encode($gamelist_baru_all, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?>;

document.addEventListener('DOMContentLoaded', function () {
    const selectProvider = document.getElementById('select_provider');
    const selectGameFromList = document.getElementById('select_game_from_list');
    const gameNameInput = document.getElementById('game_name_populer');
    const bannerInput = document.getElementById('banner_populer');
    const providerAgentInput = document.getElementById('provideragent_populer');
    const gameCodeInput = document.getElementById('game_code_populer');
    const isHotInput = document.getElementById('is_hot_populer');

    function clearAddFormFields() {
        gameNameInput.value = '';
        bannerInput.value = '';
        providerAgentInput.value = '';
        gameCodeInput.value = '';
        if(isHotInput) {
             isHotInput.checked = true; // Default to checked
        }
    }

    if (selectProvider && selectGameFromList) {
        selectProvider.addEventListener('change', function () {
            const selectedProviderName = this.value;
            selectGameFromList.innerHTML = '<option value="">-- Pilih Game --</option>';
            clearAddFormFields();

            if (selectedProviderName) {
                selectGameFromList.disabled = false;
                const gamesOfProvider = allGamesFromGamelist.filter(function (game) {
                    return game.provider === selectedProviderName; // Filter by 'provider' key
                });

                if (gamesOfProvider.length > 0) {
                    gamesOfProvider.forEach(function (game) {
                        const option = document.createElement('option');
                        // Ensure that game.provider, game.game_name, game.banner (game_image_local), game.game_code are correctly accessed
                        option.value = JSON.stringify({
                            game_name: game.game_name,
                            banner: game.banner, // Maps to game_image_local
                            provideragent: game.provideragent, // Maps to provider_code
                            game_code: game.game_code
                        });
                        option.textContent = `${game.game_name} (${game.game_code})`;
                        selectGameFromList.appendChild(option);
                    });
                } else {
                    selectGameFromList.innerHTML = '<option value="">-- Tidak ada game untuk provider ini --</option>';
                }
            } else {
                selectGameFromList.disabled = true;
            }
        });

        selectGameFromList.addEventListener('change', function () {
            if (this.value) {
                try {
                    const gameData = JSON.parse(this.value);
                    gameNameInput.value = gameData.game_name || '';
                    bannerInput.value = gameData.banner || ''; // From game_image_local
                    providerAgentInput.value = gameData.provideragent || ''; // From provider_code
                    gameCodeInput.value = gameData.game_code || '';
                } catch (e) {
                    console.error("Error parsing game data: ", e);
                    clearAddFormFields();
                }
            } else {
                clearAddFormFields();
            }
        });
    }

    const editGameModal = document.getElementById('editGameModal');
    if (editGameModal) {
        editGameModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const name = button.getAttribute('data-name');
            const banner = button.getAttribute('data-banner');
            const provideragent = button.getAttribute('data-provideragent');
            const code = button.getAttribute('data-code');
            const hot = button.getAttribute('data-hot');

            const modalTitle = editGameModal.querySelector('.modal-title');
            const idInput = editGameModal.querySelector('#edit_id_populer');
            const nameInput = editGameModal.querySelector('#edit_game_name_populer');
            const bannerInput = editGameModal.querySelector('#edit_banner_populer');
            const provideragentInput = editGameModal.querySelector('#edit_provideragent_populer');
            const codeInput = editGameModal.querySelector('#edit_game_code_populer');
            const hotInput = editGameModal.querySelector('#edit_is_hot_populer');

            modalTitle.textContent = 'Edit Game: ' + name;
            idInput.value = id;
            nameInput.value = name;
            bannerInput.value = banner;
            provideragentInput.value = provideragent;
            codeInput.value = code;
            hotInput.checked = (hot == '1'); // '1' sebagai string karena dari atribut data
        });
    }
});
</script>
}