<?php
// File: admin/pilihan_slot.php
// Halaman ini di-include oleh admin/index.php.
// Variabel $koneksi, $alamat_admin, dll., sudah tersedia dari admin/index.php.
// session_start() juga sudah dipanggil oleh admin/index.php.

// Perlindungan tambahan, meskipun index.php sudah melakukan pengecekan sesi.
if (!isset($_SESSION['kode_admin'])) {
    // Seharusnya tidak akan pernah sampai di sini jika index.php bekerja dengan benar.
    echo '<script>alert("Sesi tidak ditemukan, harap login kembali."); window.location.replace("'.$alamat_admin.'keluar.php");</script>';
    exit();
}

$message_slot = ''; // Variabel pesan khusus untuk halaman ini.

// --- Handle Delete Game slot ---
if (isset($_GET['action']) && $_GET['action'] === 'delete_slot' && isset($_GET['id'])) {
    $id_to_delete = intval($_GET['id']);
    if ($id_to_delete > 0) {
        $stmt = $koneksi->prepare("DELETE FROM slot_gamelist WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id_to_delete);
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $message_slot = '<div class="alert alert-success">Game slot berhasil dihapus!</div>';
                } else {
                    $message_slot = '<div class="alert alert-warning">Game slot tidak ditemukan atau sudah dihapus sebelumnya.</div>';
                }
            } else {
                $message_slot = '<div class="alert alert-danger">Error saat menghapus game slot: ' . htmlspecialchars($stmt->error) . '</div>';
            }
            $stmt->close();
        } else {
            $message_slot = '<div class="alert alert-danger">Gagal mempersiapkan statement hapus: ' . htmlspecialchars($koneksi->error) . '</div>';
        }
    } else {
        $message_slot = '<div class="alert alert-danger">ID game slot tidak valid untuk dihapus.</div>';
    }
    // Membersihkan query string dari URL jika ada
    echo '<script>if(history.replaceState){history.replaceState(null, null, window.location.href.split("?")[0]);}</script>';
}


// --- Handle Add Game slot ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_slot_game_submit'])) {
    $selected_games_json = $_POST['selected_games_data_hidden'] ?? '[]'; 
    $games_to_add = json_decode($selected_games_json, true);

    if (empty($games_to_add) || !is_array($games_to_add)) {
        $message_slot = '<div class="alert alert-danger">Tidak ada game yang dipilih atau format data tidak valid.</div>';
    } else {
        $insert_success_count = 0;
        $insert_fail_count = 0;
        $duplicate_count = 0;
        
        foreach ($games_to_add as $game_data) {
            $game_code = $game_data['game_code'] ?? '';
            $provider_code = $game_data['provider_code'] ?? '';
            $game_source = $game_data['game_source'] ?? 'nexus'; // Default 'nexus'
            $game_type = $game_data['game_type'] ?? '';
            $game_name = $game_data['game_name'] ?? '';
            $display_order = intval($_POST['display_order_slot_input'] ?? 0); // ambil dari input form, bukan data per game
            $is_featured = isset($_POST['is_featured_slot_input']) ? 1 : 0; // ambil dari input form, bukan data per game
            $custom_image_path = $_POST['custom_image_path_slot_input'] ?? null;

            if (empty($game_code) || empty($provider_code) || empty($game_name) || empty($game_type)) {
                $insert_fail_count++;
                continue; // Lewati game jika data tidak lengkap
            }

            // Cek duplikasi game_code untuk provider yang sama
            $stmt_check = $koneksi->prepare("SELECT COUNT(*) FROM slot_gamelist WHERE game_code = ? AND provider_code = ?");
            if ($stmt_check) {
                $stmt_check->bind_param("ss", $game_code, $provider_code);
                $stmt_check->execute();
                $stmt_check->bind_result($count);
                $stmt_check->fetch();
                $stmt_check->close();

                if ($count > 0) {
                    $duplicate_count++;
                    continue; // Lewati jika duplikat
                }
            } else {
                $insert_fail_count++;
                error_log("Failed to prepare duplicate check for slot game: " . $koneksi->error);
                continue;
            }
            
            $stmt = $koneksi->prepare("INSERT INTO slot_gamelist (game_code, provider_code, game_source, game_type, game_name, display_order, is_featured, custom_image_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("sssssiis", $game_code, $provider_code, $game_source, $game_type, $game_name, $display_order, $is_featured, $custom_image_path);
                if ($stmt->execute()) {
                    $insert_success_count++;
                } else {
                    $insert_fail_count++;
                    error_log("Error adding slot game: " . htmlspecialchars($stmt->error));
                }
                $stmt->close();
            } else {
                $insert_fail_count++;
                error_log("Failed to prepare add statement for slot game: " . htmlspecialchars($koneksi->error));
            }
        }
        
        if ($insert_success_count > 0) {
            $message_slot .= '<div class="alert alert-success">' . $insert_success_count . ' game slot berhasil ditambahkan.';
            if ($duplicate_count > 0) {
                $message_slot .= ' (' . $duplicate_count . ' duplikat dilewati)';
            }
            if ($insert_fail_count > 0) {
                $message_slot .= ' (' . $insert_fail_count . ' gagal ditambahkan)';
            }
            $message_slot .= '</div>';
        } else if ($duplicate_count > 0) {
             $message_slot .= '<div class="alert alert-warning">Semua game yang dipilih sudah ada di daftar. (' . $duplicate_count . ' duplikat dilewati)</div>';
        }
        else {
            $message_slot .= '<div class="alert alert-danger">Tidak ada game yang berhasil ditambahkan. (' . $insert_fail_count . ' gagal)</div>';
        }
    }
}

// --- Handle Update Game slot ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_slot_game_submit'])) {
    $id_to_edit_form = intval($_POST['edit_id_slot_hidden_input']);
    $game_code_form = $_POST['edit_game_code_slot_input'] ?? '';
    $provider_code_form = $_POST['edit_provider_code_slot_hidden'] ?? ''; // from hidden field
    $game_source_form = $_POST['edit_game_source_slot_hidden'] ?? ''; // from hidden field
    $game_type_form = $_POST['edit_game_type_slot_hidden'] ?? ''; // from hidden field
    $game_name_form = $_POST['edit_game_name_slot_input'] ?? '';
    $display_order_form = intval($_POST['edit_display_order_slot_input'] ?? 0);
    $is_featured_form = isset($_POST['edit_is_featured_slot_input']) ? 1 : 0;
    $custom_image_path_form = $_POST['edit_custom_image_path_slot_input'] ?? null;

    if (!empty($game_code_form) && !empty($provider_code_form) && !empty($game_name_form) && !empty($game_type_form) && $id_to_edit_form > 0) {
        $stmt = $koneksi->prepare("UPDATE slot_gamelist SET game_code = ?, provider_code = ?, game_source = ?, game_type = ?, game_name = ?, display_order = ?, is_featured = ?, custom_image_path = ? WHERE id = ?");
        if($stmt){
            $stmt->bind_param("sssssiisi", $game_code_form, $provider_code_form, $game_source_form, $game_type_form, $game_name_form, $display_order_form, $is_featured_form, $custom_image_path_form, $id_to_edit_form);
            if ($stmt->execute()) {
                $message_slot = '<div class="alert alert-success">Game slot berhasil diperbarui!</div>';
            } else {
                $message_slot = '<div class="alert alert-danger">Error memperbarui game slot: ' . htmlspecialchars($stmt->error) . '</div>';
            }
            $stmt->close();
        } else {
            $message_slot = '<div class="alert alert-danger">Gagal mempersiapkan statement update: ' . htmlspecialchars($koneksi->error) . '</div>';
        }
    } else {
        $message_slot = '<div class="alert alert-danger">Mohon isi semua field yang wajib untuk diedit dan pastikan ID valid.</div>';
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

// Fetch ALL games from nexus_gamelist for the Add Game modal dropdown
$form_gamelist_all = [];
$query_form_gamelist = "SELECT game_code, game_name, game_type, game_source, game_image_local AS custom_image_path, provider_code FROM nexus_gamelist ORDER BY provider_code ASC, game_name ASC";
$result_form_gamelist = mysqli_query($koneksi, $query_form_gamelist);
if ($result_form_gamelist) {
    while ($row_fg = mysqli_fetch_assoc($result_form_gamelist)) {
        $form_gamelist_all[] = $row_fg;
    }
}

// Fetch games from tabel slot_gamelist untuk ditampilkan di tabel utama
$display_slot_games = [];
$query_display_slot = "SELECT id, game_code, provider_code, game_source, game_type, game_name, display_order, is_featured, custom_image_path FROM slot_gamelist ORDER BY id DESC";
$result_display_slot = mysqli_query($koneksi, $query_display_slot);
if ($result_display_slot) {
    while ($row_dr = mysqli_fetch_assoc($result_display_slot)) {
        $display_slot_games[] = $row_dr;
    }
} else {
    $message_slot .= '<div class="alert alert-warning">Tidak dapat mengambil daftar game slot dari database: '.mysqli_error($koneksi).'</div>';
}
?>
<div class="container-xxl flex-grow-1 container-p-y">
  <h4 class="py-3 mb-4">
    <span class="text-muted fw-light">Menu Utama /</span> Kelola Pilihan Game slot
  </h4>

  <?php if (!empty($message_slot)) echo $message_slot; ?>

  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addslotGameModal">
            <i class="bx bx-plus me-1"></i> Tambah Game slot
          </button>
        </div>
        <div class="card-body">
          <div class="table-responsive text-nowrap">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Kode Game</th>
                  <th>Nama Game</th>
                  <th>Provider</th>
                  <th>Tipe Game</th>
                  <th>Order Tampilan</th>
                  <th>Featured</th>
                  <th>Gambar Kustom</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody class="table-border-bottom-0">
                <?php if (empty($display_slot_games)): ?>
                  <tr>
                    <td colspan="9" class="text-center">Belum ada game slot yang ditambahkan.</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($display_slot_games as $game_item): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($game_item['id']); ?></td>
                      <td><strong><?php echo htmlspecialchars($game_item['game_code']); ?></strong></td>
                      <td><?php echo htmlspecialchars($game_item['game_name']); ?></td>
                      <td><?php echo htmlspecialchars($game_item['provider_code'] ?? '-'); ?></td>
                      <td><?php echo htmlspecialchars($game_item['game_type'] ?? '-'); ?></td>
                      <td><?php echo htmlspecialchars($game_item['display_order']); ?></td>
                      <td><?php echo $game_item['is_featured'] ? '<span class="badge bg-label-success">Ya</span>' : '<span class="badge bg-label-secondary">Tidak</span>'; ?></td>
                      <td>
                        <?php if (!empty($game_item['custom_image_path'])): ?>
                            <img src="<?php echo htmlspecialchars($game_item['custom_image_path']); ?>" alt="Gambar" width="100" onerror="this.style.display='none'; this.onerror=null; const p = document.createElement('span'); p.innerText='No Img'; this.parentNode.appendChild(p);"/>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                      </td>
                      <td>
                        <button type="button" class="btn btn-icon btn-sm btn-outline-primary"
                                data-bs-toggle="modal" data-bs-target="#editslotGameModal"
                                data-id="<?php echo $game_item['id']; ?>"
                                data-game-code="<?php echo htmlspecialchars($game_item['game_code']); ?>"
                                data-game-name="<?php echo htmlspecialchars($game_item['game_name']); ?>"
                                data-provider-code="<?php echo htmlspecialchars($game_item['provider_code']); ?>"
                                data-game-source="<?php echo htmlspecialchars($game_item['game_source']); ?>"
                                data-game-type="<?php echo htmlspecialchars($game_item['game_type']); ?>"
                                data-display-order="<?php echo htmlspecialchars($game_item['display_order']); ?>"
                                data-is-featured="<?php echo htmlspecialchars($game_item['is_featured']); ?>"
                                data-custom-image-path="<?php echo htmlspecialchars($game_item['custom_image_path'] ?? ''); ?>">
                          <i class="bx bx-edit-alt"></i>
                        </button>
                        <a href="<?php echo rtrim($alamat_admin, '/'); ?>/pilihan_slot/delete/<?php echo $game_item['id']; ?>" class="btn btn-icon btn-sm btn-outline-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus game slot ini?');">
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

<div class="modal fade" id="addslotGameModal" tabindex="-1" aria-labelledby="addslotGameModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" action="<?php echo rtrim($alamat_admin, '/'); ?>/pilihan_slot">
        <div class="modal-header">
          <h5 class="modal-title" id="addslotGameModalLabel">Tambah Game slot Baru</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="add_select_provider_slot" class="form-label">Pilih Provider dari Gamelist (Tipe slot) <span class="text-danger">*</span></label>
            <select class="form-select" id="add_select_provider_slot">
              <option value="">-- Pilih Provider --</option>
              <?php foreach ($form_providers_list as $provider_code_val): ?>
                <option value="<?php echo htmlspecialchars($provider_code_val); ?>">
                  <?php echo htmlspecialchars($provider_code_val); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label for="add_select_game_slot" class="form-label">Pilih Game (Multi-select)</label>
            <select class="form-select" id="add_select_game_slot" multiple size="10" disabled>
              </select>
            <small class="form-text text-muted">Tekan Ctrl/Cmd untuk memilih lebih dari satu game.</small>
          </div>
          <hr/>
          <input type="hidden" id="selected_games_data_hidden" name="selected_games_data_hidden">

          <div class="mb-3">
            <label for="add_game_code_slot_input" class="form-label">Kode Game (Preview)</label>
            <input type="text" class="form-control" id="add_game_code_slot_input" readonly>
          </div>
          <div class="mb-3">
            <label for="add_game_name_slot_input" class="form-label">Nama Game (Preview)</label>
            <input type="text" class="form-control" id="add_game_name_slot_input" readonly>
          </div>
          <div class="mb-3">
            <label for="add_display_order_slot_input" class="form-label">Urutan Tampilan</label>
            <input type="number" class="form-control" id="add_display_order_slot_input" name="display_order_slot_input" value="0">
          </div>
          <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="add_is_featured_slot_input" name="is_featured_slot_input" value="1" checked>
            <label class="form-check-label" for="add_is_featured_slot_input">Tampilkan sebagai Featured</label>
          </div>
          <div class="mb-3">
            <label for="add_custom_image_path_slot_input" class="form-label">Path Gambar Kustom (Opsional)</label>
            <input type="text" class="form-control" id="add_custom_image_path_slot_input" name="custom_image_path_slot_input">
            <small class="form-text text-muted">Akan menggunakan <code>game_image_local</code> dari <code>srg_gamelist</code> jika dikosongkan.</small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
          <button type="submit" name="add_slot_game_submit" class="btn btn-primary">Tambah Game</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="editslotGameModal" tabindex="-1" aria-labelledby="editslotGameModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" action="<?php echo rtrim($alamat_admin, '/'); ?>/pilihan_slot">
        <input type="hidden" name="edit_id_slot_hidden_input" id="edit_id_slot_hidden_input">
        <input type="hidden" id="edit_provider_code_slot_hidden" name="edit_provider_code_slot_hidden">
        <input type="hidden" id="edit_game_source_slot_hidden" name="edit_game_source_slot_hidden">
        <input type="hidden" id="edit_game_type_slot_hidden" name="edit_game_type_slot_hidden">

        <div class="modal-header">
          <h5 class="modal-title" id="editslotGameModalLabel">Edit Game slot</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Provider (Tidak dapat diubah)</label>
            <input type="text" class="form-control" id="edit_provider_code_slot_display" readonly>
          </div>
          <div class="mb-3">
            <label for="edit_game_code_slot_input" class="form-label">Kode Game <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="edit_game_code_slot_input" name="edit_game_code_slot_input" required readonly>
            <small class="form-text text-muted">Kode game tidak dapat diubah setelah ditambahkan.</small>
          </div>
          <div class="mb-3">
            <label for="edit_game_name_slot_input" class="form-label">Nama Game <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="edit_game_name_slot_input" name="edit_game_name_slot_input" required>
          </div>
          <div class="mb-3">
            <label for="edit_display_order_slot_input" class="form-label">Urutan Tampilan</label>
            <input type="number" class="form-control" id="edit_display_order_slot_input" name="edit_display_order_slot_input" value="0">
          </div>
          <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="edit_is_featured_slot_input" name="edit_is_featured_slot_input" value="1">
            <label class="form-check-label" for="edit_is_featured_slot_input">Tampilkan sebagai Featured</label>
          </div>
          <div class="mb-3">
            <label for="edit_custom_image_path_slot_input" class="form-label">Path Gambar Kustom (Opsional)</label>
            <input type="text" class="form-control" id="edit_custom_image_path_slot_input" name="edit_custom_image_path_slot_input">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
          <button type="submit" name="edit_slot_game_submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Data semua game slot dari nexus_gamelist, diambil dari PHP
const allslotGamesData = <?php echo json_encode($form_gamelist_all, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?>;

document.addEventListener('DOMContentLoaded', function () {
    const addSelectProviderslot = document.getElementById('add_select_provider_slot');
    const addSelectGameslot = document.getElementById('add_select_game_slot');
    const selectedGamesDataHidden = document.getElementById('selected_games_data_hidden');

    const addGameCodeSlotInput = document.getElementById('add_game_code_slot_input');
    const addGameNameSlotInput = document.getElementById('add_game_name_slot_input');

    function clearAddslotModalFields() {
        addGameCodeSlotInput.value = '';
        addGameNameSlotInput.value = '';
        document.getElementById('add_display_order_slot_input').value = '0';
        document.getElementById('add_is_featured_slot_input').checked = true;
        document.getElementById('add_custom_image_path_slot_input').value = '';
        selectedGamesDataHidden.value = '[]';
        if (addSelectGameslot) {
            Array.from(addSelectGameslot.options).forEach(option => {
                option.selected = false;
            });
            addSelectGameslot.value = '';
        }
    }

    const addslotGameModal = document.getElementById('addslotGameModal');
    if (addslotGameModal) {
        addslotGameModal.addEventListener('show.bs.modal', clearAddslotModalFields);
    }

    if (addSelectProviderslot && addSelectGameslot) {
        addSelectProviderslot.addEventListener('change', function () {
            const selectedProviderCode = this.value;
            addSelectGameslot.innerHTML = '';
            selectedGamesDataHidden.value = '[]';
            addSelectGameslot.disabled = true;

            if (selectedProviderCode) {
                addSelectGameslot.disabled = false;
                const gamesOfThisProvider = allslotGamesData.filter(function (game) {
                    return game.provider_code === selectedProviderCode;
                });

                if (gamesOfThisProvider.length > 0) {
                    gamesOfThisProvider.forEach(function (game) {
                        const option = document.createElement('option');
                        const gameDataForOption = {
                            game_code: game.game_code,
                            game_name: game.game_name,
                            provider_code: game.provider_code,
                            game_source: game.game_source,
                            game_type: game.game_type,
                            custom_image_path: game.custom_image_path
                        };
                        option.value = JSON.stringify(gameDataForOption);
                        option.textContent = game.game_name;
                        addSelectGameslot.appendChild(option);
                    });
                } else {
                    addSelectGameslot.innerHTML = '<option value="">-- Tidak ada game slot untuk provider ini --</option>';
                }
            }
            addGameCodeSlotInput.value = '';
            addGameNameSlotInput.value = '';
        });

        addSelectGameslot.addEventListener('change', function () {
            const selectedOptions = Array.from(this.selectedOptions);
            const selectedGamesData = [];

            if (selectedOptions.length > 0) {
                selectedOptions.forEach(option => {
                    try {
                        const gameData = JSON.parse(option.value);
                        selectedGamesData.push(gameData);
                    } catch (e) {
                        console.error("Error parsing selected game option value: ", e);
                    }
                });
                if (selectedGamesData.length > 0) {
                    addGameCodeSlotInput.value = selectedGamesData[0].game_code || '';
                    addGameNameSlotInput.value = selectedGamesData[0].game_name || '';
                }
            } else {
                addGameCodeSlotInput.value = '';
                addGameNameSlotInput.value = '';
            }
            selectedGamesDataHidden.value = JSON.stringify(selectedGamesData);
        });
    }

    const editslotGameModalEl = document.getElementById('editslotGameModal');
    if (editslotGameModalEl) {
        editslotGameModalEl.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;

            const gameId = button.getAttribute('data-id');
            const gameCode = button.getAttribute('data-game-code');
            const gameName = button.getAttribute('data-game-name');
            const providerCode = button.getAttribute('data-provider-code');
            const gameSource = button.getAttribute('data-game-source');
            const gameType = button.getAttribute('data-game-type');
            const displayOrder = button.getAttribute('data-display-order');
            const isFeatured = button.getAttribute('data-is-featured');
            const customImagePath = button.getAttribute('data-custom-image-path');

            document.getElementById('edit_id_slot_hidden_input').value = gameId;
            document.getElementById('edit_game_code_slot_input').value = gameCode;
            document.getElementById('edit_game_name_slot_input').value = gameName;
            document.getElementById('edit_provider_code_slot_display').value = providerCode;
            document.getElementById('edit_display_order_slot_input').value = displayOrder;
            document.getElementById('edit_is_featured_slot_input').checked = (isFeatured === '1');
            document.getElementById('edit_custom_image_path_slot_input').value = customImagePath;

            document.getElementById('edit_provider_code_slot_hidden').value = providerCode;
            document.getElementById('edit_game_source_slot_hidden').value = gameSource;
            document.getElementById('edit_game_type_slot_hidden').value = gameType;
        });
    }
});
</script>
}