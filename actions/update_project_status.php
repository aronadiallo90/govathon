<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Vérification de l'authentification et du rôle admin
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'superadmin'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé.']);
    exit;
}

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$project_id = $data['project_id'] ?? null;
$action = $data['action'] ?? null; // 'approve' ou 'reject'
$preselection_notes = $data['preselection_notes'] ?? null;

if (!$project_id || !$action) {
    echo json_encode(['success' => false, 'message' => 'Paramètres manquants.']);
    exit;
}

try {
    $pdo->beginTransaction();

    $new_status = '';
    switch ($action) {
        case 'approve':
            $new_status = 'submitted';
            break;
        case 'reject':
            $new_status = 'rejected';
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Action invalide.']);
            $pdo->rollBack();
            exit;
    }

    // Mettre à jour le statut du projet
    $stmt_update_status = $pdo->prepare("
        UPDATE projects
        SET status = ?
        WHERE id = ?
    ");
    $stmt_update_status->execute([$new_status, $project_id]);

    // Gérer les notes de présélection si fournies
    if ($preselection_notes !== null) {
         // Tenter de trouver l'ID du champ dynamique 'Preselection Notes'
         $stmt_notes_field = $pdo->prepare("SELECT id FROM dynamic_field_definitions WHERE field_name = 'Preselection Notes' LIMIT 1");
         $stmt_notes_field->execute();
         $notes_field = $stmt_notes_field->fetch();

         if ($notes_field) {
             // Insérer ou mettre à jour la note de présélection
             $stmt_insert_notes = $pdo->prepare("
                 INSERT INTO project_dynamic_values (project_id, field_id, field_value)
                 VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE field_value = VALUES(field_value)
             ");
             $stmt_insert_notes->execute([$project_id, $notes_field['id'], $preselection_notes]);
         } else {
             // Optionnel: Logguer une erreur si le champ 'Preselection Notes' n'existe pas
             error_log("Le champ dynamique 'Preselection Notes' n'est pas défini.");
         }
    }


    // Si le projet est approuvé, l'ajouter à l'étape 'Présélection' si elle n'est pas terminée
    if ($new_status === 'submitted') {
        // Trouver l'étape de présélection active
        $stmt_etape_preselection = $pdo->prepare("SELECT id FROM etapes WHERE nom = 'Présélection' AND statut != 'terminee' LIMIT 1");
        $stmt_etape_preselection->execute();
        $etape_preselection = $stmt_etape_preselection->fetch();

        if ($etape_preselection) {
            // Vérifier si le projet n'est pas déjà dans cette étape (normalement géré par l'unicité)
            $stmt_check_etape = $pdo->prepare("SELECT COUNT(*) FROM project_etapes WHERE project_id = ? AND etape_id = ?");
            $stmt_check_etape->execute([$project_id, $etape_preselection['id']]);
            $count = $stmt_check_etape->fetchColumn();

            if ($count == 0) {
                // Ajouter le projet à l'étape de présélection
                $stmt_add_etape = $pdo->prepare("
                    INSERT INTO project_etapes (project_id, etape_id, status)
                    VALUES (?, ?, 'en_cours')
                ");
                $stmt_add_etape->execute([$project_id, $etape_preselection['id']]);
            }
        } else {
            // Optionnel: Logguer une erreur si l'étape de présélection active n'est pas trouvée
            error_log("Aucune étape de 'Présélection' active trouvée pour ajouter le projet.");
        }
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Statut du projet mis à jour avec succès.']);

} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur : ' . $e->getMessage()]);
} 