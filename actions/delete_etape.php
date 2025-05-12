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
    // Récupérer les données JSON
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id']) || !is_numeric($data['id'])) {
        throw new Exception('ID d\'étape invalide');
    }

    $etapeId = intval($data['id']);

    // Vérifier si l'étape existe
    $stmt = $pdo->prepare("SELECT id FROM etapes WHERE id = ?");
    $stmt->execute([$etapeId]);
    if (!$stmt->fetch()) {
        throw new Exception('Étape non trouvée');
    }

    // Vérifier si l'étape est active
    $stmt = $pdo->prepare("SELECT etat FROM etapes WHERE id = ?");
    $stmt->execute([$etapeId]);
    $etape = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($etape['etat'] === 'active') {
        throw new Exception('Impossible de supprimer une étape active');
    }

    // Vérifier s'il y a des votes associés à cette étape
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM votes WHERE etape_id = ?");
    $stmt->execute([$etapeId]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception('Impossible de supprimer une étape qui contient des votes');
    }

    // Démarrer une transaction
    $pdo->beginTransaction();

    // Supprimer l'étape
    $stmt = $pdo->prepare("DELETE FROM etapes WHERE id = ?");
    $stmt->execute([$etapeId]);

    // Valider la transaction
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Étape supprimée avec succès'
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