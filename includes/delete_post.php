<?php
include 'core/init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $id = $_POST['id'];
    $type = $_POST['type'];
    
    $response = array('success' => false, 'message' => '');
    
    try {
        if ($type === 'tweet') {
            // Check if user can delete this tweet
            if (!Tweet::canDeleteTweet($user_id, $id)) {
                $response['message'] = 'You can only delete your own tweets';
                echo json_encode($response);
                exit;
            }
            
            $result = Tweet::deleteTweet($id);
            
        } else if ($type === 'comment') {
            // Check if user can delete this comment
            if (!Tweet::canEditComment($user_id, $id)) {
                $response['message'] = 'You can only delete your own comments';
                echo json_encode($response);
                exit;
            }
            
            $result = Tweet::deleteComment($id);
            
        } else if ($type === 'reply') {
            // Check if user can delete this reply
            if (!Tweet::canEditReply($user_id, $id)) {
                $response['message'] = 'You can only delete your own replies';
                echo json_encode($response);
                exit;
            }
            
            $result = Tweet::deleteReply($id);
            
        } else {
            $response['message'] = 'Invalid type';
            echo json_encode($response);
            exit;
        }
        
        if ($result) {
            $response['success'] = true;
            $response['message'] = 'Deleted successfully';
        } else {
            $response['message'] = 'Failed to delete';
        }
        
    } catch (Exception $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
    
    echo json_encode($response);
}
?>