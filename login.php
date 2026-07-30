<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - OpenGestiCourrier V1.3</title>
    <link rel="stylesheet" href="css/style_general.css">
    <link rel="stylesheet" href="css/arrive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'partials/parametres.php'; ?>

    <!-- En-tête principal -->
    <div class="header">
        <div class="header-content">
            <div class="header-title-row">
                <h1 class="brand-title">
                    📬 <span class="brand-open">Open</span><span class="brand-gesti">GestiCourrier</span> <span class="version-badge">V1.3</span>
                </h1>
                <a href="./version.txt" target="_blank" class="version-link"><i class="fa-solid fa-code-branch"></i> Note de version</a>
            </div>
            <div class="header-subtitle">
                <span class="ged-badge">
                    <span class="highlight-letter">G</span>estion 
                    <span class="highlight-letter">É</span>lectronique de 
                    <span class="highlight-letter">D</span>ocuments
                </span>
            </div>
        </div>
        <img src="<?php echo $urllogiciel; ?>img/logo-conques.jpg" alt="Logo" class="logo">
    </div>

    <!-- Barre de navigation d'attente connexion -->
    <div class="navbar" style="justify-content: center;">
        <span style="color: #ffffff; font-weight: 600; font-size: 0.95rem;">
            <i class="fas fa-lock"></i> Veuillez vous identifier pour accéder au système de Gestion Électronique de Documents
        </span>
    </div>

    <div class="main-container" style="max-width: 580px; margin-top: 40px;">
        <!-- Barre d'action et Titre (Style Courrier Entrant - Jaune) -->
        <div class="page-action-bar" style="border-left: 5px solid #f59e0b;">
            <div class="page-title-badge" style="color: #d97706;">
                <i class="fas fa-user-lock"></i> Espace de Connexion
            </div>
        </div>

        <!-- Formulaire Carte de Section (Style Courrier Entrant - Jaune) -->
        <div class="form-container">
            <form id="login-form" method="post" action="login_handler.php">
                <div class="form-section-card" style="border-top: 4px solid #f59e0b; padding: 30px;">
                    <div class="form-section-header">
                        <h2 style="color: #d97706;"><i class="fas fa-key"></i> Identification utilisateur</h2>
                    </div>

                    <div class="error" id="error-message" style="display: none; background: #fef2f2; color: #dc2626; padding: 12px; border-radius: 8px; font-weight: 600; margin-bottom: 16px; border: 1px solid #fecaca;">
                        <i class="fas fa-exclamation-triangle"></i> <span>Veuillez remplir tous les champs.</span>
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="username"><i class="fas fa-user" style="color: #d97706;"></i> Nom d'utilisateur :</label>
                        <input type="text" id="username" name="username" placeholder="Saisir votre nom d'utilisateur..." required autofocus>
                    </div>

                    <div class="form-group" style="margin-bottom: 12px;">
                        <label for="password"><i class="fas fa-lock" style="color: #d97706;"></i> Mot de passe :</label>
                        <input type="password" id="password" name="password" placeholder="Saisir votre mot de passe..." required>
                    </div>

                    <div style="text-align: right; margin-bottom: 24px;">
                        <a href="<?php echo $urllogiciel; ?>admin/reset_password_request.php" style="color: #d97706; font-size: 0.9rem; font-weight: 600; text-decoration: underline;">
                            Mot de passe oublié ?
                        </a>
                    </div>

                    <button type="submit" class="btn-submit-main" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;">
                        <i class="fas fa-sign-in-alt"></i> Se connecter
                    </button>
                </div>
            </form>
        </div>

        <div style="text-align: center; margin-top: 20px; color: #64748b; font-size: 0.85rem;">
            Concepteur - développeur : <strong>Ronan BOZOC</strong>
        </div>
    </div>

    <script>
        document.getElementById('login-form').addEventListener('submit', function(event) {
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;

            if (!username || !password) {
                event.preventDefault();
                const errBox = document.getElementById('error-message');
                errBox.querySelector('span').textContent = 'Veuillez remplir tous les champs.';
                errBox.style.display = 'block';
            }
        });
    </script>
</body>
</html>
