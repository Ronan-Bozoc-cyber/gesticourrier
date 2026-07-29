<?php include 'admin/auth_check.php'; ?>
<?php include 'partials/utilisateur/utilisateur_bd.php';?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>👥 Gestion des utilisateurs</title>
    <link rel="stylesheet" href="css/settings.css">
    <link rel="stylesheet" href="css/style_general.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

   
</head>
<body>
<?php include 'partials/header.html'; ?>

<div class="main-container">
    <div class="content-container">
        <h1>👥 Gestion des utilisateurs</h1>

        <!-- Formulaire pour ajouter un nouvel utilisateur -->
        <div class="form-control">
            <button id="add-user-btn">Ajouter un utilisateur</button>
        </div>

        <!-- Champ de recherche pour les utilisateurs -->
        <div class="form-control">
            <label for="search-user">Rechercher un utilisateur</label>
            <input type="text" id="search-user" placeholder="Rechercher par nom d'utilisateur...">
        </div>

        <!-- Modal pour ajout d'un nouvel utilisateur -->
        <div id="addModal" class="modal">
            <div class="modal-content">
                <span class="close-add">&times;</span>
                <h2>👥 Ajouter un nouvel utilisateur</h2>
                <form id="add-user-form">
                    <label for="add-username">Nom d'utilisateur</label>
                    <input type="text" id="add-username" name="username" required>

                    <label for="add-email">Email</label>
                    <input type="email" id="add-email" name="email" required>

                    <label for="add-password">Mot de passe</label>
                    <input type="password" id="add-password" name="password" required>

                    <label for="add-role">Rôle</label>
                    <select id="add-role" name="role" required>
                        <option value="user">Utilisateur</option>
                        <option value="admin">Administrateur</option>
                    </select>

                    <button type="submit">Ajouter l'Utilisateur</button>
                </form>
            </div>
        </div>

        <!-- Liste des utilisateurs -->
        <div class="user-list">
            <h2>Liste des utilisateurs</h2>
            <table id="user-table">
                <thead>
                    <tr>
                        <th>Nom d'utilisateur</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($utilisateurs as $utilisateur): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($utilisateur['username']); ?></td>
                        <td><?php echo htmlspecialchars($utilisateur['email']); ?></td>
                        <td><?php echo htmlspecialchars($utilisateur['role']); ?></td>
                        <td>
                            <button class="edit-btn" data-id="<?php echo $utilisateur['id']; ?>" data-username="<?php echo htmlspecialchars($utilisateur['username']); ?>" data-email="<?php echo htmlspecialchars($utilisateur['email']); ?>" data-role="<?php echo htmlspecialchars($utilisateur['role']); ?>">✏️ Modifier</button>
                        	
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Modal pour modification d'un utilisateur -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>👥 Modifier l'utilisateur</h2>
        <form id="edit-user-form">
            <input type="hidden" id="edit-id" name="id">
            
            <label for="edit-username">Nom d'utilisateur</label>
            <input type="text" id="edit-username" name="username" required>

            <label for="edit-email">Email</label>
            <input type="email" id="edit-email" name="email" required>
            
            <label for="edit-password">Mot de passe</label>
            <input type="password" id="edit-password" name="password">

            <label for="edit-role">Rôle</label>
            <select id="edit-role" name="role" required>
                <option value="user">Utilisateur</option>
                <option value="admin">Administrateur</option>
            </select>

            <button type="submit">Enregistrer les modifications</button>
        </form>
    </div>
</div>

    </div>
</div>
<?php include 'partials/utilisateur/utilisateur_script.php';?>
 <?php include 'partials/menu_actif.html'; ?>

</body>
</html>