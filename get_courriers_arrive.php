<?php
require_once('partials/connexion.php');

// Requête SQL pour récupérer tous les champs nécessaires, triés par date décroissante
$sql = "SELECT
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
$courriers = array();
while ($row = $result->fetch_assoc()) {
    $courriers[] = $row;
}
header('Content-Type: application/json');
echo json_encode($courriers);
/* DB connection intentionally left open for Singleton */
?>

