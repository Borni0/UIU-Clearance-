<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/flash.php';
require_once __DIR__ . '/../config/db.php';

require_role('vc');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /vc/user-management.php');
    exit;
}
csrf_check();

$me = current_user();
$id = (int)($_POST['id'] ?? 0);
$back = '/vc/user-management.php';

if ($id <= 0) {
    flash_bad('Invalid user id.');
    header('Location: ' . $back);
    exit;
}

$pw  = (string)($_POST['pw']  ?? '');
$pw2 = (string)($_POST['pw2'] ?? '');

if (strlen($pw) < 8) {
    flash_bad('Password must be at least 8 characters.');
    header('Location: /vc/user-management.php');
    exit;
}
if ($pw !== $pw2) {
    flash_bad('Passwords do not match.');
    header('Location: /vc/user-management.php');
    exit;
}

try {
    $db  = db();
    $sel = $db->prepare('SELECT id, role, email FROM users WHERE id = ? LIMIT 1');
    $sel->execute([$id]);
    $target = $sel->fetch();

    if (!$target) {
        flash_bad('User not found.');
        header('Location: ' . $back);
        exit;
    }

    if ((int)$target['id'] === (int)$me['id']) {
        flash_bad('You cannot reset your own password through this form.');
        header('Location: ' . $back);
        exit;
    }

    if ($target['role'] === 'vc') {
        $count = (int)$db->query("SELECT COUNT(*) FROM users WHERE role = 'vc' AND status = 'active'")->fetchColumn();
        if ($count <= 1) {
            flash_bad('You cannot reset the last active Vice Chancellor account\'s password.');
            header('Location: ' . $back);
            exit;
        }
    }

    $hash = password_hash($pw, PASSWORD_BCRYPT);
    $upd  = $db->prepare('UPDATE users SET password = ? WHERE id = ?');
    $upd->execute([$hash, $id]);

    flash_ok('Password for ' . $target['email'] . ' reset. New temporary password: ' . $pw);
    header('Location: ' . $back);
    exit;
} catch (Throwable $e) {
    error_log('vc-reset-password error: ' . $e->getMessage());
    flash_bad('Could not reset the password. Please try again.');
    header('Location: ' . $back);
    exit;
}
