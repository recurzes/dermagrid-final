<?php
// Initialize the session
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database configuration
require_once __DIR__ . '/../config/database.php';

// Define variables and initialize with empty values
$first_name = $last_name = $role = $email = $mobile = $username = $password = "";
$first_name_err = $last_name_err = $role_err = $email_err = $mobile_err = $username_err = $password_err = $terms_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validate first name
    if (empty(trim($_POST["first_name"]))) {
        $first_name_err = "Please enter your first name.";
    } else {
        $first_name = trim($_POST["first_name"]);
    }
    
    // Validate last name
    if (empty(trim($_POST["last_name"]))) {
        $last_name_err = "Please enter your last name.";
    } else {
        $last_name = trim($_POST["last_name"]);
    }
    
    // Validate role
    if (empty(trim($_POST["role"]))) {
        $role_err = "Please select a role.";
    } else {
        $role = trim($_POST["role"]);
        // Check if role is valid
        if (!in_array($role, ['doctor', 'nurse', 'receptionist'])) {
            $role_err = "Invalid role selected.";
        }
    }
    
    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter an email.";
    } else {
        $email = trim($_POST["email"]);
        // Check if email is valid
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email_err = "Please enter a valid email address.";
        }
    }
    
    // Validate mobile
    if (empty(trim($_POST["mobile"]))) {
        $mobile_err = "Please enter a mobile number.";
    } else {
        $mobile = trim($_POST["mobile"]);
    }
    
    // Validate username
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter a username.";
    } else {
        $username = trim($_POST["username"]);
        
        // Check if username exists
        try {
            $result = executeQuery("SELECT id FROM staff WHERE username = ?", [$username]);
            if (!empty($result)) {
                $username_err = "This username is already taken.";
            }
        } catch (Exception $e) {
            $username_err = "An error occurred: " . $e->getMessage();
        }
    }
    
    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";
    } elseif (strlen(trim($_POST["password"])) < 8) {
        $password_err = "Password must have at least 8 characters.";
    } else {
        $password = trim($_POST["password"]);
    }
    
    // Validate terms
    if (!isset($_POST["terms"])) {
        $terms_err = "You must agree to the terms and conditions.";
    }
    
    // For debugging: Print out all errors
    echo "<div style='background: #f8d7da; padding: 10px; margin: 10px 0;'>";
    echo "<h3>Validation Errors:</h3>";
    echo "First Name: " . $first_name_err . "<br>";
    echo "Last Name: " . $last_name_err . "<br>";
    echo "Role: " . $role_err . "<br>";
    echo "Email: " . $email_err . "<br>";
    echo "Mobile: " . $mobile_err . "<br>";
    echo "Username: " . $username_err . "<br>";
    echo "Password: " . $password_err . "<br>";
    echo "Terms: " . $terms_err . "<br>";
    echo "</div>";
    
    // Check input errors before inserting in database
    if (empty($first_name_err) && empty($last_name_err) && empty($role_err) && empty($email_err) && 
        empty($mobile_err) && empty($username_err) && empty($password_err) && empty($terms_err)) {
        
        try {
            // Hash the password
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            // Debug: Show parameters being sent
            echo "<div style='background: #d1e7dd; padding: 10px; margin: 10px 0;'>";
            echo "<h3>Data being sent to AddStaff procedure:</h3>";
            echo "First Name: " . $first_name . "<br>";
            echo "Last Name: " . $last_name . "<br>";
            echo "Role: " . $role . "<br>";
            echo "Email: " . $email . "<br>";
            echo "Phone (mobile): " . $mobile . "<br>";
            echo "Username: " . $username . "<br>";
            echo "Password Hash: " . $password_hash . "<br>";
            echo "</div>";
            
            // Get a direct database connection to debug
            $conn = getDbConnection();
            
            // Try a direct SQL query first to check if we can insert
            $sql = "INSERT INTO staff (first_name, last_name, role, email, phone, username, password_hash) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $result = $stmt->execute([$first_name, $last_name, $role, $email, $mobile, $username, $password_hash]);
            
            if ($result) {
                echo "<div style='background: #d1e7dd; padding: 10px; margin: 10px 0;'>";
                echo "Direct SQL insert successful! Staff ID: " . $conn->lastInsertId();
                echo "</div>";
                
                // Redirect to login page with success message after 5 seconds
                echo "<script>
                    setTimeout(function() {
                        window.location.href = '../../frontend/login.php';
                    }, 5000);
                </script>";
                echo "<div style='background: #cce5ff; padding: 10px; margin: 10px 0;'>";
                echo "Registration successful! Redirecting to login page in 5 seconds...";
                echo "</div>";
                exit();
            } else {
                echo "<div style='background: #f8d7da; padding: 10px; margin: 10px 0;'>";
                echo "Direct SQL insert failed!";
                echo "</div>";
            }
            
        } catch (Exception $e) {
            echo "<div style='background: #f8d7da; padding: 10px; margin: 10px 0;'>";
            echo "<h3>Database Error:</h3>";
            echo $e->getMessage();
            echo "</div>";
        }
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; margin: 10px 0;'>";
        echo "Form has validation errors. Please fix them and try again.";
        echo "<br><a href='/frontend/signup.php'>Go back to the form</a>";
        echo "</div>";
    }
    
    // Prevent further execution
    exit;
}
?>

<a href='/frontend/signup.php'>Go back to the form</a>
