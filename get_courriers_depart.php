<?php
include 'partials/connexion.php';

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
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $courriers[] = $row;
    }
}
$conn->close();
header('Content-Type: application/json');
echo json_encode($courriers);
?>
