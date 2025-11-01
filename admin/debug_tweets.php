<?php
// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../core/init.php';
Admin::checkAdmin();

echo "<h2>Debug Tweets Data</h2>";

// Test database connection and structure
try {
    $db = new PDO(
        "mysql:host=localhost;dbname=tweetphp;charset=utf8mb4",
        "root", 
        "",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
        ]
    );
    echo "<p style='color: green;'>✓ Database connected successfully</p>";
    
    // Check tweets table
    echo "<h3>Tweets Table</h3>";
    $stmt = $db->query("DESCRIBE tweets");
    $tweets_structure = $stmt->fetchAll();
    echo "<pre>";
    print_r($tweets_structure);
    echo "</pre>";
    
    // Check users table
    echo "<h3>Users Table</h3>";
    $stmt = $db->query("DESCRIBE users");
    $users_structure = $stmt->fetchAll();
    echo "<pre>";
    print_r($users_structure);
    echo "</pre>";
    
    // Check sample data
    echo "<h3>Sample Tweets (first 3)</h3>";
    $stmt = $db->query("SELECT * FROM tweets LIMIT 3");
    $sample_tweets = $stmt->fetchAll();
    echo "<pre>";
    print_r($sample_tweets);
    echo "</pre>";
    
    echo "<h3>Sample Users (first 3)</h3>";
    $stmt = $db->query("SELECT id, username, name, img FROM users LIMIT 3");
    $sample_users = $stmt->fetchAll();
    echo "<pre>";
    print_r($sample_users);
    echo "</pre>";
    
    // Test the getAllTweets method
    echo "<h3>Testing Admin::getAllTweets()</h3>";
    $tweets = Admin::getAllTweets(1, 3);
    echo "<p>Number of tweets found: " . count($tweets) . "</p>";
    if (!empty($tweets)) {
        echo "<pre>";
        print_r($tweets);
        echo "</pre>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Database error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='tweets.php' class='btn btn-primary'>Go to Tweets Page</a></p>";
?>