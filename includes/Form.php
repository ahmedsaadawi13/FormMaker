<?php
/**
 * Form Model Class
 * Handles all form-related operations
 */

require_once __DIR__ . '/Database.php';

class Form {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Get all forms for a user
     */
    public function getAllForms($userId = null) {
        if ($userId) {
            $sql = "SELECT * FROM forms WHERE user_id = :user_id ORDER BY created_at DESC";
            return $this->db->fetchAll($sql, array(':user_id' => $userId));
        } else {
            $sql = "SELECT * FROM forms ORDER BY created_at DESC";
            return $this->db->fetchAll($sql);
        }
    }

    /**
     * Get form by ID
     */
    public function getFormById($id, $userId = null) {
        if ($userId) {
            $sql = "SELECT * FROM forms WHERE id = :id AND user_id = :user_id";
            return $this->db->fetch($sql, array(':id' => $id, ':user_id' => $userId));
        } else {
            $sql = "SELECT * FROM forms WHERE id = :id";
            return $this->db->fetch($sql, array(':id' => $id));
        }
    }

    /**
     * Create new form
     */
    public function createForm($userId, $name, $description = '', $status = 'active') {
        $data = array(
            'user_id' => $userId,
            'name' => $name,
            'description' => $description,
            'status' => $status
        );
        return $this->db->insert('forms', $data);
    }

    /**
     * Update form
     */
    public function updateForm($id, $name, $description = '', $status = 'active') {
        $data = array(
            'name' => $name,
            'description' => $description,
            'status' => $status
        );
        return $this->db->update('forms', $data, 'id = :id', array(':id' => $id));
    }

    /**
     * Delete form
     */
    public function deleteForm($id, $userId = null) {
        if ($userId) {
            return $this->db->delete('forms', 'id = :id AND user_id = :user_id', array(':id' => $id, ':user_id' => $userId));
        } else {
            return $this->db->delete('forms', 'id = :id', array(':id' => $id));
        }
    }

    /**
     * Check if user owns form
     */
    public function userOwnsForm($formId, $userId) {
        $sql = "SELECT COUNT(*) as count FROM forms WHERE id = :id AND user_id = :user_id";
        $result = $this->db->fetch($sql, array(':id' => $formId, ':user_id' => $userId));
        return $result && $result['count'] > 0;
    }

    /**
     * Get form with all fields
     */
    public function getFormWithFields($id) {
        $form = $this->getFormById($id);
        if ($form) {
            $sql = "SELECT * FROM form_fields WHERE form_id = :form_id ORDER BY field_order ASC";
            $form['fields'] = $this->db->fetchAll($sql, array(':form_id' => $id));
        }
        return $form;
    }

    /**
     * Get form submissions count
     */
    public function getSubmissionsCount($formId) {
        $sql = "SELECT COUNT(*) as count FROM form_submissions WHERE form_id = :form_id";
        $result = $this->db->fetch($sql, array(':form_id' => $formId));
        return $result ? $result['count'] : 0;
    }

    /**
     * Get last database error
     */
    public function getError() {
        return $this->db->getError();
    }
}
