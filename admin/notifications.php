<?php
require_once __DIR__ . '/../backend/includes/auth.php';
require_once __DIR__ . '/../backend/includes/layout.php';
require_role_any(['admin', 'vc']);
$me = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (string)($_POST['id'] ?? '');
    try {
        if ($id === 'all') {
            $s = db()->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ?');
            $s->execute([(int)$me['id']]);
        } else {
            $nid = (int)$id;
            if ($nid > 0) {
                $s = db()->prepare('UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?');
                $s->execute([$nid, (int)$me['id']]);
            }
        }
    } catch (Throwable $e) {  }
    header('Location: ' . ($_POST['back'] ?? '/admin/notifications.php'));
    exit;
}

$rows = [];
try {
    $s = db()->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 100');
    $s->execute([(int)$me['id']]);
    $rows = $s->fetchAll();
} catch (Throwable $e) {}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications — UIU Compliance Management Platform</title>
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
            <h1>Notifications</h1>
            <p>Requests, status updates, and emergency alerts.</p>
            <?php if (!empty($rows)): ?>
                <div class="actions">
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                        <input type="hidden" name="id" value="all">
                        <input type="hidden" name="back" value="/admin/notifications.php">
                        <button class="btn btn-secondary btn-sm" type="submit"><i class="fa-solid fa-check-double"></i> Mark all read</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>

        <?= uiu_flash_banner() ?>

        <?php if (empty($rows)): ?>
            <section class="card"><div class="empty"><i class="fa-regular fa-bell-slash"></i><h3>No notifications yet</h3></div></section>
        <?php else: ?>
            <section class="card">
                <ul class="notif-list">
                    <?php foreach ($rows as $n): ?>
                        <li class="notif-item <?= $n['is_read'] ? '' : 'unread' ?>">
                            <div class="notif-icon"><i class="fa-solid fa-bell"></i></div>
                            <div class="notif-body">
                                <div class="notif-title"><?= htmlspecialchars($n['title']) ?></div>
                                <?php if (!empty($n['body'])): ?><div class="notif-text"><?= htmlspecialchars($n['body']) ?></div><?php endif; ?>
                                <div class="notif-time"><?= htmlspecialchars($n['created_at']) ?></div>
                                <?php if (!empty($n['link'])): ?>
                                    <a class="btn btn-sm btn-secondary mt-8" href="<?= htmlspecialchars($n['link']) ?>">Open</a>
                                <?php endif; ?>
                            </div>
                            <?php if (!$n['is_read']): ?>
                                <form method="POST" style="margin-left:auto;">
                                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                                    <input type="hidden" name="id" value="<?= (int)$n['id'] ?>">
                                    <input type="hidden" name="back" value="/admin/notifications.php">
                                    <button class="btn btn-sm btn-secondary" type="submit">Mark read</button>
                                </form>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </section>
        <?php endif; ?>
    </main>
</div>
<style>
.notif-btn{position:relative;color:var(--text);text-decoration:none;padding:6px 10px;border-radius:8px;}
.notif-list{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:8px;}
.notif-item{display:flex;gap:12px;padding:10px 12px;border-radius:10px;background:var(--surface-2);align-items:flex-start;}
.notif-item.unread{background:rgba(37,99,235,0.08);}
.notif-icon{width:32px;height:32px;border-radius:8px;background:var(--surface);display:flex;align-items:center;justify-content:center;color:var(--brand,#2563EB);flex-shrink:0;}
.notif-body{flex:1;min-width:0;}
.notif-title{font-weight:600;font-size:14px;}
.notif-text{font-size:13px;color:var(--text-muted);margin-top:2px;}
.notif-time{font-size:11px;color:var(--text-muted);margin-top:4px;}
.mt-8{margin-top:8px;}
</style>
</body>
</html>
