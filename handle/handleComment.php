<?php
session_start();
include '../core/init.php';

if ($_POST) {
    $tweet_id = $_POST['tweet_id'];
    $comment = $_POST['comment'];
    $user_id = $_SESSION['user_id'];
    
    // Validate comment
    if (empty($comment)) {
        echo json_encode(['status' => 'error', 'message' => 'Comment cannot be empty']);
        exit();
    }
    
    if (strlen($comment) > 140) {
        echo json_encode(['status' => 'error', 'message' => 'Comment too long']);
        exit();
    }
    
    // Handle image upload if exists
    $image = '';
    if (!empty($_FILES['comment_img']['name'])) {
        $image = Tweet::uploadImage($_FILES['comment_img']);
        if (!$image) {
            $image = ''; // Set to empty if upload fails
        }
    }
    
    // Create comment using your Tweet class method
    if (Tweet::createComment($tweet_id, $user_id, $comment, $image)) {
        $commentCount = Tweet::countComments($tweet_id);
        echo json_encode(['status' => 'success', 'commentCount' => $commentCount]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to post comment']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>