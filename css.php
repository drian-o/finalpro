<?php
// Pastikan path ke file CSS Anda benar
$css_file_path = '_next/static/css/0a4ae62ed810513b.css'; // Sesuaikan path ini

$colors = [];
if (file_exists($css_file_path)) {
    $css_content = file_get_contents($css_file_path);

    // Regex untuk mencari blok :root
    preg_match('/:root\s*\{([^}]+)\}/', $css_content, $root_block_match);

    if (isset($root_block_match[1])) {
        $root_block = $root_block_match[1];
        
        // Regex untuk mencari setiap variabel (--nama-variabel: #nilai;)
        preg_match_all('/(--[a-zA-Z0-9-]+):\s*([^;]+);/', $root_block, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            $variable_name = trim($match[1]);
            $color_value = trim($match[2]);
            $colors[$variable_name] = $color_value;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Palet Warna Website</title>
    <link rel="stylesheet" href="<?php echo $css_file_path; ?>">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            background-color: var(--background, #1b1b1b);
            color: var(--base, #fff);
            padding: 2rem;
            font-family: sans-serif;
        }
        .color-swatch {
            width: 50px;
            height: 50px;
            border: 1px solid var(--separator, #7e869e);
            border-radius: 8px;
            margin-right: 1rem;
        }
    </style>
</head>
<body>

    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">Palet Warna Website</h1>
        
        <?php if (!empty($colors)): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ($colors as $name => $value): ?>
                    <div class="bg-secondaryBackground p-4 rounded-lg flex items-center">
                        <div class="color-swatch" style="background-color: <?php echo htmlspecialchars($value); ?>"></div>
                        <div>
                            <p class="font-semibold"><?php echo htmlspecialchars($name); ?></p>
                            <p class="text-sm text-caption"><?php echo htmlspecialchars($value); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>Tidak ada variabel warna yang ditemukan atau file CSS tidak dapat dimuat.</p>
        <?php endif; ?>
    </div>

</body>
</html>