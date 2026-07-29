<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Utilisateur</title>
    <link rel="stylesheet" href="../css/style_general.css">
</head>
<body>
    <?php include '../partials/header.html'; ?>

    <div class="main-container">
        <div class="content-container">
            <h1>Ajouter un Utilisateur</h1>
            <form id="add-user-form" method="post" action="admin_add_user_handler.php">
                <label for="username">Nom d'utilisateur</label>
                <input type="text" id="username" name="username" required>

                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>

                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required>

                <label for="role">Rôle</label>
                <select id="role" name="role" required>
                    <option value="user">Utilisateur</option>
                    <option value="admin">Administrateur</option>
                </select>

                <button type="submit">Ajouter l'utilisateur</button>
            </form>
        </div>
    </div>
</body>
</html>
