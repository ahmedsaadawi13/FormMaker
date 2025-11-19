<?php
/**
 * Database Migration Script
 * Updates existing database to latest schema
 */

require_once 'config.php';
require_once 'includes/Database.php';

$db = new Database();
$migrations = array();
$errors = array();

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Database Migration - FormMaker</title>
    <link rel='stylesheet' href='assets/style.css'>
</head>
<body>
    <div class='container'>
        <h1>Database Migration</h1>
        <p>This script will update your database to the latest schema.</p>
";

// Check if users table exists
try {
    $result = $db->query("SHOW TABLES LIKE 'users'");
    if ($result->rowCount() == 0) {
        echo "<p><strong>Creating users table...</strong></p>";

        $sql = "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            google_id VARCHAR(255) UNIQUE NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            name VARCHAR(255) NOT NULL,
            picture VARCHAR(500),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_login TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_email (email),
            INDEX idx_google_id (google_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $db->query($sql);
        $migrations[] = "Created users table";
        echo "<p style='color: green;'>✓ Users table created successfully</p>";
    } else {
        echo "<p>✓ Users table already exists</p>";
    }
} catch (Exception $e) {
    $errors[] = "Users table: " . $e->getMessage();
    echo "<p style='color: red;'>✗ Error with users table: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Check if user_profiles table exists
try {
    $result = $db->query("SHOW TABLES LIKE 'user_profiles'");
    if ($result->rowCount() == 0) {
        echo "<p><strong>Creating user_profiles table...</strong></p>";

        $sql = "CREATE TABLE IF NOT EXISTS user_profiles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            phone VARCHAR(50),
            company VARCHAR(255),
            website VARCHAR(255),
            bio TEXT,
            address TEXT,
            city VARCHAR(100),
            country VARCHAR(100),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_user (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $db->query($sql);
        $migrations[] = "Created user_profiles table";
        echo "<p style='color: green;'>✓ User profiles table created successfully</p>";
    } else {
        echo "<p>✓ User profiles table already exists</p>";
    }
} catch (Exception $e) {
    $errors[] = "User profiles table: " . $e->getMessage();
    echo "<p style='color: red;'>✗ Error with user_profiles table: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Check if forms table has user_id column
try {
    $result = $db->query("SHOW COLUMNS FROM forms LIKE 'user_id'");
    if ($result->rowCount() == 0) {
        echo "<p><strong>Adding user_id column to forms table...</strong></p>";

        // First, create a default user if none exists
        $userCheck = $db->query("SELECT COUNT(*) as count FROM users");
        $userCount = $userCheck->fetch();

        if ($userCount['count'] == 0) {
            echo "<p><strong>Creating default user...</strong></p>";
            $db->query("INSERT INTO users (google_id, email, name, picture) VALUES ('default_user', 'admin@example.com', 'Default User', '')");
            $defaultUserId = $db->getConnection()->lastInsertId();
            $db->query("INSERT INTO user_profiles (user_id) VALUES ($defaultUserId)");
            echo "<p style='color: green;'>✓ Default user created (ID: $defaultUserId)</p>";
        } else {
            // Get first user ID
            $firstUser = $db->query("SELECT id FROM users LIMIT 1")->fetch();
            $defaultUserId = $firstUser['id'];
        }

        // Add user_id column
        $db->query("ALTER TABLE forms ADD COLUMN user_id INT NOT NULL DEFAULT $defaultUserId AFTER id");

        // Add foreign key
        $db->query("ALTER TABLE forms ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE");

        // Add index
        $db->query("ALTER TABLE forms ADD INDEX idx_user (user_id)");

        $migrations[] = "Added user_id column to forms table";
        echo "<p style='color: green;'>✓ Forms table updated with user_id column</p>";
        echo "<p style='color: orange;'>⚠ All existing forms have been assigned to user ID: $defaultUserId</p>";
    } else {
        echo "<p>✓ Forms table already has user_id column</p>";
    }
} catch (Exception $e) {
    $errors[] = "Forms table user_id column: " . $e->getMessage();
    echo "<p style='color: red;'>✗ Error updating forms table: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Summary
echo "<hr style='margin: 30px 0;'>";
echo "<h2>Migration Summary</h2>";

if (count($migrations) > 0) {
    echo "<div class='message success'>";
    echo "<strong>Migrations Applied:</strong>";
    echo "<ul>";
    foreach ($migrations as $migration) {
        echo "<li>" . htmlspecialchars($migration) . "</li>";
    }
    echo "</ul>";
    echo "</div>";
} else {
    echo "<div class='message success'>";
    echo "<strong>✓ Database is already up to date!</strong>";
    echo "</div>";
}

if (count($errors) > 0) {
    echo "<div class='message error'>";
    echo "<strong>Errors:</strong>";
    echo "<ul>";
    foreach ($errors as $error) {
        echo "<li>" . htmlspecialchars($error) . "</li>";
    }
    echo "</ul>";
    echo "</div>";
}

echo "<div style='margin-top: 30px; text-align: center;'>";
echo "<a href='index.php' class='btn btn-primary'>Go to Dashboard</a> ";
echo "<a href='check-requirements.php' class='btn btn-secondary'>Check Requirements</a>";
echo "</div>";

echo "    </div>
</body>
</html>";
?>
