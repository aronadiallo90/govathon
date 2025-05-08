<?php
header('Content-Type: application/json');
require_once '../includes/db.php';

session_start();

// Check if user is superadmin for password update permission
$currentUserRole = $_SESSION['role'] ?? '';

$data = $_POST;

if (empty($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID utilisateur manquant.']);
    exit;
}

$id = intval($data['id']);

try {
    // Fetch existing user data
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$existingUser) {
        echo json_encode(['success' => false, 'message' => 'Utilisateur non trouvé.']);
        exit;
    }

    // Prepare fields to update
    $fields = [];
    $params = [];

    if (isset($data['name']) && $data['name'] !== '') {
        $fields[] = 'name = ?';
        $params[] = trim($data['name']);
    }

    if (isset($data['email']) && $data['email'] !== '') {
        // Check if email already exists for another user
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?");
        $stmtCheck->execute([trim($data['email']), $id]);
        if ($stmtCheck->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'message' => 'L\'email existe déjà.']);
            exit;
        }
        $fields[] = 'email = ?';
        $params[] = trim($data['email']);
    }

    if (isset($data['role']) && $data['role'] !== '') {
        $fields[] = 'role = ?';
        $params[] = $data['role'];
    }

    if (isset($data['secteur_id'])) {
        if ($data['secteur_id'] === '') {
            $fields[] = 'secteur_id = NULL';
        } else {
            $fields[] = 'secteur_id = ?';
            $params[] = intval($data['secteur_id']);
        }
    }

    if (isset($data['is_active'])) {
        $fields[] = 'is_active = ?';
        $params[] = intval($data['is_active']);
    }

    if (isset($data['is_global_jury'])) {
        $fields[] = 'is_global_jury = ?';
        $params[] = intval($data['is_global_jury']);
    }

    // Password update only if superadmin and password provided
    if ($currentUserRole === 'superadmin') {
        if (isset($data['password']) && $data['password'] !== '') {
            $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
            $fields[] = 'password = ?';
            $params[] = $hashed_password;
        }
    }

    if (empty($fields)) {
        echo json_encode(['success' => false, 'message' => 'Aucun champ à mettre à jour.']);
        exit;
    }

    $params[] = $id;
    $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    echo json_encode(['success' => true, 'message' => 'Utilisateur mis à jour avec succès.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
