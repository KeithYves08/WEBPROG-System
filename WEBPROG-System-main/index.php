<?php
session_start();

// Check for flash messages
$flash_error = '';
$flash_success = '';

if (isset($_SESSION['flash_error'])) {
    $flash_error = $_SESSION['flash_error'];
    unset($_SESSION['flash_error']);
}

if (isset($_SESSION['flash_success'])) {
    $flash_success = $_SESSION['flash_success'];
    unset($_SESSION['flash_success']);
}

// Handle logout and timeout messages
if (isset($_GET['logout']) && $_GET['logout'] === 'success') {
    $flash_success = 'You have been successfully logged out.';
}

if (isset($_GET['timeout']) && $_GET['timeout'] === '1') {
    $flash_error = 'Your session has expired. Please login again.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="AILPO System">
    <title>AILPO - Login</title>
    <link rel="stylesheet" href="view/styles/styles.css">
    <link rel="stylesheet" href="view/styles/flash-messages.css">
</head>
<body>
    <header class="site-header">
        <div class="header-inner">
            <h1 class="app-title">AILPO</h1>
            <img src="view/assets/NU-icon.webp" alt="NU Logo" class="nu-logo">
        </div>
        <div class="header-accent-line"></div>
    </header>

    <main class="auth-wrapper">
        <section class="auth-card">
            <div class="auth-left">
                <img src="view/assets/nu-campus.webp" alt="NU Campus" class="auth-left-img">
            </div>
            <div class="auth-right">
                <h2 class="login-title">Hello Admin!</h2>
                <div class="login-underline"></div>
                <p class="login-subtitle">Welcome back.</p>
                
                <?php if ($flash_error): ?>
                    <div class="flash-message flash-error">
                        <span class="flash-icon">⚠</span>
                        <?php echo htmlspecialchars($flash_error); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($flash_success): ?>
                    <div class="flash-message flash-success">
                        <span class="flash-icon">✓</span>
                        <?php echo htmlspecialchars($flash_success); ?>
                    </div>
                <?php endif; ?>
      
                <form class="login-form" action="controller/login.php" method="POST" autocomplete="on">
                    <div class="form-field">
                        <input type="text" id="username" name="username" placeholder="Username" required>
                    </div>
                    <div class="form-field">
                        <input type="password" id="password" name="password" placeholder="Password" required>
                    </div>
                    <button type="submit" class="login-btn">Login</button>
                    <a href="#" class="forgot-link">Forgot password?</a>
                </form>
                
                <div style="margin-top: 30px; padding: 15px; background: #f8f9fa; border-radius: 8px; font-size: 0.85rem;">
                    <strong>Demo Credentials:</strong><br>
                    Username: admin<br>
                    Password: admin123
                </div>
            </div>
        </section>
    </main>
</body>
</html>