<?php
require_once __DIR__ . '/../backend/includes/auth.php';
require_once __DIR__ . '/../backend/includes/layout.php';
require_role('vc');

$rows = [];
$search = trim((string)($_GET['q'] ?? ''));
$action_filter = (string)($_GET['action'] ?? '');
$where = ['1=1']; $params = [];
if ($search !== '') {
    $where[] = '(al.detail LIKE ? OR al.action LIKE ? OR u.full_name LIKE ?)';
    $like = '%' . $search . '%';
    array_push($params, $like, $like, $like);
}
if ($action_filter !== '') {
    $where[] = 'al.action LIKE ?';
    $params[] = $action_filter . '%';
}
try {
    $sql = "SELECT al.*, u.full_name AS actor_name, u.role AS actor_role
            FROM activity_log al
            LEFT JOIN users u ON u.id = al.actor_id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY al.created_at DESC LIMIT 200";
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
} catch (Throwable $e) { /* ignore */ }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity log — UIU Compliance Management Platform</title>
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
            <a class="nav-item active" href="activity-log.php"><i class="fa-solid fa-clock-rotate-left"></i> Activity log</a>
            <a class="nav-item" href="certificates.php"><i class="fa-solid fa-certificate"></i> Certificates</a>
        </nav>
        <nav class="nav-section">
            <h3>Administration</h3>
            <a class="nav-item" href="user-management.php"><i class="fa-solid fa-user-gear"></i> User management</a>
        </nav>
    </aside>

    <main class="main">
        <div class="page-head">
            <h1>Activity log</h1>
            <p>Every clearance decision, certificate, and emergency request.</p>
        </div>

        <?= uiu_flash_banner() ?>

        <section class="card mb-24">
            <form method="GET" class="filter-row">
                <div class="filter-field">
                    <label>Action type</label>
                    <select name="action" class="input">
                        <option value="">All</option>
                        <?php foreach (['clearance_','emergency_','certificate_'] as $p): ?>
                            <option value="<?= $p ?>" <?= str_starts_with($action_filter, $p) ? 'selected' : '' ?>><?= ucfirst(rtrim($p,'_')) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-field" style="flex:1;">
                    <label>Search</label>
                    <div class="input"><i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Detail or actor name">
                    </div>
                </div>
                <div class="filter-field" style="align-self: end;">
                    <button class="btn btn-primary" type="submit"><i class="fa-solid fa-filter"></i> Apply</button>
                </div>
            </form>
        </section>

        <section class="card">
            <div class="card-head"><h2>Recent activity</h2></div>
            <?php if (!$rows): ?>
                <p class="muted">No matching activity.</p>
            <?php else: ?>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>When</th><th>Actor</th><th>Action</th><th>Detail</th></tr></thead>
                    <tbody>
                    <?php foreach ($rows as $a): ?>
                        <tr>
                            <td><?= htmlspecialchars((string)$a['created_at']) ?></td>
                            <td><?= htmlspecialchars((string)($a['actor_name'] ?? '—')) ?> <span class="muted small">(<?= htmlspecialchars((string)($a['actor_role'] ?? 'system')) ?>)</span></td>
                            <td><code><?= htmlspecialchars((string)$a['action']) ?></code></td>
                            <td><?= htmlspecialchars((string)($a['detail'] ?? '')) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </section>
    </main>
</div>
<style>.notif-btn{position:relative;color:var(--text);text-decoration:none;padding:6px 10px;border-radius:8px;}.muted{color:var(--text-muted);}.small{font-size:11px;}.filter-row{display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;}.filter-field label{display:block;font-size:12px;color:var(--text-muted);margin-bottom:4px;}</style>
</body>
</html>
