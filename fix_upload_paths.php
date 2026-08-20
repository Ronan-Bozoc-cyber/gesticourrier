<?php
require_once __DIR__ . '/partials/connexion.php';

echo "=== Nettoyage et Normalisation des Chemins des Fichiers Joints ===\n\n";

$tables = ['courriers_arrive', 'courriers_depart'];
$columns = ['document_path', 'document_path2', 'document_path3', 'document_path4', 'document_path5'];

$totalUpdated = 0;

foreach ($tables as $table) {
    echo "Traitement de la table '$table'...\n";
    foreach ($columns as $col) {
        // Sélectionner les enregistrements contenant des chemins absolus obsolètes
        $sql = "SELECT id, $col FROM $table WHERE $col IS NOT NULL AND $col != '' AND ($col LIKE '%/uploads/%' OR $col LIKE '/%')";
        $res = $conn->query($sql);
        
        if ($res && $res->num_rows > 0) {
            while ($row = $res->fetch_assoc()) {
                $oldPath = $row[$col];
                // Extraire le nom de fichier seul
                $fileName = basename($oldPath);
                $newPath = '/uploads/' . $fileName;

                if ($oldPath !== $newPath) {
                    $updateStmt = $conn->prepare("UPDATE $table SET $col = ? WHERE id = ?");
                    $updateStmt->bind_param("si", $newPath, $row['id']);
                    $updateStmt->execute();
                    $totalUpdated++;
                }
            }
        }
    }
}

echo "✅ Terminé ! Total des liens de documents corrigés en base de données : $totalUpdated\n";
?>
