<?php
require_once __DIR__ . '/config.php';

$data = get_data();
$settings = is_array($data['settings'] ?? null) ? $data['settings'] : [];
$rawRadios = is_array($data['radios'] ?? null) ? $data['radios'] : [];

$radios = array_filter($rawRadios, function($r) {
    return is_array($r) && (!isset($r['is_active']) || $r['is_active']);
});

usort($radios, function($a, $b) {
    return (int)($a['order'] ?? 0) <=> (int)($b['order'] ?? 0);
});

$siteUrl = get_site_url($settings);

$defaultRadio = [
    'id' => 1,
    'slug' => 'genel-kuran-radyosu',
    'title' => "Genel Kur'an Radyosu",
    'reciter' => "Diyanet & Seçkin Kâriler",
    'category' => "Diyanet Yayınları",
    'stream_url' => "https://backup.qurango.net/radio/mix",
    'logo' => "",
    'description' => "Diyanet İşleri Başkanlığı resmi 7/24 kesintisiz Kur'an-ı Kerim yayını."
];

if (!empty($radios)) {
    $firstRadio = reset($radios);
    if (is_array($firstRadio)) {
        $defaultRadio = $firstRadio;
    }
    foreach ($radios as $r) {
        if (is_array($r) && !empty($r['is_default'])) {
            $defaultRadio = $r;
            break;
        }
    }
}

// SEO Radio Station Routing: /radyo/genel-kuran-radyosu or index.php?r=genel-kuran-radyosu
$requestedSlug = sanitize_input($_GET['r'] ?? $_GET['radio'] ?? $_GET['slug'] ?? '');
if (empty($requestedSlug) && isset($_SERVER['REQUEST_URI'])) {
    $uriPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (preg_match('#/radyo/([^/]+)#', $uriPath, $matches)) {
        $requestedSlug = sanitize_input($matches[1]);
    }
}
if (!empty($requestedSlug)) {
    foreach ($radios as $r) {
        if (is_array($r) && ((!empty($r['slug']) && $r['slug'] === $requestedSlug) || (string)($r['id'] ?? 0) === $requestedSlug)) {
            $defaultRadio = $r;
            break;
        }
    }
}

$radioSlug = !empty($defaultRadio['slug']) ? $defaultRadio['slug'] : slugify($defaultRadio['title'] ?? 'karma-kuran-radyosu');
$pageTitle = sanitize($defaultRadio['title'] ?? '') . " Canlı Dinle - " . sanitize($settings['site_title'] ?? "Kur'an Radyo");
$pageDesc = sanitize($defaultRadio['description'] ?? $settings['site_description'] ?? '');
$pageCanonical = $siteUrl . '/radyo/' . $radioSlug;

$defaultCover = !empty($settings['default_cover_url']) ? $settings['default_cover_url'] : 'assets/images/default-cover.jpg';
$currentCover = !empty($defaultRadio['logo']) ? $defaultRadio['logo'] : $defaultCover;

$v = time();
?>
<!DOCTYPE html>
<html lang="tr" class="dark scroll-smooth">
<head>
    <meta charset="UTF-8">
    <script>
        (function() {
            const savedTheme = localStorage.getItem('quran_theme');
            if (savedTheme === 'light') {
                document.documentElement.classList.remove('dark');
            } else {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    <base href="<?= htmlspecialchars(rtrim($siteUrl, '/') . '/') ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <meta name="description" content="<?= $pageDesc ?>">
    <meta name="keywords" content="<?= sanitize($settings['site_keywords'] ?? "") ?>">
    <link rel="canonical" href="<?= htmlspecialchars($pageCanonical) ?>">

    <link rel="manifest" href="manifest.json">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Kur'an Radyo">
    <link rel="apple-touch-icon" href="assets/images/logo.webp">

    <?php if(!empty($settings['favicon_url'])): ?>
        <link rel="icon" type="image/webp" href="<?= sanitize($settings['favicon_url']) ?>">
    <?php else: ?>
        <link rel="icon" type="image/webp" href="assets/images/logo.webp">
    <?php endif; ?>
    <meta name="theme-color" content="#0F2C23">

    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= htmlspecialchars($pageCanonical) ?>">
    <meta property="og:title" content="<?= $pageTitle ?>">
    <meta property="og:description" content="<?= $pageDesc ?>">
    <?php 
    $ogImage = !empty($currentCover) ? $currentCover : (!empty($settings['logo_url']) ? $settings['logo_url'] : 'assets/images/default-cover.jpg');
    if (!empty($ogImage) && strpos($ogImage, 'http') !== 0) {
        $ogImage = $siteUrl . '/' . ltrim($ogImage, '/');
    }
    ?>
    <meta property="og:image" content="<?= htmlspecialchars($ogImage) ?>">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {}
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css?v=<?= $v ?>">

    <style>
        .header-site-logo {
            height: 60px !important;
            max-height: 60px !important;
            width: auto !important;
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
        }
    </style>

    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@graph": [
        {
          "@type": "WebSite",
          "@id": "<?= htmlspecialchars($siteUrl) ?>/#website",
          "url": "<?= htmlspecialchars($siteUrl) ?>",
          "name": "<?= sanitize($settings['site_title'] ?? "") ?>",
          "description": "<?= sanitize($settings['site_description'] ?? "") ?>"
        },
        {
          "@type": "RadioStation",
          "@id": "<?= htmlspecialchars($siteUrl) ?>/#radiostation",
          "name": "<?= sanitize($settings['site_title'] ?? "") ?>",
          "url": "<?= htmlspecialchars($siteUrl) ?>",
          "genre": ["Islamic", "Quran Recitation", "Spiritual"],
          "inLanguage": ["ar", "tr"]
        }
      ]
    }
    </script>

    <?php if (!empty($settings['header_code'])): ?>
        <?= $settings['header_code'] ?>
    <?php endif; ?>
</head>
<body class="islamic-bg-pattern min-h-screen flex flex-col antialiased selection:bg-emerald-600 selection:text-white">

    <!-- Header Ticker & Main Nav -->
    <header class="sticky top-0 z-40 bg-white/95 dark:bg-emerald-950/95 backdrop-blur-md border-b border-emerald-900/10 dark:border-emerald-800/40 shadow-sm">
        <div class="bg-gradient-to-r from-emerald-950 via-emerald-900 to-emerald-950 text-amber-300 py-2 px-3 text-xs md:text-sm font-medium border-b border-amber-500/30">
            <div class="max-w-7xl mx-auto flex items-center justify-between">
                <span class="inline-flex items-center gap-1.5 opacity-90 text-amber-400 font-bold flex-shrink-0">
                    <i class="fas fa-book-quran"></i> Günün Ayeti / Hadisi
                </span>
                <div class="ticker-scroll-container">
                    <span id="dailyAyahTicker" class="ticker-scroll-text transition-opacity duration-300 font-serif italic text-amber-200">
                        «Kur'an okunduğu zaman onu dinleyin ve susun ki size merhamet edilsin.» (A'râf Sûresi, 204)
                    </span>
                </div>
                <span class="hidden md:inline-flex items-center gap-2 text-emerald-300 text-xs font-semibold flex-shrink-0">
                    <i class="fas fa-wifi text-emerald-400 animate-pulse"></i> 7/24 Canlı Yayın
                </span>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <a href="index.php" class="flex items-center gap-3.5 group">
                <?php if (!empty($settings['logo_url'])): ?>
                    <img src="<?= sanitize($settings['logo_url']) ?>" alt="Logo" class="header-site-logo object-contain">
                <?php else: ?>
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-emerald-800 to-emerald-950 p-2.5 border border-amber-500/40 shadow-sm flex items-center justify-center text-amber-400 text-lg">
                        <i class="fas fa-radio"></i>
                    </div>
                <?php endif; ?>
                <div>
                    <h1 class="font-extrabold text-xl tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
                        Kur'an Radyo
                    </h1>
                    <p class="text-xs text-slate-600 dark:text-emerald-300 font-medium">Tilavet & Mealli Yayın Portalı</p>
                </div>
            </a>

            <div class="flex items-center gap-2 sm:gap-3">
                <button id="pwaInstallBtn" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-2 rounded-2xl bg-amber-500/20 text-amber-600 dark:text-amber-300 border border-amber-500/40 text-xs font-bold hover:scale-105 transition-all">
                    <i class="fas fa-download text-amber-500"></i> Uygulamayı Yükle
                </button>

                <button id="themeToggleBtn" aria-label="Gece/Gündüz Modu" class="w-10 h-10 rounded-2xl bg-slate-100 dark:bg-emerald-900/60 border border-slate-200 dark:border-emerald-700/50 flex items-center justify-center hover:scale-105 active:scale-95 transition-all">
                    <i class="fas fa-moon text-emerald-800 dark:text-amber-400 text-base"></i>
                </button>

                <button id="embedModalOpenBtn" title="Sitene Ekle" class="hidden md:inline-flex items-center gap-1.5 px-3.5 py-2 rounded-2xl bg-slate-100 dark:bg-emerald-900/60 border border-slate-200 dark:border-emerald-700/50 text-slate-800 dark:text-emerald-100 text-xs font-bold hover:border-amber-500 transition-all">
                    <i class="fas fa-code text-amber-500 text-sm"></i> Sitene Ekle
                </button>
            </div>
        </div>
    </header>

    <main class="flex-grow max-w-7xl w-full mx-auto px-3 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-6 sm:space-y-8">

        <!-- Hero Section: Dynamic Player -->
        <section class="hero-player-card rounded-2xl sm:rounded-3xl p-4 sm:p-6 lg:p-8 shadow-2xl text-white">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 lg:gap-8 items-center">
                
                <div class="lg:col-span-4 flex flex-col items-center justify-center text-center">
                    <div class="relative group">
                        <div class="w-44 h-44 sm:w-52 sm:h-52 rounded-3xl bg-emerald-950/80 p-3 border-2 border-amber-400/60 shadow-2xl relative z-10 overflow-hidden flex items-center justify-center">
                            <img id="currentLogo" src="<?= !empty($currentCover) ? sanitize($currentCover) : '' ?>" alt="Station Logo" class="w-full h-full object-contain <?= empty($currentCover) ? 'hidden' : '' ?>" onerror="this.classList.add('hidden'); const fallback = document.getElementById('currentLogoIcon'); if(fallback) fallback.classList.remove('hidden');">
                            <div id="currentLogoIcon" class="w-full h-full flex flex-col items-center justify-center text-amber-400 text-5xl <?= !empty($currentCover) ? 'hidden' : '' ?>">
                                <i class="fas fa-book-quran"></i>
                            </div>
                        </div>
                        <div class="absolute -inset-2 rounded-3xl bg-gradient-to-r from-amber-400 to-emerald-500 opacity-20 blur-lg group-hover:opacity-40 transition-all"></div>
                    </div>
                    
                    <div class="mt-4 flex items-center justify-center gap-2 h-8 min-h-[32px]">
                        <span id="statusBadge" class="hidden px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider"></span>
                        <span id="sleepTimerBadge" class="hidden px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-500/20 text-amber-300 flex items-center gap-1">
                            <i class="fas fa-clock"></i> <span></span>
                        </span>
                    </div>
                </div>

                <div class="lg:col-span-8 space-y-6">
                    <div>
                        <div class="flex items-center gap-3">
                            <span id="currentCategory" class="px-3 py-1 rounded-lg text-xs font-extrabold bg-amber-400/20 text-amber-300 border border-amber-400/40 uppercase tracking-wider">
                                <?= sanitize($defaultRadio['category'] ?? 'Genel') ?>
                            </span>
                        </div>
                        <h2 id="currentTitle" class="text-2xl sm:text-3xl lg:text-4xl font-extrabold mt-2 text-white tracking-tight">
                            <?= sanitize($defaultRadio['title'] ?? 'Kur\'an Radyo') ?>
                        </h2>
                        <p id="currentReciter" class="text-base sm:text-lg text-emerald-200/90 font-semibold mt-1 flex items-center gap-2">
                            <i class="fas fa-microphone-lines text-amber-400"></i>
                            <?= sanitize($defaultRadio['reciter'] ?? 'Kâri Bilgisi') ?>
                        </p>
                    </div>

                    <div class="bg-emerald-950/70 rounded-2xl p-3 border border-emerald-700/40">
                        <canvas id="visualizerCanvas"></canvas>
                    </div>

                    <div class="flex flex-wrap items-center justify-center sm:justify-between gap-4 pt-2">
                        
                        <div class="flex items-center gap-3 sm:gap-4">
                            <button id="prevBtn" aria-label="Önceki Radyo" class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl bg-emerald-900/80 hover:bg-emerald-800 border border-emerald-600/50 text-emerald-100 flex items-center justify-center hover:scale-105 active:scale-95 transition-all">
                                <i class="fas fa-step-backward text-base sm:text-lg"></i>
                            </button>

                            <button id="playPauseBtn" aria-label="Oynat/Durdur" class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-gradient-to-br from-amber-300 via-amber-400 to-amber-500 hover:from-amber-200 hover:to-amber-400 text-emerald-950 flex items-center justify-center shadow-lg shadow-amber-500/30 hover:scale-105 active:scale-95 transition-all">
                                <i class="fas fa-play text-xl sm:text-2xl ml-0.5"></i>
                            </button>

                            <button id="nextBtn" aria-label="Sonraki Radyo" class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl bg-emerald-900/80 hover:bg-emerald-800 border border-emerald-600/50 text-emerald-100 flex items-center justify-center hover:scale-105 active:scale-95 transition-all">
                                <i class="fas fa-step-forward text-base sm:text-lg"></i>
                            </button>
                        </div>

                        <div class="flex items-center gap-2.5 bg-emerald-950/50 px-3.5 py-2 sm:py-2.5 rounded-2xl border border-emerald-700/40">
                            <button id="muteBtn" aria-label="Sesi Aç/Kapat" class="text-emerald-300 hover:text-amber-400 transition-colors">
                                <i class="fas fa-volume-up text-base sm:text-lg"></i>
                            </button>
                            <input id="volumeSlider" type="range" min="0" max="1" step="0.01" value="0.8" class="w-20 sm:w-32 accent-amber-400 cursor-pointer">
                        </div>

                        <div class="flex items-center gap-2">
                            <button id="favoriteToggleBtn" title="Favorilere Ekle" class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-emerald-950/50 border border-emerald-700/40 flex items-center justify-center hover:border-rose-400 transition-all">
                                <i id="favIcon" class="far fa-heart text-gray-400 text-base sm:text-lg"></i>
                            </button>
                            <button id="shareModalOpenBtn" title="Radyoyu Paylaş" class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-emerald-950/50 border border-emerald-700/40 flex items-center justify-center hover:border-amber-400 text-amber-400 transition-all">
                                <i class="fas fa-share-alt text-base sm:text-lg"></i>
                            </button>
                            <button id="sleepTimerBtn" title="Uyku Zamanlayıcısı" class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-emerald-950/50 border border-emerald-700/40 flex items-center justify-center hover:border-amber-400 text-amber-300 transition-all">
                                <i class="fas fa-moon text-base sm:text-lg"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stations Filtering & Search Catalog -->
        <section class="space-y-6">
            <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
                <div>
                    <h3 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                        <i class="fas fa-radio text-amber-500"></i> Canlı Radyo İstasyonları
                    </h3>
                    <p class="text-sm text-slate-600 dark:text-emerald-300 font-medium">Dinlemek istediğiniz yayını veya kâriyi seçiniz</p>
                </div>

                <div class="relative w-full md:w-80">
                    <input id="stationSearchInput" type="text" placeholder="İstasyon veya Kâri Ara..." class="w-full pl-10 pr-4 py-2.5 rounded-2xl bg-white dark:bg-emerald-900/40 border border-slate-300 dark:border-emerald-700 text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-emerald-400/60 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-all shadow-sm">
                    <i class="fas fa-search absolute left-3.5 top-3.5 text-slate-400 dark:text-emerald-400 text-sm"></i>
                </div>
            </div>

            <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none">
                <button class="category-filter-btn whitespace-nowrap flex-shrink-0 px-4 py-2 rounded-xl text-xs font-extrabold transition-all bg-emerald-600 text-white shadow-md" data-category="all">
                    Tümü
                </button>
                <button class="category-filter-btn whitespace-nowrap flex-shrink-0 px-4 py-2 rounded-xl text-xs font-bold transition-all bg-slate-200 dark:bg-emerald-900/40 text-slate-800 dark:text-emerald-200 hover:border-amber-500 border border-transparent" data-category="Popüler Hafızlar">
                    Popüler Hafızlar
                </button>
                <button class="category-filter-btn whitespace-nowrap flex-shrink-0 px-4 py-2 rounded-xl text-xs font-bold transition-all bg-slate-200 dark:bg-emerald-900/40 text-slate-800 dark:text-emerald-200 hover:border-amber-500 border border-transparent" data-category="Türkçe Mealli Yayınlar">
                    Türkçe Mealli Yayınlar
                </button>
                <button class="category-filter-btn whitespace-nowrap flex-shrink-0 px-4 py-2 rounded-xl text-xs font-bold transition-all bg-slate-200 dark:bg-emerald-900/40 text-slate-800 dark:text-emerald-200 hover:border-amber-500 border border-transparent" data-category="7/24 Kesintisiz Hatim">
                    7/24 Kesintisiz Hatim
                </button>
                <button class="category-filter-btn whitespace-nowrap flex-shrink-0 px-4 py-2 rounded-xl text-xs font-bold transition-all bg-slate-200 dark:bg-emerald-900/40 text-slate-800 dark:text-emerald-200 hover:border-amber-500 border border-transparent" data-category="Diyanet Yayınları">
                    Diyanet Yayınları
                </button>
                <button class="category-filter-btn whitespace-nowrap flex-shrink-0 px-4 py-2 rounded-xl text-xs font-bold transition-all bg-slate-200 dark:bg-emerald-900/40 text-slate-800 dark:text-emerald-200 hover:border-amber-500 border border-transparent" data-category="favorites">
                    <i class="fas fa-heart text-rose-500 mr-1"></i> Favorilerim
                </button>
            </div>

            <div id="stationsGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($radios as $index => $r): 
                    $cardLogo = !empty($r['logo']) ? $r['logo'] : $defaultCover;
                ?>
                    <div class="station-card rounded-2xl p-5 cursor-pointer flex flex-col justify-between group"
                         data-id="<?= (int)($r['id'] ?? 0) ?>"
                         data-index="<?= (int)$index ?>"
                         data-title="<?= sanitize($r['title'] ?? '') ?>"
                         data-reciter="<?= sanitize($r['reciter'] ?? '') ?>"
                         data-category="<?= sanitize($r['category'] ?? '') ?>"
                         onclick="window.QuranPlayerInstance.loadStation(<?= (int)$index ?>, true)">
                        
                        <div>
                            <div class="flex items-start justify-between gap-3">
                                <div class="w-14 h-14 rounded-xl bg-slate-100 dark:bg-emerald-950 p-2 border border-slate-200 dark:border-amber-500/30 flex items-center justify-center overflow-hidden flex-shrink-0">
                                    <?php if(!empty($cardLogo)): ?>
                                        <img src="<?= sanitize($cardLogo) ?>" alt="Logo" class="w-full h-full object-contain">
                                    <?php else: ?>
                                        <i class="fas fa-book-quran text-amber-500 text-xl"></i>
                                    <?php endif; ?>
                                </div>
                                <span class="px-2.5 py-1 rounded-md text-[10px] font-extrabold uppercase bg-slate-100 dark:bg-emerald-900/50 text-slate-700 dark:text-emerald-300 border border-slate-200 dark:border-emerald-700/40">
                                    <?= sanitize($r['category'] ?? 'Genel') ?>
                                </span>
                            </div>

                            <h4 class="text-lg font-extrabold text-slate-900 dark:text-white mt-4 group-hover:text-emerald-600 dark:group-hover:text-amber-400 transition-colors">
                                <?= sanitize($r['title'] ?? '') ?>
                            </h4>
                            <p class="text-xs text-slate-600 dark:text-emerald-300 font-semibold mt-1">
                                <i class="fas fa-user-circle text-amber-500 mr-1"></i> <?= sanitize($r['reciter'] ?? '') ?>
                            </p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 line-clamp-2">
                                <?= sanitize($r['description'] ?? '') ?>
                            </p>
                        </div>

                        <div class="mt-5 pt-4 border-t border-slate-200 dark:border-emerald-800/40 flex items-center justify-between">
                            <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 live-badge-dot"></span> Canlı Yayın
                            </span>

                            <button class="play-station-btn w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-700 to-emerald-900 text-amber-300 flex items-center justify-center shadow-md group-hover:scale-110 transition-all">
                                <i class="fas fa-play text-xs"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Site Features Highlights Section -->
        <section class="mt-8 rounded-3xl bg-slate-100/70 dark:bg-emerald-950/40 border border-slate-200 dark:border-emerald-800/60 p-6 sm:p-8 space-y-5">
            <div class="flex items-center justify-between border-b border-slate-200 dark:border-emerald-800/60 pb-3">
                <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-star text-amber-500"></i> Platform Özellikleri
                </h3>
                <span class="text-xs font-semibold text-slate-500 dark:text-emerald-300/70">7/24 Gelişmiş Dinleme Deneyimi</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div class="p-5 rounded-2xl bg-white dark:bg-emerald-900/30 border border-slate-200 dark:border-emerald-800/50 flex items-start gap-4 shadow-sm hover:border-amber-400/50 transition-all">
                    <div class="w-11 h-11 rounded-2xl bg-amber-500/20 text-amber-500 flex items-center justify-center text-lg flex-shrink-0 border border-amber-500/30">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-extrabold text-slate-900 dark:text-white">Kilit Ekranı Destekli</h4>
                        <p class="text-xs text-slate-600 dark:text-emerald-300/80 mt-1.5 leading-relaxed">
                            Telefonunuzun kilit ekranında radyo ve kâri bilgilerini görebilir, yayını kilit ekranından kolayca durdurup başlatabilirsiniz.
                        </p>
                    </div>
                </div>

                <div class="p-5 rounded-2xl bg-white dark:bg-emerald-900/30 border border-slate-200 dark:border-emerald-800/50 flex items-start gap-4 shadow-sm hover:border-amber-400/50 transition-all">
                    <div class="w-11 h-11 rounded-2xl bg-amber-500/20 text-amber-500 flex items-center justify-center text-lg flex-shrink-0 border border-amber-500/30">
                        <i class="fas fa-moon"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-extrabold text-slate-900 dark:text-white">Uyku Zamanlayıcısı</h4>
                        <p class="text-xs text-slate-600 dark:text-emerald-300/80 mt-1.5 leading-relaxed">
                            Gece Kur'an-ı Kerim dinleyerek uyumak isteyenler için 15, 30 veya 60 dakika sonra yayını otomatik kapatma özelliği.
                        </p>
                    </div>
                </div>

                <div class="p-5 rounded-2xl bg-white dark:bg-emerald-900/30 border border-slate-200 dark:border-emerald-800/50 flex items-start gap-4 shadow-sm hover:border-amber-400/50 transition-all">
                    <div class="w-11 h-11 rounded-2xl bg-amber-500/20 text-amber-500 flex items-center justify-center text-lg flex-shrink-0 border border-amber-500/30">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-extrabold text-slate-900 dark:text-white">Kesintisiz & Hızlı</h4>
                        <p class="text-xs text-slate-600 dark:text-emerald-300/80 mt-1.5 leading-relaxed">
                            Veritabanı bağımlılığı olmadan, yüksek performanslı ve düşük veri tüketen canlı ses akış mimarisi.
                        </p>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <footer class="mt-12 border-t border-slate-200 dark:border-emerald-800/40 bg-white dark:bg-emerald-950 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-4 text-center md:text-left">
            <div>
                <p class="text-sm font-extrabold text-slate-900 dark:text-white">
                    <?= sanitize($settings['site_title'] ?? "Canlı Kur'an-ı Kerim Radyosu") ?>
                </p>
                <p class="text-xs text-slate-600 dark:text-emerald-300 mt-1">
                    7/24 Kesintisiz Canlı Kur'an-ı Kerim & Tilavet Portalı
                </p>
            </div>

            <div class="flex items-center gap-3">
                <?php if(!empty($settings['social_facebook'])): ?>
                    <a href="<?= sanitize($settings['social_facebook']) ?>" target="_blank" aria-label="Facebook" class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-emerald-900/60 text-slate-700 dark:text-emerald-200 flex items-center justify-center hover:bg-emerald-600 hover:text-white transition-all">
                        <i class="fab fa-facebook-f text-sm"></i>
                    </a>
                <?php endif; ?>
                <?php if(!empty($settings['social_twitter'])): ?>
                    <a href="<?= sanitize($settings['social_twitter']) ?>" target="_blank" aria-label="X (Twitter)" class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-emerald-900/60 text-slate-800 dark:text-emerald-200 flex items-center justify-center hover:bg-slate-900 hover:text-white dark:hover:bg-emerald-600 transition-all">
                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                    </a>
                <?php endif; ?>
                <?php if(!empty($settings['social_instagram'])): ?>
                    <a href="<?= sanitize($settings['social_instagram']) ?>" target="_blank" aria-label="Instagram" class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-emerald-900/60 text-slate-700 dark:text-emerald-200 flex items-center justify-center hover:bg-emerald-600 hover:text-white transition-all">
                        <i class="fab fa-instagram text-sm"></i>
                    </a>
                <?php endif; ?>
                <?php if(!empty($settings['social_whatsapp'])): ?>
                    <a href="<?= sanitize($settings['social_whatsapp']) ?>" target="_blank" aria-label="WhatsApp" class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-emerald-900/60 text-slate-700 dark:text-emerald-200 flex items-center justify-center hover:bg-emerald-600 hover:text-white transition-all">
                        <i class="fab fa-whatsapp text-sm"></i>
                    </a>
                <?php endif; ?>
            </div>

            <p class="text-xs text-slate-500 dark:text-slate-400">
                &copy; <?= date('Y') ?> Tüm Hakları Saklıdır.
            </p>
        </div>
    </footer>

    <div id="embedModal" class="hidden fixed inset-0 z-50 flex items-center justify-center modal-backdrop p-4 overflow-y-auto">
        <div class="bg-white dark:bg-emerald-950 border border-slate-200 dark:border-emerald-800 rounded-3xl max-w-2xl w-full p-6 space-y-5 shadow-2xl relative my-8">
            <div class="flex items-center justify-between border-b border-slate-200 dark:border-emerald-800/60 pb-3">
                <div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-code text-amber-500"></i> Sitene Radyo Player Ekle (Widget)
                    </h3>
                    <p class="text-xs text-slate-600 dark:text-emerald-300 mt-0.5">Kendi web siteniz için özelleştirilebilir canlı Kur'an-ı Kerim radyosu</p>
                </div>
                <button id="embedModalCloseBtn" class="text-gray-400 hover:text-gray-600 dark:hover:text-white text-lg">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Customization Controls -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 dark:text-emerald-200 uppercase mb-1">Radyo Kanalı</label>
                    <select id="embedRadioSelect" class="w-full p-2.5 rounded-xl bg-slate-50 dark:bg-emerald-900/60 border border-slate-300 dark:border-emerald-700/60 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-amber-500">
                        <?php foreach ($radios as $r): ?>
                            <option value="<?= sanitize($r['slug'] ?? $r['id']) ?>"><?= sanitize($r['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 dark:text-emerald-200 uppercase mb-1">Tema Stili</label>
                    <select id="embedThemeSelect" class="w-full p-2.5 rounded-xl bg-slate-50 dark:bg-emerald-900/60 border border-slate-300 dark:border-emerald-700/60 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-amber-500">
                        <option value="dark">Koyu Tema (Karanlık)</option>
                        <option value="light">Aydınlık Tema (Açık)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 dark:text-emerald-200 uppercase mb-1">Görünüm Tasarımı</label>
                    <select id="embedStyleSelect" class="w-full p-2.5 rounded-xl bg-slate-50 dark:bg-emerald-900/60 border border-slate-300 dark:border-emerald-700/60 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-amber-500">
                        <option value="card">Geniş Kart Player (Album Art)</option>
                        <option value="bar">İnce Mini Bar (Footer/Sidebar)</option>
                    </select>
                </div>
            </div>

            <!-- Live Interactive Preview -->
            <div class="space-y-1">
                <span class="block text-[11px] font-bold text-amber-600 dark:text-amber-400 uppercase">Canlı Widget Önizlemesi:</span>
                <div id="embedPreviewWrapper" class="w-full rounded-2xl bg-slate-100 dark:bg-slate-900/80 p-3 border border-slate-300 dark:border-emerald-800 flex items-center justify-center min-h-[170px]">
                    <iframe id="embedPreviewIframe" src="" class="w-full border-0 rounded-xl" style="height: 170px;"></iframe>
                </div>
            </div>

            <!-- Generated Iframe Code -->
            <div class="space-y-1">
                <label class="block text-[11px] font-bold text-slate-700 dark:text-emerald-200 uppercase">HTML Embed Kodu:</label>
                <textarea id="embedCodeInput" readonly rows="3" class="w-full p-3 rounded-xl bg-slate-50 dark:bg-emerald-900/40 border border-slate-300 dark:border-emerald-800 font-mono text-xs text-slate-800 dark:text-emerald-200 focus:outline-none select-all"></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-1">
                <button id="copyEmbedCodeBtn" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-amber-400 to-amber-500 text-emerald-950 font-extrabold text-xs shadow-md hover:scale-105 active:scale-95 transition-all flex items-center gap-1.5">
                    <i class="fas fa-copy"></i> Kodu Kopyala
                </button>
            </div>
        </div>
    </div>

    <div id="sleepTimerModal" class="hidden fixed inset-0 z-50 flex items-center justify-center modal-backdrop p-4">
        <div class="bg-white dark:bg-emerald-950 border border-slate-200 dark:border-emerald-800 rounded-3xl max-w-sm w-full p-6 space-y-5 shadow-2xl relative">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-moon text-amber-500"></i> Uyku Zamanlayıcısı
                </h3>
                <button id="sleepTimerCloseBtn" class="text-gray-400 hover:text-gray-600 dark:hover:text-white text-lg">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <p class="text-xs text-slate-600 dark:text-emerald-300">
                Yayın belirlenen süre sonunda otomatik olarak durdurulacaktır.
            </p>
            <div class="grid grid-cols-2 gap-3">
                <button class="sleep-option-btn p-3 rounded-xl border border-slate-200 dark:border-emerald-800 text-slate-800 dark:text-emerald-200 font-bold text-xs hover:bg-emerald-600 hover:text-white transition-all" data-minutes="15">
                    15 Dakika
                </button>
                <button class="sleep-option-btn p-3 rounded-xl border border-slate-200 dark:border-emerald-800 text-slate-800 dark:text-emerald-200 font-bold text-xs hover:bg-emerald-600 hover:text-white transition-all" data-minutes="30">
                    30 Dakika
                </button>
                <button class="sleep-option-btn p-3 rounded-xl border border-slate-200 dark:border-emerald-800 text-slate-800 dark:text-emerald-200 font-bold text-xs hover:bg-emerald-600 hover:text-white transition-all" data-minutes="45">
                    45 Dakika
                </button>
                <button class="sleep-option-btn p-3 rounded-xl border border-slate-200 dark:border-emerald-800 text-slate-800 dark:text-emerald-200 font-bold text-xs hover:bg-emerald-600 hover:text-white transition-all" data-minutes="60">
                    60 Dakika
                </button>
            </div>
            <div class="pt-2">
                <button class="sleep-option-btn w-full p-2.5 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-500 font-bold text-xs hover:bg-rose-500 hover:text-white transition-all" data-minutes="0">
                    Zamanlayıcıyı İptal Et
                </button>
            </div>
        </div>
    </div>

    <!-- Şehir Seçimi Modalı -->
    <div id="cityModal" class="hidden fixed inset-0 z-50 flex items-center justify-center modal-backdrop p-4">
        <div class="bg-white dark:bg-emerald-950 border border-slate-200 dark:border-emerald-800 rounded-3xl max-w-md w-full p-6 space-y-5 shadow-2xl relative">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-city text-amber-500"></i> Şehir Seçiniz (Namaz Vakitleri)
                </h3>
                <button id="cityModalCloseBtn" class="text-gray-400 hover:text-gray-600 dark:hover:text-white text-lg">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <p class="text-xs text-slate-600 dark:text-emerald-300">
                Bulunduğunuz şehri seçerek namaz vakitlerini ve kalan süreyi kendi konumunuza göre güncelleyebilirsiniz.
            </p>
            <div class="relative">
                <input id="citySearchInput" type="text" placeholder="Şehir Ara (örn: İstanbul, Ankara, İzmir, Berlin...)" class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-100 dark:bg-emerald-900/40 border border-slate-300 dark:border-emerald-800 text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <i class="fas fa-search absolute left-3.5 top-3 text-slate-400 text-xs"></i>
            </div>
            <div id="cityListGrid" class="grid grid-cols-2 sm:grid-cols-3 gap-2 max-h-56 overflow-y-auto pr-1 text-xs font-semibold">
                <!-- Dynamically populated cities -->
            </div>
        </div>
    </div>

    <!-- Sosyal Medya Paylaşım Modalı -->
    <div id="shareModal" class="hidden fixed inset-0 z-50 flex items-center justify-center modal-backdrop p-4">
        <div class="bg-white dark:bg-emerald-950 border border-slate-200 dark:border-emerald-800 rounded-3xl max-w-md w-full p-6 space-y-5 shadow-2xl relative">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-share-alt text-amber-500"></i> Yayın Paylaş
                </h3>
                <button id="shareModalCloseBtn" class="text-gray-400 hover:text-gray-600 dark:hover:text-white text-lg">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <p id="shareStationText" class="text-xs text-slate-600 dark:text-emerald-300 font-semibold">
                Canlı Kur'an-ı Kerim radyosunu sevdiklerinizle ve sosyal medyada paylaşabilirsiniz.
            </p>
            <div class="grid grid-cols-3 gap-3 pt-2">
                <button id="shareWhatsappBtn" class="p-3 rounded-2xl bg-emerald-600/10 border border-emerald-600/30 text-emerald-500 flex flex-col items-center justify-center gap-1.5 hover:bg-emerald-600 hover:text-white transition-all">
                    <i class="fab fa-whatsapp text-2xl"></i>
                    <span class="text-[11px] font-bold">WhatsApp</span>
                </button>
                <button id="shareTelegramBtn" class="p-3 rounded-2xl bg-sky-500/10 border border-sky-500/30 text-sky-400 flex flex-col items-center justify-center gap-1.5 hover:bg-sky-500 hover:text-white transition-all">
                    <i class="fab fa-telegram-plane text-2xl"></i>
                    <span class="text-[11px] font-bold">Telegram</span>
                </button>
                <button id="shareTwitterBtn" class="p-3 rounded-2xl bg-slate-100 dark:bg-slate-800/80 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 flex flex-col items-center justify-center gap-1.5 hover:bg-slate-900 hover:text-white dark:hover:bg-slate-700 transition-all">
                    <i class="fa-brands fa-x-twitter text-2xl"></i>
                    <span class="text-[11px] font-bold">X (Twitter)</span>
                </button>
            </div>
            <div class="pt-2 flex gap-2">
                <input id="shareUrlInput" type="text" readonly value="<?= htmlspecialchars($siteUrl) ?>" class="flex-grow p-2.5 rounded-xl bg-slate-100 dark:bg-emerald-900/40 border border-slate-300 dark:border-emerald-800 text-xs text-slate-800 dark:text-emerald-200 focus:outline-none">
                <button id="copyShareUrlBtn" class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-amber-400 to-amber-500 text-emerald-950 font-bold text-xs shadow hover:scale-105 transition-all">
                    Kopyala
                </button>
            </div>
        </div>
    </div>

    <!-- 114 Sure Rehberi Modalı -->
    <div id="surahModal" class="hidden fixed inset-0 z-50 flex items-center justify-center modal-backdrop p-4">
        <div class="bg-white dark:bg-emerald-950 border border-slate-200 dark:border-emerald-800 rounded-3xl max-w-3xl w-full p-6 space-y-5 shadow-2xl relative">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-book-quran text-amber-500"></i> Kur'an-ı Kerim 114 Sure Rehberi
                    </h3>
                    <p class="text-xs text-slate-600 dark:text-emerald-300 mt-0.5">Sure adları, ayet sayıları, cüz numaraları ve nüzul bilgileri</p>
                </div>
                <button id="surahModalCloseBtn" class="text-gray-400 hover:text-gray-600 dark:hover:text-white text-lg">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="relative">
                <input id="surahSearchInput" type="text" placeholder="Sure adı, cüz veya anlam ara (örn: Fatiha, Yasin, Ayet 286...)" class="w-full pl-10 pr-4 py-3 rounded-2xl bg-slate-100 dark:bg-emerald-900/40 border border-slate-300 dark:border-emerald-800 text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500">
                <i class="fas fa-search absolute left-3.5 top-3.5 text-slate-400 text-sm"></i>
            </div>

            <div id="surahGridList" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 max-h-96 overflow-y-auto pr-1">
                <!-- Dynamically populated 114 surahs -->
            </div>
        </div>
    </div>

    <!-- Uygulama Yükleme / PWA Rehberi Modalı -->
    <div id="pwaModal" class="hidden fixed inset-0 z-50 flex items-center justify-center modal-backdrop p-4">
        <div class="bg-white dark:bg-emerald-950 border border-slate-200 dark:border-emerald-800 rounded-3xl max-w-lg w-full p-6 space-y-5 shadow-2xl relative">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-desktop text-amber-500"></i> Uygulamayı Masaüstüne & Telefona Yükle
                </h3>
                <button id="pwaModalCloseBtn" class="text-gray-400 hover:text-gray-600 dark:hover:text-white text-lg">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <p class="text-xs text-slate-600 dark:text-emerald-300">
                Kur'an Radyo uygulamasını bilgisayarınıza (Masaüstü PC/Mac) veya telefonunuza uygulama olarak kurabilirsiniz.
            </p>

            <div class="space-y-3">
                <div class="p-3.5 rounded-2xl bg-slate-100 dark:bg-emerald-900/40 border border-slate-200 dark:border-emerald-800/60 space-y-1">
                    <h4 class="text-xs font-bold text-amber-500 flex items-center gap-2">
                        <i class="fas fa-laptop text-sm"></i> Masaüstü Bilgisayar (Chrome & Microsoft Edge)
                    </h4>
                    <p class="text-[11px] text-slate-600 dark:text-emerald-200/90 leading-relaxed">
                        Adres çubuğunun sağ tarafındaki <b>"Uygulamayı Yükle"</b> (<i class="fas fa-download text-amber-400"></i>) simgesine veya aşağıdaki butona tıklayarak Masaüstü Programı olarak kurabilirsiniz.
                    </p>
                </div>

                <div class="p-3.5 rounded-2xl bg-slate-100 dark:bg-emerald-900/40 border border-slate-200 dark:border-emerald-800/60 space-y-1">
                    <h4 class="text-xs font-bold text-amber-500 flex items-center gap-2">
                        <i class="fab fa-apple text-sm"></i> iPhone & Mac (Safari)
                    </h4>
                    <p class="text-[11px] text-slate-600 dark:text-emerald-200/90 leading-relaxed">
                        Safari altındaki <b>Paylaş</b> (<i class="fas fa-share-square text-amber-400"></i>) butonuna tıklayıp <b>"Ana Ekrana Ekle"</b> veya <b>"Dock'a Ekle"</b> seçeneğini tıklayın.
                    </p>
                </div>

                <div class="p-3.5 rounded-2xl bg-slate-100 dark:bg-emerald-900/40 border border-slate-200 dark:border-emerald-800/60 space-y-1">
                    <h4 class="text-xs font-bold text-amber-500 flex items-center gap-2">
                        <i class="fab fa-android text-sm"></i> Android Telefonlar
                    </h4>
                    <p class="text-[11px] text-slate-600 dark:text-emerald-200/90 leading-relaxed">
                        Chrome menüsünden (üç nokta) <b>"Uygulamayı Yükle"</b> seçeneğini seçin.
                    </p>
                </div>
            </div>

            <div class="pt-2 flex justify-end gap-3">
                <button id="triggerPwaPromptBtn" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-400 to-amber-500 text-emerald-950 font-extrabold text-xs shadow-md hover:scale-105 transition-all">
                    Otomatik Yüklemeyi Başlat <i class="fas fa-download ml-1"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Auto Install Bottom Banner -->
    <div id="mobilePwaBanner" class="hidden fixed bottom-4 left-4 right-4 z-50 bg-gradient-to-r from-emerald-950 via-emerald-900 to-emerald-950 p-3.5 rounded-2xl border border-amber-500/50 shadow-2xl flex items-center justify-between gap-3 text-white transition-all">
        <div class="flex items-center gap-3">
            <img src="assets/images/logo.webp" alt="App Icon" class="w-10 h-10 rounded-xl border border-amber-400/50 flex-shrink-0 object-cover">
            <div>
                <h4 class="font-extrabold text-xs text-amber-300">Kur'an Radyo Uygulamasını Yükle</h4>
                <p class="text-[11px] text-emerald-200/90">Ana ekrana ekle, kilit ekranında dinle</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button id="mobilePwaInstallBtn" class="px-3.5 py-1.5 rounded-xl bg-amber-400 text-emerald-950 font-extrabold text-xs shadow-md hover:scale-105 active:scale-95 transition-all">
                Yükle
            </button>
            <button id="mobilePwaCloseBtn" class="w-7 h-7 rounded-lg text-emerald-300 hover:text-white flex items-center justify-center text-xs">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    <script>
        window.SITE_BASE_URL = <?= json_encode(rtrim($siteUrl, '/') . '/', JSON_UNESCAPED_UNICODE) ?>;
        window.INITIAL_RADIO_SLUG = <?= json_encode($radioSlug, JSON_UNESCAPED_UNICODE) ?>;
    </script>
    <script src="assets/js/player.js?v=<?= $v ?>"></script>
    <script src="assets/js/main.js?v=<?= $v ?>"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const phpStations = <?= json_encode(array_values($radios), JSON_UNESCAPED_UNICODE) ?>;
            const defaultCoverUrl = <?= json_encode($defaultCover, JSON_UNESCAPED_UNICODE) ?>;
            window.DEFAULT_COVER_URL = defaultCoverUrl;
            if (window.QuranPlayerInstance) {
                window.QuranPlayerInstance.setDefaultCover(defaultCoverUrl);
                window.QuranPlayerInstance.setStations(phpStations);
            }
        });
    </script>

    <?php if (!empty($settings['footer_code'])): ?>
        <?= $settings['footer_code'] ?>
    <?php endif; ?>
</body>
</html>
