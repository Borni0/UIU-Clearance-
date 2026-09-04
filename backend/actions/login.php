<?php
/**
 
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/flash.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /auth/login.php');
    exit;
}

csrf_check();

$email  = strtolower(trim((string)($_POST['email']  ?? '')));
$pw     = (string)($_POST['password'] ?? '');
$role   = (string)($_POST['role']    ?? '');

$redirect_login = '/auth/login.php';

if ($email === '' || $pw === '' || !in_array($role, ['student', 'admin', 'vc'], true)) {
    flash_bad('Please fill in email, password, and choose a role.');
    header('Location: ' . $redirect_login);
    exit;
}

$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

try {
    $stmt = db()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    $ok = $user && $user['status'] === 'active'
        && hash_equals((string)$user['role'], $role)
        && password_verify($pw, (string)$user['password']);

    $audit = db()->prepare(
        'INSERT INTO login_audit (user_id, email, ip, success) VALUES (?, ?, ?, ?)'
    );
    $audit->execute([$user['id'] ?? null, $email, $ip, $ok ? 1 : 0]);

    if (!$ok) {
        flash_bad('Email, password, or role is incorrect.');
        header('Location: ' . $redirect_login);
        exit;
    }

    login_user($user);

    $dest = $_SESSION['post_login_redirect'] ?? null;
    unset($_SESSION['post_login_redirect']);

    $home = match ($user['role']) {
        'student' => '/student/dashboard.php',
        'admin'   => '/admin/dashboard.php',
        'vc'      => '/vc/dashboard.php',
        default   => $redirect_login,
    };
    header('Location: ' . ($dest ?: $home));
    exit;
} catch (Throwable $e) {
    error_log('login error: ' . $e->getMessage());
    flash_bad('Something went wrong. Please try again.');
    header('Location: ' . $redirect_login);
    exit;
}
