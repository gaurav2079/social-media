<?php
include '../core/init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tweet_id'])) {
    $tweet_id = $_POST['tweet_id'];
    $user_id = $_SESSION['user_id'];
    
    // Check if user is logged in
    if (!User::checkLogIn()) {
        echo json_encode(['success' => false, 'message' => 'Please log in.']);
        exit();
    }
    
    // Get tweet data using the fixed method
    $tweet_data = Tweet::getTweetForEdit($tweet_id);
    
    if ($tweet_data) {
        // Check if the tweet belongs to the user
        if ($tweet_data->user_id == $user_id) {
            $tweet_text = '';
            
            // Get the correct text based on tweet type
            if (isset($tweet_data->type) && $tweet_data->type === 'tweet') {
                $tweet_text = $tweet_data->status;
            } else if (isset($tweet_data->type) && $tweet_data->type === 'retweet') {
                $tweet_text = $tweet_data->retweet_msg;
            } else {
                // Fallback for older method
                if (isset($tweet_data->status)) {
                    $tweet_text = $tweet_data->status;
                } else if (isset($tweet_data->retweet_msg)) {
                    $tweet_text = $tweet_data->retweet_msg;
                }
            }
            
            echo json_encode([
                'success' => true,
                'tweet_text' => $tweet_text,
                'tweet_id' => $tweet_id
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'You can only edit your own tweets.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Tweet not found.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>