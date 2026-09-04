<?php


declare(strict_types=1);


if (session_status() === PHP_SESSION_NONE) {
    $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_name('UIU_SESSION');
    session_start();
}


 
function require_login(): void
{
    if (empty($_SESSION['user_id'])) {
       
        $back = $_SERVER['REQUEST_URI'] ?? '';
        if ($back) {
            $_SESSION['post_login_redirect'] = $back;
        }
        header('Location: /auth/login.php');
        exit;
    }
}


function require_role(string $role): void
{
    require_login();
    if (($_SESSION['role'] ?? null) !== $role) {
        http_response_code(403);
        exit('403 — Forbidden. This page is for ' . htmlspecialchars($role) . 's only.');
    }
}


function require_role_any(array $roles): void
{
    require_login();
    if (!in_array($_SESSION['role'] ?? null, $roles, true)) {
        http_response_code(403);
        exit('403 — Forbidden. This page is for ' . htmlspecialchars(implode(' or ', $roles)) . ' only.');
    }
}
