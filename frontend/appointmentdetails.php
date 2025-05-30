<?php
session_start();
require_once '../backend/config/database.php';
require_once '../backend/models/Appointment.php';
require_once '../backend/models/MedicalRecord.php';
require_once '../backend/models/Prescription.php';
require_once '../backend/models/Patient.php';

$appointments = [];
$history = [];
$latestByPatient = [];

// Get appointment ID from URL
$appointment_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$contact_number = $_GET['contact_number'] ?? null;

// Initialize database models
$database = getDbConnection();
$appointmentModel = new Appointment($database);
$patientModel = new Patient($database); // Assuming you have a Patient model

if ($contact_number) {
    // If viewing by contact number, get the patient first
    $patients = $patientModel->searchByPhone($contact_number);
    
    if (!empty($patients)) {
        $patient_id = $patients[0]['id'];
        
        // Get all appointments for this patient
        $allAppointments = $appointmentModel->getByPatient($patient_id);
        
        if (!empty($allAppointments)) {
            // Process each appointment
            foreach ($allAppointments as $appt) {
                $processed = [
                    'id' => $appt['id'],
                    'first_name' => explode(' ', $appt['patient_name'])[0] ?? '',
                    'last_name' => explode(' ', $appt['patient_name'])[1] ?? '',
                    'contact_number' => $appt['contact_number'] ?? '',
                    'email' => $appt['email'] ?? '',
                    'appointment_date' => $appt['appointment_date'],
                    'appointment_time' => $appt['appointment_time'],
                    'doctor' => $appt['doctor_name'] ?? '',
                    'reason' => $appt['reason'] ?? '',
                    'booked_on' => $appt['created_at'] ?? '',
                    'status' => $appt['status']
                ];
                
                // Normalize status
                if ($processed['status'] == 'completed') {
                    $processed['status'] = 'Completed';
                } else if (date('Y-m-d') == $processed['appointment_date']) {
                    $processed['status'] = 'Today';
                } else if ($processed['status'] == 'scheduled') {
                    $processed['status'] = 'Upcoming';
                }
                
                // Add to appointments or history
                if (count($appointments) == 0) {
                    $appointments[] = $processed;
            } else {
                    $history[] = $processed;
                }
            }
            
            // Sort history by date
            usort($history, function($a, $b) {
                $dateA = strtotime($a['appointment_date'] . ' ' . $a['appointment_time']);
                $dateB = strtotime($b['appointment_date'] . ' ' . $b['appointment_time']);
                return $dateB - $dateA; // Descending order
            });
        }
    }
} else if ($appointment_id) {
    // If viewing by appointment ID, just get that appointment
    $appointment = $appointmentModel->getById($appointment_id);
    
    if ($appointment) {
        // Add to appointments array in the same format
        $appointments[] = [
            'id' => $appointment['id'],
            'first_name' => explode(' ', $appointment['patient_name'])[0] ?? '',
            'last_name' => explode(' ', $appointment['patient_name'])[1] ?? '',
            'contact' => $appointment['contact_number'] ?? '',
            'email' => $appointment['email'] ?? '',
            'appointment_date' => $appointment['appointment_date'],
            'appointment_time' => $appointment['appointment_time'],
            'doctor' => $appointment['doctor_name'] ?? '',
            'reason' => $appointment['reason'] ?? '',
            'booked_on' => $appointment['created_at'] ?? '',
            'status' => ucfirst($appointment['status'] ?? 'Upcoming')
        ];
        
        // Get patient ID from the appointment
        $patient_id = $appointment['patient_id'] ?? null;
        
        // Get patient's appointment history
        if ($patient_id) {
            $patientAppointments = $appointmentModel->getByPatient($patient_id);
            
            foreach ($patientAppointments as $appt) {
                // Skip the current appointment
                if ($appt['id'] != $appointment_id) {
                    $history[] = [
                        'id' => $appt['id'],
                        'first_name' => explode(' ', $appt['patient_name'])[0] ?? '',
                        'last_name' => explode(' ', $appt['patient_name'])[1] ?? '',
                        'contact' => $appt['contact_number'] ?? '',
                        'email' => $appt['email'] ?? '',
                        'appointment_date' => $appt['appointment_date'],
                        'appointment_time' => $appt['appointment_time'],
                        'doctor' => $appt['doctor_name'] ?? '',
                        'reason' => $appt['reason'] ?? '',
                        'booked_on' => $appt['created_at'] ?? '',
                        'status' => ucfirst($appt['status'] ?? 'Upcoming')
                    ];
                }
            }
            
            // Sort history
            usort($history, function($a, $b) {
        $dateA = strtotime($a['appointment_date'] . ' ' . $a['appointment_time']);
        $dateB = strtotime($b['appointment_date'] . ' ' . $b['appointment_time']);
                return $dateB - $dateA;
    });
        }
    }
}

// Get appointment ID from URL
$appointment_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$appointment_id) {
    // Redirect to appointments page if no ID provided
    header('Location: appointments.php');
    exit;
}

// Initialize database connection and models
$database = getDbConnection();
$appointmentModel = new Appointment($database);
$medicalRecordModel = new MedicalRecord($database);
$prescriptionModel = new Prescription($database);

// Fetch appointment details
try {
    $appointment = $appointmentModel->getById($appointment_id);

    if (!$appointment) {
        // Appointment not found, redirect to appointments page
        header('Location: appointments.php');
        exit;
    }

    // We need to get the patient ID directly from the appointment table
    // Let's add a direct SQL query to get the patient ID for this appointment
    $patientIdQuery = "SELECT patient_id FROM appointment WHERE id = ?";
    $stmt = $database->prepare($patientIdQuery);
    $stmt->execute([$appointment_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $patientId = $result ? $result['patient_id'] : null;

    // Get medical records for this patient
    $medicalRecords = [];
    if ($patientId) {
        $medicalRecords = $medicalRecordModel->getByPatient($patientId);
    }

    // Get prescriptions for this patient
    $prescriptions = [];
    if ($patientId) {
        $prescriptions = $prescriptionModel->getByPatient($patientId);
    }

    // For debugging
    $debug = [
        'appointment_id' => $appointment_id,
        'patient_id' => $patientId,
        'medical_records_count' => count($medicalRecords),
        'prescriptions_count' => count($prescriptions)
    ];

    // Get appointment history for this patient (appointments other than the current one)
    $history = [];
    if ($patientId) {
        try {
            $patientAppointments = $appointmentModel->getByPatient($patientId);
            foreach ($patientAppointments as $appt) {
                // Skip the current appointment
                if ($appt['id'] != $appointment_id) {
                    $history[] = $appt;
                }
            }
        } catch (Exception $e) {
            // Handle error
        }
    }

    // After you get the current appointment and patient ID:
    // Get all completed appointments for this patient
    $completedAppointments = [];
    if ($patientId) {
        try {
            // Prepare SQL to get all completed appointments for this patient
            $completedQuery = "SELECT a.id, a.appointment_date, a.appointment_time, 
                               a.reason, a.status, a.created_at as booked_on, 
                               CONCAT(s.first_name, ' ', s.last_name) AS doctor_name
                               FROM appointment a
                               JOIN staff s ON a.staff_id = s.id
                               WHERE a.patient_id = ? AND a.status = 'completed'
                               ORDER BY a.appointment_date DESC, a.appointment_time DESC";

            $stmt = $database->prepare($completedQuery);
            $stmt->execute([$patientId]);
            $completedAppointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            // Optional: Log error or handle as needed
            $completedAppointments = []; // Ensure empty array on error
        }
    }

} catch (Exception $e) {
    $error = $e->getMessage();
}

// Set links for medical records and prescriptions with patient details
$patientName = isset($appointment) ? urlencode($appointment['patient_name']) : '';
$doctor = isset($appointment) ? urlencode($appointment['doctor_name']) : '';
$date = isset($appointment) ? urlencode($appointment['appointment_date']) : '';
$contact = isset($appointment) ? urlencode($appointment['contact_number']) : '';
$link = "addmedicalrecords.php?patient=$patientName&doctor=$doctor&date=$date&contact=$contact&debug=1";
$preslink = "addprescription.php?patient=$patientName&doctor=$doctor&date=$date&contact=$contact";

// Display success or error messages if they exist
if (isset($_SESSION['success_message'])) {
    echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
    echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
    unset($_SESSION['error_message']);
}

// Near the beginning of the file where you process form actions
if (isset($_GET['id']) && isset($_GET['mark_successful'])) {
    $appointment_id = $_GET['id'];

    try {
        $result = $appointmentModel->updateStatus($appointment_id, 'completed');

        if ($result['success']) {
            $_SESSION['success_message'] = "Appointment marked as completed successfully!";
        } else {
            $_SESSION['error_message'] = $result['error'];
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
    }

    // Redirect to refresh the page
    header("Location: appointmentdetails.php?id=$appointment_id");
    exit;
}

// Process appointment rescheduling
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['date'], $_POST['time'])) {
    $appt_id = (int)$_POST['id'];
    $new_date = $_POST['date'];
    $new_time = $_POST['time'];

    try {
        // Get current appointment data
        $current = $appointmentModel->getById($appt_id);

        if (!$current) {
            $_SESSION['error_message'] = "Appointment not found - ID: $appt_id";
            header("Location: appointmentdetails.php?id=$appt_id");
            exit;
        }

        // Debug current appointment data
        $_SESSION['debug_data'] = "Current appointment data: " . print_r($current, true);

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

        // Get patient_id and staff_id directly from the database if they're not in the current array
        if (!isset($current['patient_id']) || !isset($current['staff_id'])) {
            $stmt = $database->prepare("SELECT patient_id, staff_id FROM appointment WHERE id = ?");
            $stmt->execute([$appt_id]);
            $ids = $stmt->fetch(PDO::FETCH_ASSOC);
            $patient_id = $ids['patient_id'];
            $staff_id = $ids['staff_id'];
        } else {
            $patient_id = $current['patient_id'];
            $staff_id = $current['staff_id'];
        }

        // Debug data to be passed to update
        $debug_data = [
            'id' => $appt_id,
            'patient_id' => $patient_id,
            'staff_id' => $staff_id,
            'new_date' => $new_date,
            'formatted_time' => $formatted_time,
            'status' => $current['status'] ?? 'scheduled',
            'reason' => $current['reason'] ?? '',
            'notes' => $current['notes'] ?? null
        ];
        $_SESSION['debug_update_params'] = $debug_data;

        // Call the UpdateAppointment stored procedure to update the appointment
        $result = $appointmentModel->update(
            $appt_id,
            $patient_id,
            $staff_id,
            $new_date,
            $formatted_time,
            $current['status'] ?? 'scheduled',
            $current['reason'] ?? '',
            $current['notes'] ?? null
        );

        if ($result) {
            $_SESSION['success_message'] = "Appointment rescheduled successfully";
        } else {
            // Add detailed debug info to error message
            $_SESSION['error_message'] = "Failed to update appointment. Debug info: " . 
                                       json_encode($debug_data);
        }
    } catch (Exception $e) {
        // Include the exception message and trace for better debugging
        $_SESSION['error_message'] = "Error: " . $e->getMessage() . 
                                   "<br>Trace: " . $e->getTraceAsString();
    }

    // Redirect to refresh the page with debug parameter
    header("Location: appointmentdetails.php?id=$appt_id&debug=1");
    exit;
}

?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>DermaGrid - Appointment Details</title>

    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link
            href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
            rel="stylesheet">

    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/style2.css">


    <style>
        .btn-blue, .hover-blue:hover {
            background-color: #4a73df;
            color: white;
            border: 1px solid #4a73df;
        }
        .hover-blue:hover {
            background-color: #0130a7;
            border-color: #0130a7;
        }

        #overlay {
            position: fixed;
            display: none;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 2;
            cursor: pointer;
            align-items: center;
            justify-content: center;
            padding: 60px;
        }

        .container {
            background-color: #fff;
            max-width: 600px;
            width: 100%;
            overflow-y: auto;
            padding: 20px;
            border-radius: 8px;
            cursor: default;
            height: 100%;
        }
    </style>

</head>

<body id="page-top">

<!-- Page Wrapper -->
<div id="wrapper">

    <!-- Sidebar -->
    <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

        <!-- Sidebar - Brand -->
        <a class="sidebar-brand d-flex align-items-center" href="dashboard.php">
            <div class="sidebar-brand-text">DermaGrid</div>
        </a>

        <hr class="sidebar-divider my-0">

        <li class="nav-item ">
            <a class="nav-link" href="dashboard.php">
                <i class="fas fa-fw fa-tachometer-alt"></i>
                <span>Dashboard</span></a>
        </li>

        <hr class="sidebar-divider">

        <div class="sidebar-heading">
            Project Management
        </div>

        <li class="nav-item active">
            <a class="nav-link" href="appointments.php">
                <i class="bi bi-person"></i>
                <span>Appointments</span></a>
        </li>

        <hr class="sidebar-divider">

        <div class="sidebar-heading">
            Staff Management
        </div>

        <li class="nav-item">
            <a class="nav-link" href="doctors&staff.php">
                <i class="bi bi-people"></i>
                <span>Doctors & Staff</span></a>
        </li>

        <hr class="sidebar-divider d-none d-md-block">

        <div class="text-center d-none d-md-inline">
            <button class="rounded-circle border-0" id="sidebarToggle"></button>
        </div>

    </ul>
    <!-- End of Sidebar -->

    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">

        <!-- Main Content -->
        <div id="content">

            <!-- Topbar -->
            <?php include 'includes/header.php'; ?>

            <!-- Begin Page Content -->
            <div class="container-fluid">

                <!-- Content Row -->
                <div class="row">

                    <div id="overlay" onclick="off()">
                        <div class="container" onclick="event.stopPropagation();">
                            <form method="POST" id="rescheduleForm">
                                <input type="hidden" name="id" id="appointment_id" value="">
                                <input type="hidden" name="date" id="selected_date" value="">
                                <input type="hidden" name="time" id="selected_time" value="">

                                <div class="column" style="flex-direction: column; gap: 20px; height: 100%;">
                                    <div class="calendar" style="width: 100%;">
                                        <h2>Choose a Date</h2>
                                        <div class="header">
                                            <select id="month"></select>
                                            <select id="year"></select>
                                        </div>
                                        <div class="days" id="calendar-days">
                                            <!-- Days will be dynamically inserted here -->
                                        </div>
                                    </div>

                                    <div class="time-picker" style="width: 100%;">
                                        <h2>Pick a time</h2>
                                        <div class="time-grid" id="timeGrid">
                                            <!-- Times will be generated here -->
                                        </div>
                                    </div>

                                    <div class="in-column btn-2">
                                        <button type="button" class="btn btn-secondary" onclick="cancelOverlay()">
                                            Cancel
                                        </button>
                                        <button type="submit" class="actn-btn">
                                            Make Appointment
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Content Column -->
                    <div class="col">

                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger">
                                <?= htmlspecialchars($error) ?>
                            </div>
                        <?php elseif (!empty($appointments)): ?>
                            <?php $a = $appointments[0]; ?>

                            <!-- Appointment Details -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <div class="row px-3">
                                        <h6 class="m-0 font-weight-bold text-primary">Appointment</h6>
                                        <h6 class="ml-auto mb-0 font-weight-bold text--bs-dark-bg-subtle">
                                            Status: <span
                                                    class="text-primary"><?= htmlspecialchars($appointment['status']) ?></span>
                                        </h6>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="container">
                                        <div class="row row-cols-4 small">
                                            <div class="col pb-3 pr-3">
                                                <div class="row">Booked On</div>
                                                <div class="row font-weight-bold text-primary"><?= htmlspecialchars($appointment['booked_on']) ?></div>
                                            </div>
                                            <div class="col pr-3">
                                                <div class="row">Appointment Date</div>
                                                <div class="row font-weight-bold text-primary"><?= htmlspecialchars($appointment['appointment_date']) ?></div>
                                            </div>
                                            <div class="col pr-3">
                                                <div class="row">Appointment Time</div>
                                                <div class="row font-weight-bold text-primary"><?= htmlspecialchars($appointment['appointment_time']) ?></div>
                                            </div>
                                            <div class="col pr-3">
                                                <div class="row">Doctor Assigned</div>
                                                <div class="row font-weight-bold text-primary"><?= htmlspecialchars($appointment['doctor_name']) ?></div>
                                            </div>
                                            <div class="col">
                                                <div class="row">Reason for Visit</div>
                                                <div class="row font-weight-bold text-primary"><?= htmlspecialchars($appointment['reason']) ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer py-3">
                                    <div class="row px-3">
                                        <?php if (strtolower($appointment['status']) !== 'completed'): ?>
                                            <button type="button" onclick="on(<?= $appointment['id'] ?>)"
                                                    class="btn btn-blue text-white hover-blue"
                                                    style="font-size: 12px; margin-right: 10px;">
                                                Reschedule
                                            </button>
                                            <a href="appointmentdetails.php?id=<?= $appointment_id ?>&mark_successful=1"
                                               class="btn btn-success text-white" style="font-size: 12px;">
                                                Mark as Successful
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">This appointment has been completed</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Patient Details -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Patient Details</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row row-cols-4 small">
                                        <?php
                                        // Extract first and last name from patient_name
                                        $nameParts = explode(' ', $appointment['patient_name'], 2);
                                        $firstName = $nameParts[0] ?? '';
                                        $lastName = $nameParts[1] ?? '';

                                        $patientFields = [
                                            ['First Name', $firstName],
                                            ['Last Name', $lastName],
                                            ['Contact Number', $appointment['contact_number']],
                                            ['Email Address', $appointment['email']]
                                        ];
                                        foreach ($patientFields as [$label, $value]): ?>
                                            <div class="col pr-3">
                                                <div class="row"><?= $label ?></div>
                                                <div class="row font-weight-bold text-primary"><?= htmlspecialchars($value) ?></div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="row" style="gap: 10px;">

                            <!-- Medical Records -->
                            <div class="col card shadow mb-4 p-0">
                                <div class="p-2">
                                    <p class="small text-black fw-semibold border-bottom border-black pb-2 mb-3">Medical
                                        Records
                                        <span>
                                                <a href="<?php echo $link; ?>" title="Add Medical Record">
                                                    <i class="bi bi-plus"></i>
                                                </a>
                                            </span>
                                    </p>

                                    <?php if (!empty($medicalRecords)): ?>
                                        <div class="d-flex flex-column gap-2">
                                            <?php foreach ($medicalRecords as $record): ?>
                                                <a href="viewmedical.php?record=<?= $record['id'] ?>"
                                                   style="text-decoration: none;">
                                                    <button type="button"
                                                            class="btn-blue hover-blue d-flex align-items-center p-3 border-0 w-100 text-start"
                                                            style="cursor: pointer;">
                                                        <div class="d-flex justify-content-center align-items-center bg-secondary me-3"
                                                             style="width: 40px; height: 40px;">
                                                            <img src="https://storage.googleapis.com/a1aa/image/e859548d-40b7-4237-dafc-f172c8de247a.jpg"
                                                                 alt="Medical record icon" width="16" height="16"/>
                                                        </div>
                                                        <p class="mb-0 text-white small flex-grow-1"><?= htmlspecialchars($record['diagnosis'] ?? 'Medical Record') ?></p>
                                                        <i class="fas fa-chevron-right text-white"></i>
                                                    </button>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <p>No medical records found for this patient.</p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Prescription -->
                            <div class="col card shadow mb-4 p-0">
                                <div class="p-2" style="max-height: 200px; overflow-y: auto;">
                                    <p class="small text-black fw-semibold border-bottom border-black pb-2 mb-3">
                                        Prescription
                                        <span>
                                                <a href="<?php echo $preslink; ?>" title="Add Prescription">
                                                    <i class="bi bi-plus"></i>
                                                </a>
                                            </span>
                                    </p>

                                    <?php if (!empty($prescriptions)): ?>
                                        <div class="d-flex flex-column gap-2">
                                            <?php foreach ($prescriptions as $prescription): ?>
                                                <a href="viewpres.php?record=<?= $prescription['id'] ?>"
                                                   style="text-decoration: none;">
                                                    <button type="button"
                                                            class="btn-blue hover-blue d-flex align-items-center p-3 border-0 w-100 text-start"
                                                            style="cursor: pointer;">
                                                        <div class="d-flex justify-content-center align-items-center bg-secondary me-3"
                                                             style="width: 40px; height: 40px;">
                                                            <img src="https://storage.googleapis.com/a1aa/image/e859548d-40b7-4237-dafc-f172c8de247a.jpg"
                                                                 alt="Prescription icon" width="16" height="16"/>
                                                        </div>
                                                        <p class="mb-0 text-white small flex-grow-1"><?= htmlspecialchars($prescription['medication_name'] ?? 'Unknown Medicine') ?></p>
                                                        <i class="fas fa-chevron-right text-white"></i>
                                                    </button>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <p>No prescriptions found for this patient.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Appointment History -->
                    <div class="col">
                        <div class="row">
                            <div class="col-12" style="max-height: 800px; overflow-y: auto;">
                                <div class="card shadow mb-4">
                                    <div class="card-header py-3">
                                        <h6 class="m-0 font-weight-bold text-primary">Successful Appointments
                                            History</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive table-striped">
                                            <table class="table table-bordered table-hover">
                                                <thead class="thead-dark">
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Time</th>
                                                    <th>Doctor</th>
                                                    <th>Reason</th>
                                                    <th>Status</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php if (!empty($completedAppointments)): ?>
                                                    <?php foreach ($completedAppointments as $appt): ?>
                                                        <tr class="clickable-row"
                                                            onclick="window.location='appointmentdetails.php?id=<?= urlencode($appt['id']) ?>'">
                                                            <td><?= htmlspecialchars($appt['appointment_date']) ?></td>
                                                            <td><?= htmlspecialchars($appt['appointment_time']) ?></td>
                                                            <td><?= htmlspecialchars($appt['doctor_name']) ?></td>
                                                            <td><?= htmlspecialchars($appt['reason']) ?></td>
                                                            <td><span class="badge badge-success">Completed</span></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted">No completed
                                                            appointments found for this patient.
                                                        </td>
                                                    </tr>
                                                <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>

                                        <?php if (count($completedAppointments) > 10): ?>
                                            <div class="d-flex justify-content-between align-items-center mt-3">
                                                <div class="text-gray-600 small">
                                                    Showing 1 to <?= min(count($completedAppointments), 10) ?>
                                                    of <?= count($completedAppointments) ?> entries
                                                </div>
                                                <a href="#" id="viewMoreBtn"
                                                   class="small text-primary font-weight-bold">View More</a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>

    </div>

</div>
<!-- End of Main Content -->

</div>
<!-- End of Content Wrapper -->

</div>
<!-- End of Page Wrapper -->

<!-- Scroll to Top Button-->
<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

<!-- Logout Modal-->
<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
     aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                <a class="btn btn-primary" href="logout.php">Logout</a>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap core JavaScript-->
<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

<!-- Core plugin JavaScript-->
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>

<!-- Custom scripts for all pages-->
<script src="js/sb-admin-2.min.js"></script>

<!-- Page level plugins -->
<script src="vendor/chart.js/Chart.min.js"></script>

<!-- Page level custom scripts -->
<script src="js/demo/chart-area-demo.js"></script>
<script src="js/demo/chart-pie-demo.js"></script>

<script>
    let selectedOverlayDate = '';
    let selectedOverlayTime = '';
    let selectedAppointmentIndex = -1;

    function selectDate(year, month, day) {
        // Format the date as YYYY-MM-DD for database compatibility
        selectedOverlayDate = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        // Update the hidden input
        document.getElementById('selected_date').value = selectedOverlayDate;
        console.log("Date selected: " + selectedOverlayDate);
    }

    function selectTime(time) {
        selectedOverlayTime = time;
        // Update the hidden input
        document.getElementById('selected_time').value = selectedOverlayTime;
        console.log("Time selected: " + selectedOverlayTime);
    }

    function on(index) {
        selectedAppointmentIndex = index;
        // Set the appointment ID in the hidden input
        document.getElementById('appointment_id').value = index;
        document.getElementById("overlay").style.display = "block";
    }

    function off() {
        if (!selectedOverlayDate || !selectedOverlayTime) {
            alert("Please select both a date and a time.");
            return false;
        }

        // Validate that the selected date is not in the past
        const selectedDate = new Date(selectedOverlayDate);
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        if (selectedDate < today) {
            alert("Cannot schedule appointments in the past.");
            return false;
        }

        // Form will submit naturally
        document.getElementById("overlay").style.display = "none";
        return true;
    }

    function cancelOverlay() {
        document.getElementById("overlay").style.display = "none";
        selectedOverlayDate = '';
        selectedOverlayTime = '';
        selectedAppointmentIndex = -1;
    }
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const items = document.querySelectorAll(".list-group-item");
        const viewMoreBtn = document.getElementById("viewMoreBtn");
        let visibleCount = 10;
        const increment = 5;

        items.forEach((item, index) => {
            if (index >= visibleCount) {
                item.style.display = "none";
            }
        });

        viewMoreBtn?.addEventListener("click", function (e) {
            e.preventDefault();
            let shown = 0;
            for (let i = visibleCount; i < items.length && shown < increment; i++) {
                items[i].style.display = "";
                shown++;
            }
            visibleCount += shown;
            if (visibleCount >= items.length) {
                viewMoreBtn.style.display = "none";
            }
        });
    });
</script>

<script>
    const calendarDays = document.getElementById("calendar-days");
    const monthSelect = document.getElementById("month");
    const yearSelect = document.getElementById("year");

    const today = new Date();

    const months = [
        "January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December"
    ];

    months.forEach((month, i) => {
        const opt = document.createElement("option");
        opt.value = i;
        opt.textContent = month;
        monthSelect.appendChild(opt);
    });

    for (let y = 2020; y <= 2030; y++) {
        const opt = document.createElement("option");
        opt.value = y;
        opt.textContent = y;
        yearSelect.appendChild(opt);
    }

    function renderCalendar(month, year) {
        calendarDays.innerHTML = "";

        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();

        const dayHeaders = ["M", "Tu", "W", "Th", "Fr", "Sa", "Su"];
        dayHeaders.forEach(d => {
            const day = document.createElement("div");
            day.classList.add("header");
            day.textContent = d;
            calendarDays.appendChild(day);
        });

        const startDay = (firstDay + 6) % 7;

        for (let i = 0; i < startDay; i++) {
            const blank = document.createElement("div");
            blank.classList.add("inactive");
            calendarDays.appendChild(blank);
        }

        for (let day = 1; day <= daysInMonth; day++) {
            const dateCell = document.createElement("div");
            dateCell.textContent = day;

            dateCell.addEventListener("click", () => {
                // Remove 'selected' class from all date cells
                document.querySelectorAll("#calendar-days div").forEach(div => {
                    if (!div.classList.contains("header") && !div.classList.contains("inactive")) {
                        div.classList.remove("selected");
                    }
                });
                // Add 'selected' class to the clicked cell
                dateCell.classList.add("selected");
                // Call selectDate function with the date values
                selectDate(year, month, day);
            });

            calendarDays.appendChild(dateCell);
        }
    }

    monthSelect.value = today.getMonth();
    yearSelect.value = today.getFullYear();

    monthSelect.addEventListener("change", () => {
        renderCalendar(+monthSelect.value, +yearSelect.value);
    });

    yearSelect.addEventListener("change", () => {
        renderCalendar(+monthSelect.value, +yearSelect.value);
    });

    renderCalendar(today.getMonth(), today.getFullYear());
</script>

<script>
    const times = [
        "8:30", "9:30", "10:30",
        "11:30", "12:30", "1:30",
        "2:30", "3:30", "4:30"
    ];

    const timeGrid = document.getElementById("timeGrid");

    times.forEach(time => {
        const div = document.createElement("div");
        div.classList.add("time-slot");
        div.textContent = time;
        div.addEventListener("click", () => {
            // Remove 'selected' class from all time cells
            document.querySelectorAll(".time-slot").forEach(el => el.classList.remove("selected"));
            // Add 'selected' class to the clicked cell
            div.classList.add("selected");
            // Call selectTime function with the time value
            selectTime(time);
        });
        timeGrid.appendChild(div);
    });
</script>

<script>
    function searchAppointments() {
        const searchQuery = document.getElementById('searchInput').value.toLowerCase();
        const appointmentCards = document.querySelectorAll('.card.shadow.mb-4');
        const historyItems = document.querySelectorAll('.list-group-item');
        let foundAny = false;

        // Search in current appointment
        appointmentCards.forEach(card => {
            const cardText = card.textContent.toLowerCase();
            if (cardText.includes(searchQuery)) {
                card.style.display = '';
                foundAny = true;
            } else {
                card.style.display = 'none';
            }
        });

        // Search in appointment history
        historyItems.forEach(item => {
            const itemText = item.textContent.toLowerCase();
            if (itemText.includes(searchQuery)) {
                item.style.display = '';
                foundAny = true;
            } else {
                item.style.display = 'none';
            }
        });

        // Show/hide "no results" message
        const noResultsMsg = document.getElementById('noResultsMessage');
        if (!foundAny) {
            if (!noResultsMsg) {
                const msg = document.createElement('div');
                msg.id = 'noResultsMessage';
                msg.className = 'alert alert-info text-center mt-3';
                msg.textContent = 'No appointments found matching your search.';
                document.querySelector('.container-fluid').appendChild(msg);
            }
        } else {
            if (noResultsMsg) {
                noResultsMsg.remove();
            }
        }
    }

    // Add event listener for Enter key
    document.getElementById('searchInput').addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            searchAppointments();
        }
    });

    // Add event listener for input changes to clear results when search is empty
    document.getElementById('searchInput').addEventListener('input', function (e) {
        if (e.target.value === '') {
            // Show all appointments and history items
            document.querySelectorAll('.card.shadow.mb-4, .list-group-item').forEach(item => {
                item.style.display = '';
            });
            // Remove no results message if it exists
            const noResultsMsg = document.getElementById('noResultsMessage');
            if (noResultsMsg) {
                noResultsMsg.remove();
            }
        }
    });
</script>

<!-- Add this before the closing </body> tag -->
<script>
    // Existing scripts...

    // Notification handling
    document.addEventListener('DOMContentLoaded', function () {
        const alertsDropdown = document.getElementById('alertsDropdown');
        const notificationCounter = document.getElementById('notificationCounter');
        const alertsDropdownMenu = document.getElementById('alertsDropdownMenu');

        // Function to reset notification counter
        function resetNotificationCounter() {
            notificationCounter.style.display = 'none';
        }

        // Handle dropdown toggle
        alertsDropdown.addEventListener('click', function (e) {
            e.preventDefault();
            const isOpen = alertsDropdownMenu.classList.contains('show');

            if (isOpen) {
                // If dropdown is open, close it
                alertsDropdownMenu.classList.remove('show');
                alertsDropdown.setAttribute('aria-expanded', 'false');
            } else {
                // If dropdown is closed, open it and reset counter
                alertsDropdownMenu.classList.add('show');
                alertsDropdown.setAttribute('aria-expanded', 'true');
                resetNotificationCounter();
            }
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function (e) {
            if (!alertsDropdown.contains(e.target) && !alertsDropdownMenu.contains(e.target)) {
                alertsDropdownMenu.classList.remove('show');
                alertsDropdown.setAttribute('aria-expanded', 'false');
            }
        });
    });
</script>

<script>
    function markAsSuccessful() {
        // Get appointment ID from URL
        const urlParams = new URLSearchParams(window.location.search);
        const appointmentId = urlParams.get('id');

        if (!appointmentId) return;

        // Store successful appointment IDs in localStorage
        let successfulAppointments = JSON.parse(localStorage.getItem('successfulAppointments') || '[]');
        if (!successfulAppointments.includes(appointmentId)) {
            successfulAppointments.push(appointmentId);
            localStorage.setItem('successfulAppointments', JSON.stringify(successfulAppointments));
        }

        alert("Appointment marked as successful!");
        location.reload(); // Reload to update the UI
    }
</script>

<script>
    // Run this on page load to filter the appointment history
    document.addEventListener("DOMContentLoaded", function () {
        // Get successful appointment IDs from localStorage
        const successfulAppointments = JSON.parse(localStorage.getItem('successfulAppointments') || '[]');

        // Update appointment history title
        const historyTitle = document.querySelector('.card-header h6');
        if (historyTitle) {
            historyTitle.textContent = 'Successful Appointments History';
        }

        // Hide non-successful appointments in history
        const historyItems = document.querySelectorAll('.list-group-item');
        let visibleCount = 0;

        historyItems.forEach(item => {
            // If we have the appointment ID, we can check if it's in our successful list
            const appointmentId = item.dataset.appointmentId; // You may need to add this data attribute
            if (!successfulAppointments.includes(appointmentId)) {
                item.style.display = 'none';
            } else {
                visibleCount++;
            }
        });

        // Show message if no successful appointments
        if (visibleCount === 0) {
            const historyContainer = document.querySelector('.list-group');
            if (historyContainer) {
                historyContainer.innerHTML = '<div class="text-center text-muted py-3">No successful appointments found.</div>';
            }
        }
    });
</script>

</body>

</html>