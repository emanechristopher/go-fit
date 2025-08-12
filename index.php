<?php
require_once 'config/connexion.php';

// Statistiques
$totalClients = $pdo->query("SELECT COUNT(*) FROM clients")->fetchColumn();
$totalPaiements = $pdo->query("SELECT COUNT(*) FROM paiements")->fetchColumn();
$totalCA = $pdo->query("SELECT SUM(montant) FROM paiements")->fetchColumn();
$activeAbos = $pdo->prepare("SELECT COUNT(*) FROM clients WHERE date_fin_abonnement > CURDATE()");
$activeAbos->execute();
$activeAbosCount = $activeAbos->fetchColumn();
$currentMonth = date('Y-m');
$paymentsThisMonth = $pdo->prepare("SELECT COUNT(*) FROM paiements WHERE DATE_FORMAT(date_paiement, '%Y-%m') = :month");
$paymentsThisMonth->execute(['month' => $currentMonth]);
$paymentsThisMonthCount = $paymentsThisMonth->fetchColumn();
$caThisMonth = $pdo->prepare("SELECT SUM(montant) FROM paiements WHERE DATE_FORMAT(date_paiement, '%Y-%m') = :month");
$caThisMonth->execute(['month' => $currentMonth]);
$caThisMonthSum = $caThisMonth->fetchColumn();

// Formater la date du mois en cours
setlocale(LC_TIME, 'fr_FR.UTF-8');
$monthName = strftime('%B %Y', strtotime($currentMonth));
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Tableau de bord | GO FIT</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/jpeg" href="media/logo.jpg">
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
            --info: #2196F3;
            --warning: #FF9800;
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

        /* Cartes de statistiques */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .card {
            background: var(--dark-gray);
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            border-top: 4px solid var(--orange);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(241, 90, 36, 0.3);
        }

        .card h3 {
            font-size: 1.2rem;
            margin-bottom: 15px;
            color: #ddd;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card h3 i {
            color: var(--orange);
            font-size: 1.3rem;
        }

        .card .value {
            font-size: 2.5rem;
            font-weight: bold;
            text-align: center;
            margin: 15px 0;
            color: var(--orange);
        }

        .card .description {
            text-align: center;
            color: #aaa;
            font-size: 0.9rem;
        }

        /* Section d'actions rapides */
        .actions {
            background: var(--dark-gray);
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
            margin-bottom: 40px;
        }

        .actions h2 {
            color: var(--orange);
            text-align: center;
            margin-bottom: 25px;
            font-size: 1.8rem;
            position: relative;
            padding-bottom: 15px;
        }

        .actions h2::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 2px;
            background: var(--orange);
            border-radius: 2px;
        }

        .actions .btn-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .actions .button {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 15px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            text-decoration: none;
            color: var(--white);
            text-align: center;
            transition: var(--transition);
            border: 1px solid #333;
        }

        .actions .button:hover {
            background: var(--orange);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(241, 90, 36, 0.3);
        }

        .actions .button i {
            font-size: 2.5rem;
            color: var(--orange);
            transition: color 0.3s ease;
        }

        .actions .button:hover i {
            color: var(--white);
        }

        .actions .button span {
            font-weight: 600;
            font-size: 1.1rem;
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
        @media (max-width: 1200px) {
            .stats-container {
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            }
        }

        @media (max-width: 992px) {
            .card .value {
                font-size: 2.2rem;
            }
        }

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
            
            .stats-container {
                grid-template-columns: 1fr;
            }
            
            .actions .btn-container {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            }
        }

        @media (max-width: 576px) {
            .header-container {
                padding: 12px 15px;
            }
            
            .page-title {
                font-size: 1.7rem;
            }
            
            .card {
                padding: 20px;
            }
            
            .card .value {
                font-size: 2rem;
            }
            
            .actions .btn-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Header fixe seulement pour le logo et le titre -->
    <div class="header-container">
        <header>
            <div class="logo-title">
                <img src="media/logo.jpg" alt="GO FIT Logo">
                <h1>Tableau de bord</h1>
            </div>
            <button class="menu-toggle" id="menuToggle">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </header>
    </div>

    <?php include __DIR__ . '/pages/_nav_links.php'; ?>

    <!-- Navigation mobile -->
    <nav class="mobile-nav" id="mobileNav">
        <h2><i class="fas fa-bolt"></i> Actions rapides</h2>
        <div class="btn-container">
                         <a href="pages/clients.php" class="button"><i class="fas fa-users"></i> Liste des clients</a>
             <a href="pages/admin_add_client.php" class="button"><i class="fas fa-user-plus"></i> Ajouter un client</a>
             <a href="pages/paiements.php" class="button"><i class="fas fa-history"></i> Historique des paiements</a>
             <a href="pages/ajouter_paiement.php" class="button"><i class="fas fa-money-bill-wave"></i> Ajouter un paiement</a>
             <a href="pages/abonnements.php" class="button"><i class="fas fa-id-card"></i> Gestion des abonnements</a>
             <a href="pages/admin_presences.php" class="button"><i class="fas fa-clipboard-check"></i> Carnet de présence</a>
            <a href="index.php" class="button"><i class="fas fa-sync-alt"></i> Rafraîchir</a>
        </div>
    </nav>

    <div class="overlay" id="overlay"></div>

    <main>
        <h2 class="page-title"><i class="fas fa-tachometer-alt"></i> Tableau de bord</h2>
        
        <div class="stats-container">
            <div class="card">
                <h3><i class="fas fa-users"></i> Total clients</h3>
                <div class="value"><?= $totalClients ?></div>
                <div class="description">Nombre total de clients inscrits</div>
            </div>
            
            <div class="card">
                <h3><i class="fas fa-money-bill-wave"></i> Total paiements</h3>
                <div class="value"><?= $totalPaiements ?></div>
                <div class="description">Transactions effectuées</div>
            </div>
            
            <div class="card">
                <h3><i class="fas fa-chart-line"></i> Chiffre d'affaires total</h3>
                <div class="value"><?= number_format($totalCA ?? 0, 0, ',', ' ') ?> FCFA</div>
                <div class="description">Revenus générés</div>
            </div>
            
            <div class="card">
                <h3><i class="fas fa-id-card"></i> Abonnements actifs</h3>
                <div class="value"><?= $activeAbosCount ?></div>
                <div class="description">Actuellement valides</div>
            </div>
            
            <div class="card">
                <h3><i class="fas fa-calendar-alt"></i> Paiements ce mois</h3>
                <div class="value"><?= $paymentsThisMonthCount ?></div>
                <div class="description">(<?= $monthName ?>)</div>
            </div>
            
            <div class="card">
                <h3><i class="fas fa-wallet"></i> CA ce mois</h3>
                <div class="value"><?= number_format($caThisMonthSum ?? 0, 0, ',', ' ') ?> FCFA</div>
                <div class="description">(<?= $monthName ?>)</div>
            </div>
        </div>

        <section class="actions">
            <h2><i class="fas fa-bolt"></i> Actions rapides</h2>
            <div class="btn-container">
                                 <a href="pages/clients.php" class="button">
                     <i class="fas fa-users"></i>
                     <span>Liste des clients</span>
                 </a>
                 
                 <a href="pages/admin_add_client.php" class="button">
                     <i class="fas fa-user-plus"></i>
                     <span>Ajouter un client</span>
                 </a>
                 
                 <a href="pages/paiements.php" class="button">
                     <i class="fas fa-history"></i>
                     <span>Historique des paiements</span>
                 </a>
                 
                 <a href="pages/ajouter_paiement.php" class="button">
                     <i class="fas fa-money-bill-wave"></i>
                     <span>Ajouter un paiement</span>
                 </a>
                 
                 <a href="pages/abonnements.php" class="button">
                     <i class="fas fa-id-card"></i>
                     <span>Gestion des abonnements</span>
                 </a>
                
                <a href="index.php" class="button">
                    <i class="fas fa-sync-alt"></i>
                    <span>Rafraîchir</span>
                </a>
            </div>
        </section>
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
    </script>
</body>
</html>