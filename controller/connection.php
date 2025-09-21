<?php
$host = "localhost";
$user = "root";   // your MySQL username
$pass = "";       // your MySQL password
$db   = "ailpo";

try {
    $conn = new PDO("mysql:host=$host;dbname=$db", $user, $pass);

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected successfully";
} catch (PDOException $e){
    echo "Connection failed: " . $e->getMessage();
}
?>
