<?php
/**
 * Login Page
 * Google OAuth login
 */

require_once 'config.php';
require_once 'includes/Auth.php';

// Redirect if already logged in
Auth::redirect_if_authenticated();

// Generate Google OAuth URL
$googleAuthUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
    'client_id' => GOOGLE_CLIENT_ID,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'response_type' => 'code',
    'scope' => 'email profile',
    'access_type' => 'online',
    'prompt' => 'select_account'
]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .login-container {
            max-width: 450px;
            margin: 100px auto;
            text-align: center;
        }

        .login-box {
            background: white;
            padding: 50px 40px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }

        .login-box h1 {
            color: #667eea;
            margin-bottom: 15px;
            font-size: 2.5em;
        }

        .login-box p {
            color: #666;
            margin-bottom: 40px;
            font-size: 1.1em;
        }

        .google-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: white;
            border: 2px solid #e0e0e0;
            padding: 15px 30px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            color: #333;
            text-decoration: none;
            transition: all 0.3s;
            cursor: pointer;
        }

        .google-btn:hover {
            border-color: #667eea;
            box-shadow: 0 5px 15px rgba(102,126,234,0.2);
        }

        .google-icon {
            width: 24px;
            height: 24px;
            margin-right: 12px;
        }

        .features {
            margin-top: 40px;
            text-align: left;
        }

        .features h3 {
            color: #333;
            margin-bottom: 15px;
        }

        .features ul {
            list-style: none;
            padding: 0;
        }

        .features li {
            padding: 10px 0;
            color: #666;
            border-bottom: 1px solid #f0f0f0;
        }

        .features li:before {
            content: "✓ ";
            color: #667eea;
            font-weight: bold;
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h1><?php echo APP_NAME; ?></h1>
            <p>Sign in with your Google account to get started</p>

            <a href="<?php echo htmlspecialchars($googleAuthUrl); ?>" class="google-btn">
                <svg class="google-icon" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                Sign in with Google
            </a>

            <div class="features">
                <h3>What you can do:</h3>
                <ul>
                    <li>Create unlimited custom forms</li>
                    <li>Add multiple field types</li>
                    <li>Collect and view submissions</li>
                    <li>Export data to CSV/JSON</li>
                    <li>Manage your identity and profile</li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>
