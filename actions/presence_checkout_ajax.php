<?php
session_start();
require_once __DIR__ . '/../../config/connexion.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID manquant']);
    exit;
}

try {
    // Enregistrer l'heure de sortie
    $heure_sortie = date('Y-m-d H:i:s');
    $stmt = $pdo->prepare("UPDATE presences SET heure_sortie = ? WHERE id = ?");
    $stmt->execute([$heure_sortie, $id]);

    // Formatage de l'heure pour la réponse
    $formatted_time = date('H\hi', strtotime($heure_sortie));
    
    echo json_encode([
        'success' => true,
        'heure_sortie' => $formatted_time
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur de base de données: ' . $e->getMessage()]);
}