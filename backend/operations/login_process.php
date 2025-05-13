<?php
// Initialize the session
session_start();
 
// Include database configuration
require_once __DIR__ . '/../config/database.php';
 
// Define variables and initialize with empty values
$email = $password = "";
$email_err = $password_err = $login_err = "";
 
// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    // Check if email is empty
    if(empty(trim($_POST["email"]))){        
        $email_err = "Please enter your email.";        
    } else{
        $email = trim($_POST["email"]);
    }
    
    // Check if password is empty
    if(empty(trim($_POST["password"]))){        
        $password_err = "Please enter your password.";        
    } else{
        $password = trim($_POST["password"]);
    }
    
    // Validate credentials
    if(empty($email_err) && empty($password_err)){
        try {
            // Extract username from email (if your system uses this approach)
            // Or look up the staff by email in the database
            $result = executeQuery("SELECT * FROM staff WHERE email = ?", [$email]);
            
            if (!empty($result)) {
                $staff = $result[0];
                
                // Verify password
                if (password_verify($password, $staff['password_hash'])) {
                    // Password is correct, start session
                    
                    // Store data in session variables
                    $_SESSION["loggedin"] = true;
                    $_SESSION["staff_id"] = $staff["id"];
                    $_SESSION["first_name"] = $staff["first_name"];
                    $_SESSION["last_name"] = $staff["last_name"];
                    $_SESSION["email"] = $staff["email"];
                    $_SESSION["role"] = $staff["role"];
                    
                    // Remember me functionality
                    if(isset($_POST["remember"]) && $_POST["remember"] === "on"){
                        // Set cookies for 30 days
                        setcookie("email", $email, time() + (86400 * 30), "/");
                    }
                    
                    // Redirect user to dashboard
                    header("location: /frontend/dashboard.php");
                    exit;
                } else {
                    // Password is not valid
                    $login_err = "Invalid email or password.";
                }
            } else {
                // Email doesn't exist
                $login_err = "Invalid email or password.";
            }
        } catch (Exception $e) {
            $login_err = "An error occurred: " . $e->getMessage();
        }
    }
    
    // Store errors in session
    $_SESSION['email_err'] = $email_err;
    $_SESSION['password_err'] = $password_err;
    $_SESSION['login_err'] = $login_err;
    $_SESSION['email'] = $email; // To refill the form
    
    header("location: /frontend/login.php");
    exit;
}
?>