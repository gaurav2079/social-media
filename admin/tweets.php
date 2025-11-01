<?php
// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../core/init.php';
Admin::checkAdmin();

$page = $_GET['page'] ?? 1;
$limit = 20;
$search = $_GET['search'] ?? '';

// Get tweets
$tweets = Admin::getAllTweets($page, $limit, $search);

// List of inappropriate words for content moderation
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

// Debug: Check if Admin methods exist
error_log("=== Checking Admin Methods ===");
if (method_exists('Admin', 'sendFlagNotification')) {
    error_log("✓ sendFlagNotification method exists");
} else {
    error_log("✗ sendFlagNotification method DOES NOT exist");
}

if (method_exists('Admin', 'flagTweet')) {
    error_log("✓ flagTweet method exists");
} else {
    error_log("✗ flagTweet method DOES NOT exist");
}

if (method_exists('Admin', 'deleteTweet')) {
    error_log("✓ deleteTweet method exists");
} else {
    error_log("✗ deleteTweet method DOES NOT exist");
}

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    error_log("Admin action received: " . $action);
    
    switch ($action) {
        case 'delete_tweet':
            $tweet_id = $_POST['tweet_id'] ?? '';
            $admin_id = $_SESSION['admin_id'];
            error_log("Attempting to delete tweet: " . $tweet_id);
            
            if (Admin::deleteTweet($tweet_id, $admin_id)) {
                $_SESSION['message'] = 'Tweet deleted successfully and user notified';
                error_log("✓ Tweet deleted successfully: " . $tweet_id);
            } else {
                $_SESSION['error'] = 'Failed to delete tweet';
                error_log("✗ Failed to delete tweet: " . $tweet_id);
            }
            break;
            
        case 'analyze_content':
            $tweet_id = $_POST['tweet_id'] ?? '';
            $tweet_content = $_POST['tweet_content'] ?? '';
            error_log("Analyzing content for tweet: " . $tweet_id);
            
            $found_words = checkInappropriateContent($tweet_content, $inappropriate_words);
            
            // Auto-flag if inappropriate content found
            if (!empty($found_words)) {
                $admin_id = $_SESSION['admin_id'];
                $reason = "Your tweet was automatically flagged for containing inappropriate words: " . implode(', ', $found_words);
                error_log("Auto-flagging tweet: " . $tweet_id . " with reason: " . $reason);
                
                if (Admin::sendFlagNotification($tweet_id, $admin_id, $reason)) {
                    $_SESSION['message'] = 'Content analyzed and user notified about flagged content';
                    error_log("✓ Auto-flag notification sent successfully");
                } else {
                    $_SESSION['error'] = 'Failed to send flag notification';
                    error_log("✗ Failed to send auto-flag notification");
                }
            } else {
                $_SESSION['message'] = 'Content analyzed - no inappropriate content found';
                error_log("✓ No inappropriate content found");
            }
            
            $_SESSION['analysis_result'] = [
                'tweet_id' => $tweet_id,
                'found_words' => $found_words,
                'content' => $tweet_content
            ];
            break;
            
        case 'flag_tweet':
            $tweet_id = $_POST['tweet_id'] ?? '';
            $admin_id = $_SESSION['admin_id'];
            $reason = $_POST['reason'] ?? 'Content policy violation';
            error_log("Attempting to flag tweet: " . $tweet_id . " with reason: " . $reason);
            
            if (Admin::flagTweet($tweet_id, $admin_id, $reason)) {
                $_SESSION['message'] = 'Tweet flagged and user notified';
                error_log("✓ Tweet flagged successfully: " . $tweet_id);
            } else {
                $_SESSION['error'] = 'Failed to flag tweet';
                error_log("✗ Failed to flag tweet: " . $tweet_id);
            }
            break;
            
        default:
            error_log("Unknown action: " . $action);
            $_SESSION['error'] = 'Unknown action';
            break;
    }
    
    header("location: tweets.php?page=$page&search=" . urlencode($search));
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Tweets | Kabi Admin</title>
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
        .btn-flag {
            background-color: #ffc107;
            border-color: #ffc107;
            color: #212529;
        }
        .btn-flag:hover {
            background-color: #e0a800;
            border-color: #e0a800;
            color: #212529;
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
                        <i class="fas fa-comment"></i> Post
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
                        <h2>Manage Post</h2>
                        <span class="text-muted">Content Moderation System</span>
                    </div>

                    <?php if (isset($_SESSION['message'])): ?>
                        <div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                    <?php endif; ?>

                    <!-- Content Analysis Result -->
                    <?php if (isset($_SESSION['analysis_result'])): 
                        $result = $_SESSION['analysis_result'];
                        unset($_SESSION['analysis_result']);
                    ?>
                        <div class="alert alert-info">
                            <h5><i class="fas fa-search"></i> Content Analysis Result</h5>
                            <p><strong>Tweet Content:</strong> "<?php echo htmlspecialchars($result['content']); ?>"</p>
                            <?php if (!empty($result['found_words'])): ?>
                                <p class="text-danger">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Found inappropriate words: 
                                    <?php foreach ($result['found_words'] as $word): ?>
                                        <span class="inappropriate-word"><?php echo htmlspecialchars($word); ?></span>
                                    <?php endforeach; ?>
                                </p>
                                <p class="text-warning">
                                    <i class="fas fa-bell"></i>
                                    User has been notified about the flagged content.
                                </p>
                            <?php else: ?>
                                <p class="text-success">
                                    <i class="fas fa-check-circle"></i>
                                    No inappropriate content detected.
                                </p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Search Form -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <form method="GET" class="form-inline">
                                <div class="form-group mr-2">
                                    <input type="text" name="search" class="form-control" placeholder="Search tweets..." value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                                <button type="submit" class="btn btn-primary mr-2">
                                    <i class="fas fa-search"></i> Search
                                </button>
                                <a href="tweets.php" class="btn btn-secondary">Clear</a>
                            </form>
                        </div>
                    </div>

                    <!-- Tweets Table -->
                    <div class="card">
                        <div class="card-body">
                            <?php if (empty($tweets)): ?>
                                <div class="alert alert-warning">
                                    <h5><i class="fas fa-exclamation-triangle"></i> No Tweets Found</h5>
                                    <p>This could be because:</p>
                                    <ul>
                                        <li>There are no post in the database</li>
                                        <li>The database connection is not working properly</li>
                                        <li>The table structure is different than expected</li>
                                    </ul>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>User</th>
                                                <th>Post Content</th>
                                                <th>Stats</th>
                                                <th>Date</th>
                                                <th>Content Check</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($tweets as $tweet): 
                                                $found_words = checkInappropriateContent($tweet->status ?? '', $inappropriate_words);
                                                $tweet_id = $tweet->post_id ?? $tweet->tweetID ?? $tweet->id ?? 'unknown';
                                                $user_img = $tweet->user_img ?? 'default.jpg';
                                                $username = $tweet->username ?? 'Unknown';
                                                $name = $tweet->name ?? 'Unknown User';
                                                $status = $tweet->status ?? 'No content';
                                                $tweet_date = $tweet->tweetDate ?? $tweet->created_at ?? 'now';
                                            ?>
                                                <tr>
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
                                                            // Highlight inappropriate words
                                                            foreach ($found_words as $word) {
                                                                $content = str_ireplace($word, "<span class='inappropriate-word'>$word</span>", $content);
                                                            }
                                                            echo $content;
                                                            ?>
                                                        </div>
                                                        <?php if (!empty($tweet->img) && $tweet->img !== 'null'): ?>
                                                            <div class="mt-2">
                                                                <img src="../assets/images/tweets/<?php echo htmlspecialchars($tweet->img); ?>" 
                                                                     alt="Tweet image" style="max-width: 100px; max-height: 100px; border-radius: 5px;"
                                                                     onerror="this.style.display='none'">
                                                            </div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex flex-column">
                                                            <span class="badge badge-primary mb-1">
                                                                <i class="fas fa-comment"></i> <?php echo $tweet->comment_count ?? 0; ?>
                                                            </span>
                                                            <span class="badge badge-danger mb-1">
                                                                <i class="fas fa-heart"></i> <?php echo $tweet->like_count ?? 0; ?>
                                                            </span>
                                                            <span class="badge badge-success">
                                                                <i class="fas fa-retweet"></i> <?php echo $tweet->retweet_count ?? 0; ?>
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <?php echo date('M j, Y H:i', strtotime($tweet_date)); ?>
                                                    </td>
                                                    <td>
                                                        <?php if (!empty($found_words)): ?>
                                                            <span class="badge badge-warning">
                                                                <i class="fas fa-exclamation-triangle"></i> Flagged
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="badge badge-success">
                                                                <i class="fas fa-check"></i> Clean
                                                            </span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <form method="POST" class="d-inline">
                                                                <input type="hidden" name="tweet_id" value="<?php echo $tweet_id; ?>">
                                                                <input type="hidden" name="tweet_content" value="<?php echo htmlspecialchars($status); ?>">
                                                                <input type="hidden" name="action" value="analyze_content">
                                                                <button type="submit" class="btn btn-sm btn-outline-info">
                                                                    <i class="fas fa-search"></i> Analyze
                                                                </button>
                                                            </form>
                                                            
                                                            <button class="btn btn-sm btn-flag" 
                                                                    data-toggle="modal" 
                                                                    data-target="#flagTweetModal<?php echo $tweet_id; ?>">
                                                                <i class="fas fa-flag"></i> Flag
                                                            </button>
                                                            
                                                            <button class="btn btn-sm btn-outline-danger" 
                                                                    data-toggle="modal" 
                                                                    data-target="#deleteTweetModal<?php echo $tweet_id; ?>">
                                                                <i class="fas fa-trash"></i> Delete
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>

                                                <!-- Delete Tweet Modal -->
                                                <div class="modal fade" id="deleteTweetModal<?php echo $tweet_id; ?>" tabindex="-1">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <form method="POST">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Delete post</h5>
                                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <p>Are you sure you want to delete this post? The user will be notified.</p>
                                                                    <div class="alert alert-light">
                                                                        <strong>Content:</strong><br>
                                                                        <?php echo htmlspecialchars($status); ?>
                                                                    </div>
                                                                    <?php if (!empty($found_words)): ?>
                                                                        <div class="alert alert-warning">
                                                                            <i class="fas fa-exclamation-triangle"></i>
                                                                            This post contains inappropriate words.
                                                                        </div>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <input type="hidden" name="tweet_id" value="<?php echo $tweet_id; ?>">
                                                                    <input type="hidden" name="action" value="delete_tweet">
                                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                                    <button type="submit" class="btn btn-danger">Delete post</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Flag Tweet Modal -->
                                                <div class="modal fade" id="flagTweetModal<?php echo $tweet_id; ?>" tabindex="-1">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <form method="POST">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Flag post</h5>
                                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <p>Flag this post and notify the user?</p>
                                                                    <div class="alert alert-light">
                                                                        <strong>Content:</strong><br>
                                                                        <?php echo htmlspecialchars($status); ?>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label for="reason<?php echo $tweet_id; ?>">Reason for flagging:</label>
                                                                        <select class="form-control" name="reason" id="reason<?php echo $tweet_id; ?>">
                                                                            <option value="Inappropriate content">Inappropriate content</option>
                                                                            <option value="Hate speech">Hate speech</option>
                                                                            <option value="Harassment">Harassment</option>
                                                                            <option value="Spam">Spam</option>
                                                                            <option value="False information">False information</option>
                                                                            <option value="Copyright violation">Copyright violation</option>
                                                                            <option value="Other">Other</option>
                                                                        </select>
                                                                    </div>
                                                                    <?php if (!empty($found_words)): ?>
                                                                        <div class="alert alert-warning">
                                                                            <i class="fas fa-exclamation-triangle"></i>
                                                                            This post contains inappropriate words.
                                                                        </div>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <input type="hidden" name="tweet_id" value="<?php echo $tweet_id; ?>">
                                                                    <input type="hidden" name="action" value="flag_tweet">
                                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                                    <button type="submit" class="btn btn-warning">Flag Tweet</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <nav aria-label="Page navigation" class="mt-4">
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
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/jquery-3.5.1.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function() {
        console.log('Document ready - checking buttons');
        
        // Check if modals are working
        $('[data-toggle="modal"]').on('click', function() {
            console.log('Modal button clicked:', this);
            var target = $(this).data('target');
            console.log('Modal target:', target);
        });
        
        // Check if forms are submitting
        $('form').on('submit', function() {
            console.log('Form submitted:', this);
            console.log('Form action:', $(this).find('input[name="action"]').val());
            console.log('Tweet ID:', $(this).find('input[name="tweet_id"]').val());
        });
        
        // Check if Bootstrap is loaded
        if (typeof $().modal === 'function') {
            console.log('✓ Bootstrap modals are loaded');
        } else {
            console.log('✗ Bootstrap modals are NOT loaded');
        }
    });
    </script>
</body>
</html>