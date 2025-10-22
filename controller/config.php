<?php
$host = "localhost";
$user = "root";
$pass = ""; 
$db = "ailpo";

try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Align PHP and MySQL session timezones to avoid date drift between layers
    // Use PHP's current offset (e.g., +08:00) which works even if MySQL timezone tables aren't loaded
    if (!ini_get('date.timezone')) {
        // Optionally set a default PHP timezone if none defined; adjust as needed
        // date_default_timezone_set('UTC');
    }
    $tzOffset = (new DateTime())->format('P'); // e.g., +08:00
    try { $conn->exec("SET time_zone = '" . $tzOffset . "'"); } catch (Throwable $e) { /* best effort */ }
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Connection failed. Please check your database configuration.");
}
?>