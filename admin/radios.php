<?php
require_once __DIR__ . '/header.php';

$data = get_data();
$radios = &$data['radios'];
$message = '';
$error = '';
$action = sanitize($_GET['action'] ?? 'list');
$editingId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// POST Handlers (CSRF Protected)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (is_demo()) {
        $error = 'Demo kullanıcısı modundasınız (Sadece okuma/görüntüleme yetkisi). Değişiklikler kaydedilemez!';
    } elseif (!verify_csrf($token)) {
        $error = 'CSRF Doğrulama Jetonu Geçersiz.';
    } else {
        $postAction = sanitize($_POST['post_action'] ?? '');

        if ($postAction === 'save_radio') {
            $id = intval($_POST['id'] ?? 0);
            $title = sanitize_input($_POST['title'] ?? '');
            $reciter = sanitize_input($_POST['reciter'] ?? '');
            $category = sanitize_input($_POST['category'] ?? '');
            $stream_url = trim($_POST['stream_url'] ?? '');
            $backup_url = trim($_POST['backup_url'] ?? '');
            $logo = sanitize_input($_POST['logo'] ?? '');
            $description = sanitize_input($_POST['description'] ?? '');
            $order = intval($_POST['order'] ?? 1);
            $is_active = isset($_POST['is_active']) ? true : false;
            $is_default = isset($_POST['is_default']) ? true : false;

            if (empty($title) || empty($stream_url)) {
                $error = 'Radyo başlığı ve Yayın URL alanları zorunludur.';
            } else {
                if ($is_default) {
                    foreach ($radios as &$r) {
                        $r['is_default'] = false;
                    }
                }

                if ($id > 0) {
                    // Update existing
                    foreach ($radios as &$r) {
                        if ($r['id'] === $id) {
                            $r['title'] = $title;
                            $r['reciter'] = $reciter;
                            $r['category'] = $category;
                            $r['stream_url'] = $stream_url;
                            $r['backup_url'] = $backup_url;
                            $r['logo'] = $logo;
                            $r['description'] = $description;
                            $r['order'] = $order;
                            $r['is_active'] = $is_active;
                            $r['is_default'] = $is_default;
                            break;
                        }
                    }
                    $message = 'Radyo istasyonu başarıyla güncellendi.';
                } else {
                    // Create new
                    $newId = 1;
                    foreach ($radios as $r) {
                        if ($r['id'] >= $newId) $newId = $r['id'] + 1;
                    }
                    $radios[] = [
                        'id' => $newId,
                        'title' => $title,
                        'reciter' => $reciter,
                        'category' => $category,
                        'stream_url' => $stream_url,
                        'backup_url' => $backup_url,
                        'logo' => $logo,
                        'description' => $description,
                        'order' => $order,
                        'is_active' => $is_active,
                        'is_default' => $is_default
                    ];
                    $message = 'Yeni radyo istasyonu başarıyla eklendi.';
                }

                save_data($data);
                $action = 'list';
            }
        } elseif ($postAction === 'delete_radio') {
            $idToDelete = intval($_POST['id'] ?? 0);
            $radios = array_values(array_filter($radios, function($r) use ($idToDelete) {
                return $r['id'] !== $idToDelete;
            }));
            save_data($data);
            $message = 'Radyo istasyonu silindi.';
            $action = 'list';
        }
    }
}

// Find radio item for editing
$editRadio = null;
if ($action === 'edit' && $editingId > 0) {
    foreach ($radios as $r) {
        if ($r['id'] === $editingId) {
            $editRadio = $r;
            break;
        }
    }
}
?>

<div class="space-y-6">
    
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Radyo İstasyonları Yönetimi</h2>
            <p class="text-xs text-slate-600 dark:text-emerald-300/80 font-medium">Canlı akışları ekleyin, kapak görsellerini düzenleyin</p>
        </div>
        
        <?php if ($action === 'list'): ?>
            <a href="radios.php?action=new" class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-amber-400 to-amber-600 text-emerald-950 font-extrabold text-xs shadow-md hover:scale-105 active:scale-95 transition-all flex items-center gap-1.5">
                <i class="fas fa-plus"></i> Yeni İstasyon Ekle
            </a>
        <?php else: ?>
            <a href="radios.php" class="px-4 py-2.5 rounded-xl bg-slate-200 dark:bg-emerald-900 border border-slate-300 dark:border-emerald-700 text-slate-800 dark:text-emerald-300 font-bold text-xs hover:border-amber-500 transition-all flex items-center gap-1.5">
                <i class="fas fa-arrow-left"></i> Listeye Dön
            </a>
        <?php endif; ?>
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

    <!-- Action: NEW or EDIT FORM -->
    <?php if ($action === 'new' || $action === 'edit'): ?>
        
        <div class="bg-white dark:bg-emerald-950/70 p-6 sm:p-8 rounded-3xl border border-slate-200 dark:border-emerald-800/40 shadow-sm max-w-3xl mx-auto space-y-6">
            <h3 class="text-xl font-bold text-slate-900 dark:text-white border-b border-slate-200 dark:border-emerald-800/60 pb-4">
                <?= $action === 'edit' ? 'İstasyon Düzenle: ' . sanitize($editRadio['title'] ?? '') : 'Yeni İstasyon Oluştur' ?>
            </h3>

            <form method="POST" action="radios.php" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="post_action" value="save_radio">
                <input type="hidden" name="id" value="<?= $editRadio['id'] ?? 0 ?>">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-emerald-200 uppercase mb-1">Radyo / Yayın Adı *</label>
                        <input type="text" name="title" required value="<?= sanitize($editRadio['title'] ?? '') ?>" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-emerald-950/60 border border-slate-300 dark:border-emerald-700/40 text-xs text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-emerald-200 uppercase mb-1">Kâri / Okuyan Hafız</label>
                        <input type="text" name="reciter" value="<?= sanitize($editRadio['reciter'] ?? '') ?>" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-emerald-950/60 border border-slate-300 dark:border-emerald-700/40 text-xs text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-emerald-200 uppercase mb-1">Kategori</label>
                        <select name="category" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-emerald-950/60 border border-slate-300 dark:border-emerald-700/40 text-xs text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none">
                            <?php 
                            $cats = ["Diyanet Yayınları", "7/24 Kesintisiz Hatim", "Türkçe Mealli Yayınlar", "Popüler Hafızlar", "Özel Tilavetler"];
                            $currentCat = $editRadio['category'] ?? "Popüler Hafızlar";
                            foreach ($cats as $c):
                            ?>
                                <option value="<?= $c ?>" <?= $currentCat === $c ? 'selected' : '' ?>><?= $c ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-amber-600 dark:text-amber-400 uppercase mb-1">
                            <i class="fas fa-compact-disc mr-1"></i> Radyo Kapak Görseli URL *
                        </label>
                        <input type="text" name="logo" value="<?= sanitize($editRadio['logo'] ?? '') ?>" placeholder="https://.../cover.jpg veya resim linki" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-emerald-950/60 border border-amber-400 dark:border-amber-500/50 text-xs text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none">
                        <span class="text-[10px] text-slate-500 dark:text-emerald-400/80 mt-1 block">Player üzerinde görüntülenecek istasyon kapak resmi.</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-emerald-200 uppercase mb-1">Birincil Canlı Akış (Stream) URL *</label>
                        <input type="url" name="stream_url" required value="<?= htmlspecialchars($editRadio['stream_url'] ?? '') ?>" placeholder="https://backup.qurango.net/radio/..." class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-emerald-950/60 border border-slate-300 dark:border-emerald-700/40 text-xs text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-emerald-200 uppercase mb-1">Yedek Akış (Backup Stream) URL</label>
                        <input type="url" name="backup_url" value="<?= htmlspecialchars($editRadio['backup_url'] ?? '') ?>" placeholder="https://stream.radiojar.com/..." class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-emerald-950/60 border border-slate-300 dark:border-emerald-700/40 text-xs text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-emerald-200 uppercase mb-1">Açıklama</label>
                    <textarea name="description" rows="3" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-emerald-950/60 border border-slate-300 dark:border-emerald-700/40 text-xs text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none"><?= sanitize($editRadio['description'] ?? '') ?></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-emerald-200 uppercase mb-1">Görüntüleme Sırası</label>
                        <input type="number" name="order" value="<?= $editRadio['order'] ?? 1 ?>" class="w-full px-4 py-2 rounded-xl bg-slate-50 dark:bg-emerald-950/60 border border-slate-300 dark:border-emerald-700/40 text-xs text-slate-900 dark:text-white focus:border-amber-500">
                    </div>

                    <div class="flex items-center gap-2 pt-5">
                        <input type="checkbox" id="is_active" name="is_active" value="1" <?= (!isset($editRadio['is_active']) || $editRadio['is_active']) ? 'checked' : '' ?> class="w-4 h-4 accent-emerald-600">
                        <label for="is_active" class="text-xs font-bold text-slate-800 dark:text-white">Yayında (Aktif)</label>
                    </div>

                    <div class="flex items-center gap-2 pt-5">
                        <input type="checkbox" id="is_default" name="is_default" value="1" <?= (!empty($editRadio['is_default'])) ? 'checked' : '' ?> class="w-4 h-4 accent-amber-500">
                        <label for="is_default" class="text-xs font-bold text-amber-700 dark:text-amber-400">Varsayılan Başlangıç Radyosu</label>
                    </div>
                </div>

                <div class="pt-6 border-t border-slate-200 dark:border-emerald-800/60 flex justify-end gap-3">
                    <a href="radios.php" class="px-5 py-2.5 rounded-xl bg-slate-200 dark:bg-emerald-900 border border-slate-300 dark:border-emerald-700 text-xs font-bold text-slate-700 dark:text-emerald-300">İptal</a>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-amber-400 to-amber-600 text-emerald-950 font-extrabold text-xs shadow-md hover:scale-105 active:scale-95 transition-all">
                        Kaydet <i class="fas fa-save ml-1"></i>
                    </button>
                </div>

            </form>
        </div>

    <!-- Action: LIST RADIOS -->
    <?php else: ?>

        <div class="bg-white dark:bg-emerald-950/70 p-6 rounded-2xl border border-slate-200 dark:border-emerald-800/40 shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-emerald-800/60 text-emerald-800 dark:text-emerald-400 font-bold uppercase tracking-wider">
                            <th class="pb-3 px-3">Sıra</th>
                            <th class="pb-3 px-3">Kapak</th>
                            <th class="pb-3 px-3">Başlık</th>
                            <th class="pb-3 px-3">Kâri / Okuyan</th>
                            <th class="pb-3 px-3">Kategori</th>
                            <th class="pb-3 px-3 text-center">Favori</th>
                            <th class="pb-3 px-3 text-center">Durum</th>
                            <th class="pb-3 px-3 text-right">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-emerald-800/30">
                        <?php 
                        $adminStats = get_stats_data();
                        foreach ($radios as $r): 
                            $rFavs = $adminStats['channels'][$r['id']]['favorites'] ?? 0;
                        ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-emerald-900/30 transition-colors">
                                <td class="py-3 px-3 font-bold text-amber-600 dark:text-amber-400">#<?= $r['order'] ?? $r['id'] ?></td>
                                <td class="py-3 px-3">
                                    <?php
                                    $logoSrc = !empty($r['logo']) ? $r['logo'] : ($settings['default_cover_url'] ?? '');
                                    if (!empty($logoSrc) && strpos($logoSrc, 'http') !== 0 && strpos($logoSrc, '/') !== 0) {
                                        $logoSrc = '../' . $logoSrc;
                                    }
                                    ?>
                                    <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-emerald-950 p-0.5 border border-slate-200 dark:border-emerald-700/60 flex items-center justify-center overflow-hidden">
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
                                </td>
                                <td class="py-3 px-3 font-bold text-slate-900 dark:text-white">
                                    <?= sanitize($r['title']) ?>
                                    <?php if (!empty($r['is_default'])): ?>
                                        <span class="ml-2 px-1.5 py-0.5 rounded bg-amber-500/20 text-amber-700 dark:text-amber-300 text-[10px] font-bold">Varsayılan</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-3 text-slate-700 dark:text-emerald-200"><?= sanitize($r['reciter']) ?></td>
                                <td class="py-3 px-3 text-emerald-700 dark:text-emerald-400 font-semibold"><?= sanitize($r['category']) ?></td>
                                <td class="py-3 px-3 text-center">
                                    <span class="px-2.5 py-1 rounded-full bg-rose-500/10 dark:bg-rose-500/20 text-rose-600 dark:text-rose-300 font-bold text-[10px] inline-flex items-center gap-1">
                                        <i class="fas fa-heart text-rose-500"></i> <?= number_format($rFavs) ?>
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <?php if (!isset($r['is_active']) || $r['is_active']): ?>
                                        <span class="px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-700 dark:text-emerald-400 font-bold text-[10px]">Aktif</span>
                                    <?php else: ?>
                                        <span class="px-2 py-0.5 rounded-full bg-rose-500/20 text-rose-700 dark:text-rose-400 font-bold text-[10px]">Pasif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="radios.php?action=edit&id=<?= $r['id'] ?>" class="px-3 py-1 rounded-lg bg-slate-100 dark:bg-emerald-700/50 hover:bg-emerald-600 hover:text-white text-slate-700 dark:text-emerald-100 font-bold transition-all">
                                            Düzenle
                                        </a>

                                        <form method="POST" action="radios.php" onsubmit="return confirm('Bu istasyonu silmek istediğinize emin misiniz?');" class="inline">
                                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                            <input type="hidden" name="post_action" value="delete_radio">
                                            <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                            <button type="submit" class="px-3 py-1 rounded-lg bg-rose-500/10 dark:bg-rose-500/20 hover:bg-rose-600 text-rose-600 dark:text-rose-300 hover:text-white font-bold transition-all">
                                                Sil
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>
