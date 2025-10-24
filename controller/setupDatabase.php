<?php
$host = "localhost";
$user = "root";
$pass = ""; 
$db = "ailpo";

if ($host === false || $user === false || $pass === false) {
    die("Error: Database credentials are not set in environment variables (DB_HOST, DB_USER, DB_PASS).");
}
try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS ailpo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database 'ailpo' created successfully.<br>";
    
    $pdo->exec("USE ailpo");

    // Create admins table
    $sql = "
    CREATE TABLE IF NOT EXISTS admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $pdo->exec($sql);
    echo "Admins table created successfully.<br>";

    // Create companies table
    $sql = "
    CREATE TABLE IF NOT EXISTS companies (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        address VARCHAR(255),
        industry_sector VARCHAR(100),
        website VARCHAR(255)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $pdo->exec($sql);
    echo "Companies table created successfully.<br>";

    // Create persons table
    $sql = "
    CREATE TABLE IF NOT EXISTS persons (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        position VARCHAR(100),
        email VARCHAR(100),
        phone VARCHAR(50)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $pdo->exec($sql);
    echo "Persons table created successfully.<br>";

    // Create scopes table
    $sql = "
    CREATE TABLE IF NOT EXISTS scopes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $pdo->exec($sql);
    echo "Scopes table created successfully.<br>";

    // Create agreements table
    $sql = "
    CREATE TABLE IF NOT EXISTS agreements (
        id INT AUTO_INCREMENT PRIMARY KEY,
        funding_source VARCHAR(100),
        budget_amount DECIMAL(12,2)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $pdo->exec($sql);
    echo "Agreements table created successfully.<br>";

    // Create academe_information table
    $sql = "
    CREATE TABLE IF NOT EXISTS academe_information (
        id INT AUTO_INCREMENT PRIMARY KEY,
        department_program VARCHAR(255),
        faculty_coordinator VARCHAR(100),
        contact_number VARCHAR(50),
        email_academe VARCHAR(100)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $pdo->exec($sql);
    echo "Academe information table created successfully.<br>";

    // Create deliverables table
    $sql = "
    CREATE TABLE IF NOT EXISTS deliverables (
        id INT AUTO_INCREMENT PRIMARY KEY,
        expected_outputs TEXT,
        kpi_success_metrics TEXT,
        objectives TEXT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $pdo->exec($sql);
    echo "Deliverables table created successfully.<br>";

    // Create partnerships table
    $sql = "
    CREATE TABLE IF NOT EXISTS partnerships (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        agreement_start_date DATE,
        agreement_end_date DATE,
        mou_contract VARCHAR(255),
        academe_liaison_id INT,
        custom_scope TEXT NULL,
        status VARCHAR(20) DEFAULT 'active',
        termination_date DATE NULL,
        termination_reason TEXT NULL,
        terminated_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (company_id) REFERENCES companies(id),
        FOREIGN KEY (academe_liaison_id) REFERENCES persons(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $pdo->exec($sql);
    echo "Partnerships table created successfully.<br>";

    // Create partnership_contacts table
    $sql = "
    CREATE TABLE IF NOT EXISTS partnership_contacts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        partnership_id INT NOT NULL,
        person_id INT NOT NULL,
        contact_role VARCHAR(50),
        FOREIGN KEY (partnership_id) REFERENCES partnerships(id),
        FOREIGN KEY (person_id) REFERENCES persons(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $pdo->exec($sql);
    echo "Partnership contacts table created successfully.<br>";

    // Create partnership_scopes table
    $sql = "
    CREATE TABLE IF NOT EXISTS partnership_scopes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        partnership_id INT NOT NULL,
        scope_id INT NOT NULL,
        FOREIGN KEY (partnership_id) REFERENCES partnerships(id),
        FOREIGN KEY (scope_id) REFERENCES scopes(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $pdo->exec($sql);
    echo "Partnership scopes table created successfully.<br>";

    // Create projects table
    $sql = "
    CREATE TABLE IF NOT EXISTS projects (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        project_type VARCHAR(50),
        start_date DATE,
        end_date DATE,
        agreement_id INT,
        academe_id INT,
        industry_partner_id INT,
        deliverable_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (agreement_id) REFERENCES agreements(id),
        FOREIGN KEY (academe_id) REFERENCES academe_information(id),
        FOREIGN KEY (industry_partner_id) REFERENCES companies(id),
        FOREIGN KEY (deliverable_id) REFERENCES deliverables(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $pdo->exec($sql);
    echo "Projects table created successfully.<br>";

    // Create milestones table
    $sql = "
    CREATE TABLE IF NOT EXISTS milestones (
        id INT AUTO_INCREMENT PRIMARY KEY,
        project_id INT,
        name VARCHAR(255),
        description TEXT,
        start_date DATE,
        end_date DATE,
        person_responsible VARCHAR(100),
        FOREIGN KEY (project_id) REFERENCES projects(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $pdo->exec($sql);
    echo "Milestones table created successfully.<br>";

    // Create students table (used by Dashboard and placement/student features)
    $sql = "
    CREATE TABLE IF NOT EXISTS students (
        id INT AUTO_INCREMENT PRIMARY KEY,
        school_id VARCHAR(50) NOT NULL UNIQUE,
        first_name VARCHAR(100) NOT NULL,
        last_name VARCHAR(100) NOT NULL,
        program VARCHAR(150) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_students_name (last_name, first_name)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    $pdo->exec($sql);
    echo "Students table created successfully.<br>";

    // Create student_projects junction table (many-to-many Students <-> Projects)
    $sql = "
    CREATE TABLE IF NOT EXISTS student_projects (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        project_id INT NOT NULL,
        assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_student_project (student_id, project_id),
        CONSTRAINT fk_sp_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
        CONSTRAINT fk_sp_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
        INDEX idx_sp_project (project_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    $pdo->exec($sql);
    echo "Student-Projects table created successfully.<br>";

    // Helpful indexes for common queries
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_projects_created_at ON projects(created_at)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_projects_dates ON projects(start_date, end_date)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_partnerships_company ON partnerships(company_id)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_partnerships_status_end ON partnerships(status, agreement_end_date)");
    echo "Indexes created/ensured successfully.<br>";

    // Create activity_log table
    $sql = "
    CREATE TABLE IF NOT EXISTS `activity_log` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `occurred_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `user_id` INT NULL,
        `username` VARCHAR(100) NULL,
        `action` VARCHAR(50) NOT NULL,
        `entity_type` VARCHAR(50) NULL,
        `entity_id` INT NULL,
        `description` VARCHAR(255) NULL,
        `details` TEXT NULL,
        `ip_address` VARCHAR(45) NULL,
        `user_agent` VARCHAR(255) NULL,
        INDEX `idx_occurred_at` (`occurred_at`),
        INDEX `idx_action` (`action`),
        INDEX `idx_user` (`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    $pdo->exec($sql);
    echo "Activity log table created successfully.<br>";

    // Insert default scopes
    $defaultScopes = [
        'Research and Development',
        'Internship Programs', 
        'Training and Workshops',
        'Consultancy Services',
        'Technology Transfer',
        'Others'
    ];
    
    $insertScope = "INSERT IGNORE INTO scopes (name) VALUES (?)";
    $stmt = $pdo->prepare($insertScope);
    
    foreach ($defaultScopes as $scope) {
        $stmt->execute([$scope]);
    }
    echo "Default scopes inserted successfully.<br>";
    
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    
    $insertAdmin = "
    INSERT IGNORE INTO admins (username, password) 
    VALUES ('admin', ?);
    ";
    
    $stmt = $pdo->prepare($insertAdmin);
    $stmt->execute([$adminPassword]);
    
    if ($stmt->rowCount() > 0) {
        echo "Admin user created successfully.<br>";
        echo "<strong>Login Credentials:</strong><br>";
        echo "Username: admin<br>";
        echo "Password: admin123<br>";
        echo "<em>Please change the default password after first login!</em><br>";
    } else {
        echo "Admin user already exists.<br>";
    }
    
    echo "<br><strong>🎉 Database setup completed!</strong><br>";
    echo "You can now use the login system with your admins table!<br>";
    echo "<br><strong>Next steps:</strong><br>";
    echo "1. Go to: <a href='index.php'>index.php</a> to test the login<br>";
    echo "2. Use username: admin, password: admin123<br>";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>