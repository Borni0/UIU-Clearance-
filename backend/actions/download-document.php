<?php
/**
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/flash.php';

require_login();

$me = current_user();
if (!$me) {
    header('Location: /auth/login.php');
    exit;
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    exit('Bad request.');
}

try {
    $stmt = db()->prepare('SELECT * FROM documents WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $doc = $stmt->fetch();
    if (!$doc) {
        http_response_code(404);
        exit('Document not found.');
    }

    $is_owner = (int)$doc['user_id'] === (int)$me['id'];
    $is_staff = in_array($me['role'], ['admin', 'vc'], true);
    if (!$is_owner && !$is_staff) {
        http_response_code(403);
        exit('403 — Forbidden.');
    }

    $path = realpath(__DIR__ . '/../uploads/' . $doc['user_id'] . '/' . $doc['stored_name']);
    $root = realpath(__DIR__ . '/../uploads');
    if ($path === false || $root === false || strpos($path, $root) !== 0) {
        http_response_code(404);
        exit('File missing.');
    }

    if (!is_file($path)) {
        http_response_code(404);
        exit('File missing on disk.');
    }

    header('Content-Type: ' . $doc['mime_type']);
    header('Content-Length: ' . (string)$doc['size_bytes']);
    header('Content-Disposition: attachment; filename="' . str_replace('"', '', (string)$doc['original_name']) . '"');
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('X-Content-Type-Options: nosniff');
    readfile($path);
    exit;
} catch (Throwable $e) {
    error_log('download error: ' . $e->getMessage());
    http_response_code(500);
    exit('Server error.');
}
