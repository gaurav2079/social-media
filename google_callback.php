<?php
session_start();
include 'core/init.php';

if (isset($_GET['code']) && isset($_GET['state'])) {
    // Verify state parameter
    if ($_GET['state'] !== $_SESSION['oauth_state']) {
        die('Invalid state parameter');
    }
    
    // Exchange authorization code for access token
    $client_id = 'YOUR_GOOGLE_CLIENT_ID';
    $client_secret = 'YOUR_GOOGLE_CLIENT_SECRET';
    $redirect_uri = 'http://localhost/google_callback.php';
    
    $token_url = 'https://oauth2.googleapis.com/token';
    $post_data = [
        'code' => $_GET['code'],
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'redirect_uri' => $redirect_uri,
        'grant_type' => 'authorization_code'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $token_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $token_data = json_decode($response, true);
    
    if (isset($token_data['access_token'])) {
        // Get user info from Google
        $user_info_url = 'https://www.googleapis.com/oauth2/v2/userinfo';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $user_info_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token_data['access_token']
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $user_info = curl_exec($ch);
        curl_close($ch);
        
        $user_data = json_decode($user_info, true);
        
        // Register or login the user
        if (isset($user_data['email'])) {
            $email = $user_data['email'];
            $name = $user_data['name'] ?? 'Google User';
            $username = 'google_' . $user_data['id'];
            
            // Check if user exists, if not create new account
            if (User::checkEmail($email) === true) {
                // User exists - log them in
                $user = User::getUserByEmail($email);
                $_SESSION['user_id'] = $user->user_id;
                header('location: home.php');
            } else {
                // Create new user
                $password = password_hash(uniqid(), PASSWORD_DEFAULT);
                User::register($email, $password, $name, $username, 'google');
                $user = User::getUserByEmail($email);
                $_SESSION['user_id'] = $user->user_id;
                header('location: home.php');
            }
            exit();
        }
    }
}

// If something went wrong
$_SESSION['errors_signup'] = ['Google login failed. Please try again.'];
header('location: index.php');
?>