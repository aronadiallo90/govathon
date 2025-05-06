<?php
session_start();
require_once '../includes/db.php';

// Vérifier les droits d'accès
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'superadmin'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Accès non autorisé'
    ]);
    exit;
}

header('Content-Type: application/json');

try {
    $stmt = $pdo->query("
        SELECT s.*, 
            (SELECT COUNT(*) FROM users WHERE secteur_id = s.id) as user_count
        FROM secteurs s 
        ORDER BY s.nom ASC
    ");
    
    $sectors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'sectors' => $sectors
    ]);
} catch (PDOException $e) {
    error_log($e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors du chargement des secteurs'
    ]);
}