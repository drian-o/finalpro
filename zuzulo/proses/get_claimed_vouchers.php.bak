<?php
// admin/voucher/get_claimed_vouchers.php
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
// Alias columns for easier use in DataTables and joins
$columns = [
    'ucv.id',
    'v.voucher_code',
    'a.nama_pengguna_anggota', // Alias for anggota username
    'v.amount',
    'ucv.claimed_at'
];
$orderColumn = $columns[$orderColumnIndex] ?? 'ucv.id';

// Base Query with JOIN
$baseQuery = "FROM user_claimed_vouchers ucv
              JOIN vouchers v ON ucv.voucher_id = v.id
              JOIN anggota a ON ucv.user_id = a.id_anggota";

// Total records (without filtering)
$totalRecordsQuery = "SELECT COUNT(ucv.id) AS total " . $baseQuery;
$totalResult = mysqli_query($koneksi, $totalRecordsQuery);
$totalRecords = mysqli_fetch_assoc($totalResult)['total'];

// Filtered records query
$whereClause = "";
if (!empty($searchValue)) {
    $searchTerms = [];
    // Only search on relevant string columns for user viewing
    $searchableColumns = ['v.voucher_code', 'a.nama_pengguna_anggota'];
    foreach ($searchableColumns as $col) {
        $searchTerms[] = $col . " LIKE '%" . mysqli_real_escape_string($koneksi, $searchValue) . "%'";
    }
    // Also allow searching by amount if numeric
    if (is_numeric($searchValue)) {
         $searchTerms[] = "v.amount = " . floatval($searchValue);
    }
    $whereClause = " WHERE " . implode(" OR ", $searchTerms);
}

$filteredRecordsQuery = "SELECT COUNT(ucv.id) AS total " . $baseQuery . $whereClause;
$filteredResult = mysqli_query($koneksi, $filteredRecordsQuery);
$filteredRecords = mysqli_fetch_assoc($filteredResult)['total'];

// Main data query
$dataQuery = "SELECT ucv.id AS claim_id, v.voucher_code, a.nama_pengguna_anggota, v.amount, ucv.claimed_at "
             . $baseQuery
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