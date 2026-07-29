<?php include 'admin/auth_check.php'; ?>
<?php include 'partials/categorie/categorie_bd.php';?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🗂️ Gestion des catégories</title>
    <link rel="stylesheet" href="css/settings.css">
    <link rel="stylesheet" href="css/style_general.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

   </head>
<body>
<?php include 'partials/header.html'; ?>

<div class="main-container">
    <div class="content-container">
        <h1>🗂️ Gestion des catégories</h1>

        <!-- Formulaire pour ajouter une nouvelle catégorie -->
        <div class="form-control">
            <button id="add-category-btn">Ajouter une catégorie</button>
        </div>

        <!-- Champ de recherche pour les catégories -->
        <div class="form-control">
            <label for="search-category">Rechercher une catégorie</label>
            <input type="text" id="search-category" placeholder="Rechercher par nom...">
        </div>

        <!-- Modal pour ajout d'une nouvelle catégorie -->
        <div id="addModal" class="modal">
            <div class="modal-content">
                <span class="close-add">&times;</span>
                <h2>🗂️ Ajouter une catégorie</h2>
                <form id="add-category-form">
                    <label for="add-name">Nom</label>
                    <input type="text" id="add-name" name="name" required>

                    <button type="submit">Ajouter la catégorie</button>
                </form>
            </div>
        </div>

        <!-- Liste des catégories -->
        <div class="category-list">
            <h2>Liste des catégories</h2>
            <table id="category-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $category): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($category['name']); ?></td>
                        <td>
                            <button class="edit-btn" data-id="<?php echo $category['id']; ?>" data-name="<?php echo htmlspecialchars($category['name']); ?>">✏️ Modifier</button>
                          
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Modal pour modification d'une catégorie -->
        <div id="editModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>🗂️ Modifier la catégorie</h2>
                <form id="edit-category-form">
                    <input type="hidden" id="edit-id" name="id">
                    
                    <label for="edit-name">Nom</label>
                    <input type="text" id="edit-name" name="name">
                    
                    <label for="update-all">Mettre à jour tous les enregistrements associés</label>
            		<input type="checkbox" id="update-all" name="update_all">

                    <button type="submit">Enregistrer les modifications</button>
                </form>
            </div>
        </div>
    </div>
</div>
                 
<?php include 'partials/menu_actif.html';?>

<?php include 'partials/categorie/categorie_script.php';?>
</body>
</html>




            

            