# Google OAuth Setup Guide

Follow these steps to set up Google OAuth for the FormMaker System.

## Step 1: Create a Google Cloud Project

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Click on "Select a project" at the top
3. Click "NEW PROJECT"
4. Enter project name: "FormMaker System" (or your preferred name)
5. Click "CREATE"

## Step 2: Enable Google+ API

1. In the Google Cloud Console, go to "APIs & Services" > "Library"
2. Search for "Google+ API"
3. Click on it and press "ENABLE"

## Step 3: Create OAuth 2.0 Credentials

1. Go to "APIs & Services" > "Credentials"
2. Click "CREATE CREDENTIALS" > "OAuth client ID"
3. If prompted, configure the OAuth consent screen:
   - User Type: Select "External"
   - App name: "FormMaker System"
   - User support email: Your email
   - Developer contact: Your email
   - Click "SAVE AND CONTINUE"
   - Skip Scopes (click "SAVE AND CONTINUE")
   - Add test users if needed
   - Click "BACK TO DASHBOARD"

4. Back in Credentials, click "CREATE CREDENTIALS" > "OAuth client ID"
5. Application type: Select "Web application"
6. Name: "FormMaker Web Client"
7. Authorized redirect URIs:
   - Add: `http://localhost/formmaker/oauth-callback.php`
   - Add: `http://yourdomain.com/formmaker/oauth-callback.php` (for production)
8. Click "CREATE"

## Step 4: Copy Credentials

1. You will see a popup with your credentials
2. Copy the "Client ID"
3. Copy the "Client secret"

## Step 5: Update config.php

1. Open `config.php` in your FormMaker installation
2. Replace the following values:
   ```php
   define('GOOGLE_CLIENT_ID', 'YOUR_GOOGLE_CLIENT_ID'); // Paste your Client ID here
   define('GOOGLE_CLIENT_SECRET', 'YOUR_GOOGLE_CLIENT_SECRET'); // Paste your Client secret here
   define('GOOGLE_REDIRECT_URI', 'http://localhost/formmaker/oauth-callback.php'); // Update to match your setup
   ```

3. Also update the BASE_URL:
   ```php
   define('BASE_URL', 'http://localhost/formmaker'); // Update to match your installation path
   ```

## Step 6: Test the Login

1. Navigate to your FormMaker installation: `http://localhost/formmaker/`
2. You should be redirected to the login page
3. Click "Sign in with Google"
4. Select your Google account
5. Grant permissions
6. You should be redirected back and logged in!

## Troubleshooting

### Error: redirect_uri_mismatch

- Make sure the redirect URI in config.php exactly matches the one in Google Cloud Console
- Include the full URL path including `/oauth-callback.php`
- Check for http vs https
- No trailing slashes

### Error: invalid_client

- Verify your Client ID and Client Secret are correct
- Make sure there are no extra spaces when copying
- Regenerate credentials if needed

### Error: access_denied

- The user denied permission to the app
- Check the OAuth consent screen configuration
- Make sure your email is added as a test user if using External user type

### CURL SSL Certificate Problem

- The code includes `CURLOPT_SSL_VERIFYPEER => false` for development
- For production, remove this line and ensure proper SSL certificates

## Production Deployment

For production deployment:

1. Use HTTPS instead of HTTP
2. Update redirect URIs in Google Cloud Console to use your production domain
3. Update config.php with production URLs
4. Enable `CURLOPT_SSL_VERIFYPEER` for secure connections
5. Set error reporting to 0 in config.php
6. Verify OAuth consent screen is properly configured
7. Consider changing to "Internal" user type if using Google Workspace

## Security Notes

- Never commit your Client ID and Client Secret to public repositories
- Use environment variables for sensitive data in production
- Regularly rotate your Client Secret
- Monitor OAuth usage in Google Cloud Console
- Review permissions requested in OAuth consent screen
