<?php
/**
 * View Form Submissions
 * Display and export form submission data
 */

require_once 'config.php';
require_once 'includes/Auth.php';
require_once 'includes/Form.php';
require_once 'includes/FormField.php';
require_once 'includes/FormSubmission.php';

Auth::require_auth();

$formModel = new Form();
$fieldModel = new FormField();
$submissionModel = new FormSubmission();

$formId = isset($_GET['form_id']) ? (int)$_GET['form_id'] : 0;
$form = null;
$fields = array();
$submissions = array();

if ($formId > 0) {
    $form = $formModel->getFormById($formId, Auth::id());
    if (!$form) {
        die("Form not found or you don't have permission to view it.");
    }
    $fields = $fieldModel->getFieldsByFormId($formId);
    $submissions = $submissionModel->getFormSubmissionsWithData($formId);
} else {
    die("Invalid form ID.");
}

// Handle delete submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_submission'])) {
    $submissionId = (int)$_POST['submission_id'];
    $submissionModel->deleteSubmission($submissionId);
    header("Location: view-data.php?form_id=$formId&msg=deleted");
    exit;
}

$message = isset($_GET['msg']) ? $_GET['msg'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Data - <?php echo htmlspecialchars($form['name']); ?></title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <header>
            <div class="header-content">
                <h1>Form Submissions: <?php echo htmlspecialchars($form['name']); ?></h1>
                <div class="user-info">
                    <?php if (Auth::picture()): ?>
                        <img src="<?php echo htmlspecialchars(Auth::picture()); ?>" alt="Profile" class="user-avatar">
                    <?php endif; ?>
                    <span><?php echo htmlspecialchars(Auth::name()); ?></span>
                </div>
            </div>
            <nav>
                <a href="index.php">Dashboard</a>
                <a href="form-builder.php?id=<?php echo $formId; ?>">Edit Form</a>
                <a href="form-display.php?form_id=<?php echo $formId; ?>" target="_blank">View Form</a>
                <a href="export.php?form_id=<?php echo $formId; ?>&format=csv" class="btn btn-success">Export CSV</a>
                <a href="export.php?form_id=<?php echo $formId; ?>&format=json" class="btn btn-success">Export JSON</a>
                <a href="profile.php">My Profile</a>
                <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
            </nav>
        </header>

        <?php if ($message === 'deleted'): ?>
            <div class="message success">Submission deleted successfully!</div>
        <?php endif; ?>

        <div class="stats">
            <div class="stat-card">
                <h3>Total Submissions</h3>
                <p class="stat-number"><?php echo count($submissions); ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Fields</h3>
                <p class="stat-number"><?php echo count($fields); ?></p>
            </div>
        </div>

        <?php if (count($submissions) > 0): ?>
            <div class="data-table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Submitted At</th>
                            <?php foreach ($fields as $field): ?>
                                <th><?php echo htmlspecialchars($field['field_label']); ?></th>
                            <?php endforeach; ?>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($submissions as $submission): ?>
                            <tr>
                                <td><?php echo $submission['id']; ?></td>
                                <td><?php echo date('Y-m-d H:i', strtotime($submission['submitted_at'])); ?></td>
                                <?php
                                // Create a map of field_id => value
                                $dataMap = array();
                                foreach ($submission['data'] as $data) {
                                    $dataMap[$data['field_id']] = $data['field_value'];
                                }

                                // Display values in the same order as fields
                                foreach ($fields as $field):
                                    $value = isset($dataMap[$field['id']]) ? $dataMap[$field['id']] : '';

                                    // Handle JSON values (checkboxes)
                                    if ($value && $value[0] === '[') {
                                        $decoded = json_decode($value, true);
                                        if (is_array($decoded)) {
                                            $value = implode(', ', $decoded);
                                        }
                                    }
                                ?>
                                    <td><?php echo htmlspecialchars($value ?? ''); ?></td>
                                <?php endforeach; ?>
                                <td>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this submission?');">
                                        <input type="hidden" name="delete_submission" value="1">
                                        <input type="hidden" name="submission_id" value="<?php echo $submission['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="no-data">
                <p>No submissions yet.</p>
                <a href="form-display.php?form_id=<?php echo $formId; ?>" class="btn btn-primary">View Form</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
