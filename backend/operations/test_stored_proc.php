<?php
require_once '../config/database.php';
$conn = getDbConnection();
$procedures = executeQuery("SHOW PROCEDURE STATUS WHERE Db = 'dermagrid'");
echo "<pre>";
print_r($procedures);
echo "</pre>";
