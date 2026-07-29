<?php
require_once __DIR__ . '/../partials/connexion.php';

class CourrierModel {
    /**
     * Retrieve all depart courriers.
     */
    public static function getAllDepart() {
        $conn = Database::getInstance()->getConnection();
        $sql = "
        SELECT
            c.num_ordre,
            c.date,
            c.type_courrier,
            e.name as expediteur,
            c.sujet_courrier,
            c.num_recommande,
            c.categorie_courrier,
            c.document_path,
            c.document_path2,
            c.document_path3,
            c.document_path4,
            c.document_path5,
            ca.num_ordre AS courrier_arrive_num_ordre,
            ca.document_path AS courrier_arrive_document_path,
            YEAR(c.date) as annee
        FROM
            courriers_depart c
        JOIN
            expediteurs e ON c.expediteur_id = e.id
        LEFT JOIN
            courriers_arrive ca ON c.courrier_arrive_id = ca.id
        ORDER BY
            c.date DESC, c.num_ordre DESC";

        $result = $conn->query($sql);
        $courriers = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $courriers[] = $row;
            }
        }
        return $courriers;
    }

    /**
     * Retrieve all arrive courriers.
     */
    public static function getAllArrive() {
        $conn = Database::getInstance()->getConnection();
        $sql = "
        SELECT
            c.num_ordre,
            c.date,
            c.type_courrier,
            e.name as expediteur,
            c.sujet_courrier,
            c.num_recommande,
            c.categorie_courrier,
            c.courrier_depart_id,
            c.document_path,
            c.document_path2,
            c.document_path3,
            c.document_path4,
            c.document_path5,
            YEAR(c.date) as annee
        FROM
            courriers_arrive c
        LEFT JOIN
            expediteurs e ON c.expediteur_id = e.id
        ORDER BY
            c.date DESC, c.num_ordre DESC";

        $result = $conn->query($sql);
        $courriers = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $courriers[] = $row;
            }
        }
        return $courriers;
    }

    /**
     * Get next num_ordre for depart courriers for a given year.
     */
    public static function getNextNumOrdreDepart($year) {
        $conn = Database::getInstance()->getConnection();
        $query = "SELECT MAX(num_ordre) AS max_num_ordre FROM courriers_depart WHERE annee = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $year);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $nextNumOrdre = ($row['max_num_ordre'] ?? 0) + 1;
        $stmt->close();
        return $nextNumOrdre;
    }

    /**
     * Get next num_ordre for arrive courriers for a given year.
     */
    public static function getNextNumOrdreArrive($year) {
        $conn = Database::getInstance()->getConnection();
        $query = "SELECT MAX(num_ordre) AS max_num_ordre FROM courriers_arrive WHERE annee = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $year);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $nextNumOrdre = ($row['max_num_ordre'] ?? 0) + 1;
        $stmt->close();
        return $nextNumOrdre;
    }
}
?>
