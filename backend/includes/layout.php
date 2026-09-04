<?php


declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/flash.php';

function uiu_unread_count(int $user_id): int
{
    static $cache = [];
    if (isset($cache[$user_id])) {
        return $cache[$user_id];
    }
    try {
        $stmt = db()->prepare('SELECT COUNT(*) AS n FROM notifications WHERE user_id = ? AND is_read = 0');
        $stmt->execute([$user_id]);
        $cache[$user_id] = (int) $stmt->fetchColumn();
    } catch (Throwable $e) {
        $cache[$user_id] = 0;
    }
    return $cache[$user_id];
}

function uiu_status_badge(string $status): string
{
    $map = [
        'pending'  => ['warn', 'Pending'],
        'approved' => ['ok',   'Approved'],
        'rejected' => ['bad',  'Rejected'],
        'hold'     => ['warn', 'On hold'],
        'blocked'  => ['bad',  'Blocked'],
    ];
    $s = $map[$status] ?? ['info', ucfirst($status)];
    return '<span class="badge ' . htmlspecialchars($s[0]) . '">' . htmlspecialchars($s[1]) . '</span>';
}


function uiu_flash_banner(): string
{
    $f = flash_get();
    if (!$f) {
        return '';
    }
    $type = htmlspecialchars($f['type']);
    $msg  = htmlspecialchars($f['message']);
    $icon = $f['type'] === 'ok' ? 'fa-circle-check' : 'fa-circle-exclamation';
    return '<div class="alert ' . $type . '" role="alert">'
         .   '<i class="fa-solid ' . $icon . '"></i>'
         .   '<span>' . $msg . '</span>'
         . '</div>';
}
