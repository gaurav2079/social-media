<?php
include '../core/init.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tweet_id']) && isset($_POST['tweet_text'])) {
    $tweet_id = $_POST['tweet_id'];
    $tweet_text = trim($_POST['tweet_text']);
    $user_id = $_SESSION['user_id'];
    
    error_log("Edit tweet request - Tweet ID: $tweet_id, User ID: $user_id");
    
    // Check if user is logged in
    if (!User::checkLogIn()) {
        echo json_encode(['success' => false, 'message' => 'Please log in.']);
        exit();
    }
    
    // Validate input
    if (empty($tweet_text)) {
        echo json_encode(['success' => false, 'message' => 'Tweet cannot be empty.']);
        exit();
    }
    
    if (strlen($tweet_text) > 280) {
        echo json_encode(['success' => false, 'message' => 'Tweet cannot exceed 280 characters.']);
        exit();
    }
    
    // Get tweet data to verify ownership using the fixed method
    $tweet_data = Tweet::getTweetForEdit($tweet_id);
    
    error_log("Tweet data: " . print_r($tweet_data, true));
    
    if ($tweet_data && $tweet_data->user_id == $user_id) {
        error_log("User owns tweet, proceeding with update");
        
        // Update the tweet using the fixed method
        if (Tweet::updateTweet($tweet_id, $tweet_text)) {
            error_log("Tweet update successful");
            echo json_encode([
                'success' => true,
                'message' => 'Tweet updated successfully!',
                'new_text' => Tweet::getTweetLinks($tweet_text)
            ]);
        } else {
            error_log("Tweet update failed in method");
            echo json_encode(['success' => false, 'message' => 'Failed to update tweet in database.']);
        }
    } else {
        error_log("User doesn't own tweet or tweet not found");
        if (!$tweet_data) {
            echo json_encode(['success' => false, 'message' => 'Tweet not found.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'You do not have permission to edit this tweet.']);
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>