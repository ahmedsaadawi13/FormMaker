<?php
/**
 * Authentication Helper Class
 * Handles user authentication and session management
 */

require_once __DIR__ . '/User.php';

class Auth {

    /**
     * Check if user is logged in
     */
    public static function check() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * Get current user ID
     */
    public static function id() {
        return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    }

    /**
     * Get current user data
     */
    public static function user() {
        if (!self::check()) {
            return null;
        }

        if (!isset($_SESSION['user_data'])) {
            $userModel = new User();
            $_SESSION['user_data'] = $userModel->getUserById(self::id());
        }

        return $_SESSION['user_data'];
    }

    /**
     * Login user
     */
    public static function login($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_data'] = $user;

        // Update last login
        $userModel = new User();
        $userModel->updateLastLogin($user['id']);

        return true;
    }

    /**
     * Logout user
     */
    public static function logout() {
        unset($_SESSION['user_id']);
        unset($_SESSION['user_data']);
        session_destroy();
        return true;
    }

    /**
     * Require authentication (redirect if not logged in)
     */
    public static function require_auth() {
        if (!self::check()) {
            header('Location: ' . BASE_URL . '/login.php');
            exit;
        }
    }

    /**
     * Redirect if already authenticated
     */
    public static function redirect_if_authenticated() {
        if (self::check()) {
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }
    }

    /**
     * Get user's full name
     */
    public static function name() {
        $user = self::user();
        return $user ? $user['name'] : 'Guest';
    }

    /**
     * Get user's email
     */
    public static function email() {
        $user = self::user();
        return $user ? $user['email'] : '';
    }

    /**
     * Get user's picture
     */
    public static function picture() {
        $user = self::user();
        return $user && isset($user['picture']) ? $user['picture'] : '';
    }
}
