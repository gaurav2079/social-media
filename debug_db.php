<?php
include 'core/init.php';

echo "<h2>Database Structure Debug</h2>";

try {
    $pdo = new PDO("mysql:host=localhost;dbname=tweetphp", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check tweets table
    echo "<h3>Tweets Table Structure:</h3>";
    $stmt = $pdo->query("DESCRIBE tweets");
    $tweets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($tweets);
    echo "</pre>";
    
    // Check posts table
    echo "<h3>Posts Table Structure:</h3>";
    $stmt = $pdo->query("DESCRIBE posts");
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($posts);
    echo "</pre>";
    
    // Check retweets table
    echo "<h3>Retweets Table Structure:</h3>";
    $stmt = $pdo->query("DESCRIBE retweets");
    $retweets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($retweets);
    echo "</pre>";
    
    // Check if a specific tweet exists
    if (isset($_GET['tweet_id'])) {
        $tweet_id = $_GET['tweet_id'];
        echo "<h3>Checking Tweet ID: $tweet_id</h3>";
        
        // Check in tweets table
        $stmt = $pdo->prepare("SELECT * FROM tweets WHERE post_id = ?");
        $stmt->execute([$tweet_id]);
        $tweet_data = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "In tweets table: ";
        print_r($tweet_data);
        
        // Check in posts table
        $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
        $stmt->execute([$tweet_id]);
        $post_data = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "In posts table: ";
        print_r($post_data);
        
        // Check in retweets table
        $stmt = $pdo->prepare("SELECT * FROM retweets WHERE post_id = ?");
        $stmt->execute([$tweet_id]);
        $retweet_data = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "In retweets table: ";
        print_r($retweet_data);
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
?>