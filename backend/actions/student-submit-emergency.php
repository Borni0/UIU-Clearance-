<?php


declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/flash.php';
require_once __DIR__ . '/../includes/upload.php';
require_once __DIR__ . '/../includes/notifications.php';

require_role('student');
csrf_check();

$me = current_user();
if (!$me) {
    header('Location: /auth/login.php');
    exit;
}

$title = trim((string)($_POST['title'] ?? ''));
$reason = trim((string)($_POST['reason'] ?? ''));
$required_by = trim((string)($_POST['required_by'] ?? ''));

if ($title === '' || $reason === '' || $required_by === '') {
    flash_bad('Please fill in title, reason, and required completion date.');
    header('Location: /student/emergency-request.php');
    exit;
}

$ts = strtotime($required_by);
if ($ts === false || $ts < strtotime('today')) {
    flash_bad('Required completion date must be today or later.');
    header('Location: /student/emergency-request.php');
    exit;
}

$document_id = null;
if (!empty($_FILES['file']) && is_array($_FILES['file']) && ($_FILES['file']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
    try {
        $r = save_uploaded_file($_FILES['file'], (int)$me['id'], 'other');
        $document_id = (int)$r['doc_id'];
    } catch (RuntimeException $e) {
        flash_bad('Document upload failed: ' . $e->getMessage());
        header('Location: /student/emergency-request.php');
        exit;
    }
}

try {
    $stmt = db()->prepare(
        'INSERT INTO emergency_requests (student_id, title, reason, required_by, document_id)
         VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([(int)$me['id'], $title, $reason, date('Y-m-d', $ts), $document_id]);
    $em_id = (int) db()->lastInsertId();

    // Notify the student.
    notify(
        (int)$me['id'],
        'emergency',
        'Emergency request submitted',
        'Your request "' . $title . '" has been sent to admins and the Vice Chancellor.',
        '/student/emergency-page.php'
    );

    $recipients = db()->query(
        "SELECT id FROM users WHERE role IN ('admin', 'vc') AND status = 'active'"
    )->fetchAll();
    foreach ($recipients as $r) {
        notify(
            (int)$r['id'],
            'emergency',
            'New emergency request',
            $me['full_name'] . ' submitted: ' . $title,
            '/admin/emergency-inbox.php'
        );
    }

    log_activity(
        (int)$me['id'],
        'emergency_submitted',
        'emergency_request',
        $em_id,
        $title
    );

    flash_ok('Emergency request submitted. Admins have been notified.');
} catch (Throwable $e) {
    error_log('submit-emergency error: ' . $e->getMessage());
    flash_bad('Could not submit your emergency request. Please try again.');
}

header('Location: /student/emergency-page.php');
exit;
