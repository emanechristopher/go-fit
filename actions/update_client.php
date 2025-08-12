<?php
require_once __DIR__ . '/../config/connexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) $_POST['id'];
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $sexe = $_POST['sexe'];
    $age = $_POST['age'];
    $abonnement_id = $_POST['abonnement_id'] ?: null;
    $date_debut = $_POST['date_debut_abonnement'] ?: null;
    $date_fin = $_POST['date_fin_abonnement'] ?: null;

    try {
        // Mettre à jour la table users
        $stmt1 = $pdo->prepare("UPDATE users SET nom = ?, prenom = ?, email = ?, age = ? WHERE id = ?");
        $stmt1->execute([$nom, $prenom, $email, $age, $id]);

        // Mettre à jour la table clients
        $stmt2 = $pdo->prepare("UPDATE clients SET sexe = ?, abonnement_id = ?, date_debut_abonnement = ?, date_fin_abonnement = ? WHERE id = ?");
        $stmt2->execute([$sexe, $abonnement_id, $date_debut, $date_fin, $id]);

        header("Location: ../pages/clients.php");
        exit;
    } catch (PDOException $e) {
        die("Erreur lors de la mise à jour du client : " . $e->getMessage());
    }
}
