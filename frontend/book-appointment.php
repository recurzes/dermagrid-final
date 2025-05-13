<?php
require_once '../backend/config/database.php';
require_once '../backend/models/Appointment.php';

// Initialize variables to store any error/success messages
$message = '';
$messageType = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get form data
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $contactNumber = $_POST['contact_number'];
    $email = $_POST['email'];
    $reason = $_POST['reason'];
    $appointmentDate = $_POST['selected_date'];
    $appointmentTime = $_POST['selected_time'];

    // Set default staff ID (you can modify this to let users select a doctor)
    $staffId = 1; // Default to first doctor in the system

    // Format patient data
    $patientData = [
        'first_name' => $firstName,
        'last_name' => $lastName,
        'phone' => $contactNumber,
        'email' => $email
    ];

    // Format appointment data
    $appointmentData = [
        'staff_id' => $staffId,
        'appointment_date' => $appointmentDate,
        'appointment_time' => $appointmentTime,
        'reason' => $reason
    ];

    try {
        // Connect to database
        $database = getDbConnection();
        $appointment = new Appointment($database);

        // Book appointment
        $result = $appointment->bookAppointment($patientData, $appointmentData, false);

        if ($result['success']) {
            // Redirect to appointment details page
            header("Location: appointmentdetails.php?id=" . $result['appointment_id']);
            exit();
        } else {
            $messageType = 'error';
            $message = 'Failed to book appointment: ' . $result['error'];
        }
    } catch (Exception $e) {
        $messageType = 'error';
        $message = 'An error occurred: ' . $e->getMessage();
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/style2.css">
    <title>Book Appointment</title>
</head>

<body>
    <div class="navbar-1">
        <div class="container">
            <div class="column">
                <div class="in-column">
                    <div class="norm-text">Opening hours:</div>
                    <div class="frame">
                        <div class="norm-text bold">Mon-Wed &amp; Fri</div>
                        <div class="norm-text">8:00am - 5:00pm</div>
                    </div>
                    <div class="frame">
                        <div class="norm-text bold">Thu</div>
                        <div class="norm-text">7:30am - 4:30pm</div>
                    </div>
                    <div class="frame">
                        <div class="norm-text bold">Sat-Sun</div>
                        <div class="norm-text">Closed</div>
                    </div>
                </div>
                <div class="in-column-2">
                    <a href="#" class="btn-outline">
                        <div>Place</div>
                    </a>
                    <a href="#" class="btn-solid">
                        <div>Place</div>
                    </a>
                    <a href="#" class="btn-outline">
                        <div>Place</div>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="navbar-2">
        <div class="container">
            <div class="column">
                <div class="in-column free">
                    <h2 class="logo-text">DermaGrid</h2>
                </div>
                <!-- <div class="in-column nav-link">
                    <a href="#" class="text-link">Dashboard</a>
                    <a href="#" class="text-link">Medical Records</a>
                    <a href="#" class="text-link">Appointments</a>
                </div> -->
                <div class="in-column btn">
                    <a href="#" class="btn-outline big navbar2">
                        <div>0912 3456 789</div>
                    </a>
                    <a href="#" class="btn-solid big navbar2">
                        <div>Book Appointment</div>
                    </a>
                    <img src="assets\menu.svg" loading="lazy" alt="" class="menu-icon" />
                </div>
            </div>
        </div>
    </div>
    <!-- <div class="side-bar">
        <div class="sidebar-menu">
            <div class="in-column nav-link mobile">
                <a href="#" class="text-link white">Dashboard</a>
                <a href="#" class="text-link white">Medical Records</a>
                <a href="#" class="text-link white">Appointments</a>
            </div>
        </div>
    </div> -->
    <div class="appointment">
        <div class="container">
            <div class="column">
                <div class="appointment-heading">
                    <h2>Book An Appointment</h2>
                    <div class="appointment-intro">
                        <h4>Hello, Let’s Talk !</h4>
                        <p>
                            Schedule a 30 min one-to-one Appointment to dicuss your challenges
                        </p>
                        <div class="optional">
                            <img src="assets\i.svg" alt="">
                            <p>This is optional but highly recommended!</p>
                        </div>
                    </div>
                </div>

                <!-- form -->
                <div class="form-div">
                    <h2>Input Details</h2>
                    <form action="book-appointment.php" method="POST" onsubmit="return attachDateTime()">
                        <div class="form-group">
                            <label for="first-name">First Name</label>
                            <input type="text" id="first-name" name="first_name" required placeholder="Placeholder">
                        </div>

                        <div class="form-group">
                            <label for="last-name">Last Name</label>
                            <input type="text" id="last-name" name="last_name" required placeholder="Placeholder">
                        </div>

                        <div class="form-group">
                            <label for="contact-number">Contact Number</label>
                            <input type="text" id="contact-number" name="contact_number" required placeholder="Placeholder">
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" required placeholder="Placeholder">
                        </div>

                        <div class="form-group">
                            <label for="reason">Reason for Visit/Concern</label>
                            <textarea id="reason" name="reason" placeholder="Placeholder" required></textarea>
                        </div>

                        <input type="hidden" name="selected_date" id="selected_date">
                        <input type="hidden" name="selected_time" id="selected_time">

                        <div class="calendar">
                            <h2>Choose a Date</h2>
                            <div class="header">
                                <select id="month"></select>
                                <select id="year"></select>
                            </div>
                            <div class="days" id="calendar-days"></div>
                        </div>

                        <div class="time-picker">
                            <h2>Pick a time</h2>
                            <div class="time-grid" id="timeGrid"></div>
                        </div>

                        <div class="in-column btn-2">
                            <button type="submit" class="btn btn-sm btn-blue hover-blue fw-semibold actn-btn">
                                Book Appointment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let selectedDate = '';
        let selectedTime = '';

        function attachDateTime() {
            if (!selectedDate || !selectedTime) {
                alert('Please select both a date and a time.');
                return false;
            }
            document.getElementById('selected_date').value = selectedDate;
            document.getElementById('selected_time').value = selectedTime;
            return true;
        }

        const calendarDays = document.getElementById("calendar-days");
        const monthSelect = document.getElementById("month");
        const yearSelect = document.getElementById("year");

        const today = new Date();
        const months = ["January", "February", "March", "April", "May", "June",
            "July", "August", "September", "October", "November", "December"
        ];

        months.forEach((month, i) => {
            const opt = document.createElement("option");
            opt.value = i;
            opt.textContent = month;
            monthSelect.appendChild(opt);
        });

        for (let y = 2020; y <= 2030; y++) {
            const opt = document.createElement("option");
            opt.value = y;
            opt.textContent = y;
            yearSelect.appendChild(opt);
        }

        function renderCalendar(month, year) {
            calendarDays.innerHTML = "";
            const firstDay = new Date(year, month, 1).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const dayHeaders = ["M", "Tu", "W", "Th", "Fr", "Sa", "Su"];

            dayHeaders.forEach(d => {
                const div = document.createElement("div");
                div.className = "header";
                div.textContent = d;
                calendarDays.appendChild(div);
            });

            const startDay = (firstDay + 6) % 7;
            for (let i = 0; i < startDay; i++) {
                const blank = document.createElement("div");
                blank.className = "inactive";
                calendarDays.appendChild(blank);
            }

            for (let d = 1; d <= daysInMonth; d++) {
                const cell = document.createElement("div");
                cell.textContent = d;
                cell.addEventListener("click", () => {
                    document.querySelectorAll(".days div").forEach(el => el.classList.remove("selected"));
                    cell.classList.add("selected");
                    selectedDate = `${year}-${(month+1).toString().padStart(2,'0')}-${d.toString().padStart(2,'0')}`;
                });
                calendarDays.appendChild(cell);
            }
        }

        monthSelect.value = today.getMonth();
        yearSelect.value = today.getFullYear();
        renderCalendar(today.getMonth(), today.getFullYear());

        monthSelect.addEventListener("change", () => {
            renderCalendar(+monthSelect.value, +yearSelect.value);
        });

        yearSelect.addEventListener("change", () => {
            renderCalendar(+monthSelect.value, +yearSelect.value);
        });

        const timeGrid = document.getElementById("timeGrid");
        const times = ["8:30", "9:30", "10:30", "11:30", "12:30", "1:30", "2:30", "3:30", "4:30"];

        times.forEach(time => {
            const div = document.createElement("div");
            div.className = "time-slot";
            div.textContent = time;
            div.addEventListener("click", () => {
                document.querySelectorAll(".time-slot").forEach(el => el.classList.remove("selected"));
                div.classList.add("selected");
                selectedTime = time;
            });
            timeGrid.appendChild(div);
        });
    </script>
</body>

</html>