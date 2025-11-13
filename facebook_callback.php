<?php
session_start();
include 'core/init.php';

if (isset($_GET['code']) && isset($_GET['state'])) {
    if ($_GET['state'] !== $_SESSION['oauth_state']) {
        die('Invalid state parameter');
    }
    
    $app_id = 'YOUR_FACEBOOK_APP_ID';
    $app_secret = 'YOUR_FACEBOOK_APP_SECRET';
    $redirect_uri = 'http://localhost/facebook_callback.php';
    
    // Exchange code for access token
    $token_url = "https://graph.facebook.com/v12.0/oauth/access_token?" . http_build_query([
        'client_id' => $app_id,
        'redirect_uri' => $redirect_uri,
        'client_secret' => $app_secret,
        'code' => $_GET['code']
    ]);
    
    $token_response = file_get_contents($token_url);
    $token_data = json_decode($token_response, true);
    
    if (isset($token_data['access_token'])) {
        // Get user info
        $graph_url = "https://graph.facebook.com/me?fields=id,name,email&access_token=" . $token_data['access_token'];
        $user_info = file_get_contents($graph_url);
        $user_data = json_decode($user_info, true);
        
        $email = $user_data['email'] ?? 'facebook_' . $user_data['id'] . '@example.com';
        $name = $user_data['name'];
        $username = 'facebook_' . $user_data['id'];
        
        if (User::checkEmail($email) === true) {
            $user = User::getUserByEmail($email);
            $_SESSION['user_id'] = $user->user_id;
            header('location: home.php');
        } else {
            $password = password_hash(uniqid(), PASSWORD_DEFAULT);
            User::register($email, $password, $name, $username, 'facebook');
            $user = User::getUserByEmail($email);
            $_SESSION['user_id'] = $user->user_id;
            header('location: home.php');
        }
        exit();
    }
}

$_SESSION['errors_signup'] = ['Facebook login failed. Please try again.'];
header('location: index.php');
?>