<?php
// Initialize the session
session_start();
 
// Unset all of the session variables
$_SESSION = array();
 
// Destroy the session.
session_destroy();
 
// Delete the remember me cookie if it exists
if (isset($_COOKIE['email'])) {
    setcookie('email', '', time() - 3600, '/'); // Set expiration to an hour ago
}
 
// Redirect to login page
header("location: login.php");
exit;
?>
