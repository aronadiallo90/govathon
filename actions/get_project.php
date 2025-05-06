<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_role'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID de projet invalide']);
    exit;
}

$projectId = (int)$_GET['id'];

try {
    // Get dynamic fields
    $dynamicFieldsStmt = $pdo->query("SELECT * FROM dynamic_field_definitions ORDER BY id ASC");
    $dynamicFields = $dynamicFieldsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Get project with dynamic values
    $stmt = $pdo->prepare("
        SELECT 
            p.*,
            s.nom as secteur_name,
            u.email as created_by_email,
            COALESCE(
                (SELECT AVG(note) 
                 FROM evaluations e 
                 WHERE e.project_id = p.id
                ), 
                0
            ) as note_moyenne,
            GROUP_CONCAT(
                CONCAT(pdv.field_id, ':', COALESCE(pdv.field_value, ''))
                SEPARATOR '||'
            ) as dynamic_fields
        FROM projects p
        LEFT JOIN secteurs s ON p.secteur_id = s.id
        LEFT JOIN users u ON p.created_by = u.id
        LEFT JOIN project_dynamic_values pdv ON p.id = pdv.project_id
        WHERE p.id = ?
        GROUP BY p.id, p.nom, p.description, p.status, p.secteur_id, s.nom, u.email
        LIMIT 1
    ");
    $stmt->execute([$projectId]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$project) {
        echo json_encode(['success' => false, 'message' => 'Projet non trouvé']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'project' => $project,
        'dynamicFields' => $dynamicFields
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
