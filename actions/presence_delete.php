<?php
session_start();
require_once __DIR__ . '/../config/connexion.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: ../pages/admin_presences.php');
    exit;
}

$stmt = $pdo->prepare("DELETE FROM presences WHERE id = ?");
$stmt->execute([$id]);

$_SESSION['message'] = "Présence supprimée.";
$_SESSION['message_type'] = "success";
header('Location: ../pages/admin_presences.php');
exit;
