<?php
class Patient
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function searchByName($firstName, $lastName)
    {
        try {
            // Create a search term that combines first and last name
            $searchTerm = $firstName;
            if (!empty($lastName)) {
                $searchTerm = $lastName; // Prioritize last name if available
            }

            $query = "CALL SearchPatients(?)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$searchTerm]);

            $results = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $results[] = $row;
            }

            $stmt->closeCursor();
            return $results;
        } catch (PDOException $e) {
            throw new Exception("Error searching patients: " . $e->getMessage());
        }
    }

    // Other methods...
}
