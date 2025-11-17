<?php
// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../core/init.php';
Admin::checkAdmin();

// Get statistics
$stats = Admin::getStats();

// Get recent data with error handling
$recent_reports = [];
$recent_tweets = [];
$recent_users = [];

try {
    $recent_reports = Admin::getReports('pending', 1, 5);
} catch (Exception $e) {
    error_log("Reports error: " . $e->getMessage());
}

try {
    $recent_tweets = Admin::getAllTweets(1, 5);
} catch (Exception $e) {
    error_log("Tweets error: " . $e->getMessage());
}

try {
    $recent_users = Admin::getAllUsers(1, 5);
} catch (Exception $e) {
    error_log("Users error: " . $e->getMessage());
}

// Get admin activity logs
$admin_logs = [];
try {
    $db = new PDO(
        "mysql:host=localhost;dbname=tweetphp;charset=utf8mb4",
        "root", 
        "",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
        ]
    );
    $stmt = $db->prepare("
        SELECT al.*, a.username 
        FROM admin_logs al 
        LEFT JOIN admins a ON al.admin_id = a.id 
        ORDER BY al.created_at DESC 
        LIMIT 10
    ");
    $stmt->execute();
    $admin_logs = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Admin logs error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Kabi</title>
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
        .stat-card {
            border-radius: 10px;
            transition: transform 0.2s;
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
        }
        .activity-item {
            border-left: 3px solid #007bff;
            padding-left: 15px;
            margin-bottom: 15px;
        }
        .activity-item.success {
            border-left-color: #28a745;
        }
        .activity-item.warning {
            border-left-color: #ffc107;
        }
        .activity-item.danger {
            border-left-color: #dc3545;
        }
        .recent-item {
            border-bottom: 1px solid #e9ecef;
            padding: 10px 0;
        }
        .recent-item:last-child {
            border-bottom: none;
        }
        .badge-stat {
            font-size: 0.75em;
        }
        .card-header {
            background: linear-gradient(45deg, #f8f9fa, #e9ecef);
            border-bottom: 1px solid #dee2e6;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar p-0">
                <div class="p-3 text-center">
                    <h4 class="mb-0">Kabi Admin</h4>
                    <small class="text-light">Control Panel</small>
                    <hr class="bg-light my-2">
                </div>
                <nav class="nav flex-column">
                    <a class="nav-link active" href="dashboard.php">
                        <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                    </a>
                    <a class="nav-link" href="users.php">
                        <i class="fas fa-users mr-2"></i> Users
                        <span class="badge badge-primary badge-stat float-right"><?php echo $stats['total_users']; ?></span>
                    </a>
                    <a class="nav-link" href="tweets.php">
                        <i class="fas fa-comment mr-2"></i> Post
                        <span class="badge badge-success badge-stat float-right"><?php echo $stats['total_tweets']; ?></span>
                    </a>
                    <a class="nav-link" href="reports.php">
                        <i class="fas fa-flag mr-2"></i> Reports
                        <?php if ($stats['pending_reports'] > 0): ?>
                            <span class="badge badge-danger float-right"><?php echo $stats['pending_reports']; ?></span>
                        <?php else: ?>
                            <span class="badge badge-secondary badge-stat float-right">0</span>
                        <?php endif; ?>
                    </a>
                    <a class="nav-link" href="logout.php">
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </a>
                </nav>
                
            </div>

            <!-- Main Content -->
            <div class="col-md-10">
                <div class="p-4">
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h2 class="mb-1">Dashboard</h2>
                            <p class="text-muted mb-0">Welcome back, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>! Here's what's happening.</p>
                        </div>
                        <div class="text-right">
                            <small class="text-muted">Last Login: <?php echo date('M j, Y g:i A'); ?></small>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card stat-card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Total Users</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_users']; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-users fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card stat-card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Total Post</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_tweets']; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-comment fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card stat-card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                Pending Reports</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['pending_reports']; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-flag fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card stat-card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                Total Likes</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_likes']; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-heart fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity Section -->
                    <div class="row">
                        <!-- Recent Reports -->
                        <div class="col-xl-6 col-lg-6 mb-4">
                            <div class="card shadow h-100">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-flag mr-1"></i>Recent Reports
                                    </h6>
                                    <a href="reports.php" class="btn btn-sm btn-primary">View All</a>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($recent_reports)): ?>
                                        <div class="text-center py-4">
                                            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                            <p class="text-muted mb-0">No pending reports</p>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($recent_reports as $report): ?>
                                            <div class="recent-item">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1 text-danger">
                                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                                            <?php echo htmlspecialchars($report->reason); ?>
                                                        </h6>
                                                        <p class="mb-1 small text-muted">
                                                            <?php echo htmlspecialchars(substr($report->description ?? 'No description', 0, 100)); ?>
                                                            <?php if (strlen($report->description ?? '') > 100): ?>...<?php endif; ?>
                                                        </p>
                                                        <small class="text-muted">
                                                            By <?php echo htmlspecialchars($report->reporter_username ?? 'Unknown'); ?> • 
                                                            <?php echo date('M j, g:i A', strtotime($report->created_at)); ?>
                                                        </small>
                                                    </div>
                                                    <span class="badge badge-warning ml-2">Pending</span>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Tweets -->
                        <div class="col-xl-6 col-lg-6 mb-4">
                            <div class="card shadow h-100">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-success">
                                        <i class="fas fa-comment mr-1"></i>Recent Post
                                    </h6>
                                    <a href="tweets.php" class="btn btn-sm btn-success">View All</a>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($recent_tweets)): ?>
                                        <div class="text-center py-4">
                                            <i class="fas fa-comment-slash fa-2x text-muted mb-2"></i>
                                            <p class="text-muted mb-0">No post found</p>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($recent_tweets as $tweet): ?>
                                            <div class="recent-item">
                                                <div class="d-flex align-items-start">
                                                    <img src="../assets/images/users/<?php echo htmlspecialchars($tweet->user_img ?? 'default.jpg'); ?>" 
                                                         alt="User" class="user-avatar mr-3"
                                                         onerror="this.src='../assets/images/users/default.jpg'">
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1">
                                                            <?php echo htmlspecialchars($tweet->name ?? 'Unknown User'); ?>
                                                            <small class="text-muted">@<?php echo htmlspecialchars($tweet->username ?? 'unknown'); ?></small>
                                                        </h6>
                                                        <p class="mb-1 small">
                                                            <?php echo htmlspecialchars(substr($tweet->status ?? 'No content', 0, 120)); ?>
                                                            <?php if (strlen($tweet->status ?? '') > 120): ?>...<?php endif; ?>
                                                        </p>
                                                        <div class="d-flex align-items-center">
                                                            <small class="text-muted mr-3">
                                                                <i class="fas fa-clock mr-1"></i>
                                                                <?php echo date('M j, g:i A', strtotime($tweet->tweetDate ?? $tweet->created_at ?? 'now')); ?>
                                                            </small>
                                                            <small class="text-primary mr-2">
                                                                <i class="fas fa-heart mr-1"></i><?php echo $tweet->like_count ?? 0; ?>
                                                            </small>
                                                            <small class="text-success">
                                                                <i class="fas fa-retweet mr-1"></i><?php echo $tweet->retweet_count ?? 0; ?>
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Second Row -->
                    <div class="row">
                        <!-- Recent Users -->
                        <div class="col-xl-6 col-lg-6 mb-4">
                            <div class="card shadow h-100">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-info">
                                        <i class="fas fa-users mr-1"></i>Recent Users
                                    </h6>
                                    <a href="users.php" class="btn btn-sm btn-info">View All</a>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($recent_users)): ?>
                                        <div class="text-center py-4">
                                            <i class="fas fa-user-slash fa-2x text-muted mb-2"></i>
                                            <p class="text-muted mb-0">No users found</p>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($recent_users as $user): ?>
                                            <div class="recent-item">
                                                <div class="d-flex align-items-center">
                                                    <img src="../assets/images/users/<?php echo htmlspecialchars($user->img); ?>" 
                                                         alt="User" class="user-avatar mr-3"
                                                         onerror="this.src='../assets/images/users/default.jpg'">
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1"><?php echo htmlspecialchars($user->name); ?></h6>
                                                        <p class="mb-1 small text-muted">
                                                            @<?php echo htmlspecialchars($user->username); ?>
                                                        </p>
                                                        <div class="d-flex align-items-center">
                                                            <small class="text-primary mr-3">
                                                                <i class="fas fa-comment mr-1"></i><?php echo $user->tweet_count ?? 0; ?> tweets
                                                            </small>
                                                            <small class="text-success mr-3">
                                                                <i class="fas fa-user-plus mr-1"></i><?php echo $user->following_count ?? 0; ?> following
                                                            </small>
                                                            <small class="text-info">
                                                                <i class="fas fa-users mr-1"></i><?php echo $user->followers_count ?? 0; ?> followers
                                                            </small>
                                                        </div>
                                                    </div>
                                                    <small class="text-muted">
                                                        <?php echo date('M j', strtotime($user->registered_at ?? $user->created_at ?? 'now')); ?>
                                                    </small>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Admin Activity Logs -->
                        <div class="col-xl-6 col-lg-6 mb-4">
                            <div class="card shadow h-100">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-warning">
                                        <i class="fas fa-history mr-1"></i>Admin Activity
                                    </h6>
                                    <span class="badge badge-warning">Recent</span>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($admin_logs)): ?>
                                        <div class="text-center py-4">
                                            <i class="fas fa-history fa-2x text-muted mb-2"></i>
                                            <p class="text-muted mb-0">No recent activity</p>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($admin_logs as $log): ?>
                                            <div class="activity-item 
                                                <?php 
                                                if (strpos($log->action, 'delete') !== false) echo 'danger';
                                                elseif (strpos($log->action, 'login') !== false) echo 'success';
                                                else echo 'warning';
                                                ?>
                                            ">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1">
                                                            <i class="fas 
                                                                <?php 
                                                                if (strpos($log->action, 'delete') !== false) echo 'fa-trash text-danger';
                                                                elseif (strpos($log->action, 'login') !== false) echo 'fa-sign-in-alt text-success';
                                                                else echo 'fa-cog text-warning';
                                                                ?>
                                                                mr-1">
                                                            </i>
                                                            <?php echo htmlspecialchars(ucfirst($log->action)); ?>
                                                        </h6>
                                                        <p class="mb-1 small text-muted">
                                                            <?php echo htmlspecialchars($log->description ?? 'No description'); ?>
                                                        </p>
                                                        <small class="text-muted">
                                                            By <?php echo htmlspecialchars($log->username ?? 'System'); ?>
                                                        </small>
                                                    </div>
                                                    <small class="text-muted ml-2">
                                                        <?php echo date('g:i A', strtotime($log->created_at)); ?>
                                                    </small>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card shadow">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-dark">
                                        <i class="fas fa-bolt mr-1"></i>Quick Actions
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-md-3 mb-3">
                                            <a href="users.php" class="btn btn-outline-primary btn-block py-3">
                                                <i class="fas fa-users fa-2x mb-2"></i><br>
                                                Manage Users
                                            </a>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <a href="tweets.php" class="btn btn-outline-success btn-block py-3">
                                                <i class="fas fa-comment fa-2x mb-2"></i><br>
                                                Manage Tweets
                                            </a>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <a href="reports.php" class="btn btn-outline-warning btn-block py-3">
                                                <i class="fas fa-flag fa-2x mb-2"></i><br>
                                                Review Reports
                                            </a>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <a href="logout.php" class="btn btn-outline-danger btn-block py-3">
                                                <i class="fas fa-sign-out-alt fa-2x mb-2"></i><br>
                                                Logout
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/jquery-3.5.1.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto refresh stats every 30 seconds
        setInterval(function() {
            $.get('dashboard.php', function(data) {
                // You can implement partial page refresh here if needed
                console.log('Dashboard stats refreshed');
            });
        }, 30000);

        // Add some interactive effects
        $(document).ready(function() {
            // Add hover effects to cards
            $('.stat-card').hover(
                function() {
                    $(this).addClass('shadow-lg');
                },
                function() {
                    $(this).removeClass('shadow-lg');
                }
            );

            // Add click animation to quick action buttons
            $('.btn-block').click(function() {
                $(this).addClass('btn-active');
                setTimeout(() => {
                    $(this).removeClass('btn-active');
                }, 300);
            });
        });
    </script>
</body>
</html>