<?php
/**

 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/flash.php';
require_once __DIR__ . '/../includes/notifications.php';

require_role_any(['admin', 'vc']);
csrf_check();

$me = current_user();
if (!$me) {
    header('Location: /auth/login.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$decision = (string)($_POST['decision'] ?? '');
$remark = trim((string)($_POST['remark'] ?? ''));

if ($id <= 0 || !in_array($decision, ['approved', 'rejected'], true)) {
    flash_bad('Invalid request.');
    header('Location: ' . ($_POST['back'] ?? '/admin/dashboard.php'));
    exit;
}

try {
    $stmt = db()->prepare('SELECT * FROM emergency_requests WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $em = $stmt->fetch();
    if (!$em) {
        flash_bad('Emergency request not found.');
        header('Location: ' . ($_POST['back'] ?? '/admin/dashboard.php'));
        exit;
    }

    $upd = db()->prepare(
        'UPDATE emergency_requests
         SET status = ?, admin_remark = ?, decided_by = ?, decided_at = NOW()
         WHERE id = ?'
    );
    $upd->execute([$decision, $remark !== '' ? $remark : null, (int)$me['id'], $id]);

    $kind = $decision === 'approved' ? 'approval' : 'rejection';
    notify(
        (int)$em['student_id'],
        $kind,
        'Emergency request ' . $decision,
        $remark !== '' ? $remark : ('Your emergency request "' . $em['title'] . '" was ' . $decision . '.'),
        '/student/emergency-page.php'
    );

    log_activity(
        (int)$me['id'],
        'emergency_' . $decision,
        'emergency_request',
        $id,
        $em['title']
    );

    flash_ok('Emergency request ' . $decision . '.');
} catch (Throwable $e) {
    error_log('update-emergency error: ' . $e->getMessage());
    flash_bad('Could not update the emergency request.');
}

$back = $_POST['back'] ?? '/admin/dashboard.php';
header('Location: ' . $back);
exit;
