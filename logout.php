<?php
/**
 * Logout Handler
 */

require_once 'config.php';
require_once 'includes/Auth.php';

Auth::logout();

header('Location: login.php?msg=logged_out');
exit;
