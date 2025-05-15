<?php
// Initialize the session
session_start();
 
// Include database configuration
require_once __DIR__ . '/../config/database.php';
 
// Define variables and initialize with empty values
$login_identifier = $password = "";
$login_identifier_err = $password_err = $login_err = "";
 
// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    // Check if email/username is empty
    if(empty(trim($_POST["email"]))){        
        $login_identifier_err = "Please enter your email or username.";        
    } else{
        $login_identifier = trim($_POST["email"]);
    }
    
    // Check if password is empty
    if(empty(trim($_POST["password"]))){        
        $password_err = "Please enter your password.";        
    } else{
        $password = trim($_POST["password"]);
    }
    
    // Validate credentials
    if(empty($login_identifier_err) && empty($password_err)){
        try {
            // Look up the staff by email OR username in the database
            $result = executeQuery("SELECT * FROM staff WHERE email = ? OR username = ?", [$login_identifier, $login_identifier]);
            
            if (!empty($result)) {
                $staff = $result[0];
                
                // Verify password
                if (password_verify($password, $staff['password_hash'])) {
                    // Password is correct, start session
                    
                    // Store data in session variables
                    $_SESSION["loggedin"] = true;
                    $_SESSION["user_id"] = $staff["id"];  // Set user_id for consistency
                    $_SESSION["staff_id"] = $staff["id"]; // Keep staff_id for backward compatibility
                    $_SESSION["first_name"] = $staff["first_name"];
                    $_SESSION["last_name"] = $staff["last_name"];
                    $_SESSION["email"] = $staff["email"];
                    $_SESSION["username"] = $staff["username"];
                    $_SESSION["role"] = $staff["role"];
                    
                    // Remember me functionality
                    if(isset($_POST["remember"]) && $_POST["remember"] === "on"){
                        // Set cookies for 30 days
                        setcookie("login_identifier", $login_identifier, time() + (86400 * 30), "/");
                    }
                    
                    // Redirect user to dashboard
                    header("location: /frontend/dashboard.php");
                    exit;
                } else {
                    // Password is not valid
                    $login_err = "Invalid email/username or password.";
                }
            } else {
                // Email/username doesn't exist
                $login_err = "Invalid email/username or password.";
            }
        } catch (Exception $e) {
            $login_err = "An error occurred: " . $e->getMessage();
        }
    }
    
    // Store errors in session
    $_SESSION['email_err'] = $login_identifier_err;
    $_SESSION['password_err'] = $password_err;
    $_SESSION['login_err'] = $login_err;
    $_SESSION['email'] = $login_identifier; // To refill the form
    
    header("location: /frontend/login.php");
    exit;
}
?>