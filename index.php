<?php
/**
 * Dashboard - Main Page
 * List all forms and their statistics
 */

require_once 'config.php';
require_once 'includes/Form.php';

$formModel = new Form();
$forms = $formModel->getAllForms();

// Handle delete form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_form'])) {
    $formId = (int)$_POST['form_id'];
    $formModel->deleteForm($formId);
    header("Location: index.php?msg=deleted");
    exit;
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
            <h1><?php echo APP_NAME; ?></h1>
            <p class="subtitle">Design, manage, and track your forms</p>
        </header>

        <?php if ($message === 'deleted'): ?>
            <div class="message success">Form deleted successfully!</div>
        <?php endif; ?>

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
