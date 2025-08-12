<?php
session_start();
require_once __DIR__ . '/../config/connexion.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: ../pages/admin_presences.php');
    exit;
}

// Mettre à jour heure_sortie à maintenant
$stmt = $pdo->prepare("UPDATE presences SET heure_sortie = NOW() WHERE id = ?");
$stmt->execute([$id]);

$_SESSION['message'] = "Heure de sortie enregistrée.";
$_SESSION['message_type'] = "success";
header('Location: ../pages/admin_presences.php');
exit;
