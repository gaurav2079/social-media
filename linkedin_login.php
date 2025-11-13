<?php
session_start();

// LinkedIn OAuth Configuration
$client_id = 'YOUR_LINKEDIN_CLIENT_ID';
$redirect_uri = 'http://localhost/linkedin_callback.php';
$scope = 'r_liteprofile r_emailaddress';

$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;

$auth_url = "https://www.linkedin.com/oauth/v2/authorization?" . http_build_query([
    'response_type' => 'code',
    'client_id' => $client_id,
    'redirect_uri' => $redirect_uri,
    'state' => $state,
    'scope' => $scope
]);

header('Location: ' . $auth_url);
exit();
?>