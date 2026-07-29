<?php

require_once('partials/connexion.php');

header('Content-Type: application/json');

// Récupération des critères de recherche
$flux = $_POST['flux'] ?? '';
$num_ordre = $_POST['num_ordre'] ?? '';
$annee = $_POST['annee'] ?? '';
$date_debut = $_POST['date_debut'] ?? '';
$date_fin = $_POST['date_fin'] ?? '';
$type_courrier = $_POST['type_courrier'] ?? '';
$expediteur = $_POST['expediteur'] ?? '';
$sujet = $_POST['sujet'] ?? '';
$categories = $_POST['categorie'] ?? [];

// Construction de la requête SQL pour courriers_arrive avec les informations du courrier départ associé
$query_arrive = "SELECT 
                    ca.id, 
                    ca.num_ordre, 
                    ca.annee,
                    ca.date, 
                    users.username AS traite_par, 
                    ca.type_courrier, 
                    ca.expediteur_id, 
                    ca.sujet_courrier, 
                    ca.num_recommande, 
                    ca.categorie_courrier, 
                    ca.document_path, 
                    ca.document_path2, 
                    ca.document_path3, 
                    ca.document_path4, 
                    ca.document_path5,
                    ca.courrier_depart_id,
                    cd.num_ordre AS courrier_depart_num_ordre,
                    cd.annee AS courrier_depart_annee
                 FROM courriers_arrive ca
                 LEFT JOIN users ON ca.traite_par = users.id 
                 LEFT JOIN courriers_depart cd ON ca.courrier_depart_id = cd.id
                 WHERE 1=1";

// Construction de la requête SQL pour courriers_depart avec les informations du courrier arrivé associé
$query_depart = "SELECT 
                    cd.id, 
                    cd.num_ordre, 
                    cd.annee,
                    cd.date, 
                    users.username AS traite_par, 
                    cd.type_courrier, 
                    cd.expediteur_id, 
                    cd.sujet_courrier, 
                    cd.num_recommande, 
                    cd.categorie_courrier, 
                    cd.document_path, 
                    cd.document_path2, 
                    cd.document_path3, 
                    cd.document_path4, 
                    cd.document_path5, 
                    cd.courrier_arrive_id,
                    ca.num_ordre AS courrier_arrive_num_ordre,
                    ca.annee AS courrier_arrive_annee
                 FROM courriers_depart cd
                 LEFT JOIN users ON cd.traite_par = users.id 
                 LEFT JOIN courriers_arrive ca ON cd.courrier_arrive_id = ca.id
                 WHERE 1=1";

$params = [];
$types = "";

// Ajout de paramètres conditionnels
if (!empty($num_ordre)) {
    $query_arrive .= " AND ca.num_ordre = ?";
    $query_depart .= " AND cd.num_ordre = ?";
    $params[] = $num_ordre;
    $types .= "i";
}

if (!empty($annee)) {
    $query_arrive .= " AND ca.annee = ?";
    $query_depart .= " AND cd.annee = ?";
    $params[] = $annee;
    $types .= "i";
}

if (!empty($date_debut) && !empty($date_fin)) {
    $query_arrive .= " AND ca.date BETWEEN ? AND ?";
    $query_depart .= " AND cd.date BETWEEN ? AND ?";
    $params[] = $date_debut;
    $params[] = $date_fin;
    $types .= "ss";
} elseif (!empty($date_debut)) {
    $query_arrive .= " AND ca.date >= ?";
    $query_depart .= " AND cd.date >= ?";
    $params[] = $date_debut;
    $types .= "s";
} elseif (!empty($date_fin)) {
    $query_arrive .= " AND ca.date <= ?";
    $query_depart .= " AND cd.date <= ?";
    $params[] = $date_fin;
    $types .= "s";
}

if (!empty($type_courrier)) {
    $query_arrive .= " AND ca.type_courrier = ?";
    $query_depart .= " AND cd.type_courrier = ?";
    $params[] = $type_courrier;
    $types .= "s";
}

if (!empty($expediteur)) {
    $query_arrive .= " AND ca.expediteur_id IN (SELECT id FROM expediteurs WHERE name LIKE ?)";
    $query_depart .= " AND cd.expediteur_id IN (SELECT id FROM expediteurs WHERE name LIKE ?)";
    $params[] = "%$expediteur%";
    $types .= "s";
}

if (!empty($sujet)) {
    $query_arrive .= " AND ca.sujet_courrier LIKE ?";
    $query_depart .= " AND cd.sujet_courrier LIKE ?";
    $params[] = "%$sujet%";
    $types .= "s";
}

if (!empty($categories)) {
    $placeholders = implode(',', array_fill(0, count($categories), '?'));
    $query_arrive .= " AND ca.categorie_courrier IN ($placeholders)";
    $query_depart .= " AND cd.categorie_courrier IN ($placeholders)";
    foreach ($categories as $category) {
        $params[] = $category;
        $types .= "s";
    }
}

// Ajout de l'ordre de tri par date décroissante
$query_arrive .= " ORDER BY ca.date DESC, ca.num_ordre DESC";
$query_depart .= " ORDER BY cd.date DESC, cd.num_ordre DESC";

$results = [];

// Exécution de la requête pour courriers_arrive
if ($flux === 'ARRIVE' || $flux === 'TOUS') {
    $stmt_arrive = $conn->prepare($query_arrive);
    if ($stmt_arrive === false) {
        die(json_encode(["error" => "Erreur lors de la préparation de la requête arrive: " . $conn->error]));
    }
    if (!empty($types)) {
        $stmt_arrive->bind_param($types, ...$params);
    }
    if (!$stmt_arrive->execute()) {
        die(json_encode(["error" => "Erreur lors de l'exécution de la requête arrive: " . $stmt_arrive->error]));
    }
    $result_arrive = $stmt_arrive->get_result();
    while ($row = $result_arrive->fetch_assoc()) {
        $row['flux'] = 'ARRIVE';
        $expediteur_id = $row['expediteur_id'];
        $expediteur_query = $conn->prepare("SELECT name FROM expediteurs WHERE id = ?");
        $expediteur_query->bind_param("i", $expediteur_id);
        $expediteur_query->execute();
        $expediteur_result = $expediteur_query->get_result();
        $expediteur_data = $expediteur_result->fetch_assoc();
        $row['expediteur'] = $expediteur_data ? $expediteur_data['name'] : 'Inconnu';
        unset($row['expediteur_id']);
        $results[] = $row;
    }
    $stmt_arrive->close();
}

// Exécution de la requête pour courriers_depart
if ($flux === 'DEPART' || $flux === 'TOUS') {
    $stmt_depart = $conn->prepare($query_depart);
    if ($stmt_depart === false) {
        die(json_encode(["error" => "Erreur lors de la préparation de la requête depart: " . $conn->error]));
    }
    if (!empty($types)) {
        $stmt_depart->bind_param($types, ...$params);
    }
    if (!$stmt_depart->execute()) {
        die(json_encode(["error" => "Erreur lors de l'exécution de la requête depart: " . $stmt_depart->error]));
    }
    $result_depart = $stmt_depart->get_result();
    while ($row = $result_depart->fetch_assoc()) {
        $row['flux'] = 'DEPART';
        $expediteur_id = $row['expediteur_id'];
        $expediteur_query = $conn->prepare("SELECT name FROM expediteurs WHERE id = ?");
        $expediteur_query->bind_param("i", $expediteur_id);
        $expediteur_query->execute();
        $expediteur_result = $expediteur_query->get_result();
        $expediteur_data = $expediteur_result->fetch_assoc();
        $row['expediteur'] = $expediteur_data ? $expediteur_data['name'] : 'Inconnu';
        unset($row['expediteur_id']);
        $results[] = $row;
    }
    $stmt_depart->close();
}

/* DB connection intentionally left open for Singleton */

echo json_encode($results);
?>