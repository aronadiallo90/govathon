<?php
session_start();
require_once 'auth.php';
requireLogin();
requireAdmin();
require_once 'config.php';

header('Content-Type: application/json');

// Vérification de la méthode HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Méthode HTTP non autorisée'
    ]);
    exit;
}

// Récupération et validation des données
$data = json_decode(file_get_contents('php://input'), true);
$fieldId = intval($data['id'] ?? 0);

if (!$fieldId) {
    echo json_encode([
        'success' => false,
        'message' => 'ID du champ invalide ou manquant'
    ]);
    exit;
}

try {
    // Début de la transaction
    $pdo->beginTransaction();

    // Vérification de l'existence du champ
    $checkStmt = $pdo->prepare("
        SELECT COUNT(*) FROM dynamic_field_definitions 
        WHERE id = ?
    ");
    $checkStmt->execute([$fieldId]);
    
    if ($checkStmt->fetchColumn() == 0) {
        throw new Exception('Champ introuvable');
    }

    // Suppression des valeurs associées
    $deleteValuesStmt = $pdo->prepare("
        DELETE FROM project_dynamic_values 
        WHERE field_id = ?
    ");
    $deleteValuesStmt->execute([$fieldId]);

    // Suppression de la définition du champ
    $deleteFieldStmt = $pdo->prepare("
        DELETE FROM dynamic_field_definitions 
        WHERE id = ?
    ");
    $deleteFieldStmt->execute([$fieldId]);

    // Validation de la transaction
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Champ dynamique supprimé avec succès'
    ]);

} catch (Exception $e) {
    // Annulation de la transaction en cas d'erreur
    $pdo->rollBack();
    
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la suppression : ' . $e->getMessage()
    ]);
}