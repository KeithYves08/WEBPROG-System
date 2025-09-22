<?php
$host = getenv('DB_HOST');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');

if ($host === false || $user === false || $pass === false) {
    die("❌ Error: Database credentials are not set in environment variables (DB_HOST, DB_USER, DB_PASS).");
}
try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS ailpo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✅ Database 'ailpo' created successfully.<br>";
    
    $pdo->exec("USE ailpo");

    $sql = "
    CREATE TABLE IF NOT EXISTS admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $pdo->exec($sql);
    echo "✅ Admins table created successfully.<br>";
    
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    
    $insertAdmin = "
    INSERT IGNORE INTO admins (username, password) 
    VALUES ('admin', ?);
    ";
    
    $stmt = $pdo->prepare($insertAdmin);
    $stmt->execute([$adminPassword]);
    
    if ($stmt->rowCount() > 0) {
        echo "✅ Admin user created successfully.<br>";
        echo "<strong>Login Credentials:</strong><br>";
        echo "Username: admin<br>";
        echo "Password: admin123<br>";
        echo "<em>⚠️ Please change the default password after first login!</em><br>";
    } else {
        echo "ℹ️ Admin user already exists.<br>";
    }
    
    echo "<br><strong>🎉 Database setup completed!</strong><br>";
    echo "You can now use the login system with your admins table!<br>";
    echo "<br><strong>Next steps:</strong><br>";
    echo "1. Go to: <a href='index.php'>index.php</a> to test the login<br>";
    echo "2. Use username: admin, password: admin123<br>";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>