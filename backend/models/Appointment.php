
<?php

class Appointment
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
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
}
