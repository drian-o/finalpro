<?php
// File: admin/gamerecomen.php
// Halaman ini di-include oleh admin/index.php.
// Variabel $koneksi, $alamat_admin, dll., sudah tersedia dari admin/index.php.
// session_start() juga sudah dipanggil oleh admin/index.php.

// Perlindungan tambahan, meskipun index.php sudah melakukan pengecekan sesi.
if (!isset($_SESSION['kode_admin'])) {
    // Seharusnya tidak akan pernah sampai di sini jika index.php bekerja dengan benar.
    echo '<script>alert("Sesi tidak ditemukan, harap login kembali."); window.location.replace("'.$alamat_admin.'keluar.php");</script>';
    exit();
}

$message_gamerecomen = ''; // Variabel pesan khusus untuk halaman ini.

// --- Handle Delete Game Rekomendasi ---
if (isset($_GET['action']) && $_GET['action'] === 'delete_recomen' && isset($_GET['id'])) {
    $id_to_delete = intval($_GET['id']);
    if ($id_to_delete > 0) {
        $stmt = $koneksi->prepare("DELETE FROM gamerekomended WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id_to_delete);
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $message_gamerecomen = '<div class="alert alert-success">Game rekomendasi berhasil dihapus!</div>';
                } else {
                    $message_gamerecomen = '<div class="alert alert-warning">Game rekomendasi tidak ditemukan atau sudah dihapus sebelumnya.</div>';
                }
            } else {
                $message_gamerecomen = '<div class="alert alert-danger">Error saat menghapus game rekomendasi: ' . htmlspecialchars($stmt->error) . '</div>';
            }
            $stmt->close();
        } else {
            $message_gamerecomen = '<div class="alert alert-danger">Gagal mempersiapkan statement hapus: ' . htmlspecialchars($koneksi->error) . '</div>';
        }
    } else {
        $message_gamerecomen = '<div class="alert alert-danger">ID game rekomendasi tidak valid untuk dihapus.</div>';
    }
    echo '<script>if(history.replaceState){history.replaceState(null, null, window.location.href.split("?")[0]);}</script>';
}


// --- Handle Add Game Rekomendasi ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_game_recomen_submit'])) {
    $game_name_form = $_POST['game_name_recomen_input'] ?? '';
    $banner_form = $_POST['banner_recomen_input'] ?? '';
    $provider_name_selected = $_POST['provider_name_recomen_hidden'] ?? ''; 
    $provideragent_selected = $_POST['provideragent_recomen_hidden'] ?? ''; 
    $game_code_form = $_POST['game_code_recomen_input'] ?? '';

    if (!empty($game_name_form) && !empty($banner_form) && !empty($game_code_form) && !empty($provider_name_selected)) {
        $stmt = $koneksi->prepare("INSERT INTO gamerekomended (game_name, banner, provider, provideragent, game_code) VALUES (?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sssss", $game_name_form, $banner_form, $provider_name_selected, $provideragent_selected, $game_code_form);
            if ($stmt->execute()) {
                $message_gamerecomen = '<div class="alert alert-success">Game rekomendasi berhasil ditambahkan!</div>';
            } else {
                $message_gamerecomen = '<div class="alert alert-danger">Error menambahkan game rekomendasi: ' . htmlspecialchars($stmt->error) . '</div>';
            }
            $stmt->close();
        } else {
            $message_gamerecomen = '<div class="alert alert-danger">Gagal mempersiapkan statement tambah: ' . htmlspecialchars($koneksi->error) . '</div>';
        }
    } else {
        $message_gamerecomen = '<div class="alert alert-danger">Mohon isi semua field yang wajib (Nama Game, Banner, Provider, Kode Game).</div>';
    }
}

// --- Handle Update Game Rekomendasi ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_game_recomen_submit'])) {
    $id_to_edit_form = intval($_POST['edit_id_recomen_hidden_input']);
    $game_name_form = $_POST['edit_game_name_recomen_input'] ?? '';
    $banner_form = $_POST['edit_banner_recomen_input'] ?? '';
    $provider_name_selected = $_POST['edit_provider_name_recomen_hidden'] ?? ''; 
    $provideragent_selected = $_POST['edit_provideragent_recomen_hidden'] ?? ''; 
    $game_code_form = $_POST['edit_game_code_recomen_input'] ?? '';

    if (!empty($game_name_form) && !empty($banner_form) && !empty($game_code_form) && !empty($provider_name_selected) && $id_to_edit_form > 0) {
        $stmt = $koneksi->prepare("UPDATE gamerekomended SET game_name = ?, banner = ?, provider = ?, provideragent = ?, game_code = ? WHERE id = ?");
        if($stmt){
            $stmt->bind_param("sssssi", $game_name_form, $banner_form, $provider_name_selected, $provideragent_selected, $game_code_form, $id_to_edit_form);
            if ($stmt->execute()) {
                $message_gamerecomen = '<div class="alert alert-success">Game rekomendasi berhasil diperbarui!</div>';
            } else {
                $message_gamerecomen = '<div class="alert alert-danger">Error memperbarui game rekomendasi: ' . htmlspecialchars($stmt->error) . '</div>';
            }
            $stmt->close();
        } else {
            $message_gamerecomen = '<div class="alert alert-danger">Gagal mempersiapkan statement update: ' . htmlspecialchars($koneksi->error) . '</div>';
        }
    } else {
        $message_gamerecomen = '<div class="alert alert-danger">Mohon isi semua field yang wajib untuk diedit.</div>';
    }
}

// Fetch unique providers from nexus_gamelist
$form_providers_list = [];
$query_form_providers = "SELECT DISTINCT provider_code FROM nexus_gamelist WHERE provider_code IS NOT NULL AND provider_code != '' ORDER BY provider_code ASC";
$result_form_providers = mysqli_query($koneksi, $query_form_providers);
if ($result_form_providers) {
    while ($row_fp = mysqli_fetch_assoc($result_form_providers)) {
        $form_providers_list[] = $row_fp['provider_code'];
    }
}

// Fetch ALL games from nexus_gamelist for the Add Game modal dropdown (to be filtered by JS)
$form_gamelist_all = [];
$query_form_gamelist = "SELECT game_code, game_name, game_image_local AS banner, provider_code AS provider, provider_code AS provideragent FROM nexus_gamelist ORDER BY provider_code ASC, game_name ASC";
$result_form_gamelist = mysqli_query($koneksi, $query_form_gamelist);
if ($result_form_gamelist) {
    while ($row_fg = mysqli_fetch_assoc($result_form_gamelist)) {
        $form_gamelist_all[] = $row_fg;
    }
}

// Fetch games from gamerekomended to display
$display_rekomended_games = [];
$query_display_rekomended = "SELECT id, game_name, banner, provider, provideragent, game_code FROM gamerekomended ORDER BY id DESC";
$result_display_rekomended = mysqli_query($koneksi, $query_display_rekomended);
if ($result_display_rekomended) {
    while ($row_dr = mysqli_fetch_assoc($result_display_rekomended)) {
        $display_rekomended_games[] = $row_dr;
    }
} else {
    $message_gamerecomen .= '<div class="alert alert-warning">Tidak dapat mengambil daftar game rekomendasi dari database: '.mysqli_error($koneksi).'</div>';
}
?>
<div class="container-xxl flex-grow-1 container-p-y">
  <h4 class="py-3 mb-4">
    <span class="text-muted fw-light">Menu Utama /</span> Kelola Game Rekomendasi
  </h4>

  <?php if (!empty($message_gamerecomen)) echo $message_gamerecomen; ?>

  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addGameRecomenModal">
            <i class="bx bx-plus me-1"></i> Tambah Game Rekomendasi
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
                  <th>Provider</th> <th>Kode Game</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody class="table-border-bottom-0">
                <?php if (empty($display_rekomended_games)): ?>
                  <tr>
                    <td colspan="6" class="text-center">Belum ada game rekomendasi yang ditambahkan.</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($display_rekomended_games as $game_item): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($game_item['id']); ?></td>
                      <td>
                        <img src="<?php echo htmlspecialchars($game_item['banner']); ?>" alt="<?php echo htmlspecialchars($game_item['game_name']); ?>" width="100" onerror="this.style.display='none'; this.onerror=null; const p = document.createElement('span'); p.innerText='No Img'; this.parentNode.appendChild(p);"/>
                      </td>
                      <td><strong><?php echo htmlspecialchars($game_item['game_name']); ?></strong></td>
                      <td><?php echo htmlspecialchars($game_item['provider'] ?? '-'); ?></td> <td><?php echo htmlspecialchars($game_item['game_code']); ?></td>
                      <td>
                        <button type="button" class="btn btn-icon btn-sm btn-outline-primary"
                                data-bs-toggle="modal" data-bs-target="#editGameRecomenModal"
                                data-id="<?php echo $game_item['id']; ?>"
                                data-name="<?php echo htmlspecialchars($game_item['game_name']); ?>"
                                data-banner="<?php echo htmlspecialchars($game_item['banner']); ?>"
                                data-provider="<?php echo htmlspecialchars($game_item['provider'] ?? ''); ?>"
                                data-provideragent="<?php echo htmlspecialchars($game_item['provideragent'] ?? ''); ?>"
                                data-code="<?php echo htmlspecialchars($game_item['game_code']); ?>">
                          <i class="bx bx-edit-alt"></i>
                        </button>
                        <a href="<?php echo rtrim($alamat_admin, '/'); ?>/gamerecomen/delete/<?php echo $game_item['id']; ?>" class="btn btn-icon btn-sm btn-outline-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus game rekomendasi ini?');">
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

<div class="modal fade" id="addGameRecomenModal" tabindex="-1" aria-labelledby="addGameRecomenModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" action="<?php echo rtrim($alamat_admin, '/'); ?>/gamerecomen">
        <div class="modal-header">
          <h5 class="modal-title" id="addGameRecomenModalLabel">Tambah Game Rekomendasi Baru</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="add_select_provider_recomen" class="form-label">Pilih Provider dari Gamelist <span class="text-danger">*</span></label>
            <select class="form-select" id="add_select_provider_recomen">
              <option value="">-- Pilih Provider --</option>
              <?php foreach ($form_providers_list as $provider_name_val): ?>
                <option value="<?php echo htmlspecialchars($provider_name_val); ?>">
                  <?php echo htmlspecialchars($provider_name_val); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label for="add_select_game_recomen" class="form-label">Pilih Game (Isi Otomatis)</label>
            <select class="form-select" id="add_select_game_recomen" disabled>
              <option value="">-- Pilih Game --</option>
            </select>
          </div>
          <hr/>
          <input type="hidden" id="add_provider_name_recomen_hidden" name="provider_name_recomen_hidden">
          <input type="hidden" id="add_provideragent_recomen_hidden" name="provideragent_recomen_hidden">

          <div class="mb-3">
            <label for="add_game_name_recomen_input" class="form-label">Nama Game <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="add_game_name_recomen_input" name="game_name_recomen_input" required>
          </div>
          <div class="mb-3">
            <label for="add_banner_recomen_input" class="form-label">URL Banner <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="add_banner_recomen_input" name="banner_recomen_input" required>
          </div>
          <div class="mb-3">
            <label for="add_game_code_recomen_input" class="form-label">Kode Game <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="add_game_code_recomen_input" name="game_code_recomen_input" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
          <button type="submit" name="add_game_recomen_submit" class="btn btn-primary">Tambah Game</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="editGameRecomenModal" tabindex="-1" aria-labelledby="editGameRecomenModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" action="<?php echo rtrim($alamat_admin, '/'); ?>/gamerecomen">
        <input type="hidden" name="edit_id_recomen_hidden_input" id="edit_id_recomen_hidden_input">
        <div class="modal-header">
          <h5 class="modal-title" id="editGameRecomenModalLabel">Edit Game Rekomendasi</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="edit_provider_name_recomen_hidden" name="edit_provider_name_recomen_hidden">
          <input type="hidden" id="edit_provideragent_recomen_hidden" name="edit_provideragent_recomen_hidden">

          <div class="mb-3">
            <label class="form-label">Provider (Tidak dapat diubah)</label>
            <input type="text" class="form-control" id="edit_provider_name_recomen_display" readonly>
          </div>
          <div class="mb-3">
            <label for="edit_game_name_recomen_input" class="form-label">Nama Game <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="edit_game_name_recomen_input" name="edit_game_name_recomen_input" required>
          </div>
          <div class="mb-3">
            <label for="edit_banner_recomen_input" class="form-label">URL Banner <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="edit_banner_recomen_input" name="edit_banner_recomen_input" required>
          </div>
          <div class="mb-3">
            <label for="edit_game_code_recomen_input" class="form-label">Kode Game <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="edit_game_code_recomen_input" name="edit_game_code_recomen_input" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
          <button type="submit" name="edit_game_recomen_submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Data semua game dari srg_gamelist, diambil dari PHP
const allGamesDataForRecomenForm = <?php echo json_encode($form_gamelist_all, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?>;

document.addEventListener('DOMContentLoaded', function () {
    const addSelectProvider = document.getElementById('add_select_provider_recomen');
    const addSelectGame = document.getElementById('add_select_game_recomen');

    // Fungsi untuk membersihkan field pada modal tambah
    function clearAddRecomenModalFields() {
        document.getElementById('add_game_name_recomen_input').value = '';
        document.getElementById('add_banner_recomen_input').value = '';
        document.getElementById('add_game_code_recomen_input').value = '';
        document.getElementById('add_provider_name_recomen_hidden').value = ''; // Clear hidden field
        document.getElementById('add_provideragent_recomen_hidden').value = ''; // Clear hidden field
        if (addSelectGame) { // Reset pilihan game juga
            addSelectGame.value = ''; // Reset selected option
        }
    }

    if (addSelectProvider && addSelectGame) {
        addSelectProvider.addEventListener('change', function () {
            const selectedProviderName = this.value;
            addSelectGame.innerHTML = '<option value="">-- Pilih Game --</option>'; // Reset game dropdown
            clearAddRecomenModalFields(); // Bersihkan field input juga

            if (selectedProviderName) {
                addSelectGame.disabled = false;
                const gamesOfThisProvider = allGamesDataForRecomenForm.filter(function (game) {
                    return game.provider === selectedProviderName; // Filter by 'provider' key
                });

                if (gamesOfThisProvider.length > 0) {
                    gamesOfThisProvider.forEach(function (game) {
                        const option = document.createElement('option');
                        const gameDataForOption = {
                            game_name: game.game_name,
                            banner: game.banner, // Maps to game_image_local
                            game_code: game.game_code,
                            provider: game.provider,        // provider_code
                            provideragent: game.provideragent // provider_code
                        };
                        option.value = JSON.stringify(gameDataForOption);
                        option.textContent = `${game.game_name} (${game.game_code})`;
                        addSelectGame.appendChild(option);
                    });
                } else {
                    addSelectGame.innerHTML = '<option value="">-- Tidak ada game untuk provider ini --</option>';
                }
            } else {
                addSelectGame.disabled = true;
            }
        });

        addSelectGame.addEventListener('change', function () {
            if (this.value) { // Jika ada game yang dipilih (bukan option default)
                try {
                    const gameData = JSON.parse(this.value);
                    document.getElementById('add_game_name_recomen_input').value = gameData.game_name || '';
                    document.getElementById('add_banner_recomen_input').value = gameData.banner || ''; // From game_image_local
                    document.getElementById('add_game_code_recomen_input').value = gameData.game_code || '';
                    // Set hidden fields for provider and provideragent
                    document.getElementById('add_provider_name_recomen_hidden').value = gameData.provider || '';
                    document.getElementById('add_provideragent_recomen_hidden').value = gameData.provideragent || '';
                } catch (e) {
                    console.error("Error parsing game data (rekomendasi - add): ", e);
                    clearAddRecomenModalFields(); // Bersihkan jika ada error parsing
                }
            } else {
                clearAddRecomenModalFields(); // Bersihkan jika pilihan kembali ke default "-- Pilih Game --"
            }
        });
    }

    // Untuk Modal Edit Game Rekomendasi
    const editGameRecomenModalEl = document.getElementById('editGameRecomenModal');
    if (editGameRecomenModalEl) {
        editGameRecomenModalEl.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget; // Tombol yang memicu modal

            // Ambil data dari atribut data-* pada tombol
            const gameId = button.getAttribute('data-id');
            const gameName = button.getAttribute('data-name');
            const gameBanner = button.getAttribute('data-banner');
            const gameProvider = button.getAttribute('data-provider'); // from data-provider
            const gameProviderAgent = button.getAttribute('data-provideragent'); // from data-provideragent
            const gameCode = button.getAttribute('data-code');

            // Isi field pada modal edit
            document.getElementById('edit_id_recomen_hidden_input').value = gameId;
            document.getElementById('edit_game_name_recomen_input').value = gameName;
            document.getElementById('edit_banner_recomen_input').value = gameBanner;
            document.getElementById('edit_provider_name_recomen_display').value = gameProvider; // Untuk display readonly
            document.getElementById('edit_game_code_recomen_input').value = gameCode;

            // Isi hidden fields untuk provider dan provideragent yang akan disubmit
            document.getElementById('edit_provider_name_recomen_hidden').value = gameProvider;
            document.getElementById('edit_provideragent_recomen_hidden').value = gameProviderAgent;
        });
    }
});
</script>
}