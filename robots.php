<?php
require_once __DIR__ . '/config.php';

header('Content-Type: text/plain; charset=utf-8');

$data = get_data();
$settings = $data['settings'];
$siteUrl = get_site_url($settings);
?>
User-agent: *
Disallow: /admin/
Disallow: /data/

Sitemap: <?= htmlspecialchars($siteUrl) ?>/sitemap.xml
