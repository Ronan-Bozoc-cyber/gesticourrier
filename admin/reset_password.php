<!DOCTYPE html>
<html>
<head>
    <title>Réinitialiser le mot de passe</title>
</head>
<body>
    <h2>Réinitialiser le mot de passe</h2>
    <form action="update_password.php" method="post">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token'], ENT_QUOTES, 'UTF-8'); ?>">
        <label for="password">Nouveau mot de passe :</label>
        <input type="password" id="password" name="password" required>
        <button type="submit">Mettre à jour le mot de passe</button>
    </form>
</body>
</html>
