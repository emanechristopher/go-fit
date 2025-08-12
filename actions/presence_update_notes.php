<?php
session_start();
require_once __DIR__ . '/../config/connexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $notes = $_POST['notes'] ?? '';

    if (!$id) {
        $_SESSION['message'] = "ID de présence manquant.";
        $_SESSION['message_type'] = "error";
        header('Location: ../pages/admin_presences.php');
        exit;
    }

    $stmt = $pdo->prepare("UPDATE presences SET notes = ? WHERE id = ?");
    $stmt->execute([$notes, $id]);

    $_SESSION['message'] = "Notes mises à jour.";
    $_SESSION['message_type'] = "success";
    header('Location: ../pages/admin_presences.php');
    exit;
}
