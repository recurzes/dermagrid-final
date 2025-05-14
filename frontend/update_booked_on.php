<?php
session_start();
require_once '../backend/config/database.php';
require_once '../backend/models/Appointment.php';

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get POST data
    $appointment_id = isset($_POST['index']) ? (int)$_POST['index'] : 0;
    $new_date = isset($_POST['new_date']) ? $_POST['new_date'] : '';
    $new_time = isset($_POST['new_time']) ? $_POST['new_time'] : '';
    
    // Validate inputs
    if ($appointment_id <= 0) {
        http_response_code(400);
        echo "Invalid appointment ID";
        exit;
    }
    
    if (empty($new_date) || empty($new_time)) {
        http_response_code(400);
        echo "Date and time are required";
        exit;
    }
    
    try {
        // Initialize database and appointment model
        $database = getDbConnection();
        $appointmentModel = new Appointment($database);
        
        // Get current appointment to preserve other fields
        $currentAppointment = $appointmentModel->getById($appointment_id);
        
        if (!$currentAppointment) {
            http_response_code(404);
            echo "Appointment not found";
            exit;
        }
        
        // Format time to ensure compatibility with database
        $time_obj = DateTime::createFromFormat('g:i', $new_time);
        if (!$time_obj) {
            // Try alternate format if first one fails
            $time_obj = DateTime::createFromFormat('G:i', $new_time);
        }
        
        if ($time_obj) {
            $formatted_time = $time_obj->format('H:i:s');
        } else {
            // If we couldn't parse the time, use as-is and let the database handle it
            $formatted_time = $new_time . ':00';
        }
        
        // Update the appointment using the stored procedure UpdateAppointment
        $result = $appointmentModel->update(
            $appointment_id,
            $currentAppointment['patient_id'],
            $currentAppointment['staff_id'],
            $new_date,
            $formatted_time,
            $currentAppointment['status'],
            $currentAppointment['reason'],
            $currentAppointment['notes'] ?? null
        );
        
        if ($result) {
            echo "success";
        } else {
            http_response_code(500);
            echo "Failed to update appointment";
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo "Error: " . $e->getMessage();
    }
} else {
    http_response_code(405);
    echo "Method not allowed";
}
?>