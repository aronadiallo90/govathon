<?php
session_start();
require_once 'auth.php';
requireLogin();
requireAdmin();
require_once 'config.php';

// Read JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Données invalides']);
    exit;
}

$id = $input['id'] ?? null;
$nom = trim($input['nom'] ?? '');
$equipe = trim($input['equipe'] ?? '');
$secteur = intval($input['secteur'] ?? 0);
$description = trim($input['description'] ?? '');
$status = trim($input['status'] ?? '');
$dynamic_fields = $input['dynamic_fields'] ?? [];

if (!$nom || !$equipe || !$secteur) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Champs obligatoires manquants']);
    exit;
}

try {
    if ($id) {
        // Update existing project
        $setParts = ['nom = ?', 'created_by = ?', 'secteur_id = ?'];
        $params = [$nom, $equipe, $secteur];

        // Add dynamic fields to update
        foreach ($dynamic_fields as $fieldName => $fieldValue) {
            $setParts[] = "`$fieldName` = ?";
            $params[] = $fieldValue;
        }

        $params[] = $id;
        $setClause = implode(', ', $setParts);
        $stmt = $pdo->prepare("UPDATE projects SET $setClause WHERE id = ?");
        $stmt->execute($params);
    } else {
        // Insert new project
        $columns = ['nom', 'created_by', 'secteur_id'];
        $placeholders = ['?', '?', '?'];
        $params = [$nom, $equipe, $secteur];

        // Add dynamic fields columns and values
        foreach ($dynamic_fields as $fieldName => $fieldValue) {
            $columns[] = "`$fieldName`";
            $placeholders[] = '?';
            $params[] = $fieldValue;
        }

        $columnsList = implode(', ', $columns);
        $placeholdersList = implode(', ', $placeholders);

        $stmt = $pdo->prepare("INSERT INTO projects ($columnsList) VALUES ($placeholdersList)");
        $stmt->execute($params);
        $id = $pdo->lastInsertId();
    }

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
}
?>
