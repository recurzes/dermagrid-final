<?php
// Start session at the very beginning of the file
session_start();

require_once '../backend/config/database.php';
require_once '../backend/models/MedicalRecord.php';

$record_id = isset($_GET['record']) ? (int)$_GET['record'] : -1;
$parsed = [];
$debug_record = null; // For debugging
$message = '';
$error = '';

// Initialize database connection and models
$database = getDbConnection();
$medicalRecordModel = new MedicalRecord($database);

// Handle form submission for updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_record'])) {
    // Get form data
    $updateData = [
        'patient_id' => $_POST['patient_id'] ?? null,
        'staff_id' => $_POST['staff_id'] ?? null,
        'visit_date' => $_POST['visit_date'] ?? null,
        'diagnosis' => $_POST['diagnosis'] ?? null,
        'treatment_plan' => $_POST['treatment_plan'] ?? null,
        'notes' => $_POST['clinical_notes'] ?? null,
        'chief_complaint' => $_POST['chief_complaint'] ?? null,
        'skin_type' => $_POST['skin_type'] ?? null,
        'instructions' => $_POST['instructions'] ?? null,
    ];
    
    // Handle the appointment_id - if it's empty or invalid, set to NULL
    $appointment_id = $_POST['appointment_id'] ?? null;
    if (!empty($appointment_id) && is_numeric($appointment_id)) {
        $updateData['appointment_id'] = $appointment_id;
    } else {
        $updateData['appointment_id'] = null;
    }
    
    // Handle the prescription_id - if it's empty or invalid, set to NULL
    $prescription_id = $_POST['prescription_id'] ?? null;
    if (!empty($prescription_id) && is_numeric($prescription_id)) {
        $updateData['prescription_id'] = $prescription_id;
    } else {
        $updateData['prescription_id'] = null;
    }

    // Handle image update if a new one is uploaded
    if (isset($_FILES['image_file']) && $_FILES['image_file']['size'] > 0) {
        $uploadDir = '../uploads/medical/';
        
        // Create the directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true)) {
                $error = "Failed to create upload directory.";
            }
        }
        
        $file = $_FILES['image_file'];
        
        // Debug info
        error_log("Uploading file: " . $file['name'] . ", size: " . $file['size'] . ", type: " . $file['type']);
        
        // Validate file
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        if (in_array($file['type'], $allowedTypes) && $file['size'] <= $maxSize) {
            // Generate unique filename
            $filename = uniqid() . '_' . basename($file['name']);
            $targetFile = $uploadDir . $filename;
            
            // Upload file
            if (move_uploaded_file($file['tmp_name'], $targetFile)) {
                $updateData['image_path'] = $targetFile;
                error_log("File uploaded successfully to: " . $targetFile);
            } else {
                $error = "Failed to upload file. PHP Error: " . error_get_last()['message'];
                error_log("Failed to upload file: " . error_get_last()['message']);
            }
        } else {
            if (!in_array($file['type'], $allowedTypes)) {
                $error = "Invalid file type. Please upload JPEG, PNG, or GIF.";
            } else {
                $error = "File size exceeds 5MB limit.";
            }
        }
    }

    // Update the record
    try {
        $result = $medicalRecordModel->update($record_id, $updateData);
        
        if ($result['success']) {
            $message = "Medical record updated successfully.";
        } else {
            $error = "Error updating record: " . ($result['error'] ?? "Unknown error");
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Fetch medical record from database
try {
    $record = $medicalRecordModel->getById($record_id);
    $debug_record = $record; // Store for debugging
    
    if (!$record) {
        // Record not found
        $error = "Medical record not found";
    } else {
        // Map database fields to view/edit fields
        $parsed = [
            'patient_id' => $record['patient_id'] ?? '',
            'staff_id' => $record['staff_id'] ?? '',
            'appointment_id' => $record['appointment_id'] ?? '',
            'prescription_id' => $record['prescription_id'] ?? '',
            'patient_name' => $record['patient_name'] ?? '',
            'doctor' => $record['doctor_name'] ?? '',
            'booked_on' => $record['visit_date'] ?? '',
            'recommended_treatment' => $record['treatment_plan'] ?? '',
            'prescriptions_given' => $record['prescription_name'] ?? '',
            'diagnosis' => $record['diagnosis'] ?? '',
            'treatment' => $record['treatment_plan'] ?? '',
            'chief_complaint' => $record['chief_complaint'] ?? '',
            'skin_type' => $record['skin_type'] ?? '',
            'clinical_notes' => $record['notes'] ?? '',
            'instructions' => $record['instructions'] ?? '',
            'saved_on' => $record['created_at'] ?? '',
            'image_path' => $record['image_path'] ?? ''
        ];
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}

// Uncomment for debugging
// echo '<pre>Database Record: '; print_r($debug_record); echo '</pre>';
// echo '<pre>Parsed Record: '; print_r($parsed); echo '</pre>';

// Get patient ID from request or record
$patient_id = $_GET['patient_id'] ?? $record['patient_id'] ?? null;

// Get prescriptions for this patient only
$prescriptions = [];
if ($patient_id) {
    $stmt = $database->prepare("SELECT id, medication_name FROM prescription WHERE patient_id = ?");
    $stmt->execute([$patient_id]);
    $prescriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Check if we're in edit mode
$edit_mode = isset($_GET['edit']) && $_GET['edit'] == '1';

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

        /* Add a style for edit mode */
        .edit-mode-controls {
            display: none;
        }
        .edit-mode .edit-mode-controls {
            display: block;
        }
        .edit-mode .view-mode-controls {
            display: none;
        }
    </style>
</head>

<body id="page-top" class="<?= $edit_mode ? 'edit-mode' : '' ?>">

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
                    echo '<h1 class="h3 m-auto text-gray-800">View Medical Record</h1>';
                    echo '</nav>';
                }
                ?>

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Content Row -->
                    <div class="row">

                        <!-- Content Column -->
                        <div class="col">
                            <main class="container-fluid">
                                <p class="text-muted small mb-1"><?= $edit_mode ? 'Editing' : 'Viewing' ?> Record</p>
                                <h1 class="h5 fw-bold border-bottom border-dark pb-2 mb-4">Medical Record Details</h1>

                                <?php if (!empty($message)): ?>
                                <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
                                <?php endif; ?>
                                
                                <?php if (!empty($error)): ?>
                                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                                <?php endif; ?>
                                
                                <form action="viewmedical.php?record=<?= $record_id ?>&edit=1" method="post" enctype="multipart/form-data">
                                    <!-- Add hidden fields for IDs -->
                                    <input type="hidden" name="patient_id" value="<?= htmlspecialchars($parsed['patient_id']) ?>">
                                    <input type="hidden" name="staff_id" value="<?= htmlspecialchars($parsed['staff_id']) ?>">
                                    <input type="hidden" name="appointment_id" value="<?= htmlspecialchars($parsed['appointment_id']) ?>">
                                    <input type="hidden" name="prescription_id" value="<?= htmlspecialchars($parsed['prescription_id']) ?>">
                                    <input type="hidden" name="visit_date" value="<?= htmlspecialchars($parsed['booked_on']) ?>">
                                    
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
                                                <textarea class="form-control form-control-sm" name="treatment_plan" rows="3" <?= $edit_mode ? '' : 'readonly' ?>><?= htmlspecialchars($parsed['treatment']) ?></textarea>
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
                                                <input type="text" name="chief_complaint" class="form-control form-control-sm" value="<?= htmlspecialchars($parsed['chief_complaint']) ?>" <?= $edit_mode ? '' : 'readonly' ?>>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold">Diagnosis</label>
                                                <textarea class="form-control form-control-sm" name="diagnosis" rows="3" <?= $edit_mode ? '' : 'readonly' ?>><?= htmlspecialchars($parsed['diagnosis']) ?></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold">Skin Type</label>
                                                <?php if ($edit_mode): ?>
                                                <select name="skin_type" class="form-select form-select-sm">
                                                    <option value="oily" <?= $parsed['skin_type'] == 'oily' ? 'selected' : '' ?>>Oily</option>
                                                    <option value="dry" <?= $parsed['skin_type'] == 'dry' ? 'selected' : '' ?>>Dry</option>
                                                    <option value="combination" <?= $parsed['skin_type'] == 'combination' ? 'selected' : '' ?>>Combination</option>
                                                    <option value="sensitive" <?= $parsed['skin_type'] == 'sensitive' ? 'selected' : '' ?>>Sensitive</option>
                                                </select>
                                                <?php else: ?>
                                                <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars($parsed['skin_type']) ?>" readonly>
                                                <?php endif; ?>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold">Clinical Notes / Observations</label>
                                                <textarea class="form-control form-control-sm" name="clinical_notes" rows="3" <?= $edit_mode ? '' : 'readonly' ?>><?= htmlspecialchars($parsed['clinical_notes']) ?></textarea>
                                            </div>
                                            <h2 class="h6 fw-semibold border-bottom pb-1 mb-3">Follow Up Info</h2>
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold">Instructions to Patient</label>
                                                <textarea class="form-control form-control-sm" name="instructions" rows="3" <?= $edit_mode ? '' : 'readonly' ?>><?= htmlspecialchars($parsed['instructions']) ?></textarea>
                                            </div>
                                        </div>

                                        <!-- Image Display -->
                                        <div class="col-12 col-md-4">
                                            <h2 class="h6 fw-semibold border-bottom pb-1 mb-3">Clinical Image</h2>
                                            <div class="card shadow">
                                                <div class="card-body text-center">
                                                    <?php if (!empty($parsed['image_path']) && file_exists($parsed['image_path'])): ?>
                                                        <img src="<?= htmlspecialchars($parsed['image_path']) ?>" 
                                                             class="img-fluid rounded" 
                                                             alt="Clinical image"
                                                             style="max-height: 300px;">
                                                        <p class="small text-muted mt-2">Uploaded clinical image</p>
                                                    <?php else: ?>
                                                        <div class="py-5 bg-light rounded">
                                                            <i class="fas fa-image fa-4x text-muted mb-3"></i>
                                                            <p class="text-muted">No image available for this record</p>
                                                        </div>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($edit_mode): ?>
                                                    <div class="mt-3 edit-mode-controls">
                                                        <label class="form-label small fw-semibold">Upload New Image</label>
                                                        <input type="file" class="form-control form-control-sm" name="image_file" accept="image/*">
                                                        <p class="small text-muted mt-1">Supported formats: JPEG, PNG, GIF</p>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            
                                            <!-- Download link if image exists -->
                                            <?php if (!empty($parsed['image_path']) && file_exists($parsed['image_path'])): ?>
                                            <div class="text-center mt-2">
                                                <a href="<?= htmlspecialchars($parsed['image_path']) ?>" 
                                                   class="btn btn-sm btn-outline-primary" 
                                                   download>
                                                    <i class="fas fa-download"></i> Download Image
                                                </a>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Action buttons -->
                                    <div class="mt-4">
                                        <!-- View mode buttons -->
                                        <div class="view-mode-controls">
                                            <a href="javascript:history.go(-1)" class="btn btn-secondary">
                                                <i class="fas fa-arrow-left"></i> Back to Records
                                            </a>
                                            <a href="viewmedical.php?record=<?= $record_id ?>&edit=1" class="btn btn-primary">
                                                <i class="fas fa-edit"></i> Edit Record
                                            </a>
                                        </div>
                                        
                                        <!-- Edit mode buttons -->
                                        <div class="edit-mode-controls">
                                            <button type="submit" name="update_record" class="btn btn-success">
                                                <i class="fas fa-save"></i> Save Changes
                                            </button>
                                            <a href="viewmedical.php?record=<?= $record_id ?>" class="btn btn-secondary">
                                                <i class="fas fa-times"></i> Cancel
                                            </a>
                                        </div>
                                    </div>
                                </form>
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
    document.addEventListener('DOMContentLoaded', function() {
        const imageInput = document.querySelector('input[name="image_file"]');
        if (imageInput) {
            imageInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const imgElement = document.querySelector('.card-body img');
                        if (imgElement) {
                            imgElement.src = e.target.result;
                            imgElement.style.display = 'block';
                            document.querySelector('.py-5.bg-light.rounded')?.style.display = 'none';
                        } else {
                            const imgContainer = document.querySelector('.card-body');
                            const newImg = document.createElement('img');
                            newImg.src = e.target.result;
                            newImg.classList.add('img-fluid', 'rounded');
                            newImg.style.maxHeight = '300px';
                            imgContainer.querySelector('.py-5.bg-light.rounded').style.display = 'none';
                            imgContainer.prepend(newImg);
                        }
                    }
                    reader.readAsDataURL(file);
                }
            });
        }
    });
    </script>

</body>

</html>
