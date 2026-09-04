<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create student account — UIU Compliance Management Platform</title>
    <link rel="stylesheet" href="../assets/css/theme.css">
    <link rel="stylesheet" href="../assets/css/page2.css">
    <script src="https://kit.fontawesome.com/063f939101.js" crossorigin="anonymous"></script>
</head>

<body class="photo-bg">

<?php
require_once __DIR__ . '/../backend/includes/session.php';
require_once __DIR__ . '/../backend/includes/auth.php';
require_once __DIR__ . '/../backend/includes/flash.php';
if (!empty($_SESSION['user_id'])) {
    header('Location: /student/dashboard.php'); exit;
}
$flash = flash_get();
?>

<main class="auth-wrap">

    <a class="auth-back" href="login.php">
        <i class="fa-solid fa-arrow-left"></i> Back to sign in
    </a>

    <section class="auth-card">

        <header class="auth-head">
            <img class="auth-logo" src="../assets/images/UIU%20LOGO.png" alt="UIU Logo">
            <h1>Create a student account</h1>
            <p>Already registered? <a href="login.php">Sign in</a> instead.</p>
        </header>

        <?php if ($flash): ?>
            <div class="auth-flash auth-flash-<?= htmlspecialchars($flash['type']) ?>" role="alert">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="../backend/actions/signup-student.php" class="auth-form">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">

            <div class="field">
                <label for="fullname">Full name</label>
                <div class="input">
                    <i class="fa-regular fa-user" aria-hidden="true"></i>
                    <input id="fullname" name="fullname" type="text" placeholder="Your full name" required>
                </div>
            </div>

            <div class="field">
                <label for="email">University email</label>
                <div class="input">
                    <i class="fa-regular fa-envelope" aria-hidden="true"></i>
                    <input id="email" name="email" type="email" placeholder="name@university.edu" required>
                </div>
                <span class="field-hint">Use the email issued by your university.</span>
            </div>

            <div class="field">
                <label for="dept">Department</label>
                <div class="input">
                    <i class="fa-solid fa-briefcase" aria-hidden="true"></i>
                    <select id="dept" name="dept" required>
                        <option value="">Choose your department</option>
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
                    <label for="pw">Password</label>
                    <div class="input">
                        <i class="fa-solid fa-lock" aria-hidden="true"></i>
                        <input id="pw" name="pw" type="password" placeholder="At least 8 characters" required>
                    </div>
                </div>
                <div class="field" style="margin-bottom: 0;">
                    <label for="pw2">Confirm password</label>
                    <div class="input">
                        <i class="fa-solid fa-lock" aria-hidden="true"></i>
                        <input id="pw2" name="pw2" type="password" placeholder="Type it again" required>
                    </div>
                </div>
            </div>

            <label class="auth-terms">
                <input type="checkbox" required>
                <span>I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>.</span>
            </label>

            <button class="btn btn-primary btn-block" type="submit" name="signup">
                Create account
            </button>

        </form>

    </section>

</main>

</body>
</html>
