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

$ch = curl_init($tokenUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For development only

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    header('Location: login.php?error=token_failed');
    exit;
}

$tokenData = json_decode($response, true);

if (!isset($tokenData['access_token'])) {
    header('Location: login.php?error=no_token');
    exit;
}

$accessToken = $tokenData['access_token'];

// Get user info from Google
$userInfoUrl = 'https://www.googleapis.com/oauth2/v2/userinfo';

$ch = curl_init($userInfoUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Authorization: Bearer ' . $accessToken
));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For development only

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
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
