<?php
require_once __DIR__ . '/../partials/connexion.php';

class UserModel {
    /**
     * Retrieve all users.
     * @return array List of associative arrays representing users.
     */
    public static function getAll() {
        $conn = Database::getInstance()->getConnection();
        $query = "SELECT id, username, email, role FROM users";
        $result = $conn->query($query);
        
        $users = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
        }
        return $users;
    }
}
?>
