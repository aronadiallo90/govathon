<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_role'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

try {
    // Get dynamic fields first
$dynamicFieldsStmt = $pdo->query("SELECT * FROM dynamic_field_definitions ORDER BY id ASC");
    $dynamicFields = $dynamicFieldsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Get projects with their values
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
            ) as dynamic_values
        FROM projects p
        LEFT JOIN secteurs s ON p.secteur_id = s.id
        LEFT JOIN users u ON p.created_by = u.id
        LEFT JOIN project_dynamic_values pdv ON p.id = pdv.project_id
        GROUP BY p.id, p.nom, p.description, p.status, p.secteur_id, s.nom, u.email
        ORDER BY p.created_at DESC
    ");
    
    $stmt->execute();
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'projects' => $projects,
        'dynamicFields' => $dynamicFields
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>