<?php
require_once __DIR__ . '/header.php';

$radios = $data['radios'];
$totalCount = count($radios);
$activeCount = count(array_filter($radios, function($r) { return !isset($r['is_active']) || $r['is_active']; }));
$defaultRadio = null;
foreach ($radios as $r) {
    if (!empty($r['is_default'])) {
        $defaultRadio = $r;
        break;
    }
}

$stats = get_stats_data();
$totalPlays = $stats['total_plays'] ?? 0;
$todayPlays = $stats['daily'][date('Y-m-d')] ?? 0;

$channelPlaySum = 0;
if (!empty($stats['channels'])) {
    foreach ($stats['channels'] as $ch) {
        $channelPlaySum += intval($ch['count'] ?? 0);
    }
}
$totalPlays = max($totalPlays, $channelPlaySum);

$recentPings = $stats['recent_pings'] ?? [];
$activeListeners = 0;
$now = time();
foreach ($recentPings as $ping) {
    if ($now - ($ping['time'] ?? 0) <= 600) {
        $activeListeners++;
    }
}

$countries = $stats['countries'] ?? [];
arsort($countries);

$channelsStat = $stats['channels'] ?? [];
usort($channelsStat, function($a, $b) {
    return ($b['count'] ?? 0) <=> ($a['count'] ?? 0);
});

$favChannels = array_values($channelsStat);
usort($favChannels, function($a, $b) {
    return ($b['favorites'] ?? 0) <=> ($a['favorites'] ?? 0);
});

$totalFavorites = $stats['total_favorites'] ?? 0;
if ($totalFavorites == 0 && !empty($channelsStat)) {
    foreach ($channelsStat as $ch) {
        $totalFavorites += intval($ch['favorites'] ?? 0);
    }
}
?>

<div class="space-y-6">
    <div>
        <h2 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Sistem, Dinleyici & Favori İstatistikleri</h2>
        <p class="text-xs text-slate-600 dark:text-emerald-300/80 font-medium">Kur'an Radyo portalınızın canlı dinleyici, favori etkileşimi ve kanal analizleri</p>
    </div>

    <!-- Stat Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 sm:gap-6">
        
        <div class="bg-white dark:bg-emerald-950/70 p-5 rounded-2xl border border-slate-200 dark:border-emerald-800/40 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-400 border border-emerald-300 dark:border-emerald-500/30 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fas fa-headphones animate-pulse"></i>
            </div>
            <div>
                <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400"><?= $activeListeners ?></span>
                <span class="block text-xs text-slate-600 dark:text-emerald-300 font-semibold">Anlık Canlı Dinleyici</span>
            </div>
        </div>

        <div class="bg-white dark:bg-emerald-950/70 p-5 rounded-2xl border border-slate-200 dark:border-emerald-800/40 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-blue-100 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 border border-blue-300 dark:border-blue-500/30 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fas fa-calendar-day"></i>
            </div>
            <div>
                <span class="text-2xl font-black text-slate-900 dark:text-white"><?= number_format($todayPlays) ?></span>
                <span class="block text-xs text-slate-600 dark:text-emerald-300 font-semibold">Bugünkü Dinlenme</span>
            </div>
        </div>

        <div class="bg-white dark:bg-emerald-950/70 p-5 rounded-2xl border border-slate-200 dark:border-emerald-800/40 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 border border-amber-300 dark:border-amber-500/30 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fas fa-chart-line"></i>
            </div>
            <div>
                <span class="text-2xl font-black text-amber-600 dark:text-amber-400"><?= number_format($totalPlays) ?></span>
                <span class="block text-xs text-slate-600 dark:text-emerald-300 font-semibold">Tüm Zamanlar Dinlenme</span>
            </div>
        </div>

        <div class="bg-white dark:bg-emerald-950/70 p-5 rounded-2xl border border-slate-200 dark:border-emerald-800/40 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 border border-rose-300 dark:border-rose-500/30 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fas fa-heart text-rose-500"></i>
            </div>
            <div>
                <span class="text-2xl font-black text-rose-600 dark:text-rose-400"><?= number_format($totalFavorites) ?></span>
                <span class="block text-xs text-slate-600 dark:text-emerald-300 font-semibold">Toplam Favoriye Ekleme</span>
            </div>
        </div>

        <div class="bg-white dark:bg-emerald-950/70 p-5 rounded-2xl border border-slate-200 dark:border-emerald-800/40 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-amber-400 border border-emerald-300 dark:border-amber-500/30 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fas fa-radio"></i>
            </div>
            <div>
                <span class="text-2xl font-black text-slate-900 dark:text-white"><?= $totalCount ?></span>
                <span class="block text-xs text-slate-600 dark:text-emerald-300 font-semibold">Aktif Radyo Kanalı</span>
            </div>
        </div>

    </div>

    <!-- Analytics Breakdown Section: Country, Channel & Favorite Stats -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Dinlenen Ülkeler -->
        <div class="bg-white dark:bg-emerald-950/70 p-6 rounded-2xl border border-slate-200 dark:border-emerald-800/40 shadow-sm space-y-4">
            <h3 class="text-base font-bold text-emerald-700 dark:text-amber-400 flex items-center gap-2 border-b border-slate-200 dark:border-emerald-800/60 pb-2">
                <i class="fas fa-globe-americas"></i> Dinleyici Ülke Dağılımı (IP Konum)
            </h3>
            <div class="space-y-3 max-h-64 overflow-y-auto pr-1">
                <?php if (empty($countries)): ?>
                    <p class="text-xs text-slate-400 dark:text-emerald-400/60 py-4 text-center italic">Henüz canlı dinleme verisi kaydedilmedi.</p>
                <?php else: ?>
                    <?php 
                    $maxC = max($countries) ?: 1;
                    foreach ($countries as $cCode => $cCount): 
                        $pct = round(($cCount / $maxC) * 100);
                    ?>
                        <div class="space-y-1">
                            <div class="flex justify-between text-xs font-semibold text-slate-700 dark:text-emerald-200">
                                <span><?= get_country_name($cCode) ?></span>
                                <span class="font-mono text-amber-600 dark:text-amber-400"><?= number_format($cCount) ?> dinleme</span>
                            </div>
                            <div class="w-full h-2 rounded-full bg-slate-100 dark:bg-emerald-950 overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-emerald-500 to-amber-400 rounded-full" style="width: <?= $pct ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- En Popüler Radyolar -->
        <div class="bg-white dark:bg-emerald-950/70 p-6 rounded-2xl border border-slate-200 dark:border-emerald-800/40 shadow-sm space-y-4">
            <h3 class="text-base font-bold text-emerald-700 dark:text-amber-400 flex items-center gap-2 border-b border-slate-200 dark:border-emerald-800/60 pb-2">
                <i class="fas fa-fire"></i> En Çok Dinlenen Radyolar
            </h3>
            <div class="space-y-3 max-h-64 overflow-y-auto pr-1">
                <?php if (empty($channelsStat)): ?>
                    <p class="text-xs text-slate-400 dark:text-emerald-400/60 py-4 text-center italic">Henüz kanal dinleme istatistiği oluşmadı.</p>
                <?php else: ?>
                    <?php 
                    $maxCh = max(array_column($channelsStat, 'count')) ?: 1;
                    foreach (array_slice($channelsStat, 0, 5) as $ch): 
                        $chPct = round(($ch['count'] / $maxCh) * 100);
                    ?>
                        <div class="space-y-1">
                            <div class="flex justify-between text-xs font-semibold text-slate-700 dark:text-emerald-200 truncate">
                                <span class="truncate"><?= sanitize($ch['title']) ?></span>
                                <span class="font-mono text-amber-600 dark:text-amber-300 ml-2"><?= number_format($ch['count']) ?> kez</span>
                            </div>
                            <div class="w-full h-2 rounded-full bg-slate-100 dark:bg-emerald-950 overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-amber-400 to-amber-500 rounded-full" style="width: <?= $chPct ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- En Çok Favorilenen Radyolar -->
        <div class="bg-white dark:bg-emerald-950/70 p-6 rounded-2xl border border-slate-200 dark:border-emerald-800/40 shadow-sm space-y-4">
            <h3 class="text-base font-bold text-rose-600 dark:text-rose-400 flex items-center gap-2 border-b border-slate-200 dark:border-emerald-800/60 pb-2">
                <i class="fas fa-heart text-rose-500"></i> En Çok Favorilenen Radyolar
            </h3>
            <div class="space-y-3 max-h-64 overflow-y-auto pr-1">
                <?php if (empty($favChannels)): ?>
                    <p class="text-xs text-slate-400 dark:text-emerald-400/60 py-4 text-center italic">Henüz favoriye ekleme verisi oluşmadı.</p>
                <?php else: ?>
                    <?php 
                    $maxFav = max(array_column($favChannels, 'favorites')) ?: 1;
                    foreach (array_slice($favChannels, 0, 5) as $ch): 
                        $favCount = $ch['favorites'] ?? 0;
                        $favPct = round(($favCount / $maxFav) * 100);
                    ?>
                        <div class="space-y-1">
                            <div class="flex justify-between text-xs font-semibold text-slate-700 dark:text-emerald-200 truncate">
                                <span class="truncate"><?= sanitize($ch['title']) ?></span>
                                <span class="font-mono text-rose-600 dark:text-rose-400 ml-2 flex items-center gap-1">
                                    <i class="fas fa-heart text-[10px]"></i> <?= number_format($favCount) ?> kişi
                                </span>
                            </div>
                            <div class="w-full h-2 rounded-full bg-slate-100 dark:bg-emerald-950 overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-rose-500 to-pink-500 rounded-full" style="width: <?= $favPct ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- Quick Actions Section -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <a href="radios.php?action=new" class="bg-white dark:bg-emerald-950/70 p-6 rounded-2xl border border-slate-200 dark:border-emerald-800/40 shadow-sm hover:border-amber-400 transition-all group">
            <div class="w-12 h-12 rounded-xl bg-amber-500/10 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition-transform">
                <i class="fas fa-plus"></i>
            </div>
            <h3 class="text-base font-bold text-slate-900 dark:text-white group-hover:text-amber-500 transition-colors">Yeni İstasyon Ekle</h3>
            <p class="text-xs text-slate-600 dark:text-emerald-300/70 mt-1">Sisteme yeni canlı radyo akışı, kâri ismi ve kategori tanımlayın.</p>
        </a>

        <a href="settings.php" class="bg-white dark:bg-emerald-950/70 p-6 rounded-2xl border border-slate-200 dark:border-emerald-800/40 shadow-sm hover:border-amber-400 transition-all group">
            <div class="w-12 h-12 rounded-xl bg-emerald-500/10 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition-transform">
                <i class="fas fa-sliders-h"></i>
            </div>
            <h3 class="text-base font-bold text-slate-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-300 transition-colors">Site & SEO Ayarları</h3>
            <p class="text-xs text-slate-600 dark:text-emerald-300/70 mt-1">Site başlığı, meta açıklamaları, logo ve Google Analytics kodlarını yönetin.</p>
        </a>

        <a href="security.php" class="bg-white dark:bg-emerald-950/70 p-6 rounded-2xl border border-slate-200 dark:border-emerald-800/40 shadow-sm hover:border-amber-400 transition-all group">
            <div class="w-12 h-12 rounded-xl bg-amber-500/10 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition-transform">
                <i class="fas fa-key"></i>
            </div>
            <h3 class="text-base font-bold text-slate-900 dark:text-white group-hover:text-amber-500 transition-colors">Şifre Değiştir</h3>
            <p class="text-xs text-slate-600 dark:text-emerald-300/70 mt-1">Yönetici kullanıcı adınızı ve giriş şifrenizi güvenli bir şekilde güncelleyin.</p>
        </a>

    </div>

    <!-- Active Radios Overview Table -->
    <div class="bg-white dark:bg-emerald-950/70 p-6 rounded-2xl border border-slate-200 dark:border-emerald-800/40 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-list text-amber-500"></i> Yüklü Radyo İstasyonları
            </h3>
            <a href="radios.php" class="text-xs font-bold text-amber-600 dark:text-amber-400 hover:underline">Tümünü Yönet &rarr;</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-emerald-800/60 text-emerald-800 dark:text-emerald-400 font-bold uppercase tracking-wider">
                        <th class="pb-3 px-2">Sıra</th>
                        <th class="pb-3 px-2">Radyo Başlığı</th>
                        <th class="pb-3 px-2">Kâri / Hafız</th>
                        <th class="pb-3 px-2">Kategori</th>
                        <th class="pb-3 px-2 text-center">Durum</th>
                        <th class="pb-3 px-2 text-right">İşlem</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-emerald-800/30">
                    <?php foreach ($radios as $r): ?>
                        <tr class="hover:bg-slate-50 dark:hover:bg-emerald-900/30 transition-colors">
                            <td class="py-3 px-2 font-bold text-amber-600 dark:text-amber-400">#<?= $r['order'] ?? $r['id'] ?></td>
                            <td class="py-3 px-2 font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                <?php
                                $logoSrc = !empty($r['logo']) ? $r['logo'] : ($settings['default_cover_url'] ?? '');
                                if (!empty($logoSrc) && strpos($logoSrc, 'http') !== 0 && strpos($logoSrc, '/') !== 0) {
                                    $logoSrc = '../' . $logoSrc;
                                }
                                ?>
                                <div class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-emerald-950 p-0.5 border border-slate-200 dark:border-emerald-700/60 flex items-center justify-center flex-shrink-0 overflow-hidden">
                                    <?php if (!empty($logoSrc)): ?>
                                        <img src="<?= sanitize($logoSrc) ?>" class="w-full h-full object-contain rounded" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="w-full h-full items-center justify-center text-amber-500 text-xs hidden">
                                            <i class="fas fa-radio"></i>
                                        </div>
                                    <?php else: ?>
                                        <div class="w-full h-full flex items-center justify-center text-amber-500 text-xs">
                                            <i class="fas fa-radio"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <span class="truncate"><?= sanitize($r['title']) ?></span>
                                <?php if (!empty($r['is_default'])): ?>
                                    <span class="px-1.5 py-0.5 rounded bg-amber-500/20 text-amber-700 dark:text-amber-300 text-[10px] font-extrabold flex-shrink-0">Varsayılan</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-2 text-slate-700 dark:text-emerald-200"><?= sanitize($r['reciter']) ?></td>
                            <td class="py-3 px-2 text-emerald-700 dark:text-emerald-400 font-semibold"><?= sanitize($r['category']) ?></td>
                            <td class="py-3 px-2 text-center">
                                <?php if (!isset($r['is_active']) || $r['is_active']): ?>
                                    <span class="px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-700 dark:text-emerald-400 font-bold text-[10px]">Aktif</span>
                                <?php else: ?>
                                    <span class="px-2 py-0.5 rounded-full bg-rose-500/20 text-rose-700 dark:text-rose-400 font-bold text-[10px]">Pasif</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-2 text-right">
                                <a href="radios.php?action=edit&id=<?= $r['id'] ?>" class="text-emerald-700 dark:text-emerald-300 hover:text-amber-600 dark:hover:text-amber-400 font-semibold mr-2">Düzenle</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
