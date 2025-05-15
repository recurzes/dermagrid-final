<?php
// Initialize the session
session_start();

// Basic error reporting to see any issues
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../backend/config/database.php';

// Get database connection
$database = getDbConnection();

// Direct SQL query to debug
$staffSchedules = [];
try {
    // Simple direct SQL query
    $sql = "SELECT id, first_name, last_name, role FROM staff";
    $stmt = $database->prepare($sql);
    $stmt->execute();
    
    $count = $stmt->rowCount();
    
    // Fetch the results
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Process each staff member
    foreach ($results as $row) {
        $name = $row['first_name'] . ' ' . $row['last_name'];
        $role = $row['role'];
        
        // Determine schedule based on role
        $schedule = 'Mon-Fri, 9:00 AM - 6:00 PM'; // Default
        if ($role == 'doctor') {
            $schedule = 'Mon-Fri, 8:00 AM - 5:00 PM';
        } elseif ($role == 'nurse') {
            $schedule = 'Mon-Sat, 7:00 AM - 4:00 PM';
        }
        
        // Add to staff schedules
        $staffSchedules[] = [
            'name' => $name,
            'expertise' => ucfirst($role),
            'working_days' => $schedule
        ];
    }
} catch (PDOException $e) {
    // Fallback data if query fails
    $staffSchedules = [
        [
            'name' => 'Dr. Smith (Fallback)', 
            'expertise' => 'Doctor',
            'working_days' => 'Mon-Fri, 8:00 AM - 5:00 PM'
        ],
        [
            'name' => 'Nurse Johnson (Fallback)', 
            'expertise' => 'Nurse',
            'working_days' => 'Mon-Sat, 7:00 AM - 4:00 PM'
        ]
    ];
}

// Now load the models for appointments
require_once '../backend/models/Appointment.php';
$appointmentModel = new Appointment($database);
$appointments = $appointmentModel->getAll();
$appointment_stats = $appointmentModel->getAppointmentStats();

// Set flag for dashboard page to customize header
$isDashboard = true;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>DermaGrid - Dashboard</title>

    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <link href="css/sb-admin-2.min.css" rel="stylesheet">

    <style>
        .table-clickable tbody tr {
            cursor: pointer;
            transition: all 0.25s ease-in-out;
        }
        
        /* Disable search bar and notification button */
        .navbar-search input, 
        .navbar-search button {
            pointer-events: none;
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        #alertsDropdown {
            pointer-events: none;
            opacity: 0.6;
            cursor: not-allowed;
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

            <li class="nav-item active">
                <a class="nav-link" href="dashboard.php">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span></a>
            </li>

            <hr class="sidebar-divider">

            <div class="sidebar-heading">
                Project Management
            </div>

            <li class="nav-item">
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
                <!-- Include the common header with logout functionality -->
                <?php include 'includes/header.php'; ?>

                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <!-- Content Row -->
                    <div class="row">
                        <!-- Content Column -->
                        <div class="col">
                            <!-- Appointments Table -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Appointments</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive table-striped" id="appointmentsTable" style="max-height: 868px; overflow-y: auto;">
                                        <table class="table table-bordered table-hover">
                                            <thead class="thead-dark">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Name</th>
                                                    <th>Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($appointments)): ?>
                                                    <?php foreach ($appointments as $index => $a): ?>
                                                        <tr class="clickable-row" onclick="window.location='appointmentdetails.php?id=<?= urlencode($a['id']) ?>'">
                                                            <td><?= $index + 1 ?></td>
                                                            <td><?= htmlspecialchars($a['name']) ?></td>
                                                            <td><?= htmlspecialchars($a['appointment_date']) ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="7" class="text-center text-muted">No appointments found.</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End Table -->

                        <div class="col">
                            <div class="row mb-4">
                                <div class="col">
                                    <div class="row text-center mb-4">
                                        <div class="col">
                                            <div class="card shadow h-100 py-2">
                                                <div class="card-body py-5 px-3">
                                                    <div class="row no-gutters align-items-center">
                                                        <div class="col mr-2">
                                                            <div
                                                                class="text-xs font-weight-bold text-primary text-uppercase mb-3">
                                                                Total No. of Appointments</div>
                                                            <div class="h2 mb-0 font-weight-bold text-gray-800">
                                                                <?= $appointment_stats[0]['total_appointments'] ?? 0 ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="card shadow h-100 py-2">
                                                <div class="card-body py-5 px-3">
                                                    <div class="row no-gutters align-items-center">
                                                        <div class="col mr-2">
                                                            <div
                                                                class="text-xs font-weight-bold text-primary text-uppercase mb-3">
                                                                Appointments Served</div>
                                                            <div class="h2 mb-0 font-weight-bold text-gray-800">
                                                                <?= $appointment_stats[0]['total_completed'] ?? 0 ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row text-center">
                                        <div class="col">
                                            <div class="card shadow h-100 py-2">
                                                <div class="card-body py-5 px-3">
                                                    <div class="row no-gutters align-items-center">
                                                        <div class="col mr-2">
                                                            <div
                                                                class="text-xs font-weight-bold text-primary text-uppercase mb-3">
                                                                Appointments Pending</div>
                                                            <div class="h2 mb-0 font-weight-bold text-gray-800">
                                                                <?= $appointment_stats[0]['total_scheduled'] ?? 0 ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="card shadow h-100 py-2">
                                                <div class="card-body py-5 px-3">
                                                    <div class="row no-gutters align-items-center">
                                                        <div class="col mr-2">
                                                            <div
                                                                class="text-xs font-weight-bold text-primary text-uppercase mb-3">
                                                                Cancel Appointment</div>
                                                            <div class="h2 mb-0 font-weight-bold text-gray-800">
                                                                <?= $appointment_stats[0]['total_cancelled'] ?? 0 ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col">
                                    <div class="card shadow mb-4">
                                        <div class="card-header py-3">
                                            <h6 class="m-0 font-weight-bold text-primary">Employee Schedule</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive" style="max-height: 450px; overflow-y: auto;">
                                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                                    <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Name</th>
                                                            <th>Expertise</th>
                                                            <th>Working Days</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php if (!empty($staffSchedules)): ?>
                                                            <?php foreach ($staffSchedules as $index => $staff): ?>
                                                                <tr>
                                                                    <td><?= $index + 1 ?></td>
                                                                    <td><?= htmlspecialchars($staff['name']) ?></td>
                                                                    <td><?= htmlspecialchars($staff['expertise']) ?></td>
                                                                    <td><?= htmlspecialchars($staff['working_days']) ?></td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        <?php else: ?>
                                                            <tr>
                                                                <td colspan="4" class="text-center text-muted">
                                                                    No staff records found in database.
                                                                    <?php if (isset($count)): ?>
                                                                        SQL query returned <?= $count ?> records.
                                                                    <?php endif; ?>
                                                                </td>
                                                            </tr>
                                                        <?php endif; ?>
                                                    </tbody>
                                                </table>
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
    </div>

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
        const tableRows = document.querySelectorAll(".table-clickable tbody tr");
        for (const tableRow of tableRows) {
            tableRow.addEventListener("click", function() {
                window.open(this.dataset.href, "_blank");
            });
        }
    </script>
</body>
</html>