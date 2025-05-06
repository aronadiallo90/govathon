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

try {
    // Sélectionner les jurys depuis la table users unifiée
    $stmt = $pdo->prepare("
        SELECT 
            u.id,
            u.name,
            u.email,
            u.is_active,
            u.is_global_jury,
            s.nom as secteur_nom,
            (SELECT COUNT(*) FROM votes v WHERE v.user_id = u.id) as projets_evalues
        FROM users u
        LEFT JOIN secteurs s ON u.secteur_id = s.id
        WHERE u.role = 'jury'
        ORDER BY u.name ASC
    ");
    
    $stmt->execute();
    $jurys = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'jurys' => $jurys
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors du chargement des jurys'
    ]);
}