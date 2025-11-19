<?php
/**
 * Form Builder Interface
 * Create and edit forms with fields
 */

require_once 'config.php';
require_once 'includes/Auth.php';
require_once 'includes/Form.php';
require_once 'includes/FormField.php';

Auth::require_auth();

$formModel = new Form();
$fieldModel = new FormField();

$formId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$form = null;
$fields = array();

if ($formId > 0) {
    $form = $formModel->getFormById($formId, Auth::id());
    if (!$form) {
        die("Form not found or you don't have permission to edit it.");
    }
    $fields = $fieldModel->getFieldsByFormId($formId);
}

// Handle form save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'save_form') {
        $name = $_POST['form_name'];
        $description = $_POST['form_description'];
        $status = $_POST['form_status'];

        if ($formId > 0) {
            $formModel->updateForm($formId, $name, $description, $status);
            $message = "Form updated successfully!";
        } else {
            $newFormId = $formModel->createForm(Auth::id(), $name, $description, $status);
            if ($newFormId) {
                header("Location: form-builder.php?id=$newFormId&msg=created");
                exit;
            }
        }
    } elseif ($action === 'add_field') {
        $fieldData = array(
            'form_id' => $formId,
            'field_name' => $_POST['field_name'],
            'field_label' => $_POST['field_label'],
            'field_type' => $_POST['field_type'],
            'field_options' => $_POST['field_options'],
            'is_required' => isset($_POST['is_required']) ? 1 : 0,
            'default_value' => $_POST['default_value'],
            'validation_pattern' => $_POST['validation_pattern'],
            'placeholder' => $_POST['placeholder'],
            'help_text' => $_POST['help_text']
        );

        $fieldModel->createField($fieldData);
        header("Location: form-builder.php?id=$formId&msg=field_added");
        exit;
    } elseif ($action === 'delete_field') {
        $fieldId = (int)$_POST['field_id'];
        $fieldModel->deleteField($fieldId);
        header("Location: form-builder.php?id=$formId&msg=field_deleted");
        exit;
    }
}

$availableTypes = $fieldModel->getAvailableFieldTypes();
$message = isset($_GET['msg']) ? $_GET['msg'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Builder - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <header>
            <div class="header-content">
                <h1><?php echo APP_NAME; ?></h1>
                <div class="user-info">
                    <?php if (Auth::picture()): ?>
                        <img src="<?php echo htmlspecialchars(Auth::picture()); ?>" alt="Profile" class="user-avatar">
                    <?php endif; ?>
                    <span><?php echo htmlspecialchars(Auth::name()); ?></span>
                </div>
            </div>
            <nav>
                <a href="index.php">Dashboard</a>
                <?php if ($formId > 0): ?>
                    <a href="view-data.php?form_id=<?php echo $formId; ?>">View Data</a>
                    <a href="form-display.php?form_id=<?php echo $formId; ?>" target="_blank">Preview Form</a>
                <?php endif; ?>
                <a href="profile.php">My Profile</a>
                <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
            </nav>
        </header>

        <?php if ($message): ?>
            <div class="message success">
                <?php
                switch ($message) {
                    case 'created': echo 'Form created successfully!'; break;
                    case 'field_added': echo 'Field added successfully!'; break;
                    case 'field_deleted': echo 'Field deleted successfully!'; break;
                }
                ?>
            </div>
        <?php endif; ?>

        <div class="form-builder">
            <!-- Form Settings -->
            <div class="panel">
                <h2><?php echo $formId > 0 ? 'Edit Form' : 'Create New Form'; ?></h2>
                <form method="POST" class="form-settings">
                    <input type="hidden" name="action" value="save_form">

                    <div class="form-group">
                        <label for="form_name">Form Name *</label>
                        <input type="text" id="form_name" name="form_name"
                               value="<?php echo $form ? htmlspecialchars($form['name']) : ''; ?>"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="form_description">Description</label>
                        <textarea id="form_description" name="form_description" rows="3"><?php echo $form ? htmlspecialchars($form['description']) : ''; ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="form_status">Status</label>
                        <select id="form_status" name="form_status">
                            <option value="active" <?php echo ($form && $form['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo ($form && $form['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <?php echo $formId > 0 ? 'Update Form' : 'Create Form'; ?>
                    </button>
                </form>
            </div>

            <?php if ($formId > 0): ?>
                <!-- Form Fields -->
                <div class="panel">
                    <h2>Form Fields</h2>

                    <?php if (count($fields) > 0): ?>
                        <div class="fields-list">
                            <?php foreach ($fields as $field): ?>
                                <div class="field-item">
                                    <div class="field-header">
                                        <strong><?php echo htmlspecialchars($field['field_label']); ?></strong>
                                        <span class="field-type"><?php echo $availableTypes[$field['field_type']]; ?></span>
                                        <?php if ($field['is_required']): ?>
                                            <span class="required-badge">Required</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="field-details">
                                        <p><strong>Name:</strong> <?php echo htmlspecialchars($field['field_name']); ?></p>
                                        <?php if ($field['placeholder']): ?>
                                            <p><strong>Placeholder:</strong> <?php echo htmlspecialchars($field['placeholder']); ?></p>
                                        <?php endif; ?>
                                        <?php if ($field['default_value']): ?>
                                            <p><strong>Default Value:</strong> <?php echo htmlspecialchars($field['default_value']); ?></p>
                                        <?php endif; ?>
                                        <?php if ($field['validation_pattern']): ?>
                                            <p><strong>Validation Pattern:</strong> <code><?php echo htmlspecialchars($field['validation_pattern']); ?></code></p>
                                        <?php endif; ?>
                                        <?php if ($field['field_options']): ?>
                                            <p><strong>Options:</strong> <?php echo htmlspecialchars($field['field_options']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="field-actions">
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this field?');">
                                            <input type="hidden" name="action" value="delete_field">
                                            <input type="hidden" name="field_id" value="<?php echo $field['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="no-data">No fields added yet. Add your first field below.</p>
                    <?php endif; ?>
                </div>

                <!-- Add Field Form -->
                <div class="panel">
                    <h2>Add New Field</h2>
                    <form method="POST" class="add-field-form" id="addFieldForm">
                        <input type="hidden" name="action" value="add_field">

                        <div class="form-row">
                            <div class="form-group">
                                <label for="field_name">Field Name * (no spaces)</label>
                                <input type="text" id="field_name" name="field_name" required
                                       pattern="[a-zA-Z0-9_]+" title="Only letters, numbers, and underscores">
                            </div>

                            <div class="form-group">
                                <label for="field_label">Field Label *</label>
                                <input type="text" id="field_label" name="field_label" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="field_type">Field Type *</label>
                                <select id="field_type" name="field_type" required>
                                    <?php foreach ($availableTypes as $type => $label): ?>
                                        <option value="<?php echo $type; ?>"><?php echo $label; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="is_required">
                                    <input type="checkbox" id="is_required" name="is_required" value="1">
                                    Required Field
                                </label>
                            </div>
                        </div>

                        <div class="form-group" id="optionsGroup" style="display:none;">
                            <label for="field_options">Options (comma-separated for select/radio/checkbox)</label>
                            <input type="text" id="field_options" name="field_options"
                                   placeholder="Option 1, Option 2, Option 3">
                            <small>Example: Red, Green, Blue</small>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="placeholder">Placeholder</label>
                                <input type="text" id="placeholder" name="placeholder">
                            </div>

                            <div class="form-group">
                                <label for="default_value">Default Value</label>
                                <input type="text" id="default_value" name="default_value">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="validation_pattern">Validation Pattern (regex)</label>
                            <input type="text" id="validation_pattern" name="validation_pattern"
                                   placeholder="e.g., ^[0-9]{10}$ for 10-digit phone">
                            <small>Leave empty for no custom validation</small>
                        </div>

                        <div class="form-group">
                            <label for="help_text">Help Text</label>
                            <textarea id="help_text" name="help_text" rows="2"></textarea>
                        </div>

                        <button type="submit" class="btn btn-success">Add Field</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Show/hide options field based on field type
        document.getElementById('field_type').addEventListener('change', function() {
            const optionsGroup = document.getElementById('optionsGroup');
            const needsOptions = ['select', 'radio', 'checkbox'].indexOf(this.value) !== -1;
            optionsGroup.style.display = needsOptions ? 'block' : 'none';
        });

        // Auto-generate field name from label
        document.getElementById('field_label').addEventListener('blur', function() {
            const nameField = document.getElementById('field_name');
            if (!nameField.value) {
                nameField.value = this.value.toLowerCase()
                    .replace(/[^a-z0-9]+/g, '_')
                    .replace(/^_|_$/g, '');
            }
        });
    </script>
</body>
</html>
