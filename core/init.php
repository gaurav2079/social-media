<?php

// Start session only if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
include 'classes/connection.php';

// Include classes
include 'classes/User.php';
include 'classes/Follow.php';
include 'classes/Tweet.php';
include 'classes/Admin.php';

global $pdo;

// Define base URL
define("BASE_URL", "http://localhost/Social/");

?>