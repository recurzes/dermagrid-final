<?php
// Only start session if headers haven't been sent and session isn't active
if (!headers_sent() && session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Current page filename
$currentPage = basename($_SERVER['PHP_SELF']);
// Pages that shouldn't show logout
$excludedPages = ['signup.php', 'login.php', 'homepage.php'];

// Only show logout if page is not excluded
$showLogout = !in_array($currentPage, $excludedPages);

// Check if we're on the appointments page to show search
$isAppointments = ($currentPage === 'appointmentdetails.php' || $currentPage === 'appointments.php');

// Check if we're on the dashboard page
$isDashboard = ($currentPage === 'dashboard.php' || isset($isDashboard));

// Include database connection if not already included
if (!function_exists('getDbConnection')) {
    require_once __DIR__ . '/../../backend/config/database.php';
}

// Get user information from database if logged in
$user_name = "User";

// Check all possible session variables that might contain user identification
$user_id = null;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} elseif (isset($_SESSION['id'])) {
    $user_id = $_SESSION['id'];
} elseif (isset($_SESSION['staff_id'])) {
    $user_id = $_SESSION['staff_id'];
} elseif (isset($_SESSION['doctor_id'])) {
    $user_id = $_SESSION['doctor_id'];
}

// If we have a user ID, get the name from database
if ($user_id) {
    try {
        $database = getDbConnection();
        $stmt = $database->prepare("SELECT first_name, last_name FROM staff WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $user_name = $user['first_name'] . ' ' . $user['last_name'];
        } elseif (isset($_SESSION['first_name']) && isset($_SESSION['last_name'])) {
            // Fallback to session variables if database lookup fails
            $user_name = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];
        } elseif (isset($_SESSION['username'])) {
            // Use username if available
            $user_name = $_SESSION['username'];
        }
    } catch (PDOException $e) {
        // If database lookup fails, try to use session variables directly
        if (isset($_SESSION['first_name']) && isset($_SESSION['last_name'])) {
            $user_name = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];
        } elseif (isset($_SESSION['username'])) {
            $user_name = $_SESSION['username'];
        }
    }
} elseif (isset($_SESSION['first_name']) && isset($_SESSION['last_name'])) {
    // If no user ID but we have name in session
    $user_name = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];
} elseif (isset($_SESSION['username'])) {
    // Use username if available
    $user_name = $_SESSION['username'];
} elseif (isset($_SESSION['email'])) {
    // Last resort - use email
    $user_name = $_SESSION['email'];
}
?>

<!-- Topbar -->
<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
    <!-- Sidebar Toggle (Topbar) -->
    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
        <i class="fa fa-bars"></i>
    </button>

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-center">
        <h1 class="h3 mb-0 text-gray-800">
            <?php echo isset($_SESSION["user_id"]) ? htmlspecialchars($user_name) : "User"; ?>
        </h1>
    </div>

    <!-- Topbar Search -->
    <?php if ($isAppointments): ?>
    <form class="d-none d-sm-inline-block form-inline ml-auto my-2 my-md-0 mw-100 navbar-search">
        <div class="input-group">
            <input type="text" class="form-control bg-light border-0 small"
                   placeholder="Search appointments..."
                   aria-label="Search" aria-describedby="basic-addon2" id="searchInput">
            <div class="input-group-append">
                <button class="btn btn-primary" type="button" onclick="searchAppointments()">
                    <i class="fas fa-search fa-sm"></i>
                </button>
            </div>
        </div>
    </form>
    <?php else: ?>
    <!-- Regular disabled search for other pages -->
    <form class="d-none d-sm-inline-block form-inline ml-auto my-2 my-md-0 mw-100 navbar-search">
        <div class="input-group">
            <input type="text" class="form-control bg-light border-0 small" placeholder="Search for..."
                aria-label="Search" aria-describedby="basic-addon2" disabled>
            <div class="input-group-append">
                <button class="btn btn-primary" type="button" disabled>
                    <i class="fas fa-search fa-sm"></i>
                </button>
            </div>
        </div>
    </form>
    <?php endif; ?>

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
                               aria-describedby="basic-addon2" <?php echo $isAppointments ? '' : 'disabled'; ?>>
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="button" <?php echo $isAppointments ? 'onclick="searchAppointments()"' : 'disabled'; ?>>
                                <i class="fas fa-search fa-sm"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </li>

        <!-- Notifications section completely removed -->

        <?php if ($showLogout): ?>
            <div class="topbar-divider d-none d-sm-block"></div>

            <!-- Nav Item - User Information -->
            <li class="nav-item dropdown no-arrow">
                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                   data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                        <?php echo htmlspecialchars($user_name); ?>
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
        <?php endif; ?>
    </ul>
</nav>
<!-- End of Topbar -->

<?php if ($showLogout): ?>
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
<?php endif; ?>
