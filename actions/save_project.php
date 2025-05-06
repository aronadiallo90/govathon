<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'superadmin'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Accès non autorisé'
    ]);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validation des champs requis
    if (empty($data['project_name']) || empty($data['project_description']) || 
        empty($data['project_sector'])) {
        throw new Exception('Tous les champs obligatoires doivent être remplis');
    }

    $pdo->beginTransaction();

    // Valider les champs dynamiques requis
    $stmt = $pdo->query("SELECT id, field_name, is_required FROM dynamic_field_definitions WHERE is_required = 1");
    $requiredFields = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($requiredFields as $field) {
        if (empty($data['dynamic_fields'][$field['id']])) {
            throw new Exception("Le champ {$field['field_name']} est obligatoire");
        }
    }

    // Insert or update project
    if (isset($data['id'])) {
        $stmt = $pdo->prepare("
            UPDATE projects 
            SET nom = :nom,
                description = :description,
                secteur_id = :secteur_id,
                status = :status,
                updated_at = NOW()
            WHERE id = :id
        ");
        
        $params = [
            'nom' => $data['project_name'],
            'description' => $data['project_description'],
            'secteur_id' => $data['project_sector'],
            'status' => $data['project_status'] ?? 'draft',
            'id' => $data['id']
        ];
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO projects (
                nom, 
                description, 
                secteur_id, 
                status, 
                created_by,
                created_at
            ) VALUES (
                :nom, 
                :description, 
                :secteur_id, 
                :status, 
                :created_by,
                NOW()
            )
        ");
        
        $params = [
            'nom' => $data['project_name'],
            'description' => $data['project_description'],
            'secteur_id' => $data['project_sector'],
            'status' => $data['project_status'] ?? 'draft',
            'created_by' => $_SESSION['user_id']
        ];
    }

    if (!$stmt->execute($params)) {
        throw new Exception('Erreur lors de la sauvegarde du projet');
    }

    $projectId = isset($data['id']) ? $data['id'] : $pdo->lastInsertId();

    // Gestion des champs dynamiques
    if (isset($data['dynamic_fields'])) {
        // Supprimer les anciennes valeurs si modification
        if (isset($data['id'])) {
            $stmt = $pdo->prepare("DELETE FROM project_dynamic_values WHERE project_id = ?");
            $stmt->execute([$projectId]);
        }

        // Insérer les nouvelles valeurs
        $stmt = $pdo->prepare("
            INSERT INTO project_dynamic_values (project_id, field_id, field_value)
            VALUES (:project_id, :field_id, :field_value)
        ");

        foreach ($data['dynamic_fields'] as $fieldId => $value) {
            $stmt->execute([
                'project_id' => $projectId,
                'field_id' => $fieldId,
                'field_value' => $value
            ]);
        }
    }

    $pdo->commit();

    // Récupérer le projet mis à jour avec toutes ses informations
    $stmt = $pdo->prepare("
        SELECT 
            p.*,
            s.nom as secteur_name,
            u.name as created_by_name,
            COALESCE((SELECT AVG(note) FROM evaluations e WHERE e.project_id = p.id), 0) as note_moyenne
        FROM projects p
        LEFT JOIN secteurs s ON p.secteur_id = s.id
        LEFT JOIN users u ON p.created_by = u.id
        WHERE p.id = ?
    ");
    $stmt->execute([$projectId]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'message' => isset($data['id']) ? 'Projet mis à jour avec succès' : 'Projet créé avec succès',
        'project' => $project
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