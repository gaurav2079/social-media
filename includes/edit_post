<?php
include 'core/init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $id = $_POST['id'];
    $type = $_POST['type'];
    $content = trim($_POST['content']);
    
    $response = array('success' => false, 'message' => '', 'formatted_content' => '');
    
    // Validate content
    if (empty($content)) {
        $response['message'] = 'Content cannot be empty';
        echo json_encode($response);
        exit;
    }
    
    if (strlen($content) > 280) {
        $response['message'] = 'Content is too long (max 280 characters)';
        echo json_encode($response);
        exit;
    }
    
    try {
        if ($type === 'tweet') {
            // Check if user can edit this tweet
            if (!Tweet::canEditTweet($user_id, $id)) {
                $response['message'] = 'You can only edit your own tweets';
                echo json_encode($response);
                exit;
            }
            
            // Update tweet
            if (Tweet::isTweet($id)) {
                $result = Tweet::updateTweet($id, $content);
            } else if (Tweet::isRetweet($id)) {
                $result = Tweet::updateRetweet($id, $content);
            } else {
                $response['message'] = 'Invalid tweet';
                echo json_encode($response);
                exit;
            }
            
        } else if ($type === 'comment') {
            // Check if user can edit this comment
            if (!Tweet::canEditComment($user_id, $id)) {
                $response['message'] = 'You can only edit your own comments';
                echo json_encode($response);
                exit;
            }
            
            $result = Tweet::updateComment($id, $content);
            
        } else if ($type === 'reply') {
            // Check if user can edit this reply
            if (!Tweet::canEditReply($user_id, $id)) {
                $response['message'] = 'You can only edit your own replies';
                echo json_encode($response);
                exit;
            }
            
            $result = Tweet::updateReply($id, $content);
            
        } else {
            $response['message'] = 'Invalid type';
            echo json_encode($response);
            exit;
        }
        
        if ($result) {
            $response['success'] = true;
            $response['formatted_content'] = Tweet::getTweetLinks($content);
            $response['message'] = 'Updated successfully';
        } else {
            $response['message'] = 'Failed to update';
        }
        
    } catch (Exception $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
    
    echo json_encode($response);
}
?>