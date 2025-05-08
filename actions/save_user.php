<?php

header('Content-Type: application/json');
require_once '../includes/db.php';

$data = $_POST;

if (empty($data['name']) || empty($data['email']) || empty($data['role']) || empty($data['password'])) {
    echo json_encode(['success' => false, 'message' => 'Nom, email, rôle et mot de passe sont obligatoires.']);
    exit;
}

$name = trim($data['name']);
$email = trim($data['email']);
$role = $data['role'];
$secteur_id = isset($data['secteur_id']) && $data['secteur_id'] !== '' ? intval($data['secteur_id']) : null;
$is_active = isset($data['is_active']) ? intval($data['is_active']) : 1;
$is_global_jury = isset($data['is_global_jury']) ? intval($data['is_global_jury']) : 0;
$password = $data['password'];

try {
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'message' => 'L\'email existe déjà.']);
        exit;
    }
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, secteur_id, is_active, is_global_jury) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$name, $email, $hashed_password, $role, $secteur_id, $is_active, $is_global_jury]);
    echo json_encode(['success' => true, 'message' => 'Utilisateur créé avec succès.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
