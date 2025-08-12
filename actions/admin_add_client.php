<?php
require_once '../config/connexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et validation simple des données
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $telephone = trim($_POST['telephone']);
    // Mot de passe supprimé du formulaire: générer un mot de passe par défaut sécurisé
    $mot_de_passe = bin2hex(random_bytes(6));
    $sexe = $_POST['sexe'];
    $age = $_POST['age'];
    $abonnement_id = (int) $_POST['abonnement_id'];
    $date_debut = $_POST['date_debut_abonnement'];
    $date_fin = $_POST['date_fin_abonnement'];

    // Vérifier que l'email n'existe pas déjà
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        die("Erreur : cet email est déjà utilisé.");
    }

    // Hasher le mot de passe généré
    $hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);

    try {
        $pdo->beginTransaction();

        // Insérer dans users
        $stmt = $pdo->prepare("INSERT INTO users (nom, prenom, email, mot_de_passe, role, telephone,age) VALUES (?, ?, ?, ?, 'client', ?,?)");
        $stmt->execute([$nom, $prenom, $email, $hash, $telephone,$age]);

        $user_id = $pdo->lastInsertId();

        // Insérer dans clients
        $stmt = $pdo->prepare("INSERT INTO clients (id, sexe, abonnement_id, date_debut_abonnement, date_fin_abonnement) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $sexe, $abonnement_id, $date_debut, $date_fin]);

        $pdo->commit();

        header('Location: ../pages/clients.php');
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Erreur lors de l'ajout du client : " . $e->getMessage());
    }
} else {
    echo "Méthode non autorisée.";
}
