<div class="col-md-2 sidebar p-0">
    <div class="p-3">
        <h4 class="text-center">Kabi Admin</h4>
        <hr class="bg-light">
    </div>
    <nav class="nav flex-column">
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'users.php' ? 'active' : ''; ?>" href="users.php">
            <i class="fas fa-users"></i> Users
        </a>
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'tweets.php' ? 'active' : ''; ?>" href="tweets.php">
            <i class="fas fa-comment"></i> Tweets
        </a>
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'reports.php' ? 'active' : ''; ?>" href="reports.php">
            <i class="fas fa-flag"></i> Reports
            <?php
            $db = DB::getInstance();
            $stmt = $db->query("SELECT COUNT(*) as total FROM reports WHERE status = 'pending'");
            $pending_count = $stmt->fetch(PDO::FETCH_OBJ)->total;
            if ($pending_count > 0): ?>
                <span class="badge badge-danger float-right"><?php echo $pending_count; ?></span>
            <?php endif; ?>
        </a>
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'logs.php' ? 'active' : ''; ?>" href="logs.php">
            <i class="fas fa-history"></i> Logs
        </a>
        <a class="nav-link" href="logout.php">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </nav>
</div>