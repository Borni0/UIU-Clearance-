<?php


declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/flash.php';

require_login();

$me = current_user();
if (!$me) {
    header('Location: /auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /index.php');
    exit;
}
csrf_check();

$id = (string)($_POST['id'] ?? '');

try {
    if ($id === 'all') {
        $stmt = db()->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ?');
        $stmt->execute([(int)$me['id']]);
    } else {
        $nid = (int)$id;
        if ($nid > 0) {
            $stmt = db()->prepare('UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?');
            $stmt->execute([$nid, (int)$me['id']]);
        }
    }
} catch (Throwable $e) {
    error_log('mark-notification-read error: ' . $e->getMessage());
}

$back = $_POST['back'] ?? '/index.php';
header('Location: ' . $back);
exit;
