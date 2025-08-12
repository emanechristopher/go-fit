<?php
require_once '../config/connexion.php';

$errors = [];
$success = '';

try {
    // Récupérer les clients avec leurs abonnements
    $clients = $pdo->query("
        SELECT c.id, u.nom, u.prenom, a.prix, a.nom AS nom_abonnement
        FROM clients c
        JOIN users u ON c.id = u.id
        LEFT JOIN abonnements a ON c.abonnement_id = a.id
        ORDER BY u.nom, u.prenom
    ")->fetchAll(PDO::FETCH_ASSOC);

    $methodes_valides = ['espèces', 'mobile money'];

    $client_id = '';
    $methode_paiement = '';
    $description = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $client_id = $_POST['client_id'] ?? '';
        $methode_paiement = $_POST['methode_paiement'] ?? '';
        $description = $_POST['description'] ?? null;

        // Récupérer le montant à partir du client sélectionné
        $stmt = $pdo->prepare("
            SELECT a.prix, a.nom AS nom_abonnement 
            FROM clients c
            LEFT JOIN abonnements a ON c.abonnement_id = a.id
            WHERE c.id = ?
        ");
        $stmt->execute([$client_id]);
        $result = $stmt->fetch();

        if (!$result || !$result['prix']) {
            $errors[] = "Ce client n'a pas d'abonnement actif ou le prix n'est pas défini.";
        } else {
            $montant = $result['prix'];
        }

        if (empty($client_id)) {
            $errors[] = "Veuillez sélectionner un client.";
        }

        if (empty($methode_paiement) || !in_array($methode_paiement, $methodes_valides, true)) {
            $errors[] = "Veuillez sélectionner une méthode de paiement valide.";
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare("INSERT INTO paiements (client_id, montant, type, description) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$client_id, $montant, $methode_paiement, $description])) {
                $success = "Paiement ajouté avec succès.";
                $client_id = '';
                $methode_paiement = '';
                $description = '';
            } else {
                $errors[] = "Erreur lors de l'ajout du paiement.";
            }
        }
    }
} catch (Exception $e) {
    $errors[] = "Erreur serveur : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un paiement | GO FIT</title>
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
            max-width: 600px;
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

        /* Messages d'erreur et de succès */
        .message-container {
            margin-bottom: 25px;
        }

        .error {
            background-color: rgba(244, 67, 54, 0.15);
            border-left: 4px solid var(--error);
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .error ul {
            padding-left: 20px;
            margin-top: 10px;
        }

        .error li {
            margin-bottom: 5px;
        }

        .success {
            background-color: rgba(76, 175, 80, 0.15);
            border-left: 4px solid var(--success);
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        /* Formulaire */
        .form-container {
            background: var(--dark-gray);
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
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

        .form-control[readonly] {
            background-color: #333;
            color: #aaa;
            cursor: not-allowed;
        }

        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }

        .submit-btn {
            width: 100%;
            padding: 16px;
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
            
            .back-btn {
                width: 100%;
                justify-content: center;
            }
            
            .page-title {
                font-size: 1.7rem;
            }
            
            .form-control {
                padding: 12px;
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
                <h1>Ajouter paiement</h1>
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
        <h2 class="page-title">Ajouter un paiement</h2>
        
        <a href="paiements.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Retour aux paiements
        </a>
        
        <!-- Messages d'erreur/succès -->
        <?php if ($errors): ?>
            <div class="message-container">
                <div class="error">
                    <strong><i class="fas fa-exclamation-circle"></i> Erreurs :</strong>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="message-container">
                <p class="success">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
                </p>
            </div>
        <?php endif; ?>
        
        <div class="form-container">
            <form method="post">
                <div class="form-group">
                    <label for="client_id">
                        <i class="fas fa-user"></i> Client :
                    </label>
                    <select id="client_id" name="client_id" class="form-control" required>
                        <option value="">-- Sélectionnez un client --</option>
                        <?php foreach ($clients as $client): ?>
                            <option value="<?= $client['id'] ?>" <?= ($client_id == $client['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($client['nom'] . ' ' . $client['prenom']) ?> - <?= $client['nom_abonnement'] ?? 'Aucun abonnement' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="montant">
                        <i class="fas fa-money-bill-wave"></i> Montant (FCFA) :
                    </label>
                    <input type="number" id="montant" name="montant" class="form-control" readonly>
                </div>

                <div class="form-group">
                    <label for="methode_paiement">
                        <i class="fas fa-credit-card"></i> Méthode de paiement :
                    </label>
                    <select id="methode_paiement" name="methode_paiement" class="form-control" required>
                        <option value="">-- Sélectionnez une méthode --</option>
                        <option value="espèces" <?= $methode_paiement === 'espèces' ? 'selected' : '' ?>>Espèces</option>
                        <option value="mobile money" <?= $methode_paiement === 'mobile money' ? 'selected' : '' ?>>Mobile Money</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="description">
                        <i class="fas fa-comment"></i> Description (facultatif) :
                    </label>
                    <textarea id="description" name="description" class="form-control" rows="3"><?= htmlspecialchars($description) ?></textarea>
                </div>

                <button type="submit" class="submit-btn">
                    <i class="fas fa-plus-circle"></i> Ajouter le paiement
                </button>
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

        // Gestion du montant basé sur le client sélectionné
        const clientSelect = document.getElementById('client_id');
        const montantInput = document.getElementById('montant');

        const prices = {
            <?php foreach ($clients as $client): ?>
                <?= json_encode($client['id']) ?>: <?= json_encode($client['prix'] ?? null) ?>,
            <?php endforeach; ?>
        };

        clientSelect.addEventListener('change', () => {
            const id = clientSelect.value;
            const price = prices[id];
            if (price && price > 0) {
                montantInput.value = price;
            } else {
                montantInput.value = '';
                // Afficher un message d'avertissement si nécessaire
                if (id && !price) {
                    console.warn('Ce client n\'a pas d\'abonnement avec prix défini');
                }
            }
        });

        // Initialisation
        window.addEventListener('DOMContentLoaded', () => {
            const id = clientSelect.value;
            const price = prices[id];
            if (price && price > 0) {
                montantInput.value = price;
            } else {
                montantInput.value = '';
            }
        });
    </script>
</body>
</html>