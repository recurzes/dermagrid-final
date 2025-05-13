CREATE TABLE IF NOT EXISTS appointment (
    id int(11) NOT NULL AUTO_INCREMENT,
    patient_id int(11) NOT NULL,
    staff_id int(11) NOT NULL,
    appointment_date date NOT NULL,
    appointment_time time NOT NULL,
    status enum('scheduled','completed','cancelled','no-show') NOT NULL DEFAULT 'scheduled',
    reason text DEFAULT NULL,
    notes text DEFAULT NULL,
    created_at timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (id),
    KEY patient_id (patient_id),
    KEY staff_id (staff_id),
    CONSTRAINT appointment_ibfk_1 FOREIGN KEY (patient_id) REFERENCES patient (id) ON DELETE CASCADE,
    CONSTRAINT appointment_ibfk_2 FOREIGN KEY (staff_id) REFERENCES staff (id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS medical_record (
    id int(11) NOT NULL AUTO_INCREMENT,
    patient_id int(11) NOT NULL,
    staff_id int(11) NOT NULL,
    appointment_id int(11) DEFAULT NULL,
    visit_date date NOT NULL,
    diagnosis text DEFAULT NULL,
    treatment_plan text DEFAULT NULL,
    notes text DEFAULT NULL,
    created_at timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (id),
    KEY patient_id (patient_id),
    KEY staff_id (staff_id),
    KEY appointment_id (appointment_id),
    CONSTRAINT medical_record_ibfk_1 FOREIGN KEY (patient_id) REFERENCES patient (id) ON DELETE CASCADE,
    CONSTRAINT medical_record_ibfk_2 FOREIGN KEY (staff_id) REFERENCES staff (id) ON DELETE CASCADE,
    CONSTRAINT medical_record_ibfk_3 FOREIGN KEY (appointment_id) REFERENCES appointment (id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS patient (
    id int(11) NOT NULL AUTO_INCREMENT,
    first_name varchar(100) NOT NULL,
    last_name varchar(100) NOT NULL,
    date_of_birth date DEFAULT NULL,
    gender varchar(10) DEFAULT NULL,
    email varchar(100) DEFAULT NULL,
    phone varchar(20) DEFAULT NULL,
    address text DEFAULT NULL,
    registration_date date DEFAULT curdate(),
    PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS prescription (
    id int(11) NOT NULL AUTO_INCREMENT,
    patient_id int(11) NOT NULL,
    staff_id int(11) NOT NULL,
    medication_name varchar(100) NOT NULL,
    dosage varchar(50) DEFAULT NULL,
    frequency varchar(50) DEFAULT NULL,
    duration varchar(50) DEFAULT NULL,
    instructions text DEFAULT NULL,
    status varchar(20) DEFAULT 'active' CHECK (status in ('active','completed','cancelled')),
    created_at timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (id),
    KEY patient_id (patient_id),
    KEY staff_id (staff_id),
    CONSTRAINT prescription_ibfk_1 FOREIGN KEY (patient_id) REFERENCES patient (id) ON DELETE CASCADE,
    CONSTRAINT prescription_ibfk_2 FOREIGN KEY (staff_id) REFERENCES staff (id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS staff (
    id int(11) NOT NULL AUTO_INCREMENT,
    first_name varchar(100) NOT NULL,
    last_name varchar(100) NOT NULL,
    role varchar(20) NOT NULL CHECK (role in ('doctor','nurse','receptionist')),
    email varchar(100) DEFAULT NULL,
    phone varchar(20) NOT NULL,
    username varchar(50) NOT NULL,
    password_hash varchar(255) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY username (username)
);

CREATE PROCEDURE if not exists GetAppointmentStats()
BEGIN
SELECT COUNT(*)                            AS total_appointments,
       SUM(IF(status = 'completed', 1, 0)) AS total_completed,
       SUM(IF(status = 'scheduled', 1, 0)) AS total_scheduled,
       SUM(IF(status = 'cancelled', 1, 0)) AS total_cancelled,
       SUM(IF(status = 'no-show', 1, 0))   AS total_no_show
FROM appointment;
END;

CREATE PROCEDURE if not exists GetStaffRolesSummary()
BEGIN
SELECT
    COUNT(*)                                               AS total_staff,
    SUM(IF(role = 'doctor', 1, 0))                         AS total_doctors,
    SUM(IF(role = 'nurse', 1, 0))                          AS total_nurses,
    SUM(IF(role = 'receptionist', 1, 0)) AS total_receptionists
FROM staff;
END

INSERT INTO staff VALUES (1,'Lance','Limbaro','doctor','donlancelotknight123@gmail.com','09700651307','recurzes','$2y$10$jl8LNLAqxVZ/U4jxXw6PR.cq4xxyMtKpvuohU/vJNa.Zr0UxvgDWy');