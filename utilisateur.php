<?php include 'admin/auth_check.php'; ?>
<?php include 'partials/utilisateur/utilisateur_bd.php';?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>👥 Utilisateurs - OpenGestiCourrier</title>
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
            <i class="fas fa-users"></i> Enregistrement des utilisateurs
        </div>
        <button onclick="document.getElementById('search-user').focus()" class="btn-action-search" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);">
            <i class="fas fa-search"></i> Filtrer / Rechercher un utilisateur
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
            #add-user-form input, 
            #add-user-form select,
            .btn-submit-main,
            .edit-btn,
            .delete-btn {
                pointer-events: none !important;
                opacity: 0.65;
            }
            .btn-submit-main, .edit-btn, .delete-btn {
                display: none !important;
            }
        </style>
    <?php endif; ?>

    <!-- Formulaire principal d'enregistrement direct -->
    <div class="form-container" id="form-utilisateur">
        <form id="add-user-form">
            <div class="form-section-card" style="border-top: 4px solid #2563eb;">
                <div class="form-section-header">
                    <h2 style="color: #2563eb;"><i class="fas fa-user-plus"></i> 1. Identification & Compte utilisateur</h2>
                </div>
                <div class="form-group">
                    <label for="add-username">📌 Nom d'utilisateur :</label>
                    <input type="text" id="add-username" name="username" placeholder="Saisir le nom d'utilisateur..." required>
                </div>
                <div class="form-group">
                    <label for="add-email">📌 Adresse e-mail :</label>
                    <input type="email" id="add-email" name="email" placeholder="Saisir l'adresse e-mail..." required>
                </div>
                <div class="form-group">
                    <label for="add-password">📌 Mot de passe :</label>
                    <input type="password" id="add-password" name="password" placeholder="Saisir un mot de passe..." required>
                </div>
                <div class="form-group">
                    <label for="add-role">📌 Rôle / Droits d'accès :</label>
                    <select id="add-role" name="role" required>
                        <option value="user">Utilisateur classique</option>
                        <option value="admin">Administrateur</option>
                    </select>
                </div>
            </div>

            <!-- Bouton principal d'enregistrement -->
            <div style="margin-top: 24px;">
                <button type="submit" class="btn-submit-main" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important;">
                    <i class="fas fa-save"></i> Enregistrer l'Utilisateur
                </button>
            </div>
        </form>
    </div>

    <!-- Section Liste des utilisateurs (exactement comme arrive.php / depart.php) -->
    <div class="table-header-bar" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); margin-top: 35px;">
        <h3><i class="fas fa-list-alt"></i> Liste des utilisateurs référencés</h3>
        <div style="display: flex; align-items: center; gap: 10px;">
            <input type="text" id="search-user" placeholder="🔍 Filtrer les utilisateurs..." style="padding: 6px 12px; border-radius: 6px; border: 1px solid rgba(255,255,255,0.4); font-size: 0.88rem; width: 220px; background: rgba(255,255,255,0.9); color: #0f172a;">
        </div>
    </div>
    <div class="table-container2" style="margin-top: 0; border-radius: 0 0 12px 12px;">
        <table id="user-table" style="font-size: 13px;">
            <thead>
                <tr>
                    <th>Nom d'utilisateur</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th style="width: 130px; text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($utilisateurs as $utilisateur): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($utilisateur['username']); ?></strong></td>
                    <td><?php echo htmlspecialchars($utilisateur['email']); ?></td>
                    <td><span style="background: #e2e8f0; color: #1e293b; padding: 4px 10px; border-radius: 12px; font-size: 0.85rem; font-weight: 600;"><?php echo htmlspecialchars($utilisateur['role']); ?></span></td>
                    <td style="text-align: center;">
                        <button class="edit-btn" data-id="<?php echo $utilisateur['id']; ?>" data-username="<?php echo htmlspecialchars($utilisateur['username']); ?>" data-email="<?php echo htmlspecialchars($utilisateur['email']); ?>" data-role="<?php echo htmlspecialchars($utilisateur['role']); ?>" style="background: #2563eb; color: white; border: none; padding: 6px 14px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 0.85rem;"><i class="fas fa-edit"></i> Modifier</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal pour modification d'un utilisateur -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close close-modal">&times;</span>
            <h2 style="color: #2563eb;"><i class="fas fa-user-edit"></i> Modifier l'utilisateur</h2>
            <form id="edit-user-form">
                <input type="hidden" id="edit-id" name="id">
                <div class="form-group">
                    <label for="edit-username">Nom d'utilisateur :</label>
                    <input type="text" id="edit-username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="edit-email">Email :</label>
                    <input type="email" id="edit-email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="edit-password">Nouveau mot de passe (laisser vide pour ne pas modifier) :</label>
                    <input type="password" id="edit-password" name="password">
                </div>
                <div class="form-group">
                    <label for="edit-role">Rôle :</label>
                    <select id="edit-role" name="role" required>
                        <option value="user">Utilisateur</option>
                        <option value="admin">Administrateur</option>
                    </select>
                </div>
                <button type="submit" class="btn-submit-main" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important; margin-top: 15px;">Enregistrer les modifications</button>
            </form>
        </div>
    </div>
</div>

<?php include 'partials/utilisateur/utilisateur_script.php';?>
<?php include 'partials/menu_actif.html'; ?>
</body>
</html>