<?php
// ajax_get_featured_games_list.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include_once '../koneksi.php';

// Keamanan: Pastikan admin yang login
if (!isset($_SESSION['kode_admin'])) {
    // Bisa juga mengembalikan HTML error atau response kosong
    // tergantung bagaimana Anda ingin menanganinya di frontend
    http_response_code(403); 
    echo '<p class="text-danger">Akses ditolak.</p>';
    exit();
}

$featured_games_by_provider = [];
$query_featured_games = mysqli_query($koneksi, "SELECT id, game_name, game_code, provider FROM gamelist_slot WHERE is_featured = 1 ORDER BY provider ASC, game_name ASC");

if ($query_featured_games) {
    while ($row_fg = mysqli_fetch_assoc($query_featured_games)) {
        $featured_games_by_provider[$row_fg['provider']][] = $row_fg;
    }
} else {
    // Sebaiknya log error ini di server
    error_log("AJAX Get Featured List Error: " . mysqli_error($koneksi));
    echo '<p class="text-danger">Gagal memuat daftar game unggulan.</p>';
    exit();
}

if (!empty($featured_games_by_provider)) {
    foreach ($featured_games_by_provider as $provider_name => $games) {
        echo '<h6 class="mt-3"><strong>Provider: ' . htmlspecialchars($provider_name) . '</strong></h6>';
        echo '<ul class="list-group mb-3">';
        foreach ($games as $fg) {
            echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
            echo '<span>' . htmlspecialchars($fg['game_name']) . ' (' . htmlspecialchars($fg['game_code']) . ')</span>';
            // Tombol Hapus akan menggunakan class untuk event listener AJAX
            echo '<button type="button" class="btn btn-sm btn-outline-danger remove-featured-btn" data-game-id="' . $fg['id'] . '" data-provider-code="' . htmlspecialchars($provider_name) . '">Hapus</button>';
            echo '</li>';
        }
        echo '</ul>';
    }
} else {
    echo '<p>Belum ada game yang dijadikan unggulan.</p>';
}
exit();
?>