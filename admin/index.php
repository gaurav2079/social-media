<?php
session_start();
require_once '../core/init.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$admin_id = $_SESSION['admin_id'];
$current_page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Get dashboard stats
$stats = Admin::getDashboardStats();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel | Kabi</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/all.min.css">
    <style>
        .sidebar {
            background: #343a40;
            color: white;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            width: 250px;
            padding-top: 20px;
        }
        .sidebar .nav-link {
            color: #adb5bd;
            padding: 10px 20px;
            margin: 5px 0;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: white;
            background: #495057;
        }
        .sidebar .nav-link i {
            width: 20px;
            margin-right: 10px;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .stat-card {
            border-radius: 10px;
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-card .card-body {
            padding: 20px;
        }
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
        }
        .table-actions .btn {
            margin: 2px;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="text-center mb-4">
            <h4><i class="fas fa-cog"></i> Admin Panel</h4>
            <small>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></small>
        </div>
        
        <nav class="nav flex-column">
            <a class="nav-link <?php echo $current_page === 'dashboard' ? 'active' : ''; ?>" href="?page=dashboard">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a class="nav-link <?php echo $current_page === 'users' ? 'active' : ''; ?>" href="?page=users">
                <i class="fas fa-users"></i> Users
            </a>
            <a class="nav-link <?php echo $current_page === 'tweets' ? 'active' : ''; ?>" href="?page=tweets">
                <i class="fas fa-comment"></i> Tweets
            </a>
            <a class="nav-link <?php echo $current_page === 'reports' ? 'active' : ''; ?>" href="?page=reports">
                <i class="fas fa-flag"></i> Reports
                <?php if ($stats['pending_reports'] > 0): ?>
                    <span class="badge badge-danger float-right"><?php echo $stats['pending_reports']; ?></span>
                <?php endif; ?>
            </a>
            <a class="nav-link <?php echo $current_page === 'logs' ? 'active' : ''; ?>" href="?page=logs">
                <i class="fas fa-history"></i> Activity Logs
            </a>
            <div class="mt-4">
                <a class="nav-link" href="../home.php">
                    <i class="fas fa-arrow-left"></i> Back to Site
                </a>
                <a class="nav-link" href="logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </nav>
    </div>

    <div class="main-content">
        <?php
        // Include the appropriate page
        switch($current_page) {
            case 'dashboard':
                include 'pages/dashboard.php';
                break;
            case 'users':
                include 'pages/users.php';
                break;
            case 'tweets':
                include 'pages/tweets.php';
                break;
            case 'reports':
                include 'pages/reports.php';
                break;
            case 'logs':
                include 'pages/logs.php';
                break;
            default:
                include 'pages/dashboard.php';
        }
        ?>
    </div>

    <script src="../assets/js/jquery-3.5.1.min.js"></script>
    <script src="../assets/js/bootstrap.min.js"></script>
    <script src="../assets/js/admin.js"></script>
</body>
</html>