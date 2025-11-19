<?php
/**
 * User Model Class
 * Handles user-related operations
 */

require_once __DIR__ . '/Database.php';

class User {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Create or update user from Google OAuth data
     */
    public function createOrUpdateFromGoogle($googleData) {
        // Check if user exists
        $user = $this->getUserByGoogleId($googleData['id']);

        $userData = array(
            'google_id' => $googleData['id'],
            'email' => $googleData['email'],
            'name' => $googleData['name'],
            'picture' => isset($googleData['picture']) ? $googleData['picture'] : null
        );

        if ($user) {
            // Update existing user
            $this->db->update('users', $userData, 'id = :id', array(':id' => $user['id']));
            return $this->getUserById($user['id']);
        } else {
            // Create new user
            $userId = $this->db->insert('users', $userData);
            if ($userId) {
                // Create empty profile
                $this->db->insert('user_profiles', array('user_id' => $userId));
                return $this->getUserById($userId);
            }
        }

        return false;
    }

    /**
     * Get user by ID
     */
    public function getUserById($id) {
        $sql = "SELECT * FROM users WHERE id = :id";
        return $this->db->fetch($sql, array(':id' => $id));
    }

    /**
     * Get user by email
     */
    public function getUserByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = :email";
        return $this->db->fetch($sql, array(':email' => $email));
    }

    /**
     * Get user by Google ID
     */
    public function getUserByGoogleId($googleId) {
        $sql = "SELECT * FROM users WHERE google_id = :google_id";
        return $this->db->fetch($sql, array(':google_id' => $googleId));
    }

    /**
     * Get user with profile
     */
    public function getUserWithProfile($userId) {
        $user = $this->getUserById($userId);
        if ($user) {
            $sql = "SELECT * FROM user_profiles WHERE user_id = :user_id";
            $profile = $this->db->fetch($sql, array(':user_id' => $userId));
            $user['profile'] = $profile ? $profile : array();
        }
        return $user;
    }

    /**
     * Update user profile
     */
    public function updateProfile($userId, $profileData) {
        // Check if profile exists
        $sql = "SELECT id FROM user_profiles WHERE user_id = :user_id";
        $profile = $this->db->fetch($sql, array(':user_id' => $userId));

        if ($profile) {
            return $this->db->update('user_profiles', $profileData, 'user_id = :user_id', array(':user_id' => $userId));
        } else {
            $profileData['user_id'] = $userId;
            return $this->db->insert('user_profiles', $profileData);
        }
    }

    /**
     * Get user profile
     */
    public function getProfile($userId) {
        $sql = "SELECT * FROM user_profiles WHERE user_id = :user_id";
        return $this->db->fetch($sql, array(':user_id' => $userId));
    }

    /**
     * Delete user
     */
    public function deleteUser($id) {
        return $this->db->delete('users', 'id = :id', array(':id' => $id));
    }

    /**
     * Update last login
     */
    public function updateLastLogin($userId) {
        $sql = "UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = :id";
        return $this->db->query($sql, array(':id' => $userId));
    }

    /**
     * Get all users (admin function)
     */
    public function getAllUsers() {
        $sql = "SELECT * FROM users ORDER BY created_at DESC";
        return $this->db->fetchAll($sql);
    }
}
