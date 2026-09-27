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
        $data['settings']['site_title'] = sanitize_input($_POST['site_title'] ?? '');
        $data['settings']['site_description'] = sanitize_input($_POST['site_description'] ?? '');
        $data['settings']['site_keywords'] = sanitize_input($_POST['site_keywords'] ?? '');
        $data['settings']['canonical_url'] = trim($_POST['canonical_url'] ?? '');
        $data['settings']['site_url'] = trim($_POST['site_url'] ?? '');
        $data['settings']['logo_url'] = sanitize_input($_POST['logo_url'] ?? '');
        $data['settings']['favicon_url'] = sanitize_input($_POST['favicon_url'] ?? '');
        $data['settings']['default_cover_url'] = sanitize_input($_POST['default_cover_url'] ?? '');
        $data['settings']['contact_email'] = sanitize_input($_POST['contact_email'] ?? '');
        $data['settings']['social_facebook'] = trim($_POST['social_facebook'] ?? '');
        $data['settings']['social_twitter'] = trim($_POST['social_twitter'] ?? '');
        $data['settings']['social_instagram'] = trim($_POST['social_instagram'] ?? '');
        $data['settings']['social_whatsapp'] = trim($_POST['social_whatsapp'] ?? '');
        
        $data['settings']['header_code'] = $_POST['header_code'] ?? '';
        $data['settings']['footer_code'] = $_POST['footer_code'] ?? '';

        if (save_data($data)) {
            $message = 'Site ve SEO ayarları başarıyla güncellendi.';
            $settings = $data['settings'];
        } else {
            $error = 'Ayarlar kaydedilirken hata oluştu.';
        }
    }
}
?>

<div class="space-y-6 max-w-4xl mx-auto">
    
    <div>
        <h2 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Genel Ayarlar & SEO Yapılandırması</h2>
        <p class="text-xs text-slate-600 dark:text-emerald-300/80 font-medium">Site meta başlıkları, görseller ve sosyal medya bağlantılarını düzenleyin</p>
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

    <form method="POST" action="settings.php" class="bg-white dark:bg-emerald-950/70 p-6 sm:p-8 rounded-3xl border border-slate-200 dark:border-emerald-800/40 shadow-sm space-y-6">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

        <!-- Basic SEO Settings -->
        <div class="space-y-4">
            <h3 class="text-base font-bold text-emerald-700 dark:text-amber-400 flex items-center gap-2 border-b border-slate-200 dark:border-emerald-800/60 pb-2">
                <i class="fas fa-search"></i> Temel SEO & Meta Bilgileri
            </h3>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-emerald-200 uppercase mb-1">Site Başlığı (Title) *</label>
                <input type="text" name="site_title" required value="<?= sanitize($settings['site_title']) ?>" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-emerald-950/60 border border-slate-300 dark:border-emerald-700/40 text-xs text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-emerald-200 uppercase mb-1">Meta Açıklaması (Description) *</label>
                <textarea name="site_description" rows="3" required class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-emerald-950/60 border border-slate-300 dark:border-emerald-700/40 text-xs text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none"><?= sanitize($settings['site_description']) ?></textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-emerald-200 uppercase mb-1">Anahtar Kelimeler (Keywords)</label>
                <input type="text" name="site_keywords" value="<?= sanitize($settings['site_keywords']) ?>" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-emerald-950/60 border border-slate-300 dark:border-emerald-700/40 text-xs text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-emerald-200 uppercase mb-1">Canonical / Domain URL</label>
                    <input type="url" name="canonical_url" value="<?= htmlspecialchars($settings['canonical_url'] ?? '') ?>" placeholder="https://kuranradyosu.com" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-emerald-950/60 border border-slate-300 dark:border-emerald-700/40 text-xs text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-emerald-200 uppercase mb-1">İletişim E-Posta</label>
                    <input type="email" name="contact_email" value="<?= sanitize($settings['contact_email'] ?? '') ?>" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-emerald-950/60 border border-slate-300 dark:border-emerald-700/40 text-xs text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none">
                </div>
            </div>
        </div>

        <!-- Media Assets Management -->
        <div class="space-y-4 pt-4 border-t border-slate-200 dark:border-emerald-800/40">
            <h3 class="text-base font-bold text-emerald-700 dark:text-amber-400 flex items-center gap-2 border-b border-slate-200 dark:border-emerald-800/60 pb-2">
                <i class="fas fa-image"></i> Görsel & Logo Yönetimi
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-emerald-200 uppercase mb-1">Site Ana Logosu URL</label>
                    <input type="text" name="logo_url" value="<?= sanitize($settings['logo_url'] ?? '') ?>" placeholder="assets/images/logo.webp" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-emerald-950/60 border border-slate-300 dark:border-emerald-700/40 text-xs text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none">
                    <span class="text-[10px] text-slate-500 dark:text-emerald-400/70 mt-1 block">Header ve Footer'da sabit duran site logosu.</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-amber-600 dark:text-amber-400 uppercase mb-1">
                        <i class="fas fa-compact-disc mr-1"></i> Varsayılan Kapak Görsel URL
                    </label>
                    <input type="text" name="default_cover_url" value="<?= sanitize($settings['default_cover_url'] ?? '') ?>" placeholder="assets/images/default-cover.jpg" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-emerald-950/60 border border-amber-400 dark:border-amber-500/50 text-xs text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none">
                    <span class="text-[10px] text-slate-500 dark:text-emerald-400/80 mt-1 block">Özel kapağı olmayan radyolarda çalarken duracak kapak resmi.</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-emerald-200 uppercase mb-1">Favicon İkon URL</label>
                    <input type="text" name="favicon_url" value="<?= sanitize($settings['favicon_url'] ?? '') ?>" placeholder="assets/images/logo.webp" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-emerald-950/60 border border-slate-300 dark:border-emerald-700/40 text-xs text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none">
                </div>
            </div>
        </div>

        <!-- Social Media Links -->
        <div class="space-y-4 pt-4 border-t border-slate-200 dark:border-emerald-800/40">
            <h3 class="text-base font-bold text-emerald-700 dark:text-amber-400 flex items-center gap-2 border-b border-slate-200 dark:border-emerald-800/60 pb-2">
                <i class="fas fa-share-alt"></i> Sosyal Medya Bağlantıları
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-emerald-200 uppercase mb-1">Facebook URL</label>
                    <input type="url" name="social_facebook" value="<?= htmlspecialchars($settings['social_facebook'] ?? '') ?>" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-emerald-950/60 border border-slate-300 dark:border-emerald-700/40 text-xs text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-emerald-200 uppercase mb-1">X / Twitter URL</label>
                    <input type="url" name="social_twitter" value="<?= htmlspecialchars($settings['social_twitter'] ?? '') ?>" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-emerald-950/60 border border-slate-300 dark:border-emerald-700/40 text-xs text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-emerald-200 uppercase mb-1">Instagram URL</label>
                    <input type="url" name="social_instagram" value="<?= htmlspecialchars($settings['social_instagram'] ?? '') ?>" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-emerald-950/60 border border-slate-300 dark:border-emerald-700/40 text-xs text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-emerald-200 uppercase mb-1">WhatsApp URL</label>
                    <input type="url" name="social_whatsapp" value="<?= htmlspecialchars($settings['social_whatsapp'] ?? '') ?>" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-emerald-950/60 border border-slate-300 dark:border-emerald-700/40 text-xs text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none">
                </div>
            </div>
        </div>

        <!-- Custom Code Injections -->
        <div class="space-y-4 pt-4 border-t border-slate-200 dark:border-emerald-800/40">
            <h3 class="text-base font-bold text-emerald-700 dark:text-amber-400 flex items-center gap-2 border-b border-slate-200 dark:border-emerald-800/60 pb-2">
                <i class="fas fa-code"></i> Özel Kod Enjeksiyonu (Header & Footer)
            </h3>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-emerald-200 uppercase mb-1">Header Özel Kod (Analytics / Search Console / CSS)</label>
                <textarea name="header_code" rows="4" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-emerald-950/60 border border-slate-300 dark:border-emerald-700/40 font-mono text-xs text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none"><?= htmlspecialchars($settings['header_code'] ?? '') ?></textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-emerald-200 uppercase mb-1">Footer Özel Kod (JS Takip Kodları)</label>
                <textarea name="footer_code" rows="4" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-emerald-950/60 border border-slate-300 dark:border-emerald-700/40 font-mono text-xs text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none"><?= htmlspecialchars($settings['footer_code'] ?? '') ?></textarea>
            </div>
        </div>

        <div class="pt-6 border-t border-slate-200 dark:border-emerald-800/60 flex justify-end">
            <button type="submit" class="px-8 py-3 rounded-xl bg-gradient-to-r from-amber-400 via-amber-500 to-amber-600 text-emerald-950 font-extrabold text-xs shadow-lg shadow-amber-500/20 hover:scale-105 active:scale-95 transition-all">
                Tüm Ayarları Kaydet <i class="fas fa-save ml-1"></i>
            </button>
        </div>

    </form>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
