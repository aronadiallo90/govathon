<?php
header('Content-Type: application/json');
require_once '../includes/db.php';

if (empty($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID utilisateur manquant.']);
    exit;
}

$id = intval($_GET['id']);

try {
    $stmt = $pdo->prepare("SELECT id, name, email, role, secteur_id, is_active, is_global_jury FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo json_encode(['success' => true, 'user' => $user]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Utilisateur non trouvé.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
