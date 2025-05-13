<?php
// Common utility functions

// Start session if not already started
function session_start_once()
{
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

// Check if user is logged in
function is_logged_in()
{
    session_start_once();;
    return isset($_SESSION['staff_id']);
}

// Redirect if not logged in
function require_login()
{
    if (!is_logged_in()) {
        header("Location: /auth/login.php");
        exit;
    }
}

// Check if current user has permission based on role
function has_permission($action, $resource): bool
{
    session_start_once();

    if (!isset($_SESSION['role'])) {
        return false;
    }

    $role = $_SESSION['role'];

    // Define permissions for each role
    $permissions = [
        'doctor' => [
            'patient' => ['read', 'write', 'update', 'delete'],
            'appointment' => ['read', 'write', 'update', 'delete'],
            'medical_record' => ['read', 'write', 'update', 'delete'],
            'prescription' => ['read', 'write', 'update', 'delete'],
            'staff' => ['read']
        ],
        'nurse' => [
            'patient' => ['read', 'write', 'update'],
            'appointment' => ['read', 'write', 'update'],
            'medical_record' => ['read', 'write', 'update'],
            'prescription' => ['read'],
            'staff' => ['read']
        ],
        'receptionist' => [
            'patient' => ['read', 'write', 'update'],
            'appointment' => ['read', 'write', 'update', 'delete'],
            'medical_record' => ['read'],
            'prescription' => ['read'],
            'staff' => ['read']
        ]
    ];

    if (isset($permissions[$role][$resource]) && in_array($action, $permissions[$role][$resource])) {
        return true;
    }

    return false;
}

// Sanitize user input
function sanitize($input) {
    if (is_array($input)) {
        foreach ($input as $key => $value) {
            $input[$key] = sanitize($value);
        }
    } else {
        $input = trim($input);
        $input = stripslashes($input);
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }

    return $input;
}

// Format date for display
function format_date($date, $include_time = false) {
    if (!$date) return '';

    $format = 'm/d/Y';
    if ($include_time) {
        $format .= 'g:i A';
    }

    $datetime = new DateTime($date);
    return $datetime->format($format);
}

// Flash messages
function set_flash_message($type, $message) {
    session_start_once();
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

function get_flash_message() {
    session_start_once();
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flash;
    }
    return null;
}

// Access denied
function access_denied() {
    header("HTTP/1.1 403 Forbidden");
    echo '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Access Denied</title>
        <link rel="stylesheet" href="/assets/css/style.css">
    </head>
    <body>
        <div class="container">
            <div class="access-denied">
                <h1>Access Denied</h1>
                <p>You do not have permission to access this page.</p>
                <a href="/dashboard/" class="btn btn-primary">Back to Dashboard</a>
            </div>
        </div>
    </body>
    </html>';
    exit;
}