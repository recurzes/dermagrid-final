<?php
// Initialize the session
session_start();
 
// Unset all of the session variables
$_SESSION = array();
 
// Destroy the session.
session_destroy();
 
// If a cookie was set for "remember me", delete it
if (isset($_COOKIE['login_identifier'])) {
    setcookie('login_identifier', '', time() - 3600, '/');
}
 
// Redirect to login page
header("location: login.php");
exit;
?>
