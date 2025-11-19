<?php
/**
 * User Profile Page
 * Manage user identity and profile information
 */

require_once 'config.php';
require_once 'includes/Auth.php';
require_once 'includes/User.php';

Auth::require_auth();

$userModel = new User();
$user = Auth::user();
$profile = $userModel->getProfile(Auth::id());

// Handle profile update
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $profileData = array(
        'phone' => $_POST['phone'],
        'company' => $_POST['company'],
        'website' => $_POST['website'],
        'bio' => $_POST['bio'],
        'address' => $_POST['address'],
        'city' => $_POST['city'],
        'country' => $_POST['country']
    );

    if ($userModel->updateProfile(Auth::id(), $profileData)) {
        $message = 'Profile updated successfully!';
        $profile = $userModel->getProfile(Auth::id());
    } else {
        $error = 'Failed to update profile. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1><?php echo APP_NAME; ?></h1>
            <nav>
                <a href="index.php">Dashboard</a>
                <a href="profile.php">My Profile</a>
                <a href="logout.php">Logout</a>
            </nav>
        </header>

        <?php if ($message): ?>
            <div class="message success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="profile-container">
            <div class="panel">
                <h2>My Identity</h2>

                <div class="profile-header">
                    <?php if ($user['picture']): ?>
                        <img src="<?php echo htmlspecialchars($user['picture']); ?>" alt="Profile Picture" class="profile-picture">
                    <?php endif; ?>
                    <div class="profile-info">
                        <h3><?php echo htmlspecialchars($user['name']); ?></h3>
                        <p><?php echo htmlspecialchars($user['email']); ?></p>
                        <p class="profile-meta">
                            Member since: <?php echo date('F Y', strtotime($user['created_at'])); ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="panel">
                <h2>Profile Information</h2>
                <p class="help-text">Add your identity information to personalize your account</p>

                <form method="POST">
                    <input type="hidden" name="update_profile" value="1">

                    <div class="form-row">
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone"
                                   value="<?php echo $profile ? htmlspecialchars($profile['phone']) : ''; ?>"
                                   placeholder="+1 (555) 123-4567">
                        </div>

                        <div class="form-group">
                            <label for="company">Company</label>
                            <input type="text" id="company" name="company"
                                   value="<?php echo $profile ? htmlspecialchars($profile['company']) : ''; ?>"
                                   placeholder="Your company name">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="website">Website</label>
                        <input type="url" id="website" name="website"
                               value="<?php echo $profile ? htmlspecialchars($profile['website']) : ''; ?>"
                               placeholder="https://example.com">
                    </div>

                    <div class="form-group">
                        <label for="bio">Bio</label>
                        <textarea id="bio" name="bio" rows="4"
                                  placeholder="Tell us about yourself..."><?php echo $profile ? htmlspecialchars($profile['bio']) : ''; ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="address">Address</label>
                        <input type="text" id="address" name="address"
                               value="<?php echo $profile ? htmlspecialchars($profile['address']) : ''; ?>"
                               placeholder="Street address">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="city">City</label>
                            <input type="text" id="city" name="city"
                                   value="<?php echo $profile ? htmlspecialchars($profile['city']) : ''; ?>"
                                   placeholder="Your city">
                        </div>

                        <div class="form-group">
                            <label for="country">Country</label>
                            <input type="text" id="country" name="country"
                                   value="<?php echo $profile ? htmlspecialchars($profile['country']) : ''; ?>"
                                   placeholder="Your country">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </form>
            </div>

            <div class="panel">
                <h2>Account Information</h2>
                <div class="account-info">
                    <div class="info-row">
                        <strong>Google ID:</strong>
                        <span><?php echo htmlspecialchars($user['google_id']); ?></span>
                    </div>
                    <div class="info-row">
                        <strong>Last Login:</strong>
                        <span><?php echo date('F d, Y H:i', strtotime($user['last_login'])); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .profile-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .profile-header {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 8px;
            color: white;
        }

        .profile-picture {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 4px solid white;
        }

        .profile-info h3 {
            margin: 0;
            font-size: 1.8em;
        }

        .profile-info p {
            margin: 5px 0;
            opacity: 0.9;
        }

        .profile-meta {
            font-size: 0.9em;
        }

        .account-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e0e0e0;
        }

        .info-row:last-child {
            border-bottom: none;
        }
    </style>
</body>
</html>
