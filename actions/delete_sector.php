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

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id']) || !is_numeric($data['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID invalide'
    ]);
    exit;
}

try {
    // Vérifier si le secteur est utilisé
    $check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE secteur_id = ?");
    $check->execute([$data['id']]);
    if ($check->fetchColumn() > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Ce secteur ne peut pas être supprimé car il est utilisé'
        ]);
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM secteurs WHERE id = ?");
    $stmt->execute([$data['id']]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Secteur non trouvé'
        ]);
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la suppression'
    ]);
}