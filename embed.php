<?php
require_once __DIR__ . '/config.php';

$data = get_data();
$settings = is_array($data['settings'] ?? null) ? $data['settings'] : [];
$rawRadios = is_array($data['radios'] ?? null) ? $data['radios'] : [];

$radios = array_filter($rawRadios, function($r) {
    return is_array($r) && (!isset($r['is_active']) || $r['is_active']);
});

$requestedSlug = sanitize_input($_GET['slug'] ?? $_GET['r'] ?? '');
if (empty($requestedSlug) && isset($_SERVER['REQUEST_URI'])) {
    $uriPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (preg_match('#/embed/([^/]+)#', $uriPath, $matches)) {
        $requestedSlug = sanitize_input($matches[1]);
    }
}
$requestedId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$theme = (isset($_GET['theme']) && $_GET['theme'] === 'light') ? 'light' : 'dark';
$style = (isset($_GET['style']) && $_GET['style'] === 'bar') ? 'bar' : 'card';

$activeRadio = null;

if (!empty($requestedSlug)) {
    foreach ($radios as $r) {
        if (is_array($r) && !empty($r['slug']) && $r['slug'] === $requestedSlug) {
            $activeRadio = $r;
            break;
        }
    }
}

if (!$activeRadio && $requestedId > 0) {
    foreach ($radios as $r) {
        if (is_array($r) && (int)($r['id'] ?? 0) === $requestedId) {
            $activeRadio = $r;
            break;
        }
    }
}

if (!$activeRadio && !empty($radios)) {
    foreach ($radios as $r) {
        if (is_array($r) && !empty($r['is_default'])) {
            $activeRadio = $r;
            break;
        }
    }
    if (!$activeRadio) {
        $activeRadio = reset($radios);
    }
}

if (!$activeRadio || !is_array($activeRadio)) {
    $activeRadio = [
        'id' => 1,
        'slug' => 'genel-kuran-radyosu',
        'title' => "Genel Kur'an Radyosu",
        'reciter' => "Diyanet & Seçkin Kâriler",
        'category' => "Diyanet Yayınları",
        'stream_url' => "https://backup.qurango.net/radio/mix",
        'backup_url' => "https://stream.radiojar.com/0tpy1h0kxtzuv",
        'logo' => ""
    ];
}

$siteUrl = rtrim($settings['site_url'] ?? '', '/');
if (empty($siteUrl)) {
    $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $dir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
    $siteUrl = rtrim($scheme . '://' . $host . $dir, '/');
}

$embedCover = !empty($activeRadio['logo']) ? $activeRadio['logo'] : ($settings['default_cover_url'] ?? 'assets/images/default-cover.jpg');
if (!empty($embedCover) && strpos($embedCover, 'http') !== 0 && strpos($embedCover, '/') !== 0) {
    $embedCover = $siteUrl . '/' . ltrim($embedCover, '/');
}
?>
<!DOCTYPE html>
<html lang="tr" class="<?= $theme === 'dark' ? 'dark' : '' ?>">
<head>
    <meta charset="UTF-8">
    <base href="<?= htmlspecialchars(rtrim($siteUrl, '/') . '/') ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= sanitize($activeRadio['title'] ?? 'Kur\'an Radyo') ?> - Canlı Dinle</title>
    <link rel="icon" type="image/webp" href="assets/images/logo.webp">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        emerald: {
                            950: '#081B15',
                            900: '#0F2C23',
                            800: '#143E32',
                            700: '#1A5041',
                            600: '#059669',
                            500: '#10B981',
                        },
                        amber: {
                            400: '#FCE081',
                            500: '#D4AF37',
                            600: '#AA820A',
                        }
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&display=swap');
        body {
            font-family: 'Outfit', sans-serif;
            margin: 0;
            padding: 0;
            overflow: hidden;
            background: transparent;
        }
        @keyframes pulse-live {
            0% { transform: scale(0.95); opacity: 0.8; }
            50% { transform: scale(1.15); opacity: 1; }
            100% { transform: scale(0.95); opacity: 0.8; }
        }
        .live-dot {
            animation: pulse-live 1.6s infinite ease-in-out;
        }
        .bar-equalizer {
            display: flex;
            align-items: flex-end;
            gap: 2px;
            height: 16px;
        }
        .bar-equalizer span {
            width: 3px;
            background-color: #FCE081;
            border-radius: 2px;
            height: 4px;
            transition: height 0.15s ease;
        }
        .is-playing .bar-equalizer span:nth-child(1) { animation: eq 0.6s infinite alternate ease-in-out; }
        .is-playing .bar-equalizer span:nth-child(2) { animation: eq 0.8s infinite alternate ease-in-out 0.2s; }
        .is-playing .bar-equalizer span:nth-child(3) { animation: eq 0.5s infinite alternate ease-in-out 0.4s; }
        .is-playing .bar-equalizer span:nth-child(4) { animation: eq 0.7s infinite alternate ease-in-out 0.1s; }
        @keyframes eq {
            0% { height: 3px; }
            100% { height: 16px; }
        }
        input[type=range] {
            -webkit-appearance: none;
            background: transparent;
        }
        input[type=range]:focus { outline: none; }
        input[type=range]::-webkit-slider-runnable-track {
            width: 100%;
            height: 5px;
            cursor: pointer;
            background: rgba(148, 163, 184, 0.3);
            border-radius: 3px;
        }
        input[type=range]::-webkit-slider-thumb {
            height: 14px;
            width: 14px;
            border-radius: 50%;
            background: #D4AF37;
            cursor: pointer;
            -webkit-appearance: none;
            margin-top: -4.5px;
        }
    </style>
</head>
<body class="p-2 flex items-center justify-center">

<?php if ($style === 'bar'): ?>
    <!-- COMPACT MINI BAR WIDGET -->
    <div id="embedContainer" class="w-full max-w-2xl bg-white dark:bg-emerald-950 text-slate-900 dark:text-white border border-slate-200 dark:border-amber-500/40 rounded-2xl p-2.5 shadow-xl flex items-center gap-3 relative overflow-hidden transition-all">
        
        <div class="w-12 h-12 rounded-xl bg-slate-100 dark:bg-emerald-900 border border-slate-200 dark:border-emerald-700/60 flex-shrink-0 overflow-hidden flex items-center justify-center relative">
            <?php if (!empty($embedCover)): ?>
                <img src="<?= sanitize($embedCover) ?>" alt="Cover" class="w-full h-full object-cover">
            <?php else: ?>
                <i class="fas fa-book-quran text-amber-500 text-xl"></i>
            <?php endif; ?>
        </div>

        <div class="flex-grow min-w-0 space-y-0.5">
            <div class="flex items-center gap-2">
                <span class="text-[9px] font-extrabold px-1.5 py-0.2 rounded bg-amber-500/20 text-amber-600 dark:text-amber-400 uppercase tracking-wider">
                    <?= sanitize($activeRadio['category'] ?? 'Genel') ?>
                </span>
                <span id="embedStatus" class="text-[9px] font-extrabold px-1.5 py-0.2 rounded bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 live-dot"></span> CANLI
                </span>
            </div>
            <h3 class="text-xs font-extrabold truncate text-slate-900 dark:text-white">
                <?= sanitize($activeRadio['title'] ?? 'Kur\'an Radyo') ?>
            </h3>
            <p class="text-[10px] text-slate-500 dark:text-emerald-300 font-medium truncate">
                <?= sanitize($activeRadio['reciter'] ?? 'Kâri Bilgisi') ?>
            </p>
        </div>

        <div class="flex items-center gap-2 flex-shrink-0">
            <button id="embedPlayBtn" aria-label="Oynat/Durdur" class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-400 to-amber-600 text-emerald-950 font-bold flex items-center justify-center shadow hover:scale-105 active:scale-95 transition-all">
                <i class="fas fa-play text-xs ml-0.5"></i>
            </button>

            <div class="hidden sm:flex items-center gap-1.5 bg-slate-100 dark:bg-emerald-900/60 px-2.5 py-1.5 rounded-xl border border-slate-200 dark:border-emerald-700/40">
                <i class="fas fa-volume-up text-xs text-slate-500 dark:text-amber-400"></i>
                <input id="embedVolSlider" type="range" min="0" max="1" step="0.01" value="0.8" class="w-14 accent-amber-500 cursor-pointer">
            </div>

            <a href="<?= $siteUrl ?>/radyo/<?= sanitize($activeRadio['slug'] ?? 'karma-kuran-radyosu') ?>" target="_blank" title="Sitede Dinle" class="w-8 h-8 rounded-lg text-slate-400 dark:text-emerald-300 hover:text-amber-500 flex items-center justify-center">
                <i class="fas fa-external-link-alt text-xs"></i>
            </a>
        </div>
    </div>

<?php else: ?>
    <!-- CARD PLAYER WIDGET -->
    <div id="embedContainer" class="w-full max-w-md bg-white dark:bg-gradient-to-br dark:from-emerald-950 dark:via-emerald-900 dark:to-emerald-950 text-slate-900 dark:text-white border border-slate-200 dark:border-amber-500/40 rounded-3xl p-4 shadow-2xl flex items-center gap-4 relative overflow-hidden transition-all">
        
        <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-slate-100 dark:bg-emerald-950 border border-slate-200 dark:border-amber-400/40 flex-shrink-0 overflow-hidden relative shadow-md flex items-center justify-center">
            <?php if (!empty($embedCover)): ?>
                <img src="<?= sanitize($embedCover) ?>" alt="Cover" class="w-full h-full object-cover">
            <?php else: ?>
                <div class="w-full h-full flex items-center justify-center text-amber-500 text-3xl">
                    <i class="fas fa-book-quran"></i>
                </div>
            <?php endif; ?>
        </div>

        <div class="flex-grow min-w-0 space-y-2">
            <div class="flex items-center justify-between gap-1">
                <span class="text-[9px] font-extrabold px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-700 dark:text-amber-300 uppercase tracking-wider border border-amber-500/30">
                    <?= sanitize($activeRadio['category'] ?? 'Genel') ?>
                </span>
                <span id="embedStatus" class="text-[9px] font-extrabold px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-700 dark:text-emerald-400 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 live-dot"></span> CANLI
                </span>
            </div>

            <div>
                <h3 class="text-sm sm:text-base font-extrabold truncate text-slate-900 dark:text-white">
                    <?= sanitize($activeRadio['title'] ?? 'Kur\'an Radyo') ?>
                </h3>
                <p class="text-xs text-slate-600 dark:text-emerald-200/90 font-semibold truncate mt-0.5">
                    <i class="fas fa-microphone-lines text-amber-500 mr-1 text-[10px]"></i>
                    <?= sanitize($activeRadio['reciter'] ?? 'Kâri Bilgisi') ?>
                </p>
            </div>

            <div class="flex items-center justify-between gap-2 pt-1 border-t border-slate-100 dark:border-emerald-800/40">
                <button id="embedPlayBtn" aria-label="Oynat/Durdur" class="w-10 h-10 rounded-xl bg-gradient-to-r from-amber-400 to-amber-600 text-emerald-950 font-extrabold flex items-center justify-center shadow hover:scale-105 active:scale-95 transition-all">
                    <i class="fas fa-play text-xs ml-0.5"></i>
                </button>

                <div class="flex items-center gap-1.5 bg-slate-100 dark:bg-emerald-950/60 px-2.5 py-1.5 rounded-xl border border-slate-200 dark:border-emerald-700/40">
                    <i class="fas fa-volume-up text-xs text-slate-500 dark:text-amber-400"></i>
                    <input id="embedVolSlider" type="range" min="0" max="1" step="0.01" value="0.8" class="w-14 sm:w-20 accent-amber-500 cursor-pointer">
                </div>

                <div class="bar-equalizer opacity-70">
                    <span></span><span></span><span></span><span></span>
                </div>
            </div>
        </div>

    </div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const streamUrl = "<?= $activeRadio['stream_url'] ?? '' ?>";
    const backupUrl = "<?= $activeRadio['backup_url'] ?? '' ?>";
    const audio = new Audio(streamUrl);
    const container = document.getElementById('embedContainer');
    const playBtn = document.getElementById('embedPlayBtn');
    const volSlider = document.getElementById('embedVolSlider');
    const statusEl = document.getElementById('embedStatus');

    let isPlaying = false;

    function playAudio(url) {
        audio.src = url;
        statusEl.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-ping"></span> YÜKLENİYOR...';
        statusEl.className = 'text-[9px] font-extrabold px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center gap-1';
        
        audio.play().then(() => {
            isPlaying = true;
            if (container) container.classList.add('is-playing');
            playBtn.querySelector('i').className = 'fas fa-pause text-xs';
            statusEl.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-emerald-500 live-dot"></span> CANLI';
            statusEl.className = 'text-[9px] font-extrabold px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-700 dark:text-emerald-400 flex items-center gap-1';
        }).catch(err => {
            console.error('Stream error:', err);
            if (url !== backupUrl && backupUrl) {
                playAudio(backupUrl);
            } else {
                isPlaying = false;
                if (container) container.classList.remove('is-playing');
                playBtn.querySelector('i').className = 'fas fa-play text-xs ml-0.5';
                statusEl.innerHTML = '<i class="fas fa-exclamation-triangle"></i> YAYIN YOK';
                statusEl.className = 'text-[9px] font-extrabold px-2 py-0.5 rounded-full bg-rose-500/20 text-rose-600 dark:text-rose-400 flex items-center gap-1';
            }
        });
    }

    playBtn.addEventListener('click', () => {
        if (isPlaying) {
            audio.pause();
            isPlaying = false;
            if (container) container.classList.remove('is-playing');
            playBtn.querySelector('i').className = 'fas fa-play text-xs ml-0.5';
            statusEl.innerHTML = '<i class="fas fa-pause-circle"></i> DURDURULDU';
            statusEl.className = 'text-[9px] font-extrabold px-2 py-0.5 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 flex items-center gap-1';
        } else {
            playAudio(streamUrl);
        }
    });

    if (volSlider) {
        volSlider.addEventListener('input', (e) => {
            audio.volume = parseFloat(e.target.value);
        });
    }
});
</script>

</body>
</html>
