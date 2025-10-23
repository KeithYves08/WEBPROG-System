<?php
session_start();

// Capture user context before clearing session
$userId = $_SESSION['admin_id'] ?? null;
$username = $_SESSION['admin_username'] ?? null;

// Attempt to log logout action before destroying the session
try {
    require_once __DIR__ . '/config.php';
    require_once __DIR__ . '/activityLogger.php';
    log_activity($conn, [
        'action' => 'logout',
        'user_id' => $userId,
        'username' => $username,
        'description' => 'User logged out',
    ]);
} catch (Throwable $e) { /* best-effort */ }

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Redirect to login page
header("Location: ../index.php?logout=success");
exit();
?>