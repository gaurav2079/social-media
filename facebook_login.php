<?php
session_start();

// Facebook OAuth Configuration
$app_id = 'YOUR_FACEBOOK_APP_ID';
$redirect_uri = 'http://localhost/facebook_callback.php';
$scope = 'email,public_profile';

$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;

$auth_url = "https://www.facebook.com/v12.0/dialog/oauth?" . http_build_query([
    'client_id' => $app_id,
    'redirect_uri' => $redirect_uri,
    'state' => $state,
    'scope' => $scope
]);

header('Location: ' . $auth_url);
exit();
?>