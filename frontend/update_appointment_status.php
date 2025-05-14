<?php
require_once '../backend/config/database.php';
require_once '../backend/models/Appointment.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? $_POST['id'] : null;
    $status = isset($_POST['status']) ? $_POST['status'] : null;
    
    if (!$id || !$status) {
        http_response_code(400);
        echo "Missing appointment ID or status";
        exit;
    }
    
    try {
        $database = getDbConnection();
        $appointmentModel = new Appointment($database);
        
        // Update appointment status
        $success = $appointmentModel->updateStatus($id, $status);
        
        if ($success) {
            echo "Status updated successfully";
        } else {
            http_response_code(500);
            echo "Failed to update status";
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo "Error: " . $e->getMessage();
    }
} else {
    http_response_code(405);
    echo "Method not allowed";
}
