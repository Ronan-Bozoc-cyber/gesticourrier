<?php
require_once __DIR__ . '/../partials/connexion.php';

class ExpediteurModel {
    /**
     * Retrieve all expediteurs (contacts).
     * @return array List of associative arrays
     */
    public static function getAll() {
        $conn = Database::getInstance()->getConnection();
        $sql = "SELECT * FROM expediteurs";
        $result = $conn->query($sql);
        
        $expediteurs = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $expediteurs[] = $row;
            }
        }
        return $expediteurs;
    }
}
?>
