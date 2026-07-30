<?php
include 'admin/auth_check.php';
include_once 'partials/parametres.php';
include_once 'partials/connexion.php';

$archivesDir = __DIR__ . '/archives/';
if (!is_dir($archivesDir)) {
    mkdir($archivesDir, 0755, true);
    file_put_contents($archivesDir . '.htaccess', 'Deny from all');
}

$indexFile = $archivesDir . 'archives_index.json';
$archivesIndex = [];
if (file_exists($indexFile)) {
    $json = @file_get_contents($indexFile);
    $decoded = json_decode($json, true);
    if (is_array($decoded)) {
        $archivesIndex = $decoded;
    }
}

function saveArchiveIndex($indexFile, $archivesIndex) {
    file_put_contents($indexFile, json_encode($archivesIndex, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// 1. TÉLÉCHARGER UNE ARCHIVE ZIP
if ($action === 'download_archive' && !empty($_GET['file'])) {
    $filename = basename($_GET['file']);
    $filepath = $archivesDir . $filename;

    if (file_exists($filepath)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit;
    } else {
        die("Fichier d'archive introuvable.");
    }
}

// 2. SUPPRIMER UNE ARCHIVE DU RÉPERTOIRE ET DE L'INDEX
if ($action === 'delete_archive' && !empty($_GET['file'])) {
    $filename = basename($_GET['file']);
    $filepath = $archivesDir . $filename;

    if (file_exists($filepath)) {
        unlink($filepath);
    }

    // Filtrer l'index
    $archivesIndex = array_filter($archivesIndex, function($item) use ($filename) {
        return $item['archive_filename'] !== $filename;
    });
    saveArchiveIndex($indexFile, array_values($archivesIndex));

    header('Location: parametrage.php?msg=archive_deleted');
    exit;
}

// 3. EXÉCUTION DE L'ARCHIVAGE ET DE LA PURGE
if ($action === 'process_archive' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $cutoffDate = trim($_POST['cutoff_date'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($cutoffDate) || empty($description)) {
        die("La date de fin et la description sont obligatoires.");
    }

    // A. Récupération des enregistrements courriers_arrive <= $cutoffDate
    $stmtArrive = $conn->prepare("SELECT * FROM courriers_arrive WHERE date <= ?");
    $stmtArrive->bind_param('s', $cutoffDate);
    $stmtArrive->execute();
    $resArrive = $stmtArrive->get_result();
    $rowsArrive = [];
    while ($row = $resArrive->fetch_assoc()) {
        $rowsArrive[] = $row;
    }

    // B. Récupération des enregistrements courriers_depart <= $cutoffDate
    $stmtDepart = $conn->prepare("SELECT * FROM courriers_depart WHERE date <= ?");
    $stmtDepart->bind_param('s', $cutoffDate);
    $stmtDepart->execute();
    $resDepart = $stmtDepart->get_result();
    $rowsDepart = [];
    while ($row = $resDepart->fetch_assoc()) {
        $rowsDepart[] = $row;
    }

    $countArrive = count($rowsArrive);
    $countDepart = count($rowsDepart);
    $totalCount = $countArrive + $countDepart;

    if ($totalCount === 0) {
        header('Location: parametrage.php?msg=archive_empty');
        exit;
    }

    // C. Collecte de tous les chemins de fichiers associés
    $filesToArchive = [];
    $docColumns = ['document_path', 'document_path2', 'document_path3', 'document_path4', 'document_path5'];

    foreach (array_merge($rowsArrive, $rowsDepart) as $rec) {
        foreach ($docColumns as $col) {
            if (!empty($rec[$col])) {
                $rawPath = $rec[$col];
                // Gérer chemin absolu ou relatif
                if (file_exists($rawPath)) {
                    $filesToArchive[] = $rawPath;
                } else {
                    $relPath = __DIR__ . '/' . ltrim($rawPath, '/');
                    if (file_exists($relPath)) {
                        $filesToArchive[] = $relPath;
                    }
                }
            }
        }
    }
    $filesToArchive = array_unique($filesToArchive);

    // D. Génération du dump des enregistrements archivés
    $timestamp = time();
    $dateCutoffStr = date('Y-m-d', strtotime($cutoffDate));
    $zipFilename = "archive_ged_" . $dateCutoffStr . "_" . date('Ymd_His') . ".zip";
    $zipFilePath = $archivesDir . $zipFilename;

    $archiveSqlDump = "-- ARCHIVE OPENGESTICOURRIER V1.3\n";
    $archiveSqlDump .= "-- Description: " . $description . "\n";
    $archiveSqlDump .= "-- Courriers antérieurs au: " . $cutoffDate . "\n";
    $archiveSqlDump .= "-- Date de création: " . date('Y-m-d H:i:s') . "\n\n";

    // Reconstitution des INSERTs Arrive
    if (!empty($rowsArrive)) {
        $archiveSqlDump .= "-- === COURRIERS ARRIVE ARCHIVÉS (" . count($rowsArrive) . ") ===\n";
        foreach ($rowsArrive as $r) {
            $cols = array_keys($r);
            $vals = array_map(function($v) use ($conn) {
                return ($v === null) ? "NULL" : '"' . $conn->real_escape_string($v) . '"';
            }, array_values($r));
            $archiveSqlDump .= "INSERT INTO `courriers_arrive` (`" . implode("`, `", $cols) . "`) VALUES (" . implode(", ", $vals) . ");\n";
        }
        $archiveSqlDump .= "\n";
    }

    // Reconstitution des INSERTs Depart
    if (!empty($rowsDepart)) {
        $archiveSqlDump .= "-- === COURRIERS DEPART ARCHIVÉS (" . count($rowsDepart) . ") ===\n";
        foreach ($rowsDepart as $r) {
            $cols = array_keys($r);
            $vals = array_map(function($v) use ($conn) {
                return ($v === null) ? "NULL" : '"' . $conn->real_escape_string($v) . '"';
            }, array_values($r));
            $archiveSqlDump .= "INSERT INTO `courriers_depart` (`" . implode("`, `", $cols) . "`) VALUES (" . implode(", ", $vals) . ");\n";
        }
        $archiveSqlDump .= "\n";
    }

    // E. Création de l'archive ZIP complète
    if (class_exists('ZipArchive')) {
        $zip = new ZipArchive();
        if ($zip->open($zipFilePath, ZipArchive::CREATE) === TRUE) {
            // Ajouter le fichier de données SQL
            $zip->addFromString('donnees_courriers_archives.sql', $archiveSqlDump);

            // Ajouter un manifeste JSON
            $manifest = [
                'description' => $description,
                'date_cutoff' => $cutoffDate,
                'count_arrive' => $countArrive,
                'count_depart' => $countDepart,
                'archived_at' => date('Y-m-d H:i:s')
            ];
            $zip->addFromString('manifeste.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            // Ajouter les fichiers joints physiques
            foreach ($filesToArchive as $fPath) {
                if (file_exists($fPath)) {
                    $zip->addFile($fPath, 'documents/' . basename($fPath));
                }
            }
            $zip->close();
        }
    } else {
        die("Extension ZipArchive PHP non installée.");
    }

    // F. Suppression physique des fichiers joints du serveur
    foreach ($filesToArchive as $fPath) {
        if (file_exists($fPath)) {
            @unlink($fPath);
        }
    }

    // G. Purge des enregistrements archivés dans la base de données
    $delArrive = $conn->prepare("DELETE FROM courriers_arrive WHERE date <= ?");
    $delArrive->bind_param('s', $cutoffDate);
    $delArrive->execute();

    $delDepart = $conn->prepare("DELETE FROM courriers_depart WHERE date <= ?");
    $delDepart->bind_param('s', $cutoffDate);
    $delDepart->execute();

    // H. Mise à jour de l'index des archives
    $zipSize = file_exists($zipFilePath) ? filesize($zipFilePath) : 0;
    $archivesIndex[] = [
        'archive_filename' => $zipFilename,
        'description'      => $description,
        'date_cutoff'      => date('d/m/Y', strtotime($cutoffDate)),
        'created_at'       => date('d/m/Y H:i'),
        'count_arrive'     => $countArrive,
        'count_depart'     => $countDepart,
        'size_bytes'       => $zipSize
    ];
    saveArchiveIndex($indexFile, $archivesIndex);

    header('Location: parametrage.php?msg=archive_success&total=' . $totalCount);
    exit;
}
?>
