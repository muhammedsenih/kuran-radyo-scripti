<?php
require_once __DIR__ . '/header.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (is_demo()) {
        $error = 'Demo kullanıcısı modundasınız (Sadece okuma/görüntüleme yetkisi). Değişiklikler kaydedilemez!';
    } elseif (!verify_csrf($token)) {
        $error = 'CSRF Güvenlik Jetonu Geçersiz.';
    } else {
        $new_username = sanitize($_POST['admin_username'] ?? '');
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($new_username)) {
            $error = 'Kullanıcı adı boş bırakılamaz.';
        } elseif (!password_verify($current_password, $settings['admin_password'])) {
            $error = 'Mevcut şifrenizi hatalı girdiniz!';
        } else {
            // Update username
            $data['settings']['admin_username'] = $new_username;

            // If new password provided
            if (!empty($new_password)) {
                if (strlen($new_password) < 6) {
                    $error = 'Yeni şifre en az 6 karakter olmalıdır.';
                } elseif ($new_password !== $confirm_password) {
                    $error = 'Yeni şifreler birbiriyle uyuşmuyor!';
                } else {
                    $data['settings']['admin_password'] = password_hash($new_password, PASSWORD_DEFAULT);
                }
            }

            if (empty($error)) {
                if (save_data($data)) {
                    $_SESSION['admin_user'] = $new_username;
                    $_SESSION['admin_pass_hash'] = md5($data['settings']['admin_password']);
                    $message = 'Güvenlik bilgileri başarıyla güncellendi. Yeni şifreniz aktifleşti.';
                    $settings = $data['settings'];
                    $isDefaultPass = password_verify('admin123', $settings['admin_password']);
                } else {
                    $error = 'Kayıt sırasında bir hata oluştu.';
                }
            }
        }
    }
}
?>

<div class="space-y-6 max-w-2xl mx-auto">
    
    <div>
        <h2 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Yönetici Güvenlik Ayarları</h2>
        <p class="text-xs text-slate-600 dark:text-emerald-300/80 font-medium">Kullanıcı adınızı ve giriş şifrenizi güvenle güncelleyin</p>
    </div>

    <?php if (!empty($message)): ?>
        <div class="p-4 rounded-xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-800 dark:text-emerald-200 text-xs font-semibold flex items-center gap-2">
            <i class="fas fa-check-circle text-emerald-600 dark:text-emerald-400 text-base"></i> <?= $message ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="p-4 rounded-xl bg-rose-500/20 border border-rose-500/40 text-rose-800 dark:text-rose-200 text-xs font-semibold flex items-center gap-2">
            <i class="fas fa-exclamation-circle text-rose-600 dark:text-rose-400 text-base"></i> <?= $error ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="security.php" class="bg-white dark:bg-emerald-950/70 p-6 sm:p-8 rounded-3xl border border-slate-200 dark:border-emerald-800/40 shadow-sm space-y-6">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

        <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-emerald-200 uppercase mb-1">Yönetici Kullanıcı Adı</label>
            <input type="text" name="admin_username" required value="<?= sanitize($settings['admin_username']) ?>" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-emerald-950/60 border border-slate-300 dark:border-emerald-700/40 text-xs text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none">
        </div>

        <div class="pt-4 border-t border-slate-200 dark:border-emerald-800/40 space-y-4">
            <h3 class="text-sm font-bold text-emerald-700 dark:text-amber-400">Şifre Değişikliği</h3>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-emerald-200 uppercase mb-1">Mevcut Şifreniz *</label>
                <input type="password" name="current_password" required class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-emerald-950/60 border border-slate-300 dark:border-emerald-700/40 text-xs text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none">
                <span class="text-[10px] text-slate-500 dark:text-emerald-400/70 mt-1 block">Değişiklikleri onaylamak için mevcut şifrenizi girmelisiniz.</span>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-emerald-200 uppercase mb-1">Yeni Şifre (Değiştirmek İstemiyorsanız Boş Bırakın)</label>
                <input type="password" name="new_password" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-emerald-950/60 border border-slate-300 dark:border-emerald-700/40 text-xs text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-emerald-200 uppercase mb-1">Yeni Şifre (Tekrar)</label>
                <input type="password" name="confirm_password" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-emerald-950/60 border border-slate-300 dark:border-emerald-700/40 text-xs text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none">
            </div>
        </div>

        <div class="pt-6 border-t border-slate-200 dark:border-emerald-800/60 flex justify-end">
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-gold-400 via-gold-500 to-gold-600 text-emerald-950 font-extrabold text-xs shadow-md hover:scale-105 active:scale-95 transition-all">
                Güvenlik Bilgilerini Güncelle <i class="fas fa-key ml-1"></i>
            </button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
