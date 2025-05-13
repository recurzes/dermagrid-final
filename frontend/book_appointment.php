<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get the submitted form data
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $contactNumber = $_POST['contact_number'];
    $email = $_POST['email'];
    $reason = $_POST['reason'];
    $appointment_date = $_POST['selected_date'];
    $appointment_time = $_POST['selected_time'];

    $doctor = "Dr. Michael Rivera";
    $department = "General Dermatology";

    $booked_on = $appointment_date; // Set booked on same as selected date

    // Create the appointment entry
    $entry = "$firstName|$lastName|$contactNumber|$email|$appointment_date|$appointment_time|$doctor|$department|$reason|$booked_on";

    // Append the new appointment data to the file
    file_put_contents("appointments.txt", $entry . PHP_EOL, FILE_APPEND);

    // Redirection code after successfully booking the appointment
    // Redirect to the patient's appointment details page
    header("Location: appointmentdetails.php?contact=" . urlencode($contactNumber));
    exit(); // Make sure to stop further execution to avoid any unwanted output
}
?>
