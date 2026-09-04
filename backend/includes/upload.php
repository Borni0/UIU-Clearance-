<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

const UPLOAD_ALLOWED_MIME = [
    'application/pdf',
    'image/jpeg',
    'image/png',
];

function upload_max_bytes(): int
{
    $mb = (int) (getenv('UPLOAD_MAX_MB') ?: 100);
    return max(1, $mb) * 1024 * 1024;
}

function upload_root(): string
{
   
    return realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR . 'uploads';
}

/**
 * @return array{doc_id:int, stored_name:string, original_name:string, size:int, mime:string}
 */
function save_uploaded_file(array $file, int $user_id, string $doc_type): array
{
    if (!isset($file['error']) || is_array($file['error'])) {
        throw new RuntimeException('Invalid upload payload.');
    }

    switch ($file['error']) {
        case UPLOAD_ERR_OK: break;
        case UPLOAD_ERR_NO_FILE:        throw new RuntimeException('No file was uploaded.');
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:      throw new RuntimeException('File is too large.');
        case UPLOAD_ERR_PARTIAL:        throw new RuntimeException('Upload was interrupted. Please try again.');
        default:                        throw new RuntimeException('Upload failed (code ' . $file['error'] . ').');
    }

    if (($file['size'] ?? 0) <= 0) {
        throw new RuntimeException('Empty file.');
    }
    if ($file['size'] > upload_max_bytes()) {
        throw new RuntimeException('File exceeds the size limit.');
    }

   
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']) ?: 'application/octet-stream';
    if (!in_array($mime, UPLOAD_ALLOWED_MIME, true)) {
        throw new RuntimeException('Only PDF, JPG, and PNG files are allowed.');
    }

    $user_dir = upload_root() . DIRECTORY_SEPARATOR . $user_id;
    if (!is_dir($user_dir) && !mkdir($user_dir, 0755, true) && !is_dir($user_dir)) {
        throw new RuntimeException('Could not create upload directory.');
    }
   
    $real_user_dir = realpath($user_dir);
    $real_root     = realpath(upload_root());
    if ($real_user_dir === false || $real_root === false || strpos($real_user_dir, $real_root) !== 0) {
        throw new RuntimeException('Upload path is invalid.');
    }

    $stored = bin2hex(random_bytes(16));   
    $dest   = $real_user_dir . DIRECTORY_SEPARATOR . $stored;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        throw new RuntimeException('Could not save the uploaded file.');
    }
    @chmod($dest, 0644);

    $original = substr((string)($file['name'] ?? 'upload'), 0, 255);

    $stmt = db()->prepare(
        'INSERT INTO documents (user_id, doc_type, original_name, stored_name, mime_type, size_bytes)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([$user_id, $doc_type, $original, $stored, $mime, (int) $file['size']]);

    return [
        'doc_id'        => (int) db()->lastInsertId(),
        'stored_name'   => $stored,
        'original_name' => $original,
        'size'          => (int) $file['size'],
        'mime'          => $mime,
    ];
}
