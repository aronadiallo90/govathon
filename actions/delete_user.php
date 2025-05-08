<?php
header('Content-Type: application/json');
require_once '../includes/db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID utilisateur manquant.']);
    exit;
}

$id = intval($data['id']);

try {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['success' => true, 'message' => 'Utilisateur supprimé avec succès.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
