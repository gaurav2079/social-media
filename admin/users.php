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
            $admin_id = $_SESSION['admin_id'];
            
            if (Admin::deleteUser($user_id, $admin_id)) {
                $_SESSION['message'] = "User deleted successfully";
            } else {
                $_SESSION['error'] = "Failed to delete user";
            }
            break;
    }
    
    header("location: users.php?page=$page&search=" . urlencode($search));
    exit();
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
                        <div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                    <?php endif; ?>

                    <!-- Search Form -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <form method="GET" class="form-inline">
                                <div class="form-group mr-2">
                                    <input type="text" name="search" class="form-control" placeholder="Search users..." value="<?php echo htmlspecialchars($search); ?>">
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
                                <p class="text-muted">No users found</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>User</th>
                                                <th>Username</th>
                                                <th>Email</th>
                                                <th>Stats</th>
                                                <th>Joined</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($users as $user): ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <img src="../assets/images/users/<?php echo htmlspecialchars($user->img); ?>" 
                                                                 alt="Avatar" class="user-avatar mr-3"
                                                                 onerror="this.src='../assets/images/users/default.jpg'">
                                                            <div>
                                                                <strong><?php echo htmlspecialchars($user->name); ?></strong>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>@<?php echo htmlspecialchars($user->username); ?></td>
                                                    <td><?php echo htmlspecialchars($user->email); ?></td>
                                                    <td>
                                                        <span class="badge badge-primary badge-stat" title="Tweets">
                                                            <i class="fas fa-comment"></i> <?php echo $user->tweet_count ?? 0; ?>
                                                        </span>
                                                        <span class="badge badge-info badge-stat" title="Following">
                                                            <i class="fas fa-user-plus"></i> <?php echo $user->following_count ?? 0; ?>
                                                        </span>
                                                        <span class="badge badge-success badge-stat" title="Followers">
                                                            <i class="fas fa-users"></i> <?php echo $user->followers_count ?? 0; ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php 
                                                        $joined = strtotime($user->registered_at ?? $user->created_at ?? 'now');
                                                        echo date('M j, Y', $joined);
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <a href="../<?php echo htmlspecialchars($user->username); ?>" 
                                                               class="btn btn-sm btn-outline-primary" target="_blank">
                                                                <i class="fas fa-eye"></i> View
                                                            </a>
                                                            <button class="btn btn-sm btn-outline-danger" 
                                                                    data-toggle="modal" 
                                                                    data-target="#deleteUserModal<?php echo $user->id; ?>">
                                                                <i class="fas fa-trash"></i> Delete
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>

                                                <!-- Delete User Modal -->
                                                <div class="modal fade" id="deleteUserModal<?php echo $user->id; ?>" tabindex="-1">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <form method="POST">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Delete User</h5>
                                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <p>Are you sure you want to delete user <strong><?php echo htmlspecialchars($user->name); ?></strong>?</p>
                                                                    <p class="text-danger">
                                                                        <i class="fas fa-exclamation-triangle"></i>
                                                                        This will permanently delete all their tweets, likes, and other data.
                                                                    </p>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <input type="hidden" name="user_id" value="<?php echo $user->id; ?>">
                                                                    <input type="hidden" name="action" value="delete_user">
                                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                                    <button type="submit" class="btn btn-danger">Delete User</button>
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
</body>
</html>