<?php
// Initialize the session
session_start();

// // Check if the user is logged in, if not then redirect to login page
// if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
//     header("location: login.php");
//     exit;
// }
?>
<?php
require_once __DIR__ . '/../backend/config/database.php';
require_once __DIR__ . '/../backend/models/Staff.php';

// Get staff statistics
$staffStats = ['total_doctors' => 0, 'total_nurses' => 0, 'total_receptionists' => 0, 'total_staff' => 0];
$doctors = [];
try {
    $database = getDbConnection();
    $staffModel = new Staff($database);
    $staffStats = $staffModel->getStaffStats();
    $doctors = $staffModel->getByRole('doctor');
} catch (Exception $e) {
    // Handle error silently
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

    <title>DermaGrid - Doctors & Staff</title>

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

            <li class="nav-item">
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
                    echo '<h1 class="h3 m-auto text-gray-800">Doctors & Staff</h1>';
                    echo '</nav>';
                }
                ?>

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Content Col -->
                    <div class="col">

                        <div class="row text-center">

                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="card shadow h-100 py-2">
                                    <div class="card-body py-5 px-3">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-3">
                                                    Total Staff</div>
                                                <div class="h2 mb-0 font-weight-bold text-gray-800">
                                                    <?php echo $staffStats['total_staff']; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="card shadow h-100 py-2">
                                    <div class="card-body py-5 px-3">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-3">
                                                    Total No. of Doctors</div>
                                                <div class="h2 mb-0 font-weight-bold text-gray-800">
                                                    <?php echo $staffStats['total_doctors']; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="card shadow h-100 py-2">
                                    <div class="card-body py-5 px-3">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-3">
                                                    Nurses</div>
                                                <div class="h2 mb-0 font-weight-bold text-gray-800">
                                                    <?php echo $staffStats['total_nurses']; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="card shadow h-100 py-2">
                                    <div class="card-body py-5 px-3">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-3">
                                                    Receptionists</div>
                                                <div class="h2 mb-0 font-weight-bold text-gray-800">
                                                    <?php echo $staffStats['total_receptionists']; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Content Column -->
                        <div class="col">

                            <!-- Doctors Table -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Doctors List</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive table-striped">
                                        <table class="table table-bordered table-hover table-clickable">
                                            <thead class="thead-dark">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Name</th>
                                                    <th>Email</th>
                                                    <th>Phone</th>
                                                    <th>Username</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($doctors)): ?>
                                                    <?php foreach ($doctors as $index => $doctor): ?>
                                                        <tr data-href="doctordetails.php?id=<?= $doctor['id'] ?>">
                                                            <td><?= $index + 1 ?></td>
                                                            <td><?= htmlspecialchars($doctor['full_name']) ?></td>
                                                            <td><?= htmlspecialchars($doctor['email']) ?></td>
                                                            <td><?= htmlspecialchars($doctor['phone']) ?></td>
                                                            <td><?= htmlspecialchars($doctor['username']) ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted">No doctors found.</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <!-- End Table -->

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
        // First, add the clickable class to the table
        document.querySelector(".table").classList.add("table-clickable");

        // Then handle the clicks
        const tableRows = document.querySelectorAll(".table-clickable tbody tr");
        for (const tableRow of tableRows) {
            tableRow.addEventListener("click", function() {
                if (this.dataset.href) {
                    window.location.href = this.dataset.href;
                }
            });
        }

    </script>

</body>

</html>