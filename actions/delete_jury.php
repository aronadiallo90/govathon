<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'superadmin'])) {
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id']) || !is_numeric($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID de jury invalide']);
    exit;
}

$juryId = (int)$data['id'];

try {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'jury'");
    $stmt->execute([$juryId]);

    if ($stmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Jury non trouvé ou déjà supprimé']);
        exit;
    }

    echo json_encode(['success' => true, 'message' => 'Jury supprimé avec succès']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
