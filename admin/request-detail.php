<?php


require_once __DIR__ . '/../backend/includes/auth.php';
require_once __DIR__ . '/../backend/includes/layout.php';

require_role_any(['admin', 'vc']);
$me = current_user();

$id = (int)($_GET['id'] ?? 0);
$back = (string)($_GET['back'] ?? '/admin/dashboard.php');
$req = null;
$remarks = [];
$student_docs = [];
$emergency_doc = null;

if ($id > 0) {
    try {
        $s = db()->prepare(
            'SELECT cr.*, u.full_name AS student_name, u.email AS student_email,
                    u.student_id AS sid, u.department AS student_dept,
                    du.full_name AS decider_name
             FROM clearance_requests cr
             JOIN users u ON u.id = cr.student_id
             LEFT JOIN users du ON du.id = cr.decided_by
             WHERE cr.id = ? LIMIT 1'
        );
        $s->execute([$id]);
        $req = $s->fetch();

        if ($req) {
            $r = db()->prepare(
                'SELECT cr.*, u.full_name AS author_name
                 FROM clearance_remarks cr
                 LEFT JOIN users u ON u.id = cr.author_id
                 WHERE cr.request_id = ?
                 ORDER BY cr.created_at'
            );
            $r->execute([$id]);
            $remarks = $r->fetchAll();

            $d = db()->prepare(
                'SELECT id, original_name, mime_type, size_bytes, uploaded_at, doc_type
                 FROM documents WHERE user_id = ? ORDER BY uploaded_at DESC'
            );
            $d->execute([(int)$req['student_id']]);
            $student_docs = $d->fetchAll();
        }
    } catch (Throwable $e) { }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request #<?= (int)$id ?> — UIU Compliance Management Platform</title>
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
            <a class="nav-item" href="emergency-inbox.php"><i class="fa-solid fa-circle-exclamation"></i> Emergency inbox</a>
            <a class="nav-item" href="user-management.php"><i class="fa-solid fa-user-gear"></i> User management</a>
        </nav>
    </aside>

    <main class="main">
        <div class="page-head">
            <h1>Clearance request #<?= (int)$id ?></h1>
            <p>Department: <strong><?= htmlspecialchars(ucfirst((string)($req['department'] ?? ''))) ?></strong></p>
            <div class="actions">
                <a class="btn btn-secondary" href="<?= htmlspecialchars($back) ?>"><i class="fa-solid fa-arrow-left"></i> Back</a>
            </div>
        </div>

        <?= uiu_flash_banner() ?>

        <?php if (!$req): ?>
            <section class="card"><div class="alert bad"><i class="fa-solid fa-circle-exclamation"></i><span>Request not found.</span></div></section>
        <?php else: ?>

        <div class="grid grid-2 mb-24">
            <section class="card">
                <h2>Student information</h2>
                <p>
                    <strong>Name:</strong> <?= htmlspecialchars((string)$req['student_name']) ?><br>
                    <strong>ID:</strong> <?= htmlspecialchars((string)$req['sid']) ?><br>
                    <strong>Email:</strong> <?= htmlspecialchars((string)$req['student_email']) ?><br>
                    <strong>Department:</strong> <?= htmlspecialchars((string)$req['student_dept']) ?>
                </p>
            </section>
            <section class="card">
                <h2>Request status</h2>
                <p>
                    <strong>Status:</strong> <?= uiu_status_badge((string)$req['status']) ?><br>
                    <strong>Submitted:</strong> <?= htmlspecialchars((string)$req['submitted_at']) ?><br>
                    <strong>Decided:</strong> <?= htmlspecialchars((string)($req['decided_at'] ?? '—')) ?><br>
                    <strong>Decided by:</strong> <?= htmlspecialchars((string)($req['decider_name'] ?? '—')) ?>
                </p>
                <?php if (!empty($req['reason'])): ?>
                    <h3>Student's reason</h3>
                    <p><?= nl2br(htmlspecialchars((string)$req['reason'])) ?></p>
                <?php endif; ?>
            </section>
        </div>

        <section class="card mb-24">
            <h2>Add remark or update status</h2>
            <form method="POST" action="../backend/actions/admin-update-clearance.php">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                <input type="hidden" name="request_id" value="<?= (int)$req['id'] ?>">
                <input type="hidden" name="back" value="<?= htmlspecialchars($back) ?>">
                <div class="field">
                    <label>Remark (optional)</label>
                    <textarea name="remark" rows="3" placeholder="Comment to the student…"></textarea>
                </div>
                <div class="cl-actions mt-16">
                    <button class="btn btn-success" type="submit" name="status" value="approved"><i class="fa-solid fa-check"></i> Approve</button>
                    <button class="btn btn-secondary" type="submit" name="status" value="hold"><i class="fa-solid fa-pause"></i> Hold</button>
                    <button class="btn btn-danger"  type="submit" name="status" value="rejected"><i class="fa-solid fa-xmark"></i> Reject</button>
                    <button class="btn btn-danger"  type="submit" name="status" value="blocked"><i class="fa-solid fa-ban"></i> Block</button>
                </div>
            </form>
        </section>

        <section class="card mb-24">
            <h2>Remark thread</h2>
            <?php if (!$remarks): ?>
                <p class="muted">No remarks yet.</p>
            <?php else: ?>
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
            <?php endif; ?>
        </section>

        <?php if (!empty($student_docs)): ?>
        <section class="card">
            <h2>Student's documents</h2>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Type</th><th>File</th><th>Size</th><th>Uploaded</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($student_docs as $d): ?>
                        <tr>
                            <td><?= htmlspecialchars((string)$d['doc_type']) ?></td>
                            <td><?= htmlspecialchars((string)$d['original_name']) ?></td>
                            <td><?= number_format(((int)$d['size_bytes'])/1024, 1) ?> KB</td>
                            <td><?= htmlspecialchars((string)$d['uploaded_at']) ?></td>
                            <td><a class="btn btn-sm btn-secondary" href="../backend/actions/download-document.php?id=<?= (int)$d['id'] ?>"><i class="fa-solid fa-download"></i> Download</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
        <?php endif; ?>

        <?php endif; ?>
    </main>
</div>
<style>
.notif-btn{position:relative;color:var(--text);text-decoration:none;padding:6px 10px;border-radius:8px;}
.remark-list{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:12px;}
.remark-list li{padding:12px 14px;background:var(--surface-2);border-radius:10px;}
.remark-meta{display:flex;justify-content:space-between;margin-bottom:4px;}
.remark-list p{margin:0;}
.muted{color:var(--text-muted);} .small{font-size:11px;} .mt-16{margin-top:16px;}
textarea{width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;background:var(--surface);color:var(--text);font:inherit;}
</style>
</body>
</html>
