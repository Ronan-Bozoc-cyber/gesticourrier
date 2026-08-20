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

function hexToRgba($hex, $opacityPercent = 85) {
    $hex = ltrim($hex, '#');
    if (strlen($hex) === 3) {
        $r = hexdec(str_repeat(substr($hex, 0, 1), 2));
        $g = hexdec(str_repeat(substr($hex, 1, 1), 2));
        $b = hexdec(str_repeat(substr($hex, 2, 1), 2));
    } else {
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
    }
    $alpha = round($opacityPercent / 100, 2);
    return "rgba($r,$g,$b,$alpha)";
}

// Fonction pour appliquer le cachet et compresser un document (conforme aux paramètres exacts de settings.json)
function addStampAndCompressPDF($filePath, $flux, $num_ordre, $date, $categorie = '') {
    global $org_settings;

    $defaultSettings = [
        'raison_sociale'       => 'Mairie de Conques-sur-Orbiel',
        'tampon_active'        => '1',
        'tampon_disposition'   => 'ligne',
        'tampon_position'      => 'top-right',
        'tampon_couleur'       => '#2563eb',
        'tampon_opacite'       => '85',
        'tampon_taille'        => 'medium',
        'tampon_bordure'       => 'double',
        'tampon_show_org'      => '1',
        'tampon_show_num'      => '1',
        'tampon_show_date'     => '1',
        'tampon_show_categorie'=> '1',
        'tampon_texte_custom'  => 'ARRIVÉE - COURRIER'
    ];

    if (empty($org_settings)) {
        $settingsFile = __DIR__ . '/data/settings.json';
        if (file_exists($settingsFile)) {
            $decoded = json_decode(file_get_contents($settingsFile), true);
            $org_settings = is_array($decoded) ? array_merge($defaultSettings, $decoded) : $defaultSettings;
        } else {
            $org_settings = $defaultSettings;
        }
    } else {
        $org_settings = array_merge($defaultSettings, $org_settings);
    }

    if (isset($org_settings['tampon_active']) && $org_settings['tampon_active'] === '0') {
        return;
    }

    $colorHex = $org_settings['tampon_couleur'] ?? '#2563eb';
    $opacityVal = intval($org_settings['tampon_opacite'] ?? 85);
    $colorRgba = hexToRgba($colorHex, $opacityVal);

    $position = $org_settings['tampon_position'] ?? 'top-right';
    $sizeStr = $org_settings['tampon_taille'] ?? 'medium';
    $borderStyle = $org_settings['tampon_bordure'] ?? 'double';

    $pointSize = 12;
    if ($sizeStr === 'small') $pointSize = 10;
    if ($sizeStr === 'large') $pointSize = 14;
    if ($sizeStr === 'xlarge') $pointSize = 16;

    // Construction des lignes du tampon
    $lines = [];
    if (!empty($org_settings['tampon_texte_custom'])) {
        $lines[] = mb_strtoupper($org_settings['tampon_texte_custom']);
    }
    if (($org_settings['tampon_show_org'] ?? '1') === '1' && !empty($org_settings['raison_sociale'])) {
        $lines[] = $org_settings['raison_sociale'];
    }
    if (($org_settings['tampon_show_num'] ?? '1') === '1' && !empty($num_ordre)) {
        $lines[] = "N° Ordre : " . $num_ordre;
    }
    if (($org_settings['tampon_show_date'] ?? '1') === '1' && !empty($date)) {
        $dateFormatted = date('d/m/Y', strtotime($date));
        $lines[] = ($flux === 'ARRIVE' ? "Reçu le : " : "Parti le : ") . $dateFormatted;
    }
    if (($org_settings['tampon_show_categorie'] ?? '1') === '1' && !empty($categorie)) {
        $lines[] = "Catégorie : " . $categorie;
    }

    $disposition = $org_settings['tampon_disposition'] ?? 'ligne';
    $separator = ($disposition === 'ligne') ? '  |  ' : "\n";
    $stampText = implode($separator, $lines);

    if (empty($stampText)) {
        $stampText = "ARRIVÉE - COURRIER";
    }

    $tempStampLabel = tempnam(sys_get_temp_dir(), 'stamp_lbl_') . '.png';
    $tempStampedPdf = tempnam(sys_get_temp_dir(), 'stamped_pdf_') . '.pdf';
    $outputFile = tempnam(sys_get_temp_dir(), 'compressed_pdf_') . '.pdf';

    $escapedText = escapeshellarg($stampText);
    $escapedColor = escapeshellarg($colorRgba);
    $escapedLabel = escapeshellarg($tempStampLabel);

    $fileExt = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    $escapedInputFile = escapeshellarg(($fileExt === 'pdf') ? ($filePath . '[0]') : $filePath);
    $escapedStampedPdf = escapeshellarg($tempStampedPdf);
    $escapedOutputFile = escapeshellarg($outputFile);

    // 1. Création de l'image de l'empreinte encadrée avec ImageMagick
    $borderWidth = ($borderStyle === 'none') ? 0 : (($borderStyle === 'double') ? 4 : 2);
    $bgAlpha = round($opacityVal / 100 * 0.95, 2);
    $cmdLabel = "convert -background 'rgba(255,255,255,$bgAlpha)' -fill $escapedColor -pointsize $pointSize -gravity center label:$escapedText -bordercolor $escapedColor -border ${borderWidth}x${borderWidth} $escapedLabel 2>&1";
    exec($cmdLabel, $out1, $res1);

    // Positionnement gravity
    $gravity = 'NorthEast';
    if ($position === 'top-left') $gravity = 'NorthWest';
    if ($position === 'bottom-right') $gravity = 'SouthEast';
    if ($position === 'bottom-left') $gravity = 'SouthWest';
    if ($position === 'center') $gravity = 'Center';

    if ($res1 === 0 && file_exists($tempStampLabel)) {
        if ($fileExt === 'pdf') {
            // Déterminer le nombre de pages du PDF
            $escapedOriginalPdf = escapeshellarg($filePath);
            $pageCountCmd = "identify -format \"%n\n\" $escapedOriginalPdf | head -n 1";
            exec($pageCountCmd, $pcOut, $pcRes);
            $pageCount = intval(trim($pcOut[0] ?? '1'));

            // 2. Tamponner la première page (index 0)
            $cmdComposite = "convert -density 150 {$escapedOriginalPdf}[0] $escapedLabel -gravity $gravity -geometry +30+30 -composite $escapedStampedPdf 2>&1";
            exec($cmdComposite, $out2, $res2);

            if ($res2 === 0 && file_exists($tempStampedPdf) && filesize($tempStampedPdf) > 0) {
                if ($pageCount > 1) {
                    // Extraire les pages restantes (page 2 à N)
                    $tempRestPdf = tempnam(sys_get_temp_dir(), 'rest_pdf_') . '.pdf';
                    $escapedRestPdf = escapeshellarg($tempRestPdf);

                    $extractRestCmd = "gs -sDEVICE=pdfwrite -dNOPAUSE -dBATCH -dQUIET -dFirstPage=2 -sOutputFile=$escapedRestPdf $escapedOriginalPdf 2>&1";
                    exec($extractRestCmd, $eOut, $eRes);

                    if ($eRes === 0 && file_exists($tempRestPdf) && filesize($tempRestPdf) > 0) {
                        // Fusionner la 1ère page tamponnée avec le reste des pages
                        $gsMergeCmd = "gs -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=/prepress -dNOPAUSE -dQUIET -dBATCH -sOutputFile=$escapedOutputFile $escapedStampedPdf $escapedRestPdf 2>&1";
                        exec($gsMergeCmd, $mOut, $mRes);

                        if ($mRes === 0 && file_exists($outputFile) && filesize($outputFile) > 0) {
                            @copy($outputFile, $filePath);
                            @unlink($outputFile);
                        } else {
                            @copy($tempStampedPdf, $filePath);
                        }
                        @unlink($tempRestPdf);
                    } else {
                        @copy($tempStampedPdf, $filePath);
                    }
                } else {
                    // PDF de 1 seule page
                    $gsCmd = "gs -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=/prepress -dNOPAUSE -dQUIET -dBATCH -sOutputFile=$escapedOutputFile $escapedStampedPdf 2>&1";
                    exec($gsCmd, $gsOutput, $gsReturnVar);

                    if ($gsReturnVar === 0 && file_exists($outputFile) && filesize($outputFile) > 0) {
                        @copy($outputFile, $filePath);
                        @unlink($outputFile);
                    } else {
                        @copy($tempStampedPdf, $filePath);
                    }
                }
                @unlink($tempStampedPdf);
            } else {
                error_log("Erreur composite ImageMagick : " . implode(" ", $out2));
            }
        } else {
            // Fichier Image (JPG, PNG, WEBP)
            $escapedInputImg = escapeshellarg($filePath);
            $cmdComposite = "convert $escapedInputImg $escapedLabel -gravity $gravity -geometry +30+30 -composite $escapedStampedPdf 2>&1";
            exec($cmdComposite, $out2, $res2);

            if ($res2 === 0 && file_exists($tempStampedPdf) && filesize($tempStampedPdf) > 0) {
                @copy($tempStampedPdf, $filePath);
                @unlink($tempStampedPdf);
            }
        }
        @unlink($tempStampLabel);
    } else {
        error_log("Erreur label ImageMagick : " . implode(" ", $out1));
    }
}

// Fonction pour gérer l'upload d'un fichier
function handleFileUpload($fileKey, $uploadDir, $num_ordre, $flux, $expediteur_name, $sujet_courrier, $date, $categorie = '') {
    if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) {
        error_log("Fichier $fileKey non uploadé ou erreur : " . ($_FILES[$fileKey]['error'] ?? 'non défini'));
        return null;
    }
    $file = $_FILES[$fileKey];
    $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];
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
    if (in_array($fileExtension, ['pdf', 'jpg', 'jpeg', 'png', 'webp'])) {
        addStampAndCompressPDF($uploadFilePath, $flux, $num_ordre, $date, $categorie);
    }
    // Retourne le chemin relatif pour la base de données : uploads/nom_du_fichier
    return 'uploads/' . $newFileName;
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
                $existingPath = $_POST["existing_document$i"];
                $documentPaths[$fileKey] = (strpos($existingPath, 'uploads/') !== false) 
                    ? ('uploads/' . basename($existingPath)) 
                    : ltrim($existingPath, '/');
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
                $date,
                $categorie_courrier
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

    // Suppression des anciens fichiers physiques remplacés ou supprimés lors de la mise à jour
    if ($courrier_exists) {
        $tableCheck = ($flux == 'ARRIVE') ? 'courriers_arrive' : 'courriers_depart';
        $stmt_old = $conn->prepare("SELECT document_path, document_path2, document_path3, document_path4, document_path5 FROM $tableCheck WHERE num_ordre = ? AND annee = ?");
        $stmt_old->bind_param("ii", $num_ordre, $year);
        $stmt_old->execute();
        $res_old = $stmt_old->get_result();
        if ($row_old = $res_old->fetch_assoc()) {
            $oldCols = ['document_path', 'document_path2', 'document_path3', 'document_path4', 'document_path5'];
            foreach ($oldCols as $idx => $colName) {
                $oldP = $row_old[$colName] ?? null;
                $newP = $paths[$idx] ?? null;
                if (!empty($oldP) && $oldP !== $newP) {
                    $oldFileOnDisk = __DIR__ . '/uploads/' . basename($oldP);
                    if (file_exists($oldFileOnDisk)) {
                        @unlink($oldFileOnDisk);
                    }
                }
            }
        }
        $stmt_old->close();
    }

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
