<?php
// Initialize the session
session_start();

require_once __DIR__ . '/../backend/config/database.php';
require_once __DIR__ . '/../backend/models/Staff.php';

// Get doctor ID from URL
$doctor_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Initialize variables
$doctor = null;
$appointments = [];
$receptionists = [];

// Default availability (can be customized per doctor if needed)
$availability = [
    'Monday' => '9:00 AM - 5:30 PM',
    'Tuesday' => '9:00 AM - 5:30 PM',
    'Wednesday' => '9:00 AM - 5:30 PM',
    'Thursday' => '9:00 AM - 5:30 PM',
    'Friday' => '9:00 AM - 5:30 PM',
    'Saturday' => '9:00 AM - 5:30 PM',
    'Sunday' => '9:00 AM - 4:30 PM',
];

try {
    if ($doctor_id > 0) {
        $database = getDbConnection();
        $staffModel = new Staff($database);

        // Get doctor details
        $doctor = $staffModel->getById($doctor_id);

        if ($doctor && $doctor['role'] == 'doctor') {
            // Get doctor's appointments
            $appointments = $staffModel->getAppointments($doctor_id);

            // Get receptionists
            $receptionists = $staffModel->getReceptionists();
        } else {
            // Redirect if doctor not found or not a doctor
            header("Location: doctors&staff.php");
            exit;
        }
    } else {
        // Redirect if no ID provided
        header("Location: doctors&staff.php");
        exit;
    }
} catch (Exception $e) {
    // Handle error silently
    // You could log the error here
}
?>

<?php
//$appointments = [];
//
//if (file_exists('appointments.txt')) {
//    $lines = file('appointments.txt', FILE_IGNORE_NEW_LINES);
//
//    foreach ($lines as $line) {
//        $fields = explode('|', $line);
//
//        if (count($fields) >= 10) {
//            $appointments[] = [
//                'name' => trim($fields[0]) . ' ' . trim($fields[1]),
//                'number' => trim($fields[2]),
//                'doctor' => trim($fields[6]),
//                'appointment_date' => trim($fields[4]),
//                'appointment_time' => trim($fields[5]),
//                'status' => 'Pending',
//                'booked_on' => trim($fields[9])
//            ];
//        }
//    }
//}
//?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>DermaGrid - Doctor Details</title>

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

        <li class="nav-item">
            <a class="nav-link" href="appointments.php">
                <i class="bi bi-person"></i>
                <span>Appointments</span></a>
        </li>

        <hr class="sidebar-divider">

        <div class="sidebar-heading">
            Staff Management
        </div>

        <li class="nav-item active">
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

            <!-- Include common header -->
            <?php include 'includes/header.php'; ?>

            <!-- Begin Page Content -->
            <div class="container-fluid">

                <!-- Content Row -->
                <div class="row">

                    <!-- Content Column -->
                    <div class="col">
                        <!-- Doctor Info Card -->
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <div class="row px-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Doctor Information</h6>
                                </div>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tr>
                                        <th class="pl-0 w-25 text-left">Name:</th>
                                        <td class="text-left"><?= htmlspecialchars($doctor['first_name'] . ' ' . $doctor['last_name']) ?></td>
                                    </tr>
                                    <tr>
                                        <th class="pl-0 w-25 text-left">Email:</th>
                                        <td class="text-left"><?= htmlspecialchars($doctor['email']) ?></td>
                                    </tr>
                                    <tr>
                                        <th class="pl-0 w-25 text-left">Phone:</th>
                                        <td class="text-left"><?= htmlspecialchars($doctor['phone']) ?></td>
                                    </tr>
                                    <tr>
                                        <th class="pl-0 w-25 text-left">Username:</th>
                                        <td class="text-left"><?= htmlspecialchars($doctor['username']) ?></td>
                                    </tr>
                                </table>
                            </div>


                        </div>

                        <!-- Availability Card -->
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <div class="row px-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Availability</h6>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="container">
                                    <div class="row small">
                                        <?php foreach ($availability as $day => $time): ?>
                                            <div class="col">
                                                <div class="row">
                                                    <?= htmlspecialchars($day) ?>
                                                </div>
                                                <div class="row font-weight-bold text-primary">
                                                    <?= htmlspecialchars($time) ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Secretary List Table -->
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Receptionists</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive table-striped">
                                    <table class="table table-bordered" width="100%" cellspacing="0">
                                        <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Position</th>
                                            <th>Gender</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach ($receptionists as $index => $sec): ?>
                                            <tr>
                                                <th><?= $index + 1 ?></th>
                                                <td><?= htmlspecialchars($sec['name']) ?></td>
                                                <td><?= htmlspecialchars($sec['position']) ?></td>
                                                <td><?= htmlspecialchars($sec['gender']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Appointments</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive table-striped" id="appointmentsTable">
                                    <table class="table table-bordered table-hover">
                                        <thead class="thead-dark">
                                        <tr>
                                            <th>#</th>
                                            <th>Patient Name</th>
                                            <th>Appointment Date</th>
                                            <th>Appointment Time</th>
                                            <th>Status</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php if (!empty($appointments)): ?>
                                            <?php foreach ($appointments as $index => $appointment): ?>
                                                <tr class="clickable-row" onclick="window.location='appointmentdetails.php?id=<?= urlencode($appointment['id']) ?>'">
                                                    <td><?= $index + 1 ?></td>
                                                    <td><?= htmlspecialchars($appointment['patient_name']) ?></td>
                                                    <td><?= htmlspecialchars($appointment['appointment_date']) ?></td>
                                                    <td><?= htmlspecialchars($appointment['appointment_time']) ?></td>
                                                    <td><?= htmlspecialchars($appointment['status']) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="7" class="text-center text-muted">No appointments found.
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                        </tbody>
                                    </table>
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <div id="entry-info" class="text-gray-600 small">
                                            Showing 1 to 10 of 50 entries
                                        </div>
                                        <nav>
                                            <ul class="pagination mb-0" id="pagination"></ul>
                                        </nav>
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
    const table = document.getElementById("dataTable");
    const tbody = table.querySelector("tbody");
    const rows = Array.from(tbody.querySelectorAll("tr"));
    const rowsPerPage = 10;
    let currentPage = 1;

    const pagination = document.getElementById("pagination");
    const entryInfo = document.getElementById("entry-info");

    function renderTable() {
        const totalRows = rows.length;
        const totalPages = Math.ceil(totalRows / rowsPerPage);
        const start = (currentPage - 1) * rowsPerPage;
        const end = Math.min(start + rowsPerPage, totalRows);

        // Show only the rows for the current page
        rows.forEach((row, index) => {
            row.style.display = index >= start && index < end ? "" : "none";
        });

        // Update info text
        entryInfo.textContent = `Showing ${start + 1} to ${end} of ${totalRows} entries`;

        // Build pagination
        pagination.innerHTML = "";

        const createPageItem = (label, disabled = false, active = false) => {
            const li = document.createElement("li");
            li.className = `page-item${disabled ? " disabled" : ""}${active ? " active" : ""}`;
            const a = document.createElement("a");
            a.className = "page-link";
            a.href = "#";
            a.textContent = label;
            li.appendChild(a);
            return li;
        };

        // Prev button
        const prevItem = createPageItem("Previous", currentPage === 1);
        prevItem.addEventListener("click", (e) => {
            e.preventDefault();
            if (currentPage > 1) {
                currentPage--;
                renderTable();
            }
        });
        pagination.appendChild(prevItem);

        // Page number buttons
        for (let i = 1; i <= totalPages; i++) {
            const pageItem = createPageItem(i, false, i === currentPage);
            pageItem.addEventListener("click", (e) => {
                e.preventDefault();
                currentPage = i;
                renderTable();
            });
            pagination.appendChild(pageItem);
        }

        // Next button
        const nextItem = createPageItem("Next", currentPage === totalPages);
        nextItem.addEventListener("click", (e) => {
            e.preventDefault();
            if (currentPage < totalPages) {
                currentPage++;
                renderTable();
            }
        });
        pagination.appendChild(nextItem);
    }

    renderTable();
</script>

<script>
    const tableRows = document.querySelectorAll(".table-clickable tbody tr");
    for (const tableRow of tableRows) {
        tableRow.addEventListener("click", function () {
            window.open(this.dataset.href, "_blank");
        });
    }
</script>
</body>

</html>