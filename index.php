<?php
/**
 * Dashboard - Main Page
 * List all forms and their statistics
 */

require_once 'config.php';
require_once 'includes/Auth.php';
require_once 'includes/Form.php';

Auth::require_auth();

$formModel = new Form();
$forms = $formModel->getAllForms(Auth::id());

// Handle delete form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_form'])) {
    $formId = (int)$_POST['form_id'];
    $formModel->deleteForm($formId, Auth::id());
    header("Location: index.php?msg=deleted");
    exit;
}

// Calculate dashboard statistics
$totalForms = count($forms);
$totalSubmissions = 0;
$activeForms = 0;
$inactiveForms = 0;
$mostPopularForm = null;
$maxSubmissions = 0;
$recentFormCount = 0;
$thirtyDaysAgo = date('Y-m-d H:i:s', strtotime('-30 days'));

foreach ($forms as $form) {
    $submissionsCount = $formModel->getSubmissionsCount($form['id']);
    $totalSubmissions += $submissionsCount;

    if ($form['status'] === 'active') {
        $activeForms++;
    } else {
        $inactiveForms++;
    }

    // Find most popular form
    if ($submissionsCount > $maxSubmissions) {
        $maxSubmissions = $submissionsCount;
        $mostPopularForm = $form;
    }

    // Count recent forms (created in last 30 days)
    if ($form['created_at'] >= $thirtyDaysAgo) {
        $recentFormCount++;
    }
}

$message = isset($_GET['msg']) ? $_GET['msg'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Dashboard</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <header>
            <div class="header-content">
                <div>
                    <h1><?php echo APP_NAME; ?></h1>
                    <p class="subtitle">Design, manage, and track your forms</p>
                </div>
                <div class="user-info">
                    <?php if (Auth::picture()): ?>
                        <img src="<?php echo htmlspecialchars(Auth::picture()); ?>" alt="Profile" class="user-avatar">
                    <?php endif; ?>
                    <div class="user-details">
                        <strong><?php echo htmlspecialchars(Auth::name()); ?></strong>
                        <span><?php echo htmlspecialchars(Auth::email()); ?></span>
                    </div>
                </div>
            </div>
            <nav>
                <a href="index.php">Dashboard</a>
                <a href="profile.php">My Profile</a>
                <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
            </nav>
        </header>

        <?php if ($message === 'deleted'): ?>
            <div class="message success">Form deleted successfully!</div>
        <?php endif; ?>

        <!-- Dashboard Statistics -->
        <div class="stats-dashboard">
            <div class="stat-card stat-primary">
                <div class="stat-icon">📋</div>
                <div class="stat-content">
                    <h3>Total Forms</h3>
                    <p class="stat-number"><?php echo $totalForms; ?></p>
                    <small><?php echo $activeForms; ?> active, <?php echo $inactiveForms; ?> inactive</small>
                </div>
            </div>

            <div class="stat-card stat-success">
                <div class="stat-icon">📝</div>
                <div class="stat-content">
                    <h3>Total Entries</h3>
                    <p class="stat-number"><?php echo $totalSubmissions; ?></p>
                    <small>Across all forms</small>
                </div>
            </div>

            <div class="stat-card stat-info">
                <div class="stat-icon">⭐</div>
                <div class="stat-content">
                    <h3>Most Popular Form</h3>
                    <?php if ($mostPopularForm): ?>
                        <p class="stat-text"><?php echo htmlspecialchars($mostPopularForm['name']); ?></p>
                        <small><?php echo $maxSubmissions; ?> submissions</small>
                    <?php else: ?>
                        <p class="stat-text">No forms yet</p>
                        <small>Create your first form</small>
                    <?php endif; ?>
                </div>
            </div>

            <div class="stat-card stat-warning">
                <div class="stat-icon">🕒</div>
                <div class="stat-content">
                    <h3>Recent Activity</h3>
                    <p class="stat-number"><?php echo $recentFormCount; ?></p>
                    <small>Forms created in last 30 days</small>
                </div>
            </div>

            <?php if ($totalForms > 0): ?>
                <div class="stat-card stat-secondary">
                    <div class="stat-icon">📊</div>
                    <div class="stat-content">
                        <h3>Average Entries</h3>
                        <p class="stat-number"><?php echo $totalForms > 0 ? round($totalSubmissions / $totalForms, 1) : 0; ?></p>
                        <small>Per form</small>
                    </div>
                </div>

                <div class="stat-card stat-accent">
                    <div class="stat-icon">✅</div>
                    <div class="stat-content">
                        <h3>Active Rate</h3>
                        <p class="stat-number"><?php echo round(($activeForms / $totalForms) * 100); ?>%</p>
                        <small><?php echo $activeForms; ?> of <?php echo $totalForms; ?> forms active</small>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="dashboard-header">
            <h2>Your Forms</h2>
            <a href="form-builder.php" class="btn btn-primary">Create New Form</a>
        </div>

        <?php if (count($forms) > 0): ?>
            <div class="forms-grid">
                <?php foreach ($forms as $form):
                    $submissionsCount = $formModel->getSubmissionsCount($form['id']);
                ?>
                    <div class="form-card">
                        <div class="form-card-header">
                            <h3><?php echo htmlspecialchars($form['name']); ?></h3>
                            <span class="status-badge status-<?php echo $form['status']; ?>">
                                <?php echo ucfirst($form['status']); ?>
                            </span>
                        </div>

                        <?php if ($form['description']): ?>
                            <p class="form-description"><?php echo htmlspecialchars($form['description']); ?></p>
                        <?php endif; ?>

                        <div class="form-stats">
                            <div class="stat">
                                <span class="stat-label">Submissions:</span>
                                <span class="stat-value"><?php echo $submissionsCount; ?></span>
                            </div>
                            <div class="stat">
                                <span class="stat-label">Created:</span>
                                <span class="stat-value"><?php echo date('M d, Y', strtotime($form['created_at'])); ?></span>
                            </div>
                        </div>

                        <div class="form-actions">
                            <a href="form-builder.php?id=<?php echo $form['id']; ?>" class="btn btn-secondary btn-sm">
                                Edit Form
                            </a>
                            <a href="view-data.php?form_id=<?php echo $form['id']; ?>" class="btn btn-info btn-sm">
                                View Data (<?php echo $submissionsCount; ?>)
                            </a>
                            <a href="form-display.php?form_id=<?php echo $form['id']; ?>" class="btn btn-success btn-sm" target="_blank">
                                Preview
                            </a>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this form and all its data?');">
                                <input type="hidden" name="delete_form" value="1">
                                <input type="hidden" name="form_id" value="<?php echo $form['id']; ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-data">
                <h3>No forms yet</h3>
                <p>Create your first form to get started!</p>
                <a href="form-builder.php" class="btn btn-primary btn-large">Create Your First Form</a>
            </div>
        <?php endif; ?>

        <footer>
            <p><?php echo APP_NAME; ?> v<?php echo APP_VERSION; ?> | PHP <?php echo phpversion(); ?></p>
        </footer>
    </div>
</body>
</html>
