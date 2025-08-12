<?php
session_start();
require_once __DIR__ . '/../../config/connexion.php';

// Récupération des données
$client_id = $_POST['client_id'] ?? null;
$date_visite = $_POST['date_visite'] ?? null;

// Validation
if (!$client_id || !$date_visite) {
    echo json_encode(['success' => false, 'message' => 'Données manquantes']);
    exit;
}

try {
    // Vérifier si le client a déjà une présence ce jour
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM presences WHERE client_id = ? AND date_visite = ?");
    $stmt->execute([$client_id, $date_visite]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        echo json_encode(['success' => false, 'message' => 'Ce client est déjà enregistré aujourd\'hui']);
        exit;
    }

    // Ajouter la présence
    $heure_arrivee = date('Y-m-d H:i:s');
    $stmt = $pdo->prepare("INSERT INTO presences (client_id, date_visite, heure_arrivee) VALUES (?, ?, ?)");
    $stmt->execute([$client_id, $date_visite, $heure_arrivee]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur de base de données: ' . $e->getMessage()]);
}