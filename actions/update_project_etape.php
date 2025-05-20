<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'superadmin'])) {
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['project_id']) || !isset($data['etape_id']) || !isset($data['action'])) {
        throw new Exception('Données manquantes');
    }

    $project_id = intval($data['project_id']);
    $etape_id = intval($data['etape_id']);
    
    // Vérifier que le projet et l'étape existent
    $stmt = $pdo->prepare("SELECT id FROM projects WHERE id = ?");
    $stmt->execute([$project_id]);
    if (!$stmt->fetch()) {
        throw new Exception('Projet non trouvé');
    }

    $stmt = $pdo->prepare("SELECT id FROM etapes WHERE id = ?");
    $stmt->execute([$etape_id]);
    if (!$stmt->fetch()) {
        throw new Exception('Étape non trouvée');
    }

    $pdo->beginTransaction();

    if ($data['action'] === 'add') {
        // Ajouter le projet à l'étape
        $stmt = $pdo->prepare("
            INSERT INTO project_etapes (project_id, etape_id, status) 
            VALUES (?, ?, 'en_cours')
            ON DUPLICATE KEY UPDATE status = 'en_cours'
        ");
        $stmt->execute([$project_id, $etape_id]);
        $message = 'Projet ajouté à l\'étape avec succès';
    }
    else if ($data['action'] === 'remove') {
        // Supprimer le projet de l'étape
        $stmt = $pdo->prepare("
            DELETE FROM project_etapes 
            WHERE project_id = ? AND etape_id = ?
        ");
        $stmt->execute([$project_id, $etape_id]);
        
        if ($stmt->rowCount() === 0) {
            throw new Exception('Le projet n\'est pas dans cette étape');
        }
        $message = 'Projet retiré de l\'étape avec succès';
    }
    else {
        throw new Exception('Action non valide');
    }

    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => $message
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}