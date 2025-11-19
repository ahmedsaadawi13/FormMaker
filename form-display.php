<?php
/**
 * Form Display Page
 * Renders forms for users to fill out and submit
 */

require_once 'config.php';
require_once 'includes/Form.php';
require_once 'includes/FormField.php';
require_once 'includes/FormSubmission.php';

$formModel = new Form();
$fieldModel = new FormField();
$submissionModel = new FormSubmission();

$formId = isset($_GET['form_id']) ? (int)$_GET['form_id'] : 0;
$form = null;
$fields = array();
$errors = array();
$success = false;

if ($formId > 0) {
    $form = $formModel->getFormById($formId);
    if ($form && $form['status'] === 'active') {
        $fields = $fieldModel->getFieldsByFormId($formId);
    } else {
        die("Form not found or is inactive.");
    }
} else {
    die("Invalid form ID.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_form'])) {
    $fieldData = array();
    $validationErrors = array();

    // Validate and collect field data
    foreach ($fields as $field) {
        $fieldName = 'field_' . $field['id'];
        $value = isset($_POST[$fieldName]) ? $_POST[$fieldName] : '';

        // Validate field
        $fieldErrors = $submissionModel->validateField($field, $value);
        if (!empty($fieldErrors)) {
            $validationErrors = array_merge($validationErrors, $fieldErrors);
        }

        $fieldData[$field['id']] = $value;
    }

    if (empty($validationErrors)) {
        $submissionId = $submissionModel->submitForm($formId, $fieldData);
        if ($submissionId) {
            $success = true;
        } else {
            $errors[] = "Failed to submit form. Please try again.";
        }
    } else {
        $errors = $validationErrors;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($form['name']); ?> - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <div class="form-display">
            <?php if ($success): ?>
                <div class="message success">
                    <h2>Thank you!</h2>
                    <p>Your form has been submitted successfully.</p>
                    <a href="form-display.php?form_id=<?php echo $formId; ?>" class="btn">Submit Another Response</a>
                </div>
            <?php else: ?>
                <h1><?php echo htmlspecialchars($form['name']); ?></h1>

                <?php if ($form['description']): ?>
                    <p class="form-description"><?php echo nl2br(htmlspecialchars($form['description'])); ?></p>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="message error">
                        <strong>Please correct the following errors:</strong>
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" class="form-container">
                    <input type="hidden" name="submit_form" value="1">

                    <?php foreach ($fields as $field): ?>
                        <div class="form-group">
                            <label for="field_<?php echo $field['id']; ?>">
                                <?php echo htmlspecialchars($field['field_label']); ?>
                                <?php if ($field['is_required']): ?>
                                    <span class="required">*</span>
                                <?php endif; ?>
                            </label>

                            <?php
                            $fieldName = 'field_' . $field['id'];
                            $value = isset($_POST[$fieldName]) ? $_POST[$fieldName] : $field['default_value'];
                            $required = $field['is_required'] ? 'required' : '';

                            switch ($field['field_type']):
                                case 'textarea':
                            ?>
                                    <textarea
                                        id="field_<?php echo $field['id']; ?>"
                                        name="<?php echo $fieldName; ?>"
                                        placeholder="<?php echo htmlspecialchars($field['placeholder']); ?>"
                                        <?php echo $required; ?>
                                        rows="5"><?php echo htmlspecialchars($value); ?></textarea>
                            <?php
                                    break;

                                case 'select':
                                    $options = explode(',', $field['field_options']);
                            ?>
                                    <select
                                        id="field_<?php echo $field['id']; ?>"
                                        name="<?php echo $fieldName; ?>"
                                        <?php echo $required; ?>>
                                        <option value="">-- Select --</option>
                                        <?php foreach ($options as $option): ?>
                                            <option value="<?php echo htmlspecialchars(trim($option)); ?>"
                                                <?php echo ($value === trim($option)) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars(trim($option)); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                            <?php
                                    break;

                                case 'radio':
                                    $options = explode(',', $field['field_options']);
                            ?>
                                    <div class="radio-group">
                                        <?php foreach ($options as $option): ?>
                                            <label class="radio-label">
                                                <input type="radio"
                                                       name="<?php echo $fieldName; ?>"
                                                       value="<?php echo htmlspecialchars(trim($option)); ?>"
                                                       <?php echo ($value === trim($option)) ? 'checked' : ''; ?>
                                                       <?php echo $required; ?>>
                                                <?php echo htmlspecialchars(trim($option)); ?>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                            <?php
                                    break;

                                case 'checkbox':
                                    $options = explode(',', $field['field_options']);
                                    $checkedValues = is_array($value) ? $value : array();
                            ?>
                                    <div class="checkbox-group">
                                        <?php foreach ($options as $option): ?>
                                            <label class="checkbox-label">
                                                <input type="checkbox"
                                                       name="<?php echo $fieldName; ?>[]"
                                                       value="<?php echo htmlspecialchars(trim($option)); ?>"
                                                       <?php echo in_array(trim($option), $checkedValues) ? 'checked' : ''; ?>>
                                                <?php echo htmlspecialchars(trim($option)); ?>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                            <?php
                                    break;

                                default:
                                    // text, email, number, date, tel, url
                            ?>
                                    <input
                                        type="<?php echo $field['field_type']; ?>"
                                        id="field_<?php echo $field['id']; ?>"
                                        name="<?php echo $fieldName; ?>"
                                        value="<?php echo htmlspecialchars($value); ?>"
                                        placeholder="<?php echo htmlspecialchars($field['placeholder']); ?>"
                                        <?php echo $required; ?>
                                        <?php if ($field['validation_pattern']): ?>
                                            pattern="<?php echo htmlspecialchars($field['validation_pattern']); ?>"
                                        <?php endif; ?>>
                            <?php
                                    break;
                            endswitch;
                            ?>

                            <?php if ($field['help_text']): ?>
                                <small class="help-text"><?php echo htmlspecialchars($field['help_text']); ?></small>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-large">Submit Form</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
