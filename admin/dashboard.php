<?php
require_once __DIR__ . '/../backend/includes/auth.php';
require_once __DIR__ . '/../backend/includes/layout.php';
require_role_any(['admin', 'vc']);
$me = current_user();

$stats = ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0, 'hold' => 0, 'blocked' => 0, 'students' => 0, 'emergencies' => 0, 'docs' => 0];
try {
    foreach (db()->query("SELECT status, COUNT(*) AS n FROM clearance_requests GROUP BY status")->fetchAll() as $r) {
        $stats[(string)$r['status']] = (int)$r['n'];
        $stats['total'] += (int)$r['n'];
    }
    $stats['students']     = (int) db()->query("SELECT COUNT(*) FROM users WHERE role = 'student' AND status = 'active'")->fetchColumn();
    $stats['emergencies']  = (int) db()->query("SELECT COUNT(*) FROM emergency_requests WHERE status = 'pending'")->fetchColumn();
    $stats['docs']         = (int) db()->query("SELECT COUNT(*) FROM documents")->fetchColumn();
} catch (Throwable $e) {}

$recent = [];
try {
    $stmt = db()->prepare(
        "SELECT cr.id, cr.department, cr.status, cr.submitted_at,
                u.full_name, u.student_id AS sid
         FROM clearance_requests cr
         JOIN users u ON u.id = cr.student_id
         ORDER BY cr.submitted_at DESC LIMIT 8"
    );
    $stmt->execute();
    $recent = $stmt->fetchAll();
} catch (Throwable $e) {  }

$role_badge = $me['role'] === 'vc' ? 'Vice Chancellor' : 'Department Admin';
$role_icon  = $me['role'] === 'vc' ? 'fa-user-tie' : 'fa-briefcase';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Department Admin — UIU Compliance Management Platform</title>
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
        <a class="notif-btn" href="notifications.php"><i class="fa-regular fa-bell"></i></a>
        <span class="role-badge"><i class="fa-solid <?= $role_icon ?>"></i> <?= htmlspecialchars($role_badge) ?></span>
        <a class="logout-btn" href="../backend/actions/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Sign out</a>
    </div>
</header>

<div class="layout">

    <aside class="sidebar">
        <nav class="nav-section">
            <h3>Overview</h3>
            <a class="nav-item active" href="dashboard.php"><i class="fa-solid fa-gauge-high"></i> Dashboard</a>
        </nav>
        <nav class="nav-section">
            <h3>Departments</h3>
            <a class="nav-item" href="admin-education.php?dept=education"><i class="fa-solid fa-graduation-cap"></i> Education</a>
            <a class="nav-item" href="admin-education.php?dept=library"><i class="fa-solid fa-book"></i> Library</a>
            <a class="nav-item" href="admin-education.php?dept=transport"><i class="fa-solid fa-bus"></i> Transport</a>
            <a class="nav-item" href="admin-education.php?dept=medical"><i class="fa-solid fa-hospital"></i> Medical</a>
            <a class="nav-item" href="admin-education.php?dept=hostel"><i class="fa-solid fa-house-chimney"></i> Hostel</a>
        </nav>
        <nav class="nav-section">
            <h3>Other</h3>
            <a class="nav-item" href="emergency-inbox.php"><i class="fa-solid fa-circle-exclamation"></i> Emergency inbox</a>
            <a class="nav-item" href="user-management.php"><i class="fa-solid fa-user-gear"></i> User management</a>
        </nav>
    </aside>

    <main class="main">

        <div class="page-head">
            <h1><?= htmlspecialchars($role_badge) ?> panel</h1>
            <p>Welcome, <?= htmlspecialchars((string)$me['full_name']) ?>.</p>
        </div>

        <?= uiu_flash_banner() ?>

        <div class="grid grid-4 mb-24">
            <div class="stat"><div class="icon"><i class="fa-solid fa-file-circle-check"></i></div><div class="label">Total requests</div><div class="value"><?= $stats['total'] ?></div><div class="sub">All time</div></div>
            <div class="stat warn"><div class="icon"><i class="fa-regular fa-clock"></i></div><div class="label">Pending</div><div class="value"><?= $stats['pending'] + $stats['hold'] ?></div><div class="sub">Requires action</div></div>
            <div class="stat ok"><div class="icon"><i class="fa-solid fa-circle-check"></i></div><div class="label">Approved</div><div class="value"><?= $stats['approved'] ?></div><div class="sub">Completed</div></div>
            <div class="stat bad"><div class="icon"><i class="fa-solid fa-circle-xmark"></i></div><div class="label">Rejected / Blocked</div><div class="value"><?= $stats['rejected'] + $stats['blocked'] ?></div><div class="sub">Declined</div></div>
        </div>

        <div class="grid grid-3 mb-24">
            <div class="stat"><div class="icon"><i class="fa-solid fa-user-graduate"></i></div><div class="label">Active students</div><div class="value"><?= $stats['students'] ?></div></div>
            <div class="stat warn"><div class="icon"><i class="fa-solid fa-circle-exclamation"></i></div><div class="label">Pending emergencies</div><div class="value"><?= $stats['emergencies'] ?></div></div>
            <div class="stat"><div class="icon"><i class="fa-regular fa-folder-open"></i></div><div class="label">Documents on file</div><div class="value"><?= $stats['docs'] ?></div></div>
        </div>

        <section class="card">
            <div class="card-head">
                <div>
                    <h2>Recent requests</h2>
                    <p>Latest clearance submissions across all departments.</p>
                </div>
                <a class="btn btn-secondary btn-sm" href="admin-education.php?dept=education">All requests</a>
            </div>
            <?php if (!$recent): ?>
                <div class="empty"><i class="fa-regular fa-folder-open"></i><h3>No requests yet</h3></div>
            <?php else: ?>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr><th>Student</th><th>Department</th><th>Submitted</th><th>Status</th><th></th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($recent as $r): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars((string)$r['full_name']) ?></strong> <span class="muted small">(<?= htmlspecialchars((string)$r['sid']) ?>)</span></td>
                            <td><?= htmlspecialchars(ucfirst((string)$r['department'])) ?></td>
                            <td><?= htmlspecialchars((string)$r['submitted_at']) ?></td>
                            <td><?= uiu_status_badge((string)$r['status']) ?></td>
                            <td><a class="btn btn-sm btn-secondary" href="request-detail.php?id=<?= (int)$r['id'] ?>&back=/admin/dashboard.php">Open</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </section>

    </main>
</div>

<style>
.notif-btn{position:relative;color:var(--text);text-decoration:none;padding:6px 10px;border-radius:8px;}
.muted{color:var(--text-muted);} .small{font-size:11px;}
</style>
</body>
</html>
