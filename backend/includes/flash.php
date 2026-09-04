<?php


declare(strict_types=1);

require_once __DIR__ . '/session.php';

function flash_set(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function flash_get(): ?array
{
    if (empty($_SESSION['flash'])) {
        return null;
    }
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $f;
}

function flash_ok(string $message): void  { flash_set('ok', $message); }
function flash_bad(string $message): void { flash_set('bad', $message); }
function flash_warn(string $message): void { flash_set('warn', $message); }
