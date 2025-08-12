<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/connexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Requête invalide']);
    exit;
}

$id = $_POST['id'] ?? null;
$notes = $_POST['notes'] ?? '';

if (!$id) {
    echo json_encode(['success' => false, 'error' => 'ID manquant']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE presences SET notes = ? WHERE id = ?");
    $stmt->execute([$notes, $id]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Erreur serveur']);
}
