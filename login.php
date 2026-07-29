<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
 
    <link rel="stylesheet" href="./css/style_login.css">
</head>
   
<body>
	<?php include 'partials/parametres.php'; ?>
    <div class="main-container">
       		<h1 class="accueil-titre" style="color:#c80000">GestiCourrier-Conques V1.2</h1>
            <p class="accueil-paragraphe">Solution GED <br> Concepteur - développeur : Ronan BOZOC</p>
    </div>
    <div class="main-container">
        <h1>🔒Connexion</h1>
    
        <form id="login-form" method="post" action="login_handler.php">
            <div class="error" id="error-message" style="display: none;"></div>

            <label for="username">Nom d'utilisateur</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Mot de passe</label>
            <input type="password" id="password" name="password" required>
			<a href="<?php echo $urllogiciel; ?>admin/reset_password_request.php" class="forgot-password-link" style="padding-bottom:20px">Mot de passe oublié ?</a>

            <button type="submit">Se connecter</button>
             
        </form>
    </div> 

    <script>
        document.getElementById('login-form').addEventListener('submit', function(event) {
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;

            if (!username || !password) {
                event.preventDefault();
                document.getElementById('error-message').textContent = 'Veuillez remplir tous les champs.';
                document.getElementById('error-message').style.display = 'block';
            }
        });
    </script>
</body>
</html>
