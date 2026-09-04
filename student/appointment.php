<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointments — UIU Compliance Management Platform</title>
    <link rel="stylesheet" href="../assets/css/theme.css">
    <link rel="stylesheet" href="../assets/css/appoinment.css">
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
            <a class="nav-item active" href="appointment.php"><i class="fa-regular fa-calendar-check"></i> Appointments</a>
            <a class="nav-item" href="track-status.php"><i class="fa-solid fa-location-dot"></i> Track status</a>
        </nav>
        <nav class="nav-section">
            <h3>Support</h3>
            <a class="nav-item" href="emergency-page.php"><i class="fa-solid fa-circle-exclamation"></i> Emergency</a>
            <a class="nav-item" href="help.php"><i class="fa-regular fa-circle-question"></i> Help center</a>
        </nav>
    </aside>

    <main class="main">

        <div class="page-head">
            <h1>My appointments</h1>
            <p>Manage your scheduled appointments easily.</p>
            <div class="actions">
                <a class="btn btn-primary" href="#"><i class="fa-solid fa-plus"></i> Book appointment</a>
            </div>
        </div>

        <div class="grid grid-3">

            <article class="appt-card">
                <div class="appt-head">
                    <span class="appt-office">Accounts Office</span>
                    <span class="badge ok">Confirmed</span>
                </div>
                <ul class="appt-details">
                    <li><i class="fa-regular fa-calendar"></i> 18 May 2026</li>
                    <li><i class="fa-regular fa-clock"></i> 11:00 AM</li>
                    <li><i class="fa-solid fa-thumbtack"></i> Fee clearance verification</li>
                </ul>
                <div class="appt-actions">
                    <a class="btn btn-sm btn-secondary" href="#">View</a>
                    <a class="btn btn-sm btn-secondary" href="#">Cancel</a>
                </div>
            </article>

            <article class="appt-card">
                <div class="appt-head">
                    <span class="appt-office">Library Section</span>
                    <span class="badge warn">Pending</span>
                </div>
                <ul class="appt-details">
                    <li><i class="fa-regular fa-calendar"></i> 20 May 2026</li>
                    <li><i class="fa-regular fa-clock"></i> 01:30 PM</li>
                    <li><i class="fa-solid fa-thumbtack"></i> Book clearance</li>
                </ul>
                <div class="appt-actions">
                    <a class="btn btn-sm btn-secondary" href="#">View</a>
                    <a class="btn btn-sm btn-secondary" href="#">Cancel</a>
                </div>
            </article>

            <article class="appt-card">
                <div class="appt-head">
                    <span class="appt-office">VC Office</span>
                    <span class="badge info">Completed</span>
                </div>
                <ul class="appt-details">
                    <li><i class="fa-regular fa-calendar"></i> 10 May 2026</li>
                    <li><i class="fa-regular fa-clock"></i> 10:00 AM</li>
                    <li><i class="fa-solid fa-thumbtack"></i> Final approval</li>
                </ul>
                <div class="appt-actions">
                    <a class="btn btn-sm btn-secondary" href="#">View</a>
                    <a class="btn btn-sm btn-secondary" href="#">Cancel</a>
                </div>
            </article>

        </div>

    </main>
</div>

</body>
</html>
