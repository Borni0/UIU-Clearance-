<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/flash.php';
require_once __DIR__ . '/../config/db.php';

require_role_any(['admin', 'vc']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $me = current_user();
    $back = ($me && $me['role'] === 'vc') ? '/vc/user-management.php' : '/admin/user-management.php';
    header('Location: ' . $back);
    exit;
}
csrf_check();

$me = current_user();
if (!$me || !in_array($me['role'], ['admin', 'vc'], true)) {
    http_response_code(403);
    exit('403 — Forbidden.');
}

$full_name  = trim((string)($_POST['fullname']  ?? ''));
$email      = strtolower(trim((string)($_POST['email'] ?? '')));
$dept       = trim((string)($_POST['dept']     ?? ''));
$student_id = trim((string)($_POST['sid']      ?? ''));
$pw         = (string)($_POST['pw']  ?? '');
$pw2        = (string)($_POST['pw2'] ?? '');

$back = $me['role'] === 'vc' ? '/vc/user-management.php' : '/admin/user-management.php';

$errors = [];
if ($full_name === '' || strlen($full_name) > 120) $errors[] = 'Please enter the full name.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL))    $errors[] = 'Please enter a valid email.';
if ($dept === '')                                  $errors[] = 'Please choose a department.';
if ($student_id === '' || strlen($student_id) > 40) $errors[] = 'Please enter a student ID.';
if (strlen($pw) < 8)                               $errors[] = 'Password must be at least 8 characters.';
if ($pw !== $pw2)                                  $errors[] = 'Passwords do not match.';

if ($errors) {
    flash_bad(implode(' ', $errors));
    header('Location: ' . $back);
    exit;
}

try {
    $db = db();

    $dup = $db->prepare('SELECT id FROM users WHERE email = ? OR student_id = ? LIMIT 1');
    $dup->execute([$email, $student_id]);
    if ($dup->fetch()) {
        flash_bad('An account with that email or student ID already exists.');
        header('Location: ' . $back);
        exit;
    }

    $hash = password_hash($pw, PASSWORD_BCRYPT);

    $ins = $db->prepare(
        'INSERT INTO users (role, full_name, email, password, student_id, department, status)
         VALUES (?, ?, ?, ?, ?, ?, "active")'
    );
    $ins->execute(['student', $full_name, $email, $hash, $student_id, $dept]);

    flash_ok('Student account created for ' . $email . '. Temporary password: ' . $pw);
    header('Location: ' . $back);
    exit;
} catch (Throwable $e) {
    error_log('admin-create-student error: ' . $e->getMessage());
    flash_bad('Could not create the student account. Please try again.');
    header('Location: ' . $back);
    exit;
}
