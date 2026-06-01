<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Urutan Game</title>
    <style>
        body { font-family: sans-serif; background-color: #f4f4f9; margin: 0; padding: 20px; }
        .container { max-width: 1200px; margin: auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; text-align: center; }
        .filter-container { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px; }
        .filter-container input, .filter-container select, .filter-container button { padding: 10px; border-radius: 5px; border: 1px solid #ddd; }
        .filter-container button { background-color: #007bff; color: white; border: none; cursor: pointer; }
        .filter-container button:hover { background-color: #0056b3; }
        .game-list { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .game-list th, .game-list td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #ddd; }
        .game-list th { background-color: #f2f2f2; color: #333; }
        .game-list tr:hover { background-color: #f1f1f1; }
        .edit-form { display: flex; gap: 10px; align-items: center; }
        .edit-form input[type="number"] { width: 80px; }
        .edit-form button { padding: 8px 12px; background-color: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .edit-form button:hover { background-color: #218838; }
        @media (max-width: 768px) {
            .filter-container { flex-direction: column; align-items: stretch; }
            .game-list, .game-list thead, .game-list tbody, .game-list th, .game-list td, .game-list tr { display: block; }
            .game-list thead tr { position: absolute; top: -9999px; left: -9999px; }
            .game-list tr { border: 1px solid #ddd; margin-bottom: 10px; }
            .game-list td { border: none; position: relative; padding-left: 50%; text-align: right; }
            .game-list td:before { content: attr(data-label); position: absolute; left: 6px; width: 45%; padding-right: 10px; white-space: nowrap; text-align: left; font-weight: bold; }
            .edit-form { justify-content: flex-end; }
        }
    </style>
</head>
<body>

<?php 
include_once 'koneksi.php';
// Ambil daftar provider unik untuk dropdown filter saat halaman pertama kali dimuat
$providers_query = "SELECT DISTINCT provider_code FROM srg_gamelist ORDER BY provider_code ASC";
$providers_result = mysqli_query($koneksi, $providers_query);
?>

<div class="container">
    <h1>Kelola Urutan Game (AJAX)</h1>
    
    <div class="filter-container">
        <form id="filter-form" style="display: flex; gap: 10px; flex-wrap: wrap;">
            <input type="text" name="search" placeholder="Cari Game..." id="search-input">
            
            <select name="provider" id="provider-select">
                <option value="">Semua Provider</option>
                <?php while ($provider = mysqli_fetch_array($providers_result)): ?>
                    <option value="<?php echo htmlspecialchars($provider['provider_code']); ?>">
                        <?php echo htmlspecialchars($provider['provider_code']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
            
            <select name="urutan_status" id="urutan-status-select">
                <option value="">Semua Game</option>
                <option value="set">Game yang Sudah Di-set</option>
                <option value="unset">Game yang Belum Di-set</option>
            </select>

            <button type="submit">Filter & Cari</button>
            <button type="button" id="reset-button">Reset</button>
        </form>
    </div>

    <div id="game-list-container">
        <table class="game-list">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Game</th>
                    <th>Provider</th>
                    <th>Urutan Saat Ini</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody id="game-list-body">
                <tr><td colspan="5" style="text-align: center;">Memuat data...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const gameListBody = document.getElementById('game-list-body');
        const filterForm = document.getElementById('filter-form');
        const searchInput = document.getElementById('search-input');
        const providerSelect = document.getElementById('provider-select');
        const urutanStatusSelect = document.getElementById('urutan-status-select');
        const resetButton = document.getElementById('reset-button');

        // Fungsi untuk memuat data game dengan AJAX
        function loadGames() {
            const search = searchInput.value;
            const provider = providerSelect.value;
            const urutan_status = urutanStatusSelect.value;
            
            // Bangun URL dengan parameter filter
            const params = new URLSearchParams({
                search: search,
                provider: provider,
                urutan_status: urutan_status
            });

            // Kirim permintaan AJAX
            fetch('fetch_games.php?' + params.toString())
                .then(response => response.text())
                .then(data => {
                    gameListBody.innerHTML = data;
                })
                .catch(error => {
                    gameListBody.innerHTML = '<tr><td colspan="5" style="text-align: center; color: red;">Gagal memuat data.</td></tr>';
                    console.error('Error:', error);
                });
        }

        // Event listener untuk form filter (saat di-submit)
        filterForm.addEventListener('submit', function(event) {
            event.preventDefault(); // Mencegah reload halaman
            loadGames();
        });

        // Event listener untuk tombol reset
        resetButton.addEventListener('click', function() {
            searchInput.value = '';
            providerSelect.value = '';
            urutanStatusSelect.value = '';
            loadGames();
        });

        // Event delegation untuk tombol update
        gameListBody.addEventListener('submit', function(event) {
            if (event.target.classList.contains('edit-form')) {
                event.preventDefault(); // Mencegah reload halaman
                const form = event.target;
                const formData = new FormData(form);

                fetch('update_urutan.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        loadGames(); // Muat ulang data setelah update berhasil
                    } else {
                        alert('Gagal mengupdate: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Terjadi kesalahan pada server.');
                    console.error('Error:', error);
                });
            }
        });

        // Muat data pertama kali saat halaman dibuka
        loadGames();
    });
</script>

</body>
</html>