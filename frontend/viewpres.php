<?php
// Start session at the beginning of the file
session_start();

require_once '../backend/config/database.php';
require_once '../backend/models/Prescription.php';

$record_id = isset($_GET['record']) ? (int)$_GET['record'] : -1;
$parsed = [];

// Initialize database connection and models
$database = getDbConnection();
$prescriptionModel = new Prescription($database);

// Fetch prescription from database
try {
    $prescription = $prescriptionModel->getById($record_id);
    
    if (!$prescription) {
        // Prescription not found
        $error = "Prescription not found";
    } else {
        // Use data from database
        $parsed = [
            'patient_name' => $prescription['patient_name'] ?? '',
            'contact' => $prescription['contact_number'] ?? '',
            'doctor' => $prescription['doctor_name'] ?? '',
            'appointment_date' => $prescription['appointment_date'] ?? '',
            'medicine_type' => $prescription['medicine_type'] ?? '',
            'medicine_name' => $prescription['medication_name'] ?? '',
            'generic_name' => $prescription['generic_name'] ?? '',
            'dosage' => $prescription['dosage'] ?? '',
            'frequency' => $prescription['frequency'] ?? '',
            'duration' => $prescription['duration'] ?? '',
            'instructions' => $prescription['instructions'] ?? '',
            'saved_on' => $prescription['created_at'] ?? '',
        ];
    }
} catch (Exception $e) {
    $error = $e->getMessage();
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

    <title>DermaGrid - View Prescription</title>

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

        .blue {
            background-color: #4a73df;
            color: white;
            border: 1px solid #4a73df;
        }
    </style>

    <style>
        textarea::-webkit-scrollbar {
            width: 6px;
        }

        textarea::-webkit-scrollbar-thumb {
            background-color: #d1d5db;
            border-radius: 3px;
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
            <li class="nav-item active">
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
                    echo '<h1 class="h3 m-auto text-gray-800">View Prescription</h1>';
                    echo '</nav>';
                }
                ?>

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Content Row -->
                    <div class="row">

                        <!-- Display Prescription -->
                        <div class="col">
                            <div class="Medicine">
                                <p class="small text-black">Prescription</p>
                                <h1 class="fw-bold fs-6 text-black border-bottom border-secondary pb-1 mb-3">
                                    <?= htmlspecialchars($parsed['medicine_name']) ?>
                                </h1>

                                <div class="d-flex flex-wrap gap-2 mb-4 ms-1">
                                    <span class="border border-primary small blue fw-normal px-3 py-1 rounded">
                                        <?= htmlspecialchars($parsed['dosage']) ?>
                                    </span>
                                    <span class="border border-primary small blue px-3 py-1 rounded">
                                        <?= htmlspecialchars($parsed['frequency']) ?>
                                    </span>
                                    <!-- You can add a condition to extract 'With meal' / 'Before meal' / 'After meal' if it's stored -->
                                    <span class="border border-primary small blue px-3 py-1 rounded">After meal</span>
                                    <span class="border border-primary small blue fw-normal px-3 py-1 rounded">
                                        <?= htmlspecialchars($parsed['duration']) ?>
                                    </span>
                                </div>

                                <p class="small fw-bold text-black border-bottom border-secondary pb-1 mb-2 ms-1">Instructions</p>
                                <p class="small text-muted ms-1"><?= htmlspecialchars($parsed['instructions']) ?></p>
                            </div>
                        </div>

                        <a href="javascript:history.go(-1)" class="btn btn-primary mt-4">
                            <i class="fas fa-arrow-left"></i> Back to Records
                        </a>


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