<?php
class Admin {
    private static $pdo = null;
    
    private static function getDB() {
        if (self::$pdo === null) {
            try {
                self::$pdo = new PDO(
                    "mysql:host=localhost;dbname=tweetphp;charset=utf8mb4",
                    "root", 
                    "",
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
                    ]
                );
            } catch (PDOException $e) {
                die("Database connection failed: " . $e->getMessage());
            }
        }
        return self::$pdo;
    }
    
    public static function login($username, $password) {
        $db = self::getDB();
        
        try {
            $stmt = $db->prepare("SELECT * FROM admins WHERE username = ? AND is_active = 1");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();
            
            if ($admin && password_verify($password, $admin->password)) {
                // Update last login
                $stmt = $db->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?");
                $stmt->execute([$admin->id]);
                
                return $admin;
            }
        } catch (PDOException $e) {
            error_log("Admin login error: " . $e->getMessage());
        }
        
        return false;
    }
    
    public static function checkAdmin() {
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            header('location: login.php');
            exit();
        }
    }
    
    public static function logAction($admin_id, $action, $description = '') {
        $db = self::getDB();
        try {
            $stmt = $db->prepare("INSERT INTO admin_logs (admin_id, action, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $admin_id,
                $action,
                $description,
                $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
            ]);
        } catch (PDOException $e) {
            error_log("Admin log action error: " . $e->getMessage());
        }
    }
    
    public static function sendFlagNotification($tweet_id, $admin_id, $reason = "inappropriate content") {
        $db = self::getDB();
        
        try {
            $stmt = $db->prepare("
                SELECT p.user_id 
                FROM posts p 
                WHERE p.id = ?
            ");
            $stmt->execute([$tweet_id]);
            $post = $stmt->fetch();
            
            if ($post && isset($post->user_id)) {
                $user_id = $post->user_id;
                
                $stmt = $db->prepare("
                    INSERT INTO notifications (notify_for, notify_from, target, type, reason, time) 
                    VALUES (?, ?, ?, 'flag', ?, NOW())
                ");
                $result = $stmt->execute([$user_id, $admin_id, $tweet_id, $reason]);
                
                if ($result) {
                    self::logAction($admin_id, 'flag_notification', "Sent flag notification for tweet #$tweet_id to user #$user_id");
                    return true;
                }
            }
        } catch (PDOException $e) {
            error_log("Send flag notification error: " . $e->getMessage());
        }
        
        return false;
    }
    
    public static function flagTweet($tweet_id, $admin_id, $reason = "Content policy violation") {
        return self::sendFlagNotification($tweet_id, $admin_id, $reason);
    }
    
    public static function getAllUsers($page = 1, $limit = 20, $search = '') {
        $db = self::getDB();
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT u.* FROM users u";
        
        if (!empty($search)) {
            $sql .= " WHERE u.username LIKE ? OR u.name LIKE ? OR u.email LIKE ?";
        }
        
        $sql .= " ORDER BY u.id DESC LIMIT ? OFFSET ?";
        
        $stmt = $db->prepare($sql);
        if (!empty($search)) {
            $search_term = "%$search%";
            $stmt->bindValue(1, $search_term, PDO::PARAM_STR);
            $stmt->bindValue(2, $search_term, PDO::PARAM_STR);
            $stmt->bindValue(3, $search_term, PDO::PARAM_STR);
            $stmt->bindValue(4, $limit, PDO::PARAM_INT);
            $stmt->bindValue(5, $offset, PDO::PARAM_INT);
        } else {
            $stmt->bindValue(1, $limit, PDO::PARAM_INT);
            $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_OBJ);
        
        foreach ($users as $user) {
            try {
                $stmt = $db->prepare("SELECT COUNT(*) as count FROM posts WHERE user_id = ?");
                $stmt->execute([$user->id]);
                $user->tweet_count = $stmt->fetch()->count;
            } catch (PDOException $e) {
                $user->tweet_count = 0;
            }
            
            try {
                $stmt = $db->prepare("SELECT COUNT(*) as count FROM follow WHERE sender = ?");
                $stmt->execute([$user->id]);
                $user->following_count = $stmt->fetch()->count;
            } catch (PDOException $e) {
                $user->following_count = 0;
            }
            
            try {
                $stmt = $db->prepare("SELECT COUNT(*) as count FROM follow WHERE receiver = ?");
                $stmt->execute([$user->id]);
                $user->followers_count = $stmt->fetch()->count;
            } catch (PDOException $e) {
                $user->followers_count = 0;
            }
        }
        
        return $users;
    }

    public static function getAllTweets($page = 1, $limit = 20, $search = '') {
        $db = self::getDB();
        $offset = ($page - 1) * $limit;
        
        try {
            $sql = "SELECT t.*, 
                           p.user_id,
                           p.post_on as tweet_date,
                           u.username, 
                           u.name, 
                           u.img as user_img 
                    FROM tweets t 
                    JOIN posts p ON t.post_id = p.id 
                    JOIN users u ON p.user_id = u.id";
            
            if (!empty($search)) {
                $sql .= " WHERE t.status LIKE ? OR u.username LIKE ? OR u.name LIKE ?";
            }
            
            $sql .= " ORDER BY p.post_on DESC LIMIT ? OFFSET ?";
            
            $stmt = $db->prepare($sql);
            
            if (!empty($search)) {
                $search_term = "%$search%";
                $stmt->bindValue(1, $search_term, PDO::PARAM_STR);
                $stmt->bindValue(2, $search_term, PDO::PARAM_STR);
                $stmt->bindValue(3, $search_term, PDO::PARAM_STR);
                $stmt->bindValue(4, $limit, PDO::PARAM_INT);
                $stmt->bindValue(5, $offset, PDO::PARAM_INT);
            } else {
                $stmt->bindValue(1, $limit, PDO::PARAM_INT);
                $stmt->bindValue(2, $offset, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            $tweets = $stmt->fetchAll(PDO::FETCH_OBJ);
            
            foreach ($tweets as $tweet) {
                $tweet_id = $tweet->post_id;
                
                if ($tweet_id) {
                    try {
                        $stmt = $db->prepare("SELECT COUNT(*) as count FROM likes WHERE post_id = ?");
                        $stmt->execute([$tweet_id]);
                        $tweet->like_count = $stmt->fetch()->count;
                    } catch (PDOException $e) {
                        $tweet->like_count = 0;
                    }
                    
                    try {
                        $stmt = $db->prepare("SELECT COUNT(*) as count FROM retweets WHERE post_id = ?");
                        $stmt->execute([$tweet_id]);
                        $tweet->retweet_count = $stmt->fetch()->count;
                    } catch (PDOException $e) {
                        $tweet->retweet_count = 0;
                    }
                    
                    try {
                        $stmt = $db->prepare("SELECT COUNT(*) as count FROM comments WHERE post_id = ?");
                        $stmt->execute([$tweet_id]);
                        $tweet->comment_count = $stmt->fetch()->count;
                    } catch (PDOException $e) {
                        $tweet->comment_count = 0;
                    }
                } else {
                    $tweet->like_count = 0;
                    $tweet->retweet_count = 0;
                    $tweet->comment_count = 0;
                }
            }
            
            return $tweets;
            
        } catch (Exception $e) {
            error_log("getAllTweets error: " . $e->getMessage());
            return [];
        }
    }

    public static function getReports($status = 'pending', $page = 1, $limit = 20) {
        $db = self::getDB();
        $offset = ($page - 1) * $limit;
        
        try {
            $stmt = $db->query("SHOW TABLES LIKE 'reports'");
            $table_exists = $stmt->fetch();
            
            if (!$table_exists) {
                error_log("Reports table doesn't exist");
                return [];
            }
            
            $sql = "
                SELECT r.*, 
                       u.username as reporter_username, 
                       u.name as reporter_name,
                       t.status as tweet_content,
                       tu.username as tweet_author_username,
                       tu.name as tweet_author_name,
                       a.username as resolved_admin
                FROM reports r
                LEFT JOIN users u ON r.user_id = u.id
                LEFT JOIN tweets t ON r.tweet_id = t.post_id
                LEFT JOIN users tu ON t.post_id = tu.id
                LEFT JOIN admins a ON r.resolved_by = a.id
                WHERE r.status = ?
                ORDER BY r.created_at DESC 
                LIMIT ? OFFSET ?
            ";
            
            $stmt = $db->prepare($sql);
            $stmt->bindValue(1, $status, PDO::PARAM_STR);
            $stmt->bindValue(2, $limit, PDO::PARAM_INT);
            $stmt->bindValue(3, $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            $reports = $stmt->fetchAll(PDO::FETCH_OBJ);
            
            if (empty($reports)) {
                $sql = "
                    SELECT r.*, 
                           'Unknown' as reporter_username,
                           'Unknown User' as reporter_name,
                           'Tweet content not available' as tweet_content,
                           'Unknown' as tweet_author_username,
                           'Unknown User' as tweet_author_name,
                           'Admin' as resolved_admin
                    FROM reports r
                    WHERE r.status = ?
                    ORDER BY r.created_at DESC 
                    LIMIT ? OFFSET ?
                ";
                
                $stmt = $db->prepare($sql);
                $stmt->bindValue(1, $status, PDO::PARAM_STR);
                $stmt->bindValue(2, $limit, PDO::PARAM_INT);
                $stmt->bindValue(3, $offset, PDO::PARAM_INT);
                $stmt->execute();
                
                $reports = $stmt->fetchAll(PDO::FETCH_OBJ);
            }
            
            return $reports;
            
        } catch (PDOException $e) {
            error_log("Get reports error: " . $e->getMessage());
            return [];
        }
    }
    
    // FIXED: Delete tweet method that properly handles both posts and tweets tables
    public static function deleteTweet($tweet_id, $admin_id) {
        $db = self::getDB();
        
        try {
            $db->beginTransaction();
            
            error_log("Starting deletion process for tweet ID: " . $tweet_id);
            
            // Check if tweet exists in posts table
            $stmt = $db->prepare("SELECT p.*, u.username, u.name FROM posts p 
                                JOIN users u ON p.user_id = u.id 
                                WHERE p.id = ?");
            $stmt->execute([$tweet_id]);
            $post = $stmt->fetch(PDO::FETCH_OBJ);
            
            if (!$post) {
                throw new Exception("Post not found with ID: $tweet_id");
            }
            
            $user_id = $post->user_id;
            $username = $post->username;
            
            error_log("Found post owned by user: $username (ID: $user_id)");
            
            // Send notification to user
            self::sendFlagNotification($tweet_id, $admin_id, "Your post was removed by admin for violating our content policy");
            
            // Delete from all related tables in correct order
            $delete_operations = [
                'likes' => 'DELETE FROM likes WHERE post_id = ?',
                'retweets' => 'DELETE FROM retweets WHERE post_id = ? OR tweet_id = ? OR retweet_id = ?',
                'comments' => 'DELETE FROM comments WHERE post_id = ?',
                'notifications' => 'DELETE FROM notifications WHERE target = ?',
                'reports' => 'DELETE FROM reports WHERE tweet_id = ?',
                'tweets' => 'DELETE FROM tweets WHERE post_id = ?', // Delete from tweets table
                'posts' => 'DELETE FROM posts WHERE id = ?' // Finally delete from posts table
            ];
            
            foreach ($delete_operations as $table => $query) {
                try {
                    if ($table === 'retweets') {
                        $stmt = $db->prepare($query);
                        $stmt->execute([$tweet_id, $tweet_id, $tweet_id]);
                    } else {
                        $stmt = $db->prepare($query);
                        $stmt->execute([$tweet_id]);
                    }
                    $rows = $stmt->rowCount();
                    error_log("Deleted from $table: $rows rows");
                } catch (PDOException $e) {
                    error_log("Delete from $table error: " . $e->getMessage());
                    // Continue with other tables even if one fails
                }
            }
            
            // Verify deletion from both tables
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM posts WHERE id = ?");
            $stmt->execute([$tweet_id]);
            $posts_remaining = $stmt->fetch()->count;
            
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM tweets WHERE post_id = ?");
            $stmt->execute([$tweet_id]);
            $tweets_remaining = $stmt->fetch()->count;
            
            if ($posts_remaining == 0 && $tweets_remaining == 0) {
                self::logAction($admin_id, 'delete_tweet', "Successfully deleted tweet #$tweet_id from user @$username (#$user_id)");
                $db->commit();
                error_log("Successfully deleted tweet #$tweet_id from both posts and tweets tables");
                return true;
            } else {
                throw new Exception("Tweet deletion incomplete. Posts remaining: $posts_remaining, Tweets remaining: $tweets_remaining");
            }
            
        } catch (Exception $e) {
            if (isset($db)) {
                $db->rollBack();
            }
            error_log("Delete tweet error: " . $e->getMessage());
            return false;
        }
    }
    
    // Alternative simple delete method
    public static function simpleDeleteTweet($tweet_id, $admin_id) {
        $db = self::getDB();
        
        try {
            $db->beginTransaction();
            
            error_log("Simple deletion for tweet ID: " . $tweet_id);
            
            // Get user info for logging
            $stmt = $db->prepare("SELECT p.user_id, u.username FROM posts p 
                                JOIN users u ON p.user_id = u.id 
                                WHERE p.id = ?");
            $stmt->execute([$tweet_id]);
            $post = $stmt->fetch(PDO::FETCH_OBJ);
            
            if (!$post) {
                throw new Exception("Post not found");
            }
            
            // Send notification
            self::sendFlagNotification($tweet_id, $admin_id, "Your post was removed by admin");
            
            // Delete from tweets table first
            $stmt = $db->prepare("DELETE FROM tweets WHERE post_id = ?");
            $tweet_deleted = $stmt->execute([$tweet_id]);
            error_log("Deleted from tweets: " . $stmt->rowCount() . " rows");
            
            // Then delete from posts table
            $stmt = $db->prepare("DELETE FROM posts WHERE id = ?");
            $post_deleted = $stmt->execute([$tweet_id]);
            $post_rows = $stmt->rowCount();
            error_log("Deleted from posts: " . $post_rows . " rows");
            
            if ($post_rows > 0) {
                self::logAction($admin_id, 'delete_tweet', "Simply deleted tweet #$tweet_id");
                $db->commit();
                error_log("Simple delete successful for tweet #$tweet_id");
                return true;
            } else {
                throw new Exception("No post was deleted");
            }
            
        } catch (Exception $e) {
            if (isset($db)) {
                $db->rollBack();
            }
            error_log("Simple delete tweet error: " . $e->getMessage());
            return false;
        }
    }
    
    // Force delete method as backup
    public static function forceDeleteTweet($tweet_id, $admin_id) {
        $db = self::getDB();
        
        try {
            $db->beginTransaction();
            
            error_log("Force deletion for tweet ID: " . $tweet_id);
            
            // Try to get user info
            $stmt = $db->prepare("SELECT p.user_id, u.username FROM posts p 
                                JOIN users u ON p.user_id = u.id 
                                WHERE p.id = ?");
            $stmt->execute([$tweet_id]);
            $post = $stmt->fetch(PDO::FETCH_OBJ);
            
            if ($post) {
                self::sendFlagNotification($tweet_id, $admin_id, "Your post was removed by admin");
            }
            
            // Delete from all possible tables
            $tables = [
                'likes' => 'post_id',
                'retweets' => 'post_id',
                'comments' => 'post_id',
                'notifications' => 'target',
                'reports' => 'tweet_id',
                'tweets' => 'post_id', // Critical: delete from tweets table
                'posts' => 'id' // Critical: delete from posts table
            ];
            
            $success = false;
            
            foreach ($tables as $table => $column) {
                try {
                    $stmt = $db->prepare("DELETE FROM $table WHERE $column = ?");
                    $stmt->execute([$tweet_id]);
                    $rows = $stmt->rowCount();
                    error_log("Force deleted from $table: $rows rows");
                    
                    if ($table === 'posts' && $rows > 0) {
                        $success = true;
                    }
                } catch (PDOException $e) {
                    error_log("Force delete from $table error: " . $e->getMessage());
                }
            }
            
            if ($success) {
                self::logAction($admin_id, 'force_delete_tweet', "Force deleted tweet #$tweet_id");
                $db->commit();
                error_log("Force delete successful for tweet #$tweet_id");
                return true;
            } else {
                throw new Exception("Force delete failed - no posts were deleted");
            }
            
        } catch (Exception $e) {
            if (isset($db)) {
                $db->rollBack();
            }
            error_log("Force delete tweet error: " . $e->getMessage());
            return false;
        }
    }
    
    public static function bulkDeleteTweets($tweet_ids, $admin_id) {
        $db = self::getDB();
        
        try {
            $db->beginTransaction();
            $success_count = 0;
            
            foreach ($tweet_ids as $tweet_id) {
                if (self::deleteTweet($tweet_id, $admin_id)) {
                    $success_count++;
                } else if (self::forceDeleteTweet($tweet_id, $admin_id)) {
                    $success_count++;
                }
            }
            
            $db->commit();
            return $success_count;
            
        } catch (Exception $e) {
            $db->rollBack();
            error_log("Bulk delete tweets error: " . $e->getMessage());
            return 0;
        }
    }
    
    public static function getTweetStats($timeframe = 'today') {
        $db = self::getDB();
        $stats = [];
        
        try {
            $date_condition = "";
            switch ($timeframe) {
                case 'today':
                    $date_condition = "WHERE DATE(p.post_on) = CURDATE()";
                    break;
                case 'week':
                    $date_condition = "WHERE p.post_on >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
                    break;
                case 'month':
                    $date_condition = "WHERE p.post_on >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
                    break;
                default:
                    $date_condition = "";
            }
            
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM posts p $date_condition");
            $stmt->execute();
            $stats['total_tweets'] = $stmt->fetch()->count;
            
            $stmt = $db->prepare("
                SELECT COUNT(*) as count 
                FROM tweets t 
                JOIN posts p ON t.post_id = p.id 
                $date_condition AND t.img IS NOT NULL AND t.img != 'null'
            ");
            $stmt->execute();
            $stats['tweets_with_images'] = $stmt->fetch()->count;
            
            $stmt = $db->prepare("
                SELECT u.username, u.name, COUNT(p.id) as tweet_count
                FROM posts p 
                JOIN users u ON p.user_id = u.id 
                $date_condition
                GROUP BY p.user_id 
                ORDER BY tweet_count DESC 
                LIMIT 5
            ");
            $stmt->execute();
            $stats['top_users'] = $stmt->fetchAll(PDO::FETCH_OBJ);
            
            if ($timeframe === 'today') {
                $stmt = $db->prepare("
                    SELECT HOUR(p.post_on) as hour, COUNT(*) as count
                    FROM posts p 
                    WHERE DATE(p.post_on) = CURDATE()
                    GROUP BY HOUR(p.post_on)
                    ORDER BY hour
                ");
                $stmt->execute();
                $stats['hourly_distribution'] = $stmt->fetchAll(PDO::FETCH_OBJ);
            }
            
        } catch (PDOException $e) {
            error_log("Get tweet stats error: " . $e->getMessage());
        }
        
        return $stats;
    }
    
    public static function deleteUser($user_id, $admin_id) {
        $db = self::getDB();
        
        try {
            $db->beginTransaction();
            
            $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_OBJ);
            
            if (!$user) {
                throw new Exception("User not found");
            }
            
            error_log("Starting deletion process for user #$user_id by admin #$admin_id");
            
            // Get all user's posts to delete them properly
            $stmt = $db->prepare("SELECT id as post_id FROM posts WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $posts = $stmt->fetchAll(PDO::FETCH_OBJ);
            
            error_log("Found " . count($posts) . " posts for user #$user_id");
            
            // Delete all user's tweets properly
            foreach ($posts as $post) {
                self::deleteTweet($post->post_id, $admin_id);
            }
            
            // Delete from all other tables
            $delete_queries = [
                "DELETE FROM likes WHERE user_id = ?",
                "DELETE FROM retweets WHERE user_id = ?",
                "DELETE FROM comments WHERE user_id = ?",
                "DELETE FROM notifications WHERE notify_for = ?",
                "DELETE FROM notifications WHERE target = ?",
                "DELETE FROM reports WHERE user_id = ?",
                "DELETE FROM follow WHERE sender = ?",
                "DELETE FROM follow WHERE receiver = ?",
                "DELETE FROM posts WHERE user_id = ?",
                "DELETE FROM users WHERE id = ?"
            ];
            
            foreach ($delete_queries as $query) {
                try {
                    $stmt = $db->prepare($query);
                    $stmt->execute([$user_id]);
                    $affected = $stmt->rowCount();
                    error_log("Query '$query' affected $affected rows");
                } catch (PDOException $e) {
                    error_log("Delete error for query '$query': " . $e->getMessage());
                }
            }
            
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user_still_exists = $stmt->fetch()->count;
            
            if ($user_still_exists == 0) {
                self::logAction($admin_id, 'delete_user', "Deleted user #$user_id ($user->username)");
                $db->commit();
                error_log("User #$user_id successfully deleted");
                return true;
            } else {
                throw new Exception("User still exists after deletion attempt");
            }
            
        } catch (Exception $e) {
            $db->rollBack();
            error_log("Delete user error: " . $e->getMessage());
            return false;
        }
    }
    
    public static function updateReportStatus($report_id, $status, $admin_id, $notes = '') {
        $db = self::getDB();
        
        try {
            $stmt = $db->prepare("
                UPDATE reports 
                SET status = ?, admin_notes = ?, resolved_by = ?, resolved_at = NOW() 
                WHERE id = ?
            ");
            $result = $stmt->execute([$status, $notes, $admin_id, $report_id]);
            
            if ($result) {
                self::logAction($admin_id, 'update_report', "Updated report #$report_id to $status");
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Update report status error: " . $e->getMessage());
            return false;
        }
    }
    
    public static function getStats() {
        $db = self::getDB();
        
        $stats = [];
        
        try {
            $stmt = $db->query("SELECT COUNT(*) as total FROM users");
            $stats['total_users'] = $stmt->fetch(PDO::FETCH_OBJ)->total;
            
            $stmt = $db->query("SELECT COUNT(*) as total FROM posts");
            $stats['total_tweets'] = $stmt->fetch(PDO::FETCH_OBJ)->total;
            
            $stmt = $db->query("SELECT COUNT(*) as total FROM reports WHERE status = 'pending'");
            $stats['pending_reports'] = $stmt->fetch(PDO::FETCH_OBJ)->total;
            
            try {
                $stmt = $db->query("SELECT COUNT(*) as total FROM likes");
                $stats['total_likes'] = $stmt->fetch(PDO::FETCH_OBJ)->total;
            } catch (PDOException $e) {
                $stats['total_likes'] = 0;
            }
            
            try {
                $stmt = $db->prepare("SELECT COUNT(*) as total FROM posts WHERE DATE(post_on) = CURDATE()");
                $stmt->execute();
                $stats['today_tweets'] = $stmt->fetch()->total;
            } catch (PDOException $e) {
                $stats['today_tweets'] = 0;
            }
            
        } catch (PDOException $e) {
            $stats['total_users'] = 0;
            $stats['total_tweets'] = 0;
            $stats['pending_reports'] = 0;
            $stats['total_likes'] = 0;
            $stats['today_tweets'] = 0;
        }
        
        return $stats;
    }
    
    public static function createAdminTables() {
        $db = self::getDB();
        
        $sql = [
            "CREATE TABLE IF NOT EXISTS `admins` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `username` varchar(50) NOT NULL,
                `email` varchar(100) NOT NULL,
                `password` varchar(255) NOT NULL,
                `role` enum('super_admin','admin','moderator') DEFAULT 'admin',
                `permissions` text,
                `is_active` tinyint(1) DEFAULT 1,
                `last_login` datetime DEFAULT NULL,
                `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `username` (`username`),
                UNIQUE KEY `email` (`email`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
            
            "CREATE TABLE IF NOT EXISTS `admin_logs` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `admin_id` int(11) NOT NULL,
                `action` varchar(255) NOT NULL,
                `description` text,
                `ip_address` varchar(45) DEFAULT NULL,
                `user_agent` text,
                `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `admin_id` (`admin_id`),
                KEY `created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
            
            "CREATE TABLE IF NOT EXISTS `reports` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `user_id` int(11) NOT NULL,
                `tweet_id` int(11) NOT NULL,
                `reason` varchar(255) NOT NULL,
                `description` text,
                `status` enum('pending','reviewed','resolved','dismissed') DEFAULT 'pending',
                `admin_notes` text,
                `resolved_by` int(11) DEFAULT NULL,
                `resolved_at` datetime DEFAULT NULL,
                `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `user_id` (`user_id`),
                KEY `tweet_id` (`tweet_id`),
                KEY `status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
        ];
        
        foreach ($sql as $query) {
            try {
                $db->exec($query);
            } catch (PDOException $e) {
                error_log("Create table error: " . $e->getMessage());
            }
        }
        
        try {
            $stmt = $db->query("SELECT COUNT(*) as count FROM information_schema.COLUMNS WHERE TABLE_NAME = 'notifications' AND COLUMN_NAME = 'reason'");
            $column_exists = $stmt->fetch()->count > 0;
            
            if (!$column_exists) {
                $db->exec("ALTER TABLE notifications ADD COLUMN reason TEXT NULL AFTER type");
            }
        } catch (PDOException $e) {
            error_log("Add reason column error: " . $e->getMessage());
        }
        
        try {
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM admins WHERE username = 'admin'");
            $stmt->execute();
            $result = $stmt->fetch();
            
            if ($result->count == 0) {
                $password = 'admin123';
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $db->prepare("INSERT INTO admins (username, email, password, role, permissions) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute(['admin', 'admin@tweetphp.com', $hashed_password, 'super_admin', 'all']);
                
                error_log("Default admin user created. Username: admin, Password: admin123");
            }
        } catch (PDOException $e) {
            error_log("Create admin user error: " . $e->getMessage());
        }
        
        return true;
    }
    
    public static function getUserById($user_id) {
        $db = self::getDB();
        try {
            $stmt = $db->prepare("
                SELECT u.*, 
                       (SELECT COUNT(*) FROM posts WHERE user_id = u.id) as tweet_count,
                       (SELECT COUNT(*) FROM follow WHERE sender = u.id) as following_count,
                       (SELECT COUNT(*) FROM follow WHERE receiver = u.id) as followers_count
                FROM users u 
                WHERE u.id = ?
            ");
            $stmt->execute([$user_id]);
            return $stmt->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("Get user by id error: " . $e->getMessage());
            return null;
        }
    }
    
    public static function getTweetById($tweet_id) {
        $db = self::getDB();
        try {
            $stmt = $db->prepare("
                SELECT t.*, 
                       p.user_id,
                       p.post_on as tweet_date,
                       u.username, u.name, u.img as user_img,
                       (SELECT COUNT(*) FROM likes WHERE post_id = t.post_id) as like_count,
                       (SELECT COUNT(*) FROM retweets WHERE post_id = t.post_id) as retweet_count,
                       (SELECT COUNT(*) FROM comments WHERE post_id = t.post_id) as comment_count
                FROM tweets t 
                JOIN posts p ON t.post_id = p.id
                JOIN users u ON p.user_id = u.id 
                WHERE t.post_id = ?
            ");
            $stmt->execute([$tweet_id]);
            return $stmt->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("Get tweet by id error: " . $e->getMessage());
            return null;
        }
    }
    
    public static function getReportById($report_id) {
        $db = self::getDB();
        try {
            $stmt = $db->prepare("
                SELECT r.*, 
                       u.username as reporter_username, u.name as reporter_name,
                       t.status as tweet_content, 
                       tu.username as tweet_author_username, tu.name as tweet_author_name,
                       a.username as resolved_admin
                FROM reports r
                JOIN users u ON r.user_id = u.id
                JOIN tweets t ON r.tweet_id = t.post_id
                LEFT JOIN users tu ON t.post_id = tu.id
                LEFT JOIN admins a ON r.resolved_by = a.id
                WHERE r.id = ?
            ");
            $stmt->execute([$report_id]);
            return $stmt->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("Get report by id error: " . $e->getMessage());
            return null;
        }
    }
}

// Initialize admin tables
Admin::createAdminTables();
?>