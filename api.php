<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'ping_play') {
    $radioId = intval($_GET['radio_id'] ?? $_POST['radio_id'] ?? 0);
    $radioTitle = sanitize($_GET['title'] ?? $_POST['title'] ?? '');
    
    if ($radioId > 0) {
        record_play_stat($radioId, $radioTitle);
        echo json_encode(['success' => true]);
        exit;
    }
}

if ($action === 'favorite_toggle') {
    $radioId = intval($_GET['radio_id'] ?? $_POST['radio_id'] ?? 0);
    $radioTitle = sanitize($_GET['title'] ?? $_POST['title'] ?? '');
    $isFavorite = !empty($_GET['is_favorite']) || !empty($_POST['is_favorite']);

    if ($radioId > 0) {
        record_favorite_stat($radioId, $isFavorite, $radioTitle);
        echo json_encode(['success' => true]);
        exit;
    }
}

if ($action === 'get_stats') {
    if (is_logged_in()) {
        $stats = get_stats_data();
        echo json_encode(['success' => true, 'stats' => $stats]);
        exit;
    }
}

echo json_encode(['success' => false, 'error' => 'Invalid endpoint']);
