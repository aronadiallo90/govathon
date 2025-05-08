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

    if (empty($data['id'])) {
        throw new Exception('ID du projet manquant');
    }

    $pdo->beginTransaction();

    // Delete dynamic field values
    $stmt = $pdo->prepare("DELETE FROM project_dynamic_values WHERE project_id = ?");
    $stmt->execute([$data['id']]);

    // Delete the project
    $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
    $stmt->execute([$data['id']]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Projet supprimé avec succès'
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
?>
