<?php
// Start session at the VERY TOP
session_start();

// Include the admin class
require_once '../core/init.php';

// Check if admin is logged in
Admin::checkAdmin();

// Get parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Get admin ID from session
$admin_id = isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : 1;

// Get tweets
$tweets = Admin::getAllTweets($page, $limit, $search);

// List of inappropriate words
$inappropriate_words = [
    'fuck', 'shit', 'asshole', 'bitch', 'bastard', 'dick', 'pussy', 'cunt',
    'nigger', 'fag', 'retard', 'whore', 'slut', 'damn', 'hell', 'crap',
    'kill', 'murder', 'violence', 'hate', 'terrorist', 'bomb', 'drugs'
];

// Function to check for inappropriate content
function checkInappropriateContent($text, $bad_words) {
    $text_lower = strtolower($text);
    $found_words = [];
    
    foreach ($bad_words as $word) {
        if (strpos($text_lower, $word) !== false) {
            $found_words[] = $word;
        }
    }
    
    return $found_words;
}

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $tweet_id = isset($_POST['tweet_id']) ? (int)$_POST['tweet_id'] : 0;
    
    error_log("POST ACTION: $action for tweet ID: $tweet_id by admin: $admin_id");
    
    switch ($action) {
        case 'delete_tweet':
            if (empty($tweet_id)) {
                $_SESSION['error'] = 'No post ID provided';
                break;
            }
            
            // DIRECT DATABASE DELETE - SIMPLE AND RELIABLE
            try {
                $db = new PDO("mysql:host=localhost;dbname=tweetphp;charset=utf8mb4", "root", "");
                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                error_log("Starting direct delete for post ID: $tweet_id");
                
                // Start transaction
                $db->beginTransaction();
                
                // Step 1: Delete from tweets table
                $stmt = $db->prepare("DELETE FROM tweets WHERE post_id = ?");
                $stmt->execute([$tweet_id]);
                $tweet_rows = $stmt->rowCount();
                
                // Step 2: Delete from posts table (MAIN TABLE)
                $stmt = $db->prepare("DELETE FROM posts WHERE id = ?");
                $stmt->execute([$tweet_id]);
                $post_rows = $stmt->rowCount();
                
                // Step 3: Clean up related tables
                $cleanup_tables = ['likes', 'comments', 'retweets', 'notifications'];
                foreach ($cleanup_tables as $table) {
                    try {
                        $stmt = $db->prepare("DELETE FROM $table WHERE post_id = ?");
                        $stmt->execute([$tweet_id]);
                    } catch (Exception $e) {
                        // Ignore errors in cleanup
                    }
                }
                
                if ($post_rows > 0) {
                    $db->commit();
                    $_SESSION['message'] = "✅ Post #$tweet_id deleted successfully!";
                    error_log("SUCCESS: Post $tweet_id deleted");
                    
                    // Log the action
                    Admin::logAction($admin_id, 'direct_delete', "Deleted post #$tweet_id");
                } else {
                    $db->rollBack();
                    $_SESSION['error'] = "❌ Failed to delete post #$tweet_id. Post not found.";
                    error_log("FAILED: Post $tweet_id not found in posts table");
                }
                
            } catch (Exception $e) {
                error_log("DATABASE ERROR: " . $e->getMessage());
                $_SESSION['error'] = "Database error: " . $e->getMessage();
            }
            break;
            
        case 'analyze_content':
            $tweet_content = isset($_POST['tweet_content']) ? $_POST['tweet_content'] : '';
            $found_words = checkInappropriateContent($tweet_content, $inappropriate_words);
            
            $_SESSION['analysis_result'] = [
                'tweet_id' => $tweet_id,
                'found_words' => $found_words,
                'content' => $tweet_content
            ];
            
            if (!empty($found_words)) {
                $_SESSION['message'] = 'Content analyzed - inappropriate content found';
            } else {
                $_SESSION['message'] = 'Content analyzed - no inappropriate content found';
            }
            break;
            
        case 'flag_tweet':
            $reason = isset($_POST['reason']) ? $_POST['reason'] : 'Content policy violation';
            
            if (method_exists('Admin', 'flagTweet')) {
                if (Admin::flagTweet($tweet_id, $admin_id, $reason)) {
                    $_SESSION['message'] = 'Post flagged successfully';
                } else {
                    $_SESSION['error'] = 'Failed to flag post';
                }
            }
            break;
            
        default:
            $_SESSION['error'] = 'Unknown action';
            break;
    }
    
    // Redirect back to prevent form resubmission
    header("Location: tweets.php?page=$page&search=" . urlencode($search));
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Posts | Kabi Admin</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/all.min.css">
    <style>
        .sidebar {
            background: #343a40;
            min-height: 100vh;
            color: white;
        }
        .sidebar .nav-link {
            color: #ccc;
            padding: 10px 15px;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: white;
            background: #495057;
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        .tweet-content {
            max-height: 200px;
            overflow-y: auto;
        }
        .badge-warning {
            background-color: #ffc107;
            color: #212529;
        }
        .inappropriate-word {
            background-color: #ffeb3b;
            padding: 2px 4px;
            border-radius: 3px;
            font-weight: bold;
        }
        .action-buttons .btn {
            margin: 2px;
            font-size: 12px;
        }
        .delete-btn {
            background-color: #dc3545;
            border-color: #dc3545;
            color: white;
        }
        .delete-btn:hover {
            background-color: #c82333;
            border-color: #bd2130;
            color: white;
        }
        .debug-info {
            background: #f8f9fa;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 11px;
            color: #6c757d;
        }
        .status-badge {
            font-size: 11px;
            padding: 4px 8px;
        }
        /* Fix for modal issues */
        .modal-backdrop {
            z-index: 1040;
        }
        .modal {
            z-index: 1050;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar p-0">
                <div class="p-3">
                    <h4 class="text-center">Kabi Admin</h4>
                    <hr class="bg-light">
                </div>
                <nav class="nav flex-column">
                    <a class="nav-link" href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <a class="nav-link" href="users.php">
                        <i class="fas fa-users"></i> Users
                    </a>
                    <a class="nav-link active" href="tweets.php">
                        <i class="fas fa-comment"></i> Posts
                    </a>
                    <a class="nav-link" href="reports.php">
                        <i class="fas fa-flag"></i> Reports
                    </a>
                    <a class="nav-link" href="logout.php">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-10">
                <div class="p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2>Manage Posts <span class="badge badge-success">if unwanted post</span></h2>
                        <div class="debug-info">
                           
                            Posts: <?php echo count($tweets); ?>
                        </div>
                    </div>

                    <!-- Messages -->
                    <?php if (isset($_SESSION['message'])): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle"></i> <?php echo $_SESSION['message']; ?>
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                        <?php unset($_SESSION['message']); ?>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle"></i> <?php echo $_SESSION['error']; ?>
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>

                    <!-- Search Form -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <form method="GET" class="form-inline">
                                <div class="form-group mr-2">
                                    <input type="text" name="search" class="form-control" placeholder="Search posts..." value="<?php echo htmlspecialchars($search); ?>" style="width: 300px;">
                                </div>
                                <button type="submit" class="btn btn-primary mr-2">
                                    <i class="fas fa-search"></i> Search
                                </button>
                                <a href="tweets.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </form>
                        </div>
                    </div>

                    <!-- Posts Table -->
                    <div class="card">
                        <div class="card-body">
                            <?php if (empty($tweets)): ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i> No posts found.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th>ID</th>
                                                <th>User</th>
                                                <th>Post Content</th>
                                                <th>Date</th>
                                                <th>Content Check</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($tweets as $tweet): 
                                                $found_words = checkInappropriateContent($tweet->status ?? '', $inappropriate_words);
                                                $tweet_id = $tweet->post_id;
                                                $user_img = $tweet->user_img ?? 'default.jpg';
                                                $username = $tweet->username ?? 'Unknown';
                                                $name = $tweet->name ?? 'Unknown User';
                                                $status = $tweet->status ?? 'No content';
                                                $tweet_date = $tweet->tweetDate ?? $tweet->post_on ?? 'now';
                                            ?>
                                                <tr>
                                                    <td>
                                                        <strong><?php echo $tweet_id; ?></strong>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <img src="../assets/images/users/<?php echo htmlspecialchars($user_img); ?>" 
                                                                 alt="Avatar" class="user-avatar mr-2"
                                                                 onerror="this.src='../assets/images/users/default.jpg'">
                                                            <div>
                                                                <div><strong><?php echo htmlspecialchars($name); ?></strong></div>
                                                                <small class="text-muted">@<?php echo htmlspecialchars($username); ?></small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="tweet-content">
                                                            <?php 
                                                            $content = htmlspecialchars($status);
                                                            foreach ($found_words as $word) {
                                                                $content = str_ireplace($word, "<span class='inappropriate-word'>$word</span>", $content);
                                                            }
                                                            echo $content;
                                                            ?>
                                                        </div>
                                                        <?php if (!empty($tweet->img) && $tweet->img !== 'null'): ?>
                                                            <div class="mt-2">
                                                                <img src="../assets/images/tweets/<?php echo htmlspecialchars($tweet->img); ?>" 
                                                                     alt="Post image" style="max-width: 100px; max-height: 100px; border-radius: 5px;"
                                                                     onerror="this.style.display='none'">
                                                            </div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <small><?php echo date('M j, Y H:i', strtotime($tweet_date)); ?></small>
                                                    </td>
                                                    <td>
                                                        <?php if (!empty($found_words)): ?>
                                                            <span class="badge badge-warning status-badge">
                                                                <i class="fas fa-exclamation-triangle"></i> Flagged
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="badge badge-success status-badge">
                                                                <i class="fas fa-check"></i> Clean
                                                            </span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="action-buttons">
                                                            <!-- SIMPLE DELETE BUTTON - NO MODAL -->
                                                            <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete post #<?php echo $tweet_id; ?>? This action cannot be undone.')">
                                                                <input type="hidden" name="tweet_id" value="<?php echo $tweet_id; ?>">
                                                                <input type="hidden" name="action" value="delete_tweet">
                                                                <button type="submit" class="btn btn-sm delete-btn">
                                                                    <i class="fas fa-trash"></i> Delete
                                                                </button>
                                                            </form>
                                                            
                                                            <!-- Analyze Button -->
                                                            <form method="POST" class="d-inline">
                                                                <input type="hidden" name="tweet_id" value="<?php echo $tweet_id; ?>">
                                                                <input type="hidden" name="tweet_content" value="<?php echo htmlspecialchars($status); ?>">
                                                                <input type="hidden" name="action" value="analyze_content">
                                                                <button type="submit" class="btn btn-sm btn-outline-info">
                                                                    <i class="fas fa-search"></i> Analyze
                                                                </button>
                                                            </form>
                                                            
                                                           
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination -->
                                <nav class="mt-3">
                                    <ul class="pagination justify-content-center">
                                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>">Previous</a>
                                        </li>
                                        <li class="page-item active">
                                            <span class="page-link">Page <?php echo $page; ?></span>
                                        </li>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>">Next</a>
                                        </li>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SIMPLE JAVASCRIPT - NO COMPLEX DEPENDENCIES -->
    <script src="../assets/js/jquery-3.5.1.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            console.log('Admin Posts Page Loaded - SIMPLE DELETE BUTTONS');
            
            // Auto-dismiss alerts after 5 seconds
            setTimeout(function() {
                $('.alert').alert('close');
            }, 5000);

            // Test if buttons are clickable
            $('.delete-btn').on('click', function() {
                console.log('Delete button clicked!');
                return true;
            });
        });
    </script>
</body>
</html>