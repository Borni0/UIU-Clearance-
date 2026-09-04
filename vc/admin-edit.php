<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Admin — UIU Compliance Management Platform</title>
    <link rel="stylesheet" href="../assets/css/theme.css">
    <link rel="stylesheet" href="../assets/css/department-admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<?php
require_once __DIR__ . '/../backend/includes/auth.php';
require_role('vc');
$current = current_user();
$flash = flash_get();

$id = (int)($_GET['id'] ?? 0);
$admin = null;
$err = null;
if ($id > 0) {
    try {
        $stmt = db()->prepare('SELECT * FROM users WHERE id = ? AND role = "admin" LIMIT 1');
        $stmt->execute([$id]);
        $admin = $stmt->fetch();
    } catch (Throwable $e) {
        $err = 'Could not load the admin record.';
    }
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
            <a class="nav-item active" href="user-management.php?tab=admins"><i class="fa-solid fa-user-gear"></i> User management</a>
        </nav>
        <nav class="nav-section">
            <h3>Support</h3>
            <a class="nav-item" href="#"><i class="fa-solid fa-circle-question"></i> Help center</a>
        </nav>
    </aside>

    <main class="main">

        <div class="page-head">
            <h1>Edit admin</h1>
            <p>Update profile fields, change status, or suspend access.</p>
            <div class="actions">
                <a class="btn btn-secondary" href="user-management.php?tab=admins"><i class="fa-solid fa-arrow-left"></i> Back to admins</a>
            </div>
        </div>

        <?php if ($flash): ?>
            <div class="alert <?= htmlspecialchars($flash['type']) ?>" role="alert">
                <i class="fa-solid <?= $flash['type'] === 'ok' ? 'fa-circle-check' : 'fa-circle-exclamation' ?>"></i>
                <span><?= htmlspecialchars($flash['message']) ?></span>
            </div>
        <?php endif; ?>

        <?php if ($err): ?>
            <div class="alert bad" role="alert">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span><?= htmlspecialchars($err) ?></span>
            </div>
        <?php elseif (!$admin): ?>
            <div class="alert bad" role="alert">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>Admin not found.</span>
            </div>
        <?php else: ?>

            <section class="card">
                <form method="POST" action="../backend/actions/vc-update-admin.php">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <input type="hidden" name="id" value="<?= (int)$admin['id'] ?>">

                    <div class="field">
                        <label for="fullname">Full name</label>
                        <div class="input">
                            <i class="fa-regular fa-user" aria-hidden="true"></i>
                            <input id="fullname" name="fullname" type="text"
                                   value="<?= htmlspecialchars($admin['full_name']) ?>" required>
                        </div>
                    </div>

                    <div class="field">
                        <label for="email">University email</label>
                        <div class="input">
                            <i class="fa-regular fa-envelope" aria-hidden="true"></i>
                            <input id="email" name="email" type="email"
                                   value="<?= htmlspecialchars($admin['email']) ?>" required>
                        </div>
                    </div>

                    <div class="field">
                        <label for="dept">Department</label>
                        <div class="input">
                            <i class="fa-solid fa-briefcase" aria-hidden="true"></i>
                            <input id="dept" name="dept" type="text"
                                   value="<?= htmlspecialchars($admin['department'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="field">
                        <label for="eid">Employee ID</label>
                        <div class="input">
                            <i class="fa-solid fa-id-badge" aria-hidden="true"></i>
                            <input id="eid" name="eid" type="text"
                                   value="<?= htmlspecialchars($admin['employee_id'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="field">
                        <label for="status">Account status</label>
                        <div class="input">
                            <i class="fa-solid fa-toggle-on" aria-hidden="true"></i>
                            <select id="status" name="status">
                                <option value="active"    <?= $admin['status'] === 'active'    ? 'selected' : '' ?>>Active</option>
                                <option value="suspended" <?= $admin['status'] === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                            </select>
                        </div>
                        <span class="field-hint">Suspended accounts cannot sign in. Use this for temporary blocks.</span>
                    </div>

                    <div class="cl-actions" style="margin-top: 24px;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-floppy-disk"></i> Save changes
                        </button>
                        <a class="btn btn-secondary" href="user-management.php?tab=admins">Cancel</a>
                    </div>
                </form>
            </section>

        <?php endif; ?>

    </main>
</div>

</body>
</html>
