<?php

class MedicalRecord
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAll()
    {
        try {
            $records = [];
            $query = "CALL GetAllMedicalRecords()";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $records[] = $row;
            }

            $stmt->closeCursor();
            return $records;
        } catch (PDOException $e) {
            throw new Exception("Error fetching medical records: " . $e->getMessage());
        }
    }

    public function getById($id)
    {
        try {
            $query = "CALL GetMedicalRecordDetailsById(?)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);
            $record = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            return $record;
        } catch (PDOException $e) {
            throw new Exception("Error fetching medical record: " . $e->getMessage());
        }
    }

    public function getByPatient($patientId)
    {
        try {
            $records = [];
            $query = "CALL GetMedicalRecordsByPatient(?)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$patientId]);

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $records[] = $row;
            }

            $stmt->closeCursor();
            return $records;
        } catch (PDOException $e) {
            throw new Exception("Error fetching patient medical records: " . $e->getMessage());
        }
    }

    private function validatePrescriptionPatient($prescription_id, $patient_id)
    {
        if (!$prescription_id || !$patient_id) {
            return true; // No validation needed if no prescription ID
        }

        $query = "SELECT patient_id FROM prescription WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$prescription_id]);
        $prescription = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if (!$prescription) {
            throw new Exception("Prescription not found");
        }

        if ($prescription['patient_id'] != $patient_id) {
            throw new Exception("Cannot link prescription: it belongs to a different patient");
        }

        return true;
    }

    public function create($data)
    {
        try {
            // Validate prescription belongs to the same patient
            if (isset($data['prescription_id']) && !empty($data['prescription_id'])) {
                $this->validatePrescriptionPatient($data['prescription_id'], $data['patient_id']);
            }

            $query = "CALL CreateMedicalRecord(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                $data['patient_id'],
                $data['staff_id'],
                $data['appointment_id'] ?? null,
                $data['visit_date'],
                $data['diagnosis'] ?? null,
                $data['treatment_plan'] ?? null,
                $data['notes'] ?? null,
                $data['prescription_id'] ?? null,
                $data['chief_complaint'] ?? null,
                $data['skin_type'] ?? null,
                $data['instructions'] ?? null,
                $data['image_path'] ?? null
            ]);
            $stmt->closeCursor();

            return [
                'success' => true,
                'message' => 'Medical record created successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function update($id, $data)
    {
        try {
            // Validate prescription belongs to the same patient if it's being updated
            if (isset($data['prescription_id']) && !empty($data['prescription_id'])) {
                $this->validatePrescriptionPatient($data['prescription_id'], $data['patient_id']);
            }

            // Check if we need to update prescription_id
            if (isset($data['prescription_id'])) {
                // First update prescription_id separately as it's not in the stored procedure
                $updatePrescriptionQuery = "UPDATE medical_record SET prescription_id = ? WHERE id = ?";
                $updateStmt = $this->conn->prepare($updatePrescriptionQuery);
                $updateStmt->execute([$data['prescription_id'], $id]);
                $updateStmt->closeCursor();
            }

            // Now call the standard update procedure
            $query = "CALL UpdateMedicalRecord(?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                $id,
                $data['patient_id'],
                $data['staff_id'],
                $data['appointment_id'] ?? null,
                $data['visit_date'],
                $data['diagnosis'] ?? null,
                $data['treatment_plan'] ?? null,
                $data['notes'] ?? null
            ]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            return [
                'success' => true,
                'rows_updated' => $result['rows_updated'] ?? 0,
                'message' => 'Medical record updated successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function delete($id)
    {
        try {
            $query = "CALL DeleteMedicalRecord(?)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            return [
                'success' => true,
                'rows_deleted' => $result['rows_deleted'] ?? 0,
                'message' => 'Medical record deleted successfully'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    // Enhanced prescription method with better error handling
    public function getPrescriptionsForPatient($patientId)
    {
        try {
            if (empty($patientId) || !is_numeric($patientId)) {
                return [];
            }

            // Use the GetPrescriptionsByPatient stored procedure
            $query = "CALL GetPrescriptionsByPatient(?)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$patientId]);

            $prescriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor(); // Important when using stored procedures

            return $prescriptions;
        } catch (Exception $e) {
            return [];
        }
    }
}