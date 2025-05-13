<?php
$record_id = isset($_GET['record']) ? (int)$_GET['record'] : -1;
$file = "medical_data.txt";

$parsed = [
    'patient_name' => '',
    'doctor' => '',
    'booked_on' => '',
    'recommended_treatment' => '',
    'prescriptions_given' => '',
    'diagnosis' => '',
    'treatment' => '',
    'chief_complaint' => '',
    'skin_type' => '',
    'clinical_notes' => '',
    'instructions' => '',
    'saved_on' => '',
];

if ($record_id >= 0 && file_exists($file)) {
    $records = file($file, FILE_IGNORE_NEW_LINES);
    if (isset($records[$record_id])) {
        $line = $records[$record_id];

        // Extract all relevant fields
        preg_match('/Patient:\s*(.*?)\s*\|/', $line . ' |', $m1);
        $parsed['patient_name'] = $m1[1] ?? '';

        preg_match('/Doctor:\s*(.*?)\s*\|/', $line . ' |', $m2);
        $parsed['doctor'] = $m2[1] ?? '';

        preg_match('/Appointment Date:\s*(.*?)\s*\|/', $line . ' |', $m3);
        $parsed['booked_on'] = $m3[1] ?? '';

        preg_match('/Recommended Treatment:\s*(.*?)\s*\|/', $line . ' |', $m4);
        $parsed['recommended_treatment'] = $m4[1] ?? '';

        preg_match('/Prescriptions Given:\s*(.*?)\s*\|/', $line . ' |', $m5);
        $parsed['prescriptions_given'] = $m5[1] ?? '';

        preg_match('/Diagnosis:\s*(.*?)\s*\|/', $line . ' |', $m6);
        $parsed['diagnosis'] = $m6[1] ?? '';

        preg_match('/Treatment:\s*(.*?)\s*\|/', $line . ' |', $m7);
        $parsed['treatment'] = $m7[1] ?? '';

        preg_match('/Chief Complaint:\s*(.*?)\s*\|/', $line . ' |', $m8);
        $parsed['chief_complaint'] = $m8[1] ?? '';

        preg_match('/Skin Type:\s*(.*?)\s*\|/', $line . ' |', $m9);
        $parsed['skin_type'] = $m9[1] ?? '';

        preg_match('/Clinical Notes:\s*(.*?)\s*\|/', $line . ' |', $m10);
        $parsed['clinical_notes'] = $m10[1] ?? '';

        preg_match('/Instructions:\s*(.*?)\s*\|/', $line . ' |', $m11);
        $parsed['instructions'] = $m11[1] ?? '';

        preg_match('/Saved On:\s*(.*)/', $line, $m12);
        $parsed['saved_on'] = $m12[1] ?? '';
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

    <title>DermaGrid - View Medical</title>

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

        .form-control-sm,
        .form-select-sm {
            background-color: #e9ecef !important;
            color: #495057 !important;
        }

        .fw-semibold {
            font-weight: 600;
        }

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
            <li class="nav-item ">
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
                        <h1 class="h3 mb-0 text-gray-800">Medical Record</h1>
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

                        <!-- Content Column -->
                        <div class="col">
                            <main class="container-fluid">
                                <p class="text-muted small mb-1">Viewing Record</p>
                                <h1 class="h5 fw-bold border-bottom border-dark pb-2 mb-4">Medical Record Details</h1>

                                <div class="row g-4">
                                    <!-- Basic Info -->
                                    <div class="col-12 col-md-4">
                                        <h2 class="h6 fw-semibold border-bottom pb-1 mb-3">Basic Info</h2>
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">Patient Name</label>
                                            <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars($parsed['patient_name']) ?>" readonly>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">Date of Visit</label>
                                            <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars($parsed['booked_on']) ?>" readonly>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">Doctor/Staff Name</label>
                                            <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars($parsed['doctor']) ?>" readonly>
                                        </div>
                                        <h2 class="h6 fw-semibold border-bottom pb-1 mb-3">Treatment Plan</h2>
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">Recommended Treatment</label>
                                            <textarea class="form-control form-control-sm" rows="3" readonly><?= htmlspecialchars($parsed['treatment']) ?></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">Prescriptions Given</label>
                                            <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars($parsed['prescriptions_given']) ?>" readonly>
                                        </div>
                                    </div>

                                    <!-- Consultation Details -->
                                    <div class="col-12 col-md-4">
                                        <h2 class="h6 fw-semibold border-bottom pb-1 mb-3">Consultation Details</h2>
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">Chief Complaint</label>
                                            <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars($parsed['chief_complaint']) ?>" readonly>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">Diagnosis</label>
                                            <textarea class="form-control form-control-sm" rows="3" readonly><?= htmlspecialchars($parsed['diagnosis']) ?></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">Skin Type</label>
                                            <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars($parsed['skin_type']) ?>" readonly>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">Clinical Notes / Observations</label>
                                            <textarea class="form-control form-control-sm" rows="3" readonly><?= htmlspecialchars($parsed['clinical_notes']) ?></textarea>
                                        </div>
                                        <h2 class="h6 fw-semibold border-bottom pb-1 mb-3">Follow Up Info</h2>
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">Instructions to Patient</label>
                                            <textarea class="form-control form-control-sm" rows="3" readonly><?= htmlspecialchars($parsed['instructions']) ?></textarea>
                                        </div>
                                    </div>
                                </div>
                                

                                <a href="javascript:history.go(-1)" class="btn btn-primary mt-4">
                                    <i class="fas fa-arrow-left"></i> Back to Records
                                </a>
                            </main>
                        </div>

                    </div>
                </div>

            </div>
            <!-- /.container-fluid -->

        </div>
        <!-- End of Main Content -->

        <!-- Footer -->
        <!-- <footer class="sticky-footer bg-white">
        <div class="container my-auto">
            <div class="copyright text-center my-auto">
                <span>Copyright &copy; Your Website 2021</span>
            </div>
        </div>
    </footer> -->
        <!-- End of Footer -->

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