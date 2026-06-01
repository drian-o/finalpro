<?php
include_once 'koneksi.php';

// Inisialisasi variabel dari permintaan GET
$search_query = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
$provider_filter = isset($_GET['provider']) ? mysqli_real_escape_string($koneksi, $_GET['provider']) : '';
$urutan_filter = isset($_GET['urutan_status']) ? mysqli_real_escape_string($koneksi, $_GET['urutan_status']) : '';

// Bangun query SQL
$sql = "SELECT * FROM srg_gamelist WHERE 1=1";

if (!empty($search_query)) {
    $sql .= " AND (game_name LIKE '%$search_query%' OR game_code LIKE '%$search_query%')";
}
if (!empty($provider_filter)) {
    $sql .= " AND provider_code = '$provider_filter'";
}
if ($urutan_filter == 'set') {
    $sql .= " AND urutan > 0";
} elseif ($urutan_filter == 'unset') {
    $sql .= " AND urutan = 0";
}

$sql .= " ORDER BY urutan ASC, game_name ASC";
$result = mysqli_query($koneksi, $sql);

// Cetak baris tabel sebagai respons
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_array($result)) {
        echo '<tr>';
        echo '<td data-label="ID">' . htmlspecialchars($row['id']) . '</td>';
        echo '<td data-label="Nama Game">' . htmlspecialchars($row['game_name']) . '</td>';
        echo '<td data-label="Provider">' . htmlspecialchars($row['provider_code']) . '</td>';
        echo '<td data-label="Urutan Saat Ini">' . htmlspecialchars($row['urutan']) . '</td>';
        echo '<td data-label="Aksi">';
        echo '<form class="edit-form">';
        echo '<input type="hidden" name="id" value="' . htmlspecialchars($row['id']) . '">';
        echo '<input type="number" name="urutan" value="' . htmlspecialchars($row['urutan']) . '" required>';
        echo '<button type="submit" name="update_urutan">Update</button>';
        echo '</form>';
        echo '</td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="5" style="text-align: center;">Tidak ada data game yang ditemukan.</td></tr>';
}
?>