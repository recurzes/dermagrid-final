<?php
require_once __DIR__ . '/../config/database.php';

class Staff
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Authenticate staff member
    public static function authenticate($username, $password)
    {
        $result = executeStoredProcedure('AuthenticateUser', [$username]);

        if (!empty($result)) {
            $staff = $result[0];

            // Verify password
            if (password_verify($password, $staff['password_hash'])) {
                // Remove password_hash from session data
                unset($staff['password_hash']);
                return $staff;
            }
        }

        return false;
    }

    // Add new staff member
    public static function add($firstname, $last_name, $role, $email, $phone, $username, $password)
    {

    }

    // Get staff by role
    public static function getByRole($role)
    {
        return executeStoredProcedure('GetStaffByRole', [$role]);
    }

    // Get staff statistics
    public function getStaffStats()
    {
        try {
            $staff_stats = [];
            $query = "CALL GetStaffRolesSummary()";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            if ($stmt) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($row) {
                    $staff_stats = [
                        'total_staff' => $row['total_staff'],
                        'total_doctors' => $row['total_doctors'],
                        'total_nurses' => $row['total_nurses'],
                        'total_receptionists' => $row['total_receptionists']
                    ];
                }

                $stmt->closeCursor();
            } else {
                throw new Exception("Error calling stored procedure");
            }

            return $staff_stats;
        } catch (PDOException $e) {
            throw new Exception("Error fetching staff stats: " . $e->getMessage());
        }
    }

    // Get staff member by ID
    public function getById($id)
    {
        try {
            $query = "CALL GetStaffById(?)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            return $result;
        } catch (PDOException $e) {
            throw new Exception("Error fetching staff details: " . $e->getMessage());
        }
    }

    // Get appointments for a staff member
    public function getAppointments($staffId)
    {
        try {
            $query = "CALL GetAppointmentsByStaff(?)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$staffId]);

            $appointments = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $appointments[] = $row;
            }

            $stmt->closeCursor();
            return $appointments;
        } catch (PDOException $e) {
            throw new Exception("Error fetching staff appointments: " . $e->getMessage());
        }
    }

    // Get receptionists assigned to a doctor
    public function getReceptionists()
    {
        return $this->getByRole('receptionist');
    }

    public function searchByName($firstName, $lastName)
    {
        try {
            // Create a search term that combines first and last name
            $searchTerm = $firstName;
            if (!empty($lastName)) {
                $searchTerm = $lastName; // Prioritize last name if available
            }

            $query = "CALL SearchStaff(?)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$searchTerm]);

            $results = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $results[] = $row;
            }

            $stmt->closeCursor();
            return $results;
        } catch (PDOException $e) {
            throw new Exception("Error searching staff: " . $e->getMessage());
        }
    }
}
