<?php
session_start();
require_once __DIR__ . '/../../config/connexion.php';

$date = $_GET['date'] ?? date('Y-m-d');

try {
    $sql = "SELECT p.id, p.date_visite, p.heure_arrivee, p.heure_sortie, p.notes, u.nom, u.prenom 
            FROM presences p
            JOIN clients c ON p.client_id = c.id
            JOIN users u ON c.id = u.id
            WHERE p.date_visite = ?
            ORDER BY p.heure_arrivee DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$date]);
    $presences = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($presences);
} catch (PDOException $e) {
    echo json_encode([]);
}