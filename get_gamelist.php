<?php
// Sertakan file koneksi.php
include 'koneksi.php';

$providers = [];
$query = "SELECT `provider_code`, `provider_name` FROM `nexus_provider` ORDER BY `provider_name` ASC";
$result = mysqli_query($koneksi, $query);

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $providers[] = $row;
    }
}

mysqli_close($koneksi);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ambil Data Game</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ccc; border-radius: 8px; }
        h1 { text-align: center; }
        form { display: flex; flex-direction: column; gap: 15px; }
        select, button { padding: 10px; font-size: 16px; border-radius: 4px; }
        button { background-color: #007BFF; color: white; border: none; cursor: pointer; }
        button:hover { background-color: #0056b3; }
        .response-box { margin-top: 20px; padding: 10px; background-color: #f2f2f2; border: 1px solid #ddd; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Ambil Daftar Game</h1>
        <form action="process_gamelist.php" method="POST">
            <label for="provider_code">Pilih Provider:</label>
            <select name="provider_code" id="provider_code" required>
                <option value="">-- Pilih Provider --</option>
                <?php foreach ($providers as $provider): ?>
                    <option value="<?php echo htmlspecialchars($provider['provider_code']); ?>">
                        <?php echo htmlspecialchars($provider['provider_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Ambil & Simpan Data Game</button>
        </form>
        
        <?php if (isset($_GET['status'])): ?>
            <div class="response-box">
                <?php if ($_GET['status'] === 'success'): ?>
                    <p style="color: green;">Data game berhasil disimpan!</p>
                <?php elseif ($_GET['status'] === 'error'): ?>
                    <p style="color: red;">Error: <?php echo htmlspecialchars($_GET['message']); ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>