<?php
// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

echo "<h2>Checking Database Structure</h2>";

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

// Check tables structure
$tables = ['users', 'tweets', 'follows', 'likes', 'retweets', 'comments', 'notifications'];

foreach ($tables as $table) {
    echo "<h3>Table: $table</h3>";
    try {
        $stmt = $pdo->query("DESCRIBE $table");
        $columns = $stmt->fetchAll();
        
        echo "<table border='1' style='width: 100%; border-collapse: collapse;'>";
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
        
        // Show sample data
        $stmt = $pdo->query("SELECT * FROM $table LIMIT 3");
        $sample = $stmt->fetchAll();
        echo "<h4>Sample Data (3 rows):</h4>";
        echo "<pre>";
        print_r($sample);
        echo "</pre>";
        
    } catch (PDOException $e) {
        echo "<p style='color: red;'>Table $table doesn't exist or error: " . $e->getMessage() . "</p>";
    }
    echo "<hr>";
}
?>