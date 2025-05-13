<?php
// Database connection configuration

// Database credentials
const DB_HOST = 'localhost';
const DB_NAME = 'dermagrid';
const DB_USER = 'root';
const DB_PASS = '';

// Create connection 
function getDbConnection() {
    try {
        $conn = new PDO('mysql:host='.DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
        // Set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // Set the default fetch mode to associative array 
        $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $conn;
    } catch(PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

// Execute a stored procedure
function executeStoredProcedure($procedureName, $params = []): array
{
    $conn = getDbConnection();

    // Prepare parameter placeholders
    $placeholders = implode(',', array_fill(0, count($params), '?'));

    // Prepare the statement
    $stmt = $conn->prepare("CALL $procedureName($placeholders)");

    // Execute with parameters
    $stmt->execute($params);

    // Return the result
    return $stmt->fetchAll();
}

// Execute a query
function executeQuery($sql, $params = []) {
    $conn = getDbConnection();

    // Prepare the statement
    $stmt = $conn->prepare($sql);

    // Execute with parameters
    $stmt->execute($params);

    // Return the result
    return $stmt->fetchAll();
}