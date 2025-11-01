<?php
// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

echo "<h2>Admin Setup for TweetPHP Database</h2>";

// Create a direct database connection to tweetphp
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=tweetphp;charset=utf8mb4",
        "root", 
        "",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
        ]
    );
    echo "<p style='color: green;'>✓ Database 'tweetphp' connected successfully</p>";
} catch (PDOException $e) {
    die("<p style='color: red;'>✗ Database connection failed: " . $e->getMessage() . "</p>");
}

// Drop and recreate tables to ensure clean setup
$sql = [
    "DROP TABLE IF EXISTS `admin_logs`",
    "DROP TABLE IF EXISTS `reports`", 
    "DROP TABLE IF EXISTS `admins`",
    
    "CREATE TABLE `admins` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `username` varchar(50) NOT NULL,
        `email` varchar(100) NOT NULL,
        `password` varchar(255) NOT NULL,
        `role` enum('super_admin','admin','moderator') DEFAULT 'admin',
        `permissions` text,
        `is_active` tinyint(1) DEFAULT 1,
        `last_login` datetime DEFAULT NULL,
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `username` (`username`),
        UNIQUE KEY `email` (`email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
    
    "CREATE TABLE `admin_logs` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `admin_id` int(11) NOT NULL,
        `action` varchar(255) NOT NULL,
        `description` text,
        `ip_address` varchar(45) DEFAULT NULL,
        `user_agent` text,
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `admin_id` (`admin_id`),
        KEY `created_at` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
    
    "CREATE TABLE `reports` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `tweet_id` int(11) NOT NULL,
        `reason` varchar(255) NOT NULL,
        `description` text,
        `status` enum('pending','reviewed','resolved','dismissed') DEFAULT 'pending',
        `admin_notes` text,
        `resolved_by` int(11) DEFAULT NULL,
        `resolved_at` datetime DEFAULT NULL,
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `user_id` (`user_id`),
        KEY `tweet_id` (`tweet_id`),
        KEY `status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
];

foreach ($sql as $query) {
    try {
        $pdo->exec($query);
        echo "<p style='color: green;'>✓ Executed: " . substr($query, 0, 50) . "...</p>";
    } catch (PDOException $e) {
        echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
    }
}

// Create admin user with password 'admin123'
$password = 'admin123';
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("INSERT INTO admins (username, email, password, role, permissions) VALUES (?, ?, ?, ?, ?)");
try {
    $stmt->execute(['admin', 'admin@kabi.com', $hashed_password, 'super_admin', 'all']);
    echo "<p style='color: green;'>✓ Admin user created successfully</p>";
    echo "<p><strong>Username:</strong> admin</p>";
    echo "<p><strong>Password:</strong> admin123</p>";
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Error creating admin user: " . $e->getMessage() . "</p>";
}

// Verify the admin user was created
$stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
$stmt->execute(['admin']);
$admin = $stmt->fetch();

if ($admin) {
    echo "<p style='color: green;'>✓ Admin user verified in database</p>";
    
    // Test password verification
    if (password_verify('admin123', $admin->password)) {
        echo "<p style='color: green;'>✓ Password verification successful</p>";
    } else {
        echo "<p style='color: red;'>✗ Password verification failed</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Admin user not found in database</p>";
}

echo "<hr>";
echo "<p><a href='login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Login</a></p>";
?>