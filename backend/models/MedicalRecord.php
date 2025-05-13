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
            $query = "CALL GetMedicalRecordById(?)";
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

    public function create($data)
    {
        try {
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
        } catch (PDOException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function update($id, $data)
    {
        try {
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
}
