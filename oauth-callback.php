<?php
/**
 * Google OAuth Callback Handler
 * Processes the OAuth response and logs in the user
 */

require_once 'config.php';
require_once 'includes/User.php';
require_once 'includes/Auth.php';

// Check for authorization code
if (!isset($_GET['code'])) {
    header('Location: login.php?error=no_code');
    exit;
}

$code = $_GET['code'];

// Exchange authorization code for access token
$tokenUrl = 'https://oauth2.googleapis.com/token';
$postData = array(
    'code' => $code,
    'client_id' => GOOGLE_CLIENT_ID,
    'client_secret' => GOOGLE_CLIENT_SECRET,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'grant_type' => 'authorization_code'
);

// Use file_get_contents instead of cURL (works without extensions)
$options = array(
    'http' => array(
        'method' => 'POST',
        'header' => 'Content-Type: application/x-www-form-urlencoded',
        'content' => http_build_query($postData),
        'ignore_errors' => true
    ),
    'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false
    )
);

$context = stream_context_create($options);
$response = file_get_contents($tokenUrl, false, $context);

if ($response === false) {
    header('Location: login.php?error=token_failed');
    exit;
}

$tokenData = json_decode($response, true);

if (!isset($tokenData['access_token'])) {
    header('Location: login.php?error=no_token&details=' . urlencode($response));
    exit;
}

$accessToken = $tokenData['access_token'];

// Get user info from Google
$userInfoUrl = 'https://www.googleapis.com/oauth2/v2/userinfo';

$options = array(
    'http' => array(
        'method' => 'GET',
        'header' => 'Authorization: Bearer ' . $accessToken,
        'ignore_errors' => true
    ),
    'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false
    )
);

$context = stream_context_create($options);
$response = file_get_contents($userInfoUrl, false, $context);

if ($response === false) {
    header('Location: login.php?error=userinfo_failed');
    exit;
}

$googleUser = json_decode($response, true);

// Create or update user in database
$userModel = new User();
$user = $userModel->createOrUpdateFromGoogle($googleUser);

if (!$user) {
    header('Location: login.php?error=db_failed');
    exit;
}

// Log in the user
Auth::login($user);

// Redirect to dashboard
header('Location: index.php');
exit;
