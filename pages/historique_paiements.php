<?php
require_once '../config/connexion.php';

// Vérifie si l'ID du client est fourni
if (!isset($_GET['client_id']) || !is_numeric($_GET['client_id'])) {
    die('Client non spécifié.');
}

$client_id = (int) $_GET['client_id'];

// Récupération des infos du client
$sqlClient = "SELECT u.nom, u.prenom 
              FROM clients c
              JOIN users u ON c.id = u.id
              WHERE c.id = :id";
$stmtClient = $pdo->prepare($sqlClient);
$stmtClient->execute(['id' => $client_id]);
$client = $stmtClient->fetch();

if (!$client) {
    die("Client introuvable.");
}

// Récupération de l'historique des paiements
$sqlPaiements = "SELECT * FROM paiements 
                 WHERE client_id = :client_id 
                 ORDER BY date_paiement DESC";
$stmt = $pdo->prepare($sqlPaiements);
$stmt->execute(['client_id' => $client_id]);
$paiements = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Historique des paiements | GO FIT</title>
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
            max-width: 1000px;
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

        .client-info {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: var(--dark-gray);
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .client-name {
            font-size: 1.8rem;
            color: var(--orange);
            margin-bottom: 10px;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background-color: #444;
            color: var(--white);
            padding: 12px 20px;
            text-decoration: none;
            font-weight: bold;
            border-radius: 8px;
            transition: var(--transition);
            margin-bottom: 25px;
        }

        .back-btn:hover {
            background-color: #666;
            transform: translateY(-2px);
        }

        /* Tableau des paiements */
        .table-container {
            overflow-x: auto;
            background: var(--dark-gray);
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 14px 16px;
            text-align: left;
            border-bottom: 1px solid #333;
        }

        th {
            color: var(--orange);
            font-weight: 600;
            font-size: 1.05rem;
            background-color: #2a2a2a;
        }

        tbody tr {
            transition: var(--transition);
        }

        tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }

        .amount {
            font-weight: bold;
            color: #4CAF50;
        }

        .no-results {
            text-align: center;
            padding: 40px;
            color: #aaa;
            font-style: italic;
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
            
            .client-name {
                font-size: 1.5rem;
            }
            
            .mobile-nav .button {
                padding: 16px;
                font-size: 1.15rem;
            }
            
            th, td {
                padding: 12px;
                font-size: 0.95rem;
            }
        }

        @media (max-width: 576px) {
            .header-container {
                padding: 12px 15px;
            }
            
            .back-btn {
                width: 100%;
                justify-content: center;
            }
            
            .client-info {
                padding: 15px;
            }
            
            .table-container {
                padding: 15px;
            }
            
            .page-title {
                font-size: 1.7rem;
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
                <h1>Historique</h1>
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
        <h2 class="page-title">Historique des paiements</h2>
        
        <div class="client-info">
            <div class="client-name"><?= htmlspecialchars($client['nom'] . ' ' . $client['prenom']) ?></div>
            <p>Consultez l'historique complet des paiements de ce client</p>
        </div>
        
        <a href="paiements.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Retour aux paiements
        </a>
        
        <div class="table-container">
            <?php if (empty($paiements)): ?>
                <div class="no-results">
                    <i class="fas fa-search" style="font-size: 2rem; margin-bottom: 15px;"></i><br>
                    Aucun paiement enregistré pour ce client
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th><i class="fas fa-money-bill-wave"></i> Montant</th>
                            <th><i class="fas fa-credit-card"></i> Méthode</th>
                            <th><i class="fas fa-calendar-day"></i> Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($paiements as $paiement): ?>
                            <tr>
                                <td class="amount"><?= number_format($paiement['montant'], 2) ?> FCFA</td>
                                <td><?= htmlspecialchars($paiement['type']) ?></td>
                                <td><?= date('d/m/Y à H:i', strtotime($paiement['date_paiement'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
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
    </script>
</body>
</html>