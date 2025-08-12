<?php
require_once '../config/connexion.php';

$month = $_GET['month'] ?? ''; // format attendu YYYY-MM

if ($month && preg_match('/^\d{4}-\d{2}$/', $month)) {
    // Filtrer par mois
    $sql = "SELECT p.*, u.nom, u.prenom, c.id AS client_id 
            FROM paiements p
            JOIN clients c ON p.client_id = c.id
            JOIN users u ON c.id = u.id
            WHERE DATE_FORMAT(p.date_paiement, '%Y-%m') = :month
            ORDER BY p.date_paiement DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['month' => $month]);
} else {
    // Pas de filtre, afficher les 10 derniers paiements
    $sql = "SELECT p.*, u.nom, u.prenom, c.id AS client_id 
            FROM paiements p
            JOIN clients c ON p.client_id = c.id
            JOIN users u ON c.id = u.id
            ORDER BY p.date_paiement DESC
            LIMIT 10";
    $stmt = $pdo->query($sql);
}

$paiements = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Paiements | GO FIT</title>
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
            margin-bottom: 30px;
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
            background: #2a2a2a;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
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

        /* Formulaire de recherche */
        .search-form {
            background: var(--dark-gray);
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            align-items: flex-end;
        }

        .form-group {
            flex: 1;
            min-width: 250px;
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

        .form-group input {
            width: 100%;
            padding: 14px 16px;
            background-color: var(--light-gray);
            border: 1px solid #333;
            border-radius: 8px;
            color: var(--white);
            font-size: 1.05rem;
            transition: var(--transition);
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--orange);
            box-shadow: 0 0 0 4px rgba(241, 90, 36, 0.25);
        }

        .actions-row {
            display: flex;
            gap: 20px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .button {
            background-color: var(--orange);
            color: var(--white);
            padding: 14px 24px;
            text-decoration: none;
            font-weight: bold;
            border-radius: 8px;
            transition: var(--transition);
            text-align: center;
            border: none;
            cursor: pointer;
            font-size: 1.1rem;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .button:hover {
            background-color: var(--orange-hover);
            transform: translateY(-3px);
            box-shadow: 0 6px 18px rgba(241, 90, 36, 0.35);
        }

        .button.reset {
            background-color: #444;
        }

        .button.reset:hover {
            background-color: #666;
        }

        .button.add {
            background-color: #28a745;
        }

        .button.add:hover {
            background-color: #218838;
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

        .client-name {
            font-weight: 600;
        }

        .amount {
            font-weight: bold;
            color: #4CAF50;
        }

        .actions-cell {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .action-btn {
            padding: 8px 14px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.95rem;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }

        .action-btn.edit {
            background-color: #17a2b8;
            color: white;
        }

        .action-btn.edit:hover {
            background-color: #138496;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(23, 162, 184, 0.3);
        }

        .action-btn.delete {
            background-color: #dc3545;
            color: white;
        }

        .action-btn.delete:hover {
            background-color: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(220, 53, 69, 0.3);
        }

        .action-btn.history {
            background-color: #6f42c1;
            color: white;
        }

        .action-btn.history:hover {
            background-color: #5a32a3;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(111, 66, 193, 0.3);
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

        /* Styles spécifiques pour laptop */
        @media (min-width: 992px) {
            table {
                width: 100%;
                table-layout: fixed;
            }
            
            th, td {
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            
            .col-client { width: 25%; }
            .col-amount { width: 15%; }
            .col-method { width: 15%; }
            .col-date { width: 20%; }
            .col-actions { width: 25%; }
        }

        /* Responsive général */
        @media (max-width: 1199px) {
            th, td {
                padding: 12px 14px;
                font-size: 0.95rem;
            }
            
            .action-btn {
                padding: 7px 12px;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 992px) {
            .actions-row {
                justify-content: center;
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
                font-size: 1.6rem;
            }
            
            .page-title {
                font-size: 1.9rem;
            }
            
            .mobile-nav .button {
                padding: 16px;
                font-size: 1.15rem;
            }
            
            th, td {
                padding: 10px 12px;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 576px) {
            .header-container {
                padding: 12px 15px;
            }
            
            .button {
                width: 100%;
                padding: 16px;
            }
            
            .actions-row {
                flex-direction: column;
                gap: 12px;
            }
            
            .search-form {
                padding: 20px;
            }
            
            .page-title {
                font-size: 1.7rem;
            }
            
            .actions-cell {
                flex-direction: column;
                gap: 8px;
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
                <h1>Paiements</h1>
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
        <h2 class="page-title">Gestion des paiements</h2>
        
        <div class="search-form">
            <form method="get" action="paiements.php">
                <div class="form-group">
                    <label for="month"><i class="fas fa-calendar-alt"></i> Filtrer par mois</label>
                    <input type="month" id="month" name="month" value="<?= htmlspecialchars($month) ?>">
                </div>
                
                <button type="submit" class="button"><i class="fas fa-search"></i> Rechercher</button>
            </form>
        </div>
        
        <div class="actions-row">
            <a href="paiements.php" class="button reset"><i class="fas fa-sync"></i> Réinitialiser</a>
            <a href="ajouter_paiement.php" class="button add"><i class="fas fa-plus-circle"></i> Ajouter un paiement</a>
        </div>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th class="col-client"><i class="fas fa-user"></i> Client</th>
                        <th class="col-amount"><i class="fas fa-money-bill-wave"></i> Montant</th>
                        <th class="col-method"><i class="fas fa-credit-card"></i> Méthode</th>
                        <th class="col-date"><i class="fas fa-calendar-day"></i> Date</th>
                        <th class="col-actions"><i class="fas fa-cogs"></i> Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($paiements) === 0): ?>
                        <tr>
                            <td colspan="5" class="no-results">
                                <i class="fas fa-search" style="font-size: 2rem; margin-bottom: 15px;"></i><br>
                                Aucun paiement trouvé pour ce mois
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($paiements as $paiement): ?>
                            <tr>
                                <td class="col-client">
                                    <span class="client-name"><?= htmlspecialchars($paiement['nom'] . ' ' . $paiement['prenom']) ?></span>
                                </td>
                                <td class="col-amount">
                                    <span class="amount"><?= number_format($paiement['montant'], 2) ?> FCFA</span>
                                </td>
                                <td class="col-method"><?= htmlspecialchars($paiement['type']) ?></td>
                                <td class="col-date"><?= date('d/m/Y à H:i', strtotime($paiement['date_paiement'])) ?></td>
                                <td class="col-actions">
                                    <div class="actions-cell">
                                        <a href="modifier_paiement.php?id=<?= $paiement['id'] ?>" class="action-btn edit">
                                            <i class="fas fa-edit"></i> Modifier
                                        </a>
                                        <a href="supprimer_paiement.php?id=<?= $paiement['id'] ?>" 
                                           class="action-btn delete" 
                                           onclick="return confirm('Confirmer la suppression de ce paiement ?');">
                                            <i class="fas fa-trash-alt"></i> Supprimer
                                        </a>
                                        <a href="historique_paiements.php?client_id=<?= $paiement['client_id'] ?>" class="action-btn history">
                                            <i class="fas fa-history"></i> Historique
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
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
        
        // Focus sur le champ de recherche
        document.addEventListener('DOMContentLoaded', function() {
            if (document.getElementById('month')) {
                document.getElementById('month').focus();
            }
        });
    </script>
</body>
</html>