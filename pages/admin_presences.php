<?php
session_start();
require_once __DIR__ . '/../config/connexion.php';

// Forcer l'encodage UTF-8
header('Content-Type: text/html; charset=UTF-8');
mb_internal_encoding('UTF-8');

// Date sélectionnée via GET ou aujourd’hui par défaut
$date_select = $_GET['date'] ?? date('Y-m-d');

// Récupérer toutes les dates distinctes de présence (pour naviguer)
$dates_disponibles = $pdo->query("
    SELECT DISTINCT date_visite FROM presences ORDER BY date_visite DESC
")->fetchAll(PDO::FETCH_COLUMN);

// Trouver l’index de la date actuelle dans la liste
$current_index = array_search($date_select, $dates_disponibles);

// Déterminer la date précédente et suivante (s'il y en a)
$date_precedente = $dates_disponibles[$current_index + 1] ?? null;
$date_suivante   = $dates_disponibles[$current_index - 1] ?? null;

// Récupérer les présences pour cette date
$sql = "SELECT p.id, p.date_visite, p.heure_arrivee, p.heure_sortie, p.notes, u.nom, u.prenom 
        FROM presences p
        JOIN clients c ON p.client_id = c.id
        JOIN users u ON c.id = u.id
        WHERE p.date_visite = ?
        ORDER BY p.heure_arrivee DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$date_select]);
$presences = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Liste des clients pour l'ajout de présence
$clients = $pdo->query("SELECT c.id, u.nom, u.prenom FROM clients c JOIN users u ON c.id = u.id ORDER BY u.nom, u.prenom")->fetchAll();

$message = $_SESSION['message'] ?? '';
$message_type = $_SESSION['message_type'] ?? '';
unset($_SESSION['message'], $_SESSION['message_type']);

        // Formater la date pour l'affichage en français
        // Utiliser une approche manuelle pour éviter les problèmes d'encodage
        $mois = [
            '01' => 'janvier', '02' => 'février', '03' => 'mars',
            '04' => 'avril', '05' => 'mai', '06' => 'juin',
            '07' => 'juillet', '08' => 'août', '09' => 'septembre',
            '10' => 'octobre', '11' => 'novembre', '12' => 'décembre'
        ];
        $jours = [
            'Monday' => 'lundi', 'Tuesday' => 'mardi', 'Wednesday' => 'mercredi',
            'Thursday' => 'jeudi', 'Friday' => 'vendredi', 'Saturday' => 'samedi', 'Sunday' => 'dimanche'
        ];
        
        $timestamp = strtotime($date_select);
        $jour = $jours[date('l', $timestamp)];
        $date_num = date('j', $timestamp);
        $mois_nom = $mois[date('m', $timestamp)];
        $annee = date('Y', $timestamp);
        
        $formattedDate = "$jour $date_num $mois_nom $annee";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Gestion des présences | GO FIT</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
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
            --info: #2196F3;
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
            max-width: 1200px;
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

        .current-date {
            text-align: center;
            font-size: 1.4rem;
            margin-bottom: 25px;
            color: #ddd;
            background: var(--dark-gray);
            padding: 15px;
            border-radius: 10px;
            max-width: 500px;
            margin: 0 auto 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        /* Messages */
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            text-align: center;
            max-width: 800px;
            margin: 0 auto 25px;
        }

        .success {
            background-color: rgba(76, 175, 80, 0.15);
            border-left: 4px solid var(--success);
        }

        .error {
            background-color: rgba(244, 67, 54, 0.15);
            border-left: 4px solid var(--error);
        }

        /* Contrôles de navigation */
        .controls-container {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            gap: 20px;
            align-items: center;
            margin-bottom: 30px;
            max-width: 1000px;
            margin: 0 auto 30px;
        }

        .nav-jours {
            display: flex;
            gap: 15px;
        }

        .nav-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            background-color: #444;
            color: var(--white);
            text-decoration: none;
            font-weight: bold;
            border-radius: 8px;
            transition: var(--transition);
        }

        .nav-btn:hover {
            background-color: #666;
            transform: translateY(-2px);
        }

        .nav-btn.disabled {
            opacity: 0.5;
            pointer-events: none;
        }

        .date-form {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }

        .date-form label {
            font-weight: 600;
            color: #ddd;
        }

        .date-form input {
            padding: 12px;
            background-color: #2a2a2a;
            border: 2px solid #333;
            border-radius: 8px;
            color: var(--white);
            font-size: 1rem;
            transition: border-color 0.3s ease;
            max-width: 250px;
        }

        .date-form input:focus {
            outline: none;
            border-color: var(--orange);
        }

        .add-presence-form {
            background: var(--dark-gray);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
            max-width: 1000px;
            margin: 0 auto 40px;
        }

        .form-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .form-header h3 {
            color: var(--orange);
            font-size: 1.6rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 15px;
            align-items: end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            margin-bottom: 8px;
            font-weight: 600;
            color: #ddd;
        }

        .form-control {
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

        select.form-control {
            height: 48px;
        }

        .submit-btn {
            padding: 16px 25px;
            background-color: var(--orange);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .submit-btn:hover {
            background-color: var(--orange-hover);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(241, 90, 36, 0.3);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        /* Tableau des présences */
        .table-container {
            overflow-x: auto;
            background: var(--dark-gray);
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
            max-width: 1000px;
            margin: 0 auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 700px;
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
            position: sticky;
            top: 0;
        }

        tbody tr {
            transition: var(--transition);
        }

        tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }

        .arrival {
            color: #4CAF50;
            font-weight: bold;
        }

        .departure {
            color: #FF9800;
            font-weight: bold;
        }

        .notes-cell {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .notes-input {
            flex: 1;
            padding: 10px;
            background-color: #2a2a2a;
            border: 2px solid #333;
            border-radius: 8px;
            color: var(--white);
            font-size: 0.95rem;
            transition: border-color 0.3s ease;
        }

        .notes-input:focus {
            outline: none;
            border-color: var(--orange);
        }

        .save-note-btn {
            padding: 8px 15px;
            background-color: #444;
            color: var(--white);
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .save-note-btn:hover {
            background-color: #666;
        }

        .status-msg {
            font-size: 0.9rem;
            margin-left: 10px;
        }

        .actions-cell {
            display: flex;
            gap: 10px;
        }

        .action-btn {
            padding: 8px 15px;
            border-radius: 6px;
            font-size: 0.9rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: var(--transition);
        }

        .action-btn.checkout {
            background-color: rgba(255, 152, 0, 0.2);
            color: #FF9800;
            border: 1px solid rgba(255, 152, 0, 0.3);
        }

        .action-btn.checkout:hover {
            background-color: rgba(255, 152, 0, 0.3);
        }

        .action-btn.delete {
            background-color: rgba(244, 67, 54, 0.2);
            color: #f44336;
            border: 1px solid rgba(244, 67, 54, 0.3);
        }

        .action-btn.delete:hover {
            background-color: rgba(244, 67, 54, 0.3);
        }

        .no-data {
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
        @media (max-width: 992px) {
            .controls-container {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .nav-jours {
                justify-content: center;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
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
            
            .add-presence-form {
                padding: 20px;
            }
            
            .notes-cell {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .save-note-btn {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 576px) {
            .header-container {
                padding: 12px 15px;
            }
            
            .page-title {
                font-size: 1.7rem;
            }
            
            .current-date {
                font-size: 1.2rem;
                padding: 12px;
            }
            
            .form-control {
                padding: 12px;
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
                <h1>Gestion des présences</h1>
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
            <a href="../index.php" class="button"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a>
        </div>
    </nav>

    <div class="overlay" id="overlay"></div>

    <main>
        <h2 class="page-title"><i class="fas fa-calendar-check"></i> Registre de présence</h2>
        
        <div class="current-date">
            <i class="fas fa-calendar-day"></i> <?= ucfirst($formattedDate) ?>
        </div>
        
        <?php if ($message): ?>
            <div class="message <?= htmlspecialchars($message_type) ?>">
                <i class="fas fa-info-circle"></i> <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        
        <div class="controls-container">
            <div class="nav-jours">
                <a href="?date=<?= $date_precedente ?>" class="nav-btn <?= !$date_precedente ? 'disabled' : '' ?>">
                    <i class="fas fa-arrow-left"></i> Jour précédent
                </a>
                <a href="?date=<?= $date_suivante ?>" class="nav-btn <?= !$date_suivante ? 'disabled' : '' ?>">
                    Jour suivant <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            
            <div class="date-form">
                <label for="calendar"><i class="fas fa-calendar-alt"></i> Changer de date</label>
                <form method="get">
                    <input type="date" name="date" id="calendar" value="<?= htmlspecialchars($date_select) ?>" onchange="this.form.submit()" max="<?= date('Y-m-d') ?>">
                </form>
            </div>
        </div>
        
        <div class="add-presence-form">
            <div class="form-header">
                <h3><i class="fas fa-user-plus"></i> Ajouter une présence</h3>
            </div>
            
            <form method="post" action="../actions/presence_add.php">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="client-select">Client</label>
                        <select id="client-select" name="client_id" class="form-control" required>
                            <option value="">-- Sélectionnez un client --</option>
                            <?php foreach ($clients as $client): ?>
                                <option value="<?= $client['id'] ?>"><?= htmlspecialchars($client['nom'] . ' ' . $client['prenom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <input type="hidden" name="date_visite" value="<?= htmlspecialchars($date_select) ?>">
                    
                    <button type="submit" class="submit-btn">
                        <i class="fas fa-plus-circle"></i> Enregistrer l'arrivée
                    </button>
                </div>
            </form>
        </div>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th><i class="fas fa-user"></i> Client</th>
                        <th><i class="fas fa-sign-in-alt"></i> Arrivée</th>
                        <th><i class="fas fa-sign-out-alt"></i> Sortie</th>
                        <th><i class="fas fa-comment"></i> Notes</th>
                        <th><i class="fas fa-cogs"></i> Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (count($presences) === 0): ?>
                    <tr>
                        <td colspan="5" class="no-data">
                            <i class="fas fa-search" style="font-size: 2rem; margin-bottom: 15px;"></i><br>
                            Aucune présence enregistrée ce jour
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($presences as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars($p['nom'] . ' ' . $p['prenom']) ?></td>
                            <td class="arrival"><?= $p['heure_arrivee'] ? date('H\hi', strtotime($p['heure_arrivee'])) : '-' ?></td>
                            <td class="departure"><?= $p['heure_sortie'] ? date('H\hi', strtotime($p['heure_sortie'])) : '-' ?></td>
                            <td>
                                <div class="notes-cell">
                                    <input type="text" class="notes-input form-control" data-id="<?= $p['id'] ?>" value="<?= htmlspecialchars($p['notes'] ?? '') ?>" placeholder="Ajouter une note...">
                                    <button class="save-note-btn" data-id="<?= $p['id'] ?>">
                                        <i class="fas fa-save"></i> Enregistrer
                                    </button>
                                    <span class="status-msg" id="status-<?= $p['id'] ?>"></span>
                                </div>
                            </td>
                            <td class="actions-cell">
                                <?php if (!$p['heure_sortie']): ?>
                                    <a href="../actions/presence_checkout.php?id=<?= $p['id'] ?>" class="action-btn checkout" onclick="return confirm('Enregistrer la sortie de ce client ?')">
                                        <i class="fas fa-sign-out-alt"></i> Sortie
                                    </a>
                                <?php endif; ?>
                                <a href="../actions/presence_delete.php?id=<?= $p['id'] ?>" class="action-btn delete" onclick="return confirm('Supprimer cette présence ?')">
                                    <i class="fas fa-trash-alt"></i> Supprimer
                                </a>
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
        
        // Sauvegarde des notes via AJAX
        document.querySelectorAll('.save-note-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const id = this.dataset.id;
                const input = document.querySelector(`.notes-input[data-id="${id}"]`);
                const notes = input.value;
                const statusEl = document.getElementById(`status-${id}`);
                statusEl.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enregistrement...';
                
                fetch('../actions/presence_update_notes_ajax.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: new URLSearchParams({id, notes})
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        statusEl.innerHTML = '<i class="fas fa-check"></i> Enregistré';
                        setTimeout(() => statusEl.innerHTML = '', 2000);
                    } else {
                        statusEl.innerHTML = '<i class="fas fa-times"></i> Erreur';
                        setTimeout(() => statusEl.innerHTML = '', 3000);
                    }
                })
                .catch(() => {
                    statusEl.innerHTML = '<i class="fas fa-times"></i> Erreur réseau';
                    setTimeout(() => statusEl.innerHTML = '', 3000);
                });
            });
        });
    </script>
</body>
</html>


