<?php
// Informations de connexion à la base de données
$host = '	sql111.infinityfree.com';       // ou '127.0.0.1'
$dbname = 'if0_39677540_gofitness';
$username = 'if0_39677540';        // À adapter si tu utilises un autre utilisateur
$password = 'FDeTgI60YTxru2';            // À adapter aussi (souvent vide en local sous XAMPP/WAMP)

// Configuration des options PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // Gestion des erreurs
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // Résultats sous forme de tableaux associatifs
    PDO::ATTR_EMULATE_PREPARES   => false                    // Utilise les vraies requêtes préparées
];

try {
    // Création de l'instance PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, $options);
} catch (PDOException $e) {
    // En cas d'erreur de connexion
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>
