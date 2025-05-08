<?php
header('Content-Type: application/json');
require_once '../includes/db.php';

try {
    $stmt = $pdo->prepare("SELECT users.id, users.name, users.email, users.role, users.secteur_id, users.is_active, secteurs.nom AS secteur_name
                           FROM users
                           LEFT JOIN secteurs ON users.secteur_id = secteurs.id
                           ORDER BY users.id ASC");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'users' => $users]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
