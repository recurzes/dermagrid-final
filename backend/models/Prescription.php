<?php
class Prescription
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create($data)
    {
        try {
            $query = "CALL CreatePrescription(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                $data['patient_id'],
                $data['staff_id'],
                $data['medication_name'],
                $data['dosage'],
                $data['frequency'],
                $data['duration'],
                $data['instructions'],
                $data['status'] ?? 'active',
                $data['unit'],
                $data['additional_instruction'] ?? null
            ]);

            $stmt->closeCursor();

            // Let's get the last inserted ID
            $query = "SELECT LAST_INSERT_ID() as id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'id' => $result['id'] ?? null,
                'message' => 'Prescription created successfully'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function getAll()
    {
        try {
            $query = "CALL GetAllPrescriptions()";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            $prescriptions = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $prescriptions[] = $row;
            }

            $stmt->closeCursor();
            return $prescriptions;
        } catch (PDOException $e) {
            throw new Exception("Error fetching prescriptions: " . $e->getMessage());
        }
    }

    public function getById($id)
    {
        try {
            $query = "CALL GetPrescriptionById(?)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);

            $prescription = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            return $prescription;
        } catch (PDOException $e) {
            throw new Exception("Error fetching prescription: " . $e->getMessage());
        }
    }

    public function getByPatient($patientId)
    {
        try {
            $query = "CALL GetPrescriptionsByPatient(?)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$patientId]);

            $prescriptions = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $prescriptions[] = $row;
            }

            $stmt->closeCursor();
            return $prescriptions;
        } catch (PDOException $e) {
            throw new Exception("Error fetching patient prescriptions: " . $e->getMessage());
        }
    }

    public function update($id, $data)
    {
        try {
            $query = "CALL UpdatePrescription(?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                $id,
                $data['patient_id'],
                $data['staff_id'],
                $data['medication_name'],
                $data['dosage'],
                $data['frequency'],
                $data['duration'],
                $data['instructions'],
                $data['status'] ?? 'active'
            ]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            return [
                'success' => true,
                'rows_updated' => $result['rows_updated'] ?? 0,
                'message' => 'Prescription updated successfully'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function updateStatus($id, $status)
    {
        try {
            $query = "CALL UpdatePrescriptionStatus(?, ?)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id, $status]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            return [
                'success' => true,
                'rows_updated' => $result['rows_updated'] ?? 0,
                'message' => 'Prescription status updated successfully'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function delete($id)
    {
        try {
            $query = "CALL DeletePrescription(?)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            return [
                'success' => true,
                'rows_deleted' => $result['rows_deleted'] ?? 0,
                'message' => 'Prescription deleted successfully'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
