<?php
/**
 * FormSubmission Model Class
 * Handles form submission operations
 */

require_once __DIR__ . '/Database.php';

class FormSubmission {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Submit form data
     */
    public function submitForm($formId, $fieldData) {
        $this->db->beginTransaction();

        try {
            // Insert submission metadata
            $submissionData = array(
                'form_id' => $formId,
                'ip_address' => $_SERVER['REMOTE_ADDR'],
                'user_agent' => $_SERVER['HTTP_USER_AGENT']
            );

            $submissionId = $this->db->insert('form_submissions', $submissionData);

            if (!$submissionId) {
                throw new Exception("Failed to create submission");
            }

            // Insert field values
            foreach ($fieldData as $fieldId => $value) {
                // Handle array values (checkboxes)
                if (is_array($value)) {
                    $value = json_encode($value);
                }

                $data = array(
                    'submission_id' => $submissionId,
                    'field_id' => $fieldId,
                    'field_value' => $value
                );

                if (!$this->db->insert('submission_data', $data)) {
                    throw new Exception("Failed to save field data");
                }
            }

            $this->db->commit();
            return $submissionId;

        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    /**
     * Get all submissions for a form
     */
    public function getFormSubmissions($formId, $limit = 100, $offset = 0) {
        $sql = "SELECT * FROM form_submissions
                WHERE form_id = :form_id
                ORDER BY submitted_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':form_id', $formId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get submission by ID with all data
     */
    public function getSubmissionById($id) {
        $sql = "SELECT * FROM form_submissions WHERE id = :id";
        $submission = $this->db->fetch($sql, array(':id' => $id));

        if ($submission) {
            $sql = "SELECT sd.*, ff.field_name, ff.field_label, ff.field_type
                    FROM submission_data sd
                    JOIN form_fields ff ON sd.field_id = ff.id
                    WHERE sd.submission_id = :submission_id
                    ORDER BY ff.field_order";

            $submission['data'] = $this->db->fetchAll($sql, array(':submission_id' => $id));
        }

        return $submission;
    }

    /**
     * Get all submissions with data for a form
     */
    public function getFormSubmissionsWithData($formId) {
        $submissions = $this->getFormSubmissions($formId, 1000, 0);

        foreach ($submissions as &$submission) {
            $sql = "SELECT sd.*, ff.field_name, ff.field_label, ff.field_type
                    FROM submission_data sd
                    JOIN form_fields ff ON sd.field_id = ff.id
                    WHERE sd.submission_id = :submission_id
                    ORDER BY ff.field_order";

            $submission['data'] = $this->db->fetchAll($sql, array(':submission_id' => $submission['id']));
        }

        return $submissions;
    }

    /**
     * Delete submission
     */
    public function deleteSubmission($id) {
        return $this->db->delete('form_submissions', 'id = :id', array(':id' => $id));
    }

    /**
     * Get submissions count for a form
     */
    public function getSubmissionsCount($formId) {
        $sql = "SELECT COUNT(*) as count FROM form_submissions WHERE form_id = :form_id";
        $result = $this->db->fetch($sql, array(':form_id' => $formId));
        return $result ? $result['count'] : 0;
    }

    /**
     * Validate field value
     */
    public function validateField($field, $value) {
        $errors = array();

        // Check required
        if ($field['is_required'] && empty($value)) {
            $errors[] = "{$field['field_label']} is required";
        }

        // Skip further validation if empty and not required
        if (empty($value) && !$field['is_required']) {
            return $errors;
        }

        // Type-specific validation
        switch ($field['field_type']) {
            case 'email':
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = "{$field['field_label']} must be a valid email address";
                }
                break;

            case 'url':
                if (!filter_var($value, FILTER_VALIDATE_URL)) {
                    $errors[] = "{$field['field_label']} must be a valid URL";
                }
                break;

            case 'number':
                if (!is_numeric($value)) {
                    $errors[] = "{$field['field_label']} must be a number";
                }
                break;
        }

        // Custom pattern validation
        if (!empty($field['validation_pattern']) && !empty($value)) {
            if (!preg_match('/' . $field['validation_pattern'] . '/', $value)) {
                $errors[] = "{$field['field_label']} format is invalid";
            }
        }

        return $errors;
    }
}
