<?php
// API JSON - supprimer les warnings/erreurs du flux de sortie
ini_set('display_errors', '0');
error_reporting(0);

header('Content-Type: application/json; charset=utf-8');

// Démarrer la session si elle n'est pas déjà active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérification auth
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Non authentifié.']);
    exit;
}

require_once __DIR__ . '/partials/connexion.php';


$dbname = $_ENV['DB_NAME'] ?? 'courriers_db';

try {
    $conn = Database::getInstance()->getConnection();

    // 1. Récupérer toutes les tables
    $tablesResult = $conn->query("SHOW TABLES");
    $tables = [];
    while ($row = $tablesResult->fetch_row()) {
        $tables[] = $row[0];
    }

    $schema = [];

    foreach ($tables as $table) {
        // 2. Colonnes de chaque table
        $colResult = $conn->query("DESCRIBE `$table`");
        $columns = [];
        while ($col = $colResult->fetch_assoc()) {
            $columns[] = [
                'name'    => $col['Field'],
                'type'    => $col['Type'],
                'null'    => $col['Null'],
                'key'     => $col['Key'],
                'default' => $col['Default'],
                'extra'   => $col['Extra'],
            ];
        }

        // 3. Clés étrangères via INFORMATION_SCHEMA
        $fkResult = $conn->query("
            SELECT
                kcu.COLUMN_NAME,
                kcu.REFERENCED_TABLE_NAME,
                kcu.REFERENCED_COLUMN_NAME
            FROM
                INFORMATION_SCHEMA.KEY_COLUMN_USAGE kcu
            JOIN
                INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS rc
                ON kcu.CONSTRAINT_NAME = rc.CONSTRAINT_NAME
                AND kcu.TABLE_SCHEMA = rc.CONSTRAINT_SCHEMA
            WHERE
                kcu.TABLE_SCHEMA = '$dbname'
                AND kcu.TABLE_NAME = '$table'
                AND kcu.REFERENCED_TABLE_NAME IS NOT NULL
        ");

        $foreignKeys = [];
        if ($fkResult) {
            while ($fk = $fkResult->fetch_assoc()) {
                $foreignKeys[] = [
                    'column'          => $fk['COLUMN_NAME'],
                    'referenced_table'=> $fk['REFERENCED_TABLE_NAME'],
                    'referenced_col'  => $fk['REFERENCED_COLUMN_NAME'],
                ];
            }
        }

        // 4. Nombre de lignes
        $countRes = $conn->query("SELECT COUNT(*) as n FROM `$table`");
        $count = 0;
        if ($countRes) {
            $cr = $countRes->fetch_assoc();
            $count = intval($cr['n']);
        }

        $schema[] = [
            'table'       => $table,
            'row_count'   => $count,
            'columns'     => $columns,
            'foreign_keys'=> $foreignKeys,
        ];
    }

    echo json_encode(['success' => true, 'schema' => $schema, 'database' => $dbname], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
