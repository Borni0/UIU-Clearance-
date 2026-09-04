<?php
require_once __DIR__ . '/../backend/includes/auth.php';
require_once __DIR__ . '/../backend/includes/layout.php';
require_role_any(['admin', 'vc']);
$me = current_user();

$rows = [];
$status_filter = (string)($_GET['status'] ?? '');
$search = trim((string)($_GET['q'] ?? ''));

$where = ['1=1'];
$params = [];
if ($status_filter !== '' && in_array($status_filter, ['pending','approved','rejected'], true)) {
    $where[] = 'er.status = ?';
    $params[] = $status_filter;
}
if ($search !== '') {
    $where[] = '(u.full_name LIKE ? OR er.title LIKE ?)';
    $like = '%' . $search . '%';
    $params[] = $like; $params[] = $like;
}

$stats = ['pending' => 0, 'approved' => 0, 'rejected' => 0, 'total' => 0];
try {
    $c = db()->query("SELECT status, COUNT(*) AS n FROM emergency_requests GROUP BY status");
    foreach ($c->fetchAll() as $r) {
        $stats[(string)$r['status']] = (int)$r['n'];
        $stats['total'] += (int)$r['n'];
    }
    $sql = "SELECT er.*, u.full_name AS student_name, u.email AS student_email, u.student_id AS sid, u.department AS student_dept,
                   d.original_name AS doc_name, d.id AS doc_id
            FROM emergency_requests er
            JOIN users u ON u.id = er.student_id
            LEFT JOIN documents d ON d.id = er.document_id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY FIELD(er.status,'pending','approved','rejected'), er.created_at DESC
            LIMIT 200";
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
} catch (Throwable $e) {  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emergency inbox — UIU Compliance Management Platform</title>
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
        <span class="role-badge"><i class="fa-solid fa-user-tie"></i> <?= $me['role'] === 'vc' ? 'Vice Chancellor' : 'Department Admin' ?></span>
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
            <h3>Departments</h3>
            <a class="nav-item" href="admin-education.php?dept=education"><i class="fa-solid fa-graduation-cap"></i> Education</a>
            <a class="nav-item" href="admin-education.php?dept=library"><i class="fa-solid fa-book"></i> Library</a>
            <a class="nav-item" href="admin-education.php?dept=transport"><i class="fa-solid fa-bus"></i> Transport</a>
            <a class="nav-item" href="admin-education.php?dept=medical"><i class="fa-solid fa-hospital"></i> Medical</a>
            <a class="nav-item" href="admin-education.php?dept=hostel"><i class="fa-solid fa-house-chimney"></i> Hostel</a>
        </nav>
        <nav class="nav-section">
            <h3>Other</h3>
            <a class="nav-item active" href="emergency-inbox.php"><i class="fa-solid fa-circle-exclamation"></i> Emergency inbox</a>
            <a class="nav-item" href="user-management.php"><i class="fa-solid fa-user-gear"></i> User management</a>
        </nav>
    </aside>

    <main class="main">
        <div class="page-head">
            <h1>Emergency inbox</h1>
            <p>Student emergency clearance requests needing review.</p>
        </div>

        <?= uiu_flash_banner() ?>

        <div class="grid grid-4 mb-24">
            <div class="stat"><div class="icon"><i class="fa-solid fa-bell"></i></div><div class="label">Total</div><div class="value"><?= $stats['total'] ?></div></div>
            <div class="stat warn"><div class="icon"><i class="fa-regular fa-clock"></i></div><div class="label">Pending</div><div class="value"><?= $stats['pending'] ?></div></div>
            <div class="stat ok"><div class="icon"><i class="fa-solid fa-circle-check"></i></div><div class="label">Approved</div><div class="value"><?= $stats['approved'] ?></div></div>
            <div class="stat bad"><div class="icon"><i class="fa-solid fa-circle-xmark"></i></div><div class="label">Rejected</div><div class="value"><?= $stats['rejected'] ?></div></div>
        </div>

        <section class="card mb-24">
            <form method="GET" class="filter-row">
                <div class="filter-field">
                    <label>Status</label>
                    <select name="status" class="input">
                        <option value="">All</option>
                        <?php foreach (['pending','approved','rejected'] as $s): ?>
                            <option value="<?= $s ?>" <?= $status_filter === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-field" style="flex:1;">
                    <label>Search</label>
                    <div class="input"><i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Student name or request title">
                    </div>
                </div>
                <div class="filter-field" style="align-self: end;">
                    <button class="btn btn-primary" type="submit"><i class="fa-solid fa-filter"></i> Apply</button>
                </div>
            </form>
        </section>

        <section class="card">
            <div class="card-head"><h2>Emergency requests</h2><p><?= count($rows) ?> result(s).</p></div>
            <?php if (!$rows): ?>
                <div class="empty"><i class="fa-regular fa-folder-open"></i><h3>No emergency requests</h3><p>You're all caught up.</p></div>
            <?php else: ?>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Title</th>
                            <th>Required by</th>
                            <th>Status</th>
                            <th>Document</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($rows as $r): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars((string)$r['student_name']) ?></strong>
                                <div class="muted small"><?= htmlspecialchars((string)$r['sid']) ?> · <?= htmlspecialchars((string)$r['student_dept']) ?></div>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars((string)$r['title']) ?></strong>
                                <div class="muted small"><?= htmlspecialchars(strlen((string)$r['reason']) > 80 ? substr((string)$r['reason'], 0, 80) . '…' : (string)$r['reason']) ?></div>
                            </td>
                            <td><?= htmlspecialchars((string)$r['required_by']) ?></td>
                            <td><?= uiu_status_badge((string)$r['status']) ?></td>
                            <td>
                                <?php if ($r['doc_id']): ?>
                                    <a class="btn btn-sm btn-secondary" href="../backend/actions/download-document.php?id=<?= (int)$r['doc_id'] ?>"><i class="fa-solid fa-download"></i> <?= htmlspecialchars((string)$r['doc_name']) ?></a>
                                <?php else: ?>
                                    <span class="muted small">No file</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($r['status'] === 'pending'): ?>
                                <form method="POST" action="../backend/actions/admin-update-emergency.php" class="inline-form" style="display:flex;gap:6px;flex-wrap:wrap;">
                                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                                    <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                                    <input type="hidden" name="back" value="/admin/emergency-inbox.php">
                                    <input type="text" name="remark" placeholder="Remark (optional)" class="input" style="min-width:160px;height:36px;">
                                    <button class="btn btn-sm btn-success" type="submit" name="decision" value="approved"><i class="fa-solid fa-check"></i></button>
                                    <button class="btn btn-sm btn-danger"  type="submit" name="decision" value="rejected"><i class="fa-solid fa-xmark"></i></button>
                                </form>
                                <?php else: ?>
                                    <span class="muted small">Decided</span>
                                <?php endif; ?>
                            </td>
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
.filter-row{display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;}
.filter-field label{display:block;font-size:12px;color:var(--text-muted);margin-bottom:4px;}
</style>
</body>
</html>
