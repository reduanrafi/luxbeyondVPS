<?php
// Upload this to public/ folder, visit it, then DELETE it immediately

$results = [];

// Method 1: Invalidate specific file
$file = __DIR__ . '/../app/Services/PricingService.php';
if (function_exists('opcache_invalidate')) {
    opcache_invalidate($file, true);
    $results[] = "✅ Invalidated PricingService.php";
} else {
    $results[] = "❌ opcache_invalidate not available";
}

// Method 2: Full reset
if (function_exists('opcache_reset')) {
    opcache_reset();
    $results[] = "✅ Full OPcache reset done";
} else {
    $results[] = "❌ opcache_reset not available";
}

// Method 3: Show current file content (first 5 lines to verify correct file loaded)
$lines = array_slice(file($file), 20, 5); // lines 21-25
$results[] = "📄 PricingService.php lines 21-25:<br><pre>" . htmlspecialchars(implode('', $lines)) . "</pre>";

// Status
if (function_exists('opcache_get_status')) {
    $status = opcache_get_status(false);
    $results[] = "📊 OPcache enabled: " . ($status['opcache_enabled'] ? 'Yes' : 'No');
    $results[] = "📊 Cached scripts: " . ($status['opcache_statistics']['num_cached_scripts'] ?? 'N/A');
}

echo "<html><body style='font-family:monospace;padding:20px;background:#111;color:#0f0'>";
echo "<h2 style='color:gold'>OPcache Fix Tool</h2>";
foreach ($results as $r) {
    echo "<p>$r</p>";
}
echo "<p style='color:red;margin-top:30px'>⚠️ DELETE THIS FILE NOW after reading the output!</p>";
echo "</body></html>";
