<?php
//session mngmt and auth helpers

function checkLogin() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
        // Redirect to login page
        header("Location: ../index.php");
        exit();
    }
    
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
        // Session expired
        session_unset();
        session_destroy();
        header("Location: ../index.php?timeout=1");
        exit();
    }
    
    $_SESSION['last_activity'] = time();
    
    return true;
}

function getUserInfo() {
    return [
        'id' => $_SESSION['admin_id'] ?? null,
        'username' => $_SESSION['admin_username'] ?? null,
        'name' => $_SESSION['admin_username'] ?? null // Use username as display name
    ];
}

function logout() {
    session_start();
    session_unset();
    session_destroy();
    header("Location: ../index.php");
    exit();
}
?>