# Enabling cURL Extension for PHP

The cURL extension is required for Google OAuth to work. Follow the instructions for your operating system:

## Ubuntu/Debian (Linux)

1. Install the cURL extension:
```bash
sudo apt-get update
sudo apt-get install php-curl
```

2. For specific PHP versions (e.g., PHP 7.0):
```bash
sudo apt-get install php7.0-curl
```

3. Restart Apache:
```bash
sudo service apache2 restart
```

Or restart Nginx + PHP-FPM:
```bash
sudo service php7.0-fpm restart
sudo service nginx restart
```

## CentOS/RHEL (Linux)

1. Install the cURL extension:
```bash
sudo yum install php-curl
```

2. Restart Apache:
```bash
sudo systemctl restart httpd
```

## Windows (XAMPP/WAMP)

1. Open `php.ini` file (usually in `C:\xampp\php\php.ini`)
2. Find the line: `;extension=php_curl.dll` or `;extension=curl`
3. Remove the semicolon (`;`) to uncomment it:
   ```
   extension=php_curl.dll
   ```
4. Save the file
5. Restart Apache from XAMPP/WAMP control panel

## macOS (MAMP)

1. Open `php.ini` file from MAMP application
2. Find and uncomment:
   ```
   extension=curl.so
   ```
3. Restart MAMP servers

## Verify cURL is Enabled

Create a file `phpinfo.php` with:
```php
<?php phpinfo(); ?>
```

Visit it in browser and search for "curl" - you should see cURL information.

Or run from command line:
```bash
php -m | grep curl
```

You should see `curl` in the output.
