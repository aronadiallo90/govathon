<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'superadmin'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID de jury invalide']);
    exit;
}

$juryId = (int)$_GET['id'];

try {
    $stmt = $pdo->prepare("
        SELECT 
            u.id,
            u.name,
            u.email,
            u.secteur_id,
            u.is_global_jury,
            u.is_active
        FROM users u
        WHERE u.id = ? AND u.role = 'jury'
        LIMIT 1
    ");
    $stmt->execute([$juryId]);
    $jury = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$jury) {
        echo json_encode(['success' => false, 'message' => 'Jury non trouvé']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'jury' => $jury
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
