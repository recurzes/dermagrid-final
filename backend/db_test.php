<?php
// Set headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// Capture any output including errors
ob_start();

try {
    // Include database config
    require_once __DIR__ . '/config/database.php';
    
    // Test connection
    $conn = getDbConnection();
    
    // Show tables in database
    $tables = executeQuery("SHOW TABLES");
    
    // Get sample data from first table (if any exists)
    $sampleData = [];
    if (!empty($tables)) {
        $firstTable = array_values($tables[0])[0];
        $sampleData = executeQuery("SELECT * FROM `$firstTable` LIMIT 5");
    }
    
    // Successful response
    $response = [
        'status' => 'success',
        'message' => 'Successfully connected to ' . DB_NAME . ' database',
        'tables' => $tables,
        'sampleData' => $sampleData
    ];
    
} catch (Exception $e) {
    // Capture any errors
    $response = [
        'status' => 'error',
        'message' => $e->getMessage()
    ];
}

// Clear any output that might have occurred
ob_end_clean();

// Output clean JSON response
echo json_encode($response);