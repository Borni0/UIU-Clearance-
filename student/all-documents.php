<?php
require_once __DIR__ . '/../backend/includes/auth.php';
require_once __DIR__ . '/../backend/includes/layout.php';
require_role('student');
$me = current_user();

$DOC_LABELS = [
    'id_card'      => ['icon' => 'fa-regular fa-id-card',      'title' => 'ID Card'],
    'fee_receipt'  => ['icon' => 'fa-regular fa-receipt',      'title' => 'Fee Receipt'],
    'library_card' => ['icon' => 'fa-solid fa-book',           'title' => 'Library Card'],
    'transcripts'  => ['icon' => 'fa-regular fa-file-lines',   'title' => 'Transcripts'],
    'other'        => ['icon' => 'fa-regular fa-file',         'title' => 'Other'],
];

$rows = [];
try {
    $s = db()->prepare('SELECT id, doc_type, original_name, mime_type, size_bytes, uploaded_at FROM documents WHERE user_id = ? ORDER BY uploaded_at DESC');
    $s->execute([(int)$me['id']]);
    $rows = $s->fetchAll();
} catch (Throwable $e) { /* ignore */ }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Documents — UIU Compliance Management Platform</title>
    <link rel="stylesheet" href="../assets/css/theme.css">
    <link rel="stylesheet" href="../assets/css/all-documents.css">
    <script src="https://kit.fontawesome.com/063f939101.js" crossorigin="anonymous"></script>
</head>
<body class="plain-bg">
<header class="topbar">
    <div class="brand">
        <img src="../assets/images/UIU%20LOGO.png" alt="UIU Logo">
        <div class="brand-text">UIU Compliance<span>Management Platform</span></div>
    </div>
    <div class="right">
        <a class="notif-btn" href="notifications.php"><i class="fa-regular fa-bell"></i></a>
        <span class="role-badge"><i class="fa-solid fa-graduation-cap"></i> Student</span>
        <a class="logout-btn" href="../backend/actions/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Sign out</a>
    </div>
</header>
<div class="layout">
    <aside class="sidebar">
        <nav class="nav-section">
            <h3>Overview</h3>
            <a class="nav-item" href="dashboard.php"><i class="fa-solid fa-house"></i> Dashboard</a>
        </nav>
        <nav class="nav-section">
            <h3>Apply for clearance</h3>
            <a class="nav-item" href="clearance-education.php"><i class="fa-solid fa-graduation-cap"></i> Education</a>
            <a class="nav-item" href="clearance-library.php"><i class="fa-solid fa-book"></i> Library</a>
            <a class="nav-item" href="clearance-transport.php"><i class="fa-solid fa-bus"></i> Transport</a>
            <a class="nav-item" href="clearance-medical.php"><i class="fa-solid fa-hospital"></i> Medical</a>
            <a class="nav-item" href="clearance-hostel.php"><i class="fa-solid fa-house-chimney"></i> Hostel</a>
        </nav>
        <nav class="nav-section">
            <h3>Manage</h3>
            <a class="nav-item active" href="all-documents.php"><i class="fa-regular fa-folder-open"></i> All documents</a>
            <a class="nav-item" href="upload-document.php"><i class="fa-solid fa-cloud-arrow-up"></i> Upload document</a>
            <a class="nav-item" href="track-status.php"><i class="fa-solid fa-location-dot"></i> Track status</a>
        </nav>
        <nav class="nav-section">
            <h3>Support</h3>
            <a class="nav-item" href="emergency-request.php"><i class="fa-solid fa-circle-exclamation"></i> Emergency request</a>
            <a class="nav-item" href="help.php"><i class="fa-regular fa-circle-question"></i> Help center</a>
        </nav>
    </aside>

    <main class="main">
        <div class="page-head">
            <h1>All documents</h1>
            <p><?= count($rows) ?> document(s) on file for <?= htmlspecialchars((string)$me['full_name']) ?> (<?= htmlspecialchars((string)$me['student_id']) ?>).</p>
            <div class="actions">
                <a class="btn btn-primary" href="upload-document.php"><i class="fa-solid fa-cloud-arrow-up"></i> Upload new</a>
            </div>
        </div>

        <?= uiu_flash_banner() ?>

        <?php if (!$rows): ?>
            <section class="card"><div class="empty"><i class="fa-regular fa-folder-open"></i><h3>No documents yet</h3><p>Upload your first document to get started.</p></div></section>
        <?php else: ?>
            <div class="grid grid-3">
                <?php foreach ($rows as $d):
                    $meta = $DOC_LABELS[$d['doc_type']] ?? $DOC_LABELS['other'];
                ?>
                <article class="doc-card">
                    <div class="doc-icon"><i class="<?= $meta['icon'] ?>"></i></div>
                    <h3><?= htmlspecialchars($meta['title']) ?></h3>
                    <p class="doc-meta"><?= htmlspecialchars($d['original_name']) ?></p>
                    <p class="doc-meta"><?= number_format(((int)$d['size_bytes']) / 1024, 1) ?> KB · <?= htmlspecialchars($d['uploaded_at']) ?></p>
                    <div class="doc-actions">
                        <a class="btn btn-sm btn-secondary" href="../backend/actions/download-document.php?id=<?= (int)$d['id'] ?>"><i class="fa-solid fa-download"></i> Download</a>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</div>
<style>.notif-btn{position:relative;color:var(--text);text-decoration:none;padding:6px 10px;border-radius:8px;}</style>
</body>
</html>
