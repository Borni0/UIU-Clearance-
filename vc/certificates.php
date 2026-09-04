<?php
require_once __DIR__ . '/../backend/includes/auth.php';
require_once __DIR__ . '/../backend/includes/layout.php';
require_role('vc');

$certs = [];
$eligible = [];
try {
    $certs = db()->query(
        "SELECT c.*, u.full_name, u.student_id, u.department, iss.full_name AS issuer_name
         FROM certificates c
         JOIN users u ON u.id = c.student_id
         LEFT JOIN users iss ON iss.id = c.issued_by
         ORDER BY c.issued_at DESC LIMIT 100"
    )->fetchAll();
    $eligible = db()->query(
        "SELECT u.id, u.full_name, u.student_id, u.department
         FROM users u
         WHERE u.role = 'student' AND u.status = 'active'
           AND NOT EXISTS (SELECT 1 FROM certificates c WHERE c.student_id = u.id)
           AND (SELECT COUNT(DISTINCT department) FROM clearance_requests
                WHERE student_id = u.id AND status = 'approved') = 5
         ORDER BY u.full_name"
    )->fetchAll();
} catch (Throwable $e) { /* ignore */ }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificates — UIU Compliance Management Platform</title>
    <link rel="stylesheet" href="../assets/css/theme.css">
    <link rel="stylesheet" href="../assets/css/department-admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="plain-bg">
<header class="topbar">
    <div class="brand">
        <img src="../assets/images/UIU%20LOGO.png" alt="UIU Logo">
        <div class="brand-text">UIU Compliance<span>Management Platform</span></div>
    </div>
    <div class="right">
        <a class="notif-btn" href="../admin/notifications.php"><i class="fa-regular fa-bell"></i></a>
        <span class="role-badge"><i class="fa-solid fa-user-tie"></i> Vice Chancellor</span>
        <a class="logout-btn" href="../backend/actions/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Sign out</a>
    </div>
</header>
<div class="layout">
    <aside class="sidebar">
        <nav class="nav-section">
            <h3>Overview</h3>
            <a class="nav-item" href="dashboard.php"><i class="fa-solid fa-gauge-high"></i> Dashboard</a>
        </nav>
        <nav class="nav-section">
            <h3>Approvals</h3>
            <a class="nav-item" href="../admin/admin-education.php?dept=education"><i class="fa-solid fa-graduation-cap"></i> Education</a>
            <a class="nav-item" href="../admin/admin-education.php?dept=library"><i class="fa-solid fa-book"></i> Library</a>
            <a class="nav-item" href="../admin/admin-education.php?dept=transport"><i class="fa-solid fa-bus"></i> Transport</a>
            <a class="nav-item" href="../admin/admin-education.php?dept=medical"><i class="fa-solid fa-hospital"></i> Medical</a>
            <a class="nav-item" href="../admin/admin-education.php?dept=hostel"><i class="fa-solid fa-house-chimney"></i> Hostel</a>
        </nav>
        <nav class="nav-section">
            <h3>Reports</h3>
            <a class="nav-item" href="emergency-inbox.php"><i class="fa-solid fa-circle-exclamation"></i> Emergency inbox</a>
            <a class="nav-item" href="activity-log.php"><i class="fa-solid fa-clock-rotate-left"></i> Activity log</a>
            <a class="nav-item active" href="certificates.php"><i class="fa-solid fa-certificate"></i> Certificates</a>
        </nav>
        <nav class="nav-section">
            <h3>Administration</h3>
            <a class="nav-item" href="user-management.php"><i class="fa-solid fa-user-gear"></i> User management</a>
        </nav>
    </aside>

    <main class="main">
        <div class="page-head">
            <h1>Certificates</h1>
            <p>Issued clearance certificates and the eligible queue.</p>
        </div>

        <?= uiu_flash_banner() ?>

        <section class="card mb-24">
            <div class="card-head"><h2>Eligible students (pending issue)</h2></div>
            <?php if (!$eligible): ?>
                <p class="muted">No eligible students at the moment.</p>
            <?php else: ?>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Name</th><th>Student ID</th><th>Department</th><th>Action</th></tr></thead>
                    <tbody>
                    <?php foreach ($eligible as $s): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars((string)$s['full_name']) ?></strong></td>
                            <td><?= htmlspecialchars((string)$s['student_id']) ?></td>
                            <td><?= htmlspecialchars((string)$s['department']) ?></td>
                            <td>
                                <form method="POST" action="../backend/actions/vc-issue-certificate.php" style="display:inline;">
                                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                                    <input type="hidden" name="student_id" value="<?= (int)$s['id'] ?>">
                                    <input type="hidden" name="back" value="/vc/certificates.php">
                                    <button class="btn btn-sm btn-success" type="submit"><i class="fa-solid fa-certificate"></i> Issue</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </section>

        <section class="card">
            <div class="card-head"><h2>Issued certificates</h2></div>
            <?php if (!$certs): ?>
                <p class="muted">No certificates issued yet.</p>
            <?php else: ?>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Serial</th><th>Student</th><th>Issued by</th><th>Issued at</th><th>Note</th></tr></thead>
                    <tbody>
                    <?php foreach ($certs as $c): ?>
                        <tr>
                            <td><code><?= htmlspecialchars((string)$c['serial']) ?></code></td>
                            <td><strong><?= htmlspecialchars((string)$c['full_name']) ?></strong> <span class="muted small">(<?= htmlspecialchars((string)$c['student_id']) ?>)</span></td>
                            <td><?= htmlspecialchars((string)($c['issuer_name'] ?? '—')) ?></td>
                            <td><?= htmlspecialchars((string)$c['issued_at']) ?></td>
                            <td class="muted small"><?= htmlspecialchars((string)($c['note'] ?? '')) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </section>
    </main>
</div>
<style>.notif-btn{position:relative;color:var(--text);text-decoration:none;padding:6px 10px;border-radius:8px;}.muted{color:var(--text-muted);}.small{font-size:11px;}</style>
</body>
</html>
