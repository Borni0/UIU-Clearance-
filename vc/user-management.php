<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management — UIU Compliance Management Platform</title>
    <link rel="stylesheet" href="../assets/css/theme.css">
    <link rel="stylesheet" href="../assets/css/department-admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .um-tabs {
            display: flex;
            gap: 4px;
            border-bottom: 1px solid var(--line);
            margin-bottom: 16px;
        }
        .um-tab {
            padding: 10px 18px;
            font-size: 14px;
            font-weight: 600;
            color: var(--ink-5);
            background: transparent;
            border: none;
            border-bottom: 2px solid transparent;
            margin-bottom: -1px;
            cursor: pointer;
            text-decoration: none;
        }
        .um-tab:hover { color: var(--ink); text-decoration: none; }
        .um-tab.active { color: var(--blue); border-bottom-color: var(--blue); }
        .reset-pw-cell { display: none; background: var(--line-3); }
        .reset-pw-cell.open { display: table-row; }
    </style>
</head>
<?php
require_once __DIR__ . '/../backend/includes/auth.php';
require_role('vc');
$current = current_user();
$flash = flash_get();

$tab = $_GET['tab'] ?? 'students';
if (!in_array($tab, ['students', 'admins'], true)) $tab = 'students';

try {
    $db = db();
    $total_students    = (int)$db->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
    $active_students   = (int)$db->query("SELECT COUNT(*) FROM users WHERE role = 'student' AND status = 'active'")->fetchColumn();
    $suspended_students= (int)$db->query("SELECT COUNT(*) FROM users WHERE role = 'student' AND status = 'suspended'")->fetchColumn();
    $added_this_week   = (int)$db->query("SELECT COUNT(*) FROM users WHERE role = 'student' AND created_at >= (NOW() - INTERVAL 7 DAY)")->fetchColumn();

    $students = $db->query(
        "SELECT id, full_name, email, student_id, department, status, created_at
           FROM users
          WHERE role = 'student'
          ORDER BY created_at DESC"
    )->fetchAll();

    $total_admins     = (int)$db->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
    $active_admins    = (int)$db->query("SELECT COUNT(*) FROM users WHERE role = 'admin' AND status = 'active'")->fetchColumn();
    $suspended_admins = (int)$db->query("SELECT COUNT(*) FROM users WHERE role = 'admin' AND status = 'suspended'")->fetchColumn();

    $admins = $db->query(
        "SELECT id, full_name, email, employee_id, department, status, created_at
           FROM users
          WHERE role = 'admin'
          ORDER BY created_at DESC"
    )->fetchAll();
} catch (Throwable $e) {
    $total_students = $active_students = $suspended_students = $added_this_week = 0;
    $total_admins = $active_admins = $suspended_admins = 0;
    $students = $admins = [];
}
?>

<body class="plain-bg">

<header class="topbar">
    <div class="brand">
        <img src="../assets/images/UIU%20LOGO.png" alt="UIU Logo">
        <div class="brand-text">UIU Compliance<span>Management Platform</span></div>
    </div>
    <div class="right">
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
            <h3>Reports</h3>
            <a class="nav-item" href="#"><i class="fa-solid fa-chart-line"></i> Advanced reports</a>
            <a class="nav-item" href="#"><i class="fa-solid fa-flag"></i> Flagged students</a>
            <a class="nav-item" href="#"><i class="fa-solid fa-clock-rotate-left"></i> Audit log</a>
        </nav>
        <nav class="nav-section">
            <h3>Administration</h3>
            <a class="nav-item active" href="user-management.php"><i class="fa-solid fa-user-gear"></i> User management</a>
        </nav>
        <nav class="nav-section">
            <h3>Support</h3>
            <a class="nav-item" href="#"><i class="fa-solid fa-circle-question"></i> Help center</a>
        </nav>
    </aside>

    <main class="main">

        <div class="page-head">
            <h1>User management</h1>
            <p>Add or remove students and admin accounts on the platform.</p>
            <div class="actions">
                <?php if ($tab === 'students'): ?>
                    <a class="btn btn-primary" href="student-create.php"><i class="fa-solid fa-user-plus"></i> Add student</a>
                <?php else: ?>
                    <a class="btn btn-primary" href="admin-create.php"><i class="fa-solid fa-user-shield"></i> Add admin</a>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($flash): ?>
            <div class="alert <?= htmlspecialchars($flash['type']) ?>" role="alert">
                <i class="fa-solid <?= $flash['type'] === 'ok' ? 'fa-circle-check' : 'fa-circle-exclamation' ?>"></i>
                <span><?= htmlspecialchars($flash['message']) ?></span>
            </div>
        <?php endif; ?>

        <?php if ($tab === 'students'): ?>
            <div class="grid grid-4 mb-24">
                <div class="stat"><div class="icon"><i class="fa-solid fa-user-graduate"></i></div><div class="label">Total students</div><div class="value"><?= number_format($total_students) ?></div></div>
                <div class="stat ok"><div class="icon"><i class="fa-solid fa-circle-check"></i></div><div class="label">Active</div><div class="value"><?= number_format($active_students) ?></div></div>
                <div class="stat warn"><div class="icon"><i class="fa-solid fa-circle-pause"></i></div><div class="label">Suspended</div><div class="value"><?= number_format($suspended_students) ?></div></div>
                <div class="stat"><div class="icon"><i class="fa-solid fa-user-plus"></i></div><div class="label">Added this week</div><div class="value"><?= number_format($added_this_week) ?></div></div>
            </div>

            <div class="um-tabs">
                <a class="um-tab active" href="user-management.php?tab=students"><i class="fa-solid fa-user-graduate"></i> Students</a>
                <a class="um-tab" href="user-management.php?tab=admins"><i class="fa-solid fa-user-shield"></i> Admins</a>
            </div>

            <section class="card">
                <div class="card-head">
                    <div>
                        <h2>Students</h2>
                        <p>All registered student accounts.</p>
                    </div>
                </div>

                <?php if (!$students): ?>
                    <div class="empty">
                        <i class="fa-regular fa-user"></i>
                        <h3>No students yet</h3>
                        <p>Click "Add student" above to create the first account.</p>
                    </div>
                <?php else: ?>
                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Student ID</th>
                                    <th>Department</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($students as $s): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($s['full_name']) ?></strong></td>
                                    <td><?= htmlspecialchars($s['email']) ?></td>
                                    <td><?= htmlspecialchars($s['student_id'] ?? '—') ?></td>
                                    <td><?= htmlspecialchars($s['department'] ?? '—') ?></td>
                                    <td>
                                        <span class="badge <?= $s['status'] === 'active' ? 'ok' : 'warn' ?>">
                                            <?= htmlspecialchars(ucfirst($s['status'])) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($s['created_at']) ?></td>
                                    <td>
                                        <form method="POST" action="../backend/actions/admin-delete-user.php"
                                              onsubmit="return confirm('Delete <?= htmlspecialchars($s['full_name'], ENT_QUOTES) ?> (<?= htmlspecialchars($s['email'], ENT_QUOTES) ?>)? This cannot be undone.');"
                                              style="display:inline;">
                                            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                                            <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fa-solid fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>

        <?php else: /* admins tab */ ?>

            <div class="grid grid-4 mb-24">
                <div class="stat"><div class="icon"><i class="fa-solid fa-user-shield"></i></div><div class="label">Total admins</div><div class="value"><?= number_format($total_admins) ?></div></div>
                <div class="stat ok"><div class="icon"><i class="fa-solid fa-circle-check"></i></div><div class="label">Active</div><div class="value"><?= number_format($active_admins) ?></div></div>
                <div class="stat warn"><div class="icon"><i class="fa-solid fa-circle-pause"></i></div><div class="label">Suspended</div><div class="value"><?= number_format($suspended_admins) ?></div></div>
                <div class="stat"><div class="icon"><i class="fa-solid fa-user-tie"></i></div><div class="label">Vice Chancellors</div><div class="value"><?= (int)$db->query("SELECT COUNT(*) FROM users WHERE role = 'vc'")->fetchColumn() ?></div></div>
            </div>

            <div class="um-tabs">
                <a class="um-tab" href="user-management.php?tab=students"><i class="fa-solid fa-user-graduate"></i> Students</a>
                <a class="um-tab active" href="user-management.php?tab=admins"><i class="fa-solid fa-user-shield"></i> Admins</a>
            </div>

            <section class="card">
                <div class="card-head">
                    <div>
                        <h2>Admins</h2>
                        <p>All department admin accounts. (Vice Chancellor accounts are managed separately.)</p>
                    </div>
                </div>

                <?php if (!$admins): ?>
                    <div class="empty">
                        <i class="fa-regular fa-user"></i>
                        <h3>No admins yet</h3>
                        <p>Click "Add admin" above to create the first admin account.</p>
                    </div>
                <?php else: ?>
                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Employee ID</th>
                                    <th>Department</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($admins as $a): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($a['full_name']) ?></strong></td>
                                    <td><?= htmlspecialchars($a['email']) ?></td>
                                    <td><?= htmlspecialchars($a['employee_id'] ?? '—') ?></td>
                                    <td><?= htmlspecialchars($a['department'] ?? '—') ?></td>
                                    <td>
                                        <span class="badge <?= $a['status'] === 'active' ? 'ok' : 'warn' ?>">
                                            <?= htmlspecialchars(ucfirst($a['status'])) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($a['created_at']) ?></td>
                                    <td>
                                        <div class="row-actions">
                                            <a class="btn btn-sm btn-secondary" href="admin-edit.php?id=<?= (int)$a['id'] ?>">
                                                <i class="fa-solid fa-pen"></i> Edit
                                            </a>
                                            <form method="POST" action="../backend/actions/vc-reset-password.php"
                                                  onsubmit="var p = prompt('New temporary password (min 8 chars):'); if (!p || p.length < 8) { alert('Password must be at least 8 characters.'); return false; } var c = prompt('Confirm password:'); if (p !== c) { alert('Passwords do not match.'); return false; } this.elements['pw'].value = p; this.elements['pw2'].value = c; return confirm('Reset password for <?= htmlspecialchars($a['email'], ENT_QUOTES) ?>?');"
                                                  style="display:inline;">
                                                <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                                                <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
                                                <input type="hidden" name="pw" value="">
                                                <input type="hidden" name="pw2" value="">
                                                <button type="submit" class="btn btn-sm btn-secondary">
                                                    <i class="fa-solid fa-key"></i> Reset password
                                                </button>
                                            </form>
                                            <form method="POST" action="../backend/actions/admin-delete-user.php"
                                                  onsubmit="return confirm('Delete admin <?= htmlspecialchars($a['email'], ENT_QUOTES) ?>? This cannot be undone.');"
                                                  style="display:inline;">
                                                <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                                                <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fa-solid fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>

        <?php endif; ?>

    </main>
</div>

</body>
</html>
