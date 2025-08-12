<?php
require_once '../config/connexion.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM abonnements WHERE id = ?");
$stmt->execute([$id]);
$abonnement = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$abonnement) {
    echo "Abonnement introuvable.";
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'] ?? '';
    $duree = (int) ($_POST['duree'] ?? 0);
    $prix = (float) ($_POST['prix'] ?? 0);
    $promo = $_POST['promo'] !== '' ? (float) $_POST['promo'] : null;
    $type = $_POST['type'] ?? '';

    if ($nom && $duree && $prix && $type) {
        $stmt = $pdo->prepare("UPDATE abonnements SET nom=?, duree=?, prix=?, promo=?, type=? WHERE id=?");
        $stmt->execute([$nom, $duree, $prix, $promo, $type, $id]);
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
    <meta charset="UTF-8" />
    <title>Modifier un abonnement</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; padding: 20px; }
        form { background: #fff; padding: 20px; border-radius: 8px; width: 400px; margin: auto; }
        label { display: block; margin-top: 15px; }
        input, select { width: 100%; padding: 8px; margin-top: 5px; }
        button { margin-top: 20px; background: #28a745; color: white; padding: 10px; border: none; border-radius: 5px; }
        .error { color: red; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/_nav_links.php'; ?>
    <h2>Modifier l’abonnement</h2>
    <?php if ($message): ?><p class="error"><?= $message ?></p><?php endif; ?>

    <form method="post">
        <label>Nom de l'abonnement *</label>
        <input type="text" name="nom" value="<?= htmlspecialchars($abonnement['nom']) ?>" required>

        <label>Durée (en jours) *</label>
        <input type="number" name="duree" value="<?= $abonnement['duree'] ?>" required>

        <label>Prix (FCFA) *</label>
        <input type="number" name="prix" step="0.01" value="<?= $abonnement['prix'] ?>" required>

        <label>Promo (FCFA)</label>
        <input type="number" name="promo" step="0.01" value="<?= $abonnement['promo'] ?>">

        <label>Type *</label>
        <select name="type" required>
            <option value="">-- Sélectionnez --</option>
            <option value="Standard" <?= $abonnement['type'] === 'Standard' ? 'selected' : '' ?>>Standard</option>
            <option value="Premium" <?= $abonnement['type'] === 'Premium' ? 'selected' : '' ?>>Premium</option>
        </select>

        <button type="submit">Enregistrer</button>
    </form>
</body>
</html>
