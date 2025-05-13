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
    // Récupération des données JSON
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || !isset($data['id'])) {
        throw new Exception('ID du critère manquant');
    }

    $critereId = intval($data['id']);

    // Démarrer une transaction
    $pdo->beginTransaction();

    // Vérifier si le critère existe
    $stmt = $pdo->prepare("SELECT id FROM criteres WHERE id = ?");
    $stmt->execute([$critereId]);
    
    if (!$stmt->fetch()) {
        throw new Exception('Critère non trouvé');
    }

    // Supprimer le critère
    $stmt = $pdo->prepare("DELETE FROM criteres WHERE id = ?");
    $stmt->execute([$critereId]);

    // Valider la transaction
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Critère supprimé avec succès'
    ]);

} catch (Exception $e) {
    // Annuler la transaction en cas d'erreur
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 