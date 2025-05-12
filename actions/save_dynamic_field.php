<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'superadmin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$action = $data['action'] ?? '';
$field_name = trim($data['field_name'] ?? '');
$field_type = $data['field_type'] ?? 'text';
$is_required = !empty($data['is_required']) ? 1 : 0;
$id = $data['id'] ?? null;

if (empty($field_name)) {
    echo json_encode(['success' => false, 'message' => 'Field name is required']);
    exit;
}

try {
    if ($action === 'create') {
        $stmt = $pdo->prepare("INSERT INTO dynamic_field_definitions (field_name, field_type, is_required) VALUES (?, ?, ?)");
        $stmt->execute([$field_name, $field_type, $is_required]);
        echo json_encode(['success' => true, 'message' => 'Field created']);
    } elseif ($action === 'update' && $id) {
        $stmt = $pdo->prepare("UPDATE dynamic_field_definitions SET field_name = ?, field_type = ?, is_required = ? WHERE id = ?");
        $stmt->execute([$field_name, $field_type, $is_required, $id]);
        echo json_encode(['success' => true, 'message' => 'Field updated']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action or missing id']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
