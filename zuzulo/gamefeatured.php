<?php
// gamefeatured.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once '../koneksi.php';

if (!isset($_SESSION['kode_admin'])) {
    echo '
      <script>
        alert("Sesi tidak ditemukan atau tidak valid. Harap masuk kembali!");
        window.location.replace("'.$alamat_admin.'keluar.php");
      </script>
    ';
    exit();
}

// Mengambil daftar provider unik (tetap sama)
$providers = [];
$query_providers = mysqli_query($koneksi, "SELECT DISTINCT provider FROM gamelist_slot ORDER BY provider ASC");
if ($query_providers) {
    while ($row = mysqli_fetch_assoc($query_providers)) {
        $providers[] = $row['provider'];
    }
} else {
    // Handle error jika perlu, misalnya dengan menampilkan pesan
    error_log("Gagal mengambil daftar provider: " . mysqli_error($koneksi));
}

// Logika POST untuk set featured sudah tidak diperlukan di sini lagi,
// karena akan ditangani oleh ajax_set_featured.php
// $feedback_message yang dari POST juga tidak diperlukan karena feedback via AJAX

?>
<div class="container-xxl flex-grow-1 container-p-y">
  <h4 class="py-3 mb-4">
    <span class="text-muted fw-light">Menu Utama /</span> Kelola Game Unggulan (Featured)
  </h4>

  <div id="ajaxFeedbackMessage" class="mb-3"></div>

  <div class="row">
    <div class="col-md-12 mb-4">
      <div class="card">
        <h5 class="card-header">Jadikan Game Sebagai Unggulan</h5>
        <div class="card-body">
          <div class="row g-3 mb-3">
            <div class="col-md-4">
              <label for="selectProvider" class="form-label">Pilih Provider</label>
              <select id="selectProvider" name="provider_filter_display" class="form-select">
                <option value="">-- Pilih Provider --</option>
                <?php foreach ($providers as $p_code) : ?>
                  <option value="<?php echo htmlspecialchars($p_code); ?>">
                    <?php echo htmlspecialchars($p_code); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="row g-3">
            <div class="col-md-6">
              <label for="selectGameToFeature" class="form-label">Pilih Game (yang belum featured)</label>
              <select id="selectGameToFeature" name="game_id_to_feature" class="form-select" disabled>
                <option value="">-- Pilih Provider Dahulu --</option>
              </select>
            </div>
            <div class="col-md-3 align-self-end">
              <button type="button" id="setFeaturedButton" class="btn btn-primary" disabled>Jadikan Unggulan</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-12">
      <div class="card">
        <h5 class="card-header">Daftar Game Unggulan Saat Ini</h5>
        <div class="card-body" id="featuredGamesListContainer">
          <p>Memuat daftar game unggulan...</p>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const providerSelect = document.getElementById('selectProvider');
    const gameSelect = document.getElementById('selectGameToFeature');
    const setFeaturedButton = document.getElementById('setFeaturedButton');
    const featuredGamesListContainer = document.getElementById('featuredGamesListContainer');
    const ajaxFeedbackMessageDiv = document.getElementById('ajaxFeedbackMessage');

    function displayAjaxFeedback(message, type = 'info') { // type bisa 'success', 'danger', 'warning', 'info'
        ajaxFeedbackMessageDiv.innerHTML = `<div class="alert alert-${type}" role="alert">${message}</div>`;
        setTimeout(() => {
            ajaxFeedbackMessageDiv.innerHTML = ''; // Hapus pesan setelah beberapa detik
        }, 5000);
    }

    function loadGamesForProvider(selectedProvider) {
        // Reset dan disable game select & button
        gameSelect.innerHTML = '<option value="">-- Memuat Game... --</option>';
        gameSelect.disabled = true;
        setFeaturedButton.disabled = true;

        if (selectedProvider) {
            fetch(`ajax_get_games_featured.php?provider=${encodeURIComponent(selectedProvider)}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(games => {
                    gameSelect.innerHTML = '<option value="">-- Pilih Game --</option>';
                    if (Array.isArray(games) && games.length > 0) {
                        games.forEach(game => {
                            const option = document.createElement('option');
                            option.value = game.id;
                            option.textContent = `${game.game_name} (${game.game_code})`;
                            gameSelect.appendChild(option);
                        });
                        gameSelect.disabled = false;
                    } else {
                        gameSelect.innerHTML = '<option value="">-- Tidak ada game (belum featured) --</option>';
                        gameSelect.disabled = true;
                    }
                })
                .catch(error => {
                    console.error('Error fetching games:', error);
                    gameSelect.innerHTML = '<option value="">-- Gagal memuat game --</option>';
                    displayAjaxFeedback('Gagal memuat daftar game untuk provider. Coba lagi.', 'danger');
                });
        } else {
            gameSelect.innerHTML = '<option value="">-- Pilih Provider Dahulu --</option>';
            gameSelect.disabled = true;
            setFeaturedButton.disabled = true;
        }
    }

    function loadFeaturedGamesList() {
        featuredGamesListContainer.innerHTML = '<p>Memuat daftar game unggulan...</p>';
        fetch('ajax_get_featured_games_list.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text(); // Karena kita mengharapkan HTML
            })
            .then(html => {
                featuredGamesListContainer.innerHTML = html;
            })
            .catch(error => {
                console.error('Error fetching featured games list:', error);
                featuredGamesListContainer.innerHTML = '<p class="text-danger">Gagal memuat daftar game unggulan. Coba refresh halaman.</p>';
                displayAjaxFeedback('Gagal memuat daftar game unggulan.', 'danger');
            });
    }

    providerSelect.addEventListener('change', function () {
        loadGamesForProvider(this.value);
    });

    gameSelect.addEventListener('change', function() {
        setFeaturedButton.disabled = !(this.value && this.value !== "");
    });

    setFeaturedButton.addEventListener('click', function() {
        const gameId = gameSelect.value;
        const selectedProviderValue = providerSelect.value; // Simpan provider saat ini

        if (!gameId) {
            displayAjaxFeedback('Silakan pilih game terlebih dahulu.', 'warning');
            return;
        }

        setFeaturedButton.disabled = true; // Disable tombol selama proses
        setFeaturedButton.innerHTML = 'Memproses...';

        const formData = new FormData();
        formData.append('game_id', gameId);

        fetch('ajax_set_featured.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            displayAjaxFeedback(data.message, data.success ? 'success' : 'danger');
            if (data.success) {
                loadFeaturedGamesList(); // Muat ulang daftar game unggulan
                // Muat ulang daftar game untuk provider saat ini, karena game yg baru di-feature seharusnya hilang dari daftar ini
                if (selectedProviderValue) { // Pastikan provider masih terpilih
                     loadGamesForProvider(selectedProviderValue);
                } else { // Jika tidak ada provider terpilih, reset dropdown game
                    gameSelect.innerHTML = '<option value="">-- Pilih Provider Dahulu --</option>';
                    gameSelect.disabled = true;
                }
            }
        })
        .catch(error => {
            console.error('Error setting featured game:', error);
            displayAjaxFeedback('Terjadi kesalahan saat menjadikan game unggulan.', 'danger');
        })
        .finally(() => {
            setFeaturedButton.disabled = false; // Aktifkan kembali tombol setelah selesai
            setFeaturedButton.innerHTML = 'Jadikan Unggulan';
             // Reset pilihan game setelah mencoba set featured, apakah berhasil atau tidak
            if(gameSelect.options.length > 0) gameSelect.value = gameSelect.options[0].value;
            // dan disable tombol set featured lagi karena pilihan game kembali ke default
            if(gameSelect.value === "") setFeaturedButton.disabled = true;

        });
    });

    // Event listener untuk tombol "Hapus dari Unggulan" menggunakan event delegation
    featuredGamesListContainer.addEventListener('click', function(event) {
        if (event.target.classList.contains('remove-featured-btn')) {
            const gameId = event.target.dataset.gameId;
            const gameProviderCode = event.target.dataset.providerCode; // Ambil provider code dari game yang dihapus

            if (!gameId || !confirm('Anda yakin ingin menghapus game ini dari unggulan?')) {
                return;
            }

            event.target.disabled = true; // Disable tombol selama proses
            event.target.innerHTML = 'Menghapus...';

            const formData = new FormData();
            formData.append('game_id', gameId);

            fetch('ajax_remove_featured.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                displayAjaxFeedback(data.message, data.success ? 'success' : 'danger');
                if (data.success) {
                    loadFeaturedGamesList(); // Muat ulang daftar game unggulan
                    // Jika provider yang dipilih saat ini sama dengan provider game yang baru di-unfeature,
                    // muat ulang daftar game untuk provider tersebut agar game yang di-unfeature muncul kembali
                    if (providerSelect.value === gameProviderCode) {
                        loadGamesForProvider(providerSelect.value);
                    }
                } else {
                    // Jika gagal, aktifkan kembali tombol
                     event.target.disabled = false;
                     event.target.innerHTML = 'Hapus';
                }
            })
            .catch(error => {
                console.error('Error removing featured game:', error);
                displayAjaxFeedback('Terjadi kesalahan saat menghapus game dari unggulan.', 'danger');
                event.target.disabled = false; // Aktifkan kembali tombol jika error
                event.target.innerHTML = 'Hapus';
            });
        }
    });

    // Muat daftar game unggulan saat halaman pertama kali dimuat
    loadFeaturedGamesList();
});
</script>