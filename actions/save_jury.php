<?php
session_start();
require_once '../includes/db.php';

// Vérifier les droits d'accès
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'superadmin'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Accès non autorisé'
    ]);
    exit;
}

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

// Validation
if (empty($data['name']) || empty($data['email'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Nom et email requis'
    ]);
    exit;
}

try {
    if (isset($data['id'])) {
        // Mise à jour
        $sql = "UPDATE users SET 
                name = ?,
                email = ?,
                secteur_id = ?,
                is_global_jury = ?,
                is_active = ?
                WHERE id = ? AND role = 'jury'";
        
        $params = [
            $data['name'],
            $data['email'],
            empty($data['secteur_id']) ? null : $data['secteur_id'],
            $data['is_global_jury'] ?? false,
            $data['is_active'] ?? true,
            $data['id']
        ];

        // Mise à jour du mot de passe si fourni
        if (!empty($data['password'])) {
            $sql = "UPDATE users SET 
                    name = ?,
                    email = ?,
                    password = ?,
                    secteur_id = ?,
                    is_global_jury = ?,
                    is_active = ?
                    WHERE id = ? AND role = 'jury'";
            array_splice($params, 2, 0, [password_hash($data['password'], PASSWORD_DEFAULT)]);
        }

    } else {
        // Insertion
        $sql = "INSERT INTO users (
                name, email, password, role, secteur_id, is_global_jury, is_active
            ) VALUES (?, ?, ?, 'jury', ?, ?, ?)";
        
        $params = [
            $data['name'],
            $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            empty($data['secteur_id']) ? null : $data['secteur_id'],
            $data['is_global_jury'] ?? false,
            $data['is_active'] ?? true
        ];
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    echo json_encode([
        'success' => true,
        'message' => isset($data['id']) ? 'Jury modifié avec succès' : 'Jury ajouté avec succès'
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la sauvegarde du jury'
    ]);
}