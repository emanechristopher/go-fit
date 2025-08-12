<?php
require_once '../config/connexion.php';

// Récupérer les abonnements pour le select
$abonnements = $pdo->query("SELECT id, nom FROM abonnements ORDER BY nom")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un client - Admin | GO FIT</title>
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
            padding: 20px;
            min-height: 100vh;
            position: relative;
        }

        /* Header */
        header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 30px;
            position: relative;
            z-index: 100;
        }

        .logo-title {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        header img {
            height: 60px;
        }

        header h1 {
            font-size: 2rem;
            color: var(--orange);
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        /* Menu Hamburger */
        .menu-toggle {
            display: none;
            flex-direction: column;
            justify-content: space-around;
            width: 30px;
            height: 24px;
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
            width: 30px;
            height: 3px;
            background-color: var(--orange);
            border-radius: 2px;
            transition: var(--transition);
            transform-origin: center;
        }

        .menu-toggle.active span:nth-child(1) {
            transform: translateY(8px) rotate(45deg);
        }

        .menu-toggle.active span:nth-child(2) {
            opacity: 0;
        }

        .menu-toggle.active span:nth-child(3) {
            transform: translateY(-8px) rotate(-45deg);
        }

        /* Navigation Mobile */
        .mobile-nav {
            position: fixed;
            top: 0;
            right: -300px;
            width: 280px;
            height: 100vh;
            background-color: var(--dark-gray);
            padding: 100px 20px 20px;
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
            font-size: 1.5rem;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-align: center;
        }

        .mobile-nav .btn-container {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .mobile-nav .button {
            width: 100%;
            text-align: center;
            padding: 12px 20px;
            background-color: var(--light-gray);
            color: var(--white);
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            font-size: 1.1rem;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            border: 1px solid transparent;
        }

        .mobile-nav .button:hover {
            background-color: var(--orange);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(241, 90, 36, 0.2);
        }

        .mobile-nav .button:active {
            transform: translateY(0);
        }

        /* Contenu principal */
        main {
            position: relative;
            z-index: 10;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Formulaire */
        .form-container {
            background: var(--dark-gray);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3);
        }

        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .form-header h2 {
            color: var(--orange);
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .form-header p {
            color: #aaa;
            font-size: 1.1rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--orange);
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 14px;
            background-color: var(--light-gray);
            border: 1px solid #333;
            border-radius: 6px;
            color: var(--white);
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--orange);
            box-shadow: 0 0 0 3px rgba(241, 90, 36, 0.2);
        }

        .form-group input:hover,
        .form-group select:hover {
            border-color: #555;
        }

        .submit-btn {
            display: block;
            width: 100%;
            max-width: 300px;
            margin: 30px auto 0;
            padding: 15px;
            background-color: var(--orange);
            color: var(--white);
            font-weight: bold;
            font-size: 1.1rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: var(--transition);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .submit-btn:hover {
            background-color: var(--orange-hover);
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(241, 90, 36, 0.4);
        }

        /* Overlay pour menu mobile */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
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
            
            .form-container {
                padding: 20px;
            }
            
            .form-header h2 {
                font-size: 1.8rem;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 15px;
            }
            
            header img {
                height: 50px;
            }
            
            header h1 {
                font-size: 1.5rem;
            }
            
            .form-header h2 {
                font-size: 1.5rem;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<header>
    <div class="logo-title">
        <img src="../media/logo.jpg" alt="GO FIT Logo">
        <h1>Ajouter un client</h1>
    </div>
    <button class="menu-toggle" id="menuToggle">
        <span></span>
        <span></span>
        <span></span>
    </button>
</header>

<?php include __DIR__ . '/_nav_links.php'; ?>

<!-- Navigation mobile -->
<nav class="mobile-nav" id="mobileNav">
    <h2>Actions rapides</h2>
    <div class="btn-container">
        <a href="clients.php" class="button"><i class="fas fa-users"></i>Liste des clients</a>
        <a href="admin_add_client.php" class="button"><i class="fas fa-user-plus"></i>Ajouter un client</a>
        <a href="paiements.php" class="button"><i class="fas fa-history"></i>Historique des paiements</a>
        <a href="ajouter_paiement.php" class="button"><i class="fas fa-money-bill-wave"></i>Ajouter un paiement</a>
        <a href="abonnements.php" class="button"><i class="fas fa-id-card"></i>Gestion des abonnements</a>
        <a href="admin_presences.php" class="button"><i class="fas fa-clipboard-check"></i>Carnet de présence</a>
        <a href="../index.php" class="button"><i class="fas fa-tachometer-alt"></i>Tableau de bord</a>
    </div>
</nav>

<div class="overlay" id="overlay"></div>

<main>
    <div class="form-container">
        <div class="form-header">
            <h2>Nouveau client</h2>
            <p>Remplissez les informations du nouveau client</p>
        </div>
        
        <form action="../actions/admin_add_client.php" method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label for="nom">Nom :</label>
                    <input type="text" id="nom" name="nom" required>
                </div>
                
                <div class="form-group">
                    <label for="prenom">Prénom :</label>
                    <input type="text" id="prenom" name="prenom" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email :</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="telephone">Téléphone :</label>
                    <input type="text" id="telephone" name="telephone" required>
                </div>
                
                
                
                <div class="form-group">
                    <label for="sexe">Sexe :</label>
                    <select id="sexe" name="sexe" required>
                        <option value="">-- Sélectionnez --</option>
                        <option value="Homme">Homme</option>
                        <option value="Femme">Femme</option>
                        <option value="Autre">Autre</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="age">Âge :</label>
                    <input type="number" id="age" name="age" min="0" max="150" required>
                </div>
                
                <div class="form-group">
                    <label for="abonnement_id">Abonnement :</label>
                    <select id="abonnement_id" name="abonnement_id">
                        <option value="">-- Sélectionnez --</option>
                        <?php foreach ($abonnements as $abo): ?>
                            <option value="<?= $abo['id'] ?>"><?= htmlspecialchars($abo['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="date_debut_abonnement">Date début abonnement :</label>
                    <input type="date" id="date_debut_abonnement" name="date_debut_abonnement" required>
                </div>
                
                <div class="form-group">
                    <label for="date_fin_abonnement">Date fin abonnement :</label>
                    <input type="date" id="date_fin_abonnement" name="date_fin_abonnement" required>
                </div>
            </div>
            
            <button type="submit" class="submit-btn">Ajouter le client</button>
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
    
    // Focus sur le premier champ au chargement de la page
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('nom').focus();
    });
</script>

</body>
</html>