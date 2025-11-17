<?php
// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../core/init.php';
Admin::checkAdmin();

$status = $_GET['status'] ?? 'pending';
$page = $_GET['page'] ?? 1;
$limit = 20;

$reports = Admin::getReports($status, $page, $limit);

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $report_id = $_POST['report_id'];
    $admin_id = $_SESSION['admin_id'];
    
    switch ($action) {
        case 'resolve':
            $notes = $_POST['notes'] ?? '';
            if (Admin::updateReportStatus($report_id, 'resolved', $admin_id, $notes)) {
                $_SESSION['message'] = 'Report resolved successfully';
            } else {
                $_SESSION['error'] = 'Failed to resolve report';
            }
            break;
            
        case 'dismiss':
            $notes = $_POST['notes'] ?? '';
            if (Admin::updateReportStatus($report_id, 'dismissed', $admin_id, $notes)) {
                $_SESSION['message'] = 'Report dismissed successfully';
            } else {
                $_SESSION['error'] = 'Failed to dismiss report';
            }
            break;
    }
    
    header("location: reports.php?status=$status&page=$page");
    exit();
}

// Get stats for badge counts
$stats = Admin::getStats();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Reports | Kabi Admin</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --light-bg: #f8f9fa;
        }
        
        body {
            background-color: #f5f6fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .sidebar {
            background: linear-gradient(135deg, var(--secondary-color) 0%, #34495e 100%);
            min-height: 100vh;
            color: white;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar .nav-link {
            color: #bdc3c7;
            padding: 12px 20px;
            margin: 2px 0;
            border-radius: 5px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .sidebar .nav-link:hover {
            color: white;
            background: rgba(255,255,255,0.1);
            transform: translateX(5px);
        }
        
        .sidebar .nav-link.active {
            color: white;
            background: var(--primary-color);
            box-shadow: 0 2px 10px rgba(52, 152, 219, 0.3);
        }
        
        .sidebar .nav-link i {
            width: 20px;
            margin-right: 10px;
        }
        
        .main-content {
            background-color: #f5f6fa;
            min-height: 100vh;
        }
        
        .page-header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        
        .report-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            border: none;
            margin-bottom: 20px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border-left: 4px solid var(--primary-color);
        }
        
        .report-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.12);
        }
        
        .report-card.pending {
            border-left-color: var(--warning-color);
        }
        
        .report-card.resolved {
            border-left-color: var(--success-color);
        }
        
        .report-card.dismissed {
            border-left-color: #95a5a6;
        }
        
        .report-card.reviewed {
            border-left-color: var(--primary-color);
        }
        
        .status-badge {
            font-size: 0.75rem;
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: 600;
        }
        
        .tweet-preview {
            background: var(--light-bg);
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
            border-left: 3px solid #ddd;
        }
        
        .nav-tabs .nav-link {
            border: none;
            color: #7f8c8d;
            font-weight: 500;
            padding: 12px 25px;
            border-radius: 8px 8px 0 0;
        }
        
        .nav-tabs .nav-link.active {
            background: white;
            color: var(--primary-color);
            border-bottom: 3px solid var(--primary-color);
        }
        
        .nav-tabs .nav-link:hover {
            border: none;
            color: var(--primary-color);
        }
        
        .action-buttons {
            min-width: 120px;
        }
        
        .action-buttons .btn {
            border-radius: 6px;
            font-weight: 500;
            padding: 8px 15px;
            margin-bottom: 5px;
            width: 100%;
            min-width: 100px;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #bdc3c7;
        }
        
        .admin-notes {
            background: #fff9e6;
            border-left: 3px solid #f39c12;
            padding: 10px 15px;
            border-radius: 5px;
            margin-top: 10px;
        }
        
        .report-meta {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .username {
            color: var(--primary-color);
            font-weight: 600;
        }
        
        .unknown-user {
            color: var(--danger-color);
            font-style: italic;
        }
        
        /* Custom modal styling to ensure it works */
        .modal-custom {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }
        
        .modal-custom.show {
            display: flex !important;
        }
        
        .modal-content-custom {
            background: white;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
            margin: auto;
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
                    <small class="text-muted">Administration Panel</small>
                    <hr class="bg-light my-3">
                </div>
                <nav class="nav flex-column p-3">
                    <a class="nav-link" href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <a class="nav-link" href="users.php">
                        <i class="fas fa-users"></i> Users
                    </a>
                    <a class="nav-link" href="tweets.php">
                        <i class="fas fa-comment"></i> Post
                    </a>
                    <a class="nav-link active" href="reports.php">
                        <i class="fas fa-flag"></i> Reports
                    </a>
                    <a class="nav-link" href="logout.php">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 main-content">
                <div class="p-4">
                    <!-- Page Header -->
                    <div class="page-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="mb-1">Manage Reports</h2>
                                <p class="text-muted mb-0">Review and manage user-reported content</p>
                            </div>
                            
                        </div>
                    </div>
                    
                    <!-- Messages -->
                    <?php if (isset($_SESSION['message'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> <?php echo $_SESSION['message']; ?>
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                            <?php unset($_SESSION['message']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; ?>
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                            <?php unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Status Tabs -->
                    <div class="card mb-4 border-0 shadow-sm">
                        <div class="card-body p-0">
                            <ul class="nav nav-tabs" id="statusTabs">
                                <li class="nav-item">
                                    <a class="nav-link <?php echo $status === 'pending' ? 'active' : ''; ?>" 
                                       href="reports.php?status=pending">
                                        Pending
                                        <?php if (($stats['pending_reports'] ?? 0) > 0): ?>
                                            <span class="badge badge-danger ml-1"><?php echo $stats['pending_reports']; ?></span>
                                        <?php endif; ?>
                                    </a>
                                </li>
                               
                                <li class="nav-item">
                                    <a class="nav-link <?php echo $status === 'resolved' ? 'active' : ''; ?>" 
                                       href="reports.php?status=resolved">
                                        Resolved
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo $status === 'dismissed' ? 'active' : ''; ?>" 
                                       href="reports.php?status=dismissed">
                                        Dismissed
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Reports List -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <?php if (empty($reports)): ?>
                                <div class="empty-state">
                                    <i class="fas fa-flag"></i>
                                    <h4>No Reports Found</h4>
                                    <p>No reports found with status: <strong><?php echo ucfirst($status); ?></strong></p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($reports as $report): ?>
                                    <div class="report-card <?php echo $report->status; ?> mb-4">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    <!-- Report Header -->
                                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                                        <div>
                                                            <h5 class="card-title mb-2">
                                                                <span class="status-badge badge-<?php 
                                                                    switch($report->status) {
                                                                        case 'pending': echo 'warning'; break;
                                                                        case 'resolved': echo 'success'; break;
                                                                        case 'dismissed': echo 'secondary'; break;
                                                                        case 'reviewed': echo 'primary'; break;
                                                                        default: echo 'primary';
                                                                    }
                                                                ?>">
                                                                    <?php echo ucfirst($report->status); ?>
                                                                </span>
                                                                <span class="ml-2 font-weight-bold text-dark text-capitalize">
                                                                    <?php 
                                                                    // Format the reason for better display
                                                                    $reason = htmlspecialchars($report->reason);
                                                                    $reason = str_replace('_', ' ', $reason);
                                                                    echo $reason; 
                                                                    ?>
                                                                </span>
                                                            </h5>
                                                            
                                                            <!-- Report Meta -->
                                                            <div class="report-meta mb-3">
                                                                <small class="text-muted">
                                                                    <i class="fas fa-user text-info mr-1"></i>
                                                                    Reported by 
                                                                    <strong class="<?php echo empty($report->reporter_username) ? 'unknown-user' : 'username'; ?>">
                                                                        <?php 
                                                                        if (!empty($report->reporter_username) && $report->reporter_username !== 'Unknown') {
                                                                            echo htmlspecialchars($report->reporter_username);
                                                                        } else {
                                                                            // Try to get username from user_id
                                                                            $reporter_name = Admin::getUsernameById($report->user_id);
                                                                            if ($reporter_name) {
                                                                                echo htmlspecialchars($reporter_name);
                                                                            } else {
                                                                                echo 'Unknown User';
                                                                            }
                                                                        }
                                                                        ?>
                                                                    </strong>
                                                                </small>
                                                                <small class="text-muted">
                                                                    <i class="fas fa-clock mr-1"></i>
                                                                    <?php echo date('M j, Y H:i', strtotime($report->created_at)); ?>
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Report Description -->
                                                    <?php if (!empty($report->description)): ?>
                                                        <div class="mb-3">
                                                            <p class="card-text text-dark mb-2">
                                                                <strong>Report Description:</strong>
                                                            </p>
                                                            <div class="alert alert-light border">
                                                                <i class="fas fa-comment-dots text-primary mr-2"></i>
                                                                <?php echo htmlspecialchars($report->description); ?>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                    
                                                    <!-- Tweet Content -->
                                                    <div class="tweet-preview">
                                                        <div class="d-flex justify-content-between align-items-start">
                                                            <div class="flex-grow-1">
                                                                <div class="d-flex align-items-center mb-2">
                                                                    <strong class="<?php echo (empty($report->tweet_author_username) || $report->tweet_author_username === 'Unknown') ? 'unknown-user' : 'username'; ?>">
                                                                        @<?php 
                                                                        if (!empty($report->tweet_author_username) && $report->tweet_author_username !== 'Unknown') {
                                                                            echo htmlspecialchars($report->tweet_author_username);
                                                                        } else {
                                                                            // Try to get tweet author username
                                                                            $tweet_author = Admin::getTweetAuthor($report->tweet_id);
                                                                            if ($tweet_author) {
                                                                                echo htmlspecialchars($tweet_author);
                                                                            } else {
                                                                                echo 'unknown';
                                                                            }
                                                                        }
                                                                        ?>
                                                                    </strong>
                                                                    <small class="text-muted ml-2">
                                                                        <?php 
                                                                        $tweetTime = strtotime($report->tweet_created_at ?? $report->created_at);
                                                                        echo date('M j, Y H:i', $tweetTime);
                                                                        ?>
                                                                    </small>
                                                                </div>
                                                                <p class="mb-0 text-dark">
                                                                    <?php 
                                                                    if (!empty($report->tweet_content) && $report->tweet_content !== 'Tweet content not available') {
                                                                        echo htmlspecialchars($report->tweet_content);
                                                                    } else {
                                                                        // Try to get tweet content
                                                                        $tweet_content = Admin::getTweetContent($report->tweet_id);
                                                                        if ($tweet_content) {
                                                                            echo htmlspecialchars($tweet_content);
                                                                        } else {
                                                                            echo 'Tweet content not available';
                                                                        }
                                                                    }
                                                                    ?>
                                                                </p>
                                                            </div>
                                                            <?php if (!empty($report->tweet_id)): ?>
                                                                <a href="tweets.php?tweet_id=<?php echo $report->tweet_id; ?>" 
                                                                   class="btn btn-sm btn-outline-primary ml-3">
                                                                    <i class="fas fa-external-link-alt"></i> View Tweet
                                                                </a>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Admin Notes -->
                                                    <?php if (!empty($report->admin_notes)): ?>
                                                        <div class="admin-notes mt-3">
                                                            <small>
                                                                <strong><i class="fas fa-sticky-note text-warning mr-1"></i>Admin Notes:</strong> 
                                                                <?php echo htmlspecialchars($report->admin_notes); ?>
                                                            </small>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <!-- Action Buttons - SIMPLE DIRECT FORM APPROACH -->
                                                <?php if ($status === 'pending' || $status === 'reviewed'): ?>
                                                    <div class="action-buttons ml-4">
                                                        <!-- Direct resolve form -->
                                                        <form method="POST" action="" style="display: inline; margin-bottom: 5px;">
                                                            <input type="hidden" name="report_id" value="<?php echo $report->id; ?>">
                                                            <input type="hidden" name="action" value="resolve">
                                                            <button type="submit" class="btn btn-success w-100" onclick="return confirm('Are you sure you want to resolve this report?')">
                                                                <i class="fas fa-check mr-1"></i> Resolve
                                                            </button>
                                                        </form>
                                                        
                                                        <!-- Direct dismiss form -->
                                                        <form method="POST" action="" style="display: inline;">
                                                            <input type="hidden" name="report_id" value="<?php echo $report->id; ?>">
                                                            <input type="hidden" name="action" value="dismiss">
                                                            <button type="submit" class="btn btn-secondary w-100" onclick="return confirm('Are you sure you want to dismiss this report?')">
                                                                <i class="fas fa-times mr-1"></i> Dismiss
                                                            </button>
                                                        </form>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if (!empty($reports)): ?>
                        <nav aria-label="Reports pagination" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="reports.php?status=<?php echo $status; ?>&page=<?php echo $page - 1; ?>">
                                            <i class="fas fa-chevron-left mr-1"></i> Previous
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <li class="page-item active">
                                    <span class="page-link">Page <?php echo $page; ?></span>
                                </li>
                                
                                <li class="page-item">
                                    <a class="page-link" href="reports.php?status=<?php echo $status; ?>&page=<?php echo $page + 1; ?>">
                                        Next <i class="fas fa-chevron-right ml-1"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Simple JavaScript for any additional functionality -->
    <script>
        // Simple loading state for buttons
        document.addEventListener('DOMContentLoaded', function() {
            // Add loading state to all form buttons
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    const buttons = this.querySelectorAll('button[type="submit"]');
                    buttons.forEach(button => {
                        button.disabled = true;
                        const originalText = button.innerHTML;
                        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Processing...';
                        
                        // Revert after 5 seconds if still on page (fallback)
                        setTimeout(() => {
                            if (button.disabled) {
                                button.disabled = false;
                                button.innerHTML = originalText;
                            }
                        }, 5000);
                    });
                });
            });
            
            console.log('Reports page loaded successfully');
        });
    </script>

    <!-- Load JavaScript libraries at the end -->
    <script src="../assets/js/jquery-3.5.1.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>