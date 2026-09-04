<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Document — UIU Compliance Management Platform</title>
    <link rel="stylesheet" href="../assets/css/theme.css">
    <link rel="stylesheet" href="../assets/css/Upload-document.css">
    <script src="https://kit.fontawesome.com/063f939101.js" crossorigin="anonymous"></script>
</head>
<?php
require_once __DIR__ . '/../backend/includes/auth.php';
require_once __DIR__ . '/../backend/includes/layout.php';
require_role('student');
$current = current_user();
?>

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
            <a class="nav-item" href="all-documents.php"><i class="fa-regular fa-folder-open"></i> All documents</a>
            <a class="nav-item active" href="upload-document.php"><i class="fa-solid fa-cloud-arrow-up"></i> Upload document</a>
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
            <h1>Upload documents</h1>
            <p>Upload all the necessary documents for your clearance application.</p>
        </div>

        <?php
        $flash = flash_get();
        $user_docs = [];
        try {
            $stmt = db()->prepare('SELECT id, doc_type, original_name, mime_type, size_bytes, uploaded_at FROM documents WHERE user_id = ? ORDER BY uploaded_at DESC');
            $stmt->execute([(int)$current['id']]);
            $user_docs = $stmt->fetchAll();
        } catch (Throwable $e) {
            $user_docs = [];
        }

        $DOC_LABELS = [
            'id_card'      => ['icon' => 'fa-regular fa-id-card',      'title' => 'ID Card',       'hint' => 'National ID or university ID'],
            'fee_receipt'  => ['icon' => 'fa-regular fa-receipt',      'title' => 'Fee Receipt',   'hint' => 'Latest payment receipt'],
            'library_card' => ['icon' => 'fa-solid fa-book',           'title' => 'Library Card',  'hint' => 'Library membership card'],
            'transcripts'  => ['icon' => 'fa-regular fa-file-lines',   'title' => 'Transcripts',   'hint' => 'Official academic transcript'],
            'other'        => ['icon' => 'fa-regular fa-file',         'title' => 'Other',         'hint' => 'Any supporting document'],
        ];
        ?>

        <?= uiu_flash_banner() ?>

        <section class="card">
            <div class="card-head">
                <div>
                    <h2>Required documents</h2>
                    <p>Each document must be a PDF, JPG, or PNG (max 100 MB).</p>
                </div>
            </div>

            <div class="grid grid-2">
                <?php foreach (['id_card','fee_receipt','library_card','transcripts'] as $type):
                    $meta = $DOC_LABELS[$type]; ?>
                    <form class="up-card" method="POST" action="../backend/actions/upload-document.php" enctype="multipart/form-data">
                        <input type="hidden" name="csrf"     value="<?= htmlspecialchars(csrf_token()) ?>">
                        <input type="hidden" name="doc_type" value="<?= htmlspecialchars($type) ?>">
                        <div class="up-icon"><i class="<?= $meta['icon'] ?>"></i></div>
                        <div>
                            <h3><?= htmlspecialchars($meta['title']) ?></h3>
                            <p><?= htmlspecialchars($meta['hint']) ?></p>
                        </div>
                        <label class="btn btn-primary btn-sm" style="cursor: pointer;">
                            <i class="fa-solid fa-cloud-arrow-up"></i> Upload
                            <input type="file" name="file" accept="application/pdf,image/jpeg,image/png" required style="display:none" onchange="this.form.submit()">
                        </label>
                    </form>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="card mt-24">
            <div class="card-head">
                <div>
                    <h2>Submitted documents</h2>
                    <p>Files you have uploaded so far.</p>
                </div>
            </div>

            <?php if (!$user_docs): ?>
                <div class="empty">
                    <i class="fa-regular fa-folder-open"></i>
                    <h3>No documents uploaded yet</h3>
                    <p>Use the upload buttons above to add your documents.</p>
                </div>
            <?php else: ?>
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>File name</th>
                                <th>MIME</th>
                                <th>Size</th>
                                <th>Uploaded</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($user_docs as $d):
                            $meta = $DOC_LABELS[$d['doc_type']] ?? $DOC_LABELS['other'];
                            $size = number_format(((int)$d['size_bytes']) / 1024, 1) . ' KB';
                            $when = htmlspecialchars($d['uploaded_at']); ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($meta['title']) ?></strong></td>
                                <td><?= htmlspecialchars($d['original_name']) ?></td>
                                <td><?= htmlspecialchars($d['mime_type']) ?></td>
                                <td><?= htmlspecialchars($size) ?></td>
                                <td><?= $when ?></td>
                                <td><a class="btn btn-sm btn-secondary" href="../backend/actions/download-document.php?id=<?= (int)$d['id'] ?>"><i class="fa-solid fa-download"></i> Download</a></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>

    </main>
</div>

</body>
</html>
