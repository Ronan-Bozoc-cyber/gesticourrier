<?php
require_once('partials/connexion.php');
include 'partials/parametres.php';
session_start();
$current_user_id = $_SESSION['user_id'];

// Fonction pour obtenir le prochain numéro d'ordre pour une année donnée
function getNextNumOrdre($conn, $flux, $date) {
    $year = date('Y', strtotime($date));
    $table = ($flux == 'ARRIVE') ? 'courriers_arrive' : 'courriers_depart';
    $query = "SELECT MAX(num_ordre) AS max_num_ordre FROM $table WHERE annee = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $nextNumOrdre = ($row['max_num_ordre'] ?? 0) + 1;
    $stmt->close();
    error_log("Année: $year, Max numéro d'ordre: " . ($row['max_num_ordre'] ?? 'NULL') . ", Prochain numéro d'ordre: $nextNumOrdre");
    return $nextNumOrdre;
}

// Fonction pour appliquer le cachet et compresser un PDF (améliorée pour la qualité)
function addStampAndCompressPDF($filePath, $flux, $num_ordre, $date) {
    $stampText = "MAIRIE DE CONQUES SUR ORBIEL | Flux: $flux | NUMERO ORDRE: $num_ordre | Date: $date";
    $tempFile = tempnam(sys_get_temp_dir(), 'stamped_') . '.pdf';
    $outputFile = tempnam(sys_get_temp_dir(), 'compressed_') . '.pdf';
    $escapedFilePath = escapeshellarg($filePath);
    $escapedTempFile = escapeshellarg($tempFile);
    $escapedOutputFile = escapeshellarg($outputFile);
    $escapedStampText = escapeshellarg($stampText);

    // Appliquer le cachet avec ImageMagick (meilleure résolution et qualité)
    $cmd = "convert -density 300 -quality 100 -gravity north -background white -splice 0x100 $escapedFilePath -gravity North -fill 'rgba(255,0,0,0.5)' -pointsize 12 -annotate +0+36 $escapedStampText $escapedTempFile";
    exec($cmd . " 2>&1", $output, $return_var);
    if ($return_var != 0) {
        throw new Exception("Erreur lors de l'application du cachet : " . implode("\n", $output));
    }

    // Compresser le fichier PDF avec Ghostscript (meilleure qualité)
    $gsCmd = "gs -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=/prepress -dNOPAUSE -dQUIET -dBATCH -sOutputFile=$escapedOutputFile $escapedTempFile";
    exec($gsCmd . " 2>&1", $gsOutput, $gsReturnVar);
    if ($gsReturnVar != 0) {
        throw new Exception("Erreur lors de la compression du fichier PDF : " . implode("\n", $gsOutput));
    }

    if (!copy($outputFile, $filePath)) {
        throw new Exception("Erreur lors de la copie du fichier compressé.");
    }

    unlink($tempFile);
    unlink($outputFile);
}

// Fonction pour gérer l'upload d'un fichier
function handleFileUpload($fileKey, $uploadDir, $num_ordre, $flux, $expediteur_name, $sujet_courrier, $date) {
    if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) {
        error_log("Fichier $fileKey non uploadé ou erreur : " . ($_FILES[$fileKey]['error'] ?? 'non défini'));
        return null;
    }
    $file = $_FILES[$fileKey];
    $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    // Vérification de la taille du fichier (max 2 Mo)
    $maxFileSize = 2 * 1024 * 1024; // 2 Mo
    if ($file['size'] > $maxFileSize) {
        throw new Exception("Le fichier $fileKey dépasse la taille maximale autorisée de 2 Mo.");
    }
    if (!in_array($fileExtension, $allowedExtensions)) {
        throw new Exception("Extension de fichier non autorisée pour $fileKey.");
    }
    // Nettoyage du nom de fichier
    $safeExpediteur = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $expediteur_name);
    $safeSujet = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $sujet_courrier);
    $newFileName = "$num_ordre-$flux-$safeExpediteur-$safeSujet-$date-$fileKey.$fileExtension";
    $uploadFilePath = $uploadDir . $newFileName;
    if (!move_uploaded_file($file['tmp_name'], $uploadFilePath)) {
        throw new Exception("Erreur lors de l'upload du fichier $fileKey.");
    }
    if ($fileExtension === 'pdf') {
        addStampAndCompressPDF($uploadFilePath, $flux, $num_ordre, $date);
    }
    // Retourne le chemin absolu
    return $uploadFilePath;
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("Début du traitement du formulaire.");
    error_log("Valeurs POST reçues : " . print_r($_POST, true));
    $flux = $_POST['flux'] ?? null;
    $date = $_POST['date'] ?? date('Y-m-d');
    $year = date('Y', strtotime($date));
    $num_ordre = $_POST['num_ordre'] ?? null;
    $type_courrier = $_POST['type_courrier'] ?? null;
    $expediteur_id = $_POST['expediteur_id'] ?? null;
    $new_expediteur = $_POST['new_expediteur'] ?? null;
    $adresse = $_POST['adresse'] ?? '';
    $num_recommande = $_POST['num_recommande'] ?? null;
    $sujet_courrier = $_POST['sujet_courrier'] ?? null;
    $categorie_courrier = $_POST['categorie_courrier'] ?? null;
    $traite_par = intval($_POST['traite_par'] ?? 0);
    error_log("Date reçue : $date, Année extraite : $year");

    // Vérification de $flux
    if (empty($flux)) {
        error_log("Erreur : Le champ 'flux' est vide ou non défini.");
        die("Erreur : Le champ 'flux' est obligatoire.");
    }

    // Si le numéro d'ordre n'est pas fourni, obtenir le prochain numéro d'ordre pour l'année en cours
    if (empty($num_ordre)) {
        $num_ordre = getNextNumOrdre($conn, $flux, $date);
        error_log("Nouveau numéro d'ordre généré : $num_ordre pour l'année $year");
    }

    // Gestion spécifique pour les courriers arrivés et sortants
    if ($flux == 'ARRIVE') {
        $courrier_depart_id = !empty($_POST['courrier_depart_id']) ? intval($_POST['courrier_depart_id']) : null;
        $courrier_arrive_id = null;
    } else {
        $courrier_arrive_id = !empty($_POST['courrier_arrive_id']) ? intval($_POST['courrier_arrive_id']) : null;
        if ($courrier_arrive_id !== null) {
            $stmt_check = $conn->prepare("SELECT id FROM courriers_arrive WHERE id = ?");
            $stmt_check->bind_param("i", $courrier_arrive_id);
            $stmt_check->execute();
            $result = $stmt_check->get_result();
            if ($result->num_rows === 0) {
                $courrier_arrive_id = null;
                error_log("courrier_arrive_id $courrier_arrive_id n'existe pas dans la base de données, mis à NULL");
            }
            $stmt_check->close();
        }
        $courrier_depart_id = null;
    }
    error_log("Valeurs reçues - Flux: $flux, Numéro d'ordre: $num_ordre, Date: $date, courrier_arrive_id: " . ($courrier_arrive_id ?? 'NULL'));

    // Gestion de l'expéditeur
    $expediteur_name = '';
    if ($new_expediteur) {
        $stmt = $conn->prepare("INSERT INTO expediteurs (name, adresse) VALUES (?, ?)");
        $stmt->bind_param("ss", $new_expediteur, $adresse);
        if ($stmt->execute()) {
            $expediteur_id = $stmt->insert_id;
            $expediteur_name = $new_expediteur;
            error_log("Nouvel expéditeur ajouté : $new_expediteur (ID: $expediteur_id)");
        } else {
            die("Erreur: " . $stmt->error);
        }
        $stmt->close();
    } elseif ($expediteur_id) {
        $stmt = $conn->prepare("SELECT name FROM expediteurs WHERE id = ?");
        $stmt->bind_param("i", $expediteur_id);
        $stmt->execute();
        $stmt->bind_result($expediteur_name);
        $stmt->fetch();
        $stmt->close();
        error_log("Expéditeur existant : $expediteur_name (ID: $expediteur_id)");
    } else {
        $expediteur_name = $_POST['expediteur'] ?? '';
    }

    // Dossier d'upload
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0775, true);
        error_log("Dossier d'upload créé : $uploadDir");
    }

    // Tableau pour stocker les chemins des fichiers
    $documentPaths = [];
    $uploadErrors = [];

    // Traite chaque fichier
    for ($i = 1; $i <= 5; $i++) {
        $fileKey = "document$i";
        error_log("=== Début traitement fichier $fileKey ===");
        error_log("Fichier $fileKey reçu : " . print_r($_FILES[$fileKey] ?? 'non défini', true));
        if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] === UPLOAD_ERR_NO_FILE) {
            // Si un fichier existant est présent dans les champs cachés, utilise-le
            if (!empty($_POST["existing_document$i"])) {
                $documentPaths[$fileKey] = $_POST["existing_document$i"];
                error_log("Fichier existant conservé pour $fileKey : " . $documentPaths[$fileKey]);
            } else {
                $documentPaths[$fileKey] = null;
                error_log("Aucun fichier pour $fileKey.");
            }
            continue;
        }
        $file = $_FILES[$fileKey];
        error_log("Nom du fichier : " . $file['name']);
        error_log("Taille du fichier : " . $file['size']);
        error_log("Code d'erreur : " . $file['error']);
        if ($file['error'] !== UPLOAD_ERR_OK) {
            error_log("Erreur d'upload pour $fileKey : " . $file['error']);
            continue;
        }
        try {
            $documentPaths[$fileKey] = handleFileUpload(
                $fileKey,
                $uploadDir,
                $num_ordre,
                $flux,
                $expediteur_name,
                $sujet_courrier,
                $date
            );
            error_log("Fichier $fileKey traité avec succès : " . $documentPaths[$fileKey]);
        } catch (Exception $e) {
            error_log("Erreur lors du traitement de $fileKey : " . $e->getMessage());
            $uploadErrors[] = $e->getMessage();
        }
    }

    // Si des erreurs d'upload, affiche-les et arrête le traitement
    if (!empty($uploadErrors)) {
        echo "<div class='error'>";
        foreach ($uploadErrors as $error) {
            echo "<p>$error</p>";
        }
        echo "</div>";
        exit;
    }

    // Prépare les chemins pour la requête SQL
    $paths = [
        $documentPaths['document1'] ?? null,
        $documentPaths['document2'] ?? null,
        $documentPaths['document3'] ?? null,
        $documentPaths['document4'] ?? null,
        $documentPaths['document5'] ?? null,
    ];
    error_log("Chemins des fichiers préparés pour la base de données : " . print_r($paths, true));

    // Vérifie si le courrier existe déjà pour une mise à jour
    if ($flux == 'ARRIVE') {
        $stmt_check = $conn->prepare("SELECT id FROM courriers_arrive WHERE num_ordre = ? AND annee = ?");
        $stmt_check->bind_param("ii", $num_ordre, $year);
    } else {
        $stmt_check = $conn->prepare("SELECT id FROM courriers_depart WHERE num_ordre = ? AND annee = ?");
        $stmt_check->bind_param("ii", $num_ordre, $year);
    }
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $courrier_exists = $result_check->num_rows > 0;
    $stmt_check->close();

    // Insertion ou mise à jour en base de données
    if ($flux == 'ARRIVE') {
        if ($courrier_exists) {
            // Mise à jour du courrier existant
            $stmt = $conn->prepare("
                UPDATE courriers_arrive SET
                    date = ?,
                    annee = ?,
                    type_courrier = ?,
                    expediteur_id = ?,
                    num_recommande = ?,
                    sujet_courrier = ?,
                    categorie_courrier = ?,
                    document_path = ?,
                    document_path2 = ?,
                    document_path3 = ?,
                    document_path4 = ?,
                    document_path5 = ?,
                    traite_par = ?,
                    courrier_depart_id = ?
                WHERE num_ordre = ? AND annee = ?
            ");
            $stmt->bind_param(
                "sisisssssssssiii",
                $date,
                $year,
                $type_courrier,
                $expediteur_id,
                $num_recommande,
                $sujet_courrier,
                $categorie_courrier,
                $paths[0],
                $paths[1],
                $paths[2],
                $paths[3],
                $paths[4],
                $traite_par,
                $courrier_depart_id,
                $num_ordre,
                $year
            );
        } else {
            // Insertion d'un nouveau courrier
            $stmt = $conn->prepare("
                INSERT INTO courriers_arrive (
                    flux, num_ordre, date, annee, type_courrier, expediteur_id, num_recommande,
                    sujet_courrier, categorie_courrier, document_path, document_path2,
                    document_path3, document_path4, document_path5, traite_par, courrier_depart_id
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param(
                "sisisisssssssssi",
                $flux,
                $num_ordre,
                $date,
                $year,
                $type_courrier,
                $expediteur_id,
                $num_recommande,
                $sujet_courrier,
                $categorie_courrier,
                $paths[0],
                $paths[1],
                $paths[2],
                $paths[3],
                $paths[4],
                $traite_par,
                $courrier_depart_id
            );
        }
    } else {
        // Pour les courriers sortants
        if ($courrier_exists) {
            // Mise à jour du courrier sortant existant
            $stmt = $conn->prepare("
                UPDATE courriers_depart SET
                    date = ?,
                    type_courrier = ?,
                    expediteur_id = ?,
                    num_recommande = ?,
                    sujet_courrier = ?,
                    categorie_courrier = ?,
                    document_path = ?,
                    document_path2 = ?,
                    document_path3 = ?,
                    document_path4 = ?,
                    document_path5 = ?,
                    traite_par = ?,
                    courrier_arrive_id = ?
                WHERE num_ordre = ? AND annee = ?
            ");
            $stmt->bind_param(
                "ssisssssssssiii",
                $date,
                $type_courrier,
                $expediteur_id,
                $num_recommande,
                $sujet_courrier,
                $categorie_courrier,
                $paths[0],
                $paths[1],
                $paths[2],
                $paths[3],
                $paths[4],
                $traite_par,
                $courrier_arrive_id,
                $num_ordre,
                $year
            );
        } else {
            // Insertion d'un nouveau courrier sortant
            $stmt = $conn->prepare("
                INSERT INTO courriers_depart (
                    flux, num_ordre, date, annee, type_courrier, expediteur_id, num_recommande,
                    sujet_courrier, categorie_courrier, document_path, document_path2,
                    document_path3, document_path4, document_path5, traite_par, courrier_arrive_id
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param(
                "sisssisssssssssi",
                $flux,
                $num_ordre,
                $date,
                $year,
                $type_courrier,
                $expediteur_id,
                $num_recommande,
                $sujet_courrier,
                $categorie_courrier,
                $paths[0],
                $paths[1],
                $paths[2],
                $paths[3],
                $paths[4],
                $traite_par,
                $courrier_arrive_id
            );
        }
    }

    if ($stmt->execute()) {
        error_log("Courrier enregistré avec succès dans la base de données.");
        echo "Courrier enregistré avec succès";
    } else {
        error_log("Erreur lors de l'insertion/mise à jour en base de données : " . $stmt->error);
        echo "Erreur: " . $stmt->error;
    }
    $stmt->close();
}

/* DB connection intentionally left open for Singleton */
?>
