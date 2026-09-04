<?php


declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/flash.php';
require_once __DIR__ . '/../includes/notifications.php';

require_role('vc');
csrf_check();

$me = current_user();
if (!$me) {
    header('Location: /auth/login.php');
    exit;
}

$student_id = (int)($_POST['student_id'] ?? 0);
if ($student_id <= 0) {
    flash_bad('Invalid student.');
    header('Location: ' . ($_POST['back'] ?? '/vc/dashboard.php'));
    exit;
}

try {
    $stmt = db()->prepare('SELECT * FROM users WHERE id = ? AND role = "student" LIMIT 1');
    $stmt->execute([$student_id]);
    $student = $stmt->fetch();
    if (!$student) {
        flash_bad('Student not found.');
        header('Location: ' . ($_POST['back'] ?? '/vc/dashboard.php'));
        exit;
    }

    // Check all 5 departments are approved.
    $status_stmt = db()->prepare(
        "SELECT department, status FROM clearance_requests WHERE student_id = ?"
    );
    $status_stmt->execute([$student_id]);
    $rows = $status_stmt->fetchAll();
    $by_dept = [];
    foreach ($rows as $r) {
        $by_dept[$r['department']] = $r['status'];
    }
    $missing = [];
    foreach (['education', 'library', 'transport', 'medical', 'hostel'] as $d) {
        if (($by_dept[$d] ?? null) !== 'approved') {
            $missing[] = $d;
        }
    }
    if ($missing) {
        flash_bad('Cannot issue certificate. Pending departments: ' . implode(', ', $missing) . '.');
        header('Location: ' . ($_POST['back'] ?? '/vc/dashboard.php'));
        exit;
    }

    // Already issued?
    $exists = db()->prepare('SELECT id, serial FROM certificates WHERE student_id = ? LIMIT 1');
    $exists->execute([$student_id]);
    $existing = $exists->fetch();
    if ($existing) {
        flash_warn('Certificate already issued for this student. Serial: ' . $existing['serial']);
        header('Location: ' . ($_POST['back'] ?? '/vc/dashboard.php'));
        exit;
    }

    $serial = 'CLR-' . date('Y') . '-' . str_pad((string)$student_id, 5, '0', STR_PAD_LEFT)
            . '-' . strtoupper(bin2hex(random_bytes(2)));

    $ins = db()->prepare(
        'INSERT INTO certificates (student_id, issued_by, serial, note)
         VALUES (?, ?, ?, ?)'
    );
    $ins->execute([$student_id, (int)$me['id'], $serial, 'Issued after all departmental clearances approved.']);

    notify(
        (int)$student_id,
        'approval',
        'Clearance certificate issued',
        'Your clearance certificate (' . $serial . ') has been issued by the Vice Chancellor.',
        '/student/track-status.php'
    );

    log_activity(
        (int)$me['id'],
        'certificate_issued',
        'certificate',
        $student_id,
        $serial . ' issued to ' . $student['full_name']
    );

    flash_ok('Certificate issued. Serial: ' . $serial);
} catch (Throwable $e) {
    error_log('issue-certificate error: ' . $e->getMessage());
    flash_bad('Could not issue the certificate.');
}

$back = $_POST['back'] ?? '/vc/dashboard.php';
header('Location: ' . $back);
exit;
