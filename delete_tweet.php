<?php
session_start();

// Set JSON header first
header('Content-Type: application/json');

// Enable error reporting but don't display to users
error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
    // Check if init.php exists
    if (!file_exists('core/init.php')) {
        throw new Exception('System configuration file not found');
    }
    
    require_once 'core/init.php';

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Not authenticated');
    }

    $user_id = $_SESSION['user_id'];

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    if (!isset($_POST['action'])) {
        throw new Exception('No action specified');
    }

    $action = $_POST['action'];

    if ($action === 'delete_tweet') {
        if (!isset($_POST['tweet_id'])) {
            throw new Exception('Tweet ID is required');
        }

        $tweet_id = intval($_POST['tweet_id']);
        
        if ($tweet_id <= 0) {
            throw new Exception('Invalid tweet ID');
        }

        // Validate tweet ownership
        if (!Tweet::canDeleteTweet($user_id, $tweet_id)) {
            throw new Exception('You can only delete your own tweets');
        }

        // Delete the tweet
        $result = Tweet::deleteTweet($tweet_id, $user_id);
        
        if ($result['success']) {
            echo json_encode(['success' => true, 'message' => 'Tweet deleted successfully']);
        } else {
            throw new Exception($result['message']);
        }

    } elseif ($action === 'report_tweet') {
        if (!isset($_POST['tweet_id']) || !isset($_POST['reason'])) {
            throw new Exception('Tweet ID and reason are required');
        }

        $tweet_id = intval($_POST['tweet_id']);
        $reason = trim($_POST['reason']);
        $description = isset($_POST['description']) ? trim($_POST['description']) : '';

        if ($tweet_id <= 0) {
            throw new Exception('Invalid tweet ID');
        }

        if (empty($reason)) {
            throw new Exception('Reason is required');
        }

        $result = Tweet::reportTweet($user_id, $tweet_id, $reason, $description);
        
        if ($result['success']) {
            echo json_encode(['success' => true, 'message' => 'Tweet reported successfully']);
        } else {
            throw new Exception($result['message']);
        }

    } else {
        throw new Exception('Invalid action');
    }

} catch (Exception $e) {
    // Log the error for debugging
    error_log("delete_tweet.php error: " . $e->getMessage());
    
    // Return JSON error response
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}

exit;
?>