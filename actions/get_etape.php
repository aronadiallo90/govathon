<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

// Vérifier les droits d'accès
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'superadmin'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Accès non autorisé'
    ]);
    exit;
}

try {
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        throw new Exception('ID d\'étape invalide');
    }

    $etapeId = intval($_GET['id']);

    // Récupérer les informations de l'étape
    $stmt = $pdo->prepare("
        SELECT 
            id,
            nom,
            description,
            date_debut,
            date_fin,
            ordre,
            etat,
            created_at,
            updated_at
        FROM etapes 
        WHERE id = ?
    ");
    $stmt->execute([$etapeId]);
    $etape = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$etape) {
        throw new Exception('Étape non trouvée');
    }

    echo json_encode([
        'success' => true,
        'etape' => $etape
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 