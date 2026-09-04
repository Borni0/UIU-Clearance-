<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard — UIU Compliance Management Platform</title>
    <link rel="stylesheet" href="../assets/css/theme.css">
    <link rel="stylesheet" href="../assets/css/student-dashboard.css">
    <script src="https://kit.fontawesome.com/063f939101.js" crossorigin="anonymous"></script>
</head>
<?php
require_once __DIR__ . '/../backend/includes/auth.php';
require_once __DIR__ . '/../backend/includes/layout.php';
require_role('student');
$current = current_user();
$unread = iu_unread_count_safe($current);
$depts = [
    'education' => ['icon' => 'fa-graduation-cap',  'label' => 'Education',  'page' => 'clearance-education.php'],
    'library'   => ['icon' => 'fa-book',           'label' => 'Library',    'page' => 'clearance-library.php'],
    'transport' => ['icon' => 'fa-bus',            'label' => 'Transport',  'page' => 'clearance-transport.php'],
    'medical'   => ['icon' => 'fa-hospital',       'label' => 'Medical',    'page' => 'clearance-medical.php'],
    'hostel'    => ['icon' => 'fa-house-chimney',  'label' => 'Hostel',     'page' => 'clearance-hostel.php'],
];

$by_dept = [];
try {
    $stmt = db()->prepare('SELECT department, status FROM clearance_requests WHERE student_id = ?');
    $stmt->execute([(int)$current['id']]);
    foreach ($stmt->fetchAll() as $r) {
        $by_dept[$r['department']] = $r['status'];
    }
} catch (Throwable $e) { /* ignore */ }

// Recent notifications (top 5)
$notifs = [];
try {
    $n = db()->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5');
    $n->execute([(int)$current['id']]);
    $notifs = $n->fetchAll();
} catch (Throwable $e) { /* ignore */ }

function iu_unread_count_safe($u): int {
    return $u ? uiu_unread_count((int)$u['id']) : 0;
}
?>

<body class="plain-bg">

<header class="topbar">
    <div class="brand">
        <img src="../assets/images/UIU%20LOGO.png" alt="UIU Logo">
        <div class="brand-text">UIU Compliance<span>Management Platform</span></div>
    </div>
    <div class="right">
        <a class="notif-btn" href="notifications.php" title="Notifications">
            <i class="fa-regular fa-bell"></i>
            <?php if ($unread > 0): ?>
                <span class="notif-dot"><?= (int)$unread ?></span>
            <?php endif; ?>
        </a>
        <span class="role-badge"><i class="fa-solid fa-graduation-cap"></i> Student</span>
        <a class="logout-btn" href="../backend/actions/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Sign out</a>
    </div>
</header>

<div class="layout">

    <aside class="sidebar">
        <nav class="nav-section">
            <h3>Overview</h3>
            <a class="nav-item active" href="dashboard.php"><i class="fa-solid fa-house"></i> Dashboard</a>
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
            <a class="nav-item" href="emergency-request.php"><i class="fa-solid fa-circle-exclamation"></i> Emergency request</a>
            <a class="nav-item" href="emergency-page.php"><i class="fa-solid fa-life-ring"></i> My emergencies</a>
            <a class="nav-item" href="help.php"><i class="fa-regular fa-circle-question"></i> Help center</a>
        </nav>
    </aside>

    <main class="main">

        <div class="page-head">
            <h1>Welcome back, <?= htmlspecialchars(explode(' ', (string)$current['full_name'])[0]) ?>.</h1>
            <p>
                <strong>ID:</strong> <?= htmlspecialchars((string)$current['student_id']) ?>
                &nbsp;·&nbsp;
                <strong>Department:</strong> <?= htmlspecialchars((string)$current['department']) ?>
            </p>
        </div>

        <?= uiu_flash_banner() ?>

        <div class="grid grid-4 mb-24">
            <?php foreach ($depts as $key => $meta):
                $status = $by_dept[$key] ?? 'pending';
                $cls = $status === 'approved' ? 'ok' : ($status === 'rejected' ? 'bad' : ($status === 'hold' ? 'warn' : 'warn'));
                $sub = $status === 'approved' ? 'Cleared' : ($status === 'rejected' ? 'Action needed' : ($status === 'hold' ? 'On hold' : 'Awaiting review'));
            ?>
            <div class="stat <?= $cls ?>">
                <div class="icon"><i class="fa-solid <?= $meta['icon'] ?>"></i></div>
                <div class="label"><?= $meta['label'] ?></div>
                <div class="value"><?= htmlspecialchars(ucfirst($status)) ?></div>
                <div class="sub"><?= htmlspecialchars($sub) ?></div>
            </div>
            <?php endforeach; ?>
        </div>

        <section class="card mb-24">
            <div class="card-head">
                <div>
                    <h2>Apply for clearance</h2>
                    <p>Pick a department to start or continue a clearance request.</p>
                </div>
            </div>
            <div class="grid grid-5">
                <?php foreach ($depts as $key => $meta): ?>
                    <a class="action-tile" href="<?= $meta['page'] ?>">
                        <div class="action-tile-icon"><i class="fa-solid <?= $meta['icon'] ?>"></i></div>
                        <div class="action-tile-name"><?= $meta['label'] ?></div>
                        <div class="action-tile-sub"><?= iu_status_label($by_dept[$key] ?? 'pending') ?></div>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <div class="grid grid-2">
            <section class="card">
                <div class="card-head">
                    <div>
                        <h2>Quick actions</h2>
                        <p>Common things to do from here.</p>
                    </div>
                </div>
                <div class="quick-actions">
                    <a class="quick-action" href="clearance-education.php"><i class="fa-solid fa-file-circle-plus"></i> Apply for clearance</a>
                    <a class="quick-action" href="upload-document.php"><i class="fa-solid fa-cloud-arrow-up"></i> Upload a document</a>
                    <a class="quick-action" href="track-status.php"><i class="fa-solid fa-location-dot"></i> Track application status</a>
                    <a class="quick-action" href="all-documents.php"><i class="fa-regular fa-folder-open"></i> View all documents</a>
                    <a class="quick-action" href="emergency-request.php"><i class="fa-solid fa-circle-exclamation"></i> Submit emergency request</a>
                </div>
            </section>

            <section class="card">
                <div class="card-head">
                    <div>
                        <h2>Recent notifications</h2>
                        <p>Latest updates from admins and the VC.</p>
                    </div>
                    <a class="btn btn-secondary btn-sm" href="notifications.php">View all</a>
                </div>
                <?php if (empty($notifs)): ?>
                    <p class="muted">No notifications yet.</p>
                <?php else: ?>
                    <ul class="notif-list">
                        <?php foreach ($notifs as $n): ?>
                            <li class="notif-item <?= $n['is_read'] ? '' : 'unread' ?>">
                                <div class="notif-icon">
                                    <i class="fa-solid <?= iu_notif_icon((string)$n['kind']) ?>"></i>
                                </div>
                                <div class="notif-body">
                                    <div class="notif-title"><?= htmlspecialchars($n['title']) ?></div>
                                    <?php if (!empty($n['body'])): ?>
                                        <div class="notif-text"><?= htmlspecialchars($n['body']) ?></div>
                                    <?php endif; ?>
                                    <div class="notif-time"><?= htmlspecialchars($n['created_at']) ?></div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </section>
        </div>

    </main>
</div>

<style>
.notif-btn{position:relative;color:var(--text);text-decoration:none;padding:6px 10px;border-radius:8px;}
.notif-btn:hover{background:var(--surface-2);}
.notif-dot{position:absolute;top:0;right:0;background:var(--bad);color:#fff;border-radius:999px;min-width:18px;height:18px;font-size:11px;display:flex;align-items:center;justify-content:center;padding:0 5px;}
.notif-list{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:8px;}
.notif-item{display:flex;gap:12px;padding:10px 12px;border-radius:10px;background:var(--surface-2);}
.notif-item.unread{background:rgba(37,99,235,0.08);}
.notif-icon{width:32px;height:32px;border-radius:8px;background:var(--surface);display:flex;align-items:center;justify-content:center;color:var(--brand,#2563EB);flex-shrink:0;}
.notif-body{flex:1;min-width:0;}
.notif-title{font-weight:600;font-size:14px;}
.notif-text{font-size:13px;color:var(--text-muted);margin-top:2px;}
.notif-time{font-size:11px;color:var(--text-muted);margin-top:4px;}
.muted{color:var(--text-muted);}
</style>

</body>
</html>

<?php
function iu_status_label(string $s): string {
    return match($s) {
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'hold'     => 'On hold',
        'blocked'  => 'Blocked',
        default    => 'Not submitted',
    };
}
function iu_notif_icon(string $kind): string {
    return match($kind) {
        'approval'         => 'fa-circle-check',
        'rejection'        => 'fa-circle-xmark',
        'admin_remark'     => 'fa-comment',
        'status_update'    => 'fa-rotate',
        'request_submitted'=> 'fa-paper-plane',
        'emergency'        => 'fa-circle-exclamation',
        default            => 'fa-bell',
    };
}
