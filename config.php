<?php
/**
 * FormMaker System Configuration
 * PHP 7.0.33
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'formmaker');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Application Settings
define('APP_NAME', 'FormMaker System');
define('APP_VERSION', '1.0.0');
define('TIMEZONE', 'UTC');

// Google OAuth Configuration
// Get credentials from: https://console.developers.google.com/
define('GOOGLE_CLIENT_ID', 'YOUR_GOOGLE_CLIENT_ID');
define('GOOGLE_CLIENT_SECRET', 'YOUR_GOOGLE_CLIENT_SECRET');
define('GOOGLE_REDIRECT_URI', 'http://localhost/formmaker/oauth-callback.php');

// Base URL
define('BASE_URL', 'http://localhost/formmaker');

// Set timezone
date_default_timezone_set(TIMEZONE);

// Error Reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session Configuration
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
