<?php
// handle/handleDeleteAccount.php

session_start();
include '../core/init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_account'])) {
    
    $user_id = $_SESSION['user_id'];
    $password = $_POST['password'];
    
    // Initialize errors array
    $_SESSION['errors_delete'] = array();
    
    // Validate password
    if (empty($password)) {
        $_SESSION['errors_delete'][] = "Password is required to delete your account";
    }
    
    // Check if user exists and verify password
    $user = User::getData($user_id);
    if (!$user) {
        $_SESSION['errors_delete'][] = "User not found";
    } else {
        // Verify password - using MD5 hash as shown in your database
        if (md5($password) !== $user->password) {
            $_SESSION['errors_delete'][] = "Incorrect password. Please try again.";
        }
    }
    
    // If there are errors, redirect back
    if (!empty($_SESSION['errors_delete'])) {
        header('location: ../account.php');
        exit();
    }
    
    // Use your existing database connection method
    // Since your other pages work, let's use the same approach
    require_once '../core/init.php';
    
    // Create a direct database connection
    $db_host = 'localhost'; // or your host
    $db_user = 'root'; // your database username
    $db_pass = ''; // your database password
    $db_name = 'tweetphp'; // your database name
    
    $con = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
    
    if (!$con) {
        $_SESSION['errors_delete'][] = "Database connection failed";
        header('location: ../account.php');
        exit();
    }
    
    // Simple deletion query
    $query = "DELETE FROM users WHERE id = $user_id";
    
    if (mysqli_query($con, $query)) {
        // Account deleted successfully
        mysqli_close($con);
        session_destroy();
        header('location: ../index.php?account_deleted=true');
        exit();
    } else {
        $_SESSION['errors_delete'][] = "Failed to delete account: " . mysqli_error($con);
        mysqli_close($con);
        header('location: ../account.php');
        exit();
    }
    
} else {
    header('location: ../account.php');
    exit();
}