<?php
require_once __DIR__ . '/../config/connexion.php';

if (!isset($_GET['id'])) {
    die("ID client manquant.");
}

$id = (int) $_GET['id'];

try {
    // Affichage explicite de l'avertissement (utile si tu prévois un bouton de confirmation HTML côté frontend)
    // Ici on considère que tu gères la confirmation côté frontend

    // Supprimer le client (les présences liées seront supprimées automatiquement via ON DELETE CASCADE)
    $stmt = $pdo->prepare("DELETE FROM clients WHERE id = ?");
    $stmt->execute([$id]);

    // Supprimer l'utilisateur associé si nécessaire (si users.id == clients.id)
    $stmt2 = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt2->execute([$id]);

    // Redirection après suppression
    header("Location: ../pages/clients.php?message=Client supprimé avec succès. Les présences ont aussi été supprimées.");
    exit;
} catch (PDOException $e) {
    die("Erreur lors de la suppression du client : " . $e->getMessage());
}
