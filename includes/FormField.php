<?php
/**
 * FormField Model Class
 * Handles all form field operations
 */

require_once __DIR__ . '/Database.php';

class FormField {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Get all fields for a form
     */
    public function getFieldsByFormId($formId) {
        $sql = "SELECT * FROM form_fields WHERE form_id = :form_id ORDER BY field_order ASC";
        return $this->db->fetchAll($sql, array(':form_id' => $formId));
    }

    /**
     * Get field by ID
     */
    public function getFieldById($id) {
        $sql = "SELECT * FROM form_fields WHERE id = :id";
        return $this->db->fetch($sql, array(':id' => $id));
    }

    /**
     * Create new field
     */
    public function createField($data) {
        // Ensure field_order is set
        if (!isset($data['field_order']) || empty($data['field_order'])) {
            $data['field_order'] = $this->getNextFieldOrder($data['form_id']);
        }

        return $this->db->insert('form_fields', $data);
    }

    /**
     * Update field
     */
    public function updateField($id, $data) {
        return $this->db->update('form_fields', $data, 'id = :id', array(':id' => $id));
    }

    /**
     * Delete field
     */
    public function deleteField($id) {
        return $this->db->delete('form_fields', 'id = :id', array(':id' => $id));
    }

    /**
     * Get next field order number
     */
    private function getNextFieldOrder($formId) {
        $sql = "SELECT MAX(field_order) as max_order FROM form_fields WHERE form_id = :form_id";
        $result = $this->db->fetch($sql, array(':form_id' => $formId));
        return ($result && $result['max_order']) ? $result['max_order'] + 1 : 1;
    }

    /**
     * Reorder fields
     */
    public function reorderFields($formId, $fieldOrders) {
        $this->db->beginTransaction();
        try {
            foreach ($fieldOrders as $fieldId => $order) {
                $this->db->update('form_fields',
                    array('field_order' => $order),
                    'id = :id AND form_id = :form_id',
                    array(':id' => $fieldId, ':form_id' => $formId)
                );
            }
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    /**
     * Get available field types
     */
    public function getAvailableFieldTypes() {
        return array(
            'text' => 'Text Input',
            'email' => 'Email',
            'number' => 'Number',
            'textarea' => 'Text Area',
            'select' => 'Dropdown Select',
            'radio' => 'Radio Buttons',
            'checkbox' => 'Checkboxes',
            'date' => 'Date',
            'tel' => 'Telephone',
            'url' => 'URL'
        );
    }
}
