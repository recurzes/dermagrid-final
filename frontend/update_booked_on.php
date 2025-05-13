<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $index = isset($_POST['index']) ? (int)$_POST['index'] : -1;
    $new_date = $_POST['new_date'] ?? '';
    $new_time = $_POST['new_time'] ?? '';

    $file = "appointments.txt";

    if (!file_exists($file)) {
        http_response_code(404);
        echo "File not found";
        exit;
    }

    $lines = file($file, FILE_IGNORE_NEW_LINES);
    if (!isset($lines[$index])) {
        http_response_code(400);
        echo "Invalid index";
        exit;
    }

    $originalLine = $lines[$index];
    $fields = explode("|", $originalLine);

    if (count($fields) >= 10) {
        // 1. Append old version as a history entry
        $historyFields = $fields;
        $historyFields[9] = $fields[4]; // Use original appointment_date as booked_on for history
        $historyLine = implode("|", $historyFields);
        file_put_contents($file, $historyLine . PHP_EOL, FILE_APPEND);

        // 2. Update original line with new date/time
        $fields[5] = $new_time;
        $fields[9] = $new_date; // booked_on = new date

        $lines[$index] = implode("|", $fields);
        file_put_contents($file, implode(PHP_EOL, $lines) . PHP_EOL);

        http_response_code(200);
    } else {
        http_response_code(400);
        echo "Invalid record format.";
    }
}
?>