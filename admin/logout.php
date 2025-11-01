<?php
session_start();

// Log the logout action if admin was logged in
if (isset($_SESSION['admin_id'])) {
    include '../core/init.php';
    Admin::logAction($_SESSION['admin_id'], 'logout', 'Admin logged out');
}

// Destroy admin session
session_destroy();

// Redirect to login
header('location: login.php');
exit();
?>