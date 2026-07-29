<?php
require_once __DIR__ . '/../partials/connexion.php';

class CategorieModel {
    /**
     * Retrieve all categories from the database.
     * @return array List of associative arrays representing categories.
     */
    public static function getAll() {
        $conn = Database::getInstance()->getConnection();
        $sql = "SELECT * FROM categories";
        $result = $conn->query($sql);
        
        $categories = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $categories[] = $row;
            }
        }
        return $categories;
    }
}
?>
