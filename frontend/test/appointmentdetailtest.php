<?php
$appointments = [];
$history = [];
$latestByPatient = [];

// Get the contact number from the URL (sanitize it)
$contact_number = isset($_GET['contact']) ? $_GET['contact'] : null;

$file = "appointments.txt";

if (file_exists($file)) {
    $lines = file($file, FILE_IGNORE_NEW_LINES);
    foreach ($lines as $index => $line) {
        $fields = explode("|", $line);
        if (count($fields) >= 10) {
            // Use patient contact as the key for each unique patient
            $key = $fields[2];

            if ($contact_number && $key !== $contact_number) {
                continue;  // Skip if the contact doesn't match
            }

            $appointment = [
                'index' => $index,
                'first_name' => $fields[0],   // First name
                'last_name' => $fields[1],    // Last name
                'contact' => $fields[2],      // Contact number
                'email' => $fields[3],        // Email address
                'appointment_date' => $fields[4],   // Appointment date
                'appointment_time' => $fields[5],   // Appointment time
                'doctor' => $fields[6],       // Doctor's name
                'reason' => $fields[8],       // Reason for appointment
                'booked_on' => $fields[9],    // Date when the appointment was booked
                'raw' => $line,               // Raw line from file
                'status' => 'Upcoming'        // Default status
            ];

            // Update status based on appointment date
            $appointmentDate = strtotime($appointment['appointment_date']);
            $currentDate = strtotime(date('Y-m-d'));
            
            if ($appointmentDate < $currentDate) {
                $appointment['status'] = 'Completed';
            } else if ($appointmentDate == $currentDate) {
                $appointment['status'] = 'Today';
            } else {
                $appointment['status'] = 'Upcoming';
            }

            // Check if this is the most recent appointment for this patient
            if (!isset($latestByPatient[$key]) || strtotime($appointment['booked_on']) > strtotime($latestByPatient[$key]['booked_on'])) {
                // If this is the latest appointment, move the previous one to history
                if (isset($latestByPatient[$key])) {
                    $history[] = $latestByPatient[$key];
                }
                $latestByPatient[$key] = $appointment; // This appointment becomes the most recent
            } else {
                // This is not the latest, so add it to history
                $history[] = $appointment;
            }
        }
    }

    // Now $appointments holds the latest appointment per patient
    $appointments = array_values($latestByPatient);

    // Sort history array by appointment date and time in descending order
    usort($history, function($a, $b) {
        $dateA = strtotime($a['appointment_date'] . ' ' . $a['appointment_time']);
        $dateB = strtotime($b['appointment_date'] . ' ' . $b['appointment_time']);
        return $dateB - $dateA; // Descending order (latest first)
    });
}
?>

<?php
$patientName = urlencode($appointment['first_name'] . ' ' . $appointment['last_name']);
$doctor = urlencode($appointment['doctor']);
$date = urlencode($appointment['appointment_date']);
$contact = urlencode($appointment['contact']); // get from appointment array
$link = "addmedicalrecords.php?patient=$patientName&doctor=$doctor&date=$date&contact=$contact";
$preslink = "addprescription.php?patient=$patientName&doctor=$doctor&date=$date&contact=$contact";
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
        .hover-blue:hover {
            background-color: #0130a7;
            border-color: #0130a7;
            color: white;
        }

        .btn-blue {
            background-color: #4a73df;
            color: white;
            border: 1px solid #4a73df;
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
                <a class="nav-link" href="doctors&staff.html">
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
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-center">
                        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
                    </div>

                    <!-- Topbar Search -->
                    <form class="d-none d-sm-inline-block form-inline ml-auto my-2 my-md-0 mw-100 navbar-search" onsubmit="return false;">
                        <div class="input-group">
                            <input type="text" class="form-control bg-light border-0 small" placeholder="Search appointments..."
                                aria-label="Search" aria-describedby="basic-addon2" id="searchInput">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="button" onclick="searchAppointments()">
                                    <i class="fas fa-search fa-sm"></i>
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav">

                        <li class="nav-item dropdown no-arrow d-sm-none">
                            <a class="nav-link dropdown-toggle" href="#" id="searchDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-search fa-fw"></i>
                            </a>
                            <!-- Dropdown - Messages -->
                            <div class="dropdown-menu dropdown-menu-right p-3 shadow animated--grow-in"
                                aria-labelledby="searchDropdown">
                                <form class="form-inline mr-auto w-100 navbar-search">
                                    <div class="input-group">
                                        <input type="text" class="form-control bg-light border-0 small"
                                            placeholder="Search for..." aria-label="Search"
                                            aria-describedby="basic-addon2">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="button">
                                                <i class="fas fa-search fa-sm"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </li>

                        <!-- Nav Item - Alerts -->
                        <li class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-bell fa-fw"></i>
                                <!-- Counter - Alerts -->
                                <span class="badge badge-danger badge-counter" id="notificationCounter">3+</span>
                            </a>
                            <!-- Dropdown - Alerts -->
                            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="alertsDropdown" id="alertsDropdownMenu">
                                <h6 class="dropdown-header">
                                    Alerts Center
                                </h6>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-primary">
                                            <i class="fas fa-file-alt text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="small text-gray-500">December 12, 2019</div>
                                        <span class="font-weight-bold">A new monthly report is ready to download!</span>
                                    </div>
                                </a>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-success">
                                            <i class="fas fa-donate text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="small text-gray-500">December 7, 2019</div>
                                        $290.29 has been deposited into your account!
                                    </div>
                                </a>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-warning">
                                            <i class="fas fa-exclamation-triangle text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="small text-gray-500">December 2, 2019</div>
                                        Spending Alert: We've noticed unusually high spending for your account.
                                    </div>
                                </a>
                                <a class="dropdown-item text-center small text-gray-500" href="#">Show All Alerts</a>
                            </div>
                        </li>

                        <div class="topbar-divider d-none d-sm-block"></div>

                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
<!-- code here              <?php echo htmlspecialchars($_SESSION["first_name"]); ?> -->
                                <img class="img-profile rounded-circle" src="img/undraw_profile.svg">
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="profile.php">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Profile
                                </a>
                                <!-- <a class="dropdown-item" href="#">
                                    <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Settings
                                </a> -->
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Logout
                                </a>
                            </div>
                        </li>

                    </ul>

                </nav>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Content Row -->
                    <div class="row">

                        <div id="overlay" onclick="off()">
                            <div class="container" onclick="event.stopPropagation();">
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
                                        <button type="button" class="actn-btn" onclick="off()">
                                            Make Appointment
                                        </button>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Content Column -->
                        <div class="col">

                            <?php if (!empty($appointments)): ?>
                                <?php $a = $appointments[0]; ?>

                                <!-- Appointment Details -->
                                <div class="card shadow mb-4">
                                    <div class="card-header py-3">
                                        <div class="row px-3">
                                            <h6 class="m-0 font-weight-bold text-primary">Appointment</h6>
                                            <h6 class="ml-auto mb-0 font-weight-bold text--bs-dark-bg-subtle">
                                                Status: <span class="text-primary"><?= $a['status'] ?></span>
                                            </h6>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="container">
                                            <div class="row row-cols-4 small">
                                                <div class="col pb-3 pr-3">
                                                    <div class="row">Booked On</div>
                                                    <div class="row font-weight-bold text-primary"><?= $a['booked_on'] ?></div>
                                                </div>
                                                <div class="col pr-3">
                                                    <div class="row">Appointment Date</div>
                                                    <div class="row font-weight-bold text-primary"><?= $a['appointment_date'] ?></div>
                                                </div>
                                                <div class="col pr-3">
                                                    <div class="row">Appointment Time</div>
                                                    <div class="row font-weight-bold text-primary"><?= $a['appointment_time'] ?></div>
                                                </div>
                                                <div class="col pr-3">
                                                    <div class="row">Doctor Assigned</div>
                                                    <div class="row font-weight-bold text-primary"><?= $a['doctor'] ?></div>
                                                </div>
                                                <div class="col">
                                                    <div class="row">Reason for Visit</div>
                                                    <div class="row font-weight-bold text-primary"><?= $a['reason'] ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer py-3">
                                        <div class="row px-3">
                                            <button type="button" onclick="on(<?= $a['index'] ?>)" class="btn btn-blue text-white hover-blue" style="font-size: 12px;">
                                                Reschedule
                                            </button>
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
                                            <div class="col pr-3">
                                                <div class="row">First Name</div>
                                                <div class="row font-weight-bold text-primary"><?= htmlspecialchars($a['first_name']) ?></div>
                                            </div>
                                            <div class="col pr-3">
                                                <div class="row">Last Name</div>
                                                <div class="row font-weight-bold text-primary"><?= htmlspecialchars($a['last_name']) ?></div>
                                            </div>
                                            <div class="col pr-3">
                                                <div class="row">Contact Number</div>
                                                <div class="row font-weight-bold text-primary"><?= htmlspecialchars($a['contact']) ?></div>
                                            </div>
                                            <div class="col pr-3">
                                                <div class="row">Email Address</div>
                                                <div class="row font-weight-bold text-primary"><?= htmlspecialchars($a['email']) ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="row" style="gap: 10px;">

                                <!-- Medical Records -->
                                <div class="col card shadow mb-4 p-0">
                                    <div class="p-2">
                                        <p class="small text-black fw-semibold border-bottom border-black pb-2 mb-3">Medical Records
                                            <span>
                                                <a href="<?php echo $link; ?>" title="Add Medical Record">
                                                    <i class="bi bi-plus"></i>
                                                </a>
                                            </span>
                                        </p>

                                        <?php
                                        $file = "medical_data.txt";
                                        $target_contact = $_GET['contact'] ?? null;

                                        if (file_exists($file)) {
                                            $records = file($file, FILE_IGNORE_NEW_LINES);
                                            $found = false;

                                            foreach ($records as $index => $record) {
                                                // Extract contact number from record
                                                preg_match('/Contact:\s*(.*?)\s*\|/', $record . ' |', $match);
                                                $contact = trim($match[1] ?? '');

                                                if ($target_contact && $contact !== $target_contact) {
                                                    continue;
                                                }

                                                // Extract Diagnosis
                                                preg_match('/Diagnosis:\s*(.*?)\s*\|/', $record . ' |', $matches);
                                                $diagnosis = trim($matches[1] ?? 'Unknown Diagnosis');

                                                if (!$found) {
                                                    echo '<div class="d-flex flex-column gap-2">';
                                                    $found = true;
                                                }

                                                echo '
        <a href="viewmedical.php?record=' . $index . '" style="text-decoration: none;">
            <button type="button"
                class="btn-blue hover-blue d-flex align-items-center p-3 border-0 w-100 text-start"
                style="cursor: pointer;">
                <div class="d-flex justify-content-center align-items-center bg-secondary me-3"
                    style="width: 40px; height: 40px;">
                    <img src="https://storage.googleapis.com/a1aa/image/e859548d-40b7-4237-dafc-f172c8de247a.jpg"
                        alt="Prescription icon" width="16" height="16" />
                </div>
                <p class="mb-0 text-white small flex-grow-1">' . htmlspecialchars($diagnosis) . '</p>
                <i class="fas fa-chevron-right text-white"></i>
            </button>
        </a>';
                                            }

                                            if ($found) {
                                                echo '</div>';
                                            } else {
                                                echo "<p>No medical records found for this patient.</p>";
                                            }
                                        } else {
                                            echo "<p>No medical records file found.</p>";
                                        }
                                        ?>
                                    </div>
                                </div>

                                <!-- Prescription -->
                                <div class="col card shadow mb-4 p-0">
                                    <div class="p-2" style="max-height: 200px; overflow-y: auto;">
                                        <p class="small text-black fw-semibold border-bottom border-black pb-2 mb-3">Prescription
                                            <span>
                                                <a href="<?php echo $preslink; ?>" title="Add Medical Record">
                                                    <i class="bi bi-plus"></i>
                                                </a>
                                            </span>
                                        </p>

                                        <?php
                                        $file2 = "prescriptions.txt";

                                        if (file_exists($file2)) {
                                            $records = file($file2, FILE_IGNORE_NEW_LINES);
                                            if (count($records) > 0) {
                                                echo '<div class="d-flex flex-column gap-2">';
                                                foreach ($records as $index => $record) {
                                                    // Extract Diagnosis
                                                    preg_match('/Medicine Name:\s*(.*?)\s*\|/', $record . ' |', $matches);
                                                    $medicine_name = $matches[1] ?? 'Unknown Medicine';

                                                    echo '
                                            <a href="viewpres.php?record=' . $index . '" style="text-decoration: none;">
                                                <button type="button"
                                                    class="btn-blue hover-blue d-flex align-items-center p-3 border-0 w-100 text-start"
                                                    style="cursor: pointer;">
                                                    <div class="d-flex justify-content-center align-items-center bg-secondary me-3"
                                                        style="width: 40px; height: 40px;">
                                                        <img src="https://storage.googleapis.com/a1aa/image/e859548d-40b7-4237-dafc-f172c8de247a.jpg"
                                                            alt="Prescription icon" width="16" height="16" />
                                                    </div>
                                                    <p class="mb-0 text-white small flex-grow-1">' . htmlspecialchars($medicine_name) . '</p>
                                                    <i class="fas fa-chevron-right text-white"></i>
                                                </button>
                                            </a>';
                                                }
                                                echo '</div>';
                                            } else {
                                                echo "<p>No medical records found.</p>";
                                            }
                                        } else {
                                            echo "<p>No medical records file found.</p>";
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- Appointment History -->
                        <div class="col">
                            <div class="row">
                                <div class="col-12" style="max-height: 800px; overflow-y: auto;">
                                    <div class="card shadow mb-4">
                                        <div class="card-header py-3 border-bottom">
                                            <h6 class="m-0 font-weight-bold text-primary">Successful Appointments History</h6>
                                        </div>
                                        <div class="card-body p-3">
                                            <div class="list-group list-group-flush small">
                                                <?php 
                                                // Filter history to only show completed appointments
                                                $filteredHistory = array_filter($history, function($h) { 
                                                    return $h['status'] === 'completed'; 
                                                });
                                                
                                                if (!empty($filteredHistory)): 
                                                ?>
                                                    <?php foreach ($filteredHistory as $h): ?>
                                                        <div class="list-group-item d-flex justify-content-between align-items-center px-0 border-0 pb-2">
                                                            <div>
                                                                <div class="text-muted"><?= htmlspecialchars($h['appointment_date']) ?></div>
                                                                <div class="font-weight-bold"><?= htmlspecialchars($h['appointment_time']) ?></div>
                                                                <div class="small text-muted">Dr. <?= htmlspecialchars($h['staff_name'] ?? $h['doctor_name'] ?? $h['doctor'] ?? '') ?></div>
                                                            </div>
                                                            <div class="text-right">
                                                                <button type="" class="btn btn-blue text-white hover-blue mb-2" style="font-size: 12px;">
                                                                    <?= htmlspecialchars($h['reason']) ?>
                                                                </button>
                                                                <div class="small text-muted">
                                                                    Status: <span class="font-weight-bold successful-status">Completed</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <div class="text-center text-muted py-3">No successful appointments found.</div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php if (count($filteredHistory) > 10): ?>
                                            <div class="card-footer text-center border-top-0">
                                                <a href="#" id="viewMoreBtn" class="small text-primary font-weight-bold">View More</a>
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
                    <a class="btn btn-primary" href="login.html">Logout</a>
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
            selectedOverlayDate = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        }

        function selectTime(time) {
            selectedOverlayTime = time;
        }

        function on(index) {
            selectedAppointmentIndex = index;
            document.getElementById("overlay").style.display = "block";
        }

        function off() {
            if (!selectedOverlayDate || !selectedOverlayTime) {
                alert("Please select both a date and a time.");
                return;
            }

            // Validate that the selected date is not in the past
            const selectedDate = new Date(selectedOverlayDate);
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            if (selectedDate < today) {
                alert("Cannot schedule appointments in the past.");
                return;
            }

            fetch("update_booked_on.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: `index=${selectedAppointmentIndex}&new_date=${selectedOverlayDate}&new_time=${selectedOverlayTime}`
                })
                .then(res => {
                    if (res.ok) {
                        location.reload(); // Reload the page to reflect the changes
                    } else {
                        res.text().then(msg => {
                            console.error("Server response:", msg);
                            alert("Failed to update: " + msg);
                        });
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                    alert("An error occurred while updating the appointment.");
                });

            document.getElementById("overlay").style.display = "none";
        }

        function cancelOverlay() {
            document.getElementById("overlay").style.display = "none";
            selectedOverlayDate = '';
            selectedOverlayTime = '';
            selectedAppointmentIndex = -1;
        }
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const items = document.querySelectorAll(".list-group-item");
            const viewMoreBtn = document.getElementById("viewMoreBtn");
            let visibleCount = 10;
            const increment = 5;

            items.forEach((item, index) => {
                if (index >= visibleCount) {
                    item.style.display = "none";
                }
            });

            viewMoreBtn?.addEventListener("click", function(e) {
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
                    document.querySelectorAll(".days div").forEach(div => div.classList.remove("selected"));
                    dateCell.classList.add("selected");
                    selectDate(year, month, day);
                    console.log("Selected date:", selectedOverlayDate);
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
                document.querySelectorAll(".time-slot").forEach(el => el.classList.remove("selected"));
                div.classList.add("selected");
                selectTime(time);
                console.log("Selected time:", selectedOverlayTime);
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
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                searchAppointments();
            }
        });

        // Add event listener for input changes to clear results when search is empty
        document.getElementById('searchInput').addEventListener('input', function(e) {
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
        document.addEventListener('DOMContentLoaded', function() {
            const alertsDropdown = document.getElementById('alertsDropdown');
            const notificationCounter = document.getElementById('notificationCounter');
            const alertsDropdownMenu = document.getElementById('alertsDropdownMenu');

            // Function to reset notification counter
            function resetNotificationCounter() {
                notificationCounter.style.display = 'none';
            }

            // Handle dropdown toggle
            alertsDropdown.addEventListener('click', function(e) {
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
            document.addEventListener('click', function(e) {
                if (!alertsDropdown.contains(e.target) && !alertsDropdownMenu.contains(e.target)) {
                    alertsDropdownMenu.classList.remove('show');
                    alertsDropdown.setAttribute('aria-expanded', 'false');
                }
            });
        });
    </script>

</body>

</html>