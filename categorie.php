<?php include 'admin/auth_check.php'; ?>
<?php include 'partials/categorie/categorie_bd.php';?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🗂️ Catégories - OpenGestiCourrier</title>
    <link rel="stylesheet" href="css/style_general.css">
    <link rel="stylesheet" href="css/settings.css">
    <link rel="stylesheet" href="css/arrive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<?php include 'partials/header.html'; ?>

<div class="main-container">
    <!-- Barre d'action et Titre (exactement comme arrive.php / depart.php) -->
    <div class="page-action-bar">
        <div class="page-title-badge" style="color: #2563eb;">
            <i class="fas fa-tags"></i> Enregistrement des catégories
        </div>
        <button onclick="document.getElementById('search-category').focus()" class="btn-action-search" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);">
            <i class="fas fa-search"></i> Filtrer / Rechercher une catégorie
        </button>
    </div>

    <?php if (!$can_edit): ?>
        <div style="background: #fef3c7; color: #b45309; border: 1.5px solid #fde68a; padding: 14px 20px; border-radius: 10px; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
            <div>
                <i class="fas fa-lock" style="font-size: 1.2rem; margin-right: 8px;"></i>
                <strong>Mode Consultation Uniquement :</strong> La saisie est actuellement détenue par <u><?php echo htmlspecialchars($lockState['lock_username'] ?? 'un autre utilisateur'); ?></u>.
            </div>
            <a href="lock_action.php?action=claim" class="btn-lock-action claim" style="white-space: nowrap;"><i class="fas fa-pen"></i> Prendre la main pour saisir</a>
        </div>
        <style>
            #add-category-form input, 
            .btn-submit-main,
            .edit-btn {
                pointer-events: none !important;
                opacity: 0.65;
            }
            .btn-submit-main, .edit-btn {
                display: none !important;
            }
        </style>
    <?php endif; ?>

    <!-- Formulaire principal d'enregistrement direct -->
    <div class="form-container" id="form-categorie">
        <form id="add-category-form">
            <div class="form-section-card" style="border-top: 4px solid #2563eb;">
                <div class="form-section-header">
                    <h2 style="color: #2563eb;"><i class="fas fa-folder-plus"></i> 1. Identification de la catégorie</h2>
                </div>
                <div class="form-group">
                    <label for="add-name">📌 Nom de la catégorie :</label>
                    <input type="text" id="add-name" name="name" placeholder="Saisir l'intitulé de la nouvelle catégorie..." required>
                </div>
            </div>

            <!-- Bouton principal d'enregistrement -->
            <div style="margin-top: 24px;">
                <button type="submit" class="btn-submit-main" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important;">
                    <i class="fas fa-save"></i> Enregistrer la Catégorie
                </button>
            </div>
        </form>
    </div>

    <!-- Section Liste des catégories (exactement comme arrive.php / depart.php) -->
    <div class="table-header-bar" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); margin-top: 35px;">
        <h3><i class="fas fa-list-alt"></i> Liste des catégories référencées</h3>
        <div style="display: flex; align-items: center; gap: 10px;">
            <input type="text" id="search-category" placeholder="🔍 Filtrer les catégories..." style="padding: 6px 12px; border-radius: 6px; border: 1px solid rgba(255,255,255,0.4); font-size: 0.88rem; width: 220px; background: rgba(255,255,255,0.9); color: #0f172a;">
        </div>
    </div>
    <div class="table-container2" style="margin-top: 0; border-radius: 0 0 12px 12px;">
        <table id="category-table" style="font-size: 13px;">
            <thead>
                <tr>
                    <th>Nom de la catégorie</th>
                    <th style="width: 130px; text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $category): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($category['name']); ?></strong></td>
                    <td style="text-align: center;">
                        <button class="edit-btn" data-id="<?php echo $category['id']; ?>" data-name="<?php echo htmlspecialchars($category['name']); ?>" style="background: #2563eb; color: white; border: none; padding: 6px 14px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 0.85rem;"><i class="fas fa-edit"></i> Modifier</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal pour modification d'une catégorie -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close close-modal">&times;</span>
            <h2 style="color: #2563eb;"><i class="fas fa-edit"></i> Modifier la catégorie</h2>
            <form id="edit-category-form">
                <input type="hidden" id="edit-id" name="id">
                <div class="form-group">
                    <label for="edit-name">Nom :</label>
                    <input type="text" id="edit-name" name="name" required>
                </div>
                <div class="form-group" style="flex-direction: row; align-items: center; gap: 10px; margin-top: 10px;">
                    <input type="checkbox" id="update-all" name="update_all" style="width: auto;">
                    <label for="update-all" style="margin: 0; font-weight: normal;">Mettre à jour tous les enregistrements associés</label>
                </div>
                <button type="submit" class="btn-submit-main" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important; margin-top: 15px;">Enregistrer les modifications</button>
            </form>
        </div>
    </div>
</div>

<?php include 'partials/menu_actif.html';?>
<?php include 'partials/categorie/categorie_script.php';?>
</body>
</html>