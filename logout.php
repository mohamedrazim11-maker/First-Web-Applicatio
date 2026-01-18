<?php
session_start();

// 1. Clear all session variables
$_SESSION = array();

// 2. Destroy the session completely
session_destroy();

// 3. Redirect to the login page
header("Location: login.php");
exit();
?>