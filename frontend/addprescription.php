<?php
// Add session_start at the beginning
session_start();

require_once '../backend/config/database.php';
require_once '../backend/models/Prescription.php';
require_once '../backend/models/Patient.php';
require_once '../backend/models/Staff.php';

// Initialize database connection
$database = getDbConnection();
$prescriptionModel = new Prescription($database);
$patientModel = new Patient($database);
$staffModel = new Staff($database);

// Initialize variables for form errors and success message
$error = '';
$message = '';
$prescription_id = null;

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $medicineType = $_POST['type'] ?? '';
    $medicineName = $_POST['medicineName'] ?? '';
    $genericName = $_POST['genericName'] ?? '';
    $dailyDosage = $_POST['dailyDosage'] ?? '';
    $unit = $_POST['unit'] ?? '';
    $frequency = $_POST['frequency'] ?? '';
    $duration = $_POST['duration'] ?? '';
    $additionalInstructions = $_POST['additionalInstructions'] ?? '';
    $mealTiming = $_POST['mealTiming'] ?? '';
    $durationUnit = $_POST['durationUnit'] ?? '';

    // Format duration with unit
    $formattedDuration = $duration . ' ' . $durationUnit;

    // Get patient and staff information
    $patient_name = $_GET['patient'] ?? '';
    $doctor_name = $_GET['doctor'] ?? '';

    // Find patient_id from name
    $patient_parts = explode(' ', $patient_name, 2);
    $first_name = $patient_parts[0] ?? '';
    $last_name = $patient_parts[1] ?? '';
    $patients = $patientModel->searchByName($first_name, $last_name);
    $patient_id = $patients[0]['id'] ?? null;

    // Find staff_id from name
    $doctor_parts = explode(' ', $doctor_name, 2);
    $staff_first_name = $doctor_parts[0] ?? '';
    $staff_last_name = $doctor_parts[1] ?? '';
    $staff = $staffModel->searchByName($staff_first_name, $staff_last_name);
    $staff_id = $staff[0]['id'] ?? null;

    // Check if patient and staff were found
    if (empty($patient_id)) {
//        $error = "Patient not found. Please select a valid patient.";
        $patient_id = 1; // Use the ID of Lance Limbaro from your database
    } elseif (empty($staff_id)) {
//        $error = "Doctor/Staff not found. Please select a valid doctor.";
        $staff_id = 1; // Use the ID of Lance Limbaro from your database
    } else {
        // Format instructions to include meal timing
        $instructions = '';
        if (!empty($frequency)) {
            $instructions = "Take " . $dailyDosage . " " . $unit . " " . $frequency;

            if (!empty($mealTiming)) {
                $instructions .= " " . $mealTiming;
            }
        }

// Add additional instructions if any
        if (!empty($additionalInstructions)) {
            $instructions .= ". " . $additionalInstructions;
        }

// Prepare the data for the database
        $prescriptionData = [
            'patient_id' => $patient_id,
            'staff_id' => $staff_id,
            'medication_name' => $medicineName,
            'dosage' => $dailyDosage,
            'unit' => $unit,
            'frequency' => $frequency,
            'duration' => $formattedDuration,
            'instructions' => $instructions,
            'status' => 'active',
            'additional_instruction' => $additionalInstructions
        ];

        // In addprescription.php right after calling create:
        $result = $prescriptionModel->create($prescriptionData);

        if ($result['success']) {
            $prescription_id = $result['id'];
            $message = "Prescription saved successfully.";
        } else {
            // Enhanced error reporting
            $error = "Error saving prescription: " . ($result['error'] ?? "Unknown error");
            
            // Log detailed information
            error_log("Prescription error: " . print_r($result, true));
            
            // Add debugging details to display
            echo "<div class='alert alert-danger'>";
            echo "<h4>Debug Information:</h4>";
            echo "<p>Error: " . htmlspecialchars($result['error'] ?? 'Unknown error') . "</p>";
            echo "<pre>";
            echo "Data sent to database:\n";
            print_r($prescriptionData);
            echo "\n\nPatient search results:\n";
            print_r($patients);
            echo "\n\nStaff search results:\n";
            print_r($staff);
            echo "</pre>";
            echo "</div>";
        }
    }
}

// Get prefilled data from URL parameters
$prefill = [
    'patient_name' => isset($_GET['patient']) ? urldecode($_GET['patient']) : '',
    'doctor' => isset($_GET['doctor']) ? urldecode($_GET['doctor']) : '',
    'appointment_date' => isset($_GET['date']) ? urldecode($_GET['date']) : ''
];
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>DermaGrid - Add Prescription</title>

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link
            href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
            rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <style>
        textarea::-webkit-scrollbar {
            width: 6px;
        }

        textarea::-webkit-scrollbar-thumb {
            background-color: #d1d5db;
            border-radius: 3px;
        }

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

        .btn-fade {
            background-color: rgb(153, 182, 255);
            color: white;
            border: 1px solid #4a73df;
        }
    </style>

    <style>
        .btn.active {
            background-color: #0d6efd;
            color: white;
            border-color: #0a58ca;
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
            <!-- <div class="sidebar-brand-icon rotate-n-15">
                <i class="fas fa-laugh-wink"></i>
            </div> -->
            <div class="sidebar-brand-text">DermaGrid</div>
        </a>

        <!-- Divider -->
        <hr class="sidebar-divider my-0">

        <!-- Nav Item - Dashboard -->
        <li class="nav-item ">
            <a class="nav-link" href="dashboard.php">
                <i class="fas fa-fw fa-tachometer-alt"></i>
                <span>Dashboard</span></a>
        </li>

        <!-- Divider -->
        <hr class="sidebar-divider">

        <!-- Heading -->
        <div class="sidebar-heading">
            Project Management
        </div>

        <!-- Nav Item - Charts -->
        <li class="nav-item">
            <a class="nav-link" href="appointments.php">
                <i class="bi bi-person"></i>
                <span>Appointments</span></a>
        </li>

        <!-- Divider -->
        <hr class="sidebar-divider">

        <!-- Heading -->
        <div class="sidebar-heading">
            Staff Management
        </div>

        <!-- Nav Item - Tables -->
        <li class="nav-item">
            <a class="nav-link" href="doctors&staff.php">
                <i class="bi bi-people"></i>
                <span>Doctors & Staff</span></a>
        </li>

        <!-- Divider -->
        <hr class="sidebar-divider d-none d-md-block">

        <!-- Sidebar Toggler (Sidebar) -->
        <div class="text-center d-none d-md-inline">
            <button class="rounded-circle border-0" id="sidebarToggle"></button>
        </div>

    </ul>
    <!-- End of Sidebar -->

    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">

        <!-- Main Content -->
        <div id="content">

            <!-- Include the common header with logout functionality -->
            <?php 
            // Add debugging to check if file exists
            $headerFile = 'includes/header.php';
            if (file_exists($headerFile)) {
                include $headerFile;
            } else {
                echo "<!-- Header file not found at: $headerFile -->";
                // Use a basic heading if header file is missing
                echo '<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">';
                echo '<h1 class="h3 m-auto text-gray-800">Add Prescription</h1>';
                echo '</nav>';
            }
            ?>

            <!-- Begin Page Content -->
            <div class="container-fluid">

                <!-- Content Row -->
                <div class="row">

                    <!-- Content Column -->
                    <div class="col">

                        <main class="bg-white w-100" style="padding: 1.5rem;">
                            <p class="text-muted mb-1" style="font-size: 13px;">Add Medicine</p>
                            <h1 class="fw-bold border-bottom border-dark pb-2 mb-4" style="font-size: 16px;">Medicine
                                Name</h1>

                            <?php if (!empty($error)): ?>
                                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                            <?php endif; ?>

                            <?php if (!empty($message)): ?>
                                <div class="alert alert-success">
                                    <?= htmlspecialchars($message) ?>
                                    <?php if ($prescription_id): ?>
                                        <p>Prescription ID: <?= htmlspecialchars($prescription_id) ?></p>
                                        <?php if (isset($_GET['return']) && $_GET['return'] === 'medical'): ?>
                                            <p>
                                                <a href="addmedicalrecords.php?patient=<?= urlencode($prefill['patient_name']) ?>&doctor=<?= urlencode($prefill['doctor']) ?>&date=<?= urlencode($prefill['appointment_date']) ?>&prescription=<?= $prescription_id ?>"
                                                   class="btn btn-primary btn-sm">Return to Medical Record</a></p>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <form class="row g-4" method="post" action="">
                                <!-- Left side: Basic Instructions -->
                                <section
                                        class="col-md-6 border-bottom border-md-bottom-0 border-md-end pb-4 pb-md-0 pe-md-4">
                                    <h2 class="fw-bold mb-3" style="font-size: 13px;">Basic Instructions</h2>

                                    <div class="mb-3">
                                        <label for="type" class="form-label fw-bold"
                                               style="font-size: 11px;">Type</label>
                                        <input id="type" name="type" type="text" placeholder="e.g., Antibiotic"
                                               class="form-control form-control-sm bg-light border-0" required/>
                                    </div>

                                    <div class="mb-3">
                                        <label for="medicineName" class="form-label fw-bold" style="font-size: 11px;">Medicine
                                            Name</label>
                                        <input id="medicineName" name="medicineName" type="text"
                                               placeholder="e.g., Amoxicillin"
                                               class="form-control form-control-sm bg-light border-0" required/>
                                    </div>

                                    <div class="mb-3">
                                        <label for="genericName" class="form-label fw-bold" style="font-size: 11px;">Generic
                                            Name</label>
                                        <input id="genericName" name="genericName" type="text"
                                               placeholder="e.g., Penicillin"
                                               class="form-control form-control-sm bg-light border-0"/>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold" style="font-size: 11px;">Patient</label>
                                        <input type="text" class="form-control form-control-sm bg-light text-muted"
                                               value="<?php echo isset($prefill['patient_name']) ? htmlspecialchars($prefill['patient_name']) : ''; ?>"
                                               readonly/>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold" style="font-size: 11px;">Doctor</label>
                                        <input type="text" class="form-control form-control-sm bg-light text-muted"
                                               value="<?php echo isset($prefill['doctor']) ? htmlspecialchars($prefill['doctor']) : ''; ?>"
                                               readonly/>
                                    </div>
                                </section>

                                <!-- Right side: Intake Instructions -->
                                <section class="col-md-6 ps-md-4">
                                    <h2 class="fw-bold mb-3" style="font-size: 13px;">Intake Instructions</h2>

                                    <div class="row g-3 mb-3">
                                        <div class="col-6">
                                            <label for="dailyDosage" class="form-label fw-bold"
                                                   style="font-size: 11px;">Daily Dosage</label>
                                            <input id="dailyDosage" name="dailyDosage" type="text" placeholder="e.g., 2"
                                                   class="form-control form-control-sm bg-light border-0" required/>
                                        </div>
                                        <div class="col-6">
                                            <label for="unit" class="form-label fw-bold"
                                                   style="font-size: 11px;">Unit</label>
                                            <select id="unit" name="unit"
                                                    class="form-select form-select-sm bg-light border-0" required>
                                                <option value="drop(s)">drop(s)</option>
                                                <option value="tab(s)">tab(s)</option>
                                                <option value="puff(s)">puff(s)</option>
                                                <option value="ml">ml</option>
                                                <option value="iu">iu</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Frequency -->
                                    <div class="mb-3">
                                        <label for="frequency" class="form-label fw-bold" style="font-size: 11px;">Frequency</label>
                                        <input id="frequency" name="frequency" type="text"
                                               placeholder="e.g., every 6 hours"
                                               class="form-control form-control-sm bg-light border-0" required/>
                                    </div>

                                    <!-- Meal Timing Buttons -->
                                    <div class="mb-3">
                                        <input type="hidden" name="mealTiming" id="mealTiming"/>
                                        <label class="form-label fw-bold d-block" style="font-size: 11px;">Meal
                                            Timing</label>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-outline-primary btn-sm meal-btn">With
                                                meal
                                            </button>
                                            <button type="button" class="btn btn-outline-primary btn-sm meal-btn">After
                                                meal
                                            </button>
                                            <button type="button" class="btn btn-outline-primary btn-sm meal-btn">Before
                                                meal
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Duration with Buttons -->
                                    <div class="row g-2 align-items-center mb-3">
                                        <div class="col-auto">
                                            <label for="duration" class="form-label fw-bold" style="font-size: 11px;">Duration</label>
                                        </div>
                                        <div class="col">
                                            <input id="duration" name="duration" type="text" placeholder="e.g., 7"
                                                   class="form-control form-control-sm bg-light border-0" required/>
                                        </div>
                                        <div class="col-auto">
                                            <input type="hidden" name="durationUnit" id="durationUnit" value="Days"/>
                                            <div class="btn-group" role="group">
                                                <button type="button"
                                                        class="btn btn-outline-primary btn-sm duration-btn active">Days
                                                </button>
                                                <button type="button"
                                                        class="btn btn-outline-primary btn-sm duration-btn">Weeks
                                                </button>
                                                <button type="button"
                                                        class="btn btn-outline-primary btn-sm duration-btn">Months
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="additionalInstructions" class="form-label fw-bold"
                                               style="font-size: 11px;">Additional Instructions</label>
                                        <input id="additionalInstructions" name="additionalInstructions" type="text"
                                               placeholder="Any other notes..."
                                               class="form-control form-control-sm bg-light border-0"/>
                                    </div>

                                    <button type="submit" class="btn btn-blue text-white hover-blue"
                                            style="font-size: 12px;">Save
                                    </button>

                                    <?php if (isset($_GET['return']) && $_GET['return'] === 'medical'): ?>
                                        <a href="addmedicalrecords.php?patient=<?= urlencode($prefill['patient_name']) ?>&doctor=<?= urlencode($prefill['doctor']) ?>&date=<?= urlencode($prefill['appointment_date']) ?>"
                                           class="btn btn-fade text-white hover-blue" style="font-size: 12px;">
                                            <i class="fas fa-arrow-left"></i> Back to Medical Record
                                        </a>
                                    <?php else: ?>
                                        <a href="javascript:history.go(-1)" class="btn btn-fade text-white hover-blue"
                                           style="font-size: 12px;">
                                            <i class="fas fa-arrow-left"></i> Back
                                        </a>
                                    <?php endif; ?>
                                </section>
                            </form>
                        </main>

                    </div>


                </div>

            </div>
        </div>

    </div>
    <!-- /.container-fluid -->

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
    // Initialize default value for duration unit
    document.getElementById('durationUnit').value = "Days";

    // Handle meal buttons
    document.querySelectorAll('.meal-btn').forEach(button => {
        button.addEventListener('click', function () {
            document.getElementById('mealTiming').value = this.textContent.trim();
            document.querySelectorAll('.meal-btn').forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // Handle duration buttons
    document.querySelectorAll('.duration-btn').forEach(button => {
        button.addEventListener('click', function () {
            document.getElementById('durationUnit').value = this.textContent.trim();
            document.querySelectorAll('.duration-btn').forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
        });
    });
</script>

</body>

</html>