<?php
require_once '../config/connexion.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'] ?? '';
    $duree = (int) ($_POST['duree'] ?? 0);
    $prix = (float) ($_POST['prix'] ?? 0);
    $promo = $_POST['promo'] !== '' ? (float) $_POST['promo'] : null;
    // On retire le champ type du formulaire et on met une valeur par défaut
    $type = 'Standard';

    if ($nom && $duree && $prix) {
        $stmt = $pdo->prepare("INSERT INTO abonnements (nom, duree, prix, promo, type) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nom, $duree, $prix, $promo, $type]);
        header('Location: abonnements.php');
        exit;
    } else {
        $message = "Veuillez remplir tous les champs requis.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un abonnement | GO FIT</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --orange: #f15a24;
            --orange-hover: #ff7c47;
            --black: #111;
            --dark-gray: #1a1a1a;
            --light-gray: #2a2a2a;
            --white: #fff;
            --success: #4CAF50;
            --error: #f44336;
            --transition: all 0.3s ease;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--black);
            color: var(--white);
            min-height: 100vh;
            position: relative;
        }

        /* Header fixe seulement sur mobile */
        .header-container {
            position: sticky;
            top: 0;
            z-index: 1000;
            background-color: var(--black);
            padding: 15px 20px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.4);
        }

        header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            position: relative;
            max-width: 1400px;
            margin: 0 auto;
        }

        .logo-title {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        header img {
            height: 60px;
            border-radius: 8px;
        }

        header h1 {
            font-size: 1.8rem;
            color: var(--orange);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Menu Hamburger */
        .menu-toggle {
            display: none;
            flex-direction: column;
            justify-content: space-around;
            width: 40px;
            height: 34px;
            background: transparent;
            border: none;
            cursor: pointer;
            padding: 0;
            z-index: 100;
        }

        .menu-toggle:focus {
            outline: none;
        }

        .menu-toggle span {
            width: 100%;
            height: 4px;
            background-color: var(--orange);
            border-radius: 2px;
            transition: var(--transition);
            transform-origin: center;
        }

        .menu-toggle.active span:nth-child(1) {
            transform: translateY(12px) rotate(45deg);
        }

        .menu-toggle.active span:nth-child(2) {
            opacity: 0;
        }

        .menu-toggle.active span:nth-child(3) {
            transform: translateY(-12px) rotate(-45deg);
        }

        /* Navigation Mobile */
        .mobile-nav {
            position: fixed;
            top: 0;
            right: -300px;
            width: 280px;
            height: 100vh;
            background-color: var(--dark-gray);
            padding: 100px 25px 25px;
            z-index: 99;
            transition: var(--transition);
            box-shadow: -5px 0 15px rgba(0, 0, 0, 0.5);
            overflow-y: auto;
        }

        .mobile-nav.active {
            right: 0;
        }

        .mobile-nav h2 {
            color: var(--orange);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #333;
            font-size: 1.5rem;
        }

        .mobile-nav .btn-container {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .mobile-nav .button {
            width: 100%;
            text-align: center;
            padding: 15px;
            font-size: 1.1rem;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid transparent;
            color: var(--white);
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .mobile-nav .button:hover {
            background: var(--orange);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(241, 90, 36, 0.3);
        }

        .mobile-nav .button:active {
            transform: translateY(0);
        }

        .mobile-nav .button i {
            font-size: 1.2rem;
            color: var(--orange);
            transition: color 0.3s ease;
        }

        .mobile-nav .button:hover i {
            color: var(--white);
        }

        /* Contenu principal */
        main {
            position: relative;
            z-index: 10;
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .page-title {
            color: var(--orange);
            margin-bottom: 25px;
            text-align: center;
            font-size: 2.2rem;
            position: relative;
            padding-bottom: 15px;
        }

        .page-title::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 120px;
            height: 3px;
            background: var(--orange);
            border-radius: 3px;
        }

        /* Formulaire */
        .form-container {
            background: var(--dark-gray);
            border-radius: 12px;
            padding: 30px;
            max-width: 700px;
            margin: 0 auto;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 15px;
        }

        .form-col {
            flex: 1;
            min-width: 250px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #ddd;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .required::after {
            content: " *";
            color: var(--error);
        }

        .form-control {
            width: 100%;
            padding: 14px;
            background-color: #2a2a2a;
            border: 2px solid #333;
            border-radius: 8px;
            color: var(--white);
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--orange);
        }

        .form-info {
            font-size: 0.9rem;
            color: #aaa;
            margin-top: 5px;
            display: block;
        }

        .error-message {
            color: var(--error);
            margin-top: 8px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 14px 25px;
            text-decoration: none;
            font-weight: 600;
            border-radius: 8px;
            transition: var(--transition);
            cursor: pointer;
            border: none;
            font-size: 1rem;
        }

        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(241, 90, 36, 0.3);
        }

        .button:active {
            transform: translateY(0);
        }

        .button.submit {
            background-color: var(--orange);
            color: white;
        }

        .button.submit:hover {
            background-color: var(--orange-hover);
        }

        .button.cancel {
            background-color: #444;
            color: var(--white);
        }

        .button.cancel:hover {
            background-color: #666;
        }

        .form-icon {
            color: var(--orange);
            font-size: 1.2rem;
        }

        /* Overlay pour menu mobile */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            z-index: 98;
            opacity: 0;
            visibility: hidden;
            transition: var(--transition);
        }

        .overlay.active {
            opacity: 1;
            visibility: visible;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .menu-toggle {
                display: flex;
            }
            
            header img {
                height: 50px;
            }
            
            header h1 {
                font-size: 1.5rem;
            }
            
            .page-title {
                font-size: 1.9rem;
            }
            
            .form-container {
                padding: 20px;
            }
        }

        @media (max-width: 576px) {
            .header-container {
                padding: 12px 15px;
            }
            
            .page-title {
                font-size: 1.7rem;
            }
            
            .button {
                padding: 12px 20px;
                width: 100%;
                justify-content: center;
            }
            
            .button-group {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Header fixe seulement pour le logo et le titre -->
    <div class="header-container">
        <header>
            <div class="logo-title">
                <img src="../media/logo.jpg" alt="GO FIT Logo">
                <h1>Ajouter un abonnement</h1>
            </div>
            <button class="menu-toggle" id="menuToggle">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </header>
    </div>

    <?php include __DIR__ . '/_nav_links.php'; ?>

    <!-- Navigation mobile -->
    <nav class="mobile-nav" id="mobileNav">
        <h2><i class="fas fa-bolt"></i> Actions rapides</h2>
        <div class="btn-container">
            <a href="clients.php" class="button"><i class="fas fa-users"></i> Liste des clients</a>
            <a href="admin_add_client.php" class="button"><i class="fas fa-user-plus"></i> Ajouter un client</a>
            <a href="paiements.php" class="button"><i class="fas fa-history"></i> Historique des paiements</a>
            <a href="ajouter_paiement.php" class="button"><i class="fas fa-money-bill-wave"></i> Ajouter un paiement</a>
            <a href="abonnements.php" class="button"><i class="fas fa-id-card"></i> Gestion des abonnements</a>
            <a href="admin_presences.php" class="button"><i class="fas fa-clipboard-check"></i> Carnet de présence</a>
            <a href="../index.php" class="button"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a>
        </div>
    </nav>

    <div class="overlay" id="overlay"></div>

    <main>
        <h2 class="page-title">Créer un nouvel abonnement</h2>
        
        <div class="form-container">
            <?php if ($message): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?= $message ?>
                </div>
            <?php endif; ?>
            
            <form method="post">
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="required" for="nom">
                                <i class="fas fa-tag form-icon"></i> Nom de l'abonnement
                            </label>
                            <input type="text" id="nom" name="nom" class="form-control" placeholder="Ex: Abonnement Premium" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="required" for="duree">
                                <i class="fas fa-calendar-alt form-icon"></i> Durée (jours)
                            </label>
                            <input type="number" id="duree" name="duree" min="1" max="365" class="form-control" placeholder="Ex: 30" required>
                            <span class="form-info">Durée de validité en jours</span>
                        </div>
                    </div>
                    
                    <div class="form-col">
                        <div class="form-group">
                            <label class="required" for="prix">
                                <i class="fas fa-money-bill-wave form-icon"></i> Prix (FCFA)
                            </label>
                            <input type="number" id="prix" name="prix" step="0.01" min="0" class="form-control" placeholder="Ex: 25000" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="promo">
                                <i class="fas fa-percent form-icon"></i> Promotion (FCFA)
                            </label>
                            <input type="number" id="promo" name="promo" step="0.01" min="0" class="form-control" placeholder="Ex: 5000">
                            <span class="form-info">Montant de réduction (optionnel)</span>
                        </div>
                    </div>
                </div>
                
                
                
                <div class="button-group">
                    <button type="submit" class="button submit">
                        <i class="fas fa-plus-circle"></i> Ajouter l'abonnement
                    </button>
                    <a href="abonnements.php" class="button cancel">
                        <i class="fas fa-times"></i> Annuler
                    </a>
                </div>
            </form>
        </div>
    </main>

    <script>
        // Gestion du menu hamburger
        const menuToggle = document.getElementById('menuToggle');
        const mobileNav = document.getElementById('mobileNav');
        const overlay = document.getElementById('overlay');
        
        menuToggle.addEventListener('click', function() {
            this.classList.toggle('active');
            mobileNav.classList.toggle('active');
            overlay.classList.toggle('active');
            
            // Empêcher le défilement de la page lorsque le menu est ouvert
            document.body.classList.toggle('no-scroll');
        });
        
        // Fermer le menu en cliquant sur l'overlay
        overlay.addEventListener('click', function() {
            menuToggle.classList.remove('active');
            mobileNav.classList.remove('active');
            this.classList.remove('active');
            document.body.classList.remove('no-scroll');
        });
        
        // Fermer le menu en cliquant sur un lien
        document.querySelectorAll('.mobile-nav .button').forEach(link => {
            link.addEventListener('click', () => {
                menuToggle.classList.remove('active');
                mobileNav.classList.remove('active');
                overlay.classList.remove('active');
                document.body.classList.remove('no-scroll');
            });
        });
        
        // Focus sur le premier champ
        document.addEventListener('DOMContentLoaded', function() {
            if (document.getElementById('nom')) {
                document.getElementById('nom').focus();
            }
        });
    </script>
</body>
</html>