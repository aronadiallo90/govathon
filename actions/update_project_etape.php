<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Vérification de l'authentification
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'superadmin'])) {
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
    exit;
}

// Récupération des données
$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';
$project_id = $data['project_id'] ?? null;
$etape_id = $data['etape_id'] ?? null;

if (!$project_id || !$etape_id) {
    echo json_encode(['success' => false, 'message' => 'Données manquantes']);
    exit;
}

try {
    if ($action === 'add') {
        // Vérifier si le projet est déjà dans une étape
        $stmt = $pdo->prepare("SELECT etape_id FROM project_etapes WHERE project_id = ?");
        $stmt->execute([$project_id]);
        $existing_etape = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing_etape) {
            // Si le projet est déjà dans une étape, on le déplace
            $stmt = $pdo->prepare("UPDATE project_etapes SET etape_id = ?, status = 'en_cours', updated_at = CURRENT_TIMESTAMP WHERE project_id = ?");
            $stmt->execute([$etape_id, $project_id]);
            $message = 'Projet déplacé vers la nouvelle étape';
        } else {
            // Si le projet n'est dans aucune étape, on l'ajoute
            $stmt = $pdo->prepare("INSERT INTO project_etapes (project_id, etape_id, status) VALUES (?, ?, 'en_cours')");
            $stmt->execute([$project_id, $etape_id]);
            $message = 'Projet ajouté à l\'étape';
        }
    } elseif ($action === 'remove') {
        $stmt = $pdo->prepare("DELETE FROM project_etapes WHERE project_id = ? AND etape_id = ?");
        $stmt->execute([$project_id, $etape_id]);
        $message = 'Projet retiré de l\'étape';
    } else {
        throw new Exception('Action non valide');
    }

    echo json_encode(['success' => true, 'message' => $message]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
} 