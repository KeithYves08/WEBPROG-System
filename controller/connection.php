<?php
$host = getenv('DB_HOST');
$user = getenv('DB_USER');   // your MySQL username
$pass = getenv('DB_PASS');   // your MySQL password
$db   = getenv('DB_NAME');

try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected successfully";
} catch (PDOException $e){
    error_log("Database connection failed: " . $e->getMessage());
    echo "Connection failed. Please try again later.";
}
?>
