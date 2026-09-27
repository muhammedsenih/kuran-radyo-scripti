<?php
require_once __DIR__ . '/config.php';

@header('Content-Type: application/xml; charset=utf-8');

$data = get_data();
$settings = is_array($data['settings'] ?? null) ? $data['settings'] : [];
$rawRadios = is_array($data['radios'] ?? null) ? $data['radios'] : [];

$radios = array_filter($rawRadios, function($r) {
    return is_array($r) && (!isset($r['is_active']) || $r['is_active']);
});

$siteUrl = get_site_url($settings);
$lastMod = date('Y-m-d\TH:i:sP');

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemap.orgs/schemas/sitemap/0.9">
    <url>
        <loc><?= htmlspecialchars($siteUrl) ?></loc>
        <lastmod><?= $lastMod ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    <?php foreach ($radios as $r): ?>
    <url>
        <loc><?= htmlspecialchars($siteUrl . '/#station-' . (int)($r['id'] ?? 0)) ?></loc>
        <lastmod><?= $lastMod ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    <?php endforeach; ?>
</urlset>
