<?php
require_once '../config/connexion.php';

$id = $_GET['id'] ?? null;

if ($id) {
    $stmt = $pdo->prepare("DELETE FROM paiements WHERE id = ?");
    $stmt->execute([$id]);
}

header('Location: paiements.php');
exit;
