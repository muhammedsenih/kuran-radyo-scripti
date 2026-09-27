<?php
/**
 * Canlı Kur'an-ı Kerim Radyosu & Tilavet Portalı
 * Resmî MP3Quran API Doğrulanmış Canlı Yayın Bağlantıları
 */

error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', '0');

if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

define('BASE_DIR', __DIR__);
define('DATA_FILE', BASE_DIR . '/data/data.json');

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * Varsayılan Veritabanı Yapısı
 */
function get_default_data(): array {
    return [
        'settings' => [
            'site_title' => "Canlı Kur'an-ı Kerim Radyosu & Tilavet Portalı",
            'site_description' => "7/24 Kesintisiz Canlı Kur'an-ı Kerim radyoları, dünyaca ünlü kâri ve hafızların mealli ve tecvidli tilavet yayınları.",
            'site_keywords' => "kuran radyosu, canlı kuran dinle, mealli kuran radyosu, diyanet kuran radyosu, tilavet, hafızlar, kuran dinle 7/24",
            'site_url' => '',
            'logo_url' => '',
            'favicon_url' => '',
            'default_cover_url' => '',
            'contact_email' => 'info@kuranradyo.com',
            'social_facebook' => 'https://facebook.com',
            'social_twitter' => 'https://twitter.com',
            'social_instagram' => 'https://instagram.com',
            'social_whatsapp' => 'https://whatsapp.com',
            'header_code' => '',
            'footer_code' => '',
            'canonical_url' => '',
            'admin_username' => 'admin',
            'admin_password' => password_hash('admin123', PASSWORD_DEFAULT)
        ],
        'radios' => [
            [
                'id' => 1,
                'title' => "Karma Kur'an Radyosu",
                'reciter' => "Diyanet & Seçkin Kâriler",
                'category' => "Diyanet Yayınları",
                'stream_url' => "https://backup.qurango.net/radio/mix",
                'backup_url' => "https://stream.radiojar.com/0tpy1h0kxtzuv",
                'logo' => "",
                'is_default' => true,
                'is_active' => true,
                'order' => 1,
                'description' => "7/24 kesintisiz yüksek kaliteli canlı Kur'an-ı Kerim tilaveti."
            ],
            [
                'id' => 2,
                'title' => "7/24 Kesintisiz Hatim (Tarteel)",
                'reciter' => "Seçkin Hafızlar",
                'category' => "7/24 Kesintisiz Hatim",
                'stream_url' => "https://backup.qurango.net/radio/tarateel",
                'backup_url' => "https://backup.qurango.net/radio/mix",
                'logo' => "",
                'is_default' => false,
                'is_active' => true,
                'order' => 2,
                'description' => "Gece gündüz kesintisiz olarak hatim sırasına göre tilavet edilen canlı radyo akışı."
            ],
            [
                'id' => 3,
                'title' => "Türkçe Mealli Kur'an-ı Kerim",
                'reciter' => "Arapça Tilavet & Türkçe Seslendirme",
                'category' => "Türkçe Mealli Yayınlar",
                'stream_url' => "https://backup.qurango.net/radio/translation_quran_turkish",
                'backup_url' => "https://stream.radiojar.com/0tpy1h0kxtzuv",
                'logo' => "",
                'is_default' => false,
                'is_active' => true,
                'order' => 3,
                'description' => "Arapça orijinal ayet tilaveti ve hemen ardından Türkçe meali canlı yayını."
            ],
            [
                'id' => 4,
                'title' => "Saad Al-Ghamdi (Sa'd el-Gamidi)",
                'reciter' => "Şeyh Sa'd el-Gamidi",
                'category' => "Popüler Hafızlar",
                'stream_url' => "https://backup.qurango.net/radio/saad_alghamdi",
                'backup_url' => "https://backup.qurango.net/radio/s_gmd",
                'logo' => "",
                'is_default' => false,
                'is_active' => true,
                'order' => 4,
                'description' => "Yüreğe dokunan makamıyla Şeyh Sa'd el-Gamidi'den 7/24 canlı Kur'an-ı Kerim."
            ],
            [
                'id' => 5,
                'title' => "Abdulbasit Abdussamed (Tecvidli)",
                'reciter' => "Şeyh Abdulbasit Abdussamed",
                'category' => "Popüler Hafızlar",
                'stream_url' => "https://backup.qurango.net/radio/abdulbasit_abdulsamad_mojawwad",
                'backup_url' => "https://backup.qurango.net/radio/abdulbasit_abdulsamad",
                'logo' => "",
                'is_default' => false,
                'is_active' => true,
                'order' => 5,
                'description' => "Efsanevi Mısırlı Kâri Şeyh Abdulbasit Abdussamed'in tecvidli tilavet radyosu."
            ],
            [
                'id' => 6,
                'title' => "Mishary Rashid Alafasy",
                'reciter' => "Şeyh Mişari Raşid el-Afasi",
                'category' => "Popüler Hafızlar",
                'stream_url' => "https://backup.qurango.net/radio/mishary_alafasi",
                'backup_url' => "https://backup.qurango.net/radio/mix",
                'logo' => "",
                'is_default' => false,
                'is_active' => true,
                'order' => 6,
                'description' => "Dünyaca ünlü Kuveytli Hafız Mishary Rashid Alafasy'nin 7/24 canlı Kur'an yayını."
            ],
            [
                'id' => 7,
                'title' => "Maher Al-Muaiqly",
                'reciter' => "Şeyh Mahir el-Muaykili (Mekke İmamı)",
                'category' => "Popüler Hafızlar",
                'stream_url' => "https://backup.qurango.net/radio/maher",
                'backup_url' => "https://backup.qurango.net/radio/mix",
                'logo' => "",
                'is_default' => false,
                'is_active' => true,
                'order' => 7,
                'description' => "Mescid-i Haram (Kâbe) İmamı Şeyh Mahir el-Muaykili'nin muazzam tilavet yayını."
            ],
            [
                'id' => 8,
                'title' => "Yasser Al-Dosari",
                'reciter' => "Şeyh Yasser el-Dosari (Mekke İmamı)",
                'category' => "Popüler Hafızlar",
                'stream_url' => "https://backup.qurango.net/radio/yasser_aldosari",
                'backup_url' => "https://backup.qurango.net/radio/saud_alshuraim",
                'logo' => "",
                'is_default' => false,
                'is_active' => true,
                'order' => 8,
                'description' => "Mescid-i Haram İmamı Şeyh Yasser el-Dosari'nin eşsiz canlı tilavet yayını."
            ]
        ]
    ];
}

/**
 * JSON Veritabanından Verileri Oku
 */
function get_data(): array {
    $dataDir = dirname(DATA_FILE);
    if (!is_dir($dataDir)) {
        @mkdir($dataDir, 0777, true);
    }

    if (!file_exists(DATA_FILE)) {
        $defaultData = get_default_data();
        save_data($defaultData);
        return $defaultData;
    }

    $jsonContent = @file_get_contents(DATA_FILE);
    if (!$jsonContent) {
        $defaultData = get_default_data();
        save_data($defaultData);
        return $defaultData;
    }

    $decoded = json_decode($jsonContent, true);

    if (!is_array($decoded) || empty($decoded['settings']) || empty($decoded['radios'])) {
        $defaultData = get_default_data();
        save_data($defaultData);
        return $defaultData;
    }

    $cleaned = clean_raw_strings($decoded);
    if (isset($cleaned['radios']) && is_array($cleaned['radios'])) {
        foreach ($cleaned['radios'] as &$r) {
            if (is_array($r) && empty($r['slug']) && !empty($r['title'])) {
                $r['slug'] = slugify($r['title']);
            }
        }
    }
    return $cleaned;
}

/**
 * SEO Uyumlu URL Slug Oluşturucu (Türkçe Karakter Destekli)
 */
function slugify(string $text): string {
    $text = trim($text);
    if (empty($text)) return 'radyo';
    $tr = ['ç','Ç','ğ','Ğ','ı','I','İ','ö','Ö','ş','Ş','ü','Ü'];
    $en = ['c','c','g','g','i','i','i','o','o','s','s','u','u'];
    $text = str_replace($tr, $en, $text);
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9\-]/', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-');
}

/**
 * JSON Veritabanına Verileri Kaydet
 */
function save_data(array $data): bool {
    $dataDir = dirname(DATA_FILE);
    if (!is_dir($dataDir)) {
        @mkdir($dataDir, 0777, true);
    }
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    return @file_put_contents(DATA_FILE, $json, LOCK_EX) !== false;
}

/**
 * HTML Entity çift kodlamayı önleyen ve ham metinleri temizleyen yardımcılar
 */
function clean_raw_strings($input) {
    if (is_array($input)) {
        foreach ($input as $k => $v) {
            $input[$k] = clean_raw_strings($v);
        }
        return $input;
    }
    if (is_string($input)) {
        $decoded = $input;
        while (preg_match('/&(#\d+|#[xX][0-9a-fA-F]+|[a-zA-Z]+);/', $decoded)) {
            $prev = $decoded;
            $decoded = html_entity_decode($decoded, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if ($prev === $decoded) break;
        }
        return trim($decoded);
    }
    return $input;
}

function sanitize_input($input): string {
    if ($input === null || $input === false) return '';
    return clean_raw_strings((string)$input);
}

function sanitize($input): string {
    if ($input === null || $input === false) {
        return '';
    }
    $decoded = clean_raw_strings((string)$input);
    return htmlspecialchars($decoded, ENT_QUOTES, 'UTF-8');
}

function check_admin_password(string $input_password, string $stored_hash): bool {
    if (empty($input_password) || empty($stored_hash)) {
        return false;
    }
    if (@password_verify($input_password, $stored_hash)) {
        return true;
    }
    // Fallback for unhashed legacy string
    if (!str_starts_with($stored_hash, '$2y$') && !str_starts_with($stored_hash, '$2a$')) {
        if (hash_equals($stored_hash, $input_password)) {
            return true;
        }
    }
    return false;
}

function is_logged_in(): bool {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        return false;
    }
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
        $data = get_data();
        $currentHash = $data['settings']['admin_password'] ?? '';
        $sessionHash = $_SESSION['admin_pass_hash'] ?? '';
        if (empty($sessionHash) || $sessionHash !== md5($currentHash)) {
            unset($_SESSION['admin_logged_in'], $_SESSION['admin_user'], $_SESSION['user_role'], $_SESSION['admin_pass_hash']);
            return false;
        }
    }
    return true;
}

function is_demo(): bool {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'demo';
}

function require_login(): void {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function verify_csrf($token): bool {
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], (string)$token);
}

function get_site_url(array $settings = []): string {
    if (!empty($settings['canonical_url'])) {
        return rtrim($settings['canonical_url'], '/');
    }
    if (!empty($settings['site_url'])) {
        return rtrim($settings['site_url'], '/');
    }
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $protocol . "://" . $host;
}

define('STATS_FILE', BASE_DIR . '/data/stats.json');

function get_client_country(): string {
    if (!empty($_SERVER['HTTP_CF_IPCOUNTRY'])) {
        return strtoupper($_SERVER['HTTP_CF_IPCOUNTRY']);
    }
    if (!empty($_SERVER['HTTP_X_COUNTRY_CODE'])) {
        return strtoupper($_SERVER['HTTP_X_COUNTRY_CODE']);
    }
    return 'TR';
}

function get_stats_data(): array {
    if (!file_exists(STATS_FILE)) {
        return [
            'total_plays' => 0,
            'today_plays' => 0,
            'daily' => [],
            'channels' => [],
            'countries' => [],
            'recent_pings' => []
        ];
    }
    $content = @file_get_contents(STATS_FILE);
    $decoded = json_decode($content, true);
    return is_array($decoded) ? $decoded : [
        'total_plays' => 0,
        'today_plays' => 0,
        'daily' => [],
        'channels' => [],
        'countries' => [],
        'recent_pings' => []
    ];
}

function record_play_stat(int $radioId, string $radioTitle = ''): bool {
    $stats = get_stats_data();
    $today = date('Y-m-d');
    $now = time();
    $country = get_client_country();

    $stats['total_plays'] = ($stats['total_plays'] ?? 0) + 1;
    $stats['daily'][$today] = ($stats['daily'][$today] ?? 0) + 1;

    if (!isset($stats['channels'][$radioId])) {
        $stats['channels'][$radioId] = [
            'id' => $radioId,
            'title' => $radioTitle,
            'count' => 0
        ];
    }
    $stats['channels'][$radioId]['count']++;
    if (!empty($radioTitle)) {
        $stats['channels'][$radioId]['title'] = $radioTitle;
    }

    $stats['countries'][$country] = ($stats['countries'][$country] ?? 0) + 1;

    $visitorHash = md5(($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1') . ($_SERVER['HTTP_USER_AGENT'] ?? ''));
    if (!isset($stats['recent_pings']) || !is_array($stats['recent_pings'])) {
        $stats['recent_pings'] = [];
    }

    $stats['recent_pings'][$visitorHash] = [
        'time' => $now,
        'radio_id' => $radioId,
        'title' => $radioTitle,
        'country' => $country
    ];

    foreach ($stats['recent_pings'] as $hash => $ping) {
        if ($now - ($ping['time'] ?? 0) > 600) {
            unset($stats['recent_pings'][$hash]);
        }
    }

    if (count($stats['daily']) > 30) {
        ksort($stats['daily']);
        $stats['daily'] = array_slice($stats['daily'], -30, 30, true);
    }

    $json = json_encode($stats, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return @file_put_contents(STATS_FILE, $json, LOCK_EX) !== false;
}

function record_favorite_stat(int $radioId, bool $isFavorite, string $radioTitle = ''): bool {
    $stats = get_stats_data();

    if (!isset($stats['channels'][$radioId])) {
        $stats['channels'][$radioId] = [
            'id' => $radioId,
            'title' => $radioTitle,
            'count' => 0,
            'favorites' => 0
        ];
    }

    if (!isset($stats['channels'][$radioId]['favorites'])) {
        $stats['channels'][$radioId]['favorites'] = 0;
    }

    if ($isFavorite) {
        $stats['channels'][$radioId]['favorites']++;
        $stats['total_favorites'] = ($stats['total_favorites'] ?? 0) + 1;
    } else {
        $stats['channels'][$radioId]['favorites'] = max(0, $stats['channels'][$radioId]['favorites'] - 1);
        $stats['total_favorites'] = max(0, ($stats['total_favorites'] ?? 0) - 1);
    }

    if (!empty($radioTitle)) {
        $stats['channels'][$radioId]['title'] = $radioTitle;
    }

    $json = json_encode($stats, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return @file_put_contents(STATS_FILE, $json, LOCK_EX) !== false;
}

function get_country_name(string $code): string {
    $countries = [
        'TR' => 'Türkiye 🇹🇷',
        'DE' => 'Almanya 🇩🇪',
        'FR' => 'Fransa 🇫🇷',
        'NL' => 'Hollanda 🇳🇱',
        'GB' => 'İngiltere 🇬🇧',
        'US' => 'Amerika 🇺🇸',
        'AT' => 'Avusturya 🇦🇹',
        'CH' => 'İsviçre 🇨🇭',
        'BE' => 'Belçika 🇧🇪',
        'AZ' => 'Azerbaycan 🇦🇿',
        'SA' => 'Suudi Arabistan 🇸🇦',
        'AE' => 'BİRLEŞİK ARAP EMİRLİKLERİ 🇦🇪',
        'EG' => 'Mısır 🇪🇬',
        'RU' => 'Rusya 🇷🇺',
        'CA' => 'Kanada 🇨🇦'
    ];
    return $countries[strtoupper($code)] ?? strtoupper($code);
}
