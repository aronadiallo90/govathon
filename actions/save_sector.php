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

// Validation des données
if (empty($data['nom']) || empty($data['icon'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Nom et icône requis'
    ]);
    exit;
}

try {
    if (isset($data['id']) && is_numeric($data['id'])) {
        // Update
        $stmt = $pdo->prepare("
            UPDATE secteurs 
            SET nom = ?, 
                description = ?, 
                icon = ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        $stmt->execute([
            $data['nom'],
            $data['description'] ?? '',
            $data['icon'],
            $data['id']
        ]);

        $success = $stmt->rowCount() > 0;
    } else {
        // Insert
        $stmt = $pdo->prepare("
            INSERT INTO secteurs (nom, description, icon)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([
            $data['nom'],
            $data['description'] ?? '',
            $data['icon']
        ]);

        $success = $stmt->rowCount() > 0;
    }
    
    if ($success) {
        echo json_encode([
            'success' => true,
            'id' => isset($data['id']) ? $data['id'] : $pdo->lastInsertId()
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Aucune modification effectuée'
        ]);
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la sauvegarde'
    ]);
}