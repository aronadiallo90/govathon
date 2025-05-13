<?php

header('Content-Type: application/json');
require_once '../includes/db.php';

$data = json_decode(file_get_contents('php://input'), true);

// Pour un nouvel utilisateur
if (!isset($data['id'])) {
    if (empty($data['name']) || empty($data['email']) || empty($data['role']) || empty($data['password'])) {
        echo json_encode(['success' => false, 'message' => 'Tous les champs sont obligatoires pour un nouvel utilisateur']);
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
} 
// Pour la modification
else {
    if (empty($data['role'])) {
        echo json_encode(['success' => false, 'message' => 'Le rôle est obligatoire']);
        exit;
    }
    
    $updateFields = [];
    $params = [];
    
    // Ajouter uniquement les champs présents
    if (!empty($data['name'])) {
        $updateFields[] = "name = ?";
        $params[] = $data['name'];
    }
    if (!empty($data['email'])) {
        $updateFields[] = "email = ?";
        $params[] = $data['email'];
    }
    if (!empty($data['password'])) {
        $updateFields[] = "password = ?";
        $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
    }
    
    $updateFields[] = "role = ?";
    $params[] = $data['role'];
    
    $updateFields[] = "secteur_id = ?";
    $params[] = $data['secteur_id'];
    
    if (isset($data['is_active'])) {
        $updateFields[] = "is_active = ?";
        $params[] = $data['is_active'];
    }
    if (isset($data['is_global_jury'])) {
        $updateFields[] = "is_global_jury = ?";
        $params[] = $data['is_global_jury'];
    }
    
    $params[] = $data['id'];
    
    try {
        $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        echo json_encode(['success' => true, 'message' => 'Utilisateur mis à jour avec succès']);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>
