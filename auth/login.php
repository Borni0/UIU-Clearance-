<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in — UIU Compliance Management Platform</title>
    <link rel="stylesheet" href="../assets/css/page1.css">
    <script src="https://kit.fontawesome.com/063f939101.js" crossorigin="anonymous"></script>
</head>
<?php
require_once __DIR__ . '/../backend/includes/session.php';
require_once __DIR__ . '/../backend/includes/auth.php';
require_once __DIR__ . '/../backend/includes/flash.php';


if (!empty($_SESSION['user_id'])) {
    $home = match ($_SESSION['role'] ?? '') {
        'student' => '/student/dashboard.php',
        'admin'   => '/admin/dashboard.php',
        'vc'      => '/vc/dashboard.php',
        default   => null,
    };
    if ($home) { header('Location: ' . $home); exit; }
}

$flash = flash_get();
?>

<body>

<main class="page" aria-label="Sign in">

    <header class="head">
        <img
            class="logo"
            src="../assets/images/UIU%20LOGO.png"
            alt="UIU Logo"
        >
        <h1>Sign in to UIU Compliance Management Platform</h1>
        <p>Use your university email and password.</p>
    </header>

    <?php if ($flash): ?>
        <div class="error" role="alert">
            <?= htmlspecialchars($flash['message']) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="../backend/actions/login.php" novalidate>
        <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">

        <!-- Email -->
        <div class="field">
            <label for="email">Email address</label>
            <div class="input">
                <i class="fa-regular fa-envelope" aria-hidden="true"></i>
                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="name@university.edu"
                    autocomplete="email"
                    required
                >
            </div>
            <span class="field-hint">Use the email you registered with.</span>
        </div>

          <!-- Password -->
          <div class="field">
              <label for="password">Password</label>
              <div class="input">
                  <i class="fa-solid fa-lock" aria-hidden="true"></i>
                  <input
                      type="password"
                      id="password"
                      name="password"
                      placeholder="Enter your password"
                      autocomplete="current-password"
                      required
                  >
                  <button
                      type="button"
                      class="pw-toggle"
                      id="pwToggle"
                      aria-label="Show password"
                      aria-pressed="false"
                      aria-controls="password"
                  >
                      <i class="fa-regular fa-eye" aria-hidden="true"></i>
                  </button>
              </div>
          </div>



        
        <h2 class="section-title">I am a&hellip;</h2>
        <p class="section-hint">Tap one to choose your role.</p>

        <div class="roles" role="radiogroup" aria-label="Choose your role">

            <label class="role" for="role-student">
                <input type="radio" id="role-student" name="role" value="student" required>
                <span class="role-icon" aria-hidden="true">
                    <i class="fa-solid fa-graduation-cap"></i>
                </span>
                <span class="role-text">
                    <span class="role-name">Student</span>
                    <span class="role-sub">Apply for clearance, upload documents</span>
                </span>
                <span class="role-check" aria-hidden="true">
                    <i class="fa-solid fa-check"></i>
                </span>
            </label>

            <label class="role" for="role-admin">
                <input type="radio" id="role-admin" name="role" value="admin">
                <span class="role-icon" aria-hidden="true">
                    <i class="fa-solid fa-briefcase"></i>
                </span>
                <span class="role-text">
                    <span class="role-name">Department Admin</span>
                    <span class="role-sub">Review and approve student requests</span>
                </span>
                <span class="role-check" aria-hidden="true">
                    <i class="fa-solid fa-check"></i>
                </span>
            </label>

            <label class="role" for="role-vc">
                <input type="radio" id="role-vc" name="role" value="vc">
                <span class="role-icon" aria-hidden="true">
                    <i class="fa-solid fa-user-tie"></i>
                </span>
                <span class="role-text">
                    <span class="role-name">Vice Chancellor</span>
                    <span class="role-sub">Final approval and oversight</span>
                </span>
                <span class="role-check" aria-hidden="true">
                    <i class="fa-solid fa-check"></i>
                </span>
            </label>

        </div>

        <?php if (!empty($error)): ?>
            <div class="error" role="alert">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <button class="submit" type="submit" name="login">
            Sign in
        </button>

    </form>

    <p class="foot">
        Need help? Contact your department office.
    </p>

<script>                                                                          
    (function () {
        const input = document.getElementById('password');
        const btn   = document.getElementById('pwToggle');
        const icon  = btn.querySelector('i');
        if (!input || !btn) return;                                                   
                                                                                      
        btn.addEventListener('click', function () {                                   
            const showing = input.type === 'text';                                  
            input.type = showing ? 'password' : 'text';
            btn.setAttribute('aria-pressed', showing ? 'false' : 'true');
            btn.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');                                                                         
            icon.className = showing ? 'fa-regular fa-eye' : 'fa-regular  fa-eye-slash';                                                                      
        });                                                                         
    })();
  </script>



</main>

</body>
</html>
