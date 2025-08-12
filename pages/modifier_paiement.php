<?php
require_once '../config/connexion.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: paiements.php');
    exit;
}

// Récupérer le paiement + client + abonnement actuel
$stmt = $pdo->prepare("
    SELECT p.*, u.nom, u.prenom, c.abonnement_id, c.id AS client_id
    FROM paiements p
    JOIN clients c ON p.client_id = c.id
    JOIN users u ON c.id = u.id
    WHERE p.id = ?
");
$stmt->execute([$id]);
$paiement = $stmt->fetch();

if (!$paiement) {
    echo "Paiement introuvable.";
    exit;
}

// Récupérer tous les abonnements pour le select et prix
$abonnements = $pdo->query("SELECT id, nom, prix FROM abonnements ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'];
    $date_paiement = $_POST['date_paiement'];
    $nouvel_abonnement_id = $_POST['abonnement_id'];

    // Récupérer le prix de l'abonnement choisi
    $stmtPrix = $pdo->prepare("SELECT prix FROM abonnements WHERE id = ?");
    $stmtPrix->execute([$nouvel_abonnement_id]);
    $prixAbonnement = $stmtPrix->fetchColumn();

    // Mettre à jour le paiement avec le prix correspondant à l'abonnement choisi
    $updatePaiement = $pdo->prepare("UPDATE paiements SET montant = ?, type = ?, date_paiement = ? WHERE id = ?");
    $updatePaiement->execute([$prixAbonnement, $type, $date_paiement, $id]);

    // Mettre à jour l'abonnement du client
    $updateAbonnement = $pdo->prepare("UPDATE clients SET abonnement_id = ? WHERE id = ?");
    $updateAbonnement->execute([$nouvel_abonnement_id, $paiement['client_id']]);

    header('Location: paiements.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier paiement | GO FIT</title>
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
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
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

        .client-info {
            text-align: center;
            margin-bottom: 30px;
            padding: 15px;
            background: var(--light-gray);
            border-radius: 8px;
            font-size: 1.2rem;
        }

        .client-name {
            color: var(--orange);
            font-weight: bold;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr;
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

        .form-group input[readonly] {
            background-color: #333;
            color: #aaa;
        }

        .button-container {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .submit-btn {
            flex: 1;
            min-width: 200px;
            padding: 16px;
            background-color: var(--orange);
            color: var(--white);
            font-weight: bold;
            font-size: 1.1rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: var(--transition);
            text-align: center;
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

        .cancel-btn {
            flex: 1;
            min-width: 200px;
            padding: 16px;
            background-color: #444;
            color: var(--white);
            font-weight: bold;
            font-size: 1.1rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: var(--transition);
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
        }

        .cancel-btn:hover {
            background-color: #666;
            transform: translateY(-3px);
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
            
            .form-container {
                padding: 20px;
            }
            
            .form-header h2 {
                font-size: 1.8rem;
            }
            
            .button-container {
                flex-direction: column;
            }
            
            .submit-btn, .cancel-btn {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .header-container {
                padding: 12px 15px;
            }
            
            header img {
                height: 50px;
            }
            
            header h1 {
                font-size: 1.5rem;
            }
            
            .form-header h2 {
                font-size: 1.6rem;
            }
            
            .client-info {
                font-size: 1.1rem;
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
                <h1>Modifier paiement</h1>
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
        <div class="form-container">
            <div class="form-header">
                <h2>Modifier un paiement</h2>
                <p>Mettez à jour les informations de ce paiement</p>
            </div>
            
            <div class="client-info">
                Client : <span class="client-name"><?= htmlspecialchars($paiement['nom'] . ' ' . $paiement['prenom']) ?></span>
            </div>
            
            <form method="post">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="montant"><i class="fas fa-money-bill-wave"></i> Montant :</label>
                        <input type="number" step="0.01" name="montant" id="montant" 
                               value="<?= $paiement['montant'] ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label for="type"><i class="fas fa-credit-card"></i> Type de paiement :</label>
                        <select name="type" id="type" required>
                            <option value="espèces" <?= $paiement['type'] === 'espèces' ? 'selected' : '' ?>>Espèces</option>
                            <option value="mobile money" <?= $paiement['type'] === 'mobile money' ? 'selected' : '' ?>>Mobile Money</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="abonnement_id"><i class="fas fa-id-card"></i> Abonnement :</label>
                        <select name="abonnement_id" id="abonnement_id" required>
                            <?php foreach ($abonnements as $abo): ?>
                                <option value="<?= $abo['id'] ?>" <?= $abo['id'] == $paiement['abonnement_id'] ? 'selected' : '' ?> data-prix="<?= $abo['prix'] ?>">
                                    <?= htmlspecialchars($abo['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="date_paiement"><i class="fas fa-calendar-day"></i> Date de paiement :</label>
                        <input type="datetime-local" name="date_paiement" id="date_paiement" 
                               value="<?= date('Y-m-d\TH:i', strtotime($paiement['date_paiement'])) ?>" required>
                    </div>
                </div>
                
                <div class="button-container">
                    <button type="submit" class="submit-btn"><i class="fas fa-save"></i> Mettre à jour</button>
                    <a href="paiements.php" class="cancel-btn"><i class="fas fa-times"></i> Annuler</a>
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
        
        // Met à jour le montant en fonction du prix de l'abonnement sélectionné
        const selectAbo = document.getElementById('abonnement_id');
        const montantInput = document.getElementById('montant');

        selectAbo.addEventListener('change', function() {
            const option = this.options[this.selectedIndex];
            const prix = option.getAttribute('data-prix');
            montantInput.value = prix;
        });

        // Au chargement, on met à jour le montant avec le prix sélectionné
        window.addEventListener('DOMContentLoaded', () => {
            const option = selectAbo.options[selectAbo.selectedIndex];
            const prix = option.getAttribute('data-prix');
            montantInput.value = prix;
        });
    </script>
</body>
</html>