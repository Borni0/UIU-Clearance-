<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Student — UIU Compliance Management Platform</title>
    <link rel="stylesheet" href="../assets/css/theme.css">
    <link rel="stylesheet" href="../assets/css/department-admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<?php
require_once __DIR__ . '/../backend/includes/auth.php';
require_role('vc');
$current = current_user();
$flash = flash_get();
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
            <a class="nav-item active" href="user-management.php?tab=students"><i class="fa-solid fa-user-gear"></i> User management</a>
        </nav>
        <nav class="nav-section">
            <h3>Support</h3>
            <a class="nav-item" href="#"><i class="fa-solid fa-circle-question"></i> Help center</a>
        </nav>
    </aside>

    <main class="main">

        <div class="page-head">
            <h1>Add a student</h1>
            <p>Create a new student account. The student will sign in with the email and temporary password you set.</p>
            <div class="actions">
                <a class="btn btn-secondary" href="user-management.php?tab=students"><i class="fa-solid fa-arrow-left"></i> Back to students</a>
            </div>
        </div>

        <?php if ($flash): ?>
            <div class="alert <?= htmlspecialchars($flash['type']) ?>" role="alert">
                <i class="fa-solid <?= $flash['type'] === 'ok' ? 'fa-circle-check' : 'fa-circle-exclamation' ?>"></i>
                <span><?= htmlspecialchars($flash['message']) ?></span>
            </div>
        <?php endif; ?>

        <section class="card">
            <form method="POST" action="../backend/actions/admin-create-student.php">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">

                <div class="field">
                    <label for="fullname">Full name</label>
                    <div class="input">
                        <i class="fa-regular fa-user" aria-hidden="true"></i>
                        <input id="fullname" name="fullname" type="text" placeholder="e.g. Anika Rahman" required>
                    </div>
                </div>

                <div class="field">
                    <label for="email">University email</label>
                    <div class="input">
                        <i class="fa-regular fa-envelope" aria-hidden="true"></i>
                        <input id="email" name="email" type="email" placeholder="name@university.edu" required>
                    </div>
                </div>

                <div class="field">
                    <label for="dept">Department</label>
                    <div class="input">
                        <i class="fa-solid fa-briefcase" aria-hidden="true"></i>
                        <select id="dept" name="dept" required>
                            <option value="">Choose a department</option>
                            <option>CSE</option>
                            <option>EEE</option>
                            <option>CIVIL</option>
                            <option>BBA</option>
                            <option>Economics</option>
                            <option>English</option>
                        </select>
                    </div>
                </div>

                <div class="field">
                    <label for="sid">Student ID</label>
                    <div class="input">
                        <i class="fa-solid fa-id-card" aria-hidden="true"></i>
                        <input id="sid" name="sid" type="text" placeholder="e.g. 011221000" required>
                    </div>
                </div>

                <div class="grid grid-2" style="gap: 16px;">
                    <div class="field" style="margin-bottom: 0;">
                        <label for="pw">Temporary password</label>
                        <div class="input">
                            <i class="fa-solid fa-lock" aria-hidden="true"></i>
                            <input id="pw" name="pw" type="text" placeholder="At least 8 characters" minlength="8" required>
                        </div>
                        <span class="field-hint">Shown to the student on first login. Must be at least 8 characters.</span>
                    </div>
                    <div class="field" style="margin-bottom: 0;">
                        <label for="pw2">Confirm password</label>
                        <div class="input">
                            <i class="fa-solid fa-lock" aria-hidden="true"></i>
                            <input id="pw2" name="pw2" type="text" placeholder="Type it again" minlength="8" required>
                        </div>
                    </div>
                </div>

                <div class="cl-actions" style="margin-top: 24px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-user-plus"></i> Create student account
                    </button>
                    <a class="btn btn-secondary" href="user-management.php?tab=students">Cancel</a>
                </div>
            </form>
        </section>

    </main>
</div>

</body>
</html>
