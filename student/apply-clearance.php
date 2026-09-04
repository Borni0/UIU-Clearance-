<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Clearance — UIU Compliance Management Platform</title>
    <link rel="stylesheet" href="../assets/css/theme.css">
    <link rel="stylesheet" href="../assets/css/apply-clearance.css">
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
            <a class="nav-item" href="help.php"><i class="fa-regular fa-circle-question"></i> Help center</a>
        </nav>
    </aside>

    <main class="main">

        <div class="page-head">
            <h1>Apply for clearance</h1>
            <p>Fill in your details and submit your reason for clearance.</p>
        </div>

        <div class="grid grid-2">

            <section class="card">
                <div class="card-head">
                    <div><h2>Your details</h2><p>We'll pre-fill what we know.</p></div>
                </div>
                <form>
                    <div class="field">
                        <label for="name">Full name</label>
                        <div class="input"><i class="fa-regular fa-user"></i><input id="name" type="text" placeholder="Your full name" required></div>
                    </div>
                    <div class="field">
                        <label for="sid">Student ID</label>
                        <div class="input"><i class="fa-solid fa-id-card"></i><input id="sid" type="text" placeholder="e.g. 011221000" required></div>
                    </div>
                    <div class="grid grid-2" style="gap: 16px;">
                        <div class="field" style="margin-bottom: 0;">
                            <label for="dept">Department</label>
                            <select id="dept" required>
                                <option value="">Choose department</option>
                                <option>CSE</option><option>EEE</option><option>CIVIL</option><option>BBA</option>
                            </select>
                        </div>
                        <div class="field" style="margin-bottom: 0;">
                            <label for="year">Passing year</label>
                            <select id="year" required>
                                <option value="">Choose year</option>
                                <option>2022</option><option>2023</option><option>2024</option><option>2025</option>
                            </select>
                        </div>
                    </div>
                    <div class="field mt-16">
                        <label for="email">Email</label>
                        <div class="input"><i class="fa-regular fa-envelope"></i><input id="email" type="email" placeholder="name@university.edu" required></div>
                    </div>
                    <div class="field">
                        <label for="phone">Phone number</label>
                        <div class="input"><i class="fa-solid fa-phone"></i><input id="phone" type="text" placeholder="01XXXXXXXXX" required></div>
                    </div>
                </form>
            </section>

            <section class="card">
                <div class="card-head">
                    <div><h2>Reason for clearance</h2><p>Tell us why you need this clearance.</p></div>
                </div>
                <form>
                    <div class="field">
                        <label for="reason">Your message</label>
                        <textarea id="reason" rows="8" placeholder="Explain the reason for your clearance request…"></textarea>
                    </div>
                    <button class="btn btn-primary btn-block" type="submit">Submit application</button>
                </form>
            </section>

        </div>

    </main>

</div>

</body>
</html>
