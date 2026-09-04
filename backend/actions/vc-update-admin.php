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

$full_name   = trim((string)($_POST['fullname']  ?? ''));
$email       = strtolower(trim((string)($_POST['email'] ?? '')));
$dept        = trim((string)($_POST['dept']     ?? ''));
$employee_id = trim((string)($_POST['eid']      ?? ''));
$status      = (string)($_POST['status']         ?? 'active');

$errors = [];
if ($full_name === '' || strlen($full_name) > 120)  $errors[] = 'Please enter the full name.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL))     $errors[] = 'Please enter a valid email.';
if ($dept === '')                                   $errors[] = 'Please enter a department.';
if ($employee_id === '' || strlen($employee_id) > 40) $errors[] = 'Please enter an employee ID.';
if (!in_array($status, ['active', 'suspended'], true)) $errors[] = 'Invalid status.';

if ($errors) {
    flash_bad(implode(' ', $errors));
    header('Location: /vc/admin-edit.php?id=' . $id);
    exit;
}

try {
    $db = db();

    $sel = $db->prepare('SELECT id, role, email, employee_id FROM users WHERE id = ? LIMIT 1');
    $sel->execute([$id]);
    $target = $sel->fetch();

    if (!$target) {
        flash_bad('User not found.');
        header('Location: ' . $back);
        exit;
    }


    if ($target['role'] !== 'admin') {
        flash_bad('This form can only edit admin accounts.');
        header('Location: ' . $back);
        exit;
    }

    if ((int)$target['id'] === (int)$me['id']) {
        flash_bad('You cannot edit your own account through this form.');
        header('Location: ' . $back);
        exit;
    }

    // Uniqueness check (exclude self).
    $dup = $db->prepare('SELECT id FROM users WHERE id <> ? AND (email = ? OR employee_id = ?) LIMIT 1');
    $dup->execute([$id, $email, $employee_id]);
    if ($dup->fetch()) {
        flash_bad('Another account already uses that email or employee ID.');
        header('Location: /vc/admin-edit.php?id=' . $id);
        exit;
    }

    $upd = $db->prepare(
        'UPDATE users
            SET full_name = ?, email = ?, employee_id = ?, department = ?, status = ?
          WHERE id = ?'
    );
    $upd->execute([$full_name, $email, $employee_id, $dept, $status, $id]);

    flash_ok('Updated admin ' . $email . '.');
    header('Location: ' . $back);
    exit;
} catch (Throwable $e) {
    error_log('vc-update-admin error: ' . $e->getMessage());
    flash_bad('Could not update the admin. Please try again.');
    header('Location: /vc/admin-edit.php?id=' . $id);
    exit;
}
