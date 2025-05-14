
<?php

class Appointment
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function update($id, $patient_id, $staff_id, $appointment_date, $appointment_time, $status, $reason, $notes = null) {
        try {
            // Call the UpdateAppointment stored procedure
            $sql = "CALL UpdateAppointment(?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(1, $id, PDO::PARAM_INT);
            $stmt->bindParam(2, $patient_id, PDO::PARAM_INT);
            $stmt->bindParam(3, $staff_id, PDO::PARAM_INT);
            $stmt->bindParam(4, $appointment_date, PDO::PARAM_STR);
            $stmt->bindParam(5, $appointment_time, PDO::PARAM_STR);
            $stmt->bindParam(6, $status, PDO::PARAM_STR);
            $stmt->bindParam(7, $reason, PDO::PARAM_STR);
            $stmt->bindParam(8, $notes, PDO::PARAM_STR);

            $result = $stmt->execute();

            if (!$result) {
                $errorInfo = $stmt->errorInfo();
                throw new Exception("Database error: " . $errorInfo[2]);
            }

            return $result;
        } catch (Exception $e) {
            error_log("Appointment update error: " . $e->getMessage());
            throw $e; // Re-throw so the caller can handle it
        }
    }

    public function getAll($status = null)
    {
        try {
            $appointments = [];
            $query = "CALL GetAppointments(?)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$status]);

            while ($row = $stmt->fetch()) {
                $appointments[] = [
                    'id' => $row['id'],
                    'name' => $row['patient_name'],
                    'number' => $row['contact_number'],
                    'doctor' => $row['doctor_name'],
                    'appointment_date' => $row['appointment_date'],
                    'appointment_time' => $row['appointment_time'],
                    'status' => $row['status'],
                    'booked_on' => $row['booked_on'],
                ];
            }

            $stmt->closeCursor();
            return $appointments;
        } catch (PDOException $e) {
            // Log error and handle appropriately
            throw new Exception("Error fetching appointments: " . $e->getMessage());
        }
    }

    public function getAppointmentStats()
    {
        try {
            $appointment_stats = [];
            $query = "CALL GetAppointmentStats()";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            if ($stmt) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($row) {
                    $appointment_stats[] = [
                        'total_appointments' => $row['total_appointments'],
                        'total_completed' => $row['total_completed'],
                        'total_scheduled' => $row['total_scheduled'],
                        'total_cancelled' => $row['total_cancelled'],
                        'total_no_show' => $row['total_no_show']
                    ];
                }

                $stmt->closeCursor();
            } else {
                throw new Exception("Error calling stored procedure");
            }

            return $appointment_stats;
        } catch (PDOException $e) {
            throw new Exception("Error fetching appointment stats: " . $e->getMessage());
        }
    }

    public function bookAppointment($patientData, $appointmentData, $updateExisting = false)
    {
        try {
            $query = "CALL BookAppointment(?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                $patientData['first_name'],
                $patientData['last_name'],
                $patientData['email'],
                $patientData['phone'],
                $appointmentData['staff_id'],
                $appointmentData['appointment_date'],
                $appointmentData['appointment_time'],
                $appointmentData['reason'],
                $updateExisting
            ]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            if ($result) {
                return [
                    'success' => true,
                    'appointment_id' => $result['appointment_id'],
                    'patient_id' => $result['patient_id']
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Failed to book appointment'
                ];
            }
        } catch (PDOException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function getById($id)
    {
        try {
            $query = "CALL GetAppointmentById(?)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);

            $appointment = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            return $appointment;
        } catch (PDOException $e) {
            throw new Exception("Error fetching appointment details: " . $e->getMessage());
        }
    }

    public function updateStatus($appointmentId, $status)
    {
        try {
            $query = "CALL UpdateAppointmentStatus(?, ?)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$appointmentId, $status]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            return [
                'success' => true,
                'rows_updated' => $result['rows_updated'] ?? 0
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'error' => "Error updating appointment status: " . $e->getMessage()
            ];
        }
    }

    public function getByPatient($patientId)
    {
        try {
            $query = "CALL GetAppointmentsByPatient(?)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$patientId]);

            $appointments = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $appointments[] = $row;
            }
            $stmt->closeCursor();

            return $appointments;
        } catch (PDOException $e) {
            throw new Exception("Error fetching patient appointments: " . $e->getMessage());
        }
    }
}
