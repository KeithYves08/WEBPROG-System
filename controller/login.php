<?php
session_start();
require_once 'config.php';

//input sanitization
function sanitizeInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

//ps verification
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

//check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = sanitizeInput($_POST['username']);
    $password = sanitizeInput($_POST['password']);
    
    //validation
    if (empty($username) || empty($password)) {
        $_SESSION['flash_error'] = "Please fill in all fields.";
        header("Location: ../index.php");
        exit();
    }
    
    try {
        //check if admin exists
        $stmt = $conn->prepare("SELECT id, username, password FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($admin && verifyPassword($password, $admin['password'])) {
            //login successful
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['is_logged_in'] = true;
            $_SESSION['last_activity'] = time();
            
            //redirect to dashboard
            header("Location: ../pages/dashboard.php");
            exit();
            
        } else {
            //login failed
            $_SESSION['flash_error'] = "Invalid username or password. Please try again.";
            header("Location: ../index.php");
            exit();
        }
        
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        $_SESSION['flash_error'] = "A system error occurred. Please try again later.";
        header("Location: ../index.php");
        exit();
    }
    
} else {
    //direct access not allowed
    header("Location: ../index.php");
    exit();
}
?>