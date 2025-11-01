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
        .report-card {
            border-left: 4px solid #007bff;
        }
        .report-card.pending {
            border-left-color: #ffc107;
        }
        .report-card.resolved {
            border-left-color: #28a745;
        }
        .report-card.dismissed {
            border-left-color: #6c757d;
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
            <div class="col-md-10">
                <div class="p-4">
                    <h2>Manage Reports</h2>
                    
                    <?php if (isset($_SESSION['message'])): ?>
                        <div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                    <?php endif; ?>
                    
                    <!-- Status Tabs -->
                    <ul class="nav nav-tabs mb-4">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $status === 'pending' ? 'active' : ''; ?>" href="reports.php?status=pending">
                                Pending
                                <?php 
                                $pending_count = Admin::getStats()['pending_reports'];
                                if ($pending_count > 0): ?>
                                    <span class="badge badge-danger"><?php echo $pending_count; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $status === 'reviewed' ? 'active' : ''; ?>" href="reports.php?status=reviewed">Reviewed</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $status === 'resolved' ? 'active' : ''; ?>" href="reports.php?status=resolved">Resolved</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $status === 'dismissed' ? 'active' : ''; ?>" href="reports.php?status=dismissed">Dismissed</a>
                        </li>
                    </ul>
                    
                    <!-- Reports List -->
                    <div class="card">
                        <div class="card-body">
                            <?php if (empty($reports)): ?>
                                <p class="text-muted">No reports found with status: <?php echo $status; ?></p>
                            <?php else: ?>
                                <?php foreach ($reports as $report): ?>
                                    <div class="card mb-3 report-card <?php echo $report->status; ?>">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    <h5 class="card-title">
                                                        <span class="badge badge-<?php 
                                                            switch($report->status) {
                                                                case 'pending': echo 'warning'; break;
                                                                case 'resolved': echo 'success'; break;
                                                                case 'dismissed': echo 'secondary'; break;
                                                                default: echo 'primary';
                                                            }
                                                        ?>">
                                                            <?php echo ucfirst($report->status); ?>
                                                        </span>
                                                        <?php echo htmlspecialchars($report->reason); ?>
                                                    </h5>
                                                    
                                                    <p class="card-text"><?php echo htmlspecialchars($report->description); ?></p>
                                                    
                                                    <div class="mb-3">
                                                        <small class="text-muted">
                                                            <i class="fas fa-user"></i>
                                                            Reported by <strong><?php echo htmlspecialchars($report->reporter_username); ?></strong>
                                                            on <?php echo date('M j, Y H:i', strtotime($report->created_at)); ?>
                                                        </small>
                                                    </div>
                                                    
                                                    <!-- Tweet Content -->
                                                    <div class="card bg-light">
                                                        <div class="card-body">
                                                            <div class="d-flex justify-content-between align-items-start">
                                                                <div>
                                                                    <strong><?php echo htmlspecialchars($report->tweet_author_username); ?>:</strong>
                                                                    <p class="mb-0"><?php echo htmlspecialchars($report->tweet_content); ?></p>
                                                                </div>
                                                                <a href="tweets.php?search=<?php echo urlencode($report->tweet_content); ?>" 
                                                                   class="btn btn-sm btn-outline-primary">
                                                                    <i class="fas fa-external-link-alt"></i> View Tweet
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <?php if ($report->admin_notes): ?>
                                                        <div class="mt-2">
                                                            <small class="text-muted">
                                                                <strong>Admin Notes:</strong> <?php echo htmlspecialchars($report->admin_notes); ?>
                                                            </small>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <?php if ($status === 'pending'): ?>
                                                    <div class="btn-group ml-3">
                                                        <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#resolveModal<?php echo $report->id; ?>">
                                                            <i class="fas fa-check"></i> Resolve
                                                        </button>
                                                        <button class="btn btn-sm btn-secondary" data-toggle="modal" data-target="#dismissModal<?php echo $report->id; ?>">
                                                            <i class="fas fa-times"></i> Dismiss
                                                        </button>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Resolve Modal -->
                                    <div class="modal fade" id="resolveModal<?php echo $report->id; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form method="POST">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Resolve Report</h5>
                                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>Are you sure you want to mark this report as resolved?</p>
                                                        <div class="form-group">
                                                            <label>Notes (optional):</label>
                                                            <textarea name="notes" class="form-control" rows="3" placeholder="Add resolution notes..."></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <input type="hidden" name="report_id" value="<?php echo $report->id; ?>">
                                                        <input type="hidden" name="action" value="resolve">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-success">Resolve</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Dismiss Modal -->
                                    <div class="modal fade" id="dismissModal<?php echo $report->id; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form method="POST">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Dismiss Report</h5>
                                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>Are you sure you want to dismiss this report?</p>
                                                        <div class="form-group">
                                                            <label>Notes (optional):</label>
                                                            <textarea name="notes" class="form-control" rows="3" placeholder="Add dismissal notes..."></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <input type="hidden" name="report_id" value="<?php echo $report->id; ?>">
                                                        <input type="hidden" name="action" value="dismiss">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-secondary">Dismiss</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/jquery-3.5.1.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>