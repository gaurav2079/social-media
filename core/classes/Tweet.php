<?php 

class Tweet extends User {
    
    protected static $pdo;
      
    public static function tweets($user_id) {
        $stmt = self::connect()->prepare("SELECT * from `posts`
        WHERE user_id = :user_id OR user_id IN (SELECT following_id from `follow` WHERE follower_id = :user_id)
        ORDER BY post_on DESC");
        $stmt->bindParam(":user_id" , $user_id , PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    public static function tweetsUser($user_id) {
        $stmt = self::connect()->prepare("SELECT * from `posts`
        WHERE user_id = :user_id
        ORDER BY post_on DESC");
        $stmt->bindParam(":user_id" , $user_id , PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    public static function likedTweets($user_id) {
        $stmt = self::connect()->prepare("SELECT * from `posts`
        WHERE id IN (SELECT post_id from `likes` WHERE user_id = :user_id)
        ORDER BY post_on DESC");
        $stmt->bindParam(":user_id" , $user_id , PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    public static function mediaTweets($user_id) {
        $stmt = self::connect()->prepare("SELECT * from `posts`
        WHERE id IN (SELECT post_id from `tweets` WHERE user_id = :user_id AND img is not null)
        ORDER BY post_on DESC");
        $stmt->bindParam(":user_id" , $user_id , PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    public static function comments($tweet_id) {
        $stmt = self::connect()->prepare("SELECT * from `comments`
        WHERE post_id = :tweet_id
        ORDER BY time");
        $stmt->bindParam(":tweet_id" , $tweet_id , PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public static function replies($comment_id) {
        $stmt = self::connect()->prepare("SELECT * from `replies`
        WHERE comment_id = :comment_id
        ORDER BY time");
        $stmt->bindParam(":comment_id" , $comment_id , PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public static function isTweet($tweet_id){
        $stmt = self::connect()->prepare("SELECT * FROM `tweets` 
        WHERE `post_id` = :tweet_id");
        $stmt->bindParam(":tweet_id", $tweet_id, PDO::PARAM_INT);
        $stmt->execute(); 

        if ($stmt->rowCount() > 0) {
            return true;
        } else return false;
    }
    
    public static function isRetweet($tweet_id){
        $stmt = self::connect()->prepare("SELECT * FROM `retweets` 
        WHERE `post_id` = :tweet_id");
        $stmt->bindParam(":tweet_id", $tweet_id, PDO::PARAM_INT);
        $stmt->execute(); 

        if ($stmt->rowCount() > 0) {
            return true;
        } else return false;
    }

    public static function getTimeAgo($timestamp){
        date_default_timezone_set("Africa/Cairo");
           
        $time_ago        = strtotime($timestamp);
        $current_time = strtotime(date("Y-m-d H:i:s")); 
        $time_difference = $current_time - $time_ago;
        $seconds         = $time_difference;
        
        $minutes = round($seconds / 60);
        $hours   = round($seconds / 3600);
        $days    = round($seconds / 86400);
        $weeks   = round($seconds / 604800);
        $months  = round($seconds / 2629440);
        $years   = round($seconds / 31553280);
               
        if ($seconds <= 60){
            return "just now";
        } else if ($minutes <= 60){
            if ($minutes == 1){
                return "one minute ago";
            } else {
                return "$minutes minutes ago";
            }
        } else if ($hours <= 24){
            if ($hours == 1){
                return "an hour ago";
            } else {
                return "$hours hrs ago";
            }
        } else if ($days <= 7){
            if ($days == 1){
                return "yesterday";
            } else {
                return "$days days ago";
            }
        } else if ($weeks <= 4.3){
            if ($weeks == 1){
                return "a week ago";
            } else {
                return "$weeks weeks ago";
            }
        } else if ($months <= 12){
            if ($months == 1){
                return "a month ago";
            } else {
                return "$months months ago";
            }
        } else {
            if ($years == 1){
                return "one year ago";
            } else {
                return "$years years ago";
            }
        }
    }

    public static function getTrendByHash($hashtag){
        $stmt = self::connect()->prepare("SELECT * FROM `trends` 
        WHERE `hashtag` LIKE :hashtag LIMIT 5");
        $stmt->bindValue(":hashtag", $hashtag.'%');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public static function getMention($mension){
        $stmt = self::connect()->prepare("SELECT `id`,`username`,`name`,`img` FROM `users` 
        WHERE `username` LIKE :mension OR `name` LIKE :mension LIMIT 5");
        $stmt->bindValue("mension", $mension.'%');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    public static function HashtagExist($hash){
        $stmt = self::connect()->prepare("SELECT * FROM `trends` 
        WHERE `hashtag` = '$hash' ");
        $stmt->execute(); 

        if ($stmt->rowCount() > 0) {
            return true;
        } else return false;
    }

    public static function addTrend($hashtag){
        preg_match_all("/#+([a-zA-Z0-9_]+)/i", $hashtag, $matches);
        if($matches){
            $result = array_values($matches[1]);
        }
        $sql = "INSERT INTO `trends` (`hashtag`, `created_on`) VALUES (:hashtag, CURRENT_TIMESTAMP)";
        foreach ($result as $trend) { 
             if (!Tweet::HashtagExist($trend)) {
                 
                 if($stmt = self::connect()->prepare($sql)){
                     $stmt->execute(array(':hashtag' => $trend));
                 }
             }
        }
    } 

    public static function getTweetLinks($tweet){
        $tweet = preg_replace("/(https?:\/\/)([\w]+.)([\w\.]+)/", "<a href='$0' target='_blink'>$0</a>", $tweet);
        $tweet = preg_replace("/#([\w]+)/", "<a class='hash-tweet' href='#'>$0</a>", $tweet);		
        $tweet = preg_replace("/@([\w]+)/", "<a class='hash-tweet' href=' ".BASE_URL."$1'>$0</a>", $tweet);
        return $tweet;		
    }
    
    public static function hashtagAndMentionTweet($tweet){
        $tweet = preg_replace("/(https?:\/\/)([\w]+.)([\w\.]+)/", "<a href='$0' target='_blink'>$0</a>", $tweet);
        $tweet = preg_replace("/#([\w]+)/", "<a class='hash-tweet' href='#'>$0</a>", $tweet);		
        $tweet = preg_replace("/@([\w]+)/", "<a class='hash-tweet' href='#'>$0</a>", $tweet);
        return $tweet;		
    }
    
    public static function countLikes($post_id) {
        $stmt = self::connect()->prepare("SELECT COUNT(post_id) as count FROM `likes`
        WHERE post_id = :post_id");
        $stmt->bindParam(":post_id" , $post_id , PDO::PARAM_STR);
        $stmt->execute();
        $count = $stmt->fetch(PDO::FETCH_OBJ);
        return $count->count;
    }
    
    public static function countTweets($user_id) {
        $stmt = self::connect()->prepare("SELECT COUNT(user_id) as count FROM `posts`
        WHERE user_id = :user_id");
        $stmt->bindParam(":user_id" , $user_id , PDO::PARAM_STR);
        $stmt->execute();
        $count = $stmt->fetch(PDO::FETCH_OBJ);
        return $count->count;
    }

    public static function countComments($post_id) {
        $stmt = self::connect()->prepare("SELECT COUNT(post_id) as count FROM `comments`
        WHERE post_id = :post_id");
        $stmt->bindParam(":post_id" , $post_id , PDO::PARAM_STR);
        $stmt->execute();
        $count = $stmt->fetch(PDO::FETCH_OBJ);
        return $count->count;
    }
    
    public static function countReplies($comment_id) {
        $stmt = self::connect()->prepare("SELECT COUNT(comment_id) as count FROM `replies`
        WHERE comment_id = :comment_id");
        $stmt->bindParam(":comment_id" , $comment_id , PDO::PARAM_STR);
        $stmt->execute();
        $count = $stmt->fetch(PDO::FETCH_OBJ);
        return $count->count;
    }

    public static function countRetweets($tweet_id) {
        $stmt = self::connect()->prepare("SELECT COUNT(*) as count FROM `retweets`
        WHERE (`tweet_id` = :tweet_id or `retweet_id` = :tweet_id)  and retweet_msg is null 
        GROUP BY tweet_id , retweet_id");
        $stmt->bindParam(":tweet_id" , $tweet_id , PDO::PARAM_STR);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $count = $stmt->fetch(PDO::FETCH_OBJ);
            return $count->count;
        } else return false;
    }

    public static function unLike($user_id, $tweet_id){
        $stmt = self::connect()->prepare("DELETE FROM `likes` 
        WHERE `user_id` = :user_id and `post_id` = :tweet_id");
        $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        $stmt->bindParam(":tweet_id", $tweet_id, PDO::PARAM_INT);
        $stmt->execute(); 

        if ($stmt->rowCount() > 0) {
            return true;
        } else return false;
    }

    public static function userLikeIt( $user_id ,$tweet_id){
        $stmt = self::connect()->prepare("SELECT `post_id` , `user_id` FROM `likes` 
        WHERE `user_id` = :user_id and `post_id` = :tweet_id");
        $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        $stmt->bindParam(":tweet_id", $tweet_id, PDO::PARAM_INT);
        $stmt->execute(); 

        if ($stmt->rowCount() > 0) {
            return true;
        } else return false;
    }
    
    public static function usersLiked($tweet_id){
        $stmt = self::connect()->prepare("SELECT `post_id` , `user_id` FROM `likes` 
        WHERE  `post_id` = :tweet_id");
        $stmt->bindParam(":tweet_id", $tweet_id, PDO::PARAM_INT);
        $stmt->execute(); 
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public static function userRetweeetedIt($user_id ,$tweet_id){
        $stmt = self::connect()->prepare("SELECT `id` , `user_id` FROM `posts` JOIN `retweets`
        on id = post_id
        WHERE `user_id` = :user_id and (`tweet_id` = :tweet_id or `retweet_id` = :tweet_id)  and retweet_msg is NULL");
        $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        $stmt->bindParam(":tweet_id", $tweet_id, PDO::PARAM_INT);
        $stmt->execute(); 

        if ($stmt->rowCount() > 0) {
            return true;
        } else return false;
    } 
    
    public static function usersRetweeeted($tweet_id){
        $stmt = self::connect()->prepare("SELECT `id` , `user_id` FROM `posts` JOIN `retweets`
        on id = post_id
        WHERE (`tweet_id` = :tweet_id or `retweet_id` = :tweet_id)  and retweet_msg is NULL");
        $stmt->bindParam(":tweet_id", $tweet_id, PDO::PARAM_INT);
        $stmt->execute(); 
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    public static function checkRetweet($user_id ,$tweet_id){
        $stmt = self::connect()->prepare("SELECT `id` , `user_id` FROM `posts` JOIN `retweets`
        on id = post_id
        WHERE `user_id` = :user_id and `post_id` = :tweet_id  and retweet_msg is NULL");
        $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        $stmt->bindParam(":tweet_id", $tweet_id, PDO::PARAM_INT);
        $stmt->execute(); 

        if ($stmt->rowCount() > 0) {
            return true;
        } else return false;
    }

    public static function undoRetweet($user_id , $tweet_id) {
        $stmt = self::connect()->prepare("DELETE FROM `posts` 
        WHERE `user_id` = :user_id and `id` = :tweet_id
        ");
        $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        $stmt->bindParam(":tweet_id", $tweet_id, PDO::PARAM_INT);
        $stmt->execute(); 

        if ($stmt->rowCount() > 0) {
            return true;
        } else return false;
    }

    public static function retweetRealId($tweet_id , $user_id) {
        $stmt = self::connect()->prepare("SELECT post_id FROM retweets JOIN posts
        on id = post_id
        WHERE (tweet_id = :tweet_id or  retweet_id = :tweet_id) and `user_id` = :user_id");
        $stmt->bindParam(":tweet_id" , $tweet_id , PDO::PARAM_STR);
        $stmt->bindParam(":user_id" , $user_id , PDO::PARAM_STR);
        $stmt->execute();
        $id = $stmt->fetch(PDO::FETCH_OBJ);
        return $id->post_id;
    }

    public static function likedTweetRealId($tweet_id) {
        $stmt = self::connect()->prepare("SELECT tweet_id FROM retweets 
        WHERE post_id = :tweet_id");
        $stmt->bindParam(":tweet_id" , $tweet_id , PDO::PARAM_STR);
        $stmt->execute();
        $id = $stmt->fetch(PDO::FETCH_OBJ);
        return $id->tweet_id;
    }
    
    public static function getTweet($tweet_id){
        $stmt = self::connect()->prepare("SELECT * FROM `tweets` JOIN `posts` 
        on posts.id = tweets.post_id 
        WHERE `post_id` = :tweet_id");
        $stmt->bindParam(":tweet_id", $tweet_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }
    
    public static function getComment($tweet_id){
        $stmt = self::connect()->prepare("SELECT * FROM `comments` 
        WHERE `id` = :tweet_id");
        $stmt->bindParam(":tweet_id", $tweet_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }
    
    public static function getRetweet($tweet_id){
        $stmt = self::connect()->prepare("SELECT * FROM `retweets` JOIN `posts` 
        on id = post_id 
        WHERE `post_id` = :tweet_id");
        $stmt->bindParam(":tweet_id", $tweet_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    } 
    
    public static function getData($id) {
        $stmt = self::connect()->prepare("SELECT * from `posts` WHERE `id` = :id");
        $stmt->bindParam(":id" , $id , PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }  

    public static function includeHeader($title) {
        global $tweets;
        $tweets = $title;
        include 'includes/tweets.php';
    }

       // DELETE TWEET FUNCTION - COMPLETE VERSION
    public static function deleteTweet($tweet_id, $user_id) {
        try {
            $pdo = self::connect();
            
            // Start transaction
            $pdo->beginTransaction();
            
            // Check if the user owns the tweet
            $ownership_stmt = $pdo->prepare("SELECT user_id FROM posts WHERE id = ?");
            $ownership_stmt->execute([$tweet_id]);
            
            if ($ownership_stmt->rowCount() == 0) {
                $pdo->rollBack();
                return ['success' => false, 'message' => 'Tweet not found'];
            }
            
            $tweet_data = $ownership_stmt->fetch(PDO::FETCH_OBJ);
            
            if ($tweet_data->user_id != $user_id) {
                $pdo->rollBack();
                return ['success' => false, 'message' => 'You can only delete your own tweets'];
            }
            
            // Delete from related tables first
            $delete_likes = $pdo->prepare("DELETE FROM likes WHERE post_id = ?");
            $delete_likes->execute([$tweet_id]);
            
            $delete_retweets = $pdo->prepare("DELETE FROM retweets WHERE post_id = ? OR tweet_id = ? OR retweet_id = ?");
            $delete_retweets->execute([$tweet_id, $tweet_id, $tweet_id]);
            
            $delete_comments = $pdo->prepare("DELETE FROM comments WHERE post_id = ?");
            $delete_comments->execute([$tweet_id]);
            
            $delete_tweet_data = $pdo->prepare("DELETE FROM tweets WHERE post_id = ?");
            $delete_tweet_data->execute([$tweet_id]);
            
            // Finally delete the main post
            $delete_post = $pdo->prepare("DELETE FROM posts WHERE id = ?");
            $delete_post->execute([$tweet_id]);
            
            // Commit transaction
            $pdo->commit();
            
            return ['success' => true, 'message' => 'Tweet deleted successfully'];
            
        } catch (PDOException $e) {
            if (isset($pdo)) {
                $pdo->rollBack();
            }
            error_log("Tweet deletion error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error occurred: ' . $e->getMessage()];
        }
    }

    // REPORT TWEET FUNCTION
    public static function reportTweet($user_id, $tweet_id, $reason, $description = '') {
        try {
            // Check if tweet exists
            $tweet_exists = self::connect()->prepare("SELECT id FROM posts WHERE id = ?");
            $tweet_exists->execute([$tweet_id]);
            
            if ($tweet_exists->rowCount() == 0) {
                return ['success' => false, 'message' => 'Tweet does not exist'];
            }
            
            // Check if user already reported this tweet
            $check_stmt = self::connect()->prepare("SELECT id FROM reports WHERE user_id = ? AND tweet_id = ?");
            $check_stmt->execute([$user_id, $tweet_id]);
            
            if ($check_stmt->rowCount() > 0) {
                return ['success' => false, 'message' => 'You have already reported this tweet'];
            }
            
            // Insert report
            $stmt = self::connect()->prepare("INSERT INTO reports (user_id, tweet_id, reason, description, created_at) VALUES (?, ?, ?, ?, NOW())");
            $result = $stmt->execute([$user_id, $tweet_id, $reason, $description]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Tweet reported successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to report tweet'];
            }
            
        } catch (PDOException $e) {
            error_log("Report tweet error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error occurred'];
        }
    }
    
    // CHECK IF USER CAN DELETE TWEET
    public static function canDeleteTweet($user_id, $tweet_id) {
        try {
            $stmt = self::connect()->prepare("SELECT user_id FROM posts WHERE id = ?");
            $stmt->execute([$tweet_id]);
            
            if ($stmt->rowCount() > 0) {
                $post = $stmt->fetch(PDO::FETCH_OBJ);
                return $post->user_id == $user_id;
            }
            return false;
            
        } catch (PDOException $e) {
            error_log("Can delete check error: " . $e->getMessage());
            return false;
        }
    }

}
?>