<?php
$urllogiciel = "http://localhost:8000/";
$uploadDir = __DIR__ . '/../uploads/';
$chemin = __DIR__ . '/../';
$repertoire = '/';

// Chargement des paramètres d'organisation, du tampon et de la sauvegarde
$settingsFile = __DIR__ . '/../data/settings.json';
$defaultSettings = [
    'raison_sociale'       => 'Mairie de Conques-sur-Orbiel',
    'adresse'              => '1 Place de la Mairie',
    'code_postal'          => '11600',
    'ville'                => 'Conques-sur-Orbiel',
    'telephone'            => '04 68 77 17 04',
    'logo_filename'        => 'logo-conques.jpg',
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
    'tampon_texte_custom'  => 'ARRIVÉE - COURRIER',
    'backup_active'          => '1',
    'backup_frequency'       => 'daily',
    'backup_time'            => '02:00',
    'backup_retention'       => '14',
    'backup_include_uploads' => '1',
    'last_backup_date'       => ''
];

if (file_exists($settingsFile)) {
    $jsonContent = @file_get_contents($settingsFile);
    $decoded = json_decode($jsonContent, true);
    if (is_array($decoded)) {
        $org_settings = array_merge($defaultSettings, $decoded);
    } else {
        $org_settings = $defaultSettings;
    }
} else {
    $org_settings = $defaultSettings;
}

$logo_file = !empty($org_settings['logo_filename']) ? $org_settings['logo_filename'] : 'logo-conques.jpg';
if (file_exists(__DIR__ . '/../img/' . $logo_file)) {
    $org_logo_url = $urllogiciel . 'img/' . $logo_file;
} else {
    $org_logo_url = $urllogiciel . 'img/logo-conques.jpg';
}
?>