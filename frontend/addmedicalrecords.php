<?php
require_once '../backend/config/database.php';
require_once '../backend/models/MedicalRecord.php';
require_once '../backend/models/Patient.php';
require_once '../backend/models/Staff.php';

// Initialize database connection
$database = getDbConnection();
$medicalRecord = new MedicalRecord($database);
$patientModel = new Patient($database);
$staffModel = new Staff($database);

// Initialize variables for form errors and success message
$error = '';
$message = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $patient_name = $_POST['patient_name'] ?? ($_GET['patient'] ?? '');
    $doctor_name = $_POST['doctor'] ?? ($_GET['doctor'] ?? '');
    $appointment_date = $_POST['appointment_date'] ?? ($_GET['date'] ?? date('Y-m-d'));
    
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
    
    // Prepare data for database
    $data = [
        'patient_id' => $patient_id,
        'staff_id' => $staff_id,
        'appointment_id' => null, // Would need to look up appointment ID
        'visit_date' => $appointment_date,
        'diagnosis' => $_POST['diagnosis'] ?? null,
        'treatment_plan' => $_POST['recommended_treatment'] ?? null,
        'notes' => $_POST['clinical_notes'] ?? null,
        'prescription_id' => $_POST['prescriptions_given'] ? intval($_POST['prescriptions_given']) : null, // Would need to link to prescription
        'chief_complaint' => $_POST['chief_complaint'] ?? null,
        'skin_type' => $_POST['skin_type'] ?? null,
        'instructions' => $_POST['instructions'] ?? null,
        'image_path' => null // Would handle file upload separately
    ];
    
    // Validate required fields
    if (empty($patient_id)) {
//        $error = "Patient not found. Please select a valid patient.";
        $patient_id = 1; // Use the ID of Lance Limbaro from your database
    } elseif (empty($staff_id)) {
//        $error = "Doctor/Staff not found. Please select a valid doctor.";
        $staff_id = 1; // Use the ID of Lance Limbaro from your database
    } else {
        // Save the medical record
        $result = $medicalRecord->create($data);
        
        if ($result['success']) {
            $message = "Medical record added successfully.";
        } else {
            $error = "Error saving medical record: " . ($result['error'] ?? "Unknown error");
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

    <title>DermaGrid - Add Medical</title>

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
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

        .btn-blue1 {
            background-color: #7aa0ff;
            color: white;
            border: 1px solid #7aa0ff;
        }

        .btn-fade {
            background-color: rgb(153, 182, 255);
            color: white;
            border: 1px solid #4a73df;
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

                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-center">
                        <h1 class="h3 mb-0 text-gray-800">Add Medical</h1>
                    </div>

                    <!-- Topbar Search -->
                    <form class="d-none d-sm-inline-block form-inline ml-auto my-2 my-md-0 mw-100 navbar-search">
                        <div class="input-group">
                            <input type="text" class="form-control bg-light border-0 small" placeholder="Search for..."
                                aria-label="Search" aria-describedby="basic-addon2">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="button">
                                    <i class="fas fa-search fa-sm"></i>
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav">

                        <!-- Nav Item - Search Dropdown (Visible Only XS) -->
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
                                <span class="badge badge-danger badge-counter">3+</span>
                            </a>
                            <!-- Dropdown - Alerts -->
                            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="alertsDropdown">
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
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                                    <!-- <?php echo htmlspecialchars($_SESSION["first_name"] ?? ''); ?> -->
                                </span>
                                <img class="img-profile rounded-circle" src="img/undraw_profile.svg">
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="profile.php">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Profile
                                </a>
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

                        <!-- Content Column -->
                        <div class="col">

                            <!-- add medical-->
                            <main class="container-fluid">
                                <p class="text-muted small mb-1">Add New Record</p>
                                <h1 class="h5 fw-bold border-bottom border-dark pb-2 mb-4">Medical Record</h1>

                                <?php if (!empty($message)): ?>
                                <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
                                <?php endif; ?>
                                
                                <?php if (!empty($error)): ?>
                                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                                <?php endif; ?>
                                
                                <form class="row g-4" method="post" action="">
                                    <!-- Basic Info -->
                                    <div class="col-12 col-md-4">
                                        <h2 class="h6 fw-semibold border-bottom pb-1 mb-3">Basic Info</h2>

                                        <div class="mb-3">
                                            <label for="patientName" class="form-label small fw-semibold">Patient Name</label>
                                            <input type="text" class="form-control form-control-sm bg-light text-muted"
                                                id="patientName" name="patient_name"
                                                value="<?php echo isset($prefill['patient_name']) ? htmlspecialchars($prefill['patient_name']) : ''; ?>"
                                                required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="dateVisit" class="form-label small fw-semibold">Date of Visit</label>
                                            <input type="date" class="form-control form-control-sm bg-light text-muted"
                                                id="dateVisit" name="appointment_date"
                                                value="<?php echo isset($prefill['appointment_date']) ? htmlspecialchars($prefill['appointment_date']) : date('Y-m-d'); ?>"
                                                required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="doctorName" class="form-label small fw-semibold">Doctor/Staff
                                                Name</label>
                                            <input type="text" class="form-control form-control-sm bg-light text-muted"
                                                id="doctorName" name="doctor"
                                                value="<?php echo isset($prefill['doctor']) ? htmlspecialchars($prefill['doctor']) : ''; ?>"
                                                required>
                                        </div>
                                        <h2 class="h6 fw-semibold border-bottom pb-1 mb-3">Treatment Plan</h2>
                                        <div class="mb-3">
                                            <label for="recommendedTreatment" class="form-label small fw-semibold">Recommended
                                                Treatment / Procedure</label>
                                            <textarea class="form-control form-control-sm bg-light text-muted" name="recommended_treatment"
                                                id="recommendedTreatment" placeholder="Input Recommended Treatment"
                                                rows="3"></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label for="prescriptionsGiven" class="form-label small fw-semibold">Prescriptions Given</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control form-control-sm bg-light text-muted" name="prescriptions_given"
                                                    id="prescriptionsGiven" placeholder="Enter prescription ID" 
                                                    value="<?php echo isset($_POST['prescriptions_given']) ? htmlspecialchars($_POST['prescriptions_given']) : ''; ?>">
                                                <a href="prescription.php" target="_blank" class="btn btn-sm btn-primary">
                                                    Browse Prescriptions
                                                </a>
                                                <a href="addprescription.php?patient=<?php echo urlencode($prefill['patient_name']); ?>&doctor=<?php echo urlencode($prefill['doctor']); ?>&date=<?php echo urlencode($prefill['appointment_date']); ?>" 
                                                   target="_blank" class="btn btn-sm btn-secondary">
                                                    Add New Prescription
                                                </a>
                                            </div>
                                            <small class="text-muted">Enter the ID of an existing prescription or create a new one</small>
                                        </div>
                                    </div>

                                    <!-- Consultation Details -->
                                    <div class="col-12 col-md-4">
                                        <h2 class="h6 fw-semibold border-bottom pb-1 mb-3">Consultation Details</h2>
                                        <div class="mb-3">
                                            <label for="chiefComplaint" class="form-label small fw-semibold">Chief
                                                Complaint</label>
                                            <input type="text" name="chief_complaint" class="form-control form-control-sm bg-light" id="chiefComplaint"
                                                placeholder="Patient's primary complaint" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="diagnosis" class="form-label small fw-semibold">Diagnosis</label>
                                            <textarea class="form-control form-control-sm bg-light text-muted mb-3" name="diagnosis" id="diagnosis"
                                                placeholder="Input Diagnosis" rows="3" required></textarea>
                                            <textarea class="form-control form-control-sm bg-light text-muted" name="treatment"
                                                id="treatment" placeholder="Input Treatment"
                                                rows="3"></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label for="skinType" class="form-label small fw-semibold">Skin Type</label>
                                            <select class="form-select form-select-sm bg-light" name="skin_type" id="skinType">
                                                <option value="oily">Oily</option>
                                                <option value="dry">Dry</option>
                                                <option value="combination">Combination</option>
                                                <option value="sensitive">Sensitive</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="clinicalNotes" class="form-label small fw-semibold">Clinical Notes /
                                                Observations</label>
                                            <textarea class="form-control form-control-sm bg-light text-muted" name="clinical_notes"
                                                id="clinicalNotes" placeholder="Doctor's observations, exam findings"
                                                rows="3"></textarea>
                                        </div>
                                        <h2 class="h6 fw-semibold border-bottom pb-1 mb-3">Follow Up Info</h2>
                                        <div class="mb-3">
                                            <label for="instructionsPatient" class="form-label small fw-semibold">Instructions
                                                to Patient</label>
                                            <textarea class="form-control form-control-sm bg-light" name="instructions" id="instructionsPatient"
                                                placeholder="Input Instructions" rows="3"></textarea>
                                        </div>
                                    </div>

                                    <!-- Upload Images -->
                                    <div class="col-12 col-md-4">
                                        <h2 class="h6 fw-semibold border-bottom pb-1 mb-3">Upload Images</h2>
                                        <div class="mb-2">
                                            <label class="form-label small fw-semibold">Upload</label>
                                        </div>
                                        <div class="border border-secondary border-dashed rounded d-flex flex-column justify-content-center align-items-center text-center p-3"
                                            style="height: 12rem; font-size: 0.6rem;">
                                            <img src="https://storage.googleapis.com/a1aa/image/842b36c6-ae8a-4806-7721-12ea72f59983.jpg"
                                                width="24" height="24" class="mb-1" alt="Cloud upload icon">
                                            <p>
                                                Drag & drop files or <span class="text-primary fw-semibold"
                                                    style="cursor: pointer;">Browse</span>
                                            </p>
                                            <p class="mt-1">Supported formats: JPEG, PNG, GIF, WMV, PDF, PSD, AI, Word, PPT</p>
                                        </div>
                                        <button type="button"
                                            class="btn btn-blue hover-blue btn-sm w-100 mt-3 fw-semibold">UPLOAD FILES</button>
                                        <button type="button"
                                            class="btn btn-blue1 hover-blue btn-sm w-100 mt-2 fw-semibold">DELETE FILE</button>
                                        <div class="d-flex align-items-center mt-2 gap-2">
                                            <button type="submit" class="btn btn-sm btn-blue hover-blue fw-semibold">
                                                Save
                                            </button>
                                            <a href="medicalrecord.php" class="btn btn-fade text-white hover-blue" style="font-size: 12px;">
                                                <i class="fas fa-arrow-left"></i> Back to Records
                                            </a>
                                        </div>
                                    </div>
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
                    <a class="btn btn-primary" href="login.php">Logout</a>
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

</body>

</html>