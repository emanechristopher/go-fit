<?php
session_start();
require_once __DIR__ . '/../config/connexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $sexe = $_POST['sexe'];
    $abonnement_id = $_POST['abonnement_id'] ?: null;
    $date_debut = $_POST['date_debut_abonnement'] ?: null;
    $date_fin = $_POST['date_fin_abonnement'] ?: null;

    try {
        // Insérer dans users
        $stmt = $pdo->prepare("INSERT INTO users (nom, prenom, email, password, role) VALUES (?, ?, ?, ?, 'client')");
        $stmt->execute([$nom, $prenom, $email, $password]);

        $user_id = $pdo->lastInsertId();

        // Insérer dans clients
        $stmt2 = $pdo->prepare("INSERT INTO clients (id, sexe, abonnement_id, date_debut_abonnement, date_fin_abonnement) VALUES (?, ?, ?, ?, ?)");
        $stmt2->execute([$user_id, $sexe, $abonnement_id, $date_debut, $date_fin]);

        $_SESSION['message'] = "✅ Client ajouté avec succès.";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "❌ Erreur lors de l'ajout du client : " . $e->getMessage();
        $_SESSION['message_type'] = "error";
    }

    header("Location: ../pages/clients.php");
    exit;
}
