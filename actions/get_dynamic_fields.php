<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'superadmin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $stmt = $pdo->query('SELECT * FROM dynamic_field_definitions ORDER BY created_at DESC');
    $fields = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'fields' => $fields]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
