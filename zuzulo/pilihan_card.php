<?php
// File: admin/pilihan_card.php
// Halaman ini di-include oleh admin/index.php.
// Variabel $koneksi, $alamat_admin, dll., sudah tersedia dari admin/index.php.
// session_start() juga sudah dipanggil oleh admin/index.php.

// Perlindungan tambahan, meskipun index.php sudah melakukan pengecekan sesi.
if (!isset($_SESSION['kode_admin'])) {
    // Seharusnya tidak akan pernah sampai di sini jika index.php bekerja dengan benar.
    echo '<script>alert("Sesi tidak ditemukan, harap login kembali."); window.location.replace("'.$alamat_admin.'keluar.php");</script>';
    exit();
}

$message_card = ''; // Variabel pesan khusus untuk halaman ini.

// --- Handle Delete Game card ---
// Logika ini akan aktif jika URL adalah /admin/pilihan_card/delete/ID
if (isset($_GET['action']) && $_GET['action'] === 'delete_card' && isset($_GET['id'])) {
    $id_to_delete = intval($_GET['id']);
    if ($id_to_delete > 0) {
        $stmt = $koneksi->prepare("DELETE FROM card_gamelist WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id_to_delete);
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $message_card = '<div class="alert alert-success">Game card berhasil dihapus!</div>';
                } else {
                    $message_card = '<div class="alert alert-warning">Game card tidak ditemukan atau sudah dihapus sebelumnya.</div>';
                }
            } else {
                $message_card = '<div class="alert alert-danger">Error saat menghapus game card: ' . htmlspecialchars($stmt->error) . '</div>';
            }
            $stmt->close();
        } else {
            $message_card = '<div class="alert alert-danger">Gagal mempersiapkan statement hapus: ' . htmlspecialchars($koneksi->error) . '</div>';
        }
    } else {
        $message_card = '<div class="alert alert-danger">ID game card tidak valid untuk dihapus.</div>';
    }
    // Membersihkan query string dari URL jika ada
    echo '<script>if(history.replaceState){history.replaceState(null, null, window.location.href.split("?")[0]);}</script>';
}


// --- Handle Add Game card ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_card_game_submit'])) {
    // Perubahan utama: Sekarang kita akan menerima array JSON dari JavaScript
    $selected_games_json = $_POST['selected_games_data_hidden'] ?? '[]';
    $games_to_add = json_decode($selected_games_json, true);

    if (empty($games_to_add) || !is_array($games_to_add)) {
        $message_card = '<div class="alert alert-danger">Tidak ada game yang dipilih atau format data tidak valid.</div>';
    } else {
        $insert_success_count = 0;
        $insert_fail_count = 0;
        $duplicate_count = 0;
        
        foreach ($games_to_add as $game_data) {
            $game_code_form = $game_data['game_code'] ?? '';
            $provider_code_form = $game_data['provider_code'] ?? '';
            $game_source_form = $game_data['game_source'] ?? 'srg'; // Default 'srg'
            $game_type_form = $game_data['game_type'] ?? ''; 
            $game_name_form = $game_data['game_name'] ?? '';
            $display_order_form = intval($game_data['display_order'] ?? 0); // Default 0
            $is_featured_form = isset($game_data['is_featured']) ? (int)$game_data['is_featured'] : 1; // Default 1 (featured)
            $custom_image_path_form = $game_data['custom_image_path'] ?? null; // Bisa null

            if (empty($game_code_form) || empty($provider_code_form) || empty($game_name_form) || empty($game_type_form)) {
                $insert_fail_count++;
                continue; // Lewati game jika data tidak lengkap
            }

            // Cek duplikasi game_code untuk provider yang sama
            $stmt_check = $koneksi->prepare("SELECT COUNT(*) FROM card_gamelist WHERE game_code = ? AND provider_code = ?");
            if ($stmt_check) {
                $stmt_check->bind_param("ss", $game_code_form, $provider_code_form);
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
                error_log("Failed to prepare duplicate check for card game: " . $koneksi->error);
                continue;
            }
            
            $stmt = $koneksi->prepare("INSERT INTO card_gamelist (game_code, provider_code, game_source, game_type, game_name, display_order, is_featured, custom_image_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("sssssiis", $game_code_form, $provider_code_form, $game_source_form, $game_type_form, $game_name_form, $display_order_form, $is_featured_form, $custom_image_path_form);
                if ($stmt->execute()) {
                    $insert_success_count++;
                } else {
                    $insert_fail_count++;
                    error_log("Error adding card game: " . htmlspecialchars($stmt->error));
                }
                $stmt->close();
            } else {
                $insert_fail_count++;
                error_log("Failed to prepare add statement for card game: " . htmlspecialchars($koneksi->error));
            }
        }
        
        if ($insert_success_count > 0) {
            $message_card .= '<div class="alert alert-success">' . $insert_success_count . ' game card berhasil ditambahkan.';
            if ($duplicate_count > 0) {
                $message_card .= ' (' . $duplicate_count . ' duplikat dilewati)';
            }
            if ($insert_fail_count > 0) {
                $message_card .= ' (' . $insert_fail_count . ' gagal ditambahkan)';
            }
            $message_card .= '</div>';
        } else if ($duplicate_count > 0) {
             $message_card .= '<div class="alert alert-warning">Semua game yang dipilih sudah ada di daftar. (' . $duplicate_count . ' duplikat dilewati)</div>';
        }
        else {
            $message_card .= '<div class="alert alert-danger">Tidak ada game yang berhasil ditambahkan. (' . $insert_fail_count . ' gagal)</div>';
        }
    }
}

// --- Handle Update Game card ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_card_game_submit'])) {
    $id_to_edit_form = intval($_POST['edit_id_card_hidden_input']);
    $game_code_form = $_POST['edit_game_code_card_input'] ?? '';
    $provider_code_form = $_POST['edit_provider_code_card_hidden'] ?? ''; // from hidden field
    $game_source_form = $_POST['edit_game_source_card_hidden'] ?? ''; // from hidden field
    $game_type_form = $_POST['edit_game_type_card_hidden'] ?? ''; // from hidden field
    $game_name_form = $_POST['edit_game_name_card_input'] ?? '';
    $display_order_form = intval($_POST['edit_display_order_card_input'] ?? 0);
    $is_featured_form = isset($_POST['edit_is_featured_card_input']) ? 1 : 0;
    $custom_image_path_form = $_POST['edit_custom_image_path_card_input'] ?? null;

    if (!empty($game_code_form) && !empty($provider_code_form) && !empty($game_name_form) && !empty($game_type_form) && $id_to_edit_form > 0) {
        $stmt = $koneksi->prepare("UPDATE card_gamelist SET game_code = ?, provider_code = ?, game_source = ?, game_type = ?, game_name = ?, display_order = ?, is_featured = ?, custom_image_path = ? WHERE id = ?");
        if($stmt){
            $stmt->bind_param("sssssiisi", $game_code_form, $provider_code_form, $game_source_form, $game_type_form, $game_name_form, $display_order_form, $is_featured_form, $custom_image_path_form, $id_to_edit_form);
            if ($stmt->execute()) {
                $message_card = '<div class="alert alert-success">Game card berhasil diperbarui!</div>';
            } else {
                $message_card = '<div class="alert alert-danger">Error memperbarui game card: ' . htmlspecialchars($stmt->error) . '</div>';
            }
            $stmt->close();
        } else {
            $message_card = '<div class="alert alert-danger">Gagal mempersiapkan statement update: ' . htmlspecialchars($koneksi->error) . '</div>';
        }
    } else {
        $message_card = '<div class="alert alert-danger">Mohon isi semua field yang wajib untuk diedit dan pastikan ID valid.</div>';
    }
}

// Fetch unique providers dari srg_gamelist HANYA untuk game_type 'card'
$form_providers_list = [];
$query_form_providers = "SELECT DISTINCT provider_code FROM srg_gamelist WHERE game_type = 'card' AND provider_code IS NOT NULL AND provider_code != '' ORDER BY provider_code ASC"; // Gunakan 'card' (lowercase)
$result_form_providers = mysqli_query($koneksi, $query_form_providers);
if ($result_form_providers) {
    while ($row_fp = mysqli_fetch_assoc($result_form_providers)) {
        $form_providers_list[] = $row_fp['provider_code'];
    }
}

// Fetch SEMUA games dari srg_gamelist HANYA untuk game_type 'card'
$form_gamelist_all = [];
$query_form_gamelist = "SELECT game_code, game_name, provider_code, game_type, game_source, game_image_local AS custom_image_path FROM srg_gamelist WHERE game_type = 'card' ORDER BY provider_code ASC, game_name ASC"; // Gunakan 'card' (lowercase)
$result_form_gamelist = mysqli_query($koneksi, $query_form_gamelist);
if ($result_form_gamelist) {
    while ($row_fg = mysqli_fetch_assoc($result_form_gamelist)) {
        $form_gamelist_all[] = $row_fg;
    }
}

// Fetch games dari tabel card_gamelist untuk ditampilkan di tabel utama
$display_card_games = [];
$query_display_card = "SELECT id, game_code, provider_code, game_source, game_type, game_name, display_order, is_featured, custom_image_path FROM card_gamelist ORDER BY id DESC";
$result_display_card = mysqli_query($koneksi, $query_display_card);
if ($result_display_card) {
    while ($row_dr = mysqli_fetch_assoc($result_display_card)) {
        $display_card_games[] = $row_dr;
    }
} else {
    $message_card .= '<div class="alert alert-warning">Tidak dapat mengambil daftar game card dari database: '.mysqli_error($koneksi).'</div>';
}
?>
<div class="container-xxl flex-grow-1 container-p-y">
  <h4 class="py-3 mb-4">
    <span class="text-muted fw-light">Menu Utama /</span> Kelola Pilihan Game card
  </h4>

  <?php if (!empty($message_card)) echo $message_card; // Tampilkan pesan hanya jika ada ?>

  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addcardGameModal">
            <i class="bx bx-plus me-1"></i> Tambah Game card
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
                <?php if (empty($display_card_games)): ?>
                  <tr>
                    <td colspan="9" class="text-center">Belum ada game card yang ditambahkan.</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($display_card_games as $game_item): ?>
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
                                data-bs-toggle="modal" data-bs-target="#editcardGameModal"
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
                        <a href="<?php echo rtrim($alamat_admin, '/'); ?>/pilihan_card/delete/<?php echo $game_item['id']; ?>" class="btn btn-icon btn-sm btn-outline-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus game card ini?');">
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

<div class="modal fade" id="addcardGameModal" tabindex="-1" aria-labelledby="addcardGameModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" action="<?php echo rtrim($alamat_admin, '/'); ?>/pilihan_card">
        <div class="modal-header">
          <h5 class="modal-title" id="addcardGameModalLabel">Tambah Game card Baru</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="add_select_provider_card" class="form-label">Pilih Provider dari Gamelist (Tipe card) <span class="text-danger">*</span></label>
            <select class="form-select" id="add_select_provider_card">
              <option value="">-- Pilih Provider --</option>
              <?php foreach ($form_providers_list as $provider_code_val): ?>
                <option value="<?php echo htmlspecialchars($provider_code_val); ?>">
                  <?php echo htmlspecialchars($provider_code_val); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label for="add_select_game_card" class="form-label">Pilih Game (Multi-select)</label>
            <select class="form-select" id="add_select_game_card" multiple size="10" disabled>
              </select>
            <small class="form-text text-muted">Tekan Ctrl/Cmd untuk memilih lebih dari satu game.</small>
          </div>
          <hr/>
          <input type="hidden" id="selected_games_data_hidden" name="selected_games_data_hidden">

          <div class="mb-3">
            <label for="add_game_code_card_input" class="form-label">Kode Game (Preview)</label>
            <input type="text" class="form-control" id="add_game_code_card_input" readonly>
          </div>
          <div class="mb-3">
            <label for="add_game_name_card_input" class="form-label">Nama Game (Preview)</label>
            <input type="text" class="form-control" id="add_game_name_card_input" readonly>
          </div>
          
          <div class="mb-3">
            <label for="add_display_order_card_input" class="form-label">Urutan Tampilan</label>
            <input type="number" class="form-control" id="add_display_order_card_input" name="display_order_card_input" value="0">
          </div>
          <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="add_is_featured_card_input" name="is_featured_card_input" value="1" checked>
            <label class="form-check-label" for="add_is_featured_card_input">Tampilkan sebagai Featured</label>
          </div>
          <div class="mb-3">
            <label for="add_custom_image_path_card_input" class="form-label">Path Gambar Kustom (Opsional)</label>
            <input type="text" class="form-control" id="add_custom_image_path_card_input" name="custom_image_path_card_input">
            <small class="form-text text-muted">Akan menggunakan <code>game_image_local</code> dari <code>srg_gamelist</code> jika dikosongkan.</small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
          <button type="submit" name="add_card_game_submit" class="btn btn-primary">Tambah Game</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="editcardGameModal" tabindex="-1" aria-labelledby="editcardGameModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" action="<?php echo rtrim($alamat_admin, '/'); ?>/pilihan_card">
        <input type="hidden" name="edit_id_card_hidden_input" id="edit_id_card_hidden_input">
        <input type="hidden" id="edit_provider_code_card_hidden" name="edit_provider_code_card_hidden">
        <input type="hidden" id="edit_game_source_card_hidden" name="edit_game_source_card_hidden">
        <input type="hidden" id="edit_game_type_card_hidden" name="edit_game_type_card_hidden">

        <div class="modal-header">
          <h5 class="modal-title" id="editcardGameModalLabel">Edit Game card</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Provider (Tidak dapat diubah)</label>
            <input type="text" class="form-control" id="edit_provider_code_card_display" readonly>
          </div>
          <div class="mb-3">
            <label for="edit_game_code_card_input" class="form-label">Kode Game <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="edit_game_code_card_input" name="edit_game_code_card_input" required readonly>
            <small class="form-text text-muted">Kode game tidak dapat diubah setelah ditambahkan.</small>
          </div>
          <div class="mb-3">
            <label for="edit_game_name_card_input" class="form-label">Nama Game <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="edit_game_name_card_input" name="edit_game_name_card_input" required>
          </div>
          <div class="mb-3">
            <label for="edit_display_order_card_input" class="form-label">Urutan Tampilan</label>
            <input type="number" class="form-control" id="edit_display_order_card_input" name="edit_display_order_card_input" value="0">
          </div>
          <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="edit_is_featured_card_input" name="edit_is_featured_card_input" value="1">
            <label class="form-check-label" for="edit_is_featured_card_input">Tampilkan sebagai Featured</label>
          </div>
          <div class="mb-3">
            <label for="edit_custom_image_path_card_input" class="form-label">Path Gambar Kustom (Opsional)</label>
            <input type="text" class="form-control" id="edit_custom_image_path_card_input" name="edit_custom_image_path_card_input">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
          <button type="submit" name="edit_card_game_submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Data semua game card dari srg_gamelist, diambil dari PHP
const allcardGamesData = <?php echo json_encode($form_gamelist_all, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?>;

document.addEventListener('DOMContentLoaded', function () {
    const addSelectProvidercard = document.getElementById('add_select_provider_card');
    const addSelectGamecard = document.getElementById('add_select_game_card');
    const selectedGamesDataHidden = document.getElementById('selected_games_data_hidden'); // Hidden input for multi-select data

    // Fungsi untuk membersihkan field pada modal tambah
    function clearAddcardModalFields() {
        document.getElementById('add_game_code_card_input').value = '';
        document.getElementById('add_game_name_card_input').value = '';
        document.getElementById('add_display_order_card_input').value = '0';
        document.getElementById('add_is_featured_card_input').checked = true;
        document.getElementById('add_custom_image_path_card_input').value = '';
        selectedGamesDataHidden.value = '[]'; // Clear hidden multi-select data
        if (addSelectGamecard) {
            Array.from(addSelectGamecard.options).forEach(option => {
                option.selected = false;
            });
            addSelectGamecard.value = ''; // Just in case
        }
    }

    // Event listener for modal show to clear fields
    const addcardGameModal = document.getElementById('addcardGameModal');
    if (addcardGameModal) {
        addcardGameModal.addEventListener('show.bs.modal', clearAddcardModalFields);
    }

    if (addSelectProvidercard && addSelectGamecard) {
        addSelectProvidercard.addEventListener('change', function () {
            const selectedProviderCode = this.value;
            addSelectGamecard.innerHTML = ''; // Clear all options
            selectedGamesDataHidden.value = '[]'; // Clear hidden data on provider change
            addSelectGamecard.disabled = true; // Disable until provider selected

            if (selectedProviderCode) {
                addSelectGamecard.disabled = false;
                const gamesOfThisProvider = allcardGamesData.filter(function (game) {
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
                        option.textContent = game.game_name; // HANYA TAMPILKAN NAMA GAME
                        addSelectGamecard.appendChild(option);
                    });
                } else {
                    addSelectGamecard.innerHTML = '<option value="">-- Tidak ada game card untuk provider ini --</option>';
                }
            }
            // Clear preview fields as provider changes
            document.getElementById('add_game_code_card_input').value = '';
            document.getElementById('add_game_name_card_input').value = '';
            // add_game_source_card_hidden dan add_game_type_card_hidden tidak perlu di-reset di sini
            // karena akan diisi oleh event listener addSelectGamecard on change.
        });

        // Event listener for multi-select dropdown 'change' event
        addSelectGamecard.addEventListener('change', function () {
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
                // Update preview fields with the first selected game (optional)
                if (selectedGamesData.length > 0) {
                    document.getElementById('add_game_code_card_input').value = selectedGamesData[0].game_code || '';
                    document.getElementById('add_game_name_card_input').value = selectedGamesData[0].game_name || '';
                    // Set hidden fields for the first selected game's source and type
                    // Note: If you want source/type for *all* selected games to be submitted,
                    // you'd need to modify PHP processing to iterate selectedGamesData for each property.
                    // For now, these hidden fields won't be used in PHP's multi-insert loop.
                }
            } else {
                document.getElementById('add_game_code_card_input').value = '';
                document.getElementById('add_game_name_card_input').value = '';
            }
            selectedGamesDataHidden.value = JSON.stringify(selectedGamesData);
        });
    }

    // Untuk Modal Edit Game card
    const editcardGameModalEl = document.getElementById('editcardGameModal');
    if (editcardGameModalEl) {
        editcardGameModalEl.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;

            // Ambil data dari atribut data-* pada tombol
            const gameId = button.getAttribute('data-id');
            const gameCode = button.getAttribute('data-game-code');
            const gameName = button.getAttribute('data-game-name');
            const providerCode = button.getAttribute('data-provider-code');
            const gameSource = button.getAttribute('data-game-source');
            const gameType = button.getAttribute('data-game-type');
            const displayOrder = button.getAttribute('data-display-order');
            const isFeatured = button.getAttribute('data-is-featured');
            const customImagePath = button.getAttribute('data-custom-image-path');

            // Isi field pada modal edit
            document.getElementById('edit_id_card_hidden_input').value = gameId;
            document.getElementById('edit_game_code_card_input').value = gameCode;
            document.getElementById('edit_game_name_card_input').value = gameName;
            document.getElementById('edit_provider_code_card_display').value = providerCode; // Untuk display readonly
            document.getElementById('edit_display_order_card_input').value = displayOrder;
            document.getElementById('edit_is_featured_card_input').checked = (isFeatured === '1'); // Checkbox
            document.getElementById('edit_custom_image_path_card_input').value = customImagePath;

            // Isi hidden fields
            document.getElementById('edit_provider_code_card_hidden').value = providerCode;
            document.getElementById('edit_game_source_card_hidden').value = gameSource;
            document.getElementById('edit_game_type_card_hidden').value = gameType;
        });
    }
});
</script>