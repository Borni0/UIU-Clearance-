<?php


declare(strict_types=1);

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/flash.php';


function login_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id']    = (int) $user['id'];
    $_SESSION['role']       = $user['role'];
    $_SESSION['full_name']  = $user['full_name'];
    $_SESSION['email']      = $user['email'];
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }
    session_destroy();
}


function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    if (isset($_SESSION['user_row']) && is_array($_SESSION['user_row'])) {
        return $_SESSION['user_row'];
    }
    $stmt = db()->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$_SESSION['user_id']]);
    $row = $stmt->fetch();
    if ($row) {
        $_SESSION['user_row'] = $row;
    }
    return $row ?: null;
}


function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}


function csrf_check(): void
{
    $posted = $_POST['csrf'] ?? '';
    $stored = $_SESSION['csrf'] ?? '';
    if (!$posted || !$stored || !hash_equals($stored, $posted)) {
        http_response_code(403);
        exit('403 — Invalid or missing CSRF token. Please reload the form and try again.');
    }
}
