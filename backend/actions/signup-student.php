<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/flash.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /auth/signup-student.php');
    exit;
}

csrf_check();

$full_name  = trim((string)($_POST['fullname'] ?? ''));
$email      = strtolower(trim((string)($_POST['email'] ?? '')));
$dept       = trim((string)($_POST['dept'] ?? ''));
$student_id = trim((string)($_POST['sid'] ?? ''));
$pw         = (string)($_POST['pw']  ?? '');
$pw2        = (string)($_POST['pw2'] ?? '');

$back = '/auth/signup-student.php';

// Field validation.
$errors = [];
if ($full_name === '' || strlen($full_name) > 120) {
    $errors[] = 'Please enter your full name.';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address.';
}
if ($dept === '') {
    $errors[] = 'Please choose a department.';
}
if ($student_id === '' || strlen($student_id) > 40) {
    $errors[] = 'Please enter your student ID.';
}
if (strlen($pw) < 8) {
    $errors[] = 'Password must be at least 8 characters.';
}
if ($pw !== $pw2) {
    $errors[] = 'Passwords do not match.';
}

if ($errors) {
    flash_bad(implode(' ', $errors));
    header('Location: ' . $back);
    exit;
}

try {
    $db = db();

    // Uniqueness checks (give a friendly error rather than a SQL exception).
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

    $id = (int) $db->lastInsertId();

    login_user([
        'id'        => $id,
        'role'      => 'student',
        'full_name' => $full_name,
        'email'     => $email,
    ]);

    flash_ok('Welcome, ' . $full_name . '! Your student account is ready.');
    header('Location: /student/dashboard.php');
    exit;
} catch (Throwable $e) {
    error_log('signup-student error: ' . $e->getMessage());
    flash_bad('Could not create your account. Please try again.');
    header('Location: ' . $back);
    exit;
}
