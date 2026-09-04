<?php


declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/flash.php';
require_once __DIR__ . '/../includes/notifications.php';

require_role('student');
csrf_check();

$me = current_user();
if (!$me) {
    header('Location: /auth/login.php');
    exit;
}

$dept = (string)($_POST['dept'] ?? '');
$reason = trim((string)($_POST['reason'] ?? ''));

$allowed_depts = ['education', 'library', 'transport', 'medical', 'hostel'];
if (!in_array($dept, $allowed_depts, true)) {
    flash_bad('Invalid department.');
    header('Location: /student/apply-clearance.php');
    exit;
}

try {
    $stmt = db()->prepare(
        'INSERT INTO clearance_requests (student_id, department, status, reason)
         VALUES (?, ?, "pending", ?)
         ON DUPLICATE KEY UPDATE
             reason = VALUES(reason),
             status = "pending",
             submitted_at = NOW(),
             decided_at = NULL,
             decided_by = NULL'
    );
    $stmt->execute([(int)$me['id'], $dept, $reason !== '' ? $reason : null]);

    $dept_label = ucfirst($dept);
    notify(
        (int)$me['id'],
        'request_submitted',
        $dept_label . ' clearance submitted',
        'Your request was sent to the ' . $dept_label . ' department for review.',
        '/student/track-status.php'
    );

 
    $admin_stmt = db()->prepare(
        'SELECT id FROM users
         WHERE role = "admin"
           AND (LOWER(department) = ? OR LOWER(department) = ?)
           AND status = "active"'
    );
    $admin_stmt->execute([$dept, strtolower($dept_label)]);
    foreach ($admin_stmt->fetchAll() as $admin) {
        notify(
            (int)$admin['id'],
            'request_submitted',
            'New ' . $dept_label . ' clearance request',
            $me['full_name'] . ' submitted a ' . $dept_label . ' clearance request.',
            '/admin/admin-' . $dept . '.php'
        );
    }

    log_activity((int)$me['id'], 'clearance_submitted', 'clearance_request', null, $dept . ' clearance submitted');

    flash_ok($dept_label . ' clearance submitted. You will be notified once it is reviewed.');
} catch (Throwable $e) {
    error_log('submit-clearance error: ' . $e->getMessage());
    flash_bad('Could not submit your request. Please try again.');
}

header('Location: /student/apply-clearance.php?dept=' . urlencode($dept));
exit;
