<?php
require_once __DIR__ . '/../backend/includes/auth.php';
require_once __DIR__ . '/../backend/includes/layout.php';
require_role('student');
$me = current_user();

$rows = [];
try {
    $s = db()->prepare(
        'SELECT er.*, d.original_name AS doc_name
         FROM emergency_requests er
         LEFT JOIN documents d ON d.id = er.document_id
         WHERE er.student_id = ?
         ORDER BY er.created_at DESC'
    );
    $s->execute([(int)$me['id']]);
    $rows = $s->fetchAll();
} catch (Throwable $e) { /* ignore */ }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Emergency Requests — UIU Compliance Management Platform</title>
    <link rel="stylesheet" href="../assets/css/theme.css">
    <link rel="stylesheet" href="../assets/css/emegency-page.css">
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
            <a class="nav-item" href="all-documents.php"><i class="fa-regular fa-folder-open"></i> All documents</a>
            <a class="nav-item" href="upload-document.php"><i class="fa-solid fa-cloud-arrow-up"></i> Upload document</a>
            <a class="nav-item" href="track-status.php"><i class="fa-solid fa-location-dot"></i> Track status</a>
        </nav>
        <nav class="nav-section">
            <h3>Support</h3>
            <a class="nav-item" href="emergency-request.php"><i class="fa-solid fa-circle-exclamation"></i> New emergency</a>
            <a class="nav-item active" href="emergency-page.php"><i class="fa-solid fa-life-ring"></i> My emergencies</a>
            <a class="nav-item" href="help.php"><i class="fa-regular fa-circle-question"></i> Help center</a>
        </nav>
    </aside>

    <main class="main">
        <div class="page-head">
            <h1>My emergency requests</h1>
            <p>Track urgent clearance requests you have submitted.</p>
            <div class="actions">
                <a class="btn btn-danger" href="emergency-request.php"><i class="fa-solid fa-circle-exclamation"></i> New emergency request</a>
            </div>
        </div>

        <?= uiu_flash_banner() ?>

        <?php if (empty($rows)): ?>
            <section class="card empty-card">
                <div class="empty">
                    <i class="fa-solid fa-life-ring"></i>
                    <h3>No emergency requests</h3>
                    <p>You haven't submitted any emergency clearance requests yet.</p>
                </div>
            </section>
        <?php else: ?>
            <section class="card">
                <div class="card-head"><h2>Submitted emergencies</h2></div>
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Reason</th>
                                <th>Required by</th>
                                <th>Status</th>
                                <th>Admin remark</th>
                                <th>Decided at</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rows as $r): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars((string)$r['title']) ?></strong></td>
                                    <td class="muted"><?= htmlspecialchars(strlen((string)$r['reason']) > 80 ? substr((string)$r['reason'], 0, 80) . '…' : (string)$r['reason']) ?></td>
                                    <td><?= htmlspecialchars((string)$r['required_by']) ?></td>
                                    <td><?= uiu_status_badge((string)$r['status']) ?></td>
                                    <td><?= htmlspecialchars((string)($r['admin_remark'] ?? '—')) ?></td>
                                    <td><?= htmlspecialchars((string)($r['decided_at'] ?? '—')) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        <?php endif; ?>
    </main>
</div>

<style>.muted{color:var(--text-muted);}</style>
</body>
</html>
