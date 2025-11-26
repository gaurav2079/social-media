
<?php 
include 'core/init.php';
  
$user_id = $_SESSION['user_id'];

$user = User::getData($user_id);
$who_users = Follow::whoToFollow($user_id);
$notify_count = User::CountNotification($user_id);

if (User::checkLogIn() === false) 
header('location: index.php');

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings | Kabi</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/profile_style.css?v=<?php echo time(); ?>">
    <link rel="shortcut icon" type="image/png" href="assets/images/kabi.png"> 
   
    <style>
        /* Reset and Base Styles */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8fafc;
            color: #2d3748;
            line-height: 1.6;
        }
        
        a {
            text-decoration: none;
            color: inherit;
        }
        
        /* Layout Structure */
        .settings-container {
            display: flex;
            min-height: 100vh;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        /* Left Sidebar */
        .sidebar {
            width: 250px;
            background: white;
            border-right: 1px solid #e2e8f0;
            padding: 20px 15px;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
        }
        
        .logo {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            padding: 0 10px;
        }
        
        .logo img {
            margin-right: 10px;
        }
        
        .logo span {
            font-weight: 700;
            font-size: 20px;
            color: #667eea;
        }
        
        .nav-menu {
            list-style: none;
            margin-bottom: 30px;
        }
        
        .nav-item {
            margin-bottom: 8px;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            border-radius: 10px;
            transition: all 0.3s ease;
            color: #4a5568;
        }
        
        .nav-link:hover {
            background-color: #f7fafc;
            color: #2d3748;
        }
        
        .nav-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .nav-icon {
            margin-right: 12px;
            font-size: 18px;
            width: 20px;
            text-align: center;
        }
        
        .user-profile {
            background: white;
            border-radius: 15px;
            padding: 15px;
            margin-top: auto;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
            position: absolute;
            bottom: 20px;
            left: 15px;
            right: 15px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
        }
        
        .user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 12px;
            border: 2px solid #667eea;
        }
        
        .user-details {
            flex: 1;
        }
        
        .user-name {
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 2px;
        }
        
        .user-handle {
            color: #718096;
            font-size: 12px;
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            padding: 0;
            background: #f8fafc;
            overflow-y: auto;
            max-height: 100vh;
        }
        
        .content-header {
            background: white;
            padding: 25px 30px;
            border-bottom: 1px solid #e2e8f0;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .page-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 5px;
            color: #2d3748;
        }
        
        .page-subtitle {
            color: #718096;
            font-size: 16px;
        }
        
        .content-body {
            padding: 30px;
            max-width: 100%;
            overflow-x: hidden;
        }
        
        .settings-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
            overflow: hidden;
            margin-bottom: 30px;
        }
        
        .settings-tabs {
            display: flex;
            border-bottom: 1px solid #e2e8f0;
            background: #f7fafc;
        }
        
        .tab-btn {
            flex: 1;
            padding: 18px 20px;
            text-align: center;
            background: none;
            border: none;
            font-weight: 500;
            color: #718096;
            transition: all 0.3s ease;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        
        .tab-btn:hover {
            background: #edf2f7;
            color: #4a5568;
        }
        
        .tab-btn.active {
            background: white;
            color: #667eea;
            border-bottom: 3px solid #667eea;
        }
        
        .tab-icon {
            font-size: 20px;
            margin-bottom: 8px;
        }
        
        .tab-content {
            padding: 30px;
        }
        
        .tab-pane {
            display: none;
        }
        
        .tab-pane.active {
            display: block;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #2d3748;
            font-size: 14px;
        }
        
        .input-with-icon {
            position: relative;
        }
        
        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #a0aec0;
            z-index: 2;
        }
        
        .form-control {
            width: 100%;
            padding: 14px 15px 14px 45px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #f8fafc;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            background: white;
            outline: none;
        }
        
        .btn {
            padding: 14px 30px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 10px rgba(102, 126, 234, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            box-shadow: 0 4px 10px rgba(245, 87, 108, 0.3);
        }
        
        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(245, 87, 108, 0.4);
        }
        
        .btn-icon {
            margin-right: 8px;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        
        .alert-danger {
            background: #fed7d7;
            color: #c53030;
            border-left: 4px solid #e53e3e;
        }
        
        .alert-icon {
            margin-right: 10px;
            font-size: 18px;
        }
        
        .danger-zone {
            border: 2px dashed #fc8181;
            border-radius: 15px;
            padding: 25px;
            background: #fff5f5;
            margin-bottom: 25px;
        }
        
        .danger-title {
            color: #c53030;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        
        .danger-icon {
            margin-right: 10px;
            font-size: 20px;
        }
        
        .checkbox-group {
            display: flex;
            align-items: flex-start;
            margin: 20px 0;
        }
        
        .checkbox {
            margin-right: 12px;
            margin-top: 4px;
        }
        
        .checkbox-label {
            font-size: 14px;
            color: #4a5568;
        }
        
        /* Right Sidebar */
        .right-sidebar {
            width: 300px;
            background: white;
            border-left: 1px solid #e2e8f0;
            padding: 20px;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
        }
        
        .search-container {
            margin-bottom: 25px;
        }
        
        .search-box {
            position: relative;
        }
        
        .search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #a0aec0;
        }
        
        .search-input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 1px solid #e2e8f0;
            border-radius: 25px;
            font-size: 14px;
            background: #f7fafc;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #667eea;
            background: white;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 20px;
            color: #2d3748;
            padding-bottom: 15px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .user-card {
            display: flex;
            align-items: center;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 12px;
            transition: all 0.3s ease;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }
        
        .user-card:hover {
            background: #edf2f7;
            transform: translateY(-2px);
        }
        
        .card-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 12px;
            border: 2px solid #667eea;
        }
        
        .card-info {
            flex: 1;
        }
        
        .card-name {
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 2px;
        }
        
        .card-handle {
            color: #718096;
            font-size: 12px;
        }
        
        .follows-you {
            background: #667eea;
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 10px;
            margin-left: 5px;
        }
        
        .follow-btn {
            padding: 8px 15px;
            border: none;
            border-radius: 20px;
            font-weight: 600;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .follow-btn.follow {
            background: #667eea;
            color: white;
        }
        
        .follow-btn.following {
            background: #e2e8f0;
            color: #4a5568;
        }
        
        .weather-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            padding: 20px;
            color: white;
            margin-top: 25px;
        }
        
        .weather-temp {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .weather-desc {
            font-size: 16px;
            opacity: 0.9;
        }
        
        /* Notification badge */
        .notify-count {
            background: #e53e3e;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            margin-left: auto;
        }
        
        /* Responsive Design */
        @media (max-width: 1200px) {
            .right-sidebar {
                width: 280px;
            }
        }
        
        @media (max-width: 992px) {
            .settings-container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                border-right: none;
                border-bottom: 1px solid #e2e8f0;
            }
            
            .nav-menu {
                display: flex;
                overflow-x: auto;
                margin-bottom: 15px;
            }
            
            .nav-item {
                margin-bottom: 0;
                margin-right: 10px;
            }
            
            .nav-link {
                white-space: nowrap;
            }
            
            .user-profile {
                position: relative;
                bottom: auto;
                left: auto;
                right: auto;
                margin-top: 15px;
            }
            
            .right-sidebar {
                width: 100%;
                height: auto;
                position: relative;
                border-left: none;
                border-top: 1px solid #e2e8f0;
            }
            
            .settings-tabs {
                flex-direction: column;
            }
        }
        
        @media (max-width: 768px) {
            .content-body {
                padding: 20px 15px;
            }
            
            .tab-content {
                padding: 20px;
            }
            
            .settings-tabs {
                flex-direction: row;
                overflow-x: auto;
            }
            
            .tab-btn {
                min-width: 120px;
            }
        }
        
        @media (max-width: 576px) {
            .content-header {
                padding: 20px 15px;
            }
            
            .page-title {
                font-size: 20px;
            }
            
            .page-subtitle {
                font-size: 14px;
            }
            
            .btn {
                padding: 12px 20px;
                font-size: 14px;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="settings-container">
        <!-- Left Sidebar -->
        <div class="sidebar">
            <div class="logo">
                <img src="assets/images/kabi.png" alt="Kabi" height="30" width="30">
                <span>Kabi</span>
            </div>
            
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="home.php" class="nav-link">
                        <i class="fas fa-home nav-icon"></i>
                        <span>Home</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="notification.php" class="nav-link">
                        <i class="fas fa-bell nav-icon"></i>
                        <span>Notifications</span>
                        <?php if ($notify_count > 0) { ?>
                            <span class="notify-count"><?php echo $notify_count; ?></span>
                        <?php } ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo BASE_URL . $user->username; ?>" class="nav-link">
                        <i class="fas fa-user nav-icon"></i>
                        <span>Profile</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="account.php" class="nav-link active">
                        <i class="fas fa-cog nav-icon"></i>
                        <span>Settings</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="includes/logout.php" class="nav-link">
                        <i class="fas fa-sign-out-alt nav-icon"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
            
            <div class="user-profile">
                <div class="user-info">
                    <img src="assets/images/users/<?php echo $user->img ?>" alt="User" class="user-avatar">
                    <div class="user-details">
                        <div class="user-name"><?php if($user->name !== null) { echo $user->name; } ?></div>
                        <div class="user-handle">@<?php echo $user->username; ?></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="content-header">
                <h1 class="page-title">Account Settings</h1>
                <p class="page-subtitle">Manage your account preferences and security</p>
            </div>
            
            <div class="content-body">
                <div class="settings-card">
                    <div class="settings-tabs">
                        <button class="tab-btn active" data-tab="profile">
                            <i class="fas fa-user-edit tab-icon"></i>
                            <span>Profile</span>
                        </button>
                        <button class="tab-btn" data-tab="security">
                            <i class="fas fa-lock tab-icon"></i>
                            <span>Security</span>
                        </button>
                        <button class="tab-btn" data-tab="danger">
                            <i class="fas fa-exclamation-triangle tab-icon"></i>
                            <span>Danger Zone</span>
                        </button>
                    </div>
                    
                    <div class="tab-content">
                        <!-- Profile Tab -->
                        <div class="tab-pane active" id="profile">
                            <form method="POST" action="handle/handleAccountSetting.php">
                                <?php if (isset($_SESSION['errors_account'])) { ?>
                                    <?php foreach ($_SESSION['errors_account'] as $error) { ?>
                                        <div class="alert alert-danger">
                                            <i class="fas fa-exclamation-circle alert-icon"></i>
                                            <span><?php echo $error; ?></span>
                                        </div>
                                    <?php } ?>
                                <?php } unset($_SESSION['errors_account']) ?>
                                
                                <div class="form-group">
                                    <label class="form-label">Email Address</label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-envelope input-icon"></i>
                                        <input type="email" name="email" value="<?php echo $user->email; ?>" class="form-control" placeholder="Enter email">
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Username</label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-user input-icon"></i>
                                        <input type="text" name="username" value="<?php echo $user->username; ?>" class="form-control" placeholder="Username">
                                    </div>
                                </div>
                                
                                <button type="submit" name="submit" class="btn btn-primary">
                                    <i class="fas fa-save btn-icon"></i>
                                    Save Changes
                                </button>
                            </form>
                        </div>
                        
                        <!-- Security Tab -->
                        <div class="tab-pane" id="security">
                            <form method="POST" action="handle/handleChangePassword.php">
                                <?php if (isset($_SESSION['errors_password'])) { ?>
                                    <?php foreach ($_SESSION['errors_password'] as $error) { ?>
                                        <div class="alert alert-danger">
                                            <i class="fas fa-exclamation-circle alert-icon"></i>
                                            <span><?php echo $error; ?></span>
                                        </div>
                                    <?php } ?>
                                <?php } unset($_SESSION['errors_password']) ?>
                                
                                <div class="form-group">
                                    <label class="form-label">Current Password</label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-lock input-icon"></i>
                                        <input type="password" name="old_password" class="form-control" placeholder="Current Password">
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">New Password</label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-key input-icon"></i>
                                        <input type="password" name="new_password" class="form-control" placeholder="New Password">
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Confirm New Password</label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-check-circle input-icon"></i>
                                        <input type="password" name="ver_password" class="form-control" placeholder="Confirm New Password">
                                    </div>
                                </div>
                                
                                <button type="submit" name="submit" class="btn btn-primary">
                                    <i class="fas fa-shield-alt btn-icon"></i>
                                    Update Password
                                </button>
                            </form>
                        </div>
                        
                        <!-- Danger Zone Tab -->
                        <div class="tab-pane" id="danger">
                            <div class="danger-zone">
                                <h3 class="danger-title">
                                    <i class="fas fa-radiation-alt danger-icon"></i>
                                    Warning: Account Deletion
                                </h3>
                                <p>Deleting your account is a permanent action and cannot be undone. All your data, including posts, followers, and settings will be permanently removed from our system.</p>
                                <p>If you're sure you want to proceed, please enter your password below to confirm account deletion.</p>
                            </div>
                            
                            <form method="POST" action="handle/handleDeleteAccount.php">
                                <?php if (isset($_SESSION['errors_delete'])) { ?>
                                    <?php foreach ($_SESSION['errors_delete'] as $error) { ?>
                                        <div class="alert alert-danger">
                                            <i class="fas fa-exclamation-circle alert-icon"></i>
                                            <span><?php echo $error; ?></span>
                                        </div>
                                    <?php } ?>
                                <?php } unset($_SESSION['errors_delete']) ?>
                                
                                <div class="form-group">
                                    <label class="form-label">Confirm Password</label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-lock input-icon"></i>
                                        <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
                                    </div>
                                </div>
                                
                                <div class="checkbox-group">
                                    <input type="checkbox" name="confirm_delete" class="checkbox" id="confirmDelete" required>
                                    <label class="checkbox-label" for="confirmDelete">
                                        I understand that this action cannot be undone and all my data will be permanently deleted.
                                    </label>
                                </div>
                                
                                <button type="submit" name="delete_account" class="btn btn-danger">
                                    <i class="fas fa-trash-alt btn-icon"></i>
                                    Delete My Account
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right Sidebar -->
        <div class="right-sidebar">
            <div class="search-container">
                <div class="search-box">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="search-input" placeholder="Search kabi">
                    <div class="search-result"></div>
                </div>
            </div>
            
            <h3 class="section-title">Who to follow</h3>
            
            <?php foreach($who_users as $user) { 
                $user_follow = Follow::isUserFollow($user_id , $user->id);
            ?>
                <div class="user-card">
                    <a href="<?php echo $user->username; ?>">
                        <img src="assets/images/users/<?php echo $user->img; ?>" alt="<?php echo $user->name; ?>" class="card-avatar">
                    </a>
                    <div class="card-info">
                        <div class="card-name">
                            <a href="<?php echo $user->username; ?>">
                                <?php echo $user->name; ?>
                            </a>
                        </div>
                        <div class="card-handle">
                            @<?php echo $user->username; ?>
                            <?php if (Follow::FollowsYou($user->id , $user_id)) { ?>
                                <span class="follows-you">Follows You</span>
                            <?php } ?>
                        </div>
                    </div>
                    <button class="follow-btn <?= $user_follow ? 'following' : 'follow' ?>" 
                            data-follow="<?php echo $user->id; ?>"
                            data-user="<?php echo $user_id; ?>">
                        <?php if($user_follow) { ?>
                            Following
                        <?php } else { ?>
                            Follow
                        <?php } ?>
                    </button>
                </div>
            <?php } ?>
            
            <div class="weather-card">
                <div class="weather-temp">22°C</div>
                <div class="weather-desc">Mostly cloudy</div>
            </div>
        </div>
    </div>
    
    <script src="assets/js/jquery-3.5.1.min.js"></script>
    <script src="assets/js/popper.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/search.js"></script>
    <script src="assets/js/follow.js"></script>
    
    <script>
        // Tab functionality
        document.addEventListener('DOMContentLoaded', function() {
            const tabBtns = document.querySelectorAll('.tab-btn');
            const tabPanes = document.querySelectorAll('.tab-pane');
            
            tabBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const tabId = this.getAttribute('data-tab');
                    
                    // Remove active class from all tabs and panes
                    tabBtns.forEach(b => b.classList.remove('active'));
                    tabPanes.forEach(p => p.classList.remove('active'));
                    
                    // Add active class to current tab and pane
                    this.classList.add('active');
                    document.getElementById(tabId).classList.add('active');
                });
            });
            
            // Delete account confirmation
            const deleteForm = document.querySelector('form[action="handle/handleDeleteAccount.php"]');
            if (deleteForm) {
                deleteForm.addEventListener('submit', function(e) {
                    const confirmDelete = document.getElementById('confirmDelete');
                    if (!confirmDelete.checked) {
                        e.preventDefault();
                        alert('Please confirm that you understand the consequences of deleting your account.');
                        return false;
                    }
                    
                    if (!confirm('Are you absolutely sure you want to delete your account? This action cannot be undone.')) {
                        e.preventDefault();
                        return false;
                    }
                });
            }
            
            // Auto-open security tab if there are errors
            <?php if (isset($_SESSION['errors_password'])) { ?>
                document.querySelector('[data-tab="security"]').click();
            <?php } ?>
        });
    </script>
</body>
</html>