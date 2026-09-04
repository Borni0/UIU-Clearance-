<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/flash.php';
require_once __DIR__ . '/../config/db.php';

require_role_any(['admin', 'vc']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /auth/login.php');
    exit;
}
csrf_check();

$me  = current_user();
$id  = (int)($_POST['id'] ?? 0);
$back = $me['role'] === 'vc' ? '/vc/user-management.php' : '/admin/user-management.php';

if ($id <= 0) {
    flash_bad('Invalid user id.');
    header('Location: ' . $back);
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
        flash_bad('You cannot delete your own account.');
        header('Location: ' . $back);
        exit;
    }

    if ($me['role'] === 'admin' && $target['role'] !== 'student') {
        flash_bad('Admins may only delete student accounts.');
        header('Location: ' . $back);
        exit;
    }

    if ($me['role'] === 'vc' && $target['role'] === 'vc') {
        // Count remaining active VCs to prevent deleting the last one.
        $count = (int)$db->query("SELECT COUNT(*) FROM users WHERE role = 'vc' AND status = 'active'")->fetchColumn();
        if ($count <= 1) {
            flash_bad('You cannot delete the last active Vice Chancellor account.');
            header('Location: ' . $back);
            exit;
        }
    }

    $db->beginTransaction();
    try {
        $del = $db->prepare('DELETE FROM users WHERE id = ?');
        $del->execute([$id]);
        $db->commit();
    } catch (Throwable $e) {
        $db->rollBack();
        throw $e;
    }

    // Best-effort cleanup of the user's upload directory on disk.
    $user_dir = realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $id;
    if ($user_dir && is_dir($user_dir)) {
        foreach (glob($user_dir . '/{,.}*', GLOB_BRACE) as $f) {
            if (basename($f) === '.' || basename($f) === '..') continue;
            @is_dir($f) ? @rmdir($f) : @unlink($f);
        }
        @rmdir($user_dir);
    }

    flash_ok('Deleted user ' . $target['email'] . ' (role: ' . $target['role'] . ').');
    header('Location: ' . $back);
    exit;
} catch (Throwable $e) {
    error_log('admin-delete-user error: ' . $e->getMessage());
    flash_bad('Could not delete the user. Please try again.');
    header('Location: ' . $back);
    exit;
}
