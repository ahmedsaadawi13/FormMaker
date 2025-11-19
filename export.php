<?php
/**
 * Export Form Submissions
 * Export data in CSV or JSON format
 */

require_once 'config.php';
require_once 'includes/Form.php';
require_once 'includes/FormField.php';
require_once 'includes/FormSubmission.php';

$formModel = new Form();
$fieldModel = new FormField();
$submissionModel = new FormSubmission();

$formId = isset($_GET['form_id']) ? (int)$_GET['form_id'] : 0;
$format = isset($_GET['format']) ? $_GET['format'] : 'csv';

if ($formId <= 0) {
    die("Invalid form ID.");
}

$form = $formModel->getFormById($formId);
if (!$form) {
    die("Form not found.");
}

$fields = $fieldModel->getFieldsByFormId($formId);
$submissions = $submissionModel->getFormSubmissionsWithData($formId);

// Generate filename
$filename = 'form_' . $formId . '_' . date('Y-m-d_H-i-s');

if ($format === 'csv') {
    // Export as CSV
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');

    $output = fopen('php://output', 'w');

    // Write header row
    $headers = array('ID', 'Submitted At', 'IP Address');
    foreach ($fields as $field) {
        $headers[] = $field['field_label'];
    }
    fputcsv($output, $headers);

    // Write data rows
    foreach ($submissions as $submission) {
        $row = array(
            $submission['id'],
            $submission['submitted_at'],
            $submission['ip_address']
        );

        // Create a map of field_id => value
        $dataMap = array();
        foreach ($submission['data'] as $data) {
            $dataMap[$data['field_id']] = $data['field_value'];
        }

        // Add field values in order
        foreach ($fields as $field) {
            $value = isset($dataMap[$field['id']]) ? $dataMap[$field['id']] : '';

            // Handle JSON values (checkboxes)
            if ($value && $value[0] === '[') {
                $decoded = json_decode($value, true);
                if (is_array($decoded)) {
                    $value = implode(', ', $decoded);
                }
            }

            $row[] = $value;
        }

        fputcsv($output, $row);
    }

    fclose($output);
    exit;

} elseif ($format === 'json') {
    // Export as JSON
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '.json"');

    $exportData = array(
        'form' => array(
            'id' => $form['id'],
            'name' => $form['name'],
            'description' => $form['description'],
            'exported_at' => date('Y-m-d H:i:s')
        ),
        'fields' => array(),
        'submissions' => array()
    );

    // Add fields info
    foreach ($fields as $field) {
        $exportData['fields'][] = array(
            'id' => $field['id'],
            'name' => $field['field_name'],
            'label' => $field['field_label'],
            'type' => $field['field_type']
        );
    }

    // Add submissions
    foreach ($submissions as $submission) {
        $submissionData = array(
            'id' => $submission['id'],
            'submitted_at' => $submission['submitted_at'],
            'ip_address' => $submission['ip_address'],
            'data' => array()
        );

        // Add field data
        foreach ($submission['data'] as $data) {
            $value = $data['field_value'];

            // Decode JSON values
            if ($value && $value[0] === '[') {
                $decoded = json_decode($value, true);
                if (is_array($decoded)) {
                    $value = $decoded;
                }
            }

            $submissionData['data'][$data['field_name']] = $value;
        }

        $exportData['submissions'][] = $submissionData;
    }

    echo json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;

} else {
    die("Invalid export format. Use 'csv' or 'json'.");
}
