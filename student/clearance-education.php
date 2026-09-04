<?php
/**
 * Per-department clearance apply page.
 * Generic handler used by all 5 clearance-*.php pages via ?dept=.
 */
require_once __DIR__ . '/../backend/includes/auth.php';
require_once __DIR__ . '/../backend/includes/layout.php';
require_role('student');
$me = current_user();

$valid = [
    'education' => ['label' => 'Education', 'icon' => 'fa-graduation-cap', 'css' => 'page7.css'],
    'library'   => ['label' => 'Library',   'icon' => 'fa-book',           'css' => 'page8.css'],
    'transport' => ['label' => 'Transport', 'icon' => 'fa-bus',            'css' => 'page9.css'],
    'medical'   => ['label' => 'Medical',   'icon' => 'fa-hospital',       'css' => 'page10.css'],
    'hostel'    => ['label' => 'Hostel',    'icon' => 'fa-house-chimney',  'css' => 'page11.css'],
];
$dept = (string)($_GET['dept'] ?? 'education');
if (!isset($valid[$dept])) { $dept = 'education'; }
$meta = $valid[$dept];

$status = 'pending';
$reason = '';
$remarks = [];
$decided_at = null;
try {
    $s = db()->prepare('SELECT status, reason, decided_at FROM clearance_requests WHERE student_id = ? AND department = ? LIMIT 1');
    $s->execute([(int)$me['id'], $dept]);
    $row = $s->fetch();
    if ($row) {
        $status = $row['status'];
        $reason = (string)($row['reason'] ?? '');
        $decided_at = $row['decided_at'];
    }
    $r = db()->prepare(
        'SELECT cr.body, cr.created_at, u.full_name AS author_name
         FROM clearance_remarks cr
         LEFT JOIN users u ON u.id = cr.author_id
         WHERE cr.request_id IN (SELECT id FROM clearance_requests WHERE student_id = ? AND department = ?)
         ORDER BY cr.created_at DESC'
    );
    $r->execute([(int)$me['id'], $dept]);
    $remarks = $r->fetchAll();
} catch (Throwable $e) { /* ignore */ }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($meta['label']) ?> Clearance — UIU Compliance Management Platform</title>
    <link rel="stylesheet" href="../assets/css/theme.css">
    <link rel="stylesheet" href="../assets/css/<?= htmlspecialchars($meta['css']) ?>">
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
            <?php foreach ($valid as $k => $m): ?>
                <a class="nav-item <?= $k === $dept ? 'active' : '' ?>" href="clearance-education.php?dept=<?= $k ?>">
                    <i class="fa-solid <?= $m['icon'] ?>"></i> <?= $m['label'] ?>
                </a>
            <?php endforeach; ?>
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
            <a class="nav-item" href="help.php"><i class="fa-regular fa-circle-question"></i> Help center</a>
        </nav>
    </aside>

    <main class="main">
        <div class="cl-hero">
            <div class="cl-hero-icon"><i class="fa-solid <?= $meta['icon'] ?>"></i></div>
            <div>
                <h1><?= htmlspecialchars($meta['label']) ?> Clearance</h1>
                <p>Apply for or track your <?= htmlspecialchars($meta['label']) ?> department clearance.</p>
            </div>
            <div class="cl-status">
                <?= iu_badge($status) ?>
                <?php if ($decided_at): ?>
                    <div class="muted small mt-8">Decided: <?= htmlspecialchars($decided_at) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <?= uiu_flash_banner() ?>

        <section class="card mb-24">
            <div class="card-head">
                <div>
                    <h2>Application form</h2>
                    <p>Your details are pre-filled from your account.</p>
                </div>
            </div>
            <form method="POST" action="../backend/actions/student-submit-clearance.php">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                <input type="hidden" name="dept" value="<?= htmlspecialchars($dept) ?>">
                <div class="grid grid-2">
                    <div class="field">
                        <label>Full name</label>
                        <div class="input"><i class="fa-regular fa-user"></i>
                            <input type="text" value="<?= htmlspecialchars((string)$me['full_name']) ?>" readonly>
                        </div>
                    </div>
                    <div class="field">
                        <label>Student ID</label>
                        <div class="input"><i class="fa-solid fa-id-card"></i>
                            <input type="text" value="<?= htmlspecialchars((string)$me['student_id']) ?>" readonly>
                        </div>
                    </div>
                    <div class="field">
                        <label>Department</label>
                        <div class="input"><i class="fa-solid fa-briefcase"></i>
                            <input type="text" value="<?= htmlspecialchars((string)$me['department']) ?>" readonly>
                        </div>
                    </div>
                    <div class="field">
                        <label>Email</label>
                        <div class="input"><i class="fa-regular fa-envelope"></i>
                            <input type="email" value="<?= htmlspecialchars((string)$me['email']) ?>" readonly>
                        </div>
                    </div>
                </div>
                <div class="field mt-16">
                    <label for="reason">Reason for clearance</label>
                    <textarea id="reason" name="reason" rows="5"
                              placeholder="Explain why you need this clearance (optional but recommended)…"><?= htmlspecialchars($reason) ?></textarea>
                </div>
                <div class="cl-actions mt-16">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-paper-plane"></i>
                        <?= $status === 'pending' ? 'Submit request' : 'Re-submit / update' ?>
                    </button>
                    <a class="btn btn-secondary" href="dashboard.php">Back</a>
                </div>
            </form>
        </section>

        <?php if (!empty($remarks)): ?>
        <section class="card">
            <div class="card-head">
                <div>
                    <h2>Remarks from <?= htmlspecialchars($meta['label']) ?> department</h2>
                    <p>Comments left by reviewers on your request.</p>
                </div>
            </div>
            <ul class="remark-list">
                <?php foreach ($remarks as $rm): ?>
                    <li>
                        <div class="remark-meta">
                            <strong><?= htmlspecialchars((string)($rm['author_name'] ?? 'Reviewer')) ?></strong>
                            <span class="muted small"><?= htmlspecialchars((string)$rm['created_at']) ?></span>
                        </div>
                        <p><?= nl2br(htmlspecialchars((string)$rm['body'])) ?></p>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
        <?php endif; ?>
    </main>
</div>

<style>
.notif-btn{position:relative;color:var(--text);text-decoration:none;padding:6px 10px;border-radius:8px;}
.notif-btn:hover{background:var(--surface-2);}
.muted{color:var(--text-muted);} .small{font-size:12px;} .mt-8{margin-top:8px;} .mt-16{margin-top:16px;}
.remark-list{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:12px;}
.remark-list li{padding:12px 14px;background:var(--surface-2);border-radius:10px;}
.remark-meta{display:flex;justify-content:space-between;margin-bottom:4px;}
.remark-list p{margin:0;}
textarea{width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;background:var(--surface);color:var(--text);font:inherit;}
</style>

</body>
</html>
<?php
function iu_badge(string $status): string {
    $map = [
        'pending'  => ['warn', 'Pending'],
        'approved' => ['ok',   'Approved'],
        'rejected' => ['bad',  'Rejected'],
        'hold'     => ['warn', 'On hold'],
        'blocked'  => ['bad',  'Blocked'],
    ];
    $s = $map[$status] ?? ['info', ucfirst($status)];
    return '<span class="badge ' . htmlspecialchars($s[0]) . '">' . htmlspecialchars($s[1]) . '</span>';
}
?>
