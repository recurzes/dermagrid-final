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

// Add this to the beginning of your form handling code (around line 17)
$uploadDir = '../uploads/medical/';
$image_path = null;

// Create directory if it doesn't exist
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

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
    
    // Handle file upload
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['image_file']) && $_FILES['image_file']['size'] > 0) {
        $file = $_FILES['image_file'];
        
        // Validate file
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        if (in_array($file['type'], $allowedTypes) && $file['size'] <= $maxSize) {
            // Generate unique filename
            $filename = uniqid() . '_' . basename($file['name']);
            $targetFile = $uploadDir . $filename;
            
            // Upload file
            if (move_uploaded_file($file['tmp_name'], $targetFile)) {
                $image_path = $targetFile;
            } else {
                $error = "Failed to upload file.";
            }
        } else {
            if (!in_array($file['type'], $allowedTypes)) {
                $error = "Invalid file type. Please upload JPEG, PNG, or GIF.";
            } else {
                $error = "File size exceeds 5MB limit.";
            }
        }
    }
    
    // Prepare data for database
    $data = [
        'patient_id' => $patient_id,
        'staff_id' => $staff_id,
        'appointment_id' => null,
        'visit_date' => $appointment_date,
        'diagnosis' => $_POST['diagnosis'] ?? null,
        'treatment_plan' => $_POST['recommended_treatment'] ?? null,
        'notes' => $_POST['clinical_notes'] ?? null,
        'prescription_id' => $_POST['prescriptions_given'] ? intval($_POST['prescriptions_given']) : null,
        'chief_complaint' => $_POST['chief_complaint'] ?? null,
        'skin_type' => $_POST['skin_type'] ?? null,
        'instructions' => $_POST['instructions'] ?? null,
        'image_path' => $image_path
    ];
    
    // Validate required fields
    if (empty($patient_id)) {
        $error = "Patient not found. Please select a valid patient.";
//        $patient_id = 1; // Use the ID of Lance Limbaro from your database
    } elseif (empty($staff_id)) {
        $error = "Doctor/Staff not found. Please select a valid doctor.";
//        $staff_id = 1; // Use the ID of Lance Limbaro from your database
    } else {
        // Save the medical record
        $result = $medicalRecord->create($data);
        
        if ($result['success']) {
            $message = "Medical record added successfully.";
        } else {
            $error = "Error saving medical record: " . ($result['error'] ?? "Unknown error");
        }
    }

    // Add at the top of the file where form handling is done
    if (isset($_POST['refresh_prescriptions']) && $_POST['refresh_prescriptions'] == '1') {
        // Just refresh the page with the new patient name set
        $patient_name = $_POST['patient_name'] ?? '';
        // Set the prefill data
        $prefill['patient_name'] = $patient_name;
        // Continue without processing the full form
    }
}

// Get prefilled data from URL parameters
$prefill = [
    'patient_name' => isset($_GET['patient']) ? urldecode($_GET['patient']) : '',
    'doctor' => isset($_GET['doctor']) ? urldecode($_GET['doctor']) : '',
    'appointment_date' => isset($_GET['date']) ? urldecode($_GET['date']) : ''
];

// Get patient ID from the patient name
$patient_parts = explode(' ', $prefill['patient_name'], 2);
$first_name = $patient_parts[0] ?? '';
$last_name = $patient_parts[1] ?? '';

// Make sure we're finding the exact patient
$patients = $patientModel->searchByName($first_name, $last_name);
$patient_id = null;

// Only set patient_id if we have exactly one match to prevent wrong patient
if (count($patients) === 1) {
    $patient_id = $patients[0]['id'];
    // For debugging
    error_log("Patient found: {$patient_id} for name: {$prefill['patient_name']}");
} else {
    // For debugging
    error_log("Multiple or no patients found for name: {$prefill['patient_name']}");
}

// If we found a patient, get their prescriptions
$patientPrescriptions = [];
if ($patient_id) {
    $patientPrescriptions = $medicalRecord->getPrescriptionsForPatient($patient_id);
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
                                
                                <form class="row g-4" method="post" action="" enctype="multipart/form-data">
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
                                            
                                            <select class="form-select form-select-sm bg-light text-muted" name="prescriptions_given" id="prescriptionsGiven">
                                                <option value="">-- Select a prescription --</option>
                                                <?php foreach ($patientPrescriptions as $prescription): ?>
                                                    <option value="<?= $prescription['id'] ?>" <?= (isset($_POST['prescriptions_given']) && $_POST['prescriptions_given'] == $prescription['id']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($prescription['medication_name']) ?> 
                                                        (<?= htmlspecialchars($prescription['dosage'] ?? '') ?>, <?= htmlspecialchars($prescription['frequency'] ?? '') ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            
                                            <div class="mt-2">
                                                <?php if ($patient_id): ?>
                                                    <a href="addprescription.php?patient=<?= urlencode($prefill['patient_name']) ?>&doctor=<?= urlencode($prefill['doctor']) ?>&date=<?= urlencode($prefill['appointment_date']) ?>" 
                                                       class="btn btn-sm btn-primary">
                                                        <i class="fas fa-plus"></i> Create New Prescription
                                                    </a>
                                                <?php else: ?>
                                                    <button type="button" class="btn btn-sm btn-secondary" disabled>
                                                        <i class="fas fa-plus"></i> Create New Prescription
                                                    </button>
                                                    <small class="text-muted d-block mt-1">Enter a valid patient name first</small>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <?php if ($patient_id && empty($patientPrescriptions)): ?>
                                                <small class="text-muted d-block mt-1">No prescriptions found for this patient. Please create one using the button above.</small>
                                            <?php endif; ?>
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
                                        <div class="mb-3">
                                            <label for="imageUpload" class="form-label small fw-semibold">Upload Image</label>
                                            <input type="file" class="form-control form-control-sm" id="imageUpload" name="image_file" accept="image/*">
                                            <small class="text-muted">Supported formats: JPEG, PNG, GIF</small>
                                        </div>
                                        <div id="imagePreview" class="my-2" style="display: none;">
                                            <p class="small mb-1">Selected Image Preview:</p>
                                            <img id="previewImg" class="img-thumbnail" style="max-height: 150px;" alt="Preview">
                                        </div>
                                        <div class="d-flex align-items-center mt-2 gap-2">
                                            <button type="submit" class="btn btn-sm btn-blue hover-blue fw-semibold">
                                                Save
                                            </button>
                                            <a href="javascript:history.back()" class="btn btn-fade text-white hover-blue" style="font-size: 12px;">
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

    <script>
    document.getElementById('patientName').addEventListener('change', function() {
        // Get the patient name
        const patientName = this.value;
        
        // Submit the form with a special flag to just update the prescriptions dropdown
        const form = document.createElement('form');
        form.method = 'post';
        form.action = window.location.href;
        
        const nameInput = document.createElement('input');
        nameInput.type = 'hidden';
        nameInput.name = 'patient_name';
        nameInput.value = patientName;
        
        const refreshInput = document.createElement('input');
        refreshInput.type = 'hidden';
        refreshInput.name = 'refresh_prescriptions';
        refreshInput.value = '1';
        
        form.appendChild(nameInput);
        form.appendChild(refreshInput);
        document.body.appendChild(form);
        form.submit();
    });
    </script>

    <script>
    document.getElementById('imageUpload').addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('previewImg').src = e.target.result;
                document.getElementById('imagePreview').style.display = 'block';
            }
            reader.readAsDataURL(file);
        } else {
            document.getElementById('imagePreview').style.display = 'none';
        }
    });
    </script>

</body>

</html>