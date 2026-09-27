<?php
require_once __DIR__ . '/../config.php';

$data = get_data();
$settings = $data['settings'];
$error = '';

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $token = $_POST['csrf_token'] ?? '';

    if (!verify_csrf($token)) {
        $error = 'Geçersiz CSRF güvenlik doğrulama jetonu.';
    } else {
        usleep(100000); // 100ms delay

        $storedUser = $settings['admin_username'] ?? 'admin';
        $storedHash = $settings['admin_password'] ?? '';

        if ($username === 'demo' && $password === '123456') {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_user'] = 'demo';
            $_SESSION['user_role'] = 'demo';
            $_SESSION['admin_pass_hash'] = md5('demo_123456');
            header('Location: index.php');
            exit;
        } elseif (strtolower($username) === strtolower($storedUser) && check_admin_password($password, $storedHash)) {
            if (!password_verify($password, $storedHash)) {
                $storedHash = password_hash($password, PASSWORD_DEFAULT);
                $data['settings']['admin_password'] = $storedHash;
                save_data($data);
            }
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_user'] = $storedUser;
            $_SESSION['user_role'] = 'admin';
            $_SESSION['admin_pass_hash'] = md5($storedHash);
            header('Location: index.php');
            exit;
        } else {
            $error = 'Hatalı kullanıcı adı veya şifre!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yönetim Paneli Girişi - Kur'an Radyo</title>
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
<body class="bg-slate-100 dark:bg-emerald-950 text-slate-800 dark:text-slate-100 min-h-screen flex items-center justify-center p-4 transition-colors duration-200 relative">

    <div class="w-full max-w-md bg-white dark:bg-emerald-900/90 p-8 rounded-3xl border border-slate-200 dark:border-amber-500/30 shadow-2xl shadow-slate-300/60 dark:shadow-emerald-950/80 space-y-6 relative">
        
        <div class="text-center space-y-2 relative">
            <div class="w-16 h-16 rounded-2xl bg-amber-50 dark:bg-emerald-950 p-2.5 border border-amber-200 dark:border-amber-500/40 shadow-md mx-auto flex items-center justify-center text-amber-600 dark:text-amber-400 text-2xl">
                <?php if (!empty($settings['logo_url'])): ?>
                    <img src="../<?= sanitize($settings['logo_url']) ?>" alt="Logo" class="w-full h-full object-contain">
                <?php else: ?>
                    <i class="fas fa-radio"></i>
                <?php endif; ?>
            </div>
            <h2 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Yönetici Girişi</h2>
            <p class="text-xs text-slate-600 dark:text-emerald-300 font-semibold">Canlı Kur'an Radyo Yönetim Paneli</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="p-3.5 rounded-xl bg-rose-100 dark:bg-rose-500/20 border border-rose-300 dark:border-rose-500/40 text-rose-900 dark:text-rose-200 text-xs font-bold text-center">
                <i class="fas fa-exclamation-circle mr-1 text-rose-600 dark:text-rose-400"></i> <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

            <div>
                <label class="block text-xs font-bold text-slate-800 dark:text-emerald-200 uppercase tracking-wider mb-1.5">Kullanıcı Adı</label>
                <div class="relative">
                    <input type="text" name="username" required placeholder="Kullanıcı Adınız" autocomplete="username" class="w-full pl-10 pr-4 py-3 rounded-xl bg-slate-50 dark:bg-emerald-950/70 border border-slate-300 dark:border-emerald-700/60 text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-emerald-500/60 focus:bg-white dark:focus:bg-emerald-950 focus:outline-none focus:border-amber-500 dark:focus:border-amber-400 focus:ring-2 focus:ring-amber-500/20 transition-all font-medium">
                    <i class="fas fa-user absolute left-3.5 top-3.5 text-slate-400 dark:text-emerald-400 text-sm"></i>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-800 dark:text-emerald-200 uppercase tracking-wider mb-1.5">Şifre</label>
                <div class="relative">
                    <input type="password" name="password" required placeholder="Şifreniz" class="w-full pl-10 pr-4 py-3 rounded-xl bg-slate-50 dark:bg-emerald-950/70 border border-slate-300 dark:border-emerald-700/60 text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-emerald-500/60 focus:bg-white dark:focus:bg-emerald-950 focus:outline-none focus:border-amber-500 dark:focus:border-amber-400 focus:ring-2 focus:ring-amber-500/20 transition-all font-medium">
                    <i class="fas fa-lock absolute left-3.5 top-3.5 text-slate-400 dark:text-emerald-400 text-sm"></i>
                </div>
            </div>

            <button type="submit" class="w-full py-3.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 dark:from-amber-400 dark:to-amber-500 hover:from-amber-400 hover:to-amber-500 text-slate-950 dark:text-emerald-950 font-black text-sm shadow-md shadow-amber-500/20 hover:scale-[1.01] active:scale-95 transition-all cursor-pointer">
                Giriş Yap <i class="fas fa-arrow-right ml-1"></i>
            </button>
        </form>

        <div class="pt-3 border-t border-slate-200 dark:border-emerald-800/40 flex items-center justify-between">
            <a href="../index.php" class="text-xs font-bold text-emerald-800 dark:text-emerald-400 hover:text-amber-600 dark:hover:text-amber-300 transition-colors flex items-center gap-1.5">
                <i class="fas fa-arrow-left"></i> Ana Sayfaya Dön
            </a>
            <button id="adminLoginThemeToggle" type="button" class="px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-emerald-950/60 border border-slate-200 dark:border-emerald-700/40 text-xs font-bold text-slate-700 dark:text-emerald-300 hover:text-amber-600 dark:hover:text-amber-400 transition-all flex items-center gap-1.5 shadow-sm">
                <!-- Inner HTML updated by JS -->
            </button>
        </div>

    </div>

    <script>
        function updateThemeButton() {
            const isDark = document.documentElement.classList.contains('dark');
            const btn = document.getElementById('adminLoginThemeToggle');
            if (btn) {
                btn.innerHTML = isDark 
                    ? '<i class="fas fa-sun text-amber-400"></i> Aydınlık Mod' 
                    : '<i class="fas fa-moon text-slate-600"></i> Karanlık Mod';
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            updateThemeButton();
            document.getElementById('adminLoginThemeToggle')?.addEventListener('click', () => {
                const isDark = document.documentElement.classList.toggle('dark');
                localStorage.setItem('quran_admin_theme', isDark ? 'dark' : 'light');
                updateThemeButton();
            });
        });
    </script>
</body>
</html>
