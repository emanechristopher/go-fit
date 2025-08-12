<?php
require_once __DIR__ . '/../config/connexion.php';

if (!isset($_GET['id'])) {
    die('ID client manquant.');
}

$id = (int) $_GET['id'];
$stmt = $pdo->prepare("SELECT u.nom, u.prenom, u.email, u.age, c.sexe, c.abonnement_id, c.date_debut_abonnement, c.date_fin_abonnement
                       FROM users u
                       JOIN clients c ON u.id = c.id
                       WHERE u.id = ?");
$stmt->execute([$id]);
$client = $stmt->fetch();

if (!$client) {
    die('Client introuvable.');
}

// Liste des abonnements
$abonnements = $pdo->query("SELECT id, nom FROM abonnements")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier le client | GO FIT</title>
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
            border-radius: 8px;
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
            background: var(--light-gray);
            color: var(--white);
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            transition: var(--transition);
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

        .mobile-nav .button i {
            font-size: 1.2rem;
            color: var(--orange);
            transition: var(--transition);
        }

        .mobile-nav .button:hover i {
            color: var(--white);
        }

        /* Contenu principal */
        main {
            position: relative;
            z-index: 10;
            max-width: 800px;
            margin: 0 auto;
        }

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
            position: relative;
            padding-bottom: 15px;
        }

        .form-header h2::after {
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
            margin-bottom: 10px;
            font-weight: 600;
            color: var(--orange);
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 14px 16px;
            background-color: var(--light-gray);
            border: 1px solid #333;
            border-radius: 8px;
            color: var(--white);
            font-size: 1.05rem;
            transition: var(--transition);
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--orange);
            box-shadow: 0 0 0 4px rgba(241, 90, 36, 0.25);
        }

        .submit-btn {
            display: block;
            width: 100%;
            max-width: 300px;
            margin: 30px auto 0;
            padding: 16px;
            background-color: var(--orange);
            color: var(--white);
            font-weight: bold;
            font-size: 1.1rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: var(--transition);
            text-transform: uppercase;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
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
            
            .form-grid {
                grid-template-columns: 1fr;
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
                font-size: 1.6rem;
            }
            
            .form-header h2 {
                font-size: 1.6rem;
            }
        }
    </style>
</head>
<body>

<header>
    <div class="logo-title">
        <img src="../media/logo.jpg" alt="GO FIT Logo">
        <h1>Modifier client</h1>
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
    <div class="form-container">
        <div class="form-header">
            <h2>Modifier le client</h2>
            <p>Mettez à jour les informations du client</p>
        </div>
        
        <form action="../actions/update_client.php" method="POST">
            <input type="hidden" name="id" value="<?= $id ?>">
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="nom"><i class="fas fa-user"></i> Nom :</label>
                    <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($client['nom']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="prenom"><i class="fas fa-user"></i> Prénom :</label>
                    <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($client['prenom']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email :</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($client['email']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="age"><i class="fas fa-birthday-cake"></i> Âge :</label>
                    <input type="number" id="age" name="age" min="0" max="150" value="<?= htmlspecialchars($client['age']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="sexe"><i class="fas fa-venus-mars"></i> Sexe :</label>
                    <select id="sexe" name="sexe" required>
                        <option value="Homme" <?= $client['sexe'] === 'Homme' ? 'selected' : '' ?>>Homme</option>
                        <option value="Femme" <?= $client['sexe'] === 'Femme' ? 'selected' : '' ?>>Femme</option>
                        <option value="Autre" <?= $client['sexe'] === 'Autre' ? 'selected' : '' ?>>Autre</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="abonnement_id"><i class="fas fa-id-card"></i> Abonnement :</label>
                    <select id="abonnement_id" name="abonnement_id">
                        <option value="">-- Aucun --</option>
                        <?php foreach ($abonnements as $abonnement): ?>
                            <option value="<?= $abonnement['id'] ?>" <?= $client['abonnement_id'] == $abonnement['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($abonnement['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="date_debut_abonnement"><i class="fas fa-calendar-start"></i> Date début abonnement :</label>
                    <input type="date" id="date_debut_abonnement" name="date_debut_abonnement" value="<?= $client['date_debut_abonnement'] ?>">
                </div>
                
                <div class="form-group">
                    <label for="date_fin_abonnement"><i class="fas fa-calendar-end"></i> Date fin abonnement :</label>
                    <input type="date" id="date_fin_abonnement" name="date_fin_abonnement" value="<?= $client['date_fin_abonnement'] ?>">
                </div>
            </div>
            
            <button type="submit" class="submit-btn"><i class="fas fa-save"></i> Enregistrer les modifications</button>
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