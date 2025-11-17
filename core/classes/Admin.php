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
                // Set session variables
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $admin->id;
                $_SESSION['admin_username'] = $admin->username;
                $_SESSION['admin_role'] = $admin->role;
                
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
            
            return $tweets;
            
        } catch (Exception $e) {
            error_log("getAllTweets error: " . $e->getMessage());
            return [];
        }
    }

    // FIXED DELETE METHOD - SIMPLE AND EFFECTIVE
    public static function deletePost($post_id, $admin_id) {
        $db = self::getDB();
        
        try {
            // Start transaction
            $db->beginTransaction();
            
            error_log("DELETE POST: Attempting to delete post ID: $post_id by admin: $admin_id");
            
            // Method 1: Check if post exists in posts table
            $stmt = $db->prepare("SELECT id FROM posts WHERE id = ?");
            $stmt->execute([$post_id]);
            $post_exists = $stmt->fetch();
            
            if (!$post_exists) {
                error_log("Post ID $post_id does not exist in posts table");
                $db->rollBack();
                return false;
            }
            
            // Method 2: Simple direct delete from posts table (CASCADE will handle related tables)
            $stmt = $db->prepare("DELETE FROM posts WHERE id = ?");
            $result = $stmt->execute([$post_id]);
            $rows_affected = $stmt->rowCount();
            
            error_log("Posts table delete - Rows affected: $rows_affected");
            
            if ($rows_affected > 0) {
                $db->commit();
                
                // Log the action
                self::logAction($admin_id, 'delete_post', "Deleted post #$post_id");
                
                // Send notification to user
                self::sendFlagNotification($post_id, $admin_id, "Your post was removed by admin");
                
                error_log("SUCCESS: Post $post_id deleted successfully");
                return true;
            } else {
                $db->rollBack();
                error_log("FAILED: No posts were deleted for ID: $post_id");
                return false;
            }
            
        } catch (Exception $e) {
            // Rollback on error
            if (isset($db)) {
                $db->rollBack();
            }
            error_log("DELETE ERROR: " . $e->getMessage());
            return false;
        }
    }
    
    // Alternative: Direct post deletion (simplest method)
    public static function directDeletePost($post_id, $admin_id) {
        $db = self::getDB();
        
        try {
            error_log("DIRECT DELETE: Direct deletion for post ID: $post_id");
            
            // Simply delete from posts table
            $stmt = $db->prepare("DELETE FROM posts WHERE id = ?");
            $result = $stmt->execute([$post_id]);
            $rows = $stmt->rowCount();
            
            error_log("Direct delete result: $rows rows affected");
            
            if ($rows > 0) {
                self::logAction($admin_id, 'direct_delete_post', "Direct deleted post #$post_id");
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("DIRECT DELETE ERROR: " . $e->getMessage());
            return false;
        }
    }
    
    // Emergency delete method - tries everything
    public static function emergencyDeletePost($post_id, $admin_id) {
        $db = self::getDB();
        
        try {
            error_log("EMERGENCY DELETE: Emergency deletion for post ID: $post_id");
            
            $tables_to_delete = ['posts', 'tweets', 'likes', 'comments', 'retweets'];
            $success = false;
            
            foreach ($tables_to_delete as $table) {
                try {
                    $stmt = $db->prepare("DELETE FROM $table WHERE " . ($table === 'tweets' ? 'post_id' : 'id') . " = ?");
                    if ($stmt->execute([$post_id])) {
                        $rows = $stmt->rowCount();
                        if ($rows > 0) {
                            $success = true;
                            error_log("Emergency delete SUCCESS from $table table: $rows rows");
                        }
                    }
                } catch (Exception $e) {
                    error_log("Emergency delete from $table failed: " . $e->getMessage());
                }
            }
            
            if ($success) {
                self::logAction($admin_id, 'emergency_delete_post', "Emergency deleted post #$post_id");
            }
            
            return $success;
            
        } catch (Exception $e) {
            error_log("EMERGENCY DELETE ERROR: " . $e->getMessage());
            return false;
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
                SELECT 
                    r.*, 
                    reporter.username as reporter_username,
                    reporter.name as reporter_name,
                    t.status as tweet_content,
                    tweet_author.username as tweet_author_username,
                    tweet_author.name as tweet_author_name,
                    a.username as resolved_admin,
                    p.post_on as tweet_created_at
                FROM reports r
                LEFT JOIN users reporter ON r.user_id = reporter.id
                LEFT JOIN posts p ON r.tweet_id = p.id
                LEFT JOIN tweets t ON r.tweet_id = t.post_id
                LEFT JOIN users tweet_author ON p.user_id = tweet_author.id
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
            
            return $reports;
            
        } catch (PDOException $e) {
            error_log("Get reports error: " . $e->getMessage());
            return [];
        }
    }
    
    public static function getUsernameById($user_id) {
        $db = self::getDB();
        try {
            $stmt = $db->prepare("SELECT username FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $result = $stmt->fetch();
            return $result ? $result->username : false;
        } catch (PDOException $e) {
            error_log("Get username by ID error: " . $e->getMessage());
            return false;
        }
    }
    
    public static function getTweetAuthor($tweet_id) {
        $db = self::getDB();
        try {
            $stmt = $db->prepare("
                SELECT u.username 
                FROM posts p 
                JOIN users u ON p.user_id = u.id 
                WHERE p.id = ?
            ");
            $stmt->execute([$tweet_id]);
            $result = $stmt->fetch();
            return $result ? $result->username : false;
        } catch (PDOException $e) {
            error_log("Get tweet author error: " . $e->getMessage());
            return false;
        }
    }
    
    public static function getTweetContent($tweet_id) {
        $db = self::getDB();
        try {
            $stmt = $db->prepare("
                SELECT t.status as content 
                FROM tweets t 
                WHERE t.post_id = ?
            ");
            $stmt->execute([$tweet_id]);
            $result = $stmt->fetch();
            return $result ? $result->content : false;
        } catch (PDOException $e) {
            error_log("Get tweet content error: " . $e->getMessage());
            return false;
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
            
            // Get all user's posts to delete them properly
            $stmt = $db->prepare("SELECT id as post_id FROM posts WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $posts = $stmt->fetchAll(PDO::FETCH_OBJ);
            
            // Delete all user's posts
            foreach ($posts as $post) {
                self::deletePost($post->post_id, $admin_id);
            }
            
            // Delete from all other tables
            $delete_queries = [
                "DELETE FROM likes WHERE user_id = ?",
                "DELETE FROM retweets WHERE user_id = ?",
                "DELETE FROM comments WHERE user_id = ?",
                "DELETE FROM notifications WHERE notify_for = ?",
                "DELETE FROM follow WHERE sender = ?",
                "DELETE FROM follow WHERE receiver = ?",
                "DELETE FROM users WHERE id = ?"
            ];
            
            foreach ($delete_queries as $query) {
                try {
                    $stmt = $db->prepare($query);
                    $stmt->execute([$user_id]);
                } catch (PDOException $e) {
                    error_log("Delete error for query '$query': " . $e->getMessage());
                }
            }
            
            $db->commit();
            self::logAction($admin_id, 'delete_user', "Deleted user #$user_id ($user->username)");
            return true;
            
        } catch (Exception $e) {
            if (isset($db)) {
                $db->rollBack();
            }
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
                       u.username, u.name, u.img as user_img
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
}

// Initialize admin tables
Admin::createAdminTables();
?>