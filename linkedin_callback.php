<?php
session_start();
include 'core/init.php';

if (isset($_GET['code']) && isset($_GET['state'])) {
    if ($_GET['state'] !== $_SESSION['oauth_state']) {
        die('Invalid state parameter');
    }
    
    $client_id = 'YOUR_LINKEDIN_CLIENT_ID';
    $client_secret = 'YOUR_LINKEDIN_CLIENT_SECRET';
    $redirect_uri = 'http://localhost/linkedin_callback.php';
    
    // Exchange code for access token
    $token_url = 'https://www.linkedin.com/oauth/v2/accessToken';
    $post_data = [
        'grant_type' => 'authorization_code',
        'code' => $_GET['code'],
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'redirect_uri' => $redirect_uri
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
        // Get user profile
        $profile_url = 'https://api.linkedin.com/v2/me';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $profile_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token_data['access_token']
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $profile_info = curl_exec($ch);
        curl_close($ch);
        
        $profile_data = json_decode($profile_info, true);
        
        // Get email address
        $email_url = 'https://api.linkedin.com/v2/emailAddress?q=members&projection=(elements*(handle~))';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $email_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token_data['access_token']
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $email_info = curl_exec($ch);
        curl_close($ch);
        
        $email_data = json_decode($email_info, true);
        
        $email = $email_data['elements'][0]['handle~']['emailAddress'] ?? 'linkedin_' . $profile_data['id'] . '@example.com';
        $name = $profile_data['localizedFirstName'] . ' ' . $profile_data['localizedLastName'];
        $username = 'linkedin_' . $profile_data['id'];
        
        if (User::checkEmail($email) === true) {
            $user = User::getUserByEmail($email);
            $_SESSION['user_id'] = $user->user_id;
            header('location: home.php');
        } else {
            $password = password_hash(uniqid(), PASSWORD_DEFAULT);
            User::register($email, $password, $name, $username, 'linkedin');
            $user = User::getUserByEmail($email);
            $_SESSION['user_id'] = $user->user_id;
            header('location: home.php');
        }
        exit();
    }
}

$_SESSION['errors_signup'] = ['LinkedIn login failed. Please try again.'];
header('location: index.php');
?>