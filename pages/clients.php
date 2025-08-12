<?php
session_start();
require_once __DIR__ . '/../config/connexion.php';

// Récupérer les abonnements pour le filtre
$abonnements = $pdo->query("SELECT id, nom FROM abonnements ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les paramètres
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$abonnementFilter = isset($_GET['type_abonnement']) ? trim($_GET['type_abonnement']) : '';

$params = [];
$sql = "SELECT 
            u.id, u.nom, u.prenom, u.telephone, u.email, u.age,
            c.sexe, c.date_debut_abonnement, c.date_fin_abonnement,
            a.nom AS abonnement_nom, a.prix, a.promo
        FROM clients c
        JOIN users u ON c.id = u.id
        LEFT JOIN abonnements a ON c.abonnement_id = a.id
        WHERE 1=1";

if ($search !== '') {
    $sql .= " AND (u.nom LIKE :search_nom OR u.prenom LIKE :search_prenom OR u.email LIKE :search_email)";
    $params[':search_nom'] = "%$search%";
    $params[':search_prenom'] = "%$search%";
    $params[':search_email'] = "%$search%";
}

if ($abonnementFilter !== '') {
    $sql .= " AND a.id = :abonnement_id";
    $params[':abonnement_id'] = $abonnementFilter;
}

$sql .= " ORDER BY u.nom, u.prenom";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des clients | GO FIT</title>
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

        .current-month {
            text-align: center;
            margin-bottom: 25px;
            font-size: 1.2rem;
            color: #ddd;
            background: var(--dark-gray);
            padding: 10px;
            border-radius: 8px;
            max-width: 300px;
            margin: 0 auto 25px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }

        /* Formulaire de recherche */
        .search-form {
            background: var(--dark-gray);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
        }

        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            flex: 1;
            min-width: 250px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #ddd;
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

        .actions-row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: center;
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
        }

        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(241, 90, 36, 0.3);
        }

        .button:active {
            transform: translateY(0);
        }

        .button.add {
            background-color: var(--orange);
            color: white;
        }

        .button.add:hover {
            background-color: var(--orange-hover);
        }

        .button.reset {
            background-color: #444;
            color: var(--white);
        }

        .button.reset:hover {
            background-color: #666;
        }

        .button.search {
            background-color: #2e7d32;
            color: white;
            border: none;
            cursor: pointer;
            width: 100%;
            justify-content: center;
        }

        .button.search:hover {
            background-color: #388e3c;
        }

        /* Tableau des clients */
        .table-container {
            overflow-x: auto;
            background: var(--dark-gray);
            border-radius: 12px;
            padding: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
            margin-bottom: 40px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 780px;
            font-size: 0.92rem;
        }

        th, td {
            padding: 8px 10px;
            text-align: left;
            border-bottom: 1px solid #333;
            line-height: 1.2;
        }

        th {
            color: var(--orange);
            font-weight: 600;
            font-size: 0.95rem;
            background-color: #2a2a2a;
            position: sticky;
            top: 0;
        }

        tbody tr {
            transition: var(--transition);
        }

        tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }

        .price-old {
            text-decoration: line-through;
            color: #aaa;
            font-size: 0.85rem;
        }

        .price-new {
            font-weight: bold;
            color: #4CAF50;
            font-size: 1rem;
        }

        .actions-cell {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .action-btn {
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 0.85rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: var(--transition);
        }

        .action-btn.edit {
            background-color: rgba(33, 150, 243, 0.2);
            color: #2196F3;
            border: 1px solid rgba(33, 150, 243, 0.3);
        }

        .action-btn.edit:hover {
            background-color: rgba(33, 150, 243, 0.3);
        }

        .action-btn.delete {
            background-color: rgba(244, 67, 54, 0.2);
            color: #f44336;
            border: 1px solid rgba(244, 67, 54, 0.3);
        }

        .action-btn.delete:hover {
            background-color: rgba(244, 67, 54, 0.3);
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
        @media (max-width: 1200px) {
            th, td {
                padding: 12px 14px;
                font-size: 0.95rem;
            }
            
            .action-btn {
                padding: 7px 12px;
                font-size: 0.85rem;
            }
        }

        @media (max-width: 992px) {
            .form-row {
                flex-direction: column;
                gap: 15px;
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
            
            .search-form {
                padding: 20px;
            }
            
            .button {
                padding: 12px 20px;
            }
            
            .actions-cell {
                flex-direction: column;
                gap: 8px;
            }
        }

        @media (max-width: 576px) {
            .header-container {
                padding: 12px 15px;
            }
            
            .page-title {
                font-size: 1.7rem;
            }
            
            .current-month {
                font-size: 1rem;
            }
            
            .form-control {
                padding: 12px;
            }
            
            .actions-row {
                flex-direction: column;
            }
            
            .button {
                width: 100%;
                justify-content: center;
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
                <h1>Liste des clients</h1>
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
        <h2 class="page-title">Gestion des clients</h2>
        <div class="current-month">Mois en cours : <?= date('F Y') ?></div>
        
        <div class="search-form">
            <form method="get" action="clients.php">
                <div class="form-row">
                    <div class="form-group">
                        <label for="search"><i class="fas fa-search"></i> Rechercher un client</label>
                        <input type="text" id="search" name="search" class="form-control" placeholder="Nom, prénom ou email..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="type_abonnement"><i class="fas fa-filter"></i> Filtrer par abonnement</label>
                        <select id="type_abonnement" name="type_abonnement" class="form-control">
                            <option value="">-- Tous les abonnements --</option>
                            <?php foreach ($abonnements as $abo): ?>
                                <option value="<?= $abo['id'] ?>" <?= ($abo['id'] == $abonnementFilter) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($abo['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="submit" class="button search"><i class="fas fa-search"></i> Rechercher</button>
                    </div>
                </div>
                
                <div class="actions-row">
                    <a href="clients.php" class="button reset"><i class="fas fa-sync"></i> Réinitialiser les filtres</a>
                    <a href="admin_add_client.php" class="button add"><i class="fas fa-user-plus"></i> Ajouter un client</a>
                </div>
            </form>
        </div>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th><i class="fas fa-user"></i> Nom</th>
                        <th><i class="fas fa-user"></i> Prénom</th>
                        <th><i class="fas fa-envelope"></i> Email</th>
                        <th><i class="fas fa-venus-mars"></i> Sexe</th>
                        <th><i class="fas fa-phone"></i> Téléphone</th>
                        <th><i class="fas fa-birthday-cake"></i> Âge</th>
                        <th><i class="fas fa-calendar-start"></i> Début abonnement</th>
                        <th><i class="fas fa-calendar-end"></i> Fin abonnement</th>
                        <th><i class="fas fa-id-card"></i> Type d'abonnement</th>
                        <th><i class="fas fa-money-bill-wave"></i> Montant</th>
                        <th><i class="fas fa-cogs"></i> Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($clients) > 0): ?>
                        <?php foreach ($clients as $client): ?>
                            <tr>
                                <td><?= htmlspecialchars($client['nom']) ?></td>
                                <td><?= htmlspecialchars($client['prenom']) ?></td>
                                <td><?= htmlspecialchars($client['email']) ?></td>
                                <td><?= htmlspecialchars($client['sexe']) ?></td>
                                <td><?= htmlspecialchars($client['telephone']) ?></td>
                                <td><?= htmlspecialchars($client['age']) ?></td>
                                <td><?= htmlspecialchars($client['date_debut_abonnement']) ?></td>
                                <td><?= htmlspecialchars($client['date_fin_abonnement']) ?></td>
                                <td><?= htmlspecialchars($client['abonnement_nom'] ?? 'Aucun') ?></td>
                                <td>
                                    <?php if ($client['promo'] !== null): ?>
                                        <div class="price-old"><?= number_format($client['prix'], 2) ?> FCFA</div>
                                        <div class="price-new"><?= number_format($client['prix'] - $client['promo'], 2) ?> FCFA</div>
                                    <?php else: ?>
                                        <div class="price-new"><?= number_format($client['prix'], 2) ?> FCFA</div>
                                    <?php endif; ?>
                                </td>
                                <td class="actions-cell">
                                    <a href="edit_client.php?id=<?= $client['id'] ?>" class="action-btn edit"><i class="fas fa-edit"></i> Modifier</a>
                                    <a href="../actions/delete_client.php?id=<?= $client['id'] ?>" class="action-btn delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce client ?')"><i class="fas fa-trash-alt"></i> Supprimer</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="11" class="no-results">
                                <i class="fas fa-search" style="font-size: 2rem; margin-bottom: 15px;"></i><br>
                                Aucun client trouvé avec ces critères de recherche
                            </td>
                        </tr>
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
            if (document.getElementById('search')) {
                document.getElementById('search').focus();
            }
        });
    </script>
</body>
</html>