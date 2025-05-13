<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

// Vérification de l'authentification
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'superadmin'])) {
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
    exit;
}

try {
    // Vérification de l'ID
    if (!isset($_GET['id'])) {
        throw new Exception('ID du critère manquant');
    }

    $critereId = intval($_GET['id']);

    // Récupération du critère
    $stmt = $pdo->prepare("
        SELECT id, nom, description, coefficient, created_at, updated_at 
        FROM criteres 
        WHERE id = ?
    ");
    $stmt->execute([$critereId]);
    
    $critere = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$critere) {
        throw new Exception('Critère non trouvé');
    }

    echo json_encode([
        'success' => true,
        'critere' => $critere
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 