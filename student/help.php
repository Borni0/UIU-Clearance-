<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help & Support — UIU Compliance Management Platform</title>
    <link rel="stylesheet" href="../assets/css/theme.css">
    <link rel="stylesheet" href="../assets/css/help.css">
    <script src="https://kit.fontawesome.com/063f939101.js" crossorigin="anonymous"></script>
</head>
<?php
require_once __DIR__ . '/../backend/includes/auth.php';
require_role('student');
$current = current_user();
?>

<body class="plain-bg">

<header class="topbar">
    <div class="brand">
        <img src="../assets/images/UIU%20LOGO.png" alt="UIU Logo">
        <div class="brand-text">UIU Compliance<span>Management Platform</span></div>
    </div>
    <div class="right">
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
            <a class="nav-item" href="appointment.php"><i class="fa-regular fa-calendar-check"></i> Appointments</a>
            <a class="nav-item" href="track-status.php"><i class="fa-solid fa-location-dot"></i> Track status</a>
        </nav>
        <nav class="nav-section">
            <h3>Support</h3>
            <a class="nav-item" href="emergency-page.php"><i class="fa-solid fa-circle-exclamation"></i> Emergency</a>
            <a class="nav-item active" href="help.php"><i class="fa-regular fa-circle-question"></i> Help center</a>
        </nav>
    </aside>

    <main class="main">

        <div class="page-head">
            <h1>Help & Support</h1>
            <p>Find answers to common questions or get in touch with us.</p>
        </div>

        <section class="card mb-24 help-cta">
            <div>
                <h2>Need assistance?</h2>
                <p>We are here to help you with your university clearance process.</p>
            </div>
            <a class="btn btn-primary" href="#"><i class="fa-regular fa-comments"></i> Contact support</a>
        </section>

        <div class="grid grid-2 mb-24">
            <article class="help-card">
                <div class="help-icon"><i class="fa-regular fa-file-lines"></i></div>
                <h3>Upload documents</h3>
                <p>Go to "All Documents" and click the upload button to submit your files.</p>
                <a class="btn btn-sm btn-secondary" href="all-documents.php">Learn more</a>
            </article>
            <article class="help-card">
                <div class="help-icon"><i class="fa-solid fa-thumbtack"></i></div>
                <h3>Track status</h3>
                <p>Open the "Track Status" page to check your application progress and approvals.</p>
                <a class="btn btn-sm btn-secondary" href="track-status.php">Learn more</a>
            </article>
            <article class="help-card">
                <div class="help-icon"><i class="fa-regular fa-calendar"></i></div>
                <h3>Appointment system</h3>
                <p>Book appointments with university offices for verification and final approval.</p>
                <a class="btn btn-sm btn-secondary" href="appointment.php">Learn more</a>
            </article>
            <article class="help-card">
                <div class="help-icon"><i class="fa-solid fa-screwdriver-wrench"></i></div>
                <h3>Report a problem</h3>
                <p>Facing any issue? Submit your complaint or technical problem directly to support.</p>
                <a class="btn btn-sm btn-primary" href="emergency-page.php">Report now</a>
            </article>
        </div>

        <section class="card">
            <div class="card-head">
                <div><h2>Contact information</h2><p>Reach us during office hours.</p></div>
            </div>
            <div class="grid grid-3">
                <div class="contact-item">
                    <div class="contact-icon"><i class="fa-regular fa-envelope"></i></div>
                    <div>
                        <div class="lbl">Email</div>
                        <div class="val">support@university.com</div>
                    </div>
                </div>
                <div class="contact-item">
                    <div class="contact-icon"><i class="fa-solid fa-phone"></i></div>
                    <div>
                        <div class="lbl">Phone</div>
                        <div class="val">+8801XXXXXXXXX</div>
                    </div>
                </div>
                <div class="contact-item">
                    <div class="contact-icon"><i class="fa-solid fa-building"></i></div>
                    <div>
                        <div class="lbl">Office</div>
                        <div class="val">Admin Building, UIU Campus</div>
                    </div>
                </div>
            </div>
        </section>

    </main>
</div>

</body>
</html>
