<?php
require_once __DIR__ . '/../backend/includes/auth.php';
require_once __DIR__ . '/../backend/includes/layout.php';
require_role('student');
$me = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emergency Request — UIU Compliance Management Platform</title>
    <link rel="stylesheet" href="../assets/css/theme.css">
    <link rel="stylesheet" href="../assets/css/emergency-request.css">
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
            <h1>Emergency clearance request</h1>
            <p>Submit an urgent clearance request. Admins and the VC will be notified immediately.</p>
        </div>

        <?= uiu_flash_banner() ?>

        <section class="card">
            <form method="POST" action="../backend/actions/student-submit-emergency.php" enctype="multipart/form-data">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">

                <div class="grid grid-2">
                    <div class="field">
                        <label>Student ID (auto-filled)</label>
                        <div class="input"><i class="fa-solid fa-id-card"></i>
                            <input type="text" value="<?= htmlspecialchars((string)$me['student_id']) ?>" readonly>
                        </div>
                    </div>
                    <div class="field">
                        <label>Full name (auto-filled)</label>
                        <div class="input"><i class="fa-regular fa-user"></i>
                            <input type="text" value="<?= htmlspecialchars((string)$me['full_name']) ?>" readonly>
                        </div>
                    </div>
                    <div class="field">
                        <label>Department (auto-filled)</label>
                        <div class="input"><i class="fa-solid fa-briefcase"></i>
                            <input type="text" value="<?= htmlspecialchars((string)$me['department']) ?>" readonly>
                        </div>
                    </div>
                    <div class="field">
                        <label for="required_by">Required completion date</label>
                        <div class="input"><i class="fa-regular fa-calendar"></i>
                            <input id="required_by" name="required_by" type="date" required min="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                </div>

                <div class="field mt-16">
                    <label for="title">Request title</label>
                    <div class="input"><i class="fa-solid fa-tag"></i>
                        <input id="title" name="title" type="text" placeholder="e.g. Certificate needed for job interview" required>
                    </div>
                </div>

                <div class="field mt-16">
                    <label for="reason">Reason for emergency</label>
                    <textarea id="reason" name="reason" rows="5" required
                              placeholder="Explain the urgent situation and why this needs fast-track processing."></textarea>
                </div>

                <div class="field mt-16">
                    <label for="file">Supporting document (optional)</label>
                    <input id="file" name="file" type="file" accept="application/pdf,image/jpeg,image/png">
                    <span class="muted small">PDF, JPG, or PNG. Max 100 MB.</span>
                </div>

                <div class="cl-actions mt-24">
                    <button type="submit" class="btn btn-danger">
                        <i class="fa-solid fa-circle-exclamation"></i> Submit emergency request
                    </button>
                    <a class="btn btn-secondary" href="dashboard.php">Cancel</a>
                </div>
            </form>
        </section>
    </main>
</div>

<style>
.muted{color:var(--text-muted);} .small{font-size:12px;}
textarea{width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;background:var(--surface);color:var(--text);font:inherit;}
input[type=file]{width:100%;padding:8px 0;color:var(--text);}
.mt-16{margin-top:16px;} .mt-24{margin-top:24px;}
</style>
</body>
</html>
