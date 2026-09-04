<?php


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

$request_id = (int)($_POST['request_id'] ?? 0);
$status = (string)($_POST['status'] ?? '');
$remark = trim((string)($_POST['remark'] ?? ''));

$allowed = ['pending', 'approved', 'hold', 'rejected', 'blocked'];
if ($request_id <= 0 || !in_array($status, $allowed, true)) {
    flash_bad('Invalid request.');
    header('Location: ' . ($_POST['back'] ?? '/admin/dashboard.php'));
    exit;
}

try {
    $stmt = db()->prepare(
        'SELECT cr.*, u.full_name AS student_name, u.email AS student_email
         FROM clearance_requests cr
         JOIN users u ON u.id = cr.student_id
         WHERE cr.id = ? LIMIT 1'
    );
    $stmt->execute([$request_id]);
    $req = $stmt->fetch();

    if (!$req) {
        flash_bad('Clearance request not found.');
        header('Location: ' . ($_POST['back'] ?? '/admin/dashboard.php'));
        exit;
    }

    $upd = db()->prepare(
        'UPDATE clearance_requests
         SET status = ?, decided_at = NOW(), decided_by = ?
         WHERE id = ?'
    );
    $upd->execute([$status, (int)$me['id'], $request_id]);

    if ($remark !== '') {
        $rm = db()->prepare(
            'INSERT INTO clearance_remarks (request_id, author_id, body) VALUES (?, ?, ?)'
        );
        $rm->execute([$request_id, (int)$me['id'], $remark]);
    }

    // Notify the student.
    $labels = [
        'approved' => 'approved',
        'rejected' => 'rejected',
        'hold'     => 'put on hold',
        'blocked'  => 'blocked',
        'pending'  => 'reset to pending',
    ];
    $kind = in_array($status, ['approved', 'rejected'], true) ? $status : 'status_update';
    $dept_label = ucfirst((string)$req['department']);
    notify(
        (int)$req['student_id'],
        $kind,
        $dept_label . ' clearance ' . $labels[$status],
        $remark !== '' ? $remark : null,
        '/student/track-status.php'
    );
    if ($remark !== '') {
        notify(
            (int)$req['student_id'],
            'admin_remark',
            'New remark on ' . $dept_label . ' clearance',
            $remark,
            '/student/track-status.php'
        );
    }

    log_activity(
        (int)$me['id'],
        'clearance_' . $status,
        'clearance_request',
        $request_id,
        $dept_label . ' clearance ' . $labels[$status] . ' for ' . $req['student_name']
    );

    flash_ok(ucfirst($labels[$status]) . ' successfully.');
} catch (Throwable $e) {
    error_log('admin-update-clearance error: ' . $e->getMessage());
    flash_bad('Could not update the request. Please try again.');
}

$back = $_POST['back'] ?? '/admin/dashboard.php';
header('Location: ' . $back);
exit;
