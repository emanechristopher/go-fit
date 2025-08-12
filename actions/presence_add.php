<?php
session_start();
require_once __DIR__ . '/../config/connexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_id = $_POST['client_id'] ?? null;
    $date_visite = $_POST['date_visite'] ?? null;

    if (!$client_id || !$date_visite) {
        $_SESSION['message'] = "Tous les champs sont obligatoires.";
        $_SESSION['message_type'] = "error";
        header('Location: ../pages/admin_presences.php');
        exit;
    }

    // Vérifier si une présence existe déjà pour ce client et cette date (éviter doublon)
    $stmt = $pdo->prepare("SELECT id FROM presences WHERE client_id = ? AND date_visite = ?");
    $stmt->execute([$client_id, $date_visite]);
    if ($stmt->fetch()) {
        $_SESSION['message'] = "Présence déjà enregistrée pour ce client à cette date.";
        $_SESSION['message_type'] = "error";
        header('Location: ../pages/admin_presences.php');
        exit;
    }

    // Insérer présence avec heure_arrivee à maintenant
    $stmt = $pdo->prepare("INSERT INTO presences (client_id, date_visite, heure_arrivee) VALUES (?, ?, NOW())");
    $stmt->execute([$client_id, $date_visite]);

    $_SESSION['message'] = "Présence ajoutée avec succès.";
    $_SESSION['message_type'] = "success";
    header('Location: ../pages/admin_presences.php');
    exit;
}
