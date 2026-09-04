<?php
require_once __DIR__ . '/../backend/includes/auth.php';
require_once __DIR__ . '/../backend/includes/layout.php';
require_role('vc');
$me = current_user();

$stats = [
    'students' => 0, 'approved' => 0, 'pending' => 0, 'rejected' => 0, 'hold' => 0, 'blocked' => 0,
    'emerg_total' => 0, 'emerg_pending' => 0, 'emerg_approved' => 0, 'emerg_rejected' => 0,
    'completed' => 0, 'certs' => 0,
];
try {
    foreach (db()->query("SELECT status, COUNT(*) AS n FROM clearance_requests GROUP BY status")->fetchAll() as $r) {
        $stats[(string)$r['status']] = (int)$r['n'];
    }
    $stats['students'] = (int) db()->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
    foreach (db()->query("SELECT status, COUNT(*) AS n FROM emergency_requests GROUP BY status")->fetchAll() as $r) {
        $stats['emerg_' . (string)$r['status']] = (int)$r['n'];
        $stats['emerg_total'] += (int)$r['n'];
    }
    // "Completed" = student has all 5 dept clearances approved
    $stats['completed'] = (int) db()->query(
        "SELECT COUNT(*) FROM (
            SELECT student_id FROM clearance_requests
            WHERE status = 'approved'
            GROUP BY student_id
            HAVING COUNT(DISTINCT department) = 5
         ) x"
    )->fetchColumn();
    $stats['certs'] = (int) db()->query("SELECT COUNT(*) FROM certificates")->fetchColumn();
} catch (Throwable $e) { /* ignore */ }

// Per-department breakdown (for the bar chart)
$by_dept = ['education' => 0, 'library' => 0, 'transport' => 0, 'medical' => 0, 'hostel' => 0];
try {
    $stmt = db()->query(
        "SELECT department,
                SUM(status = 'approved') AS approved,
                SUM(status = 'pending')  AS pending,
                SUM(status = 'rejected') AS rejected,
                SUM(status = 'hold')     AS holdd,
                SUM(status = 'blocked')  AS blocked
         FROM clearance_requests GROUP BY department"
    );
    foreach ($stmt->fetchAll() as $r) {
        $by_dept[(string)$r['department']] = [
            'approved' => (int)$r['approved'],
            'pending'  => (int)$r['pending'],
            'rejected' => (int)$r['rejected'],
            'hold'     => (int)$r['holdd'],
            'blocked'  => (int)$r['blocked'],
        ];
    }
} catch (Throwable $e) { /* ignore */ }

// Recent activity
$activity = [];
try {
    $stmt = db()->prepare(
        "SELECT al.*, u.full_name AS actor_name
         FROM activity_log al
         LEFT JOIN users u ON u.id = al.actor_id
         ORDER BY al.created_at DESC LIMIT 10"
    );
    $stmt->execute();
    $activity = $stmt->fetchAll();
} catch (Throwable $e) { /* ignore */ }

// Eligible students (all 5 approved but no certificate yet)
$eligible = [];
try {
    $stmt = db()->query(
        "SELECT u.id, u.full_name, u.student_id, u.department
         FROM users u
         WHERE u.role = 'student' AND u.status = 'active'
           AND NOT EXISTS (SELECT 1 FROM certificates c WHERE c.student_id = u.id)
           AND (SELECT COUNT(DISTINCT department) FROM clearance_requests
                WHERE student_id = u.id AND status = 'approved') = 5
         ORDER BY u.full_name LIMIT 50"
    );
    $eligible = $stmt->fetchAll();
} catch (Throwable $e) { /* ignore */ }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vice Chancellor — UIU Compliance Management Platform</title>
    <link rel="stylesheet" href="../assets/css/theme.css">
    <link rel="stylesheet" href="../assets/css/page17.css">
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
            <a class="nav-item active" href="dashboard.php"><i class="fa-solid fa-gauge-high"></i> Dashboard</a>
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
            <a class="nav-item" href="certificates.php"><i class="fa-solid fa-certificate"></i> Certificates</a>
        </nav>
        <nav class="nav-section">
            <h3>Administration</h3>
            <a class="nav-item" href="user-management.php"><i class="fa-solid fa-user-gear"></i> User management</a>
        </nav>
    </aside>

    <main class="main">
        <div class="page-head">
            <h1>Vice Chancellor's dashboard</h1>
            <p>Final approval queue and university-wide clearance overview.</p>
        </div>

        <?= uiu_flash_banner() ?>

        <div class="grid grid-4 mb-24">
            <div class="stat"><div class="icon"><i class="fa-solid fa-user-graduate"></i></div><div class="label">Total students</div><div class="value"><?= $stats['students'] ?></div></div>
            <div class="stat warn"><div class="icon"><i class="fa-regular fa-clock"></i></div><div class="label">Pending</div><div class="value"><?= $stats['pending'] + $stats['hold'] ?></div></div>
            <div class="stat ok"><div class="icon"><i class="fa-solid fa-circle-check"></i></div><div class="label">Approved</div><div class="value"><?= $stats['approved'] ?></div></div>
            <div class="stat bad"><div class="icon"><i class="fa-solid fa-circle-xmark"></i></div><div class="label">Rejected / Blocked</div><div class="value"><?= $stats['rejected'] + $stats['blocked'] ?></div></div>
        </div>

        <div class="grid grid-3 mb-24">
            <div class="stat ok"><div class="icon"><i class="fa-solid fa-trophy"></i></div><div class="label">Completed students</div><div class="value"><?= $stats['completed'] ?></div><div class="sub">All 5 departments approved</div></div>
            <div class="stat"><div class="icon"><i class="fa-solid fa-certificate"></i></div><div class="label">Certificates issued</div><div class="value"><?= $stats['certs'] ?></div></div>
            <div class="stat warn"><div class="icon"><i class="fa-solid fa-circle-exclamation"></i></div><div class="label">Pending emergencies</div><div class="value"><?= $stats['emerg_pending'] ?> / <?= $stats['emerg_total'] ?></div></div>
        </div>

        <div class="grid grid-2 mb-24">
            <section class="card">
                <div class="card-head">
                    <div><h2>Department breakdown</h2><p>Approved / Pending / Rejected per department.</p></div>
                </div>
                <div class="chart">
                    <?php
                    $max = 1;
                    foreach ($by_dept as $d) { if (is_array($d)) { $t = array_sum($d); if ($t > $max) $max = $t; } }
                    foreach ($by_dept as $key => $d):
                        if (!is_array($d)) continue;
                        $total = array_sum($d);
                        $pct_total = $max > 0 ? (int)round($total * 100 / $max) : 0;
                    ?>
                    <div class="chart-row">
                        <div class="chart-label"><?= htmlspecialchars(ucfirst($key)) ?></div>
                        <div class="chart-bars">
                            <div class="bar approved"  style="width:<?= $max>0?(int)round($d['approved'] *100/ $max):0 ?>%"  title="Approved: <?= $d['approved'] ?>"></div>
                            <div class="bar pending"   style="width:<?= $max>0?(int)round($d['pending']  *100/ $max):0 ?>%"  title="Pending: <?= $d['pending'] ?>"></div>
                            <div class="bar rejected"  style="width:<?= $max>0?(int)round($d['rejected'] *100/ $max):0 ?>%"  title="Rejected: <?= $d['rejected'] ?>"></div>
                            <div class="bar hold"      style="width:<?= $max>0?(int)round($d['hold']     *100/ $max):0 ?>%"  title="Hold: <?= $d['hold'] ?>"></div>
                        </div>
                        <div class="chart-total"><?= $total ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="chart-legend">
                    <span><span class="dot approved"></span> Approved</span>
                    <span><span class="dot pending"></span> Pending</span>
                    <span><span class="dot rejected"></span> Rejected</span>
                    <span><span class="dot hold"></span> On hold</span>
                </div>
            </section>

            <section class="card">
                <div class="card-head">
                    <div><h2>Activity tracking</h2><p>Latest actions across the platform.</p></div>
                    <a class="btn btn-secondary btn-sm" href="activity-log.php">View all</a>
                </div>
                <?php if (!$activity): ?>
                    <p class="muted">No activity recorded yet.</p>
                <?php else: ?>
                    <ul class="activity-list">
                        <?php foreach ($activity as $a): ?>
                            <li>
                                <div class="act-icon"><i class="fa-solid <?= activity_icon((string)$a['action']) ?>"></i></div>
                                <div class="act-body">
                                    <div><?= htmlspecialchars((string)($a['detail'] ?? $a['action'])) ?></div>
                                    <div class="muted small">
                                        <?= htmlspecialchars((string)($a['actor_name'] ?? 'System')) ?>
                                        · <?= htmlspecialchars((string)$a['created_at']) ?>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </section>
        </div>

        <section class="card mb-24">
            <div class="card-head">
                <div>
                    <h2>Generate clearance certificate</h2>
                    <p>Students whose all 5 departments are approved are eligible. Click to issue.</p>
                </div>
                <a class="btn btn-secondary btn-sm" href="certificates.php">All certificates</a>
            </div>
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
                                        <input type="hidden" name="back" value="/vc/dashboard.php">
                                        <button class="btn btn-sm btn-success" type="submit"><i class="fa-solid fa-certificate"></i> Issue certificate</button>
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
            <div class="card-head">
                <div>
                    <h2>Overall system management</h2>
                    <p>User accounts, certificates, and audit.</p>
                </div>
            </div>
            <div class="quick-actions">
                <a class="quick-action" href="user-management.php?tab=students"><i class="fa-solid fa-user-graduate"></i> Manage students</a>
                <a class="quick-action" href="user-management.php?tab=admins"><i class="fa-solid fa-user-shield"></i> Manage admins</a>
                <a class="quick-action" href="certificates.php"><i class="fa-solid fa-certificate"></i> Issued certificates</a>
                <a class="quick-action" href="activity-log.php"><i class="fa-solid fa-clock-rotate-left"></i> Full activity log</a>
                <a class="quick-action" href="emergency-inbox.php"><i class="fa-solid fa-circle-exclamation"></i> Emergency inbox</a>
            </div>
        </section>

    </main>
</div>

<style>
.notif-btn{position:relative;color:var(--text);text-decoration:none;padding:6px 10px;border-radius:8px;}
.muted{color:var(--text-muted);} .small{font-size:11px;}

.chart{display:flex;flex-direction:column;gap:14px;}
.chart-row{display:grid;grid-template-columns:90px 1fr 40px;gap:12px;align-items:center;}
.chart-label{font-weight:600;font-size:13px;}
.chart-bars{display:flex;height:18px;border-radius:6px;overflow:hidden;background:var(--surface-2);}
.bar{height:100%;}
.bar.approved{background:#16a34a;}
.bar.pending{background:#f59e0b;}
.bar.rejected{background:#dc2626;}
.bar.hold{background:#7c3aed;}
.chart-total{font-weight:700;text-align:right;}
.chart-legend{display:flex;gap:16px;flex-wrap:wrap;font-size:12px;margin-top:14px;}
.chart-legend .dot{display:inline-block;width:10px;height:10px;border-radius:50%;margin-right:6px;vertical-align:middle;}
.chart-legend .approved{background:#16a34a;}
.chart-legend .pending{background:#f59e0b;}
.chart-legend .rejected{background:#dc2626;}
.chart-legend .hold{background:#7c3aed;}

.activity-list{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:8px;}
.activity-list li{display:flex;gap:10px;align-items:flex-start;padding:8px;border-radius:8px;background:var(--surface-2);}
.act-icon{width:32px;height:32px;border-radius:8px;background:var(--surface);display:flex;align-items:center;justify-content:center;color:var(--brand,#2563EB);flex-shrink:0;}
.act-body{flex:1;}
</style>
</body>
</html>
<?php
function activity_icon(string $action): string {
    return match(true) {
        str_starts_with($action, 'clearance_approved')  => 'fa-circle-check',
        str_starts_with($action, 'clearance_rejected')  => 'fa-circle-xmark',
        str_starts_with($action, 'clearance_blocked')   => 'fa-ban',
        str_starts_with($action, 'clearance_hold')      => 'fa-pause',
        str_starts_with($action, 'clearance_submitted') => 'fa-paper-plane',
        str_starts_with($action, 'emergency_approved')  => 'fa-circle-check',
        str_starts_with($action, 'emergency_rejected')  => 'fa-circle-xmark',
        str_starts_with($action, 'emergency_submitted') => 'fa-circle-exclamation',
        str_starts_with($action, 'certificate_issued')  => 'fa-certificate',
        default => 'fa-circle-dot',
    };
}
?>
