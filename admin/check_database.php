<?php
// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

echo "<h2>Database Structure Check</h2>";

// Create a direct database connection
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
    echo "<p style='color: green;'>✓ Database connected successfully</p>";
} catch (PDOException $e) {
    die("<p style='color: red;'>✗ Database connection failed: " . $e->getMessage() . "</p>");
}

// Check all tables and their structures
$tables_to_check = ['users', 'tweets', 'follow', 'follows', 'likes', 'comments', 'reports', 'admins', 'admin_logs'];

foreach ($tables_to_check as $table) {
    echo "<h3>Table: $table</h3>";
    try {
        $stmt = $pdo->query("DESCRIBE $table");
        $columns = $stmt->fetchAll();
        
        if ($columns) {
            echo "<table border='1' style='width: 100%; border-collapse: collapse; margin-bottom: 20px;'>";
            echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
            foreach ($columns as $col) {
                echo "<tr>";
                echo "<td>{$col->Field}</td>";
                echo "<td>{$col->Type}</td>";
                echo "<td>{$col->Null}</td>";
                echo "<td>{$col->Key}</td>";
                echo "<td>{$col->Default}</td>";
                echo "<td>{$col->Extra}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color: red;'>Table exists but has no columns or is empty</p>";
        }
        
    } catch (PDOException $e) {
        echo "<p style='color: orange;'>Table doesn't exist: $table</p>";
    }
}

// Check for follow-related tables
echo "<h3>Follow System Check</h3>";
$follow_tables = ['follow', 'follows', 'following', 'followers'];
foreach ($follow_tables as $table) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table LIMIT 1");
        $result = $stmt->fetch();
        echo "<p style='color: green;'>✓ Table '$table' exists with {$result->count} records</p>";
        
        // Show structure
        $stmt = $pdo->query("DESCRIBE $table");
        $columns = $stmt->fetchAll();
        echo "<small>Columns: ";
        $column_names = [];
        foreach ($columns as $col) {
            $column_names[] = $col->Field;
        }
        echo implode(', ', $column_names);
        echo "</small>";
        
    } catch (PDOException $e) {
        echo "<p style='color: orange;'>✗ Table '$table' doesn't exist</p>";
    }
}

echo "<hr>";
echo "<p><a href='setup_fix.php' class='btn btn-primary'>Run Setup</a> ";
echo "<a href='users.php' class='btn btn-secondary'>Go to Users</a></p>";
?>