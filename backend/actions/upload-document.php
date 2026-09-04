<?php


declare(strict_types=1);

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/flash.php';
require_once __DIR__ . '/../includes/upload.php';

require_role('student');
csrf_check();

$doc_type = (string)($_POST['doc_type'] ?? '');
$allowed_types = ['id_card', 'fee_receipt', 'library_card', 'transcripts', 'other'];
if (!in_array($doc_type, $allowed_types, true)) {
    flash_bad('Invalid document type.');
    header('Location: /student/upload-document.php');
    exit;
}

$user = current_user();
if (!$user) {
    header('Location: /auth/login.php');
    exit;
}

if (empty($_FILES['file']) || !is_array($_FILES['file'])) {
    flash_bad('Please choose a file to upload.');
    header('Location: /student/upload-document.php');
    exit;
}

try {
    $r = save_uploaded_file($_FILES['file'], (int)$user['id'], $doc_type);
    flash_ok('Uploaded "' . $r['original_name'] . '" successfully.');
} catch (RuntimeException $e) {
    flash_bad($e->getMessage());
}

header('Location: /student/upload-document.php');
exit;
