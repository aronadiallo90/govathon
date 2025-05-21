<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_role'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

try {    // Construction de la requête de base
    $query = "
        SELECT 
            p.*,
            s.nom as secteur_nom,
            u.email as created_by_email,
            (SELECT AVG(CAST(note AS DECIMAL(10,2))) FROM votes v WHERE v.project_id = p.id) as note_moyenne
        FROM projects p
        LEFT JOIN secteurs s ON p.secteur_id = s.id
        LEFT JOIN users u ON p.created_by = u.id
        WHERE 1=1
    ";
    $params = [];

    // Ajout des filtres
    if (isset($_GET['sector']) && $_GET['sector'] !== 'all') {
        $query .= " AND p.secteur_id = ?";
        $params[] = $_GET['sector'];
    }

    if (isset($_GET['status']) && $_GET['status'] !== 'all') {
        $query .= " AND p.status = ?";
        $params[] = $_GET['status'];
    }

    // Groupement et tri
    $query .= " GROUP BY p.id ORDER BY p.created_at DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupération des champs dynamiques
    $stmtFields = $pdo->query("SELECT * FROM dynamic_field_definitions ORDER BY id ASC");
    $dynamicFields = $stmtFields->fetchAll(PDO::FETCH_ASSOC);    // Récupération des valeurs dynamiques pour chaque projet
    foreach ($projects as &$project) {
        // Récupérer directement les valeurs dynamiques
        $stmt = $pdo->prepare("
            SELECT field_id, field_value 
            FROM project_dynamic_values 
            WHERE project_id = ?
        ");
        $stmt->execute([$project['id']]);
        $dynamicValues = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $dynamicValues[$row['field_id']] = $row['field_value'];
        }
        $project['dynamic_values'] = $dynamicValues;
    }

    echo json_encode([
        'success' => true,
        'projects' => $projects,
        'dynamicFields' => $dynamicFields
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la récupération des projets: ' . $e->getMessage()
    ]);
}
?>