<?php
require_once __DIR__ . '/../config.php';
require_login();

$data = get_data();
$settings = $data['settings'];
$currentPage = basename($_SERVER['PHP_SELF']);

// Check default password warning
$isDefaultPass = password_verify('admin123', $settings['admin_password']);
?>
<!DOCTYPE html>
<html lang="tr" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yönetim Paneli - Kur'an Radyo</title>
    <link rel="icon" type="image/webp" href="../assets/images/logo.webp">
    <script>
        (function() {
            const savedTheme = localStorage.getItem('quran_admin_theme') || 'dark';
            if (savedTheme === 'light') {
                document.documentElement.classList.remove('dark');
            } else {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
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
                        gold: {
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
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-slate-100 dark:bg-emerald-950 text-slate-800 dark:text-slate-100 min-h-screen flex flex-col transition-colors duration-200">

    <!-- Admin Navigation Header -->
    <?php if (is_demo()): ?>
        <div class="bg-amber-500/20 border-b border-amber-500/40 py-2 px-4 text-center text-xs font-bold text-amber-950 dark:text-amber-300 flex items-center justify-center gap-2">
            <i class="fas fa-eye text-amber-500"></i> Demo Modundasınız (Kullanıcı: demo | Sadece Okuma/İnceleme). Paneli gezebilirsiniz ancak değişiklikler kaydedilemez.
        </div>
    <?php endif; ?>
    <header class="bg-white/95 dark:bg-emerald-900/90 backdrop-blur-md border-b border-slate-200 dark:border-emerald-800/60 sticky top-0 z-40 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            
            <a href="index.php" class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-800 to-emerald-950 p-1 border border-amber-500/40 shadow-sm flex items-center justify-center text-amber-400 text-lg">
                    <?php if (!empty($settings['logo_url'])): ?>
                        <img src="../<?= sanitize($settings['logo_url']) ?>" alt="Logo" class="w-full h-full object-contain">
                    <?php else: ?>
                        <i class="fas fa-radio"></i>
                    <?php endif; ?>
                </div>
                <div>
                    <span class="font-extrabold text-slate-900 dark:text-white text-base tracking-tight">Yönetim Paneli</span>
                    <span class="text-[10px] block text-emerald-600 dark:text-emerald-400 font-bold">Kur'an Radyo & Tilavet</span>
                </div>
            </a>

            <!-- Nav Links -->
            <nav class="hidden md:flex items-center gap-1">
                <a href="index.php" class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all <?= $currentPage === 'index.php' ? 'bg-emerald-600 dark:bg-emerald-700 text-white dark:text-gold-300 shadow-sm' : 'text-slate-700 dark:text-emerald-200 hover:bg-slate-100 dark:hover:bg-emerald-800' ?>">
                    <i class="fas fa-chart-pie mr-1.5"></i> Özet
                </a>
                <a href="radios.php" class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all <?= $currentPage === 'radios.php' ? 'bg-emerald-600 dark:bg-emerald-700 text-white dark:text-gold-300 shadow-sm' : 'text-slate-700 dark:text-emerald-200 hover:bg-slate-100 dark:hover:bg-emerald-800' ?>">
                    <i class="fas fa-radio mr-1.5"></i> İstasyonlar
                </a>
                <a href="settings.php" class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all <?= $currentPage === 'settings.php' ? 'bg-emerald-600 dark:bg-emerald-700 text-white dark:text-gold-300 shadow-sm' : 'text-slate-700 dark:text-emerald-200 hover:bg-slate-100 dark:hover:bg-emerald-800' ?>">
                    <i class="fas fa-cog mr-1.5"></i> Ayarlar & SEO
                </a>
                <a href="security.php" class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all <?= $currentPage === 'security.php' ? 'bg-emerald-600 dark:bg-emerald-700 text-white dark:text-gold-300 shadow-sm' : 'text-slate-700 dark:text-emerald-200 hover:bg-slate-100 dark:hover:bg-emerald-800' ?>">
                    <i class="fas fa-shield-alt mr-1.5"></i> Güvenlik
                </a>
            </nav>

            <div class="flex items-center gap-2 sm:gap-3">
                <button id="adminThemeToggleBtn" title="Tema Değiştir (Aydınlık / Karanlık)" aria-label="Gece/Gündüz Modu" class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-emerald-950/60 border border-slate-300 dark:border-emerald-700/40 flex items-center justify-center hover:scale-105 active:scale-95 transition-all text-slate-700 dark:text-amber-400">
                    <i class="fas fa-moon"></i>
                </button>

                <a href="../index.php" target="_blank" class="px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-emerald-950/60 border border-slate-300 dark:border-emerald-700/40 text-slate-800 dark:text-emerald-300 text-xs font-bold hover:border-amber-500 transition-all flex items-center gap-1.5">
                    <i class="fas fa-external-link-alt text-[10px]"></i> Sitede Gör
                </a>
                <a href="logout.php" class="px-3 py-1.5 rounded-xl bg-rose-500/10 dark:bg-rose-500/20 border border-rose-500/30 text-rose-600 dark:text-rose-300 text-xs font-bold hover:bg-rose-600 hover:text-white transition-all flex items-center gap-1.5">
                    <i class="fas fa-sign-out-alt"></i> Çıkış
                </a>
            </div>

        </div>

        <!-- Mobile Submenu -->
        <div class="md:hidden flex items-center justify-around border-t border-emerald-800/40 py-2 px-2 text-xs">
            <a href="index.php" class="<?= $currentPage === 'index.php' ? 'text-gold-400 font-bold' : 'text-emerald-300' ?>">Özet</a>
            <a href="radios.php" class="<?= $currentPage === 'radios.php' ? 'text-gold-400 font-bold' : 'text-emerald-300' ?>">İstasyonlar</a>
            <a href="settings.php" class="<?= $currentPage === 'settings.php' ? 'text-gold-400 font-bold' : 'text-emerald-300' ?>">Ayarlar</a>
            <a href="security.php" class="<?= $currentPage === 'security.php' ? 'text-gold-400 font-bold' : 'text-emerald-300' ?>">Güvenlik</a>
        </div>
    </header>

    <?php if (!is_demo() && $isDefaultPass): ?>
        <div class="bg-amber-500/20 border-b border-amber-500/40 text-amber-950 dark:text-amber-200 py-2.5 px-4 text-center text-xs font-semibold flex items-center justify-center gap-2">
            <i class="fas fa-exclamation-triangle text-amber-600 dark:text-amber-400 text-base"></i>
            <span class="font-bold">DİKKAT: Varsayılan yönetici şifrenizi henüz değiştirmediniz! Güvenliğiniz için lütfen güncelleyin.</span>
            <a href="security.php" class="underline hover:text-amber-700 dark:hover:text-white ml-2 font-extrabold">Şimdi Değiştir &rarr;</a>
        </div>
    <?php endif; ?>

    <main class="flex-grow max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
