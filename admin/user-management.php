<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management — UIU Compliance Management Platform</title>
    <link rel="stylesheet" href="../assets/css/theme.css">
    <link rel="stylesheet" href="../assets/css/department-admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<?php
require_once __DIR__ . '/../backend/includes/auth.php';
require_role_any(['admin', 'vc']);
$current = current_user();
$flash = flash_get();

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
} catch (Throwable $e) {
    $total_students = $active_students = $suspended_students = $added_this_week = 0;
    $students = [];
}
?>

<body class="plain-bg">

<header class="topbar">
    <div class="brand">
        <img src="../assets/images/UIU%20LOGO.png" alt="UIU Logo">
        <div class="brand-text">UIU Compliance<span>Management Platform</span></div>
    </div>
    <div class="right">
        <span class="role-badge"><i class="fa-solid fa-briefcase"></i> Department Admin</span>
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
            <a class="nav-item" href="admin-education.php"><i class="fa-solid fa-graduation-cap"></i> Education</a>
            <a class="nav-item" href="admin-library.php"><i class="fa-solid fa-book"></i> Library</a>
            <a class="nav-item" href="admin-transport.php"><i class="fa-solid fa-bus"></i> Transport</a>
            <a class="nav-item" href="admin-hostel.php"><i class="fa-solid fa-house-chimney"></i> Hostel</a>
            <a class="nav-item" href="admin-medical.php"><i class="fa-solid fa-hospital"></i> Medical</a>
        </nav>
        <nav class="nav-section">
            <h3>Administration</h3>
            <a class="nav-item active" href="user-management.php"><i class="fa-solid fa-user-gear"></i> User management</a>
        </nav>
    </aside>

    <main class="main">

        <div class="page-head">
            <h1>User management</h1>
            <p>Add or remove student accounts on the platform.</p>
            <div class="actions">
                <a class="btn btn-primary" href="student-create.php"><i class="fa-solid fa-user-plus"></i> Add student</a>
            </div>
        </div>

        <?php if ($flash): ?>
            <div class="alert <?= htmlspecialchars($flash['type']) ?>" role="alert">
                <i class="fa-solid <?= $flash['type'] === 'ok' ? 'fa-circle-check' : 'fa-circle-exclamation' ?>"></i>
                <span><?= htmlspecialchars($flash['message']) ?></span>
            </div>
        <?php endif; ?>

        <div class="grid grid-4 mb-24">
            <div class="stat"><div class="icon"><i class="fa-solid fa-user-graduate"></i></div><div class="label">Total students</div><div class="value"><?= number_format($total_students) ?></div></div>
            <div class="stat ok"><div class="icon"><i class="fa-solid fa-circle-check"></i></div><div class="label">Active</div><div class="value"><?= number_format($active_students) ?></div></div>
            <div class="stat warn"><div class="icon"><i class="fa-solid fa-circle-pause"></i></div><div class="label">Suspended</div><div class="value"><?= number_format($suspended_students) ?></div></div>
            <div class="stat"><div class="icon"><i class="fa-solid fa-user-plus"></i></div><div class="label">Added this week</div><div class="value"><?= number_format($added_this_week) ?></div></div>
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

    </main>
</div>

</body>
</html>
