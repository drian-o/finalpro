<?php
// admin/voucher/get_vouchers.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if (!isset($_SESSION['kode_admin'])) {
    echo json_encode(['data' => [], 'recordsTotal' => 0, 'recordsFiltered' => 0]);
    exit();
}

include_once __DIR__ . '/../../koneksi.php'; // Sesuaikan path

// DataTables parameters
$draw = $_POST['draw'] ?? 1;
$start = $_POST['start'] ?? 0;
$length = $_POST['length'] ?? 10;
$searchValue = $_POST['search']['value'] ?? '';
$orderColumnIndex = $_POST['order'][0]['column'] ?? 0;
$orderDir = $_POST['order'][0]['dir'] ?? 'asc';

// Columns array for ordering and searching
$columns = ['id', 'voucher_code', 'amount', 'is_active', 'created_at', 'updated_at'];
$orderColumn = $columns[$orderColumnIndex] ?? 'id'; // Default to 'id'

// Total records (without filtering)
$totalRecordsQuery = "SELECT COUNT(*) AS total FROM vouchers";
$totalResult = mysqli_query($koneksi, $totalRecordsQuery);
$totalRecords = mysqli_fetch_assoc($totalResult)['total'];

// Filtered records query
$whereClause = "";
if (!empty($searchValue)) {
    $searchTerms = [];
    foreach ($columns as $col) {
        $searchTerms[] = $col . " LIKE '%" . mysqli_real_escape_string($koneksi, $searchValue) . "%'";
    }
    $whereClause = " WHERE " . implode(" OR ", $searchTerms);
}

$filteredRecordsQuery = "SELECT COUNT(*) AS total FROM vouchers" . $whereClause;
$filteredResult = mysqli_query($koneksi, $filteredRecordsQuery);
$filteredRecords = mysqli_fetch_assoc($filteredResult)['total'];

// Main data query
$dataQuery = "SELECT id, voucher_code, amount, is_active, created_at, updated_at FROM vouchers"
             . $whereClause
             . " ORDER BY " . mysqli_real_escape_string($koneksi, $orderColumn) . " " . mysqli_real_escape_string($koneksi, $orderDir)
             . " LIMIT " . intval($start) . ", " . intval($length);

$dataResult = mysqli_query($koneksi, $dataQuery);

$data = [];
while ($row = mysqli_fetch_assoc($dataResult)) {
    $data[] = $row;
}

echo json_encode([
    "draw" => intval($draw),
    "recordsTotal" => intval($totalRecords),
    "recordsFiltered" => intval($filteredRecords),
    "data" => $data
]);

exit();
?>