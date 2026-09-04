<?php

require_once __DIR__ . '/../backend/includes/auth.php';
require_once __DIR__ . '/../backend/includes/layout.php';
require_role_any(['admin', 'vc']);
$me = current_user();

$valid = [
    'education' => ['label' => 'Education', 'icon' => 'fa-graduation-cap',  'page' => 'admin-education.php',  'css' => 'page13.css'],
    'library'   => ['label' => 'Library',   'icon' => 'fa-book',           'page' => 'admin-library.php',    'css' => 'page14.css'],
    'transport' => ['label' => 'Transport', 'icon' => 'fa-bus',            'page' => 'admin-transport.php',  'css' => 'page15.css'],
    'medical'   => ['label' => 'Medical',   'icon' => 'fa-hospital',       'page' => 'admin-medical.php',    'css' => 'page16.css'],
    'hostel'    => ['label' => 'Hostel',    'icon' => 'fa-house-chimney',  'page' => 'admin-hostel.php',     'css' => 'page17.css'],
];
$dept = (string)($_GET['dept'] ?? 'education');
if (!isset($valid[$dept])) { $dept = 'education'; }
$meta = $valid[$dept];

$status_filter = (string)($_GET['status'] ?? '');
$search = trim((string)($_GET['q'] ?? ''));

$where = ['cr.department = ?'];
$params = [$dept];
if ($status_filter !== '' && in_array($status_filter, ['pending','approved','hold','rejected','blocked'], true)) {
    $where[] = 'cr.status = ?';
    $params[] = $status_filter;
}
if ($search !== '') {
    $where[] = '(u.full_name LIKE ? OR u.student_id LIKE ? OR u.email LIKE ?)';
    $like = '%' . $search . '%';
    $params[] = $like; $params[] = $like; $params[] = $like;
}
$where_sql = 'WHERE ' . implode(' AND ', $where);


$stats = ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0, 'hold' => 0, 'blocked' => 0];
$rows = [];
try {
    $counts = db()->prepare("SELECT status, COUNT(*) AS n FROM clearance_requests WHERE department = ? GROUP BY status");
    $counts->execute([$dept]);
    foreach ($counts->fetchAll() as $r) {
        $stats[(string)$r['status']] = (int)$r['n'];
        $stats['total'] += (int)$r['n'];
    }

    $sql = "SELECT cr.id, cr.status, cr.reason, cr.submitted_at, cr.decided_at,
                   u.id AS student_id, u.full_name, u.email, u.student_id AS sid, u.department,
                   du.full_name AS decider_name
            FROM clearance_requests cr
            JOIN users u ON u.id = cr.student_id
            LEFT JOIN users du ON du.id = cr.decided_by
            $where_sql
            ORDER BY FIELD(cr.status,'pending','hold','rejected','blocked','approved'), cr.submitted_at DESC
            LIMIT 200";
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
} catch (Throwable $e) {  }


$remark_counts = [];
try {
    $rc = db()->prepare("SELECT request_id, COUNT(*) AS n FROM clearance_remarks GROUP BY request_id");
    $rc->execute();
    foreach ($rc->fetchAll() as $r) { $remark_counts[(int)$r['request_id']] = (int)$r['n']; }
} catch (Throwable $e) { /* ignore */ }

$role_badge = $me['role'] === 'vc' ? 'Vice Chancellor' : ($meta['label'] . ' Admin');
$role_icon  = $me['role'] === 'vc' ? 'fa-user-tie' : 'fa-briefcase';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($meta['label']) ?> Admin — UIU Compliance Management Platform</title>
    <link rel="stylesheet" href="../assets/css/theme.css">
    <link rel="stylesheet" href="../assets/css/<?= htmlspecialchars($meta['css']) ?>">
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
            <a class="nav-item" href="dashboard.php"><i class="fa-solid fa-house"></i> Dashboard</a>
        </nav>
        <nav class="nav-section">
            <h3>Departments</h3>
            <?php foreach ($valid as $k => $m): ?>
                <a class="nav-item <?= $k === $dept ? 'active' : '' ?>" href="admin-education.php?dept=<?= $k ?>">
                    <i class="fa-solid <?= $m['icon'] ?>"></i> <?= $m['label'] ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <nav class="nav-section">
            <h3>Other</h3>
            <a class="nav-item" href="emergency-inbox.php"><i class="fa-solid fa-circle-exclamation"></i> Emergency inbox</a>
            <a class="nav-item" href="user-management.php"><i class="fa-solid fa-user-gear"></i> User management</a>
        </nav>
    </aside>

    <main class="main">
        <div class="page-head">
            <h1><?= htmlspecialchars($meta['label']) ?> clearance requests</h1>
            <p>Review and approve <?= htmlspecialchars(strtolower($meta['label'])) ?> department requests.</p>
        </div>

        <?= uiu_flash_banner() ?>

        <div class="grid grid-4 mb-24">
            <div class="stat"><div class="icon"><i class="fa-solid fa-file-circle-check"></i></div><div class="label">Total</div><div class="value"><?= $stats['total'] ?></div></div>
            <div class="stat warn"><div class="icon"><i class="fa-regular fa-clock"></i></div><div class="label">Pending</div><div class="value"><?= $stats['pending'] + $stats['hold'] ?></div></div>
            <div class="stat ok"><div class="icon"><i class="fa-solid fa-circle-check"></i></div><div class="label">Approved</div><div class="value"><?= $stats['approved'] ?></div></div>
            <div class="stat bad"><div class="icon"><i class="fa-solid fa-circle-xmark"></i></div><div class="label">Rejected / Blocked</div><div class="value"><?= $stats['rejected'] + $stats['blocked'] ?></div></div>
        </div>

        <section class="card mb-24">
            <form method="GET" class="filter-row">
                <input type="hidden" name="dept" value="<?= htmlspecialchars($dept) ?>">
                <div class="filter-field">
                    <label>Status</label>
                    <select name="status" class="input">
                        <option value="">All</option>
                        <?php foreach (['pending','approved','hold','rejected','blocked'] as $s): ?>
                            <option value="<?= $s ?>" <?= $status_filter === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-field" style="flex:1;">
                    <label>Search</label>
                    <div class="input"><i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Student name, ID, or email">
                    </div>
                </div>
                <div class="filter-field" style="align-self: end;">
                    <button class="btn btn-primary" type="submit"><i class="fa-solid fa-filter"></i> Apply</button>
                </div>
            </form>
        </section>

        <section class="card">
            <div class="card-head">
                <div>
                    <h2>Student clearance requests</h2>
                    <p><?= count($rows) ?> request(s) match your filter.</p>
                </div>
            </div>

            <?php if (!$rows): ?>
                <div class="empty"><i class="fa-regular fa-folder-open"></i><h3>No matching requests</h3><p>Try changing the status filter or search query.</p></div>
            <?php else: ?>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Department</th>
                            <th>Submitted</th>
                            <th>Status</th>
                            <th>Remarks</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $r):
                            $rc_count = $remark_counts[(int)$r['id']] ?? 0;
                        ?>
                        <tr>
                            <td><?= htmlspecialchars((string)$r['sid']) ?></td>
                            <td><strong><?= htmlspecialchars((string)$r['full_name']) ?></strong><br><span class="muted small"><?= htmlspecialchars((string)$r['email']) ?></span></td>
                            <td><?= htmlspecialchars((string)$r['department']) ?></td>
                            <td><?= htmlspecialchars((string)$r['submitted_at']) ?></td>
                            <td>
                                <?= uiu_status_badge((string)$r['status']) ?>
                                <?php if ($r['decider_name']): ?>
                                    <div class="muted small">by <?= htmlspecialchars((string)$r['decider_name']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a class="btn btn-sm btn-secondary" href="request-detail.php?id=<?= (int)$r['id'] ?>&back=<?= urlencode('/admin/admin-education.php?dept=' . $dept) ?>">
                                    <i class="fa-regular fa-comments"></i> View (<?= $rc_count ?>)
                                </a>
                            </td>
                            <td>
                                <details class="action-menu">
                                    <summary class="btn btn-sm btn-primary">Action ▾</summary>
                                    <div class="action-pop">
                                        <form method="POST" action="../backend/actions/admin-update-clearance.php" class="action-form">
                                            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                                            <input type="hidden" name="request_id" value="<?= (int)$r['id'] ?>">
                                            <input type="hidden" name="back" value="/admin/admin-education.php?dept=<?= htmlspecialchars($dept) ?>">
                                            <textarea name="remark" rows="2" placeholder="Optional remark"></textarea>
                                            <div class="action-buttons">
                                                <button class="btn btn-sm btn-success" name="status" value="approved" type="submit"><i class="fa-solid fa-check"></i> Approve</button>
                                                <button class="btn btn-sm btn-secondary" name="status" value="hold" type="submit"><i class="fa-solid fa-pause"></i> Hold</button>
                                                <button class="btn btn-sm btn-danger"  name="status" value="rejected" type="submit"><i class="fa-solid fa-xmark"></i> Reject</button>
                                                <button class="btn btn-sm btn-danger"  name="status" value="blocked" type="submit"><i class="fa-solid fa-ban"></i> Block</button>
                                            </div>
                                        </form>
                                    </div>
                                </details>
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
.action-menu{position:relative;}
.action-menu summary{list-style:none;cursor:pointer;}
.action-menu summary::-webkit-details-marker{display:none;}
.action-pop{position:absolute;right:0;top:100%;background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:12px;min-width:280px;z-index:30;box-shadow:0 8px 24px rgba(0,0,0,0.08);}
.action-pop textarea{width:100%;margin-bottom:8px;padding:8px;border:1px solid var(--border);border-radius:6px;font:inherit;background:var(--surface-2);color:var(--text);resize:vertical;}
.action-buttons{display:flex;gap:6px;flex-wrap:wrap;}
textarea{font:inherit;}
</style>
</body>
</html>
