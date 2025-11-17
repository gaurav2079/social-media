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

// Get users
$users = Admin::getAllUsers($page, $limit, $search);

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'delete_user':
            $user_id = $_POST['user_id'] ?? '';
            
            // Get admin ID from session
            $admin_id = $_SESSION['admin_id'] ?? $_SESSION['admin']->id ?? 1;
            
            // DIRECT DATABASE DELETION - SIMPLE APPROACH
            try {
                $db = new PDO("mysql:host=localhost;dbname=tweetphp;charset=utf8mb4", "root", "");
                
                // Start transaction
                $db->beginTransaction();
                
                // Delete user from database
                $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
                $result = $stmt->execute([$user_id]);
                $affected_rows = $stmt->rowCount();
                
                if ($affected_rows > 0) {
                    $_SESSION['message'] = "User deleted successfully!";
                    $db->commit();
                } else {
                    $_SESSION['error'] = "User not found or already deleted!";
                    $db->rollBack();
                }
                
            } catch (Exception $e) {
                $_SESSION['error'] = "Error deleting user: " . $e->getMessage();
            }
            
            // Redirect to prevent form resubmission
            header("location: users.php?page=$page&search=" . urlencode($search));
            exit();
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users | Kabi Admin</title>
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
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }
        .badge-stat {
            font-size: 0.8em;
        }
        .table th {
            border-top: none;
        }
        .btn-action {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        .delete-form {
            display: inline-block;
            margin-left: 5px;
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
                    <a class="nav-link active" href="users.php">
                        <i class="fas fa-users"></i> Users
                    </a>
                    <a class="nav-link" href="tweets.php">
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
                        <h2>Manage Users</h2>
                        <span class="text-muted">Total Users: <?php echo count($users); ?></span>
                    </div>

                    <?php if (isset($_SESSION['message'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> 
                            <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle"></i> 
                            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <!-- Search Form -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <form method="GET" class="form-inline">
                                <input type="hidden" name="page" value="1">
                                <div class="form-group mr-2">
                                    <input type="text" name="search" class="form-control" placeholder="Search by username, name, or email..." value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                                <button type="submit" class="btn btn-primary mr-2">
                                    <i class="fas fa-search"></i> Search
                                </button>
                                <a href="users.php" class="btn btn-secondary">Clear</a>
                            </form>
                        </div>
                    </div>

                    <!-- Users Table -->
                    <div class="card">
                        <div class="card-body">
                            <?php if (empty($users)): ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No users found</p>
                                    <?php if (!empty($search)): ?>
                                        <a href="users.php" class="btn btn-primary">View All Users</a>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th>ID</th>
                                                <th>User</th>
                                                <th>Username</th>
                                                <th>Email</th>
                                                
                                                <th>Joined</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($users as $user): ?>
                                                <tr id="user-row-<?php echo $user->id; ?>">
                                                    <td><?php echo $user->id; ?></td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <img src="../assets/images/users/<?php echo htmlspecialchars($user->img); ?>" 
                                                                 alt="Avatar" class="user-avatar mr-3"
                                                                 onerror="this.src='../assets/images/users/default.jpg'">
                                                            <div>
                                                                <strong><?php echo htmlspecialchars($user->name); ?></strong>
                                                                <?php if (!empty($user->bio)): ?>
                                                                    <br><small class="text-muted"><?php echo htmlspecialchars(substr($user->bio, 0, 50)); ?><?php echo strlen($user->bio) > 50 ? '...' : ''; ?></small>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>@<?php echo htmlspecialchars($user->username); ?></td>
                                                    <td><?php echo htmlspecialchars($user->email); ?></td>
                                                   
                                                    <td>
                                                        <?php 
                                                        $joined = strtotime($user->registered_at ?? $user->created_at ?? 'now');
                                                        echo date('M j, Y', $joined);
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="../profile.php?username=<?php echo htmlspecialchars($user->username); ?>" 
                                                               class="btn btn-outline-primary btn-action" target="_blank" title="View Profile">
                                                                <i class="fas fa-eye"></i> View
                                                            </a>
                                                            <!-- SIMPLE DELETE FORM - NO JAVASCRIPT NEEDED -->
                                                            <form method="POST" class="delete-form" onsubmit="return confirm('Are you sure you want to delete <?php echo addslashes($user->name); ?>? This will permanently remove the user and all their data.');">
                                                                <input type="hidden" name="user_id" value="<?php echo $user->id; ?>">
                                                                <input type="hidden" name="action" value="delete_user">
                                                                <button type="submit" class="btn btn-outline-danger btn-action" title="Delete User">
                                                                    <i class="fas fa-trash"></i> Delete
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
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
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>">
                                    <i class="fas fa-chevron-left"></i> Previous
                                </a>
                            </li>
                            <li class="page-item active">
                                <span class="page-link">Page <?php echo $page; ?></span>
                            </li>
                            <li class="page-item <?php echo empty($users) || count($users) < $limit ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>">
                                    Next <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/jquery-3.5.1.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>