<?php
session_start();

// Google OAuth Configuration (You need to get these from Google Cloud Console)
$client_id = 'YOUR_GOOGLE_CLIENT_ID';
$redirect_uri = 'http://localhost/google_callback.php'; // Change to your domain
$scope = 'https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/userinfo.email';

// Generate a random state parameter for security
$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;

// Redirect to Google OAuth page
$auth_url = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query([
    'client_id' => $client_id,
    'redirect_uri' => $redirect_uri,
    'response_type' => 'code',
    'scope' => $scope,
    'state' => $state,
    'access_type' => 'online',
    'prompt' => 'consent'
]);

header('Location: ' . $auth_url);
exit();
?>