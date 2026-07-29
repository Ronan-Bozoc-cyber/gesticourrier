<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialiser le mot de passe</title>
 
    <link rel="stylesheet" href="../css/style_login.css">
</head>
   
<body>

    <div class="main-container">
       		<h1 class="accueil-titre" style="color:#c80000">GestiCourrier-Conques V1.2</h1>
            <p class="accueil-paragraphe">Solution GED <br> Concepteur - développeur : Ronan BOZOC</p>
    </div>
    <div class="main-container">
        <h1>Réinitialiser le mot de passe</h1>
    
       <form action="process_reset_request.php" method="post">
        <label for="email">Adresse e-mail :</label>
        <input type="email" id="email" name="email" required>
        <button type="submit">Envoyer le lien de réinitialisation</button>
    </form>
    </div> 


</body>
</html>
