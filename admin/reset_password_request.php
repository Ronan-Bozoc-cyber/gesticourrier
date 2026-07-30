<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialiser le mot de passe - OpenGestiCourrier V1.3</title>
    <link rel="stylesheet" href="../css/style_general.css">
    <link rel="stylesheet" href="../css/arrive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include '../partials/parametres.php'; ?>

    <!-- En-tête principal -->
    <div class="header">
        <div class="header-content">
            <div class="header-title-row">
                <h1 class="brand-title">
                    📬 <span class="brand-open">Open</span><span class="brand-gesti">GestiCourrier</span> <span class="version-badge">V1.3</span>
                </h1>
                <a href="../version.txt" target="_blank" class="version-link"><i class="fa-solid fa-code-branch"></i> Note de version</a>
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

    <!-- Barre de navigation d'attente -->
    <div class="navbar" style="justify-content: center;">
        <span style="color: #ffffff; font-weight: 600; font-size: 0.95rem;">
            <i class="fas fa-key"></i> Procédure de réinitialisation du mot de passe
        </span>
    </div>

    <div class="main-container" style="max-width: 580px; margin-top: 40px;">
        <!-- Barre d'action et Titre (Style Courrier Entrant - Jaune) -->
        <div class="page-action-bar" style="border-left: 5px solid #f59e0b;">
            <div class="page-title-badge" style="color: #d97706;">
                <i class="fas fa-unlock-alt"></i> Mot de passe oublié
            </div>
        </div>

        <!-- Formulaire Carte de Section -->
        <div class="form-container">
            <form action="process_reset_request.php" method="post">
                <div class="form-section-card" style="border-top: 4px solid #f59e0b; padding: 30px;">
                    <div class="form-section-header">
                        <h2 style="color: #d97706;"><i class="fas fa-envelope"></i> Saisie de l'adresse e-mail</h2>
                    </div>

                    <div class="form-group" style="margin-bottom: 24px;">
                        <label for="email"><i class="fas fa-at" style="color: #d97706;"></i> Adresse e-mail du compte :</label>
                        <input type="email" id="email" name="email" placeholder="Saisir votre adresse e-mail..." required autofocus>
                    </div>

                    <button type="submit" class="btn-submit-main" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;">
                        <i class="fas fa-paper-plane"></i> Envoyer le lien de réinitialisation
                    </button>

                    <div style="text-align: center; margin-top: 20px;">
                        <a href="../login.php" style="color: #d97706; font-size: 0.9rem; font-weight: 600; text-decoration: underline;">
                            <i class="fas fa-arrow-left"></i> Retour à la page de connexion
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <div style="text-align: center; margin-top: 20px; color: #64748b; font-size: 0.85rem;">
            Concepteur - développeur : <strong>Ronan BOZOC</strong>
        </div>
    </div>
</body>
</html>
