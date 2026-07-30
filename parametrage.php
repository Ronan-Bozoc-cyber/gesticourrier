<?php
include 'admin/auth_check.php';
include_once 'partials/parametres.php';

$success_msg = '';
$error_msg = '';

if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'created') {
        $success_msg = "Une nouvelle sauvegarde de sécurité a été générée avec succès !";
    } elseif ($_GET['msg'] === 'deleted') {
        $success_msg = "Le fichier de sauvegarde a été supprimé.";
    } elseif ($_GET['msg'] === 'restore_success') {
        $success_msg = "✅ La base de données et les documents de la sauvegarde ont été restaurés avec succès !";
    } elseif ($_GET['msg'] === 'restore_failed') {
        $error_msg = "Erreur lors de l'exécution de la restauration (Fichier SQL ou ZIP corrompu/invalide).";
    } elseif ($_GET['msg'] === 'restore_not_found') {
        $error_msg = "Fichier de sauvegarde introuvable pour la restauration.";
    } elseif ($_GET['msg'] === 'archive_success') {
        $total = intval($_GET['total'] ?? 0);
        $success_msg = "L'archivage et la purge définitive ont été exécutés avec succès ! ($total courrier(s) et leurs documents joints ont été archivés).";
    } elseif ($_GET['msg'] === 'archive_deleted') {
        $success_msg = "L'archive sélectionnée a été supprimée de l'index.";
    } elseif ($_GET['msg'] === 'archive_empty') {
        $error_msg = "Aucun courrier trouvé dans la base de données antérieur à la date sélectionnée.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['is_restore_upload'])) {
    // 1. Coordonnées Organisme
    $org_settings['raison_sociale'] = trim($_POST['raison_sociale'] ?? '');
    $org_settings['adresse']        = trim($_POST['adresse'] ?? '');
    $org_settings['code_postal']    = trim($_POST['code_postal'] ?? '');
    $org_settings['ville']          = trim($_POST['ville'] ?? '');
    $org_settings['telephone']      = trim($_POST['telephone'] ?? '');

    // 2. Configuration du Tampon d'Enregistrement
    $org_settings['tampon_active']        = isset($_POST['tampon_active']) ? '1' : '0';
    $org_settings['tampon_position']      = trim($_POST['tampon_position'] ?? 'top-right');
    $org_settings['tampon_couleur']       = trim($_POST['tampon_couleur'] ?? '#2563eb');
    $org_settings['tampon_opacite']       = trim($_POST['tampon_opacite'] ?? '85');
    $org_settings['tampon_taille']        = trim($_POST['tampon_taille'] ?? 'medium');
    $org_settings['tampon_bordure']       = trim($_POST['tampon_bordure'] ?? 'double');
    $org_settings['tampon_show_org']      = isset($_POST['tampon_show_org']) ? '1' : '0';
    $org_settings['tampon_show_num']      = isset($_POST['tampon_show_num']) ? '1' : '0';
    $org_settings['tampon_show_date']     = isset($_POST['tampon_show_date']) ? '1' : '0';
    $org_settings['tampon_show_categorie']= isset($_POST['tampon_show_categorie']) ? '1' : '0';
    $org_settings['tampon_texte_custom']  = trim($_POST['tampon_texte_custom'] ?? '');

    // 3. Configuration de la Sauvegarde Programmable (Backup)
    $org_settings['backup_active']          = isset($_POST['backup_active']) ? '1' : '0';
    $org_settings['backup_frequency']       = trim($_POST['backup_frequency'] ?? 'daily');
    $org_settings['backup_time']            = trim($_POST['backup_time'] ?? '02:00');
    $org_settings['backup_retention']       = trim($_POST['backup_retention'] ?? '14');
    $org_settings['backup_include_uploads'] = isset($_POST['backup_include_uploads']) ? '1' : '0';

    // 4. Gestion du Logo
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['logo']['tmp_name'];
        $fileName    = $_FILES['logo']['name'];
        $fileSize    = $_FILES['logo']['size'];

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'svg'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (in_array($fileExtension, $allowedExtensions)) {
            if ($fileSize <= 2 * 1024 * 1024) {
                $newFileName = 'logo-custom.' . $fileExtension;
                $uploadFileDir = __DIR__ . '/img/';

                if (!is_dir($uploadFileDir)) {
                    mkdir($uploadFileDir, 0755, true);
                }

                $dest_path = $uploadFileDir . $newFileName;

                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    $org_settings['logo_filename'] = $newFileName;
                } else {
                    $error_msg = "Erreur lors du déplacement du fichier logo téléversé.";
                }
            } else {
                $error_msg = "Le fichier logo est trop volumineux (Maximum 2 Mo).";
            }
        } else {
            $error_msg = "Extension non autorisée pour le logo (Seuls JPG, PNG, WEBP et SVG sont acceptés).";
        }
    }

    if (empty($error_msg)) {
        $settingsFile = __DIR__ . '/data/settings.json';
        if (file_put_contents($settingsFile, json_encode($org_settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
            $success_msg = "Les paramètres ont été enregistrés avec succès !";
            $logo_file = !empty($org_settings['logo_filename']) ? $org_settings['logo_filename'] : 'logo-conques.jpg';
            $org_logo_url = $urllogiciel . 'img/' . $logo_file;
        } else {
            $error_msg = "Erreur d'écriture dans le fichier de configuration des paramètres.";
        }
    }
}

// Récupération des sauvegardes simples dans backups/
$backupsDir = __DIR__ . '/backups/';
$existingBackups = [];
if (is_dir($backupsDir)) {
    $files = glob($backupsDir . "backup_ged_*");
    if ($files) {
        usort($files, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        foreach ($files as $f) {
            $existingBackups[] = [
                'name' => basename($f),
                'size' => size_format(filesize($f)),
                'date' => date('d/m/Y H:i:s', filemtime($f)),
                'is_zip' => (pathinfo($f, PATHINFO_EXTENSION) === 'zip')
            ];
        }
    }
}

// Récupération de l'index des archives définitives
$archivesDir = __DIR__ . '/archives/';
$indexFile = $archivesDir . 'archives_index.json';
$archivesIndex = [];
if (file_exists($indexFile)) {
    $json = @file_get_contents($indexFile);
    $decoded = json_decode($json, true);
    if (is_array($decoded)) {
        $archivesIndex = $decoded;
    }
}

function size_format($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' Go';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' Mo';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' Ko';
    } else {
        return $bytes . ' octets';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔧 Paramètres, Restauration & Archivage - OpenGestiCourrier</title>
    <link rel="stylesheet" href="css/style_general.css">
    <link rel="stylesheet" href="css/settings.css">
    <link rel="stylesheet" href="css/arrive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .color-preset-btn {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: 2px solid #ffffff;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
            cursor: pointer;
            transition: transform 0.2s ease;
        }
        .color-preset-btn:hover {
            transform: scale(1.18);
        }
        .stamp-preview-card {
            background: #f8fafc;
            border: 1.5px dashed #cbd5e1;
            border-radius: 12px;
            padding: 24px;
            min-height: 180px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        .stamp-box {
            padding: 12px 18px;
            line-height: 1.4;
            text-align: center;
            background: rgba(255, 255, 255, 0.95);
            display: inline-block;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
            transition: all 0.2s ease;
        }
    
        .purge-container {
            max-width: 1200px;
            margin: 20px auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            padding: 24px;
        }
        .step-title {
            color: #1e293b;
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e2e8f0;
        }
        .duration-selector {
            display: flex;
            gap: 16px;
            margin-bottom: 20px;
        }
        .duration-card {
            flex: 1;
            padding: 16px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        .duration-card:hover {
            border-color: #3b82f6;
            background: #eff6ff;
        }
        .duration-card.selected {
            border-color: #2563eb;
            background: #eff6ff;
            box-shadow: 0 0 0 3px rgba(37,99,235,0.2);
        }
        .duration-card h3 {
            margin: 0;
            font-size: 1.5rem;
            color: #1e293b;
        }
        .duration-card p {
            margin: 4px 0 0;
            color: #64748b;
            font-size: 0.9rem;
        }
        .duration-card input[type="radio"] {
            display: none;
        }
        
        /* Tabs */
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 16px;
            border-bottom: 2px solid #e2e8f0;
        }
        .tab {
            padding: 10px 20px;
            cursor: pointer;
            font-weight: 600;
            color: #64748b;
            border-bottom: 3px solid transparent;
            margin-bottom: -2px;
        }
        .tab.active {
            color: #2563eb;
            border-bottom-color: #2563eb;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }

        /* Table */
        .purge-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }
        .purge-table th, .purge-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        .purge-table th {
            background: #f8fafc;
            font-weight: 600;
            color: #475569;
        }
        .purge-table tr:hover td {
            background: #f1f5f9;
        }
        .purge-table tr.unchecked td {
            opacity: 0.6;
            background: #f8fafc;
        }

        /* Actions */
        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 24px;
            padding-top: 16px;
            border-top: 1px solid #e2e8f0;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-primary { background: #2563eb; color: white; }
        .btn-primary:hover { background: #1d4ed8; }
        .btn-danger { background: #dc2626; color: white; }
        .btn-danger:hover { background: #b91c1c; }
        .btn-secondary { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
        .btn-secondary:hover { background: #e2e8f0; }

        .linked-badge {
            background: #fef3c7;
            color: #d97706;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        /* Loading */
        #loading {
            display: none;
            text-align: center;
            padding: 40px;
            color: #64748b;
        }
        
        /* Modal */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        .modal-content {
            background: white;
            padding: 24px;
            border-radius: 12px;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);
        }
    
    </style>
</head>
<body>
<?php include 'partials/header.html'; ?>

<div class="main-container">
    <!-- Barre d'action et Titre -->
    <div class="page-action-bar">
        <div class="page-title-badge" style="color: #2563eb;">
            <i class="fas fa-cog"></i> Configuration & Gestion du Système
        </div>
        <a href="backup_handler.php?action=create" class="btn-action-search" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
            <i class="fas fa-database"></i> Sauvegarde Immédiate
        </a>
    </div>

    <?php if (!empty($success_msg)): ?>
        <div style="background: #d1fae5; color: #047857; border: 1.5px solid #a7f3d0; padding: 14px 20px; border-radius: 10px; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-check-circle" style="font-size: 1.2rem;"></i> <?php echo htmlspecialchars($success_msg); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($error_msg)): ?>
        <div style="background: #fef2f2; color: #dc2626; border: 1.5px solid #fecaca; padding: 14px 20px; border-radius: 10px; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-exclamation-triangle" style="font-size: 1.2rem;"></i> <?php echo htmlspecialchars($error_msg); ?>
        </div>
    <?php endif; ?>

    <!-- Navigation par Sous-Menu D'Onglets -->
    <div class="settings-nav-tabs">
        <button type="button" class="tab-btn active" onclick="switchSettingsTab('tab-org')">
            <i class="fas fa-building"></i> 1. Organisme & Logo
        </button>
        <button type="button" class="tab-btn" onclick="switchSettingsTab('tab-tampon')">
            <i class="fas fa-stamp"></i> 2. Tampon d'Enregistrement
        </button>
        <button type="button" class="tab-btn" onclick="switchSettingsTab('tab-backup')">
            <i class="fas fa-database"></i> 3. Sauvegardes & Restauration
        </button>
        <button type="button" class="tab-btn" onclick="switchSettingsTab('tab-archivage')">
            <i class="fas fa-trash-alt"></i> 4. Purge Légale
        </button>
        <button type="button" class="tab-btn" onclick="switchSettingsTab('tab-db')">
            <i class="fas fa-server"></i> 5. Base de Données
        </button>
    </div>

    <!-- ONGLET 1 : ORGANISME & LOGO -->
    <div id="tab-org" class="tab-content-panel" style="display: block;">
        <form action="parametrage.php" method="post" enctype="multipart/form-data">
            <div class="form-section-card" style="border-top: 4px solid #2563eb;">
                <div class="form-section-header">
                    <h2 style="color: #2563eb;"><i class="fas fa-building"></i> 1. Coordonnées de l'organisme (Affichées dans l'en-tête)</h2>
                </div>

                <div class="form-group">
                    <label for="raison_sociale">📌 Raison sociale / Nom de la structure :</label>
                    <input type="text" id="raison_sociale" name="raison_sociale" value="<?php echo htmlspecialchars($org_settings['raison_sociale'] ?? ''); ?>" placeholder="Ex: Mairie de Conques-sur-Orbiel" oninput="updateStampPreview()" required>
                </div>

                <div class="form-group">
                    <label for="adresse">📌 Adresse postale :</label>
                    <input type="text" id="adresse" name="adresse" value="<?php echo htmlspecialchars($org_settings['adresse'] ?? ''); ?>" placeholder="Ex: 1 Place de la Mairie">
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label for="code_postal">📌 Code postal :</label>
                        <input type="text" id="code_postal" name="code_postal" value="<?php echo htmlspecialchars($org_settings['code_postal'] ?? ''); ?>" placeholder="Ex: 11600">
                    </div>

                    <div class="form-group">
                        <label for="ville">📌 Ville :</label>
                        <input type="text" id="ville" name="ville" value="<?php echo htmlspecialchars($org_settings['ville'] ?? ''); ?>" placeholder="Ex: Conques-sur-Orbiel">
                    </div>
                </div>

                <div class="form-group">
                    <label for="telephone">📌 Numéro de téléphone :</label>
                    <input type="text" id="telephone" name="telephone" value="<?php echo htmlspecialchars($org_settings['telephone'] ?? ''); ?>" placeholder="Ex: 04 68 77 17 04">
                </div>
            </div>

            <div class="form-section-card" style="border-top: 4px solid #2563eb;">
                <div class="form-section-header">
                    <h2 style="color: #2563eb;"><i class="fas fa-image"></i> 2. Logo de l'en-tête</h2>
                </div>

                <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 16px; flex-wrap: wrap; background: #f8fafc; padding: 14px; border-radius: 10px; border: 1px solid #e2e8f0;">
                    <div>
                        <div style="font-size: 0.85rem; font-weight: 600; color: #64748b; margin-bottom: 6px;">Logo actuellement actif dans l'en-tête :</div>
                        <img src="<?php echo htmlspecialchars($org_logo_url); ?>" alt="Logo actuel" style="height: 60px; width: auto; object-fit: contain; border-radius: 6px; border: 1px solid #cbd5e1; background: #ffffff; padding: 4px;">
                    </div>
                </div>

                <div class="form-group">
                    <label for="logo">📌 Téléverser un nouveau logo :</label>
                    <input type="file" id="logo" name="logo" accept="image/png, image/jpeg, image/webp, image/svg+xml">
                    <small style="color: #64748b; font-size: 0.84rem; margin-top: 4px;">
                        <i class="fas fa-info-circle"></i> Formats acceptés : PNG, JPG, WEBP, SVG (Max 2 Mo). Le nouveau logo remplacera immédiatement celui de l'en-tête.
                    </small>
                </div>
            </div>

            <div style="margin-top: 24px;">
                <button type="submit" class="btn-submit-main">
                    <i class="fas fa-save"></i> Enregistrer les Coordonnées & Logo
                </button>
            </div>
        </form>
    </div>

    <!-- ONGLET 2 : TAMPON D'ENREGISTREMENT -->
    <div id="tab-tampon" class="tab-content-panel" style="display: none;">
        <form action="parametrage.php" method="post">
            <div class="form-section-card" style="border-top: 4px solid #2563eb;">
                <div class="form-section-header">
                    <h2 style="color: #2563eb;"><i class="fas fa-stamp"></i> Paramètres du Tampon / Empreinte d'Enregistrement</h2>
                </div>

                <div class="form-group" style="flex-direction: row; align-items: center; gap: 12px; background: #eff6ff; padding: 14px 18px; border-radius: 10px; border: 1px solid #bfdbfe;">
                    <input type="checkbox" id="tampon_active" name="tampon_active" value="1" <?php echo ($org_settings['tampon_active'] === '1') ? 'checked' : ''; ?> style="width: 20px; height: 20px; accent-color: #2563eb; cursor: pointer;">
                    <label for="tampon_active" style="margin: 0; font-weight: 700; color: #1e40af; cursor: pointer;">
                        Apposer automatiquement le tampon lors de l'importation d'un courrier
                    </label>
                </div>

                <div class="form-grid-2" style="margin-top: 18px;">
                    <div class="form-group">
                        <label for="tampon_position">📌 Position sur le document :</label>
                        <select id="tampon_position" name="tampon_position" onchange="updateStampPreview()">
                            <option value="top-right" <?php echo ($org_settings['tampon_position'] === 'top-right') ? 'selected' : ''; ?> >↗️ Haut à Droite (Recommandé)</option>
                            <option value="top-left" <?php echo ($org_settings['tampon_position'] === 'top-left') ? 'selected' : ''; ?> >↖️ Haut à Gauche</option>
                            <option value="bottom-right" <?php echo ($org_settings['tampon_position'] === 'bottom-right') ? 'selected' : ''; ?> >↘️ Bas à Droite</option>
                            <option value="bottom-left" <?php echo ($org_settings['tampon_position'] === 'bottom-left') ? 'selected' : ''; ?> >↙️ Bas à Gauche</option>
                            <option value="center" <?php echo ($org_settings['tampon_position'] === 'center') ? 'selected' : ''; ?> >🎯 Centre du document</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="tampon_couleur">📌 Couleur de l'empreinte :</label>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <input type="color" id="tampon_couleur" name="tampon_couleur" value="<?php echo htmlspecialchars($org_settings['tampon_couleur'] ?? '#2563eb'); ?>" onchange="updateStampPreview()" style="width: 50px; height: 42px; border: 1.5px solid #cbd5e1; border-radius: 8px; cursor: pointer; padding: 2px;">
                            <div style="display: flex; gap: 6px;">
                                <div class="color-preset-btn" style="background: #2563eb;" onclick="setStampColor('#2563eb')" title="Bleu Royal"></div>
                                <div class="color-preset-btn" style="background: #dc2626;" onclick="setStampColor('#dc2626')" title="Rouge"></div>
                                <div class="color-preset-btn" style="background: #10b981;" onclick="setStampColor('#10b981')" title="Vert Émeraude"></div>
                                <div class="color-preset-btn" style="background: #d97706;" onclick="setStampColor('#d97706')" title="Ambré"></div>
                                <div class="color-preset-btn" style="background: #7c3aed;" onclick="setStampColor('#7c3aed')" title="Violet"></div>
                                <div class="color-preset-btn" style="background: #0f172a;" onclick="setStampColor('#0f172a')" title="Noir Encre"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-grid-2" style="margin-top: 14px;">
                    <div class="form-group">
                        <label for="tampon_taille">📌 Taille du texte / police :</label>
                        <select id="tampon_taille" name="tampon_taille" onchange="updateStampPreview()">
                            <option value="small" <?php echo ($org_settings['tampon_taille'] === 'small') ? 'selected' : ''; ?> >Petite (10px)</option>
                            <option value="medium" <?php echo ($org_settings['tampon_taille'] === 'medium') ? 'selected' : ''; ?> >Moyenne (12px - Standard)</option>
                            <option value="large" <?php echo ($org_settings['tampon_taille'] === 'large') ? 'selected' : ''; ?> >Grande (14px)</option>
                            <option value="xlarge" <?php echo ($org_settings['tampon_taille'] === 'xlarge') ? 'selected' : ''; ?> >Très grande (16px)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="tampon_bordure">📌 Style du cadre / bordure :</label>
                        <select id="tampon_bordure" name="tampon_bordure" onchange="updateStampPreview()">
                            <option value="double" <?php echo ($org_settings['tampon_bordure'] === 'double') ? 'selected' : ''; ?> >Cadre double encadrement</option>
                            <option value="solid" <?php echo ($org_settings['tampon_bordure'] === 'solid') ? 'selected' : ''; ?> >Cadre simple rectangulaire</option>
                            <option value="rounded" <?php echo ($org_settings['tampon_bordure'] === 'rounded') ? 'selected' : ''; ?> >Cadre aux coins arrondis</option>
                            <option value="dashed" <?php echo ($org_settings['tampon_bordure'] === 'dashed') ? 'selected' : ''; ?> >Cadre pointillé</option>
                            <option value="none" <?php echo ($org_settings['tampon_bordure'] === 'none') ? 'selected' : ''; ?> >Sans cadre (Texte seul)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="margin-top: 14px; background: #f8fafc; padding: 14px 18px; border-radius: 10px; border: 1px solid #e2e8f0;">
                    <label for="tampon_opacite" style="font-weight: 700; color: #334155; display: flex; justify-content: space-between; align-items: center;">
                        <span>📌 Transparence / Opacité de l'empreinte :</span>
                        <span id="opacite-val" style="color: #2563eb; font-weight: 800; font-size: 1.05rem; background: #eff6ff; padding: 2px 10px; border-radius: 6px; border: 1px solid #bfdbfe;"><?php echo htmlspecialchars($org_settings['tampon_opacite'] ?? '85'); ?>%</span>
                    </label>
                    <input type="range" id="tampon_opacite" name="tampon_opacite" min="20" max="100" step="5" value="<?php echo htmlspecialchars($org_settings['tampon_opacite'] ?? '85'); ?>" oninput="document.getElementById('opacite-val').textContent = this.value + '%'; updateStampPreview();" style="width: 100%; accent-color: #2563eb; cursor: pointer; margin-top: 8px;">
                </div>

                <div style="margin-top: 18px; background: #f8fafc; padding: 18px; border-radius: 10px; border: 1px solid #e2e8f0;">
                    <label style="font-weight: 700; color: #334155; display: block; margin-bottom: 12px;">
                        📌 Informations intégrées dans l'empreinte :
                    </label>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px;">
                        <label style="display: flex; align-items: center; gap: 8px; font-weight: 500; cursor: pointer;">
                            <input type="checkbox" id="tampon_show_org" name="tampon_show_org" value="1" <?php echo ($org_settings['tampon_show_org'] === '1') ? 'checked' : ''; ?> onchange="updateStampPreview()" style="accent-color: #2563eb;">
                            Raison sociale de la structure
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; font-weight: 500; cursor: pointer;">
                            <input type="checkbox" id="tampon_show_num" name="tampon_show_num" value="1" <?php echo ($org_settings['tampon_show_num'] === '1') ? 'checked' : ''; ?> onchange="updateStampPreview()" style="accent-color: #2563eb;">
                            Numéro d'ordre du courrier
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; font-weight: 500; cursor: pointer;">
                            <input type="checkbox" id="tampon_show_date" name="tampon_show_date" value="1" <?php echo ($org_settings['tampon_show_date'] === '1') ? 'checked' : ''; ?> onchange="updateStampPreview()" style="accent-color: #2563eb;">
                            Date d'enregistrement
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; font-weight: 500; cursor: pointer;">
                            <input type="checkbox" id="tampon_show_categorie" name="tampon_show_categorie" value="1" <?php echo ($org_settings['tampon_show_categorie'] === '1') ? 'checked' : ''; ?> onchange="updateStampPreview()" style="accent-color: #2563eb;">
                            Catégorie du courrier
                        </label>
                    </div>
                </div>

                <div class="form-group" style="margin-top: 16px;">
                    <label for="tampon_texte_custom">📌 Mention légale / Texte personnalisé du tampon :</label>
                    <input type="text" id="tampon_texte_custom" name="tampon_texte_custom" value="<?php echo htmlspecialchars($org_settings['tampon_texte_custom'] ?? 'ARRIVÉE - COURRIER'); ?>" placeholder="Ex: ARRIVÉE - SERVICES MUNICIPAUX" oninput="updateStampPreview()">
                </div>

                <div class="form-group" style="margin-top: 16px;">
                    <label style="font-weight: 700; color: #334155; display: block; margin-bottom: 10px;">📌 Disposition du tampon :</label>
                    <div style="display: flex; gap: 14px; flex-wrap: wrap;">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; background: #f8fafc; border: 2px solid <?php echo ($org_settings['tampon_disposition'] ?? 'bloc') === 'bloc' ? '#2563eb' : '#e2e8f0'; ?>; border-radius: 10px; padding: 12px 18px; flex: 1; min-width: 200px; transition: border-color .2s;" id="disp-label-bloc">
                            <input type="radio" name="tampon_disposition" value="bloc" <?php echo (($org_settings['tampon_disposition'] ?? 'bloc') === 'bloc') ? 'checked' : ''; ?> onchange="updateStampPreview(); updateDispLabel();" style="accent-color: #2563eb; width: 16px; height: 16px;">
                            <span>
                                <strong style="display: block; color: #1e293b;">📦 Bloc (multi-lignes)</strong>
                                <small style="color: #64748b;">Chaque info sur une ligne séparée — tampon compact et vertical</small>
                            </span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; background: #f8fafc; border: 2px solid <?php echo ($org_settings['tampon_disposition'] ?? 'bloc') === 'ligne' ? '#2563eb' : '#e2e8f0'; ?>; border-radius: 10px; padding: 12px 18px; flex: 1; min-width: 200px; transition: border-color .2s;" id="disp-label-ligne">
                            <input type="radio" name="tampon_disposition" value="ligne" <?php echo (($org_settings['tampon_disposition'] ?? 'bloc') === 'ligne') ? 'checked' : ''; ?> onchange="updateStampPreview(); updateDispLabel();" style="accent-color: #2563eb; width: 16px; height: 16px;">
                            <span>
                                <strong style="display: block; color: #1e293b;">➖ Ligne horizontale</strong>
                                <small style="color: #64748b;">Toutes les infos sur une seule ligne séparées par « | »</small>
                            </span>
                        </label>
                    </div>
                </div>


                <div style="margin-top: 20px;">
                    <label style="font-weight: 700; color: #334155; display: block; margin-bottom: 8px;">
                        👁️ Aperçu en temps réel du Tampon :
                    </label>
                    <div class="stamp-preview-card">
                        <div id="stamp-preview" class="stamp-box">
                            <!-- Généré dynamiquement en JS -->
                        </div>
                    </div>
                </div>
            </div>

            <div style="margin-top: 24px;">
                <button type="submit" class="btn-submit-main">
                    <i class="fas fa-save"></i> Enregistrer les Réglages du Tampon
                </button>
            </div>
        </form>
    </div>

    <!-- ONGLET 3 : SAUVEGARDES & RESTAURATION -->
    <div id="tab-backup" class="tab-content-panel" style="display: none;">
        <form action="parametrage.php" method="post">
            <div class="form-section-card" style="border-top: 4px solid #2563eb;">
                <div class="form-section-header">
                    <h2 style="color: #2563eb;"><i class="fas fa-database"></i> Programmation & Gestion des Sauvegardes (Backup)</h2>
                </div>

                <div class="form-group" style="flex-direction: row; align-items: center; gap: 12px; background: #ecfdf5; padding: 14px 18px; border-radius: 10px; border: 1px solid #a7f3d0;">
                    <input type="checkbox" id="backup_active" name="backup_active" value="1" <?php echo ($org_settings['backup_active'] === '1') ? 'checked' : ''; ?> style="width: 20px; height: 20px; accent-color: #10b981; cursor: pointer;">
                    <label for="backup_active" style="margin: 0; font-weight: 700; color: #047857; cursor: pointer;">
                        Activer la sauvegarde automatique programmée
                    </label>
                </div>

                <div class="form-grid-2" style="margin-top: 18px;">
                    <div class="form-group">
                        <label for="backup_frequency">📌 Fréquence d'exécution :</label>
                        <select id="backup_frequency" name="backup_frequency">
                            <option value="daily" <?php echo ($org_settings['backup_frequency'] === 'daily') ? 'selected' : ''; ?> >📅 Quotidienne (Tous les jours)</option>
                            <option value="weekly" <?php echo ($org_settings['backup_frequency'] === 'weekly') ? 'selected' : ''; ?> >🗓️ Hebdomadaire (Chaque dimanche)</option>
                            <option value="monthly" <?php echo ($org_settings['backup_frequency'] === 'monthly') ? 'selected' : ''; ?> >📆 Mensuelle (Le 1er du mois)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="backup_time">📌 Heure d'exécution programmée :</label>
                        <input type="time" id="backup_time" name="backup_time" value="<?php echo htmlspecialchars($org_settings['backup_time'] ?? '02:00'); ?>">
                    </div>
                </div>

                <div class="form-grid-2" style="margin-top: 14px;">
                    <div class="form-group">
                        <label for="backup_retention">📌 Conservation / Rétention des sauvegardes :</label>
                        <select id="backup_retention" name="backup_retention">
                            <option value="7" <?php echo ($org_settings['backup_retention'] === '7') ? 'selected' : ''; ?> >Conserver les 7 dernières sauvegardes</option>
                            <option value="14" <?php echo ($org_settings['backup_retention'] === '14') ? 'selected' : ''; ?> >Conserver les 14 dernières sauvegardes (Défaut)</option>
                            <option value="30" <?php echo ($org_settings['backup_retention'] === '30') ? 'selected' : ''; ?> >Conserver les 30 dernières sauvegardes</option>
                            <option value="60" <?php echo ($org_settings['backup_retention'] === '60') ? 'selected' : ''; ?> >Conserver les 60 dernières sauvegardes</option>
                        </select>
                    </div>

                    <div class="form-group" style="justify-content: flex-end;">
                        <label style="display: flex; align-items: center; gap: 10px; font-weight: 600; color: #334155; cursor: pointer; padding-top: 25px;">
                            <input type="checkbox" id="backup_include_uploads" name="backup_include_uploads" value="1" <?php echo ($org_settings['backup_include_uploads'] === '1') ? 'checked' : ''; ?> style="width: 18px; height: 18px; accent-color: #2563eb;">
                            📦 Inclure les documents joints (Archivage ZIP complet)
                        </label>
                    </div>
                </div>
            </div>

            <div style="margin-top: 24px; margin-bottom: 30px;">
                <button type="submit" class="btn-submit-main">
                    <i class="fas fa-save"></i> Enregistrer la Programmation des Sauvegardes
                </button>
            </div>
        </form>

        <!-- Formulaire de Restauration Externe -->
        <div class="form-section-card" style="border-top: 4px solid #10b981; margin-top: 20px;">
            <div class="form-section-header">
                <h2 style="color: #059669;"><i class="fas fa-sync"></i> Restauration depuis un fichier de sauvegarde externe (SQL ou ZIP)</h2>
            </div>
            <form action="backup_handler.php?action=restore" method="post" enctype="multipart/form-data">
                <input type="hidden" name="is_restore_upload" value="1">
                <div class="form-group">
                    <label for="restore_file">📌 Choisir un fichier de sauvegarde (.sql ou .zip) à restaurer :</label>
                    <input type="file" id="restore_file" name="restore_file" accept=".sql, .zip" required>
                </div>
                <div style="margin-top: 14px;">
                    <button type="submit" class="btn-submit-main" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;" onclick="return confirm('⚠️ ATTENTION : La restauration de ce fichier de sauvegarde va remplacer les données existantes de la base de données. Souhaitez-vous continuer ?');">
                        <i class="fas fa-undo-alt"></i> Téléverser & Restaurer la Sauvegarde
                    </button>
                </div>
            </form>
        </div>

        <!-- Tableau des Sauvegardes Existantes -->
        <div class="table-header-bar" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); margin-top: 30px;">
            <h3><i class="fas fa-history"></i> Historique des sauvegardes de sécurité (Téléchargement & Restauration)</h3>
            <a href="backup_handler.php?action=create" class="btn-action-search" style="background: #ffffff; color: #2563eb; padding: 6px 14px; font-size: 0.86rem; border: none;">
                <i class="fas fa-plus-circle"></i> Créer une sauvegarde
            </a>
        </div>
        <div class="table-container2" style="margin-top: 0; border-radius: 0 0 12px 12px; margin-bottom: 20px;">
            <table style="font-size: 13px;">
                <thead>
                    <tr>
                        <th>Nom de la sauvegarde</th>
                        <th>Type d'archive</th>
                        <th>Date & Heure</th>
                        <th>Taille</th>
                        <th style="width: 220px; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($existingBackups)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; color: #64748b; padding: 20px;">
                            Aucune sauvegarde de sécurité disponible.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($existingBackups as $bk): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($bk['name']); ?></strong></td>
                            <td>
                                <?php if ($bk['is_zip']): ?>
                                    <span style="background: #dbeafe; color: #1e40af; padding: 3px 8px; border-radius: 12px; font-weight: 700; font-size: 0.8rem;">📦 ZIP complet</span>
                                <?php else: ?>
                                    <span style="background: #fef3c7; color: #b45309; padding: 3px 8px; border-radius: 12px; font-weight: 700; font-size: 0.8rem;">🗄️ Dump SQL</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($bk['date']); ?></td>
                            <td><strong><?php echo htmlspecialchars($bk['size']); ?></strong></td>
                            <td style="text-align: center;">
                                <a href="backup_handler.php?action=restore&file=<?php echo urlencode($bk['name']); ?>" onclick="return confirm('⚠️ ATTENTION : Souhaitez-vous restaurer cette sauvegarde ? Cette opération va remplacer la base de données actuelle.');" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; border: none; padding: 6px 12px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.82rem; margin-right: 4px; display: inline-flex; align-items: center; gap: 4px;" title="Restaurer cette sauvegarde">
                                    <i class="fas fa-undo"></i> Restaurer
                                </a>
                                <a href="backup_handler.php?action=download&file=<?php echo urlencode($bk['name']); ?>" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); color: white; border: none; padding: 6px 10px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.82rem; margin-right: 4px;" title="Télécharger">
                                    <i class="fas fa-download"></i>
                                </a>
                                <a href="backup_handler.php?action=delete&file=<?php echo urlencode($bk['name']); ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette sauvegarde ?');" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white; border: none; padding: 6px 10px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.82rem;" title="Supprimer">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
<div id="tab-archivage" class="tab-content-panel" style="display: none;">
        <div class="form-section-card" style="border-top: 4px solid #dc2626; margin-bottom: 24px; padding: 24px;">
            <div class="form-section-header" style="margin-bottom: 20px;">
                <h2 style="color: #dc2626;"><i class="fas fa-trash-alt"></i> Module de Purge Légale</h2>
            </div>
            
        <div class="purge-container">
        <!-- ÉTAPE 1 -->
        <div id="step-1">
            <h2 class="step-title">1. Sélectionner la durée de conservation</h2>
            <div class="duration-selector">
                <label class="duration-card">
                    <input type="radio" name="duree" value="3">
                    <h3>3 ans</h3>
                    <p>Courriers avant <?php echo date('Y') - 3; ?></p>
                </label>
                <label class="duration-card">
                    <input type="radio" name="duree" value="4">
                    <h3>4 ans</h3>
                    <p>Courriers avant <?php echo date('Y') - 4; ?></p>
                </label>
                <label class="duration-card">
                    <input type="radio" name="duree" value="5" checked>
                    <h3>5 ans</h3>
                    <p>Courriers avant <?php echo date('Y') - 5; ?></p>
                </label>
            </div>
            <div style="text-align: right;">
                <button type="button" class="btn btn-primary" onclick="loadCourriers()">
                    <i class="fas fa-search"></i> Rechercher les courriers à purger
                </button>
            </div>
        </div>

        <div id="loading">
            <i class="fas fa-spinner fa-spin fa-2x"></i>
            <p>Recherche en cours...</p>
        </div>

        <!-- ÉTAPE 2 -->
        <div id="step-2" style="display: none; margin-top: 32px;">
            <h2 class="step-title">2. Sélection des courriers à détruire (<span id="total-count">0</span>)</h2>
            
            <div class="tabs">
                <div class="tab active" onclick="switchTab('arrive')">📥 Arrivée (<span id="arrive-count">0</span>)</div>
                <div class="tab" onclick="switchTab('depart')">📤 Départ (<span id="depart-count">0</span>)</div>
            </div>

            <div style="margin-bottom: 12px; display: flex; gap: 8px;">
                <button class="btn btn-secondary" onclick="toggleAll(true)"><i class="fas fa-check-square"></i> Tout cocher</button>
                <button class="btn btn-secondary" onclick="toggleAll(false)"><i class="far fa-square"></i> Tout décocher</button>
            </div>

            <div id="tab-arrive" class="tab-content active">
                <table class="purge-table">
                    <thead>
                        <tr>
                            <th width="40"><input type="checkbox" id="check-all-arrive" checked onchange="toggleTab('arrive', this.checked)"></th>
                            <th>N° Ordre</th>
                            <th>Année</th>
                            <th>Date</th>
                            <th>Contact</th>
                            <th>Sujet</th>
                            <th>Docs joints</th>
                            <th>Lien</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-arrive"></tbody>
                </table>
            </div>

            <div id="tab-depart" class="tab-content">
                <table class="purge-table">
                    <thead>
                        <tr>
                            <th width="40"><input type="checkbox" id="check-all-depart" checked onchange="toggleTab('depart', this.checked)"></th>
                            <th>N° Ordre</th>
                            <th>Année</th>
                            <th>Date</th>
                            <th>Contact</th>
                            <th>Sujet</th>
                            <th>Docs joints</th>
                            <th>Lien</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-depart"></tbody>
                </table>
            </div>

            <div class="action-bar">
                <span id="selection-status" style="font-weight: 600; color: #dc2626;">0 courrier(s) sélectionné(s) pour destruction.</span>
                <div>
                    <button class="btn btn-secondary" onclick="cancelPurge()">Annuler</button>
                    <button class="btn btn-danger" onclick="confirmPurge()"><i class="fas fa-trash-alt"></i> Détruire les courriers sélectionnés</button>
                </div>
            </div>
        </div>

        <!-- JOURNAL DES DESTRUCTIONS -->
        <div style="margin-top: 48px;">
            <h2 class="step-title">Journal des destructions</h2>
            <?php if (empty($logs)): ?>
                <p style="color: #64748b;">Aucune destruction enregistrée.</p>
            <?php else: ?>
                <table class="purge-table">
                    <thead>
                        <tr>
                            <th>Date / Heure</th>
                            <th>Utilisateur</th>
                            <th>Durée</th>
                            <th>Entrants</th>
                            <th>Sortants</th>
                            <th>Total</th>
                            <th>Certificat</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?php echo date('d/m/Y H:i', strtotime($log['date_destruction'])); ?></td>
                            <td><?php echo htmlspecialchars($log['username']); ?></td>
                            <td><?php echo $log['duree_conservation']; ?> ans</td>
                            <td><?php echo $log['nb_arrive']; ?></td>
                            <td><?php echo $log['nb_depart']; ?></td>
                            <td><strong><?php echo $log['nb_total']; ?></strong></td>
                            <td>
                                <a href="archivage_certificate.php?id=<?php echo $log['id']; ?>" class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.8rem;" target="_blank">
                                    <i class="fas fa-file-pdf" style="color: #dc2626;"></i> PDF
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal de confirmation -->
    <div class="modal-overlay" id="confirm-modal">
        <div class="modal-content">
            <h3 style="color: #dc2626; margin-top: 0;"><i class="fas fa-exclamation-triangle"></i> Confirmation de destruction</h3>
            <p>Vous êtes sur le point de détruire <strong>définitivement</strong> <span id="confirm-count" style="font-weight:bold;">0</span> courrier(s).</p>
            <p>Cette action va supprimer :</p>
            <ul>
                <li>Les enregistrements dans la base de données.</li>
                <li>Les fichiers originaux associés.</li>
            </ul>
            <p style="color: #b45309; font-weight: bold; font-size: 0.9rem;" id="linked-warning"></p>
            <p>Un certificat de destruction sera généré. <strong>Cette action est irréversible.</strong></p>
            <div style="text-align: right; margin-top: 24px; display: flex; gap: 8px; justify-content: flex-end;">
                <button class="btn btn-secondary" onclick="document.getElementById('confirm-modal').style.display='none'">Annuler</button>
                <button class="btn btn-danger" onclick="executePurge()" id="btn-execute">Oui, détruire définitivement</button>
            </div>
        </div>
    </div>
        </div>
    </div>
    <!-- ONGLET 5 : BASE DE DONNÉES -->
    <div id="tab-db" class="tab-content-panel" style="display: none;">

        <!-- Formulaire Connexion DB -->
        <div class="form-section-card" style="border-top: 4px solid #7c3aed;">
            <div class="form-section-header">
                <h2 style="color: #7c3aed;"><i class="fas fa-plug"></i> Paramètres de Connexion à la Base de Données</h2>
            </div>

            <div id="db-save-msg" style="display:none; margin-bottom: 16px;"></div>

            <div style="background: #faf5ff; border: 1.5px solid #e9d5ff; border-radius: 10px; padding: 14px 18px; margin-bottom: 20px; font-size: 0.88rem; color: #6b21a8;">
                <i class="fas fa-shield-alt"></i> <strong>Sécurité :</strong> La connexion est testée avant toute sauvegarde. Le mot de passe n'est jamais affiché. Seuls les administrateurs ont accès à cette section.
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label for="db_host">🖥️ Hôte du serveur (DB_HOST) :</label>
                    <input type="text" id="db_host" placeholder="Ex: localhost ou db" autocomplete="off">
                </div>
                <div class="form-group">
                    <label for="db_name">🗄️ Nom de la base de données (DB_NAME) :</label>
                    <input type="text" id="db_name" placeholder="Ex: courriers_db" autocomplete="off">
                </div>
                <div class="form-group">
                    <label for="db_user">👤 Utilisateur MySQL (DB_USER) :</label>
                    <input type="text" id="db_user" placeholder="Ex: utilisateur" autocomplete="off">
                </div>
                <div class="form-group">
                    <label for="db_pass">🔑 Mot de passe (DB_PASS) :</label>
                    <input type="password" id="db_pass" placeholder="Laisser vide pour ne pas modifier" autocomplete="new-password">
                    <small id="db_pass_hint" style="color: #64748b; font-size: 0.83rem; margin-top: 4px; display: block;"></small>
                </div>
            </div>

            <div style="margin-top: 20px; display: flex; gap: 12px; flex-wrap: wrap;">
                <button type="button" onclick="testDbConnection()" class="btn-submit-main" style="background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%) !important;">
                    <i class="fas fa-plug"></i> Tester la connexion
                </button>
                <button type="button" onclick="saveDbConfig()" class="btn-submit-main" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important;">
                    <i class="fas fa-save"></i> Enregistrer les Paramètres DB
                </button>
            </div>
        </div>

        <!-- Diagramme ERD Visuel -->
        <div class="form-section-card" style="border-top: 4px solid #7c3aed; margin-top: 24px;">
            <div class="form-section-header" style="justify-content: space-between;">
                <h2 style="color: #7c3aed;"><i class="fas fa-project-diagram"></i> Schéma Visuel de la Base de Données (ERD)</h2>
                <button type="button" onclick="loadDbSchema()" id="btn-reload-schema" class="btn-action-search" style="background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%); font-size: 0.88rem;">
                    <i class="fas fa-sync"></i> Actualiser
                </button>
            </div>

            <div id="erd-loading" style="display:none; text-align:center; padding: 30px; color: #7c3aed;">
                <i class="fas fa-spinner fa-spin" style="font-size: 2rem;"></i>
                <p style="margin-top: 10px; font-weight: 600;">Chargement du schéma en cours...</p>
            </div>

            <div id="erd-error" style="display:none;"></div>

            <!-- Statistiques rapides -->
            <div id="erd-stats" style="display:none; display:grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px; margin-bottom: 20px;">
                <div style="background: #f5f3ff; border: 1px solid #e9d5ff; border-radius: 10px; padding: 14px; text-align: center;">
                    <div style="font-size: 1.8rem; font-weight: 800; color: #7c3aed;" id="stat-tables">-</div>
                    <div style="font-size: 0.85rem; color: #6b7280; font-weight: 600;">Tables</div>
                </div>
                <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px; padding: 14px; text-align: center;">
                    <div style="font-size: 1.8rem; font-weight: 800; color: #2563eb;" id="stat-cols">-</div>
                    <div style="font-size: 0.85rem; color: #6b7280; font-weight: 600;">Colonnes</div>
                </div>
                <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 10px; padding: 14px; text-align: center;">
                    <div style="font-size: 1.8rem; font-weight: 800; color: #059669;" id="stat-fk">-</div>
                    <div style="font-size: 0.85rem; color: #6b7280; font-weight: 600;">Relations (FK)</div>
                </div>
                <div style="background: #fff7ed; border: 1px solid #fed7aa; border-radius: 10px; padding: 14px; text-align: center;">
                    <div style="font-size: 1.8rem; font-weight: 800; color: #ea580c;" id="stat-rows">-</div>
                    <div style="font-size: 0.85rem; color: #6b7280; font-weight: 600;">Enregistrements</div>
                </div>
            </div>

            <!-- Conteneur ERD -->
            <div id="erd-container" style="overflow: auto; background: #fafafa; border: 1.5px solid #e9d5ff; border-radius: 12px; min-height: 420px; position: relative; padding: 10px;">
                <p style="text-align:center; color: #94a3b8; padding: 80px 0; font-style: italic;">Cliquez sur « Actualiser » pour afficher le schéma de la base de données.</p>
            </div>
        </div>
    </div>

</div>

<script>
function setStampColor(color) {
    document.getElementById('tampon_couleur').value = color;
    updateStampPreview();
}

function updateStampPreview() {
    const previewBox = document.getElementById('stamp-preview');
    if (!previewBox) return;

    const color = document.getElementById('tampon_couleur').value;
    const border = document.getElementById('tampon_bordure').value;
    const size = document.getElementById('tampon_taille').value;
    const customText = document.getElementById('tampon_texte_custom').value;
    const showOrg = document.getElementById('tampon_show_org').checked;
    const showNum = document.getElementById('tampon_show_num').checked;
    const showDate = document.getElementById('tampon_show_date').checked;
    const showCat = document.getElementById('tampon_show_categorie').checked;
    const orgName = document.getElementById('raison_sociale').value || 'MAIRIE DE CONQUES';

    // Disposition (bloc ou ligne)
    const dispEl = document.querySelector('input[name="tampon_disposition"]:checked');
    const disposition = dispEl ? dispEl.value : 'bloc';

    let borderStyle = '2px solid ' + color;
    if (border === 'double') borderStyle = '4px double ' + color;
    if (border === 'dashed') borderStyle = '2px dashed ' + color;
    if (border === 'rounded') borderStyle = '2px solid ' + color;
    if (border === 'none') borderStyle = 'none';

    let fontSize = '12px';
    if (size === 'small') fontSize = '10px';
    if (size === 'large') fontSize = '14px';
    if (size === 'xlarge') fontSize = '16px';

    let borderRadius = (border === 'rounded') ? '10px' : '0px';

    const opaciteInput = document.getElementById('tampon_opacite');
    const opaciteVal = opaciteInput ? (parseFloat(opaciteInput.value) / 100) : 0.85;

    previewBox.style.border = borderStyle;
    previewBox.style.color = color;
    previewBox.style.opacity = opaciteVal;
    previewBox.style.fontSize = fontSize;
    previewBox.style.borderRadius = borderRadius;

    // Construire les parties du tampon
    const parts = [];
    if (customText) parts.push(customText.toUpperCase());
    if (showOrg && orgName) parts.push(orgName);
    if (showNum) parts.push('N° Ordre : 2026-0042');
    if (showDate) parts.push('Enregistré le : 30/07/2026');
    if (showCat) parts.push('Catégorie : COMMUNICATION');

    let html = '';
    if (disposition === 'ligne') {
        // Une seule ligne avec séparateurs |
        previewBox.style.whiteSpace = 'nowrap';
        previewBox.style.display = 'inline-flex';
        previewBox.style.alignItems = 'center';
        previewBox.style.gap = '0';
        previewBox.style.padding = '6px 14px';
        html = parts.map((p, i) => {
            const isFirst = i === 0;
            const style = isFirst
                ? `font-weight:900; text-transform:uppercase; letter-spacing:0.5px;`
                : `font-weight:500;`;
            const sep = i > 0 ? `<span style="margin: 0 8px; opacity:0.5; font-weight:300;">|</span>` : '';
            return `${sep}<span style="${style}">${p}</span>`;
        }).join('');
    } else {
        // Bloc multi-lignes (comportement original)
        previewBox.style.whiteSpace = '';
        previewBox.style.display = '';
        previewBox.style.alignItems = '';
        previewBox.style.gap = '';
        previewBox.style.padding = '';
        parts.forEach((p, i) => {
            const isFirst = i === 0;
            html += `<div style="font-weight:${isFirst ? '900' : '500'}; ${isFirst ? 'text-transform:uppercase; letter-spacing:0.5px; margin-bottom:2px;' : ''}">${p}</div>`;
        });
    }

    previewBox.innerHTML = html || '<em style="opacity:0.5">Aucun élément sélectionné</em>';
}

function updateDispLabel() {
    const selected = document.querySelector('input[name="tampon_disposition"]:checked');
    if (!selected) return;
    const val = selected.value;
    const lblBloc = document.getElementById('disp-label-bloc');
    const lblLigne = document.getElementById('disp-label-ligne');
    if (lblBloc) lblBloc.style.borderColor = (val === 'bloc') ? '#2563eb' : '#e2e8f0';
    if (lblLigne) lblLigne.style.borderColor = (val === 'ligne') ? '#2563eb' : '#e2e8f0';
}

function switchSettingsTab(tabId) {
    document.querySelectorAll('.tab-content-panel').forEach(panel => panel.style.display = 'none');
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));

    const activePanel = document.getElementById(tabId);
    if (activePanel) activePanel.style.display = 'block';

    const activeBtn = document.querySelector(`[onclick="switchSettingsTab('${tabId}')"]`);
    if (activeBtn) activeBtn.classList.add('active');

    window.location.hash = tabId;

    if (tabId === 'tab-db') {
        loadDbConfig();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    updateStampPreview();
    const hash = window.location.hash.replace('#', '');
    if (hash && document.getElementById(hash)) {
        switchSettingsTab(hash);
    }
});

// =============================================
// GESTION DE LA BASE DE DONNÉES
// =============================================
function loadDbConfig() {
    fetch('db_config_handler.php', { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('db_host').value = data.DB_HOST || '';
                document.getElementById('db_name').value = data.DB_NAME || '';
                document.getElementById('db_user').value = data.DB_USER || '';
                const hint = document.getElementById('db_pass_hint');
                if (hint) hint.textContent = data.DB_PASS_SET ? '🔒 Un mot de passe est actuellement défini. Laissez vide pour le conserver.' : '⚠️ Aucun mot de passe défini.';
            }
        })
        .catch(() => {});
}

function testDbConnection() {
    const host = document.getElementById('db_host').value.trim();
    const user = document.getElementById('db_user').value.trim();
    const pass = document.getElementById('db_pass').value;
    const name = document.getElementById('db_name').value.trim();
    const msgEl = document.getElementById('db-save-msg');

    if (!host || !user || !name) {
        showDbMsg('⚠️ Veuillez remplir au moins l\'hôte, l\'utilisateur et le nom de la base.', 'warning');
        return;
    }

    showDbMsg('<i class="fas fa-spinner fa-spin"></i> Test de connexion en cours...', 'info');

    const fd = new FormData();
    fd.append('action', 'test');  // TEST uniquement - ne sauvegarde pas
    fd.append('DB_HOST', host);
    fd.append('DB_USER', user);
    fd.append('DB_PASS', pass);
    fd.append('DB_NAME', name);

    fetch('db_config_handler.php', { method: 'POST', body: fd, headers: { 'Accept': 'application/json' } })
        .then(r => r.text())
        .then(rawText => {
            try {
                const data = JSON.parse(rawText);
                if (data.success) {
                    showDbMsg('✅ ' + (data.message || 'Connexion réussie !'), 'success');
                } else {
                    showDbMsg('❌ ' + (data.error || 'Erreur de connexion.'), 'error');
                }
            } catch(e) {
                showDbMsg('❌ Réponse serveur invalide (HTTP 500). Consultez les logs Apache.', 'error');
                console.error('[db_config] Raw:', rawText);
            }
        })
        .catch(e => showDbMsg('❌ Erreur réseau : ' + e, 'error'));
}

function saveDbConfig() {
    const host = document.getElementById('db_host').value.trim();
    const user = document.getElementById('db_user').value.trim();
    const pass = document.getElementById('db_pass').value;
    const name = document.getElementById('db_name').value.trim();

    if (!host || !user || !name) {
        showDbMsg('⚠️ Hôte, utilisateur et nom de la base sont obligatoires.', 'warning');
        return;
    }

    if (!confirm('Êtes-vous sûr de vouloir modifier les paramètres de connexion à la base de données ? La page sera rechargée.')) return;

    showDbMsg('<i class="fas fa-spinner fa-spin"></i> Enregistrement en cours...', 'info');

    const fd = new FormData();
    fd.append('action', 'save');  // SAVE - écrit dans .env
    fd.append('DB_HOST', host);
    fd.append('DB_USER', user);
    fd.append('DB_PASS', pass);
    fd.append('DB_NAME', name);

    fetch('db_config_handler.php', { method: 'POST', body: fd, headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showDbMsg('✅ ' + (data.message || 'Paramètres enregistrés !'), 'success');
                document.getElementById('db_pass').value = '';
                document.getElementById('db_pass_hint').textContent = '🔒 Mot de passe mis à jour.';
            } else {
                showDbMsg('❌ ' + (data.error || 'Erreur lors de l\'enregistrement.'), 'error');
            }
        })
        .catch(e => showDbMsg('❌ Erreur réseau : ' + e, 'error'));
}

function showDbMsg(msg, type) {
    const el = document.getElementById('db-save-msg');
    const styles = {
        success: 'background:#d1fae5; color:#047857; border: 1.5px solid #a7f3d0;',
        error:   'background:#fef2f2; color:#dc2626; border: 1.5px solid #fecaca;',
        warning: 'background:#fefce8; color:#b45309; border: 1.5px solid #fde68a;',
        info:    'background:#eff6ff; color:#2563eb; border: 1.5px solid #bfdbfe;'
    };
    el.style.cssText = (styles[type] || styles.info) + ' padding: 12px 18px; border-radius: 10px; font-weight: 700; display: flex; align-items: center; gap: 10px;';
    el.innerHTML = msg;
    el.style.display = 'flex';
}

// =============================================
// DIAGRAMME ERD VISUEL
// =============================================
let erdSchema = null;

function loadDbSchema() {
    const container = document.getElementById('erd-container');
    const loadingEl = document.getElementById('erd-loading');
    const errorEl = document.getElementById('erd-error');
    const statsEl = document.getElementById('erd-stats');

    container.style.display = 'none';
    loadingEl.style.display = 'block';
    errorEl.style.display = 'none';
    statsEl.style.display = 'none';

    fetch('db_schema.php', { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            loadingEl.style.display = 'none';
            if (!data.success) {
                errorEl.innerHTML = `<div style="background:#fef2f2; color:#dc2626; border:1.5px solid #fecaca; padding:14px; border-radius:10px; font-weight:700;"><i class="fas fa-exclamation-triangle"></i> ${data.error}</div>`;
                errorEl.style.display = 'block';
                return;
            }
            erdSchema = data.schema;
            renderErd(data.schema, data.database);

            // Stats
            let totalCols = 0, totalFk = 0, totalRows = 0;
            data.schema.forEach(t => {
                totalCols += t.columns.length;
                totalFk   += t.foreign_keys.length;
                totalRows += t.row_count;
            });
            document.getElementById('stat-tables').textContent = data.schema.length;
            document.getElementById('stat-cols').textContent = totalCols;
            document.getElementById('stat-fk').textContent = totalFk;
            document.getElementById('stat-rows').textContent = totalRows.toLocaleString('fr-FR');
            statsEl.style.display = 'grid';
            container.style.display = 'block';
        })
        .catch(e => {
            loadingEl.style.display = 'none';
            errorEl.innerHTML = `<div style="background:#fef2f2; color:#dc2626; border:1.5px solid #fecaca; padding:14px; border-radius:10px; font-weight:700;"><i class="fas fa-exclamation-triangle"></i> Erreur lors du chargement : ${e}</div>`;
            errorEl.style.display = 'block';
        });
}

function renderErd(schema, dbName) {
    const container = document.getElementById('erd-container');

    const TABLE_W = 240;
    const ROW_H  = 26;
    const HEADER_H = 38;
    const PAD_X  = 60;
    const PAD_Y  = 60;
    const cols   = Math.max(1, Math.floor(Math.min(schema.length, 3)));

    // Calculer positions
    const positions = {};
    schema.forEach((t, i) => {
        const col = i % cols;
        const row = Math.floor(i / cols);
        const tableH = HEADER_H + t.columns.length * ROW_H;
        positions[t.table] = {
            x: PAD_X + col * (TABLE_W + PAD_X * 2),
            y: PAD_Y + row * 300,
            w: TABLE_W,
            h: tableH
        };
    });

    const totalW = cols * (TABLE_W + PAD_X * 2) + PAD_X;
    const rows   = Math.ceil(schema.length / cols);
    const totalH = rows * 300 + PAD_Y * 2;

    const colors = {
        header_bg: '#7c3aed', header_text: '#fff',
        body_bg: '#fff', body_border: '#e9d5ff',
        pk_bg: '#faf5ff', pk_text: '#7c3aed',
        fk_bg: '#f0fdf4', fk_text: '#059669',
        col_text: '#334155', col_alt: '#fafafa',
        arrow: '#7c3aed', arrow_fk: '#2563eb',
    };

    let svg = `<svg xmlns="http://www.w3.org/2000/svg" width="${totalW}" height="${totalH}" style="font-family: 'Inter', system-ui, sans-serif;">`;

    // Définir marqueurs de flèche
    svg += `<defs>
        <marker id="arrow" markerWidth="10" markerHeight="7" refX="9" refY="3.5" orient="auto">
            <polygon points="0 0, 10 3.5, 0 7" fill="${colors.arrow_fk}"/>
        </marker>
        <filter id="shadow" x="-5%" y="-5%" width="110%" height="110%">
            <feDropShadow dx="0" dy="3" stdDeviation="4" flood-color="rgba(124,58,237,0.12)"/>
        </filter>
    </defs>`;

    // Dessiner les relations (FK) d'abord (en arrière plan)
    schema.forEach(t => {
        t.foreign_keys.forEach(fk => {
            const from = positions[t.table];
            const to   = positions[fk.referenced_table];
            if (!from || !to) return;

            // Trouver l'index de la colonne FK pour la hauteur de départ
            const colIdx = t.columns.findIndex(c => c.name === fk.column);
            const fromY  = from.y + HEADER_H + colIdx * ROW_H + ROW_H / 2;
            const fromX  = from.x + TABLE_W;

            // Trouver l'index de la colonne référencée
            const refColIdx = (to ? to.y : 0);
            const toY  = to.y + HEADER_H + ROW_H / 2;
            const toX  = to.x;

            const midX = (fromX + toX) / 2;

            svg += `<path d="M${fromX},${fromY} C${midX},${fromY} ${midX},${toY} ${toX},${toY}"
                stroke="${colors.arrow_fk}" stroke-width="2" fill="none" stroke-dasharray="6,3"
                marker-end="url(#arrow)" opacity="0.75"/>`;

            // Label de la relation
            const labelX = midX;
            const labelY = (fromY + toY) / 2 - 4;
            svg += `<rect x="${labelX - 30}" y="${labelY - 11}" width="60" height="16" rx="4" fill="#eff6ff" stroke="#bfdbfe" stroke-width="1"/>`;
            svg += `<text x="${labelX}" y="${labelY}" text-anchor="middle" font-size="9" fill="#2563eb" font-weight="600">${fk.column} → ${fk.referenced_col}</text>`;
        });
    });

    // Dessiner les tables
    schema.forEach(t => {
        const pos = positions[t.table];
        const tableH = pos.h;

        // Ombre et fond
        svg += `<rect x="${pos.x}" y="${pos.y}" width="${TABLE_W}" height="${tableH}" rx="10" fill="#fff" stroke="${colors.body_border}" stroke-width="1.5" filter="url(#shadow)"/>`;

        // En-tête table
        svg += `<rect x="${pos.x}" y="${pos.y}" width="${TABLE_W}" height="${HEADER_H}" rx="10" fill="${colors.header_bg}"/>`;
        svg += `<rect x="${pos.x}" y="${pos.y + HEADER_H - 10}" width="${TABLE_W}" height="10" fill="${colors.header_bg}"/>`;

        // Nom de table + compteur lignes
        svg += `<text x="${pos.x + 12}" y="${pos.y + 24}" font-size="13" font-weight="800" fill="#fff">${t.table}</text>`;
        svg += `<rect x="${pos.x + TABLE_W - 52}" y="${pos.y + 9}" width="44" height="18" rx="9" fill="rgba(255,255,255,0.2)"/>`;
        svg += `<text x="${pos.x + TABLE_W - 30}" y="${pos.y + 22}" font-size="10" font-weight="700" fill="#fff" text-anchor="middle">${t.row_count} lignes</text>`;

        // Colonnes
        t.columns.forEach((col, i) => {
            const cy = pos.y + HEADER_H + i * ROW_H;
            const isPK = col.key === 'PRI';
            const isFK = col.key === 'MUL';

            // Fond alterné
            const rowBg = isPK ? '#faf5ff' : isFK ? '#f0fdf4' : (i % 2 === 0 ? '#fff' : '#f8fafc');
            svg += `<rect x="${pos.x}" y="${cy}" width="${TABLE_W}" height="${ROW_H}" fill="${rowBg}" stroke="${colors.body_border}" stroke-width="0.5"/>`;

            // Badge PK/FK
            if (isPK) {
                svg += `<rect x="${pos.x + 6}" y="${cy + 6}" width="22" height="13" rx="3" fill="${colors.header_bg}"/>`;
                svg += `<text x="${pos.x + 17}" y="${cy + 16}" font-size="8" font-weight="800" fill="#fff" text-anchor="middle">PK</text>`;
            } else if (isFK) {
                svg += `<rect x="${pos.x + 6}" y="${cy + 6}" width="22" height="13" rx="3" fill="${colors.fk_text}"/>`;
                svg += `<text x="${pos.x + 17}" y="${cy + 16}" font-size="8" font-weight="800" fill="#fff" text-anchor="middle">FK</text>`;
            }

            // Nom colonne
            const textX = isPK || isFK ? pos.x + 34 : pos.x + 10;
            const nameColor = isPK ? '#7c3aed' : isFK ? '#059669' : '#334155';
            svg += `<text x="${textX}" y="${cy + 17}" font-size="11" font-weight="${isPK || isFK ? '700' : '500'}" fill="${nameColor}">${col.name}</text>`;

            // Type
            svg += `<text x="${pos.x + TABLE_W - 8}" y="${cy + 17}" font-size="9.5" fill="#94a3b8" text-anchor="end">${col.type}</text>`;
        });

        // Bordure basse arrondie
        svg += `<rect x="${pos.x}" y="${pos.y + tableH - 10}" width="${TABLE_W}" height="10" rx="0" fill="#fff"/>`;
        svg += `<path d="M${pos.x},${pos.y + tableH - 10} L${pos.x},${pos.y + tableH} Q${pos.x},${pos.y + tableH} ${pos.x + 10},${pos.y + tableH} L${pos.x + TABLE_W - 10},${pos.y + tableH} Q${pos.x + TABLE_W},${pos.y + tableH} ${pos.x + TABLE_W},${pos.y + tableH} L${pos.x + TABLE_W},${pos.y + tableH - 10}" fill="none" stroke="${colors.body_border}" stroke-width="1.5"/>`;
    });

    svg += `</svg>`;
    container.innerHTML = svg;
}
</script>

<script>

        let currentData = { arrive: [], depart: [], duree: 5 };

        // Cartes durée
        document.querySelectorAll('.duration-card').forEach(card => {
            card.addEventListener('click', function() {
                document.querySelectorAll('.duration-card').forEach(c => c.classList.remove('selected'));
                this.classList.add('selected');
                this.querySelector('input').checked = true;
            });
        });
        document.querySelector('.duration-card input:checked').closest('.duration-card').classList.add('selected');

        function switchTab(tabName) {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            document.querySelector(`.tab[onclick="switchTab('${tabName}')"]`).classList.add('active');
            document.getElementById(`tab-${tabName}`).classList.add('active');
        }

        async function loadCourriers() {
            const duree = document.querySelector('input[name="duree"]:checked').value;
            document.getElementById('step-1').style.display = 'none';
            document.getElementById('loading').style.display = 'block';

            try {
                const res = await fetch(`archivage_list_handler.php?duree=${duree}`);
                const data = await res.json();
                
                if (!data.success) throw new Error(data.error);

                currentData = data;
                renderTables();
                
                document.getElementById('loading').style.display = 'none';
                document.getElementById('step-2').style.display = 'block';
                updateSelectionStatus();

            } catch (err) {
                alert('Erreur : ' + err.message);
                document.getElementById('loading').style.display = 'none';
                document.getElementById('step-1').style.display = 'block';
            }
        }

        function cancelPurge() {
            document.getElementById('step-2').style.display = 'none';
            document.getElementById('step-1').style.display = 'block';
        }

        function renderRow(item, type) {
            const dateStr = item.date ? new Date(item.date).toLocaleDateString('fr-FR') : '-';
            const cbId = `cb_${type}_${item.id}`;
            let lienHtml = '-';
            
            if (type === 'arrive' && item.courrier_depart_id) {
                lienHtml = `<span class="linked-badge" title="Lié au départ N°${item.depart_num_ordre}"><i class="fas fa-link"></i> Dép. ${item.depart_num_ordre}</span>`;
            } else if (type === 'depart' && item.courrier_arrive_id) {
                lienHtml = `<span class="linked-badge" title="Lié à l'arrivée N°${item.arrive_num_ordre}"><i class="fas fa-link"></i> Arr. ${item.arrive_num_ordre}</span>`;
            }

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><input type="checkbox" id="${cbId}" class="cb-purge cb-${type}" value="${item.id}" data-type="${type}" checked onchange="updateSelectionStatus(); checkLinked(this, '${type}', ${item.id})"></td>
                <td><label for="${cbId}">${item.num_ordre}</label></td>
                <td>${item.annee}</td>
                <td>${dateStr}</td>
                <td>${item.expediteur || '-'}</td>
                <td>${item.sujet_courrier || '-'}</td>
                <td>${item.nb_documents > 0 ? `<span style="background:#eff6ff; color:#2563eb; padding:2px 8px; border-radius:10px;">${item.nb_documents} 📎</span>` : '-'}</td>
                <td>${lienHtml}</td>
            `;
            return tr;
        }

        function renderTables() {
            const tbArrive = document.getElementById('tbody-arrive');
            const tbDepart = document.getElementById('tbody-depart');
            tbArrive.innerHTML = '';
            tbDepart.innerHTML = '';

            document.getElementById('arrive-count').textContent = currentData.arrive.length;
            document.getElementById('depart-count').textContent = currentData.depart.length;
            document.getElementById('total-count').textContent = currentData.total;

            currentData.arrive.forEach(item => tbArrive.appendChild(renderRow(item, 'arrive')));
            currentData.depart.forEach(item => tbDepart.appendChild(renderRow(item, 'depart')));
        }

        function toggleAll(state) {
            document.querySelectorAll('.cb-purge').forEach(cb => { cb.checked = state; });
            updateSelectionStatus();
        }
        function toggleTab(type, state) {
            document.querySelectorAll(`.cb-${type}`).forEach(cb => { cb.checked = state; });
            updateSelectionStatus();
        }

        function checkLinked(cb, type, id) {
            // Optionnel : on pourrait décocher/cocher le courrier lié automatiquement
            // Ici on gère juste le visuel
        }

        function updateSelectionStatus() {
            const count = document.querySelectorAll('.cb-purge:checked').length;
            document.getElementById('selection-status').textContent = `${count} courrier(s) sélectionné(s) pour destruction.`;
            document.querySelectorAll('.cb-purge').forEach(cb => {
                const tr = cb.closest('tr');
                if (cb.checked) tr.classList.remove('unchecked');
                else tr.classList.add('unchecked');
            });
        }

        function confirmPurge() {
            const checkedArrive = Array.from(document.querySelectorAll('.cb-arrive:checked')).map(cb => parseInt(cb.value));
            const checkedDepart = Array.from(document.querySelectorAll('.cb-depart:checked')).map(cb => parseInt(cb.value));
            const total = checkedArrive.length + checkedDepart.length;

            if (total === 0) {
                alert("Veuillez sélectionner au moins un courrier à détruire.");
                return;
            }

            document.getElementById('confirm-count').textContent = total;
            document.getElementById('confirm-modal').style.display = 'flex';
        }

        async function executePurge() {
            const btn = document.getElementById('btn-execute');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Destruction...';

            const idsArrive = Array.from(document.querySelectorAll('.cb-arrive:checked')).map(cb => parseInt(cb.value));
            const idsDepart = Array.from(document.querySelectorAll('.cb-depart:checked')).map(cb => parseInt(cb.value));
            
            try {
                const res = await fetch('archivage_destroy_handler.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        ids_arrive: idsArrive,
                        ids_depart: idsDepart,
                        duree: currentData.duree
                    })
                });
                const data = await res.json();
                
                if (!data.success) throw new Error(data.error);

                alert(data.message);
                window.location.reload();

            } catch(err) {
                alert("Erreur lors de la destruction : " + err.message);
                btn.disabled = false;
                btn.innerHTML = 'Oui, détruire définitivement';
                document.getElementById('confirm-modal').style.display = 'none';
            }
        }
    
</script>
</body>
</html>
