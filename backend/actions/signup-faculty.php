<?php


declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/flash.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /auth/signup-faculty.php');
    exit;
}

csrf_check();

$full_name   = trim((string)($_POST['fullname'] ?? ''));
$email       = strtolower(trim((string)($_POST['email'] ?? '')));
$dept        = trim((string)($_POST['dept'] ?? ''));
$employee_id = trim((string)($_POST['fid'] ?? ''));
$pw          = (string)($_POST['pw']  ?? '');
$pw2         = (string)($_POST['pw2'] ?? '');

$back = '/auth/signup-faculty.php';

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
if ($employee_id === '' || strlen($employee_id) > 40) {
    $errors[] = 'Please enter your employee ID.';
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

    $dup = $db->prepare('SELECT id FROM users WHERE email = ? OR employee_id = ? LIMIT 1');
    $dup->execute([$email, $employee_id]);
    if ($dup->fetch()) {
        flash_bad('An account with that email or employee ID already exists.');
        header('Location: ' . $back);
        exit;
    }

    $hash = password_hash($pw, PASSWORD_BCRYPT);

    $ins = $db->prepare(
        'INSERT INTO users (role, full_name, email, password, employee_id, department, status)
         VALUES (?, ?, ?, ?, ?, ?, "active")'
    );
    $ins->execute(['admin', $full_name, $email, $hash, $employee_id, $dept]);

    $id = (int) $db->lastInsertId();

    login_user([
        'id'        => $id,
        'role'      => 'admin',
        'full_name' => $full_name,
        'email'     => $email,
    ]);

    flash_ok('Welcome, ' . $full_name . '! Your admin account is ready.');
    header('Location: /admin/dashboard.php');
    exit;
} catch (Throwable $e) {
    error_log('signup-faculty error: ' . $e->getMessage());
    flash_bad('Could not create your account. Please try again.');
    header('Location: ' . $back);
    exit;
}
