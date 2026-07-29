<?php include 'admin/auth_check.php'; ?>
<?php include 'partials/contact/contact_bd.php';?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📇Gestion des contacts</title>
    <link rel="stylesheet" href="css/settings.css">
    <link rel="stylesheet" href="css/style_general.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

</head>
<body>
<?php include 'partials/header.html'; ?>

<div class="main-container">
    <div class="content-container">
        <h1>📇 Gestion des contacts</h1>

        <!-- Formulaire pour ajouter un nouveau contact -->
        <div class="form-control">
            <button id="add-expediteur-btn">Ajouter un contact</button>
        </div>

        <!-- Champ de recherche pour les contacts -->
        <div class="form-control">
            <label for="search-expediteur">Rechercher un contact</label>
            <input type="text" id="search-expediteur" placeholder="Rechercher par nom...">
        </div>

        <!-- Modal pour ajout d'un nouveau contact-->
        <div id="addModal" class="modal">
            <div class="modal-content">
                <span class="close-add">&times;</span>
                <h2>📇 Ajouter un nouveau contact</h2>
                <form id="add-expediteur-form">
                    <label for="add-name">Nom</label>
                    <input type="text" id="add-name" name="name" required>

                    <label for="add-adresse">Adresse</label>
                    <input type="text" id="add-adresse" name="adresse" required>

                    <button type="submit">Ajouter le contact</button>
                </form>
            </div>
        </div>

        <!-- Liste des contacts -->
        <div class="expediteur-list">
            <h2>Liste des contacts</h2>
            <table id="expediteur-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Adresse</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($expediteurs as $expediteur): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($expediteur['name']); ?></td>
                        <td><?php echo htmlspecialchars($expediteur['adresse']); ?></td>
                        <td>
                            <button class="edit-btn" data-id="<?php echo $expediteur['id']; ?>" data-name="<?php echo htmlspecialchars($expediteur['name']); ?>" data-adresse="<?php echo htmlspecialchars($expediteur['adresse']); ?>">✏️ Modifier</button>
                           
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Modal pour modification d'un contact -->
        <div id="editModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>📇 Modifier le contact</h2>
                <form id="edit-expediteur-form">
                    <input type="hidden" id="edit-id" name="id">
                    
                    <label for="edit-name">Nom</label>
                    <input type="text" id="edit-name" name="name">

                    <label for="edit-adresse">Adresse</label>
                    <input type="text" id="edit-adresse" name="adresse">

                    <button type="submit">Enregistrer les modifications</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'partials/contact/contact_script.php';?>
<?php include 'partials/menu_actif.html';?>
</body>
</html>