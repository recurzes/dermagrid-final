<?php
require_once '../backend/config/database.php';
require_once '../backend/models/Prescription.php';
require_once '../backend/models/Patient.php';
require_once '../backend/models/Staff.php';

// Initialize database connection
$database = getDbConnection();
$prescriptionModel = new Prescription($database);
$patientModel = new Patient($database);
$staffModel = new Staff($database);

// Get prescription ID from URL
$id = $_GET['id'] ?? 0;

// Get prescription details
$prescription = $prescriptionModel->getById($id);

// If prescription not found, redirect to prescription list
if (!$prescription) {
    header('Location: prescription.php');
    exit;
}

// Get patient and staff details
$patient = $patientModel->getById($prescription['patient_id']);
$staff = $staffModel->getById($prescription['staff_id']);

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'complete') {
        $prescriptionModel->updateStatus($id, 'completed');
        header('Location: viewprescription.php?id=' . $id);
        exit;
    } elseif ($_POST['action'] === 'cancel') {
        $prescriptionModel->updateStatus($id, 'cancelled');
        header('Location: viewprescription.php?id=' . $id);
        exit;
    }
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
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <style>
        .prescription-card {
            border: 1px solid #e3e6f0;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }

        .prescription-header {
            background-color: #4e73df;
            color: white;
            padding: 1rem;
            border-top-left-radius: 0.35rem;
            border-top-right-radius: 0.35rem;
        }

        .prescription-body {
            padding: 1.5rem;
        }

        .prescription-footer {
            background-color: #f8f9fc;
            padding: 1rem;
            border-bottom-left-radius: 0.35rem;
            border-bottom-right-radius: 0.35rem;
            border-top: 1px solid #e3e6f0;
        }

        .label {
            font-weight: bold;
            color: #5a5c69;
        }

        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: bold;
        }

        .status-active {
            background-color: #1cc88a;
            color: white;
        }

        .status-completed {
            background-color: #36b9cc;
            color: white;
        }

        .status-cancelled {
            background-color: #e74a3b;
            color: white;
        }
    </style>
</head>

<body id="page-top">

<!-- Page Wrapper -->
<div id="wrapper">

    <!-- Sidebar -->
    <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
        <!-- Sidebar content (same as in prescription.php) -->
        <!-- Sidebar - Brand -->
        <a class="sidebar-brand d-flex align-items-center" href="dashboard.php">
            <div class="sidebar-brand-text">DermaGrid</div>
        </a>

        <!-- Divider -->
        <hr class="sidebar-divider my-0">

        <!-- Nav Item - Dashboard -->
        <li class="nav-item">
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
                <!-- Topbar content (same as in prescription.php) -->
                <!-- Sidebar Toggle (Topbar) -->
                <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                    <i class="fa fa-bars"></i>
                </button>

                <!-- Page Heading -->
                <div class="d-sm-flex align-items-center justify-content-center">
                    <h1 class="h3 mb-0 text-gray-800">View Prescription</h1>
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
                            <!-- More alerts -->
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
                            <!-- User dropdown menu -->
                            <a class="dropdown-item" href="#">
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
                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <div class="prescription-card mb-4">
                            <div class="prescription-header d-flex justify-content-between align-items-center">
                                <h5 class="m-0 font-weight-bold">Prescription #<?= htmlspecialchars($prescription['id']) ?></h5>
                                <span class="status-badge status-<?= strtolower($prescription['status']) ?>">
                                        <?= htmlspecialchars(ucfirst($prescription['status'])) ?>
                                    </span>
                            </div>
                            <div class="prescription-body">
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <p><span class="label">Patient:</span> <?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?></p>
                                        <p><span class="label">Doctor:</span> <?= htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']) ?></p>
                                        <p><span class="label">Created On:</span> <?= htmlspecialchars(date('M d, Y', strtotime($prescription['created_at']))) ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><span class="label">Medication:</span> <?= htmlspecialchars($prescription['medication_name']) ?></p>
                                        <p><span class="label">Dosage:</span> <?= htmlspecialchars($prescription['dosage'] . ' ' . $prescription['unit']) ?></p>
                                        <p><span class="label">Frequency:</span> <?= htmlspecialchars($prescription['frequency']) ?></p>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-12">
                                        <p><span class="label">Duration:</span> <?= htmlspecialchars($prescription['duration']) ?></p>
                                        <p><span class="label">Instructions:</span> <?= htmlspecialchars($prescription['instructions']) ?></p>
                                        <?php if (!empty($prescription['additional_instruction'])): ?>
                                            <p><span class="label">Additional Instructions:</span> <?= htmlspecialchars($prescription['additional_instruction']) ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="prescription-footer d-flex justify-content-between">
                                <a href="prescription.php" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-arrow-left"></i> Back to List
                                </a>

                                <?php if ($prescription['status'] === 'active'): ?>
                                    <div>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="action" value="complete">
                                            <button type="submit" class="btn btn-success btn-sm">
                                                <i class="fas fa-check"></i> Mark as Completed
                                            </button>
                                        </form>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="action" value="cancel">
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fas fa-times"></i> Cancel Prescription
                                            </button>
                                        </form>
                                    </div>
                                <?php endif; ?>
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
</body>
</html>
