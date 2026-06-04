<?php
echo "<h3>Hasil Pengecekan Environment Variables:</h3>";
echo "CF_EMAIL: " . (getenv('CF_EMAIL') ?: "❌ TIDAK KEBACA") . "<br>";
echo "CF_GLOBAL_KEY: " . (getenv('CF_GLOBAL_KEY') ? "✅ KEBACA (Rahasia)" : "❌ TIDAK KEBACA") . "<br>";
echo "CF_ZONE_ID: " . (getenv('CF_ZONE_ID') ?: "❌ TIDAK KEBACA") . "<br>";
?>
