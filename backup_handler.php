<?php
include 'admin/auth_check.php';
include_once 'partials/parametres.php';
include_once 'partials/connexion.php';

$backupsDir = __DIR__ . '/backups/';
if (!is_dir($backupsDir)) {
    mkdir($backupsDir, 0755, true);
    file_put_contents($backupsDir . '.htaccess', 'Deny from all');
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// 1. TÉLÉCHARGEMENT D'UNE SAUVEGARDE
if ($action === 'download' && !empty($_GET['file'])) {
    $filename = basename($_GET['file']);
    $filepath = $backupsDir . $filename;

    if (file_exists($filepath)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit;
    } else {
        die("Fichier de sauvegarde introuvable.");
    }
}

// 2. SUPPRESSION D'UNE SAUVEGARDE
if ($action === 'delete' && !empty($_GET['file'])) {
    $filename = basename($_GET['file']);
    $filepath = $backupsDir . $filename;

    if (file_exists($filepath)) {
        unlink($filepath);
    }
    header('Location: parametrage.php?msg=deleted');
    exit;
}

// 3. RESTAURATIVE / RESTAURATION D'UNE SAUVEGARDE (Fichier local ou téléversé)
if ($action === 'restore') {
    $filepath = '';
    $filename = '';

    if (isset($_FILES['restore_file']) && $_FILES['restore_file']['error'] === UPLOAD_ERR_OK) {
        $filepath = $_FILES['restore_file']['tmp_name'];
        $filename = $_FILES['restore_file']['name'];
    } elseif (!empty($_GET['file'])) {
        $filename = basename($_GET['file']);
        $filepath = $backupsDir . $filename;
    }

    if (empty($filepath) || !file_exists($filepath)) {
        header('Location: parametrage.php?msg=restore_not_found');
        exit;
    }

    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $sqlContent = '';

    if ($ext === 'sql') {
        $sqlContent = file_get_contents($filepath);
    } elseif ($ext === 'zip' && class_exists('ZipArchive')) {
        $zip = new ZipArchive();
        if ($zip->open($filepath) === TRUE) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                $entryName = $stat['name'];

                // Extraire le contenu SQL
                if (pathinfo($entryName, PATHINFO_EXTENSION) === 'sql') {
                    $sqlContent = $zip->getFromIndex($i);
                }

                // Restaurer les fichiers joints dans uploads/
                if (strpos($entryName, 'uploads/') === 0 || strpos($entryName, 'documents/') === 0) {
                    $relName = strpos($entryName, 'documents/') === 0 ? str_replace('documents/', 'uploads/', $entryName) : $entryName;
                    $targetPath = __DIR__ . '/' . $relName;
                    $targetDir = dirname($targetPath);

                    if (!is_dir($targetDir)) {
                        mkdir($targetDir, 0755, true);
                    }

                    copy("zip://" . $filepath . "#" . $entryName, $targetPath);
                }
            }
            $zip->close();
        }
    }

    if (!empty($sqlContent)) {
        // Exécution des requêtes de restauration SQL
        $conn->query("SET FOREIGN_KEY_CHECKS=0;");
        if ($conn->multi_query($sqlContent)) {
            do {
                if ($result = $conn->store_result()) {
                    $result->free();
                }
            } while ($conn->more_results() && $conn->next_result());
        }
        $conn->query("SET FOREIGN_KEY_CHECKS=1;");

        header('Location: parametrage.php?msg=restore_success');
        exit;
    } else {
        header('Location: parametrage.php?msg=restore_failed');
        exit;
    }
}

// 4. CRÉATION MANUELLE OU AUTOMATIQUE D'UNE SAUVEGARDE
if ($action === 'create') {
    $timestamp = date('Y-m-d_H-i-s');
    $sqlFilename = "backup_ged_" . $timestamp . ".sql";
    $sqlFilePath = $backupsDir . $sqlFilename;

    // Génération du dump SQL via PHP/MySQLi
    $tables = [];
    $result = $conn->query("SHOW TABLES");
    while ($row = $result->fetch_array()) {
        $tables[] = $row[0];
    }

    $sqlDump = "-- ==========================================================\n";
    $sqlDump .= "-- OpenGestiCourrier V1.3 - Export Sauvegarde Base de Données\n";
    $sqlDump .= "-- Date: " . date('Y-m-d H:i:s') . "\n";
    $sqlDump .= "-- ==========================================================\n\n";
    $sqlDump .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

    foreach ($tables as $table) {
        $result = $conn->query("SELECT * FROM `$table`");
        $numFields = $result->field_count;

        $sqlDump .= "DROP TABLE IF EXISTS `$table`;\n";
        $createTableRes = $conn->query("SHOW CREATE TABLE `$table`");
        $createTableRow = $createTableRes->fetch_array();
        $sqlDump .= $createTableRow[1] . ";\n\n";

        while ($row = $result->fetch_row()) {
            $sqlDump .= "INSERT INTO `$table` VALUES(";
            for ($j = 0; $j < $numFields; $j++) {
                if (isset($row[$j])) {
                    $escaped = $conn->real_escape_string($row[$j]);
                    $sqlDump .= '"' . $escaped . '"';
                } else {
                    $sqlDump .= 'NULL';
                }
                if ($j < ($numFields - 1)) {
                    $sqlDump .= ', ';
                }
            }
            $sqlDump .= ");\n";
        }
        $sqlDump .= "\n\n";
    }

    $sqlDump .= "SET FOREIGN_KEY_CHECKS=1;\n";
    file_put_contents($sqlFilePath, $sqlDump);

    // Vérifier si l'option inclure les fichiers uploads est activée
    $includeUploads = isset($org_settings['backup_include_uploads']) && $org_settings['backup_include_uploads'] === '1';

    if ($includeUploads && class_exists('ZipArchive')) {
        $zipFilename = "backup_ged_complet_" . $timestamp . ".zip";
        $zipFilePath = $backupsDir . $zipFilename;
        $zip = new ZipArchive();

        if ($zip->open($zipFilePath, ZipArchive::CREATE) === TRUE) {
            $zip->addFile($sqlFilePath, $sqlFilename);

            $uploadsFolder = __DIR__ . '/uploads/';
            if (is_dir($uploadsFolder)) {
                $files = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($uploadsFolder),
                    RecursiveIteratorIterator::LEAVES_ONLY
                );

                foreach ($files as $name => $file) {
                    if (!$file->isDir()) {
                        $filePath = $file->getRealPath();
                        $relativePath = 'uploads/' . substr($filePath, strlen($uploadsFolder));
                        $zip->addFile($filePath, $relativePath);
                    }
                }
            }
            $zip->close();
            @unlink($sqlFilePath);
        }
    }

    // Rétention
    $retentionCount = intval($org_settings['backup_retention'] ?? 14);
    if ($retentionCount > 0) {
        $allBackups = glob($backupsDir . "backup_ged_*");
        usort($allBackups, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        if (count($allBackups) > $retentionCount) {
            $toDelete = array_slice($allBackups, $retentionCount);
            foreach ($toDelete as $oldFile) {
                @unlink($oldFile);
            }
        }
    }

    header('Location: parametrage.php?msg=created');
    exit;
}
?>
