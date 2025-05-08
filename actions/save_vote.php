<?php
session_start();
require_once '../includes/db.php';

// Vérifier les droits d'accès
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['jury', 'admin', 'superadmin'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Accès non autorisé'
    ]);
    exit;
}

try {
    $userId = $_SESSION['user_id'] ?? 0;
    $projectId = intval($_POST['project_id'] ?? 0);
    $critereId = intval($_POST['critere_id'] ?? 0);
    $etapeId = intval($_POST['etape_id'] ?? 0);
    $note = floatval($_POST['note'] ?? 0);

    if ($userId <= 0 || $projectId <= 0 || $critereId <= 0 || $etapeId <= 0) {
        throw new Exception('Données invalides');
    }

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

    // Vérifier si l'utilisateur a déjà voté pour ce projet, critère et étape
    $stmt = $pdo->prepare("SELECT id FROM votes WHERE user_id = ? AND project_id = ? AND critere_id = ? AND etape_id = ?");
    $stmt->execute([$userId, $projectId, $critereId, $etapeId]);
    $existingVote = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingVote) {
        // Mettre à jour le vote existant
        $stmt = $pdo->prepare("UPDATE votes SET note = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$note, $existingVote['id']]);
    } else {
        // Insérer un nouveau vote
        $stmt = $pdo->prepare("INSERT INTO votes (user_id, project_id, critere_id, etape_id, note, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->execute([$userId, $projectId, $critereId, $etapeId, $note]);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Vote enregistré avec succès'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
