<?php
require_once __DIR__ . '/../backend/includes/auth.php';
require_once __DIR__ . '/../backend/includes/layout.php';
require_role('student');
$me = current_user();

$depts = [
    'education' => 'Education',
    'library'   => 'Library',
    'transport' => 'Transport',
    'medical'   => 'Medical',
    'hostel'    => 'Hostel',
];

$rows = [];
try {
    $s = db()->prepare(
        'SELECT cr.department, cr.status, cr.submitted_at, cr.decided_at,
                u.full_name AS decider_name
         FROM clearance_requests cr
         LEFT JOIN users u ON u.id = cr.decided_by
         WHERE cr.student_id = ?
         ORDER BY cr.department'
    );
    $s->execute([(int)$me['id']]);
    $rows = $s->fetchAll();
} catch (Throwable $e) { /* ignore */ }

$by_dept = [];
foreach ($rows as $r) { $by_dept[$r['department']] = $r; }

$approved = 0; $pending = 0; $rejected = 0; $total = count($depts);
foreach ($depts as $k => $label) {
    $st = $by_dept[$k]['status'] ?? 'pending';
    if ($st === 'approved') $approved++;
    elseif (in_array($st, ['rejected','blocked'], true)) $rejected++;
    else $pending++;
}
$pct = $total > 0 ? (int)round($approved * 100 / $total) : 0;

$timeline = [
    ['Submitted',  'pending'],
    ['Education',  $by_dept['education']['status'] ?? 'pending'],
    ['Library',    $by_dept['library']['status']   ?? 'pending'],
    ['Transport',  $by_dept['transport']['status'] ?? 'pending'],
    ['Medical',    $by_dept['medical']['status']   ?? 'pending'],
    ['Hostel',     $by_dept['hostel']['status']    ?? 'pending'],
    ['VC issues certificate', 'pending'],
];

// Find the first non-approved step index.
$first_blocking = null;
foreach ($timeline as $i => $step) {
    if (!in_array($step[1], ['approved'], true)) { $first_blocking = $i; break; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Status — UIU Compliance Management Platform</title>
    <link rel="stylesheet" href="../assets/css/theme.css">
    <link rel="stylesheet" href="../assets/css/track-status.css">
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
            <a class="nav-item active" href="track-status.php"><i class="fa-solid fa-location-dot"></i> Track status</a>
        </nav>
        <nav class="nav-section">
            <h3>Support</h3>
            <a class="nav-item" href="emergency-request.php"><i class="fa-solid fa-circle-exclamation"></i> Emergency request</a>
            <a class="nav-item" href="help.php"><i class="fa-regular fa-circle-question"></i> Help center</a>
        </nav>
    </aside>

    <main class="main">
        <div class="page-head">
            <h1>Track clearance status</h1>
            <p>
                <strong>Student:</strong> <?= htmlspecialchars((string)$me['full_name']) ?>
                &nbsp;·&nbsp;
                <strong>ID:</strong> <?= htmlspecialchars((string)$me['student_id']) ?>
            </p>
        </div>

        <?= uiu_flash_banner() ?>

        <section class="card mb-24">
            <div class="card-head">
                <div>
                    <h2>Overall progress</h2>
                    <p><?= $approved ?> of <?= $total ?> departments approved.</p>
                </div>
                <span class="big-pct"><?= $pct ?>%</span>
            </div>
            <div class="progress-bar"><div class="progress-fill" style="width: <?= $pct ?>%;"></div></div>
        </section>

        <div class="grid grid-3 mb-24">
            <div class="stat ok"><div class="icon"><i class="fa-solid fa-circle-check"></i></div><div class="label">Approved</div><div class="value"><?= $approved ?></div></div>
            <div class="stat warn"><div class="icon"><i class="fa-regular fa-clock"></i></div><div class="label">Pending</div><div class="value"><?= $pending ?></div></div>
            <div class="stat bad"><div class="icon"><i class="fa-solid fa-circle-xmark"></i></div><div class="label">Rejected / Blocked</div><div class="value"><?= $rejected ?></div></div>
        </div>

        <section class="card mb-24">
            <div class="card-head">
                <div><h2>Department-wise status</h2><p>Approved, pending, or rejected by each department.</p></div>
            </div>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Department</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th>Decided</th>
                            <th>Decided by</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($depts as $k => $label):
                            $r = $by_dept[$k] ?? null;
                            $st = $r['status'] ?? 'not_submitted';
                        ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($label) ?></strong></td>
                            <td>
                                <?php if ($st === 'not_submitted'): ?>
                                    <span class="badge">Not submitted</span>
                                <?php else: ?>
                                    <?= uiu_status_badge($st) ?>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($r['submitted_at'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($r['decided_at'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($r['decider_name'] ?? '—') ?></td>
                            <td><a class="btn btn-sm btn-secondary" href="clearance-education.php?dept=<?= $k ?>">View</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="card">
            <div class="card-head">
                <div><h2>Application timeline</h2><p>Each step in your clearance journey.</p></div>
            </div>
            <ol class="timeline">
                <?php foreach ($timeline as $i => $step):
                    $status = $step[1];
                    $cls = 'pending';
                    if ($status === 'approved') $cls = 'done';
                    elseif ($first_blocking === $i) $cls = 'current';
                    elseif ($i < ($first_blocking ?? 999)) $cls = 'done';
                ?>
                <li class="step <?= $cls ?>">
                    <div class="step-dot">
                        <?php if ($cls === 'done'): ?><i class="fa-solid fa-check"></i>
                        <?php elseif ($cls === 'current'): ?><i class="fa-regular fa-clock"></i>
                        <?php else: ?><i class="fa-solid fa-circle"></i><?php endif; ?>
                    </div>
                    <div class="step-body">
                        <div class="flex-between">
                            <h4><?= htmlspecialchars($step[0]) ?></h4>
                            <?php if ($status === 'pending' && $i > 0): ?>
                                <span class="badge warn">Pending</span>
                            <?php elseif ($status === 'approved'): ?>
                                <span class="badge ok">Approved</span>
                            <?php else: ?>
                                <span class="badge warn">Pending</span>
                            <?php endif; ?>
                        </div>
                        <p>
                            <?php if ($i === 0): ?>Your clearance request has been submitted.
                            <?php elseif (in_array($status, ['pending','hold','blocked','rejected'], true)): ?>Waiting for <?= htmlspecialchars(strtolower($step[0])) ?> department action.
                            <?php else: ?>Cleared by <?= htmlspecialchars(strtolower($step[0])) ?>.
                            <?php endif; ?>
                        </p>
                    </div>
                </li>
                <?php endforeach; ?>
            </ol>
        </section>
    </main>
</div>
</body>
</html>
