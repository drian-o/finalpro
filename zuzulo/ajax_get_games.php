<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once '../koneksi.php';

$db_connected = false;
$db_connection_var = null;
if (isset($koneksi) && $koneksi instanceof mysqli) {
    $db_connected = true;
    $db_connection_var = $koneksi;
} elseif (isset($koneksi_manual) && $koneksi_manual instanceof mysqli) {
    $db_connected = true;
    $db_connection_var = $koneksi_manual;
}

if (!$db_connected) {
    http_response_code(500);
    echo '<div class="card"><div class="card-body"><div class="alert alert-danger mb-0">Kesalahan Koneksi Database.</div></div></div>';
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $selected_provider_code = $_POST['provider_code'] ?? '';
    $sort_column = $_POST['sort_by'] ?? 'game_name';
    $sort_order = $_POST['sort_order'] ?? 'ASC';

    if (empty($selected_provider_code)) {
        echo '<div class="card"><div class="card-body"><div class="alert alert-warning mb-0">Provider code tidak diterima.</div></div></div>';
        exit();
    }

    $allowed_sort_columns = ['id', 'game_code', 'game_name', 'status'];
    if (!in_array($sort_column, $allowed_sort_columns)) {
        $sort_column = 'game_name';
    }
    if (!in_array(strtoupper($sort_order), ['ASC', 'DESC'])) {
        $sort_order = 'ASC';
    }

    $game_data_from_db = [];
    $sql_select_games_db = "SELECT id, game_code, game_name, banner, status, provider FROM gamelist_slot WHERE provider = ? ORDER BY $sort_column $sort_order";
    $stmt_select_games_db = $db_connection_var->prepare($sql_select_games_db);

    if ($stmt_select_games_db) {
        $stmt_select_games_db->bind_param("s", $selected_provider_code);
        $stmt_select_games_db->execute();
        $result_select_db = $stmt_select_games_db->get_result();
        while ($row = $result_select_db->fetch_assoc()) {
            $game_data_from_db[] = $row;
        }
        $stmt_select_games_db->close();

        if (empty($game_data_from_db)) {
            echo '<div class="card"><div class="card-body"><p class="text-info text-center">Tidak ada game ditemukan untuk provider: ' . htmlspecialchars($selected_provider_code) . '</p></div></div>';
        } else {
            $html_table = '<div class="card"><div class="card-header"><h5 class="mb-0">Daftar Game (Provider: ' . htmlspecialchars($selected_provider_code) . ')</h5></div><div class="card-body"><div class="table-responsive">';
            $html_table .= '<table class="table table-bordered table-striped table-hover">';
            $html_table .= '<thead><tr>';
            $headers = [
                'id' => 'ID Game',
                'game_code' => 'Kode Game',
                'game_name' => 'Nama Game',
                'status' => 'Status'
            ];
             foreach ($headers as $col_key => $col_val) {
                $sort_class = ($sort_column == $col_key) ? ($sort_order == 'ASC' ? 'sort-asc' : 'sort-desc') : '';
                $html_table .= "<th class=\"sortable-header $sort_class\" data-sortcol=\"$col_key\">$col_val</th>";
            }
            $html_table .= '<th>Banner</th><th>Aksi</th></tr></thead><tbody>';

            foreach ($game_data_from_db as $game) {
                $html_table .= '<tr>';
                $html_table .= '<td>' . htmlspecialchars($game['id'] ?? 'N/A') . '</td>';
                $html_table .= '<td>' . htmlspecialchars($game['game_code'] ?? '') . '</td>';
                
                $game_name_display = htmlspecialchars($game['game_name'] ?? '');
                $html_table .= '<td>' . $game_name_display;
                $html_table .= ' <i class="fas fa-copy copy-game-name-icon" title="Copy Nama Game" data-game-name="' . $game_name_display . '" style="cursor: pointer; margin-left: 8px; font-size: 1.2em; color: #007bff;"></i>';
                $html_table .= '</td>';
                
                $status_badge = '';
                if (isset($game['status'])) {
                    $status_badge = ($game['status'] == '1' || $game['status'] == 1) ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-danger">Tidak Aktif</span>';
                } else {
                    $status_badge = 'N/A';
                }
                $html_table .= '<td>' . $status_badge . '</td>';

                $banner_img = 'N/A';
                if (!empty($game['banner'])) {
                    $banner_img = '<img src="' . htmlspecialchars($game['banner']) . '" alt="Banner" style="max-width: 80px; height: auto;">';
                }
                $html_table .= '<td>' . $banner_img . '</td>';
                $html_table .= '<td><button type="button" class="btn btn-sm btn-warning edit-game-btn" data-game-id="' . htmlspecialchars($game['id'] ?? '') . '">Edit</button></td>';
                $html_table .= '</tr>';
            }
            $html_table .= '</tbody></table></div></div></div>';
            echo $html_table;
        }
    } else {
        http_response_code(500);
        echo '<div class="card"><div class="card-body"><div class="alert alert-danger mb-0">Error saat menyiapkan query: ' . $db_connection_var->error . '</div></div></div>';
    }
    $db_connection_var->close();
} else {
    http_response_code(405);
    echo '<div class="card"><div class="card-body"><div class="alert alert-danger mb-0">Metode request tidak valid.</div></div></div>';
}
?>