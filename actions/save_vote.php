<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

// Vérifier les droits d'accès
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['jury', 'admin', 'superadmin'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Accès non autorisé'
    ]);
    exit;
}

function validateVote($userId, $projectId, $critereId, $etapeId, $note) {
    global $pdo;
    
    // Vérifier la contrainte UNIQUE sur les votes
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM votes 
        WHERE user_id = ? AND project_id = ? AND critere_id = ? AND etape_id = ?
    ");
    $stmt->execute([$userId, $projectId, $critereId, $etapeId]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception('Vote déjà existant');
    }

    // Vérifier que le projet est dans l'étape actuelle
    $stmt = $pdo->prepare("
        SELECT 1 FROM project_etapes 
        WHERE project_id = ? AND etape_id = ? AND status = 'en_cours'
    ");
    $stmt->execute([$projectId, $etapeId]);
    if (!$stmt->fetch()) {
        throw new Exception('Le projet n\'est pas dans cette étape');
    }
}

try {
    // Récupérer les données JSON
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['project_id']) || !isset($data['etape_id']) || !isset($data['votes'])) {
        throw new Exception('Données manquantes');
    }

    $userId = $_SESSION['user_id'];
    $projectId = intval($data['project_id']);
    $etapeId = intval($data['etape_id']);
    $votes = $data['votes'];

    // Vérifier si l'utilisateur est jury global ou non
    $stmt = $pdo->prepare("SELECT is_global_jury, secteur_id FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception('Utilisateur introuvable');
    }

    // Si jury non global, vérifier que le projet appartient au même secteur
    if (!$user['is_global_jury']) {
        $stmt = $pdo->prepare("SELECT secteur_id FROM projects WHERE id = ?");
        $stmt->execute([$projectId]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$project || $project['secteur_id'] != $user['secteur_id']) {
            throw new Exception('Vous ne pouvez pas voter pour ce projet car il n\'appartient pas à votre secteur');
        }
    }

    // Démarrer une transaction
    $pdo->beginTransaction();

    // Supprimer les votes existants pour ce projet et cette étape
    $stmt = $pdo->prepare("DELETE FROM votes WHERE user_id = ? AND project_id = ? AND etape_id = ?");
    $stmt->execute([$userId, $projectId, $etapeId]);

    // Insérer les nouveaux votes
    $stmt = $pdo->prepare("INSERT INTO votes (user_id, project_id, critere_id, etape_id, note, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
    
    foreach ($votes as $vote) {
        if (!isset($vote['critere_id']) || !isset($vote['note'])) {
            continue;
        }
        
        $critereId = intval($vote['critere_id']);
        $note = floatval($vote['note']);
        
        // Vérifier que la note est valide (entre 0 et 10)
        if ($note < 0 || $note > 10) {
            continue;
        }

        // Valider le vote
        validateVote($userId, $projectId, $critereId, $etapeId, $note);
        
        $stmt->execute([$userId, $projectId, $critereId, $etapeId, $note]);
    }

    // Valider la transaction
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Votes enregistrés avec succès'
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
?>
