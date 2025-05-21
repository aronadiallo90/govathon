<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/ProjectManager.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'superadmin'])) {
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['action']) || !isset($data['project_id'])) {
        throw new Exception('Données manquantes');
    }

    $pdo->beginTransaction();

    switch ($data['action']) {
        case 'add':
            if (!isset($data['etape_id'])) {
                throw new Exception('ID de l\'étape manquant');
            }
            $stmt = $pdo->prepare("
                INSERT INTO project_etapes (project_id, etape_id, status)
                VALUES (?, ?, 'en_cours')
            ");
            $stmt->execute([$data['project_id'], $data['etape_id']]);
            $message = 'Projet ajouté à l\'étape avec succès';
            break;

        case 'remove':
            if (!isset($data['etape_id'])) {
                throw new Exception('ID de l\'étape manquant');
            }

            // Récupérer l'ordre de l'étape actuelle
            $stmt = $pdo->prepare("
                SELECT ordre FROM etapes WHERE id = ?
            ");
            $stmt->execute([$data['etape_id']]);
            $current_ordre = $stmt->fetchColumn();

            // Supprimer le projet de l'étape actuelle et de toutes les étapes supérieures
            $stmt = $pdo->prepare("
                DELETE pe FROM project_etapes pe
                INNER JOIN etapes e ON pe.etape_id = e.id
                WHERE pe.project_id = ? 
                AND e.ordre >= ?
            ");
            $stmt->execute([$data['project_id'], $current_ordre]);
            $message = 'Projet retiré de l\'étape et des étapes suivantes avec succès';
            break;

        case 'preselect':
            // Récupérer l'étape actuelle du projet et son ordre
            $stmt = $pdo->prepare("
                SELECT e.ordre, e.id as etape_id
                FROM project_etapes pe
                JOIN etapes e ON pe.etape_id = e.id
                WHERE pe.project_id = ?
                ORDER BY e.ordre DESC
                LIMIT 1
            ");
            $stmt->execute([$data['project_id']]);
            $current_etape = $stmt->fetch();

            if (!$current_etape) {
                throw new Exception("Le projet n'est assigné à aucune étape");
            }

            // Trouver l'étape suivante dans l'ordre
            $stmt = $pdo->prepare("
                SELECT id 
                FROM etapes 
                WHERE ordre > ? 
                ORDER BY ordre ASC 
                LIMIT 1
            ");
            $stmt->execute([$current_etape['ordre']]);
            $next_etape_id = $stmt->fetchColumn();

            if (!$next_etape_id) {
                throw new Exception("Il n'y a pas d'étape suivante disponible");
            }

            // Déplacer le projet vers l'étape suivante
            $stmt = $pdo->prepare("
                INSERT INTO project_etapes (project_id, etape_id, status)
                VALUES (?, ?, 'en_cours')
            ");
            $stmt->execute([$data['project_id'], $next_etape_id]);
            $message = 'Projet déplacé vers l\'étape suivante avec succès';
            break;

        default:
            throw new Exception('Action non valide');
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => $message]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>